"""Comprueba evidencia documental y preservación; ninguna conexión ni SQL."""
from pathlib import Path
from collections import Counter
import csv, json, hashlib, ast, re

ROOT=Path(__file__).resolve().parents[3]; OUT=Path(__file__).resolve().parent; DOC=OUT.parent
def rows(p): return list(csv.DictReader(p.open(encoding='utf-8-sig')))
def digest(p): return hashlib.sha256(p.read_bytes()).hexdigest()
checks=[]
def check(name, condition, detail=''):
    checks.append({'CHECK':name,'PASS':bool(condition),'DETAIL':detail})

a=json.loads((OUT/'bd-estructura-estatica.json').read_text(encoding='utf-8')); t=a['tables']; h=a['historical_tables']
tr=rows(DOC/'32-MATRIZ-TABLAS-BD.csv'); cr=rows(DOC/'33-MATRIZ-COLUMNAS-BD.csv'); wr=rows(OUT/'bd-105-ventanas.csv')
check('Inventory tables 64 historical + 14 proposed + 1 ledger',len(h)==64 and len(t)==78 and len(tr)==79)
check('Columns 597 historical + 140 overlay + 3 ledger',sum(len(x['columns']) for x in h.values())==597 and len(cr)==740)
check('Unique table/column pairs',len({(r['TABLE'],r['COLUMN']) for r in cr})==len(cr))
check('Table column counts',all(sum(c['TABLE']==r['TABLE'] for c in cr)==int(r['COLUMN_COUNT']) for r in tr))
check('All Models mapped',len(a['models'])==55 and all(m['table'] in t for m in a['models']))
check('All fillable columns declared',all(c in t[m['table']]['columns'] for m in a['models'] for c in m['fillable']))
check('All original migrations/proposals accounted',Counter(m['phase'] for m in a['migrations'])=={'HISTORICA_DECLARADA':52,'PROPUESTA_NO_EJECUTADA':6})
check('No unresolved Blueprint/DDL statements',not a['unresolved'])
missing=[]; types=[]; nonunique=[]
for name,tab in t.items():
    for fk in tab['fks']:
        parent=t.get(fk['target'])
        if not parent or any(c not in tab['columns'] for c in fk['columns']) or any(c not in parent['columns'] for c in fk['references']):
            missing.append((name,fk)); continue
        for c,rc in zip(fk['columns'],fk['references']):
            if tab['columns'][c]['type']!=parent['columns'][rc]['type']: types.append((name,c,fk['target'],rc))
        if not any(i['kind'] in ['PRIMARY','UNIQUE'] and set(i['columns'])==set(fk['references']) for i in parent['indexes']): nonunique.append((name,fk))
check('Declared FK endpoints exist',not missing,str(missing))
check('Declared FK types compatible',not types,str(types))
check('Declared referenced keys have PK/UNIQUE',not nonunique,str(nonunique))
check('FK historic/overlay counts',sum(len(x['fks']) for x in h.values())==96 and sum(len(x['fks']) for x in t.values())==128)
check('Historic index declarations',Counter(i['kind'] for x in h.values() for i in x['indexes'])=={'PRIMARY':64,'UNIQUE':30,'INDEX':151})
catalog=Path(r'C:\Users\LOQ\.codex\worktrees\61ea\savp-reestructuracion\docs\arquitectura-maestra-savp')
orig=rows(catalog/'matrices/05-ventanas.csv'); current=rows(DOC/'MATRIZ-CONCILIACION-105.csv')
o={r['id']:r for r in orig}; w={r['ID_ORIGINAL']:r for r in wr}; c={r['ID ORIGINAL']:r for r in current}
check('105 original IDs without changes',len(o)==105 and set(o)==set(w)==set(c))
check('105 original actors/window names preserved',all(w[id]['ACTOR']==o[id]['actor'] and w[id]['VENTANA_ORIGINAL']==o[id]['nombre'] for id in o))
check('Window statuses unchanged',all(w[id]['ESTADO_ACTUAL']==c[id]['ESTADO ACTUAL'] for id in o))
check('No false PASS added',Counter(r['ESTADO_ACTUAL'] for r in wr)=={'PARTIAL':91,'BLOCKED_EXTERNALLY_DB':10,'BLOCKED_EXTERNALLY_INSTITUTIONAL':4})
catalogrows=[{'file':p.relative_to(catalog).as_posix(),'sha256':digest(p)} for p in sorted(catalog.rglob('*')) if p.is_file()]
cb=OUT/'bd-arquitectura-original-baseline.csv'
if not cb.exists():
    with cb.open('w',encoding='utf-8-sig',newline='') as f:
        writer=csv.DictWriter(f,fieldnames=['file','sha256']); writer.writeheader(); writer.writerows(catalogrows)
