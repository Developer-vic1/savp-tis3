"""Auditoría estática: nunca importa Laravel, ejecuta PHP/SQL ni abre una conexión.

Lee declaraciones del repositorio y DDL del volcado, excluyendo COPY/INSERT.
El resultado describe archivos, no certifica el schema desplegado.
"""
from pathlib import Path
import re, json, csv, hashlib, copy
from collections import defaultdict

ROOT = Path(__file__).resolve().parents[3]
OUT = Path(__file__).resolve().parent

def read(p): return p.read_text(encoding='utf-8-sig', errors='replace')
def rel(p): return p.relative_to(ROOT).as_posix()
def sha(p): return hashlib.sha256(p.read_bytes()).hexdigest()
def save(name, obj): (OUT/name).write_text(json.dumps(obj, ensure_ascii=False, indent=2)+'\n', encoding='utf-8')
def csvout(name, rows, fields=None):
    if not fields: fields=list(rows[0]) if rows else []
    with (OUT/name).open('w', encoding='utf-8-sig', newline='') as f:
        w=csv.DictWriter(f, fieldnames=fields); w.writeheader(); w.writerows(rows)

def stripped(s):
    # Preserve quoted strings and line numbers; remove PHP comments only.
    rx=r"'(?:\\.|[^'\\])*'|\"(?:\\.|[^\"\\])*\"|/\*[\s\S]*?\*/|//[^\n]*|\#[^\n]*"
    return re.sub(rx, lambda m: re.sub(r'[^\n]', ' ', m[0]) if m[0].startswith(('/*','//','#')) else m[0], s)

def end(s, start, left='(', right=')'):
    n=0; quote=None; escape=False
    for i in range(start,len(s)):
        c=s[i]
        if quote:
            if escape: escape=False
            elif c=='\\': escape=True
            elif c==quote: quote=None
        elif c in "'\"": quote=c
        elif c==left: n+=1
        elif c==right:
            n-=1
            if n==0: return i
    raise ValueError('Unbalanced declaration')

def split(s, sep=','):
    parts=[]; start=0; depth=0; quote=None; escape=False
    for i,c in enumerate(s):
        if quote:
            if escape: escape=False
            elif c=='\\': escape=True
            elif c==quote: quote=None
        elif c in "'\"": quote=c
        elif c in '([{': depth+=1
        elif c in ')]}': depth-=1
        elif c==sep and depth==0: parts.append(s[start:i].strip()); start=i+1
    parts.append(s[start:].strip()); return parts

def val(s):
    s=s.strip()
    if s.startswith('['): return [val(x) for x in split(s[1:-1]) if x]
    if len(s)>1 and s[0] in "'\"" and s[-1]==s[0]: return s[1:-1]
    return {'true':True,'false':False,'null':None}.get(s,s)

def arr(x): return x if isinstance(x,list) else [x]
def calls(s):
    out=[]
    for m in re.finditer(r'(?:\$table->|->)(\w+)\s*\(',s):
        a=s.index('(',m.start()); b=end(s,a)
        out.append((m[1],[val(x) for x in split(s[a+1:b]) if x]))
    return out

def teams_off(s):
    # Config current: teams=false; testing key is absent. Keep only else branches.
    rx=r'if\s*\(\$teams(?:\s*\|\|\s*config\([^)]*\))?\)\s*\{'
    while (m:=re.search(rx,s)):
        a=s.index('{',m.start()); b=end(s,a,'{','}'); tail=s[b+1:]
        em=re.match(r'\s*else\s*\{',tail)
        replacement=''; stop=b+1
        if em:
            ea=b+1+em.end()-1; eb=end(s,ea,'{','}'); replacement=s[ea+1:eb]; stop=eb+1
        s=s[:m.start()]+replacement+s[stop:]
    return s

def expand(s, full):
    # Literal foreach arrays and named constant CATALOGS only; never evaluate PHP.
    rx=r'foreach\s*\(\s*(\[[\s\S]*?\]|self::CATALOGS)\s+as\s+(\$\w+)(?:\s*=>\s*(\$\w+))?\s*\)\s*\{'
    while (m:=re.search(rx,s)):
        a=s.index('{',m.end()-1); b=end(s,a,'{','}'); body=s[a+1:b]; values=m[1]
        if values=='self::CATALOGS': values=re.search(r'const CATALOGS\s*=\s*(\[[\s\S]*?\]);', full)[1]
        copies=[]
        for item in split(values[1:-1]):
            if not item: continue
            kv=item.split('=>',1); pairs={m[2]:kv[0].strip()}
            if m[3]: pairs[m[3]]=kv[1].strip()
            copy=body
            for k,v in pairs.items(): copy=re.sub(re.escape(k)+r'\b',lambda _:v,copy)
            copies.append(copy)
        s=s[:m.start()]+'\n'.join(copies)+s[b+1:]
    return s

def migration_up(full):
    s=stripped(full); m=re.search(r'function up\([^)]*\)[^{]*\{',s)
    a=s.index('{',m.start()); return s[a+1:end(s,a,'{','}')]

def preprocess(s, full):
    s=teams_off(s)
    for name in ['roles','permissions','model_has_roles','model_has_permissions','role_has_permissions']:
        s=s.replace("$tableNames['"+name+"']",repr(name))
    for key,v in {'model_morph_key':'cod_usu','team_foreign_key':'team_id'}.items():
        s=s.replace("$columnNames['"+key+"']",repr(v))
    s=re.sub(r'\$pivotPermission\b',"'permission_id'",s); s=re.sub(r'\$pivotRole\b',"'role_id'",s)
    return expand(s, full)

def typepg(typ,args):
    size=args[1] if len(args)>1 else 255
    if typ in ['string','char']: return ('character varying' if typ=='string' else 'character')+f'({size})'
    if typ in ['id','bigIncrements','foreignId','unsignedBigInteger','bigInteger']: return 'bigint'
    if typ in ['integer','unsignedInteger','increments']: return 'integer'
    if typ in ['tinyInteger','unsignedTinyInteger','smallInteger','unsignedSmallInteger']: return 'smallint'
    if typ in ['text','longText','mediumText']: return 'text'
    if typ=='decimal': return f"numeric({args[1] if len(args)>1 else 8},{args[2] if len(args)>2 else 2})"
    if typ=='timestampTz': return 'timestamp with time zone'
    if typ in ['timestamp','dateTime']: return 'timestamp without time zone'
    if typ=='enum': return 'character varying(255) + CHECK'
    return typ

tables={}; historical=None; unresolved=[]; migrations=[]; checks=[]; operations=[]
def table(name,source,phase):
    if name not in tables: tables[name]={'table':name,'phase':phase,'sources':[],'columns':{},'indexes':[],'fks':[],'checks':[]}
    t=tables[name]
    if source not in t['sources']: t['sources'].append(source)
    return t

def addcol(t,name,typ,args,chain,source,phase):
    t['columns'][name]={'column':name,'type':typepg(typ,args),'blueprint':typ,'nullable':any(c[0]=='nullable' for c in chain),
       'default':next((c[1][0] for c in chain if c[0]=='default'), 'CURRENT_TIMESTAMP' if any(c[0]=='useCurrent' for c in chain) else 'AUTO_SEQUENCE (Blueprint)' if typ in ['id','bigIncrements','increments'] else ''),
       'source':source,'phase':phase,'enum':args[1] if typ=='enum' else [],'unsigned':typ.startswith('unsigned'),
       'checks':[],'auto_increment':typ in ['id','bigIncrements','increments']}

def addidx(t,kind,cols,name='',source='',phase=''):
    t['indexes'].append({'kind':kind,'columns':arr(cols),'name':name,'source':source,'phase':phase})