else: check('Original architecture hashes preserved',rows(cb)==catalogrows)
check('Original architecture 43 files present',len(catalogrows)==43)
baseline=rows(OUT/'bd-preservacion-baseline.csv'); changed=[r['file'] for r in baseline if not (ROOT/r['file']).is_file() or digest(ROOT/r['file'])!=r['sha256']]
check('Source/config/env/assets/SQL/locks/seeders preserved',not changed,f'{len(baseline)} protected files; changed={changed}')
check('52 historic migrations preserved',all(not r['file'].startswith('database/migrations/') for r in baseline if r['file'] in changed))
check('Six proposals preserved',all(digest(ROOT/m['file'])==m['sha256'] for m in a['migrations'] if m['phase']=='PROPUESTA_NO_EJECUTADA'))
check('Required documents exist',all((DOC/name).is_file() for name in ['31-AUDITORIA-BD-MAESTRA.md','32-MATRIZ-TABLAS-BD.csv','33-MATRIZ-COLUMNAS-BD.csv','34-RELACIONES-BD.md','25-MIGRATIONS-PROPUESTAS.md','35-MODELO-DATOS-OBJETIVO.md','36-DEUDA-TECNICA-BD.md']))
check('Migration review retains original exact text', (OUT/'bd-25-migrations-antes-revision.md').read_text(encoding='utf-8-sig').rstrip() in (DOC/'25-MIGRATIONS-PROPUESTAS.md').read_text(encoding='utf-8').rstrip())
check('No COPY data in structured SQL evidence', 'COPY public.' not in json.dumps(a['sql_snapshot']) and '\\N\t' not in json.dumps(a['sql_snapshot']))
for p in OUT.glob('*bd*.py'): ast.parse(p.read_text(encoding='utf-8')); check('Python syntax '+p.name,True)
badlinks=[]
for name in ['31-AUDITORIA-BD-MAESTRA.md','34-RELACIONES-BD.md','35-MODELO-DATOS-OBJETIVO.md','36-DEUDA-TECNICA-BD.md']:
    for link in re.findall(r'\]\(([^)]+)\)',(DOC/name).read_text(encoding='utf-8')):
        if not link.startswith(('https://','http://')) and not (DOC/link).exists(): badlinks.append((name,link))
check('Local document links resolve',not badlinks,str(badlinks))
result={'scope':'STATIC DOCUMENTS ONLY; no Laravel bootstrap, SQL, DB or migrations execution','checks':checks,'pass':sum(c['PASS'] for c in checks),'fail':sum(not c['PASS'] for c in checks),'changed_source_files':changed,
        'migrations_executed':0,'seeders_executed':0,'db_access':False,'schema_modified':False}
(OUT/'bd-validacion.json').write_text(json.dumps(result,ensure_ascii=False,indent=2)+'\n',encoding='utf-8')
print(json.dumps({'pass':result['pass'],'fail':result['fail'],'failures':[c for c in checks if not c['PASS']]},ensure_ascii=False,indent=2))
raise SystemExit(bool(result['fail']))