for p in sorted((ROOT/'database/migrations').rglob('*.php'),key=lambda p:(p.name,rel(p))):
    src=rel(p); full=read(p); phase='PROPUESTA_NO_EJECUTADA' if p.name.startswith('2026_09_30_10000') else 'HISTORICA_DECLARADA'
    if phase=='PROPUESTA_NO_EJECUTADA' and historical is None: historical=copy.deepcopy(tables)
    raw=migration_up(full); s=preprocess(raw, stripped(full)); migrations.append({'file':src,'phase':phase,'sha256':sha(p),'lines':len(full.splitlines())})
    for m in re.finditer(r"Schema::(create|table)\s*\(\s*('(?:[^']*)'|\"[^\"]*\")\s*,",s):
        name=val(m[2]); t=table(name,src,phase)
        if re.match(r'\s*fn\s*\(',s[m.end():]):
            stop=s.index(';',m.end()); body=s[m.end():stop]
        else:
            start=s.index('{',m.end()); stop=end(s,start,'{','}'); body=s[start+1:stop]
        for stm in re.finditer(r'\$table->',body):
            # Semicolon terminates each Blueprint chain, even inside hasColumn guards.
            stop=body.find(';',stm.start()); stmt=body[stm.start():stop if stop>=0 else len(body)]
            chain=calls(stmt); op,args=chain[0]; cmap={k:v for k,v in chain}
            if op in ['timestamps','timestampsTz']:
                for c in ['created_at','updated_at']: addcol(t,c,'timestampTz' if op.endswith('Tz') else 'timestamp',[],[('nullable',[])],src,phase)
            elif op=='rememberToken': addcol(t,'remember_token','string',['remember_token',100],[('nullable',[])],src,phase)
            elif op=='morphs':
                for c,typ,ca in [(args[0]+'_type','string',[]),(args[0]+'_id','unsignedBigInteger',[])]: addcol(t,c,typ,[c],[],src,phase)
                addidx(t,'INDEX',[args[0]+'_type',args[0]+'_id'],'',src,phase)
            elif op in ['primary','index','unique']:
                addidx(t,op.upper(),args[0],args[1] if len(args)>1 else '',src,phase)
            elif op=='foreign':
                t['fks'].append({'columns':arr(args[0]),'references':arr(cmap.get('references',['id'])[0]),'target':cmap.get('on',['?'])[0],
                    'delete':next(({'cascadeOnDelete':'CASCADE','restrictOnDelete':'RESTRICT','nullOnDelete':'SET NULL','noActionOnDelete':'NO ACTION'}.get(k,v[0] if v else '?') for k,v in chain if k.endswith('OnDelete') or k=='onDelete'),'NO ACTION'),
                    'update':next(({'cascadeOnUpdate':'CASCADE','restrictOnUpdate':'RESTRICT','nullOnUpdate':'SET NULL'}.get(k,v[0] if v else '?') for k,v in chain if k.endswith('OnUpdate') or k=='onUpdate'),'NO ACTION'),
                    'name':args[1] if len(args)>1 else '', 'source':src,'phase':phase})
            elif op.startswith('drop'):
                operations.append({'table':name,'operation':op,'args':args,'source':src,'phase':phase})
                if op=='dropForeign':
                    cols=arr(args[0]); t['fks']=[fk for fk in t['fks'] if fk['columns']!=cols]
                elif op=='dropColumn':
                    for c in arr(args[0]): t['columns'].pop(c,None)
            elif op in ['id','bigIncrements','string','char','text','longText','mediumText','uuid','date','time','dateTime','timestamp','timestampTz','boolean','integer','unsignedInteger','unsignedTinyInteger','tinyInteger','smallInteger','unsignedSmallInteger','unsignedBigInteger','bigInteger','decimal','json','jsonb','enum','foreignId']:
                c=args[0] if args else 'id'; addcol(t,c,op,args,chain,src,phase)
                if op in ['id','bigIncrements'] or 'primary' in cmap: addidx(t,'PRIMARY',[c],'',src,phase)
                for k in ['index','unique']:
                    if k in cmap: addidx(t,k.upper(),[c],cmap[k][0] if cmap[k] else '',src,phase)
                if op=='enum': t['checks'].append({'name':c+'_check','expression':f'{c} IN {args[1]}','source':src,'phase':phase,'implicit_enum':True})
                if 'constrained' in cmap:
                    ca=cmap['constrained']; t['fks'].append({'columns':[c],'references':['id'],'target':ca[0] if ca else c.removesuffix('_id')+'s',
                        'delete':'CASCADE' if 'cascadeOnDelete' in cmap else 'RESTRICT' if 'restrictOnDelete' in cmap else 'SET NULL' if 'nullOnDelete' in cmap else 'NO ACTION',
                        'update':'CASCADE' if 'cascadeOnUpdate' in cmap else 'NO ACTION','name':'','source':src,'phase':phase})
            else: unresolved.append({'source':src,'table':name,'statement':stmt.strip(),'op':op})
    # Raw DDL CHECK, literals only. Static expansion resolves foreach catalog names.
    for m in re.finditer(r'DB::statement\s*\(',s):
        a=s.index('(',m.start()); expr=s[a+1:end(s,a)]
        # Remove PHP quote delimiters/concatenation without evaluating any code.
        ddl=expr.strip()
        if ddl.startswith(('"',"'")): ddl=ddl[1:-1]
        ddl=re.sub(r"'\s*\.\s*'",'',ddl)
        ddl=re.sub(r"\{'(\w+)'\}",r'\1',ddl)
        cm=re.search(r'ALTER TABLE\s+(\w+)\s+ADD CONSTRAINT\s+(\w+)\s+CHECK\s*\(',ddl,re.I)
        if cm:
            a=ddl.index('(',cm.end()-1); expression=ddl[a+1:end(ddl,a)]
            t=table(cm[1],src,phase); t['checks'].append({'name':cm[2],'expression':expression.strip(),'source':src,'phase':phase,'not_valid':bool(re.search('NOT VALID',ddl,re.I))})
        else: unresolved.append({'source':src,'statement':expr.strip(),'op':'RAW_DDL'})

models=[]
for p in sorted((ROOT/'app/Models').rglob('*.php')):
    s=stripped(read(p)); name=re.search(r'class\s+(\w+)',s)[1]
    tm=re.search(r'\$table\s*=\s*[\'"]([^\'"]+)',s)
    t=tm[1] if tm else {'Role':'roles','RoleRequest':'role_requests'}.get(name,re.sub(r'(?<!^)(?=[A-Z])','_',name).lower()+'s')
    pk=re.search(r'\$primaryKey\s*=\s*[\'"]([^\'"]+)',s)
    f=re.search(r'\$fillable\s*=\s*(\[[\s\S]*?\]);',s)
    casts=re.search(r'(?:\$casts\s*=|function casts\([^)]*\)[^{]*\{\s*return)\s*(\[[\s\S]*?\]);',s)
    relations=[]
    for m in re.finditer(r'function\s+(\w+)\([^)]*\)[^{]*\{',s):
        a=s.index('{',m.start()); body=s[a+1:end(s,a,'{','}')]
        for rm in re.finditer(r'\$this->(belongsToMany|belongsTo|hasMany|hasOne|morphMany|morphTo|hasOneThrough|hasManyThrough)\s*\(',body):
            a=body.index('(',rm.start()); params=split(body[a+1:end(body,a)])
            relations.append({'method':m[1],'kind':rm[1],'arguments':params})
    models.append({'model':name,'table':t,'file':rel(p),'pk':pk[1] if pk else 'id','fillable':val(f[1]) if f else [],'casts':casts[1].strip() if casts else '','soft_delete':bool(re.search(r'use SoftDeletes',s)),'relations':relations})

# DDL only: discard COPY data and INSERT lines BEFORE extracting structures.
dump=ROOT/'bd-savp-tis3.sql'; sql=read(dump); ddl=[]; in_copy=False
for line in sql.splitlines():
    if re.match(r'^COPY\s',line): in_copy=True; continue
    if in_copy:
        if line=='\\.': in_copy=False
        continue
    if re.match(r'^INSERT\s',line,re.I): continue
    ddl.append(line)
sql='\n'.join(ddl); sqltables={}
for m in re.finditer(r'CREATE TABLE public\.(\w+)\s*\(',sql):
    a=sql.index('(',m.start()); body=sql[a+1:end(sql,a)]
    cols={}
    for c in split(body):
        cm=re.match(r'\s*(\w+)\s+(.+)',c,re.S)
        if cm and cm[1]!='CONSTRAINT': cols[cm[1]]={'definition':cm[2].strip()}
    sqltables[m[1]]={'columns':cols}
sql_fks=[]
for m in re.finditer(r'ALTER TABLE ONLY public\.(\w+)\s+ADD CONSTRAINT (\w+) FOREIGN KEY \(([^)]+)\) REFERENCES public\.(\w+)\(([^)]+)\)([^;]*);',sql):
    sql_fks.append({'table':m[1],'name':m[2],'columns':m[3],'target':m[4],'references':m[5],'actions':m[6].strip()})
sqlindexes=re.findall(r'CREATE (?:UNIQUE )?INDEX [^;]+;',sql)
sqlkeys=re.findall(r'ALTER TABLE ONLY public\.\w+\s+ADD CONSTRAINT \w+ (?:PRIMARY KEY|UNIQUE) \([^;]+;',sql)
sqlchecks=re.findall(r'CONSTRAINT\s+\w+\s+CHECK\s+\([^;]+?\)(?=,?\n)',sql)
drift=[]
for name in sorted(set(tables)|set(sqltables)):
    histcols={k for k,v in tables.get(name,{}).get('columns',{}).items() if v['phase']=='HISTORICA_DECLARADA'}
    dumpcols=set(sqltables.get(name,{}).get('columns',{}))
    typediff=[]; nulldiff=[]
    for c in sorted(histcols & dumpcols):
        definition=sqltables[name]['columns'][c]['definition']
        sqltype=re.split(r'\s+(?:DEFAULT|NOT NULL|NULL)\b',definition)[0].replace('timestamp(0)','timestamp')
        phptype=tables[name]['columns'][c]['type'].replace(' + CHECK','').replace(', ', ',')
        if sqltype!=phptype: typediff.append(f'{c}: DECLARED={phptype}; SQL={sqltype}')
        if ('NOT NULL' not in definition)!=tables[name]['columns'][c]['nullable']: nulldiff.append(c)
    drift.append({'TABLE':name,'IN_MIGRATIONS':name in tables and tables[name]['phase']=='HISTORICA_DECLARADA','IN_SQL_SNAPSHOT':name in sqltables,'DECLARED_ONLY_COLUMNS':'; '.join(sorted(histcols-dumpcols)),'SQL_ONLY_COLUMNS':'; '.join(sorted(dumpcols-histcols)),
       'TYPE_DIFFERENCES':'; '.join(typediff),'NULLABLE_DIFFERENCES':'; '.join(nulldiff),'INTERPRETATION':'Diferencia entre archivos; no prueba estado actual de PostgreSQL'})

# All repository source callers (including jobs/views/seeders/tests); model imports
# are candidate references, never sufficient evidence of runtime or column ownership.
paths=[]
for folder in ['app','routes','resources/views','database/seeders','database/factories','tests','config','bootstrap']:
    paths += [p for p in (ROOT/folder).rglob('*') if p.is_file() and p.suffix in ['.php','.json']]
sources={rel(p):stripped(read(p)) for p in paths}; refs=defaultdict(list)
for model in models:
    name=model['model']; t=model['table']
    for file,s in sources.items():
        if file==model['file']: continue
        if name not in s and t not in s: continue
        lines=[i+1 for i,l in enumerate(s.splitlines()) if re.search(r'\b'+re.escape(name)+r'\b',l)]
        literal=[i+1 for i,l in enumerate(s.splitlines()) if re.search(r"(?:table|join|from|leftJoin|rightJoin)\s*\(\s*['\"]"+re.escape(t)+r"['\"]",l)]
        if lines or literal: refs[t].append({'file':file,'model':name,'lines':sorted(set(lines+literal)),'certainty':'REFERENCIA_ESTATICA; verificar cadena por método'})
for t in tables:
    for file,s in sources.items():
        if t not in s: continue
        lines=[i+1 for i,l in enumerate(s.splitlines()) if re.search(r"(?:table|join|from|leftJoin|rightJoin)\s*\(\s*['\"]"+re.escape(t)+r"['\"]",l)]
        if lines and not any(r['file']==file for r in refs[t]): refs[t].append({'file':file,'model':'DB_LITERAL','lines':lines,'certainty':'REFERENCIA_LITERAL'})

baseline=[]
for folder in ['app','database','routes','config','tests','resources','bootstrap']:
    for p in (ROOT/folder).rglob('*'):
        if p.is_file(): baseline.append({'file':rel(p),'sha256':sha(p),'bytes':p.stat().st_size})
for name in ['.env','bd-savp-tis3.sql','composer.lock','package-lock.json','phpunit.xml','PETER2_REESTRUCTURACION.patch']:
    p=ROOT/name
    if p.exists(): baseline.append({'file':name,'sha256':sha(p),'bytes':p.stat().st_size})
baselinepath=OUT/'bd-preservacion-baseline.csv'
if not baselinepath.exists(): csvout(baselinepath.name,baseline)
else:
    old=list(csv.DictReader(baselinepath.open(encoding='utf-8-sig'))); current={r['file']:r for r in baseline}
    save('bd-preservacion-verificacion.json',{'checked':len(old),'changed':[r['file'] for r in old if r['file'] not in current or current[r['file']]['sha256']!=r['sha256']]})

save('bd-estructura-estatica.json',{'scope':'ARCHIVOS; no conexión ni ejecución de migrations','migrations':migrations,'historical_tables':historical,'tables':tables,'models':models,'references':refs,'unresolved':unresolved,'operations':operations,
    'sql_snapshot':{'file':'bd-savp-tis3.sql','sha256':sha(dump),'bytes':dump.stat().st_size,'tables':sqltables,'fks':sql_fks,'explicit_indexes':sqlindexes,'pk_unique':sqlkeys,'checks':sqlchecks}})
csvout('bd-schema-vs-sql.csv',drift)
fkrows=[{'TABLE':t['table'],**fk,'columns':'; '.join(fk['columns']),'references':'; '.join(fk['references'])} for t in tables.values() for fk in t['fks']]
csvout('bd-fks.csv',fkrows)
idxrows=[{'TABLE':t['table'],**idx,'columns':'; '.join(idx['columns'])} for t in tables.values() for idx in t['indexes']]
csvout('bd-indices-declarados.csv',idxrows)
csvout('bd-modelos.csv',[{**m,'fillable':'; '.join(m['fillable']),'relations':json.dumps(m['relations'],ensure_ascii=False)} for m in models])
save('bd-lectura-fuentes.json',[{'file':rel(p),'sha256':sha(p),'bytes':p.stat().st_size} for p in paths]+migrations)
print(json.dumps({'tables_historical':sum(t['phase']=='HISTORICA_DECLARADA' for t in tables.values()),'tables_proposed':sum(t['phase']=='PROPUESTA_NO_EJECUTADA' for t in tables.values()),'columns_historical':sum(c['phase']=='HISTORICA_DECLARADA' for t in tables.values() for c in t['columns'].values()),'columns_proposed':sum(c['phase']=='PROPUESTA_NO_EJECUTADA' for t in tables.values() for c in t['columns'].values()),'models':len(models),'migrations':len(migrations),'sql_snapshot_tables':len(sqltables),'fks':len(fkrows),'indexes':len(idxrows),'unresolved':unresolved},ensure_ascii=False,indent=2))
