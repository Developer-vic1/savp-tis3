"""Genera matrices de evidencia estática con decisiones de diseño explícitas.
Solo escribe documentación en docs/implementacion-maestra; no ejecuta PHP/SQL.
"""
from pathlib import Path
import json, csv, re, hashlib
from collections import defaultdict, Counter

ROOT=Path(__file__).resolve().parents[3]; OUT=Path(__file__).resolve().parent; DOC=OUT.parent
A=json.loads((OUT/'bd-estructura-estatica.json').read_text(encoding='utf-8'))
T=A['tables']; H=A['historical_tables']; M=A['models']; REF=A['references']
def read(p): return p.read_text(encoding='utf-8-sig',errors='replace')
def join(v): return '; '.join(str(x) for x in sorted(set(v)))
def write(name, text): (DOC/name).write_text(text.rstrip()+'\n',encoding='utf-8')
def csvwrite(path,rows):
    with path.open('w',encoding='utf-8-sig',newline='') as f:
        w=csv.DictWriter(f,fieldnames=list(rows[0])); w.writeheader(); w.writerows(rows)

# Domain decisions are curated, not inferred from the absence of callers.
META={}
def group(names,domain,status,purpose,recommendation,owner):
    for name in names.split(): META[name]=(domain,status,purpose,recommendation,owner)
group('cache cache_locks','CORE','CONSERVAR','Cache técnico y coordinación de locks','Retención por TTL; no usar como fuente académica','Framework Cache')
group('jobs job_batches failed_jobs','CORE','CONSERVAR','Cola, lotes y fallos de ejecución','Retención técnica, payload sin secretos; no almacenar hechos oficiales aquí','Framework Queue')
group('sessions password_reset_tokens','SEGURIDAD','CONSERVAR','Sesión y recuperación de credenciales','Expiración y minimización; cuenta institucional con PK textual','Framework Session / PasswordBroker')
group('personal_access_tokens','SEGURIDAD','NORMALIZAR','Credenciales de acceso API','Corregir morph bigint frente a User.cod_usu varchar(20), tras perfilado; no cambiar PK User','Sanctum HasApiTokens')
group('persona','IDENTIDAD','CONSERVAR_Y_MEJORAR','Identidad personal independiente del vínculo institucional','Definir CI/complemento y correo normalizados; preservar identidad y datos históricos','Livewire Gestión Personas; PersonaInteligente prevención')
group('users','IDENTIDAD; SEGURIDAD','CONSERVAR_Y_MEJORAR','Cuenta, credenciales y proveedor de autenticación','Persona 1:0..1 User; separar contacto de login; gobernar fotografías/proveedores y estados','OperationalAccountService / Fortify / CRUD administrativo')
group('user_status_logs','AUDITORÍA','CONSERVAR_Y_MEJORAR','Historial de estados de cuenta','Conservar hechos; revisar CASCADE de usuario; no sustituye bitácora','Livewire/Admin/GestionUsuarios.php')
group('roles permissions model_has_roles model_has_permissions role_has_permissions','SEGURIDAD','CONSERVAR_Y_MEJORAR','RBAC canónico Spatie y sus pivots','RolePermissionService escritor operacional; un actor institucional y roles complementarios; sin Role paralelo','RolePermissionService; Spatie; seeders solo futuros autorizados')
group('personal_institucional','IDENTIDAD','NORMALIZAR','Vínculo laboral y cargo','Cargo no es Role; decidir cardinalidad temporal y UNIQUE condicionado; preservar vínculos','app/Livewire/Admin/PersonalInstitucional.php')
group('docente','ACADÉMICO','CONSERVAR_Y_MEJORAR','Capacidad docente, especialidad y asignaciones','Conservar entidad por atributos y FK de planes; decidir unicidad cod_pin y contador de modificaciones','app/Livewire/Admin/GestionDocente.php')
group('administrador director secretaria_general','IDENTIDAD; LEGACY','LEGACY_CONSERVADO','Perfil institucional anterior al RBAC canónico','Mantener por compatibilidad y datos; no duplicar autorización basada en Role; eventual reemplazo exige prueba de usos','CRUD/perfiles institucionales existentes')
group('regente','IDENTIDAD; ACADÉMICO','CONSERVAR_Y_MEJORAR','Perfil de Regencia ligado a personal','Preservar FK y metadatos; permisos se resuelven por Role y alcance por asignaciones','CRUD de personal / RegencyAccessService lectura')
group('regente_asignaciones','ACADÉMICO; SEGURIDAD','CONSERVAR_Y_MEJORAR','Alcance Regente por gestión y grado','Reutilizar única regente/gestión/curso; revocación activa y consulta correlacionada','Administración de alcances; RegencyAccessService lectura')
group('institucion_procedencia tipo_vinculacion_estudiante','CONFIGURACIÓN','CONSERVAR','Catálogos de procedencia y vínculo estudiantil','Mantener identidad y estados; no copiar a un catálogo genérico','CRUD y Supports comunitarios existentes')
group('estudiante','IDENTIDAD; ACADÉMICO','CONSERVAR_Y_MEJORAR','Vínculo estudiantil y código RUD','Separar identidad de inscripción anual; cod_esp es dato legacy frente a especialidad por inscripción','CRUD estudiantil y Gestión Inscripciones')
group('gestion_academica','ACADÉMICO; CALENDARIO','CONSERVAR_Y_MEJORAR','Año/gestión y límites institucionales de fecha','Conservar cada gestión, fechas y cierre; parametrizar hechos, no propuestas automáticas','Gestión académica / GestionAcademicaInteligente prevención')
group('curso','ACADÉMICO; CONFIGURACIÓN','CONSERVAR_Y_MEJORAR','Catálogo de grado/nivel','Curso no es grupo anual ni ClaseVirtual; no crear grado duplicado','app/Livewire/Admin/GestionCurso.php')
group('paralelo turno','ACADÉMICO; CONFIGURACIÓN','CONSERVAR_Y_MEJORAR','Catálogo de sección y jornada','Capacidad requiere contexto anual; no persistir cupos disponibles derivados; horas coherentes','CRUD existente; TurnoInteligente actualmente también escribe')
group('asignatura especialidad_tecnica','ACADÉMICO; CONFIGURACIÓN','CONSERVAR_Y_MEJORAR','Catálogo curricular y técnico','Asignatura distinta de asignación anual; capacidad de especialidad pendiente, no inventar cupo','CRUD y Supports actuales')
group('periodo_evaluacion','EVALUACIÓN; CONFIGURACIÓN','EXTENDER','Catálogo global de períodos y orden','Actual no tiene gestión ni fechas; definir edición anual antes de introducir calendario de períodos','CRUD Período; GradeService lectura')
group('plan_asignatura plan_especialidad','ACADÉMICO','CONSERVAR_Y_MEJORAR','Asignación docente anual a materia/especialidad y grupo','Son fuente del contexto gestión/grado/paralelo/turno/docente; decidir clave natural y reemplazos docentes','CRUD de planes; PlanAsignaturaInteligente prevención')
group('inscripcion_estudiante','ACADÉMICO','CONSERVAR_Y_MEJORAR','Inscripción por estudiante y gestión con estado y decisiones administrativas','Reutilizar UNIQUE estudiante/gestión; movimientos requieren historial, no segunda inscripción por defecto','Livewire/Admin/GestionInscripciones.php; InscripcionAcademica prevención')
group('plantilla_horaria horario horario_bloque horario_detalle','ACADÉMICO; CALENDARIO','CONSERVAR_Y_MEJORAR','Plantilla, cabecera anual, bloques y celdas horarias','No duplicar turno/gestión en detalle; XOR plan curricular/técnico; revisar coherencia de bloque y alcance','Gestión Horarios; Gestión Turnos / TurnoInteligente')
group('documento_inscripcion_estudiante','ACADÉMICO; AUDITORÍA','CONSERVAR_Y_MEJORAR','Requisito documental, recepción y archivo privado','Metadatos en BD, bytes en storage privado; comprobación humana no OCR inventado','Gestión Inscripciones y descarga autorizada')
group('respaldo_gestion_academica reportes_generados','AUDITORÍA','CONSERVAR_Y_MEJORAR','Artefacto exportado y trazabilidad de generación','Propósitos distintos; hash/fecha/actor persistidos; exportación SQL actual no es backup integral restaurable','Servicios Reportes y controllers existentes')
group('bitacora','AUDITORÍA','CONSERVAR_Y_MEJORAR','Registro de operaciones de dominio','BitacoraService único escritor objetivo; corregir writers legacy y proteger historial/PII','BitacoraService; writers directos legacy detectados')
group('clase_virtual','LMS','CONSERVAR_Y_MEJORAR','Aula virtual dependiente de una asignación académica','Conservar plan, publicación, estados y ownership; no duplicar Curso','CursoVirtualService')
group('clase_estudiante','LMS','CONSERVAR_Y_MEJORAR','Membresía, acceso y contadores del aula','Membresía autorizada por inscripción; contador de acceso cache verificable, no rendimiento académico','CursoVirtualService / ClaseEstudiante.registrarAcceso')
group('publicacion_clase','LMS','CONSERVAR_Y_MEJORAR','Anuncio/publicación con autor y fecha','Reutilizar; no sustituye unidad; preservar contenido y visibilidad','PublicacionService')
group('material_clase tarea_material','LMS; CANDIDATO_FUSIÓN','CONSERVAR_Y_MEJORAR','Recurso de aula o adjunto de una tarea','Scopes distintos; compartir validación/storage antes de plantear biblioteca de activos; no fusionar ahora','MaterialService / TareaService')
group('tarea','LMS; EVALUACIÓN','CONSERVAR_Y_MEJORAR','Actividad evaluable con plazo y máximo','Reusar para prácticas/proyectos; no crear actividad_evaluable duplicada','TareaService')
group('entrega_tarea entrega_archivo','LMS','CONSERVAR_Y_MEJORAR','Entrega individual y archivos adjuntos','Una entrega lógica por tarea/estudiante; preservar revisión/reenviados y archivos; no limpieza destructiva','EntregaService')
group('calificacion_tarea','EVALUACIÓN','CONSERVAR_Y_MEJORAR','Calificación LMS y máximo al momento de evaluar','pun_max es snapshot significativo; validar contexto entrega/tarea/estudiante; nunca convertir automáticamente en nota oficial','EntregaService.calificar')
group('calificacion','EVALUACIÓN','CONSERVAR_Y_MEJORAR','Nota oficial por estudiante/asignación/período','Preservar cod_pas nulo histórico; cod_asi redundante contextual se conserva hasta conciliación; quitar cascada requiere revisión','GradeService y CRUD legacy administrativo')
group('estado_asistencia','ASISTENCIA; CONFIGURACIÓN','CONSERVAR_Y_MEJORAR','Estado y ponderación institucional de asistencia','valor_porcentual configura política, MUST_PERSIST; porcentaje agregado del estudiante se deriva','Gestión estados de asistencia / AsistenciaService lectura')
group('asistencia_clase asistencia_estudiante','ASISTENCIA','CONSERVAR_Y_MEJORAR','Sesión y hechos individuales de presencia/atraso','Preservar rectificaciones; UNIQUE nullable de sesión exige revisión; sumarizar con alcance y denominador explícito','AsistenciaService')
group('actividad_clase','LMS; ANALÍTICA','CONSERVAR_Y_MEJORAR','Telemetría/eventos de actividad, no tarea evaluable','Conservar; evaluar cobertura real de productores, minimización y retención; no reemplazar Bitácora','Relaciones de modelos y lectores de actividad; productor actual no generalizado')
group('orientacion_actividades orientacion_preguntas orientacion_respuestas orientacion_resultados','ORIENTACIÓN','EXTENDER','Evaluación local Likert, preguntas, respuestas y resultado orientativo','Versionar instrumento/preguntas/algoritmo; avance derivable; preservar intentos finalizados, sin equiparar a RIASEC','OrientacionService')
group('orientacion_carreras_sugeridas','ORIENTACIÓN; LEGACY','LEGACY_CONSERVADO','Recomendaciones conservadas de resultado anterior','Lectura actual existe; no se generan porcentajes nuevos sin contrato científico; preservar registros','OrientacionService lectura; productor especializado pendiente')
group('role_requests','SEGURIDAD; AUDITORÍA','CONSERVAR_Y_MEJORAR','Solicitud, análisis preventivo y aprobación humana de Role','Snapshot preventivo justificado como evidencia de decisión; no permiso automático ni autenticidad documental','RoleRequestService; RolePermissionService crea Role')
group('kardex_tipos kardex_categorias kardex_niveles kardex_estados kardex_medidas','KARDEX; CONFIGURACIÓN; NUEVO_REQUERIDO','NUEVO','Catálogo versionado de un eje diferente de Kardex','Cinco FK tipadas frente a catálogo genérico; publicar una edición coherente, validar relaciones y políticas sin inventar valores','Futuro KardexService de administración; no escritor actual')
group('seguimiento_academico','SEGUIMIENTO; KARDEX; NUEVO_REQUERIDO','NUEVO','Hecho formativo, responsable, contexto y acuerdos','Extender concepto selectivo; registrar event_at separado de captura; plan puede no corresponder a incidente general; contexto institucional debe decidirse','ScopedKardexRepository lectura futura; KardexService escritura pendiente')
group('seguimiento_revisiones seguimiento_evidencias','KARDEX; AUDITORÍA; NUEVO_REQUERIDO','NUEVO','Revisión append-only y evidencia privada ligada al seguimiento','Historia de dominio distinta de bitácora operativa; no mutar ni borrar; persistir autor/fecha/hash','Futuro KardexService; escritura aún pendiente')
group('unidades_clase','LMS; NUEVO_REQUERIDO','NUEVO','Unidad/sección organizativa del contenido de una clase','MIG-002 admisible; no afirma ser unidad curricular nacional; enlaces históricos nullable','UnitContentService lectura; edición futura')
group('notifications','NOTIFICACIÓN; NUEVO_REQUERIDO','NUEVO','Aviso Laravel con destinatario y lectura','MIG-003 revisar discriminador de User/retención; usar Notifiable, no segunda infraestructura','NotificationService.mark; productores pendientes')
group('metas_academicas meta_academica_revisiones','ORIENTACIÓN; AUDITORÍA; NUEVO_REQUERIDO','NUEVO','Meta personal, acción y revisión histórica','MIG-004 admisible; acción textual inicial es suficiente; progreso derivado salvo evaluación histórica explícita','AcademicGoalService')
group('calendario_evento calendario_evento_revisiones','CALENDARIO; AUDITORÍA; NUEVO_REQUERIDO','NUEVO','Hecho institucional y corrección con alcance temporal','MIG-005 revisar tipo/efecto, días parciales, hora/zona y solapamientos; plazos de tarea continúan derivados','CalendarService lectura; escritor institucional pendiente')
assert set(META)==set(T), (set(T)-set(META),set(META)-set(T))

windows=list(csv.DictReader((DOC/'MATRIZ-CONCILIACION-105.csv').open(encoding='utf-8-sig')))
assert len(windows)==105 and len({w['ID ORIGINAL'] for w in windows})==105
support_rows=list(csv.DictReader((OUT/'support-inventario.csv').open(encoding='utf-8-sig')))
sources={p.relative_to(ROOT).as_posix():read(p) for p in (ROOT/'app').rglob('*.php')}
classfiles={}
for file,s in sources.items():
    ns=re.search(r'namespace\s+([^;]+);',s); cl=re.search(r'(?:class|trait|interface|enum)\s+(\w+)',s)
    if ns and cl: classfiles[ns[1]+'\\'+cl[1]]=file
graph={}
for file,s in sources.items():
    refs=set()
    for full in re.findall(r'use\s+(App\\[^;\s]+)(?:\s+as\s+\w+)?\s*;',s):
        if full in classfiles: refs.add(classfiles[full])
    graph[file]=refs
def reachable(roots):
    seen=set(); todo=list(roots)
    while todo:
        f=todo.pop()
        if f in seen: continue
        seen.add(f)
        if not f.startswith('app/Models/'): todo.extend(graph.get(f,[]))
    return seen
windowfiles={}; tablewindows=defaultdict(set)
for w in windows:
    rootset=set()
    for key in ['ARCHIVO ACTUAL','LIVEWIRE/CONTROLLER','SERVICE','SUPPORT_ASOCIADO']:
        for f in re.findall(r'app[/\\][A-Za-z0-9_./\\-]+\.php',w.get(key,'')): rootset.add(f.replace('\\','/'))
    files=reachable(rootset); windowfiles[w['ID ORIGINAL']]=files
    for model in M:
        if model['file'] in files: tablewindows[model['table']].add(w['ID ORIGINAL'])
    for t,refs in REF.items():
        if any(r['file'] in files for r in refs): tablewindows[t].add(w['ID ORIGINAL'])
manual_windows={
 'kardex':set('V022 V035 V036 V038 V055 V063 V064 V066 V082 V083 V098'.split()),
 'units':set('V073 V074 V075 V076 V090 V091 V092'.split()),
 'notifications':set('V001 V027 V044 V058 V070 V087 V074 V075 V076 V077 V078 V079 V091 V092 V093 V094 V095'.split()),
 'goals':{'V102'},'calendar':set('V016 V042 V054 V067 V081 V096'.split())}
for name in T:
    if name.startswith(('kardex_','seguimiento_')): tablewindows[name]|=manual_windows['kardex']
    if name=='unidades_clase': tablewindows[name]|=manual_windows['units']
    if name=='notifications': tablewindows[name]|=manual_windows['notifications']
    if name.startswith(('meta_academica','metas_academicas')): tablewindows[name]|=manual_windows['goals']
    if name.startswith('calendario_evento'): tablewindows[name]|=manual_windows['calendar']

# File:line evidence is potential ownership if inferred through variable aliases.
# Exact DB literal or direct Model::write is stronger; never claim execution.
writer_re=r'(?:->|::)(?:create|insert|update|save|delete|forceDelete|updateOrCreate|firstOrCreate|firstOrNew|sync|attach|detach|syncRoles|syncPermissions|increment|decrement)\s*\('
def callers(name):
    readers=[]; writers=[]; columns=defaultdict(list)
    for r in REF.get(name,[]):
        file=r['file']; s=sources.get(file,read(ROOT/file) if (ROOT/file).exists() else '')
        ref=f"{file}:{','.join(map(str,r['lines']))} [REF_ESTATICA]"; readers.append(ref)
        if re.search(writer_re,s): writers.append(ref+' [CANDIDATO; verificar método/alias]')
        for i,line in enumerate(s.splitlines(),1):
            for c in T[name]['columns']:
                if re.search(r'\b'+re.escape(c)+r'\b',line) and not line.lstrip().startswith('use '): columns[c].append(f'{file}:{i}')
    return readers,writers,columns

derived={
 ('docente','num_mod_doc'):('DERIVABLE_AGGREGATE','Conteo de cambios; exactitud solo si historial de cambios completo'),
 ('orientacion_actividades','avance'):('DERIVABLE_REALTIME','Respuestas válidas / preguntas de la edición, no catálogo mutable actual'),
 ('inscripcion_estudiante','doc_com_ins'):('DERIVABLE_AGGREGATE','Completitud documental; confirmar si contiene decisión humana adicional antes de retirar'),
 ('clase_estudiante','cant_acc_cla_est'):('DERIVABLE_AGGREGATE','Contador operativo; solo reconstruible con ledger de accesos completo'),
 ('clase_estudiante','ult_acc_cla_est'):('DERIVABLE_AGGREGATE','Último acceso; ledger puede no conservar todos los eventos'),
 ('clase_estudiante','ult_act_cla_est'):('DERIVABLE_AGGREGATE','Última actividad; cobertura de telemetría pendiente'),
 ('job_batches','total_jobs'):('DERIVABLE_MATERIALIZED','Contador técnico framework; conservar contrato de cola'),
 ('job_batches','pending_jobs'):('DERIVABLE_MATERIALIZED','Contador técnico framework; conservar contrato de cola'),
 ('job_batches','failed_jobs'):('DERIVABLE_MATERIALIZED','Contador técnico framework; conservar contrato de cola')}
redundant={('calificacion','cod_asi'):'Derivable por cod_pas solo cuando cod_pas existe; imprescindible para históricos nulos',
 ('calificacion_tarea','cod_tar'):'Derivable por cod_ent; validar coincidencia antes de refactor',
 ('calificacion_tarea','cod_est'):'Derivable por cod_ent; validar coincidencia antes de refactor',
 ('orientacion_respuestas','cod_est'):'Derivable por actividad; asegurar pertenencia y no confiar en cliente',
 ('orientacion_resultados','cod_est'):'Derivable por actividad; mantener por búsquedas y compatibilidad hasta conciliar',
 ('estudiante','cod_esp'):'Especialidad global puede diferir de inscripción por gestión; no reemplazar automáticamente',
 ('users','profile_photo_path'):'Coexiste con persona.fot_per y users.avatar; origen/proveedor distintos, definir precedencia',
 ('users','avatar'):'Avatar remoto Google frente a foto local; no equivalente por defecto',
 ('inscripcion_estudiante','pro_ins'):'Texto procedencia frente a institución del estudiante; puede ser snapshot histórico'}
semantics={('persona','ci_per'):'Número de CI; UNIQUE actual no incluye complemento',('persona','com_per'):'Complemento CI; política de identidad y normalización pendiente',
 ('users','email'):'Correo de autenticación; persona.ema_per es contacto y no necesariamente igual',('users','current_team_id'):'Remanente Jetstream; equipos desactivados, tabla Team no declarada',
 ('estado_asistencia','valor_porcentual'):'Ponderación institucional configurada del estado; no porcentaje calculado de asistencia',('calificacion','not_cal'):'Nota oficial; rango aplicativo 0..100, histórico real no perfilado',
 ('calificacion_tarea','pun_max'):'Máximo usado al calificar: snapshot que preserva escala histórica',('role_requests','analysis_result'):'Evidencia preventiva en proceso de aprobación; no concede permisos',
 ('role_requests','document_analysis'):'Análisis local/manual provisional; no certifica autenticidad',('seguimiento_academico','fec_ape_seg'):'Fecha de apertura; falta diferenciar hora del hecho y registro',
 ('seguimiento_academico','version_catalogo'):'Versión común propuesta; requiere edición coordinada de los cinco ejes',('inscripcion_estudiante','sob_aut_ins'):'Autorización humana excepcional de sobrecupo',
 ('inscripcion_estudiante','sie_ins'):'Hecho de reporte al SIE; debe persistir fecha y trazabilidad',('inscripcion_estudiante','doc_com_ins'):'Bandera documental actual; puede desincronizarse de los requisitos',
 ('orientacion_resultados','compatibilidad_principal'):'Resultado local de dimensiones, no compatibilidad científica con carrera',('personal_access_tokens','tokenable_id'):'Morph bigint actual incompatible con PK textual del User institucional'}
common={'created_at':'Momento de creación del registro','updated_at':'Momento de última actualización; no historial de revisiones','id':'Identificador interno',
 'estado':'Estado de workflow; no es porcentaje','datos':'Snapshot JSONB de cambio con motivo/autor; no reemplaza relaciones',
 'created_by':'Autor obtenido del servidor','cod_est':'FK del vínculo estudiantil','cod_per':'FK de identidad personal','cod_gea':'Contexto de gestión académica',
 'cod_cur':'Grado/nivel catalogado','cod_par':'Sección/paralelo','cod_tur':'Jornada','cod_pas':'Asignación académica contextual','cod_doc':'Entidad docente',
 'cod_usu':'Cuenta institucional','codigo':'Código de catálogo/registro','version':'Edición de catálogo; preservar interpretaciones anteriores'}
modelmap=defaultdict(list)
for m in M: modelmap[m['table']].append(m)
table_rows=[]; column_rows=[]; service_rows=[]; supportmatrix=[]
for name,t in sorted(T.items()):
    domain,status,purpose,recommendation,owner=META[name]; readers,writers,colrefs=callers(name)
    if name in ['roles','permissions','model_has_roles','model_has_permissions','role_has_permissions']:
        writers.append('RolePermissionService / Spatie [escritor operacional canónico; catálogo permisos por seeders autorizados futuros]')
        readers.append('User.HasRoles / Spatie / RolePermissionService [framework y consultas de Permission indirectos]')
    if name=='notifications': readers.append('User.Notifiable / Illuminate.Notifications.DatabaseNotification [framework indirecto]'); writers.append('NotificationService.mark [read_at]; DatabaseChannel futuro [data]')
    if name in ['cache','cache_locks','jobs','job_batches','failed_jobs','sessions','password_reset_tokens','personal_access_tokens']: readers.append(owner+' [framework indirecto]'); writers.append(owner+' [framework indirecto]')
    history='Historial oficial; preservar contexto/rectificación' if any(x in domain for x in ['EVALUACIÓN','ASISTENCIA','AUDITORÍA','KARDEX','ORIENTACIÓN']) else 'Hechos y relaciones vigentes; conservar estados y referencias'
    deletion='HARD_DELETE_ALLOWED (TTL/retención)' if domain=='CORE' or name in ['sessions','password_reset_tokens','personal_access_tokens'] else 'IMMUTABLE_HISTORY' if name.endswith(('_revisiones','_evidencias')) or name in ['bitacora','user_status_logs'] else 'STATUS_BASED'
    table_rows.append({'TABLE':name,'MODEL':join(m['model'] for m in modelmap[name]) or ('DatabaseNotification (vendor)' if name=='notifications' else 'SIN MODEL APP; no implica desuso'),
       'DOMAIN':domain,'PURPOSE':purpose,'READERS':join(readers) or 'No caller literal localizado; revisar framework/dinámicos',
       'WRITERS':join(writers) or 'No escritor concreto localizado; no prueba tabla vacía/inútil','PK':join(','.join(i['columns']) for i in t['indexes'] if i['kind']=='PRIMARY'),
       'FK_COUNT':len(t['fks']),'INDEXES':join(i['kind']+':'+','.join(i['columns']) for i in t['indexes']),
       'HISTORY':history,'DERIVABLE_DATA':join(c for (tab,c) in derived if tab==name) or 'Sin cálculo materializado identificado',
       'DUPLICATION':join(c+': '+d for (tab,c),d in redundant.items() if tab==name) or 'No duplicidad confirmada; ver decisiones de dominio',
       'STATUS':status,'RECOMMENDATION':recommendation,'RISK':'No ejecutar; revisar datos y reglas antes de restricción' if t['phase']=='PROPUESTA_NO_EJECUTADA' else 'Ver hallazgos DB-001..DB-020 y restricciones de eliminación',
       'ORIGIN':t['phase'],'MIGRATIONS':join(t['sources']),'COLUMN_COUNT':len(t['columns']),'NULLABLE':join(c for c,v in t['columns'].items() if v['nullable']),
       'DEFAULTS':join(c+'='+str(v['default']) for c,v in t['columns'].items() if v['default']!=''),'CHECKS':join(c['name']+': '+c['expression'] for c in t['checks']),
       'SOFT_DELETE':'NO; no SoftDeletes declarado','DELETE_STRATEGY':deletion,'OWNER_TARGET':owner,'WINDOWS':join(tablewindows[name]),
       'CERTAINTY':'Declaración estática; callers candidatos, no runtime; ventanas por dependencia/transversal'})
    for file in sorted({r['file'] for r in REF.get(name,[]) if r['file'].startswith('app/Services/')}):
        service_rows.append({'SERVICE':file,'TABLE':name,'ROLE':'CANDIDATO_WRITER' if re.search(writer_re,sources[file]) else 'READ_ONLY_REFERENCES',
          'OWNER_TARGET':owner,'WINDOWS':join(tablewindows[name]),'CERTAINTY':'Archivo/método necesita verificar alias; referencias en bd-estructura-estatica.json'})
    for c,v in t['columns'].items():
        dtype,reason=derived.get((name,c),('MUST_PERSIST','Hecho, relación, configuración o evidencia de estado; no derivable de otra fuente estable'))
        sem=semantics.get((name,c),common.get(c,''))
        if not sem:
            for m in modelmap[name]:
                sm=re.search(r"['\"]"+re.escape(c)+r"['\"],?\s*//\s*([^\r\n]+)",read(ROOT/m['file']))
                if sm: sem=sm[1].strip(); break
        if not sem:
            sem=('Estado institucional' if c.startswith('est_') else 'Nombre' if c.startswith('nom_') else 'Descripción/observación' if c.startswith(('des_','obs_')) else 'Fecha del hecho' if c.startswith(('fec_','fei_','fii_','ffi_')) else 'Identificador/referencia' if c.startswith('cod_') else 'Atributo declarado; semántica adicional requiere revisar consumidor')+f' de {purpose.lower()} ({c})'
        privacy='SENSITIVE' if any(k in c for k in ['password','token','secret','recovery','likert','mot_seg','obs_','res_seg','pro_acc','datos','payload','exception','analysi','val_ant','val_nue','ip_','age_']) or c in ['not_cal','pun_obt'] or domain.startswith('ORIENTACIÓN') else 'PERSONAL' if name in ['persona','users','estudiante'] or c in ['cod_est','cod_per','cod_usu','created_by','generado_por'] else 'INSTITUTIONAL'
        fks=[fk for fk in t['fks'] if c in fk['columns']]; indexes=[i for i in t['indexes'] if c in i['columns']]
        ck=[k for k in t['checks'] if re.search(r'\b'+re.escape(c)+r'\b',k['expression'])]
        needed='Revisar estados/fechas/valores tras perfilado' if c.startswith(('est_','fec_','fii_','ffi_','pun_')) or c in ['estado','avance','valor_likert','not_cal','orden','version'] else 'No CHECK nuevo sin regla de dominio'
        column_rows.append({'TABLE':name,'COLUMN':c,'TYPE':v['type'],'SEMANTICS':sem,
          'WRITER':join(r for r in colrefs[c] if any(w.startswith(r.rsplit(':',1)[0]+':') for w in writers))+' [candidatos estáticos; ver método/alias]' if colrefs[c] and writers else 'Sin writer por columna localizado; revisar framework y aliases',
          'WRITER_TARGET':owner,'READER':join(colrefs[c]) or 'Relación/framework/lector indirecto; no referencia de columna literal en callers',
          'SOURCE_OF_TRUTH':f'{name}.{c}' if dtype=='MUST_PERSIST' else reason,'DERIVABLE':dtype,'REDUNDANT':redundant.get((name,c),'NO CONFIRMADO'),
          'HISTORICAL':history,'SENSITIVE':privacy,'INDEXABLE':join(i['kind']+':'+','.join(i['columns']) for i in indexes) or 'No índice declarado; justificar por consulta, no agregar automáticamente',
          'FK':join(','.join(fk['columns'])+' -> '+fk['target']+'('+','.join(fk['references'])+') DELETE '+fk['delete']+' UPDATE '+fk['update'] for fk in fks) or 'NO FK DECLARADA',
          'CHECK_NEEDED':needed,'RECOMMENDED_STATUS':'DERIVAR; conservar hasta conciliación y reemplazo de callers' if dtype!='MUST_PERSIST' and name!='job_batches' else 'CANDIDATO_RETIRO (columna; no retirar)' if (name,c)==('users','current_team_id') else status,
          'NULLABLE':v['nullable'],'DEFAULT':v['default'],'EXISTING_CHECKS':join(k['name']+': '+k['expression'] for k in ck),'ORIGIN':v['phase'],
          'MIGRATION':v['source'],'AUTO_INCREMENT':v['auto_increment'],'CASTS_MODEL':join(m['casts'] for m in modelmap[name]),'FILLABLE':join(m['model'] for m in modelmap[name] if c in m['fillable']),
          'CERTAINTY':'Tipo/declaración estáticos; semántica comentada o inferida; escritor objetivo no sustituye trazabilidad actual'})

# Include the migration ledger seen only in the SQL snapshot; no fabricated migration.
table_rows.append({'TABLE':'migrations','MODEL':'Framework MigrationRepository','DOMAIN':'CORE','PURPOSE':'Ledger de aplicación de migrations','READERS':'Framework Migrator','WRITERS':'Framework Migrator (no ejecutado)',
 'PK':'id (DDL snapshot)','FK_COUNT':0,'INDEXES':'PK en snapshot; no índice adicional inferido','HISTORY':'Ledger técnico','DERIVABLE_DATA':'NO','DUPLICATION':'NO','STATUS':'CONSERVAR',
 'RECOMMENDATION':'Conciliar ledger en BD aislada futura; nunca copiarlo como prueba de aplicación actual','RISK':'El SQL es snapshot histórico, no deployment actual','ORIGIN':'SQL_SNAPSHOT_ONLY',
 'MIGRATIONS':'Framework, sin Schema::create en archivos locales','COLUMN_COUNT':len(A['sql_snapshot']['tables']['migrations']['columns']),'NULLABLE':'Ver DDL snapshot','DEFAULTS':'Ver DDL snapshot','CHECKS':'Ver DDL snapshot',
 'SOFT_DELETE':'NO','DELETE_STRATEGY':'STATUS_BASED; preservar ledger','OWNER_TARGET':'Framework Migrator','WINDOWS':'Infraestructura transversal; sin ventana administrativa de escritura','CERTAINTY':'DDL snapshot, no estructura institucional confirmada'})
for c,v in A['sql_snapshot']['tables']['migrations']['columns'].items():
    row={k:'' for k in column_rows[0]}; row.update(TABLE='migrations',COLUMN=c,TYPE=v['definition'],SEMANTICS='Ledger técnico de migración aplicada',WRITER='Framework Migrator (no ejecutado)',READER='Framework Migrator',SOURCE_OF_TRUTH='Ledger de la conexión concreta',DERIVABLE='MUST_PERSIST',REDUNDANT='NO',HISTORICAL='Histórico técnico',SENSITIVE='INSTITUTIONAL',INDEXABLE='DDL snapshot',FK='NO',CHECK_NEEDED='NO',RECOMMENDED_STATUS='CONSERVAR',ORIGIN='SQL_SNAPSHOT_ONLY',MIGRATION='bd-savp-tis3.sql DDL',CERTAINTY='Snapshot, no runtime'); column_rows.append(row)
csvwrite(DOC/'32-MATRIZ-TABLAS-BD.csv',table_rows); csvwrite(DOC/'33-MATRIZ-COLUMNAS-BD.csv',column_rows); csvwrite(OUT/'bd-services-tablas.csv',service_rows)

# Direct query sites per Support; exclude Carbon/Collection create from DB counts.
query_sites=[]; unknown=[]
for sr in support_rows:
    file=sr['RUTA']; s=read(ROOT/file); tables=set(); columns=set(); sites=[]
    for i,line in enumerate(s.splitlines(),1):
        if re.search(r'DB::table\s*\(',line) or any(re.search(r'\b'+m['model']+r'::(?:query|where|find|with|all|count|first)',line) for m in M):
            sites.append(f'{file}:{i}')
            for tm in re.finditer(r"DB::table\s*\(\s*['\"]([^'\"]+)['\"]",line):
                tables.add(tm[1])
                if tm[1] not in T: unknown.append({'FILE':file,'LINE':i,'REFERENCE':tm[1],'STATUS':'REFERENCIA_NO_DECLARADA; revisar fallback, no crear tabla'})
            for m in M:
                if re.search(r'\b'+m['model']+r'::',line): tables.add(m['table'])
    indirect={'AsignaturaInteligente':['asignatura'],'CursoInteligente':['curso'],'ParaleloInteligente':['paralelo'],'DocenteInteligente':['docente'],
      'KardexInteligente':['seguimiento_academico'],'InstitutionalRoleGovernance':['roles','permissions','role_requests'],
      'PermissionLabel':['permissions'],'WorkspaceNavigation':['roles','permissions'],'ReporteAcademicoInteligente':['calificacion','inscripcion_estudiante','plan_asignatura'],
      'ReporteAdministrativoInteligente':['persona','users','inscripcion_estudiante']}
    for t in tables & set(T):
        columns.update(t+'.'+c for c in T[t]['columns'] if re.search(r'\b'+re.escape(c)+r'\b',s))
    haswrites=bool(re.search(writer_re,s)) if 'DB::table' in s else False
    supportmatrix.append({'SUPPORT':sr['SUPPORT'],'FILE':file,'QUERY_BD_DIRECT':bool(sites),'QUERY_SITES_COUNT':len(sites),'QUERY_SITES':join(sites),
      'TABLES':join(tables),'TABLES_FROM_CALLER_OR_INPUT':join(indirect.get(sr['SUPPORT'],[])), 'COLUMNS_REFERENCED':join(columns),'WINDOWS':sr['VENTANAS'],'CURRENT_WRITER':'SÍ: TurnoInteligente escribe plantilla/bloque/bitácora' if sr['SUPPORT']=='TurnoInteligente' else 'Sin writer DB directo identificado; llamadas de helper no prueban escritura',
      'TARGET':'Prevención local; escritor con autorización en Service','INDEX_NEEDS':'Ver Q-001..Q-008 e IDX-001..IDX-010; comprobar planes futuros','REGRESSION':'0 archivos Support cambiados; tests no repetidos en fase documental'})
    query_sites.extend(sites)
csvwrite(OUT/'bd-support-tablas-columnas.csv',supportmatrix)
if unknown: csvwrite(OUT/'bd-referencias-no-declaradas.csv',unknown)

window_rows=[]
for w in windows:
    id=w['ID ORIGINAL']; tabs=sorted(t for t in T if id in tablewindows[t]); proposed=[t for t in tabs if T[t]['phase']=='PROPUESTA_NO_EJECUTADA']
    deps=[]
    for k,ids in manual_windows.items():
        if id in ids: deps.append({'kardex':'MIG-001 revisada','units':'MIG-002','notifications':'MIG-003 revisada (shell)','goals':'MIG-004','calendar':'MIG-005 revisada (solo eventos)'}[k])
    window_rows.append({'ID_ORIGINAL':id,'ACTOR':w['ACTOR'],'VENTANA_ORIGINAL':w['VENTANA ORIGINAL'],'ESTADO_ACTUAL':w['ESTADO ACTUAL'],
      'TABLAS_POR_DEPENDENCIA':join(tabs) or 'No dependencia inferida; revisar feature institucional',
      'NUEVO_SCHEMA_PARCIAL':join(proposed),'MIGRATIONS':join(deps) or w['MIGRATIONS'],
      'BLOQUEO_DB_ACTUAL':w['ESTADO ACTUAL']=='BLOCKED_EXTERNALLY_DB','EFECTO_FUTURO':'Retira precondición de schema solo después de aprobación/aplicación aislada; reglas, escritores, RBAC y QA pendientes' if proposed else 'Reutiliza schema declarado; pruebas de persistencia siguen pendientes',
      'EVIDENCE':'Referencias/imports transitivos + enlace conceptual documentado; no certifica consulta de todas las tablas en cada acción'})
csvwrite(OUT/'bd-105-ventanas.csv',window_rows)

relationships=[]
for name,t in T.items():
    for fk in t['fks']:
        cols=fk['columns']; uniq=any(i['kind'] in ['PRIMARY','UNIQUE'] and set(i['columns'])==set(cols) for i in t['indexes'])
        nullable=any(t['columns'].get(c,{}).get('nullable') for c in cols)
        relationships.append({'PARENT':fk['target'],'CHILD':name,'CHILD_COLUMNS':','.join(cols),'PARENT_COLUMNS':','.join(fk['references']),
          'CARDINALITY_PARENT_TO_CHILD':'1:0..1 (UNIQUE FK)' if uniq else '1:0..N','OPTIONAL_CHILD_PARENT':nullable,
          'ON_DELETE':fk['delete'],'ON_UPDATE':fk['update'],'SOURCE':fk['source'],'ORIGIN':fk['phase'],'OWNERSHIP':META[name][4],
          'HISTORY':next(r['HISTORY'] for r in table_rows if r['TABLE']==name),'RISK':'CASCADE puede perder histórico; revisar' if fk['delete']=='CASCADE' else 'SET NULL pierde enlace histórico' if fk['delete']=='SET NULL' else 'Restricción declarada; coherencia de contexto requiere Service'})
csvwrite(OUT/'bd-relaciones-cardinalidad.csv',relationships)

decisions=[
 ('MIG-001','APPROVE_WITH_CHANGES','Añadir momento del hecho y registro; decidir plan obligatorio para incidentes generales; edición publicada de cinco catálogos y coherencia tipo/categoría/medida; autor/responsable y revisiones inmutables. Cinco tablas tipadas son justificables, pero no se aprueban sus valores.', 'Kardex/Seguimiento',manual_windows['kardex']),
 ('MIG-002','APPROVE_AS_IS','Estructura organizativa y FK compuesta evitan unidad ajena. Nullable conserva recursos. UNIQUE(id,cod_cla) respalda FK, no es índice duplicado eliminable. Falta implementación de edición y prueba concurrente, no otra tabla.', 'LMS',manual_windows['units']),
 ('MIG-003','APPROVE_WITH_CHANGES','Infraestructura Notifiable correcta y PK textual. Fijar contrato del discriminador de User/morph map, política de retención y productores idempotentes; no admitir tipos polimórficos que no cumplen FK a users.', 'Notificaciones',manual_windows['notifications']),
 ('MIG-004','APPROVE_AS_IS','Meta/acción personal no son PlanAsignatura ni Tarea. Revisiones y estado satisfacen mínimo; sin contador de progreso. API de histórico y retención requieren revisión funcional.', 'Metas personales',manual_windows['goals']),
 ('MIG-005','APPROVE_WITH_CHANGES','Evento institucional distinto de plazo de tarea/gestión/horario. Definir tipos/efectos y duración parcial o multidiaria/hora/zona; no ejecutar suspensiones de clases por simple evento. Revisión y scope temporal siguen pendientes.', 'Calendario',manual_windows['calendar']),
 ('MIG-006','SPLIT','Separar integridad estructural/retención de checks de escala; NOT VALID también restringe filas actualizadas. No imponer 0..100/1..1000 al histórico sin perfilado ni equivalencia. Reversión actual restaura CASCADE: no rollback de producción seguro.', 'Integridad',set('V021 V032 V033 V037 V061 V065 V078 V079 V080 V094 V095 V097 V099 V100'.split()))]
csvwrite(OUT/'bd-decisiones-migrations.csv',[{'ID':id,'DECISION':d,'MODULE':mod,'WINDOWS':join(w),'REASON':reason,'EXECUTED':'NO'} for id,d,reason,mod,w in decisions])

risks=[
 ('DB-001','CRITICAL','Migration histórica borra entregas y dependencias','database/migrations/2026_06_20_144115_add_unique_constraint_to_entrega_tarea_table.php','Prohibir replay/importación ciega; exportar duplicados íntegros y aprobar conciliación sin borrado. Usa alias global DB sin import explícito: verificar resolución en bootstrap autorizado, no error runtime confirmado. down no recupera filas.'),
 ('DB-002','HIGH','Cascadas de identidad y nota oficial amenazan histórico','persona -> users/personal/estudiante; calificacion.cod_est','Estados en dominio y FK restrictivas futuras; ninguna cascada corregida ahora.'),
 ('DB-003','HIGH','Morph Sanctum bigint incompatible con cod_usu textual','personal_access_tokens.tokenable_id; vendor Schema Builder/Blueprint','Alter conservador después de perfilado y contrato de morph; no migrar User a bigint.'),
 ('DB-004','HIGH','CI UNIQUE solo número contradice posible CI/complemento','persona.ci_per/com_per; PersonaInteligente.buscarCoincidencias','Resolver regla institucional y normalización; no quitar UNIQUE ni crear compuesto sin perfilado.'),
 ('DB-005','HIGH','Generación secuencial por último código no serializa escritores','Models Persona/Docente/planes/Bitacora::creating','Conservar PK; usar generador único/transacción o ID aleatorio compatible; probar carrera en PG aislado.'),
 ('DB-006','HIGH','hasOne de perfiles no garantizado por UNIQUE','personal_institucional.cod_per; docente/director/secretaria/regente/administrador.cod_pin','Decidir vínculo temporal frente a perfil único; reconciliar duplicates sin borrarlos.'),
 ('DB-007','HIGH','Checks NOT VALID no equivalen a conservación sin efectos','MIG-006 y GradeService/EntregaService/OrientacionService','Separar restricciones; perfilar rangos y actualizaciones históricas antes de autorizar.'),
 ('DB-008','HIGH','Orientación sin edición/instrumento/algoritmo reproducibles','orientacion_preguntas/respuestas/resultados; OrientacionService.finalizar','Versionar instrumento y algoritmo; congelar preguntas de intento finalizado; no convertir Likert 1..5 en RIASEC.'),
 ('DB-009','HIGH','Reporte legado fabrica compatibilidad y perfiles a partir de notas','DatosReporteVocacionalService.calcularCompatibilidad / riasecPorEspecialidad','No persistir ni publicar como medición; reemplazo funcional mediante contrato especializado aprobado y evidencia válida.'),
 ('DB-010','HIGH','Writers directos de auditoría eluden BitacoraService','TurnoInteligente:2249; GestionCurso:3648; GestionAcademica:1722; GestionTurnos:2317; GestionInscripciones:2762','Unificar cinco writers legacy con BitacoraService y redactar PII; nunca agregar segunda bitácora.'),
 ('DB-011','HIGH','Kardex todavía obliga plan/fecha sin hora para todo hecho','MIG-001; ScopedKardexRepository','Definir contexto de incidente general, evento vs captura, versión coherente, permisos y revisiones append-only.'),
 ('DB-012','MEDIUM','Estado global del período no expresa calendario anual','periodo_evaluacion; GradeService','Extensión anual condicionada a contrato, sin inventar fechas ni reescribir notas históricas.'),
 ('DB-013','MEDIUM','Asistencia UNIQUE con bloque nullable y estado','asistencia_clase uq_asistencia_clase_bloque_estado; AsistenciaService.firstOrCreate','Diseñar identidad de sesión estable y política NULL; el lock de clase ayuda, no sustituye integridad fuera del Service.'),
 ('DB-014','MEDIUM','Campos de contexto redundantes carecen de garantía cruzada','calificacion.cod_asi/cod_pas; calificacion_tarea; orientacion_respuestas.cod_est','Mantener compatibilidad; comprobar coincidencia en Service, considerar FK compuestas solo después de reconciliación.'),
 ('DB-015','MEDIUM','Capacidad referencial y consultas de tablas/columnas no declaradas','InscripcionAcademica capacidadParalelo/cap_esp_tec; GestionAcademicaInteligente reporte/calificacion_estudiante','Capacidad es configuración anual, no dato derivable; no crear tabla reporte para satisfacer fallback; corregir JOIN a cod_pas y alias.'),
 ('DB-016','MEDIUM','ProgresoCurso mezcla entregas de distintos estudiantes','ProgresoCursoService.porcentaje','Definir métrica por estudiante y total de tareas elegibles; no persistir valor agregado defectuoso.'),
 ('DB-017','MEDIUM','Exportación SQL parcial no es respaldo restaurable','GeneradorSqlAcademicoService.tablas/generar','Faltan catálogos/planes y orden FK completo, DDL/consistencia; separar exportación de backup validado sin importar el archivo.'),
 ('DB-018','MEDIUM','Factories y suite SQLite no certifican schema PostgreSQL','UserFactory.name/Team; phpunit.xml DB_CONNECTION sqlite force','Plan separado para PG aislado, Persona/cod_usu requeridos; no cambiar guardas ahora.'),
 ('DB-019','LOW','Índices aislados de baja selectividad y checks de rangos ausentes','estado_asistencia flags/horarios/estados','Priorizar planes reales y compuestos; no agregar o retirar índices automáticamente.'),
 ('DB-020','LOW','Fotos y campos framework sin contrato de precedencia','persona.fot_per; users.avatar/profile_photo_path/current_team_id','Mantener; current_team_id candidato de retiro de columna, no tabla ni pérdida de fotos.')]
csvwrite(OUT/'bd-riesgos.csv',[{'ID':id,'SEVERITY':sev,'PROBLEM':problem,'EVIDENCE':ev,'RECOMMENDATION':rec} for id,sev,problem,ev,rec in risks])

counts={'tables_declared_historical':len(H),'tables_sql_snapshot':len(A['sql_snapshot']['tables']),'tables_sql_only':1,'tables_new_proposed':14,'tables_inventory_total':len(table_rows),
 'columns_historical':sum(len(t['columns']) for t in H.values()),'columns_new_or_extension':sum(c['phase']=='PROPUESTA_NO_EJECUTADA' for t in T.values() for c in t['columns'].values()),'columns_inventory_total':len(column_rows),
 'models':len(M),'migrations_historical':52,'migrations_proposed':6,'fks_historical':sum(len(t['fks']) for t in H.values()),'fks_overlay_total':sum(len(t['fks']) for t in T.values()),
 'indexes_historical':dict(Counter(i['kind'] for t in H.values() for i in t['indexes'])),'checks_historical':sum(len(t['checks']) for t in H.values()),
 'classification_inventory':dict(Counter(r['STATUS'] for r in table_rows)),'risks':dict(Counter(r[1] for r in risks)),
 'support_count':len(supportmatrix),'support_direct_db':sum(r['QUERY_BD_DIRECT'] for r in supportmatrix),'support_query_sites':len(query_sites),
 'window_status':dict(Counter(w['ESTADO ACTUAL'] for w in windows)),'migration_apt_as_is':2,'migration_redesign':4,'migration_logical_needs':7,'proposed_future_packages':8,'target_new_tables':15,'new_physical_migrations_this_audit':0}
(OUT/'bd-resumen.json').write_text(json.dumps(counts,ensure_ascii=False,indent=2)+'\n',encoding='utf-8')

reltext='''# Relaciones del modelo real y objetivo

Estado: propuesta para revisión arquitectónica, 2026-10-01. **No se consultó PostgreSQL.** Las cardinalidades siguientes se calculan por FK y UNIQUE declarados, no por el nombre `hasOne`. Fuente completa: [bd-relaciones-cardinalidad.csv](evidencia/bd-relaciones-cardinalidad.csv); [bd-modelos.csv](evidencia/bd-modelos.csv) conserva todas las relaciones Eloquent y argumentos.

## Propiedad y cardinalidades esenciales

| Relación | Declarado | Objetivo y dependencia histórica |
|---|---|---|
| Persona → User | 1:0..1; users.cod_per UNIQUE, DELETE CASCADE | Una identidad y cuenta opcional; estados en vez de borrar; evitar perder registros del autor. |
| Persona → Personal | 1:0..N; no UNIQUE cod_per | Resolver múltiples vínculos laborales históricos frente a uno vigente; cargo no es Role. |
| Persona → Estudiante | 1:0..N según FK; no UNIQUE cod_per | Definir un vínculo lógico de estudiante; inscripciones por gestión conservan historia. |
| Personal → Docente/perfiles | 1:0..N aunque modelos hasOne | Docente contiene especialidad y referencia de planes; preservar entidad; perfilar duplicados antes de UNIQUE. |
| User ↔ Role/Permission | M:N Spatie, PK compuestas y cod_usu textual | Un actor institucional en dominio; complementarios autorizados por RolePermissionService. Sin FK polimórfica a User en los pivots; no inferirla. |
| Gestión/Catálogo grupo → PlanAsignatura | 1:N por seis FK | Plan conserva gestión, grado, sección, jornada y docente. Curso es grado; no crear otro catálogo Grado equivalente. |
| Estudiante ↔ Gestión | N:M mediante InscripcionEstudiante; UNIQUE estudiante/gestión | Una inscripción lógica por gestión, cambios de grupo requieren historial; no copiar ni reescribir la matrícula anterior. |
| PlanAsignatura → ClaseVirtual | 1:N permitido por schema | No imponer 1:1 sin decidir varias aulas/ediciones; el Service conserva alcance del plan. |
| ClaseVirtual ↔ Estudiante | M:N con clase_estudiante | Membresía LMS no sustituye inscripción vigente; mantener cruces por las cuatro dimensiones del plan. |
| Clase → Publicación/Material/Tarea | 1:N | Autor y visibilidad son hechos; material adjunto a tarea tiene dueño distinto del material de aula. |
| Tarea → Entrega | 1:N; estudiante+tarea UNIQUE | Una entrega lógica, con devolución/rectificación; no perder intentos/archivos al conciliar duplicados. |
| Entrega → Archivo/Calificación LMS | 1:N archivos; UNIQUE cod_ent de calificación | Puntaje y máximo histórico no equivalen a nota oficial; preservar escala del momento. |
| Clase → Sesión asistencia → Marca estudiante | 1:N y 1:N; marca UNIQUE sesión/estudiante | Bloque nullable y estado en UNIQUE de sesión no expresan una identidad estable ante todas las escrituras. |
| Estudiante/Plan/Período → Nota oficial | 1:N; UNIQUE estudiante/plan/período admite plan nulo | El plan histórico nulo no se infiere; consulta legacy por asignatura sigue siendo necesaria. |
| Horario → Detalle → Plan curricular/técnico | 1:N; CHECK XOR planes | Bloque debe pertenecer a la plantilla y plan al mismo contexto del horario; FK individuales no garantizan toda esa coherencia. |
| Intento orientación → Respuestas/Resultado | 1:N respuestas; 1:0..1 resultado | Congelar edición e instrumento; cod_est duplicado debe coincidir. CASCADE no es política adecuada para borrar intento finalizado. |
| Seguimiento futuro → revisiones/evidencias | 1:N con RESTRICT | Dueño del hecho: KardexService; revisiones append-only. Bitácora registra la operación sin sustituir el historial de dominio. |
| Unidad futura → recursos | 1:N FK compuesta unidad/clase | UNIQUE(id,cod_cla) es soporte deliberado de integridad de contexto. No se asigna unidad a contenidos históricos por inferencia. |
| User → Notifications | 1:N institucional; interfaz Laravel usa morph | FK actual solo admite users; fijar discriminador y contrato antes de habilitar otros tipos. |
| Meta/Evento futuro → revisión | 1:N RESTRICT | Registro cancelado o corregido conserva autor, motivo y evento previo; sin borrado normal. |

## Grafo conceptual

```mermaid
erDiagram
  PERSONA ||--o| USERS : cuenta
  PERSONA ||--o{ PERSONAL : vinculos
  PERSONAL ||--o{ DOCENTE : perfil_declarado
  PERSONA ||--o{ ESTUDIANTE : vinculo_declarado
  ESTUDIANTE ||--o{ INSCRIPCION : historico
  GESTION ||--o{ INSCRIPCION : contexto
  DOCENTE ||--o{ PLAN_ASIGNATURA : docencia
  GESTION ||--o{ PLAN_ASIGNATURA : contexto
  PLAN_ASIGNATURA ||--o{ CLASE_VIRTUAL : aula
  CLASE_VIRTUAL ||--o{ TAREA : evaluable
  TAREA ||--o{ ENTREGA : evidencia
  ENTREGA ||--o| CALIFICACION_LMS : snapshot
  PLAN_ASIGNATURA o|--o{ CALIFICACION_OFICIAL : historico_nullable
  ESTUDIANTE ||--o{ SEGUIMIENTO_PROPUESTO : hecho
  SEGUIMIENTO_PROPUESTO ||--o{ REVISION_PROPUESTA : historial
```

## FK, historia y orden de dependencia

Las 96 FK históricas se cuentan separadas de las 128 del overlay hipotético de seis propuestas: MIG-006 reemplaza una FK y se añaden 32 nuevas netas. El overlay no demuestra aplicación. Cada FK, acción DELETE/UPDATE y fuente se conserva en los CSV. Las FK de horarios condicionadas por hasTable/hasColumn describen la intención sobre un schema completo, no prueban que se crearan en una base incompleta.

Identidad precede a cuenta/vínculos; catálogos académicos preceden a planes e inscripciones; plantilla precede a horario/bloques/detalle; plan precede a clase; clase precede a contenido/entrega/asistencia; intento precede a respuestas/resultados; nuevos catálogos preceden a seguimiento y luego a revisiones/evidencias. La futura aplicación debe basarse además en el ledger real de la BD aislada, no solo en el orden de nombres.

Históricos oficiales, orientación finalizada y revisiones: `IMMUTABLE_HISTORY`/`STATUS_BASED`. Sesiones, tokens y cache: `HARD_DELETE_ALLOWED` por expiración aprobada. No se adopta SoftDeletes universal: ningún Model local declara SoftDeletes. Borrado físico de personas y cascadas actuales exige cambio específico, no una flag cosmética.
'''
write('34-RELACIONES-BD.md',reltext)

debt='# Deuda técnica de datos\n\nEstado: auditoría estática, sin cambios de schema ni correcciones de aplicación. Riesgo potencial no equivale a incidente confirmado.\n\n'
for sev in ['CRITICAL','HIGH','MEDIUM','LOW']:
    rs=[r for r in risks if r[1]==sev]; debt+=f'## {sev} — {len(rs)}\n\n'
    for id,_,problem,ev,rec in rs: debt+=f'### {id} — {problem}\n\nEvidencia: `{ev}`.\n\nAcción propuesta: {rec}\n\nValidación futura: datos sintéticos y transacciones concurrentes en PostgreSQL aislado aprobado; conciliación del histórico por el responsable. No se ejecuta en esta fase.\n\n'
debt+='''## Límites de las conclusiones

No se declaran tablas retirables por ausencia de caller. Se incluyen modelos, Services, Support, controllers, Livewire, rutas, vistas, tests, factories y seeders; llamadas dinámicas y contratos de framework se señalan. Solo current_team_id es candidato de retiro **de columna**, sujeto a demostrar que equipos siguen desactivados y a revisar integraciones. No se propone borrar ninguna tabla ni migration histórica.

No se han medido cardinalidades, duplicados, tiempos, planes EXPLAIN, tamaño de índices, rangos históricos ni retención real en PostgreSQL. Cualquier UNIQUE/CHECK nuevo necesita perfilado autorizado posterior. El volcado histórico se analiza solo como DDL, nunca como permiso para importar datos personales.
'''
write('36-DEUDA-TECNICA-BD.md',debt)

print(json.dumps(counts,ensure_ascii=False,indent=2))

target='''# Modelo de datos objetivo de SAVP-TIS3

ADR-BD-001. Fecha: 2026-10-01. **Estado: PROPUESTO, pendiente de revisión arquitectónica.** No equivale a aceptación ni autorización de ejecutar. Decisión global: **CON CAMBIOS**. Se preserva el núcleo canónico y las 105 ventanas originales; el dominio define persistencia y luego se cruza con las ventanas.

## Contexto, opciones y decisión

El checkout declara 64 tablas históricas y propone otras 14 en seis archivos no ejecutados. El volcado histórico contiene 33 tablas, una de ellas `migrations` creada por infraestructura. No conocemos el schema ni las filas actuales de la institución. El inventario une fuentes con origen explícito; no afirma que 79 tablas estén desplegadas.

Se compararon tres opciones: conservar todo y añadir tablas por pantalla; sustituir el núcleo por un schema nuevo; evolucionar las entidades actuales, añadiendo solo hechos faltantes. Se propone la tercera. Permite conservar identidad, notas históricas, matrícula y ownership del LMS, y evita duplicar Curso/Asignatura/Tarea/Bitácora. Exige conciliar datos y garantizar compatibilidad de lectores durante cada extensión. No se propone un reemplazo masivo ni un nuevo CRUD Peter 3 sobre PostgreSQL.

## Entidades existentes y modificaciones

| Estado | Entidades | Decisión de dominio |
|---|---|---|
| EXISTENTE | Persona, User, PersonalInstitucional, Estudiante, Docente | Identidad independiente de credencial y vínculo. Persona puede existir sin cuenta; User tiene una Persona por UNIQUE. Cargo laboral y Role no son equivalentes. Docente conserva especialidad y FK de planes. |
| MODIFICAR | Persona/Personal/perfiles | Resolver CI+complemento, correo case-insensitive y cardinalidad de perfiles mediante reglas aprobadas y perfilado. Conservar CI actual hasta conciliación. No hacer del género/expedido un catálogo administrativo sin necesidad real. |
| EXISTENTE | Role, Permission y tres pivots Spatie, RoleRequest | Role único. RolePermissionService único escritor operacional de autorizaciones. Un actor institucional por User; roles complementarios no sustituyen el perfil ni amplían el alcance automáticamente. Solicitud conserva evidencia y aprobación humana. |
| MODIFICAR | Tokens Sanctum y cascadas de identidad | Morph identificador varchar(20) compatible con User; revisar datos antes del ALTER. Cambiar retención mediante corrección aprobada; nunca sustituir cod_usu por bigint para acomodar un default del framework. |
| EXISTENTE | Gestión, Curso, Paralelo, Turno, Asignatura, Especialidad | Curso representa grado/nivel; grupo anual es gestión+curso+paralelo+turno. PlanAsignatura asigna docente y materia; ClaseVirtual organiza un aula de ese plan. No crear Grado, CursoVirtual o Asignación duplicados. |
| MODIFICAR | PeríodoEvaluación | Catálogo global actual con orden; fechas/gestión no existen. Diseñar una edición anual cuando institución defina calendario; no inferir esas fechas ni cambiar el período de notas anteriores. |
| EXISTENTE | Inscripción, documentos, PlanAsignatura, PlanEspecialidad, RegenteAsignacion | Matrícula UNIQUE estudiante/gestión representa una trayectoria anual. Cambio/retirada/anulación conserva fechas y motivo; transferencias requieren historial, no sobreescritura silenciosa. Regencia por gestión/grado reutiliza tabla actual y fail-closed. |
| MODIFICAR | Configuración de capacidad | La capacidad oficial de grupo/especialidad por gestión es un hecho configurado; los cupos usados/disponibles se derivan. Hoy hay fallback referencial y columnas opcionales ausentes. FUTURO: determinar si EXTEND de relación anual o entidad de cupo contextual; no crear una tabla genérica ahora. |
| EXISTENTE | PlantillaHoraria, Horario, HorarioBloque, HorarioDetalle | Jornada viene de plantilla; detalle no repite contexto. Preservar CHECK XOR plan_asignatura/plan_especialidad. Coherencia bloque/plantilla/grupo necesita validación y eventualmente FK compuesta. |
| EXISTENTE | ClaseVirtual, ClaseEstudiante, Publicación, Material, Tarea, TareaMaterial, Entrega y Archivo | Tarea ya modela actividad evaluable; actividad_clase registra telemetría. Material de clase y adjunto de tarea tienen distintos owners. Reutilizar antes de biblioteca compartida. |
| MODIFICAR | Calificación oficial/LMS y Asistencia | Nota oficial y nota de tarea son hechos distintos. Conservar máximo de tarea al evaluar; no transformar nota LMS en oficial. Sesión y marca individual se conservan; porcentajes/puntualidad se derivan con denominadores y catálogo de estados explícitos. |
| EXISTENTE | OrientaciónActividad/Pregunta/Respuesta/Resultado/CarreraSugerida | Escala local Likert 1..5; resultados orientativos. No demuestra validez RIASEC, diagnóstico cognitivo o compatibilidad con carrera. Conservar históricos y no convertir escalas sin contrato. |
| MODIFICAR | Orientación local versionada | Edición de instrumento/preguntas y versión de algoritmo para reproducir resultado finalizado. MIG-007 propuesta añade una entidad mínima de versión y vínculos nullable, sin reconstruir versiones históricas ficticias. |
| EXISTENTE | Bitácora, logs de cuenta, metadatos de reportes/respaldo | Hechos de auditoría y artefactos; escritor objetivo BitacoraService. Revisiones de dominio no son una segunda bitácora genérica. Hash de archivo verifica integridad, no autenticidad institucional. |
| LEGACY_CONSERVADO | Administrador/Director/SecretariaGeneral y carreras sugeridas locales | Hay relaciones/lectores y posiblemente historia. Preservar; eventual retiro exige evidencia de reemplazo y aprobación. No se declara tabla sin uso por no encontrar escritor. |

## Nuevos hechos necesarios del alcance aprobado para revisión

Son **15 tablas objetivo propuestas**: 14 ya descritas por las seis migrations no ejecutadas y una adicional de versión de instrumento. Que una tabla sea necesaria para el dominio no aprueba su implementación concreta ni los valores del catálogo.

| NUEVO | Tablas | Hecho persistido / propietario |
|---|---|---|
| Kardex | kardex_tipos, kardex_categorias, kardex_niveles, kardex_estados, kardex_medidas | Configuración institucional por eje/version; pendiente aprobación de reglas. |
| Seguimiento | seguimiento_academico, seguimiento_revisiones, seguimiento_evidencias | Hecho formativo, responsable, acuerdo/cierre/rectificación/anulación, autor, instante y evidencia privada; KardexService futuro. |
| Secciones LMS | unidades_clase | Orden/publicación/archivo de contenido de una clase; no unidad curricular normativa inferida. UnitContentService. |
| Notificación | notifications | Aviso/destinatario/lectura persistidos vía Laravel; NotificationService y productores after-commit pendientes. |
| Meta personal | metas_academicas, meta_academica_revisiones | Objetivo y acción personal, transición humana e historial; AcademicGoalService. |
| Calendario | calendario_evento, calendario_evento_revisiones | Evento confirmado/cancelado, motivo/efecto y alcance temporal; no duplicar plazo de tarea o fechas de gestión. |
| Instrumento local | orientacion_instrumento_versiones — SOLO DISEÑO | Identidad de edición, escala, aprobación y algoritmo; OrientacionService, sin nueva tabla genérica de resultados Peter 3. |

### Kardex: cinco catálogos frente a dos tablas genéricas

Se propone mantener cinco relaciones **tipadas**: tipo, categoría, nivel, estado y medida describen ejes distintos, y las FK impiden usar un estado como medida. Dos tablas genéricas (cabecera/items con discriminador) reducirían tablas pero exigirían FK compuestas con discriminador por cada eje o triggers para asegurar esa misma separación. No se obtiene una mejora automática por tener menos tablas, y JSONB no sustituye estas relaciones.

La edición común `version_catalogo` de MIG-001 necesita publicación coordinada: todos los valores referenciados deben existir en la misma edición, con relaciones tipo/categoría/medida aprobadas. Alternativa si las ediciones cambian de forma independiente: versionar cada FK; decidirlo antes de corregir el archivo. Los estados de workflow técnico pueden ser enum+CHECK; los parámetros institucionales administrables deben persistir. No se inventan categorías, niveles disciplinarios ni sanciones.

`seguimiento_academico` conserva un único concepto compartido de seguimiento. En este checkout no hay NovedadEstudiante ni migration histórica de ese seguimiento; existen referencias selectivas de otra fase, no evidencia de deployment institucional. Asistencia no representa una observación formativa, y Bitácora no representa un acuerdo educativo. El texto de motivo/resultado/acción es suficiente para el mínimo inicial; no se necesita otra tabla de acuerdos salvo múltiples acuerdos versionados con identidad propia. Debe decidirse cuándo un hecho exige plan_asignatura y cuándo es general de estudiante/gestión/grado. MIG-001 obliga plan para todos: por eso necesita cambios.

### MIG-007 — edición de instrumento y reproducción local

**Estado:** PROPUESTA, archivo NO CREADO; nombre/timestamp de archivo por definir tras revisión. **Ventanas:** V037, V099, V100; V101 solo si consume la evaluación. **Módulo:** Orientación. **Nuevo:** orientacion_instrumento_versiones. **Extender:** orientacion_preguntas, orientacion_actividades, orientacion_resultados. Modelo objetivo OrientacionInstrumentoVersion (no creado), Service OrientacionService, Policy/ownership del intento por estudiante activo; supervisión institucional con alcance. No nuevo CRUD externo.

Columnas propuestas de versión: `id uuid PK NOT NULL`; `codigo varchar(40) NOT NULL`; `version varchar(40) NOT NULL`; `nombre varchar(180) NOT NULL`; `escala_min smallint NOT NULL`, `escala_max smallint NOT NULL`; `algoritmo_version varchar(80) NOT NULL`; `definition_hash char(64) NOT NULL`; `estado varchar(20) NOT NULL default BORRADOR`; `aprobado_por varchar(20) NULL FK users.cod_usu`; `aprobado_at timestamptz NULL`; `created_at,updated_at timestamptz NULL` según convención Laravel. UNIQUE(codigo,version), CHECK escala_max>escala_min, estados BORRADOR/APROBADO/RETIRADO y coherencia aprobación/autor/fecha, hash hexadecimal. DELETE/UPDATE de autor RESTRICT; índice estado y aprobado_por solo según consulta aprobada.

Preguntas e intentos: `instrumento_version_id uuid NULL FK` a versión con RESTRICT; NULL conserva legado sin inventar edición. Futuras preguntas se identifican por versión+codigo; la UNIQUE global actual de codigo se sustituye solo tras perfilado y ajuste de lectores. Congelar una pregunta publicada/contestada; una edición nueva crea otra identidad y nunca modifica respuestas finalizadas. Resultado: `algoritmo_version varchar(80) NULL`, `input_hash char(64) NULL`, `generated_at timestamptz NULL`, campos nullable para historia no identificable. UNIQUE actual de resultado por intento permanece; un recálculo científico es otro evento/version, no overwrite del finalizado.

Snapshot reproducible exige referencias estables a edición/preguntas/respuestas y parámetros, versión de algoritmo, hash de entrada y fecha. JSONB solo metadatos variables del cálculo si se demuestra necesidad; las seis dimensiones locales existentes siguen como valores del resultado, no se crean tablas paralelas RIASEC. Sin SoftDeletes: retirar edición no borra preguntas ni intentos. Auditoría por BitacoraService. Riesgos: histórico sin versión, permisos de catálogo, normalización de códigos, consumidor actual de preguntas visibles y alcance; no se resuelven con backfill inventado. Rollback futuro únicamente si no hay referencias ni resultados nuevos; con historia requiere corrección forward aprobada, no quitar vínculos. Dependencias: esquema local orientación, users, contrato del instrumento y perfilado. **AUTORIZACIÓN PETER 1 NECESARIA PARA EJECUTAR: SÍ.**

## Persistencia y derivación

| Clase | Datos | Tratamiento |
|---|---|---|
| MUST_PERSIST | Identidad, inscripción, asignación docente, alcance Regente, marcas de asistencia, notas, escala usada, respuestas, estados/decisiones oficiales, metas, hechos de evento, revisiones, configuración institucional | PostgreSQL es fuente de verdad; autor/tiempo y ownership cuando corresponda. |
| DERIVABLE_REALTIME | Nombre completo, edad, completitud de formulario, sugerencia, advertencia, normalización provisional, rango temporal mostrado | Calcular en Laravel/Support; corrección elegida sí puede convertirse en dato validado. No persistir análisis preventivo de cada tecla. |
| DERIVABLE_AGGREGATE | Promedio por gestión/período, asistencias válidas/total elegible, entregadas/total tareas por estudiante, conteos, cupos usados/disponibles, avance de orientación, riesgo preliminar | Consulta con scope y denominador; no tabla por widget. Derivable solo con fuente completa: contadores de acceso no se eliminan si falta ledger. |
| DERIVABLE_MATERIALIZED | Agregado costoso de reporte medido; contadores framework de cola | Considerar cache/consulta agrupada primero. Vista materializada solo si costo/frescura lo justifican; sin creación ahora. |
| Snapshot necesario | Nota LMS con máximo al calificar; resultado orientativo finalizado con edición/algoritmo; evidence de RoleRequest aprobado; artefacto exportado con hash | Conservar hechos y definición del momento. Un cálculo reproducible puede requerir snapshot histórico aunque hoy se derive. |

`estado_asistencia.valor_porcentual` es **configuración persistente**, no porcentaje derivado de un estudiante. `inscripcion_estudiante.sob_aut_ins` es autorización, no cupo calculado. `doc_com_ins` requiere distinguir verificación humana de completitud mecánica antes de cambiarlo. `users.email` es login, `persona.ema_per` contacto: no se fusionan por coincidencia de nombre. `pun_max` de calificación LMS no se reemplaza por el máximo vigente de tarea.

## Analítica y vistas materializadas conceptuales

| Función | Inicio recomendado | FUTURO opcional | Refresco/costo/consumidor |
|---|---|---|---|
| Rendimiento anual oficial | JOIN calificación→plan→período y GROUP BY con scope | mv_rendimiento_gestion | Solo notas con cod_pas; costo según notas/gestiones; refresco tras cierre o lote autorizado. Dashboard/Reportes; consumidor vuelve a filtrar scope. |
| Asistencia por estudiante | JOIN marca→sesión→clase→plan con catálogo de estado | mv_asistencia_estudiante_gestion | Definir denominador y ponderación; lote por gestión si medición lo requiere. Asistencia/Regencia; revisar revocación antes de servir cache. |
| Avance de tareas | COUNT por estudiante y tareas elegibles, no suma global de entregas | Cache por estudiante/clase | Invalidar por entrega/devolución/publicación y cambios de matrícula. Progreso LMS. |
| Orientación/prevención | Resultado local finalizado versionado o DTO especializado | Sin vista general por defecto | No ejecutar diagnóstico ni ranking científico por SQL con proxies de notas/especialidad. |

Consulta conceptual de rendimiento (documentación; **no ejecutada**):

```sql
SELECT p.cod_gea, c.cod_est, c.cod_pev, AVG(c.not_cal) AS promedio
FROM calificacion c JOIN plan_asignatura p ON p.cod_pas = c.cod_pas
WHERE c.est_cal = 'ACTIVO' /* más condiciones de scope autorizadas */
GROUP BY p.cod_gea, c.cod_est, c.cod_pev;
```

No atribuye una gestión a cod_pas NULL: el historial sin contexto se muestra separadamente. No se crean vistas materializadas, procesos de refresh, tablas de métricas o índices analíticos por anticipación. MV no es un hecho oficial ni garantiza privacidad; exige permisos y filtros en cada lectura.

## Estados, fechas, JSONB, índices y privacidad

Workflow técnico acotado: enum PHP y CHECK sincronizados, transitions en Service. Catálogo administrable institucional: tabla y FK/version. Parámetros con vigencia: configuración relacional contextual; no hardcodear capacidad real. Las fechas de gestión/plantilla y plazos LMS ya persistidas se reutilizan. Períodos anuales, efectos de eventos, ediciones Kardex y capacidad oficial son decisiones pendientes; no se rellenan con sugerencias.

JSONB: antes/después de una revisión, metadatos variables, payload mínimo de aviso, resultado científico autorizado con identidad/versión fuera del JSON. No usarlo para matrícula, autorizaciones, líneas de tarea, FK de catálogos o columnas obligatorias de Persona. CHECK jsonb_typeof no garantiza privacidad, esquema de payload ni inmutabilidad: debe validarlo el Service.

PUBLIC solo para información institucional explícitamente publicable; no hay permiso por ser catálogo. INSTITUTIONAL para configuración/horario/organización; PERSONAL para identidad/contacto/referencias; SENSITIVE para credenciales, respuestas, seguimiento, notas y audit payload identificable. Kardex revela metadatos mínimos según actor y visibilidad; quien puede leer notas no obtiene automáticamente motivos de seguimiento. Doble control Policy+scope en lectura y mutation; minimizar DTO y auditoría. Retención/rectificación requiere política aprobada, no plazo legal inventado.

Las FK no crean automáticamente índices del lado referente en PostgreSQL; PK/UNIQUE sí tienen respaldo de índice. No duplicar ese respaldo ni indexar booleans indiscriminadamente. Las recomendaciones IDX de la auditoría se basan en filtros observados y son propuestas sujetas a EXPLAIN en ambiente aislado. CHECK/UNIQUE no sustituyen autorización, correlación de contexto ni concurrencia.

## Peter 3: frontera de datos y resultados

Laravel controla lectura/escritura de PostgreSQL, ownership, Policy, validación y auditoría. Peter 3 recibe DTO y devuelve datos de un contrato; acceso directo DB: **NO**. AcademicAnalysisData contiene student_id, período, notas con escala, asistencia agregada, intereses declarados y especialidad; AporteIngenierilClient sustituye student_id por HMAC antes del HTTP. No envía Persona, CI, dirección, teléfono, contraseña ni contexto Kardex. La clave HMAC actual proviene de app.key: rotación altera pseudónimo y exige política de integración.

| Funcionalidad | Origen / DTO | Personal/agregado | Persistencia objetivo |
|---|---|---|---|
| Análisis académico | GradeService/contexto → AcademicAnalysisData → Peter3Contract | Pseudónimo; notas/escala por período, asistencia agregada, intereses y especialidad mínimos | FUTURO PETER3_RESULT solo si hay resultado validado que deba conservarse: operación/algoritmo/modelo/versiones, input_hash, fecha, trace_id, cobertura y resultado revisado. No tabla genérica anticipada. |
| Fuentes académicas | query/top_k/official_only → KnowledgeService | Texto de búsqueda; evitar PII en consulta | Respuesta efímera/corpus especializado; corpus/model/retrieval_version recibidos no exigen tabla Laravel nueva. Guardar favorito/cita solo si se aprueba necesidad. |
| Asistente de estudio | question/schema_version → TutorService | Texto libre potencialmente personal; no historial automático | NO_PERSIST por defecto; SESSION efímera opcional; HISTORY_OPT_IN futura requiere consentimiento/retención y contrato. Sin tabla de chat ahora. |
| RIASEC/carreras/cognitivo | Instrumento aprobado y escala específica aún no equivalente | Respuestas sensibles solo por consentimiento/contrato mínimo | FUTURO, no convertir respuestas locales ni guardar diagnóstico inferido del promedio. |

Validar contratos, autorización, resultado insuficiente y fallback; Peter 3 no dicta Role, sanción, nota oficial, evento ni matrícula. Laravel revisa decisión humana antes de convertir una recomendación en hecho. Las tablas de orientación local se extienden cuando el concepto coincide; no se duplica `orientacion_resultados` con `peter3_results` universal.

## Plan de aplicación futura y testing

**Hoy:** base aislada aprobada NO EXISTE. No se crea ni se conecta. No se ejecutan migrations, seeders, SQL, rollback ni pretend. La suite existente fuerza SQLite :memory: y DB_URL vacío; se conserva esa protección. Los resultados previos de tests no prueban PG ni estas propuestas.

1. Revisión arquitectónica de este ADR, ocho paquetes propuestos, reglas CI/perfiles, instrumentos, Kardex/eventos, privacidad y retención. Peter 1 autoriza expresamente el siguiente paso, sin credenciales institucionales.
2. Definir servidor aislado y base `savp_tis3_testing`, usuario sin privilegios ni acceso al servidor institucional. Verificar host/db/url/schema allowlist y APP_ENV testing mediante guardas independientes. No heredar .env, DB_URL o caches productivos. No cambiar el phpunit actual para usar PG indiscriminadamente.
3. Revisar las 52 migrations históricas y el ledger, incluidas rutas aula_virtual, limpieza destructiva de entregas y actualización auth_provider. Aprobar manifiesto de bootstrap limpio o replay corregido explícito en entorno aislado. No importar el volcado con datos ni borrar migrations históricas.
4. Crear schema de prueba base autorizado; fixtures/factories sintéticos con Persona y User.cod_usu/cod_per y catálogos mínimos. UserFactory actual usa name y Team inexistente; corregir solo en fase autorizada, sin datos de personas reales. Seeders institucionales no se ejecutan por defecto.
5. Aplicar futura corrección estructural aprobada MIG-006A (incluye compatibilidad tokens/FK/índices priorizados); después los checks MIG-006B con datos sintéticos inválidos y válidos. Perfilado de datos reales requerirá otro acceso autorizado de solo lectura; no declararlo hecho ahora.
6. Catálogos/seguimiento/revisiones/evidencias MIG-001 corregida; unidades y enlaces MIG-002; notificaciones MIG-003 corregida; metas/revisión MIG-004; eventos/revisión MIG-005 corregida; edición orientación MIG-007. Cada paquete debe conciliar preexistencia y dependencias; no usar CREATE para sobrescribir una tabla descubierta.
7. Pruebas PG de PK textual/morphs, UNIQUE y NULL, FK cruzadas, rangos CHECK, histórico sin cod_pas, actualización de fila histórica bajo NOT VALID, concurrencia de códigos/entrega/notas, rollback vacío y rechazo con historia, append-only, scope RBAC por seis actores y revocación. API fakes para Peter 3 y pruebas de ausencia de PII en payload/logs.
8. Pruebas de lectura/escritura real y UX autenticada; habilitar flags solo con evidencia y contrato aprobado. Un archivo de migration o php -l nunca certifica PASS de una ventana.

Paquetes objetivo: MIG-001, MIG-002, MIG-003, MIG-004, MIG-005, MIG-006A estructural, MIG-006B escalas y MIG-007 orientación versionada: **8 propuestas de aplicación futura**, derivadas de siete necesidades de dominio/integridad; no son ocho archivos creados. Los seis archivos físicos anteriores se preservan como evidencia. Al revisar, decidir nombres y sustitución de MIG-006 sin registrar dos veces sus constraints. Cambios de CI, perfiles, cupos, períodos anuales y resultados científicos futuros pueden requerir paquetes adicionales según perfilado/contrato: no se incluyen a ciegas en el conteo cerrado de esta fase.

## Consecuencias y revisión requerida

Se conservan nombres/IDs de dominio y compatibilidad; se agregan solo hechos persistentes identificados. El costo es resolver versiones, propiedad de cambios y restricciones que el schema actual no garantiza. No se afirma modelo institucional final aceptado sin revisión de reglas y datos. Tablas candidatas de retiro: ninguna con evidencia suficiente. Columnas candidatas se preservan. Acciones siguientes: revisión del responsable de arquitectura/Peter 1 y aprobación del plan aislado. **DETENER implementación dependiente de BD hasta esa revisión.**
'''
write('35-MODELO-DATOS-OBJETIVO.md',target)

p3=[{'FUNCTION':fn,'ORIGIN':origin,'DTO':dto,'PERSONAL_OR_AGGREGATE':privacy,'PERSISTENCE':persist,'DIRECT_DB':'NO','CONTRACT':'Peter3Contract / SpecializedAcademicClient; aprobación especializada pendiente para RIASEC'} for fn,origin,dto,privacy,persist in [
 ('analysis','Laravel: notas/asistencia/intereses con scope','AcademicAnalysisData; student_id HMAC en Client','Pseudónimo y datos académicos mínimos; asistencia agregada','FUTURO condicionado a resultado reproducible aprobado; no tabla genérica'),
 ('knowledge','Consulta del usuario; corpus especializado externo','query,top_k,official_only,schema_version','Texto libre; evitar PII','NO_PERSIST actual; favorito/cita solo futuro aprobado'),
 ('tutor','Pregunta del usuario','question,schema_version','Texto libre potencialmente sensible','NO_PERSIST; SESSION opcional; HISTORY_OPT_IN futuro'),
 ('RIASEC / orientación especializada','Instrumento/version/consentimiento aprobados aún pendientes','No convertir Likert local 1..5 a otra escala','Respuestas sensibles; contrato mínimo','FUTURO; local existente no equivale a RIASEC científico')]]
csvwrite(OUT/'bd-peter3-data.csv',p3)

# Save the prior migration review intact before adding a clearly superseding review.
old25=OUT/'bd-25-migrations-antes-revision.md'
if not old25.exists(): old25.write_bytes((DOC/'25-MIGRATIONS-PROPUESTAS.md').read_bytes())
old=read(old25)
prefix='''# Revisión arquitectónica de migrations — 2026-10-01

**Prevalece esta revisión sobre la justificación inicial que se conserva debajo.** Los seis archivos físicos permanecen CREADA_NO_EJECUTADA, sin modificación. Una decisión APPROVE_AS_IS indica admisibilidad arquitectónica del archivo, **no autorización de ejecución ni PASS funcional**. PostgreSQL no fue consultado ni modificado. Detalle del modelo y MIG-007: [35-MODELO-DATOS-OBJETIVO.md](35-MODELO-DATOS-OBJETIVO.md).

| ID | Decisión | Consecuencia |
|---|---|---|
'''
for id,d,reason,_,_ in decisions: prefix+=f'| {id} | **{d}** | {reason} |\n'
prefix+='''
Admisibles sin cambio de schema: **2/6** (MIG-002, MIG-004). Requieren rediseño: **4/6** (MIG-001/003/005 y separación MIG-006). Ninguna propuesta se rechaza por duplicar una tabla existente en este checkout; eso no certifica ausencia en PostgreSQL institucional. MIG-001 no debe CREATE sobre seguimiento ya presente en una futura base; primero comparar columnas/keys. Cinco catálogos tipados mantienen FK específicas; publicación/relaciones/valores requieren aprobación. Un catálogo genérico solo se adoptaría garantizando su discriminador, no porque tenga menos tablas.

## Necesidad adicional y paquetes futuros

**MIG-007:** PROPUESTA, NO_CREADA, Orientación local versionada; V037/V099/V100 y V101 si consume el resultado. Una nueva entidad `orientacion_instrumento_versiones` y extensiones nullable de preguntas/intentos/resultados. Schema actual carece de edición, escala/algoritmo/hashes congelados: un resultado finalizado no se reproduce de forma fiable si se editan preguntas. Sus columnas, PK/FK, CHECK, índices, privacidad, ownership y rollback conservador se especifican en el apartado MIG-007 del modelo objetivo. No añade RIASEC científico ni guarda resultados de Peter 3 indiscriminadamente.

Necesidades identificadas: Kardex, secciones LMS, notificación persistente, meta personal, evento institucional, integridad y edición de instrumento (**7 necesidades**). Plan de aplicación: **8 paquetes propuestos** al separar MIG-006A (relaciones/retención/morphs/índices) y MIG-006B (checks de escalas). Nombres/timestamps de futuros archivos por definir tras revisión; no crear reemplazos mientras se evalúa. **Archivos nuevos en esta auditoría: 0. Tablas nuevas del objetivo: 15 (14 de proposals existentes + versión de instrumento).** No incluye FUTURO condicionado de cupos/períodos/análisis Peter 3.

## Reutilización confirmada

No se necesita tabla nueva para actividad evaluable (Tarea), entrega/archivo (EntregaTarea/EntregaArchivo), nota LMS (CalificacionTarea), nota oficial (Calificacion), marcas de asistencia, orientación local, RBAC, matrícula por gestión, alcance de Regente, metadatos de archivos ni bitácora. Las unidades organizativas no equivalen a PlanAsignatura; agenda LMS reutiliza fec_lim_tar. Fuentes y tutor usan respuesta efímera/DTO; no chats ni corpus nuevo Laravel sin requisito de conservación aprobado.

NOT VALID evita el escaneo inicial, pero restringe nuevas inserciones y actualizaciones de filas; una anomalía histórica puede impedir actualizar esa fila aunque se conserve sin escaneo. No constituye una garantía de compatibilidad con el histórico. [PostgreSQL ALTER TABLE](https://www.postgresql.org/docs/18/sql-altertable.html).

## Orden futuro, sin ejecución

Revisión y aprobación → entorno aislado y guardas → manifiesto histórico seguro y fixtures sintéticos → MIG-006A estructural → MIG-006B checks verificados → MIG-001 catálogos/contexto/historia → MIG-002 → MIG-003 → MIG-004 → MIG-005 → MIG-007. El esquema base precede a todos los paquetes. Revisar despliegue independiente de flags y cambios de código; no habilitar escritor pendiente porque apareció la tabla.

============================================================
MIGRATIONS NECESARIAS
============================================================

Total del plan propuesto: **8 paquetes**, siete necesidades; no ocho archivos físicos.

MIG-001: archivo existente prepare_kardex_seguimiento_structure.php; CREATE/ALTER; Kardex y revisión/evidencia; ventanas en tabla original inferior. Creada físicamente SÍ; rediseño NO aplicado; ejecutada NO.

MIG-002: archivo existente prepare_lms_units_structure.php; CREATE/ALTER; unidades y enlaces sin duplicar recursos; V073/V090 y recursos relacionados. Creada físicamente SÍ; ejecutada NO.

MIG-003: archivo existente prepare_database_notifications.php; CREATE notifications; shell seis actores y avisos académicos. Creada físicamente SÍ; rediseño NO aplicado; ejecutada NO.

MIG-004: archivo existente prepare_student_academic_goals.php; CREATE metas/revisión; V102. Creada físicamente SÍ; ejecutada NO.

MIG-005: archivo existente prepare_institutional_calendar.php; CREATE eventos/revisión; V016/V042/V054/V067/V081/V096. Creada físicamente SÍ; rediseño NO aplicado; ejecutada NO.

MIG-006A: archivo futuro POR_DEFINIR; ALTER integridad/FK/tokens; reemplaza parte de MIG-006. Creada físicamente NO (MIG-006 original preservada); ejecutada NO.

MIG-006B: archivo futuro POR_DEFINIR; ALTER checks de escala con perfilado; reemplaza parte de MIG-006. Creada físicamente NO; ejecutada NO.

MIG-007: archivo futuro POR_DEFINIR; CREATE instrumento versionado/ALTER orientación local; V037/V099/V100. Creada físicamente NO; ejecutada NO.

============================================================
MIGRATIONS NO NECESARIAS
============================================================

Tablas duplicadas de Tarea/Entrega/Asistencia/Nota, tablas de sugerencias/completitud/porcentaje/contador de widget, otra bitácora, segundo Role, chat automático, biblioteca de fuentes efímeras y resultados Peter3 universales: NO propuestas. Reutilizar schema existente; corregir contrato/callers antes de extender.

============================================================
ORDEN PROPUESTO DE EJECUCIÓN FUTURA
============================================================

Ver orden anterior y plan de testing de 35. No ejecución autorizada durante esta fase.

============================================================
POSTGRESQL
============================================================

Base de testing existente: NO.
PostgreSQL institucional modificado: NO.
Migrations ejecutadas: NO.
Seeders ejecutados: NO.
Schema modificado: NO.
Listo para que Peter 1 revise las migrations: SÍ.
Listo para ejecutarlas: NO.

---

## Preparación original conservada — antecedente, no aceptación vigente

'''
write('25-MIGRATIONS-PROPUESTAS.md',prefix+old)

idx=[
 ('IDX-001','inscripcion_estudiante','cod_gea,cod_cur,cod_par,cod_tur,est_ins,cod_est','GradeService.teacherContext/save; CursoVirtualService; InscripcionAcademica','Grupo anual vigente; UNIQUE estudiante/gestión no sustituye orden del filtro por grupo','SUPPORT_DIRECT'),
 ('IDX-002','plan_asignatura','cod_doc,est_pas,cod_gea,cod_cur,cod_par,cod_tur','CursoVirtualService.teacherQuery; CalendarService','Actualmente solo PK; consultar asignación de docente y alcance','SERVICE'),
 ('IDX-003','calificacion','cod_pas,est_cal,cod_pev,cod_est','GradeService.gradesForTeacherCourse; InstitutionalQueryService','Índice cod_pas actual y UNIQUE de otra dirección; justificar extensión frente al índice simple','SERVICE'),
 ('IDX-004','orientacion_actividades','cod_est,created_at DESC','OrientacionService.resumen/actividadExistente','Último intento por estudiante; el compuesto actual incluye estado, no orden temporal','SERVICE'),
 ('IDX-005','persona','LOWER(ema_per)','PersonaInteligente.buscarCoincidencias','Igualdad case-insensitive; UNIQUE original ema_per no aplica a LOWER; no UNIQUE nuevo sin perfilado','SUPPORT_DIRECT'),
 ('IDX-006','tarea','cod_cla,est_tar,fec_lim_tar','CalendarService.query','Agenda con deadline y estado; compuesto clase/estado existente puede bastar: medir diferencia','SERVICE'),
 ('IDX-007','personal_institucional','cod_per,est_pin','RolePermissionService.assignActor; CursoVirtualService.docenteDeUsuario','Vínculo laboral activo; FK no indexa automáticamente el lado referente','SERVICE'),
 ('IDX-008','docente','cod_pin,est_doc','RolePermissionService.assignActor; CursoVirtualService.docenteDeUsuario','Perfil docente vigente; no forzar UNIQUE porque cardinalidad no se concilió','SERVICE'),
 ('IDX-009','gestion_academica','ani_gea','GestionAcademicaInteligente.existeAnioGestion/existeGestionAnteriorSinCerrar; Gestión Académica','Búsqueda/año y orden; no UNIQUE anual sin regla aprobada','SUPPORT_DIRECT'),
 ('IDX-010','plan_asignatura','cod_gea,cod_cur,cod_par,cod_tur,cod_asi,cod_doc','PlanAsignaturaInteligente.analizar; InstitutionalQueryService','Existe combinación de seis FK; candidato clave natural, solo index por ahora','SUPPORT_DIRECT')]
csvwrite(OUT/'bd-indices-recomendados.csv',[{'ID':id,'TABLE':t,'KEYS':cols,'QUERY_SOURCE':src,'JUSTIFICATION':why,'CONSUMER':use,'STATUS':'PROPUESTO; no creado ni medido; comparar índices y EXPLAIN aislado'} for id,t,cols,src,why,use in idx])
optimizations=[
 ('Q-001','InscripcionAcademica','Agrupar consulta de grupo/cupo/estado y cachear metadatos schema dentro de la operación; no cachear permisos o afiliación sin revalidar','IDX-001'),
 ('Q-002','GestionAcademicaInteligente','Reutilizar agregados por gestión y corregir aliases reporte/calificacion_estudiante/cod_gea por modelos reales y JOIN al plan; ausencia no es contador cero certificado','IDX-009'),
 ('Q-003','TurnoInteligente','Cargar bloques/plantillas una vez por operación; separar writer autorizado del preview, unificar bitácora y evitar generación concurrente por último código','Índices actuales de plantilla/bloque; medir antes de añadir'),
 ('Q-004','PersonaInteligente','Mantener límites 5/5/8; normalizar correo/CI; búsqueda LIKE con % inicial no mejora por B-tree simple; trigram solo con evidencia de costo','IDX-005'),
 ('Q-005','PlanAsignaturaInteligente','Existencia por combinación completa con índice apropiado, sin convertir coincidencia preventiva en garantía UNIQUE de política no aprobada','IDX-010'),
 ('Q-006','ProgresoCursoService y reportes','Agregación por estudiante y conjunto elegible, corregir mezcla de entregas y evitar cargar todo el histórico para un promedio; scope antes de agregado','Índices existentes de entrega/asistencia y IDX-003'),
 ('Q-007','OrientacionService','Último intento y conteo por edición congelada; obtener respuestas/preguntas de una edición, no catálogo mutable, y evitar recálculo finalizado','IDX-004'),
 ('Q-008','Consultas de curso/ownership','Eager loading y filtros correlacionados del mismo plan/grupo; agrupar counts manteniendo revocación y scopes, no KPIs globales de otro actor','IDX-001/002/007/008')]
csvwrite(OUT/'bd-consultas-a-optimizar.csv',[{'ID':id,'CONSUMER':who,'PROPOSAL':why,'INDEXES':index,'MEASURED':'NO; revisión estática, no consulta ni EXPLAIN'} for id,who,why,index in optimizations])

docindex=[]
for p in sorted(DOC.rglob('*.md')):
    if p.parent==OUT or p.name.startswith(('31-','34-','35-','36-')): continue
    s=read(p); docindex.append({'FILE':p.relative_to(ROOT).as_posix(),'SHA256':hashlib.sha256(p.read_bytes()).hexdigest(),'TITLE':next((l for l in s.splitlines() if l.startswith('#')),''),
       'SCHEMA_REFERENCES':join(re.findall(r'MIG-\d{3}[AB]?|BLOCKED_EXTERNALLY_DB|NO MIGRATE|NO COMMIT',s)),'INTERPRETATION':'Antecedente documental; estado de propuesta vigente en 31/35 y revisión superior de 25'})
csvwrite(OUT/'bd-documentacion-revisada.csv',docindex)

summary='''# SAVP-TIS3 — Auditoría maestra de base de datos

Fecha: 2026-10-01. Checkout `C:/laragon/www/savp-reestructuracion`, rama `feature/REESTRUCTURACION`, HEAD `2f9d5e8dc5a85983b244f32efcd30b14ebd4c16b`. **Fase documental y de diseño, detenida para revisión arquitectónica.** Código, migrations históricas/propuestas y schema no se modifican.

La arquitectura original sigue en `C:/Users/LOQ/.codex/worktrees/61ea/savp-reestructuracion/docs/arquitectura-maestra-savp`; 43 archivos, 105 ventanas e IDs originales. La matriz de implementación se conserva: 91 PARTIAL, 10 BLOCKED_EXTERNALLY_DB, 4 BLOCKED_EXTERNALLY_INSTITUTIONAL, 0 PASS. La auditoría añade un cruce de datos, no renumera ni sustituye aquella arquitectura.

## Fuentes, método y certeza

Se analizan 58 archivos de migration (52 históricos y seis propuestas), 55 Models, app/Support, Services, Policies, Livewire, controllers, rutas, vistas, tests, factories, seeders, config/bootstrap y documentación. La lectura y hashes están en [bd-lectura-fuentes.json](evidencia/bd-lectura-fuentes.json) y [bd-documentacion-revisada.csv](evidencia/bd-documentacion-revisada.csv). `AppServiceProvider` registra explícitamente migrations/aula_virtual. No se instancia ni evalúa una migration; los scripts parsean texto y balancean bloques. No bootstrap de Laravel ni conexión de BD para esta auditoría.

Se encontró `bd-savp-tis3.sql`, 64.193 bytes: snapshot DDL PostgreSQL 18.3, no schema institucional actual. Se excluyen bloques COPY y líneas INSERT; no se exportan filas ni datos personales a evidencias. Su hash queda en bd-estructura-estatica.json. No se importa ni se interpreta su ledger como evidencia de aplicación en otra base. Se comparan tablas/columnas y DDL; las diferencias son de archivos, no diagnóstico del deployment institucional.

Fuentes distinguidas: HISTORICA_DECLARADA, PROPUESTA_NO_EJECUTADA, SQL_SNAPSHOT_ONLY, y SOLO DISEÑO para MIG-007. Las condiciones hasTable/hasColumn de migrations históricas representan intención sobre un schema base completo; aplicación real desconocida. Spatie se resuelve con config local actual: teams=false, model_morph_key=cod_usu. No se cuentan ramas teams/testing inactivas como columnas reales.

Matrices de callers señalan referencias estáticas, incluidos imports, firmas y consultas. Un import no prueba lectura; un writer candidato con alias no demuestra que modifique cada tabla importada. Se incluye propietario objetivo aparte. FK, columns y Blueprint chains provienen de declaraciones; hechos científicos, uso runtime, duplicados reales y rendimiento no se certifican por regex. Llamadas dinámicas y framework indirecto se señalan; cero instrucciones Blueprint/DDL desconocidas quedaron pendientes en el parser tras revisar foreach, Schema::table arrow y dateTime. Cero desconocidas no equivale a certificación del schema vivo.

## Inventario y conciliación entre fuentes

| Fuente | Tablas | Columnas | FK | Índices/keys |
|---|---:|---:|---:|---|
| 52 migrations históricas | 64 | 597 | 96 | 64 PRIMARY + 30 UNIQUE + 151 INDEX = 245 declaraciones |
| Overlay de seis propuestas | 78 totales: 64 + 14 nuevas | 737: 597 + 140 nuevas/extensiones | 128 totales | 302 declaraciones totales; no creadas por esta auditoría |
| Volcado SQL histórico | 33 | Solo DDL en evidencia JSON | 28 | 9 CREATE INDEX explícitos + 42 PK/UNIQUE |
| Inventario unido | 79: overlay + ledger migrations exclusivo del SQL | 740: overlay + tres del ledger | No sumar fuentes duplicadas | Separar fuente al evaluar |

Checks históricos: 43 (incluidos enums traducidos a CHECK); overlay: 62. No se cuentan FK como índices implícitos del lado referente. PK/UNIQUE ya respaldan índices; no confundir 151 índices secundarios con 245 declaraciones de índice/key. Modelos locales: 55, no 55 tablas desplegadas; tabla notifications usa Model vendor y varias tablas framework no necesitan Model app. Ningún SoftDeletes local declarado.

El SQL coincide con 32 tablas del núcleo histórico y agrega ledger `migrations`; no contiene las otras 32 declaradas posteriores. Entre diferencias relevantes: users carece de est_usu/google/avatar/auth_provider/last_login del código actual, calificacion carece de cod_pas, y existen estructuras académicas/documentales/LMS posteriores ausentes. No se declara que tales migrations estén pendientes en la institución. [bd-schema-vs-sql.csv](evidencia/bd-schema-vs-sql.csv) conserva diferencia por tabla/columna; JSON conserva tipos y constraints de ambas fuentes.

Inventarios completos: [32-MATRIZ-TABLAS-BD.csv](32-MATRIZ-TABLAS-BD.csv), [33-MATRIZ-COLUMNAS-BD.csv](33-MATRIZ-COLUMNAS-BD.csv). Incluyen propósito, origen, fuente, lectores/writers, PK/FK, nulabilidad/default/tipos PostgreSQL, checks/índices, casts/fillable, historia, privacidad, estrategia de eliminación y recomendación. Las propuestas nuevas aparecen como propuestas, nunca como schema confirmado.

## Hallazgos de dominio y decisiones

Persona, cuenta, estudiante y vínculo laboral representan hechos distintos. CI UNIQUE actual solo por número requiere decisión respecto a complemento; correo contacto y email login no se unifican a ciegas. Personal.cargo no concede Role. Docente es una entidad necesaria por especialidad y referencia de planes; Role acredita permisos. Los hasOne de perfiles no coinciden con cardinalidad de schema sin UNIQUE, y la generación de códigos por último registro no es segura bajo todos los escritores concurrentes.

Gestión+grado+paralelo+turno define grupo anual. Curso catalogado no equivale a ClaseVirtual; PlanAsignatura es asignación docente y no unidad. Inscripción única por alumno/gestión ya existe: conservar trayectoria y regularizaciones. Horarios ya poseen estructura normalizada, XOR de planes y fechas de plantilla; validar contexto cruzado antes de nuevas FK. Período global no tiene fechas anuales; capacidad referencial no es cupo institucional configurado.

LMS conserva Tarea evaluable, publicaciones, materiales, entregas/archivos y calificación. actividad_clase es telemetría. Índices cod_tar/cod_est de entrega no justifican nuevas tablas; la migration de junio que elimina duplicados/hijos es el riesgo CRITICAL del bootstrap futuro. Nota oficial contextualizada y calificación LMS son distintas; pun_max al calificar preserva escala histórica. NOT VALID afecta también actualizaciones de filas históricas: MIG-006 se separa, no se aprueba por defecto. [PostgreSQL ALTER TABLE](https://www.postgresql.org/docs/18/sql-altertable.html).

Asistencia registra sesión/marca individual; se deriva porcentaje con estados configurados y población elegible. valor_porcentual de EstadoAsistencia persiste una ponderación oficial. UNIQUE con bloque NULL permite más de una sesión según semántica PostgreSQL; fijar identidad y política de rectificación antes de corregir. Las FK no crean automáticamente índices referentes y NULL en UNIQUE requiere regla explícita. [PostgreSQL Constraints](https://www.postgresql.org/docs/18/ddl-constraints.html).

Orientación local conserva preguntas/respuestas/resultados, pero no instrumento/algoritmo de edición. Añadir una versión de instrumento y enlaces nullable tiene necesidad concreta, MIG-007 SOLO DISEÑO. El reporte antiguo calcula compatibilidad 95/80/60/40 desde promedios y asigna letras RIASEC por especialidad: esa salida no es medición científica reproducible y no debe convertirse en dato oficial. Peter 3 permanece DTO con pseudónimo; no acceso DB ni tabla genérica automática.

Kardex conserva el concepto de SeguimientoAcademico y necesita hecho/autor/momento/contexto/visibilidad/revisión/evidencia. Cinco catálogos son ejes tipados; la alternativa genérica requiere discriminador y FK equivalentes. La versión común exige edición publicada consistente; no se aprueban niveles/sanciones por crear tablas. MIG-001 cambia para no obligar silenciosamente toda observación general a un plan y para separar tiempo del hecho/captura. Meta personal y evento institucional son hechos faltantes; avance, cupo disponible, campana unread y fechas de tarea se derivan.

Hay cinco writers legacy directos de Bitácora además de BitacoraService: Support Turno, GestiónCurso, GestiónAcadémica, GestiónTurnos y GestiónInscripciones. Se documenta unificación, sin modificar código en esta fase. ReportesGenerados y RespaldoGestionAcademica tienen propósitos de artefacto distintos; exportación SQL parcial no equivale a backup integral restaurable. Archivos: solo metadata/path/hash en BD, contenido privado en storage; hash no certifica autenticidad. Nadie obtiene permisos ni ejecuta CRUD por responder un Support.

## Datos derivables, estados y candidatos

Derivar nombres/edad/completitud/sugerencias/advertencias, promedio, porcentaje de asistencia, avance por estudiante, conteos, cupo disponible y progreso del instrumento. Persistir identidad, relaciones, valores configurados, decisiones humanas, notas/escala histórica, estados oficiales, marcas individuales, autor/fecha y revisiones. Los contadores de acceso son derivables únicamente si hay ledger completo; preservar mientras la telemetría es parcial. doc_com_ins puede mezclar completitud con verificación humana: revisar antes de retirar. RoleRequest.analysis_result es snapshot de aprobación justificable; no tabla de sugerencias por tecla.

Candidatos redundantes: calificacion.cod_asi cuando cod_pas existe, contexto cod_tar/cod_est de calificacion_tarea, cod_est repetido en respuestas/resultados de orientación, cod_esp global frente a inscripción, fotos de Persona/User y pro_ins frente a catálogo procedencia. No se retiran: algunos son snapshots o sostienen históricos sin contexto. users.current_team_id es candidato de retiro de columna por framework de equipos desactivado; requiere revisar consumidores. **Ninguna tabla tiene evidencia suficiente para retiro.** Material/TareaMaterial son CANDIDATO_FUSIÓN de infraestructura de archivo, no decisión de fusionar datos.

Taxonomía: CORE/IDENTIDAD/ACADÉMICO/LMS/EVALUACIÓN/ASISTENCIA/SEGUIMIENTO/KARDEX/ORIENTACIÓN/CONFIGURACIÓN/SEGURIDAD/AUDITORÍA/NOTIFICACIÓN/CALENDARIO/ANALÍTICA se aplican en matriz. LEGACY/REDUNDANTE/DERIVABLE/CANDIDATO_FUSIÓN/CANDIDATO_RETIRO describen datos y decisiones, no legitimación de borrado. NUEVO_REQUERIDO identifica hecho faltante. PETER3_RESULT es categoría FUTURO condicionada, cero tabla actual destinada a acceso directo de Peter 3.

## Queries, Support, índices y relaciones

22 clases Support, 19 inteligentes y tres utilidades según inventario previo; 10 clases tienen accesos directos al schema/BD en **98 sitios estáticos**, algunos de escritura. Las otras consumen inputs/colecciones/callers y no se fuerza una consulta DB para cada Support. TurnoInteligente es escritor actual de plantilla/bloque/bitácora: debe separarse de preview y pasar a Service autorizado. Las tablas/columnas/ventanas y métodos quedan en [bd-support-tablas-columnas.csv](evidencia/bd-support-tablas-columnas.csv) y support-inventario.csv.

Ocho grupos de consultas a optimizar, sin tiempo ni plan medido: [bd-consultas-a-optimizar.csv](evidencia/bd-consultas-a-optimizar.csv). Diez índices **propuestos** basados en filtros reales, cuatro con consumidor Support directo; ninguno se crea ni se certifica requerido por rendimiento antes de medir: [bd-indices-recomendados.csv](evidencia/bd-indices-recomendados.csv). No recomendar B-tree de nombre como solución a LIKE '%texto%'; trigram FUTURO solo con medición y autorización.

'''
summary+='| ID | Tabla / columnas | Consulta que lo justifica |\n|---|---|---|\n'
for id,t,cols,src,_,_ in idx: summary+=f'| {id} | `{t}({cols})` | {src} |\n'
summary+='''
Inventario de índices/keys ya declarados en [bd-indices-declarados.csv](evidencia/bd-indices-declarados.csv), siempre con fase/origen. En entrega hay INDEX(cod_tar,cod_est) y UNIQUE mismo orden: candidato redundante de índice, no de datos; medir y revisar otros usos antes de retiro. En unidad UNIQUE(id,cod_cla) permite FK compuesta y se conserva deliberadamente. UI reactive no implica aprobar una consulta por cada tecla; revalidación servidor al guardar sigue necesaria.

Relaciones completas, cardinalidad, owner, historia y DELETE/UPDATE: [34-RELACIONES-BD.md](34-RELACIONES-BD.md), bd-relaciones-cardinalidad.csv, bd-fks.csv y bd-modelos.csv. Service↔tabla↔owner↔ventana: bd-services-tablas.csv. Peter3↔origen/DTO/personal/agregado/persistencia/acceso directo: bd-peter3-data.csv. Las dependencias transitivas no prueban que todas las tablas se lean en cada acción.

## Impacto en las 105 ventanas

El cruce completo [bd-105-ventanas.csv](evidencia/bd-105-ventanas.csv) conserva ID, actor, ventana y estado actual. No actualiza PASS por preparar diseño.

Bloqueos DB completos actuales: V022, V035, V038, V055, V063, V064, V082, V083, V098 por seguimiento/Kardex; V102 por metas. El modelo objetivo puede resolver **la precondición de persistencia de esas 10**, solo tras aprobación, aplicación en ambiente autorizado, writers completos, RBAC y pruebas. Reglas/visibilidad/catálogos no se aprueban por schema. Bloqueos institucionales V024/V025/V036/V066 siguen pendientes; prevención/alertas además pueden requerir el seguimiento/aviso aprobado, no otra arquitectura.

Subfunciones parciales: V073/V090 unidades; V016/V042/V054/V067/V081/V096 eventos institucionales; campana de V001/V027/V044/V058/V070/V087 notifications; orientación reproducible V037/V099/V100 con MIG-007. Estas dependencias no bloquean la lectura de publicaciones/plazos actuales ni todas las acciones de sus ventanas. Sin acceso a PG, el PASS de flujos existentes también queda pendiente de validación real; eso no significa que necesiten tabla nueva.

## Propuestas, privacidad y verificación

[25-MIGRATIONS-PROPUESTAS.md](25-MIGRATIONS-PROPUESTAS.md) contiene dictamen vigente y conserva el antecedente íntegro; [35-MODELO-DATOS-OBJETIVO.md](35-MODELO-DATOS-OBJETIVO.md) es ADR propuesto, entidades/columnas nuevas, snapshots, frontera Peter 3 y plan aislado; [36-DEUDA-TECNICA-BD.md](36-DEUDA-TECNICA-BD.md) ordena 20 hallazgos: 1 CRITICAL, 10 HIGH, 7 MEDIUM, 2 LOW. No se convierten riesgos estáticos en incidentes confirmados.

Privacidad PUBLIC/INSTITUTIONAL/PERSONAL/SENSITIVE en columnas; notes/respuestas/credenciales/seguimiento/audit sensible. Retención y borrado legal no se inventan. Sesión/cache/token se expiran; hechos oficiales y revisiones se conservan por estado/append-only. No hay SoftDeletes global y un hook de Model no impide borrado vía SQL externo; permisos y writer deben hacer cumplir retención.

Pruebas previas registradas en 30: 191 PASS/582 assertions, 31 SKIP, 0 FAIL. No se repiten ni se venden como evidencia PostgreSQL de esta auditoría documental. Verificación de esta fase: parseo estático, integridad de CSV/JSON, igualdad IDs/estados del catálogo, coherencia PK/FK de propuestas, php -l de seis archivos existentes y hashes del código/52 históricas/flags/config/.env/locks/seeders. Detalle de verificación final en evidencia/bd-validacion.json y bd-preservacion-verificacion.json. Ninguna prueba inicia DB ni llama migrations.

## Cobertura de requisitos 1–75

| Requisitos | Evidencia |
|---|---|
| 1–13 fuentes, clasificación, callers, derivación | 31–33, JSON estático, CSV modelos/relaciones y comparación SQL |
| 14–15 Support/DB | bd-support-tablas-columnas.csv, queries/índices y hallazgo writers |
| 16–26 identidad/academia/inscripción/Regencia | 32–35 y hallazgos DB-002..006/012/015 |
| 27–33 LMS/unidades/entregas/asistencia/notas | 25, 32–35 y DB-001/007/013/014/016 |
| 34–36 Kardex/historia/catálogos | Dictamen MIG-001, 34–35 y DB-011 |
| 37–40 orientación/RIASEC/Peter 3 | MIG-007 SOLO DISEÑO, 35, bd-peter3-data.csv, DB-008/009 |
| 41–49 analítica/MV/calendario/notificación/meta/fuentes/tutor/archivos/bitácora | 35, dictamen MIG-003..005 y DB-010/017 |
| 50–59 índices/unique/FK/delete/estados/JSONB/normalización/snapshot/privacidad/P3 | 32–35 y CSV complementarios |
| 60–63 pruebas/schema histórico/revisión seis migrations/preservación | Plan futuro 35 y 25; baseline/validación, sin ejecución |
| 64–70 entregables obligatorios | 31–36 y 25 |
| 71–74 105 ventanas/Support/Services/P3 | Cuatro matrices cruzadas de evidencia |
| 75 resultado y detención | Bloques exactos siguientes |

==================================================
SAVP-TIS3 — AUDITORÍA MAESTRA DE BASE DE DATOS
==================================================

INVENTARIO

Tablas históricas declaradas: 64. Volcado histórico: 33, incluye migrations solo framework. Inventario unido observado/propuesto: 79; tablas objetivo nuevas 15, una de ellas SOLO DISEÑO fuera del overlay de archivos.
Modelos: 55.
Migrations históricas: 52; propuestas físicas anteriores: 6; nuevas físicas esta fase: 0.
Columnas históricas: 597; nuevas/extensiones en seis proposals: 140; matriz con ledger: 740.
Foreign keys: 96 históricas; 128 overlay hipotético.
Índices/keys históricos: 245 = 151 secundarios + 30 UNIQUE + 64 PRIMARY; overlay 302. No contar de nuevo los índices implícitos de PK/UNIQUE.

==================================================
CLASIFICACIÓN
==================================================

CONSERVAR: 10.
CONSERVAR_Y_MEJORAR: 44.
FUSIONAR: 0; candidato de infraestructura de archivos sin fusión de tablas aprobada.
NORMALIZAR: 2.
EXTENDER: 5.
LEGACY: 4 (LEGACY_CONSERVADO).
CANDIDATO_RETIRO: 0 tablas; current_team_id es candidato de columna, conservada.
NUEVAS NECESARIAS: 15 del objetivo = 14 NUEVO en inventario de archivos + una edición instrumento SOLO DISEÑO. Ninguna creada en PostgreSQL.

==================================================
MIGRATIONS PROPUESTAS
==================================================

MIG-001: APPROVE_WITH_CHANGES.
MIG-002: APPROVE_AS_IS.
MIG-003: APPROVE_WITH_CHANGES.
MIG-004: APPROVE_AS_IS.
MIG-005: APPROVE_WITH_CHANGES.
MIG-006: SPLIT.
MIG-007 adicional: PROPUESTA, NO_CREADA, edición de orientación; ocho paquetes futuros al separar MIG-006.

==================================================
REDUNDANCIAS
==================================================

Contextos cod_asi de nota/plan, cod_tar/cod_est de nota LMS/entrega, cod_est de orientación/intento, especialidad global/anual, fotografías con origen diferente y procedencia textual/catalogada: revisar, conservar. INDEX y UNIQUE entrega del mismo par: candidato de índice. No borrados ni migración de datos.

==================================================
DATOS DERIVABLES
==================================================

Promedios, asistencia %, avance LMS por estudiante, avance de respuestas, conteos, cupos disponibles, completitud, nombres/edad, sugerencias/advertencias. Snapshots y decisiones oficiales se conservan cuando son hechos históricos; valor_porcentual de estado asistencia es configuración persistente.

==================================================
ÍNDICES RECOMENDADOS
==================================================

10 propuestas IDX-001..010 en tabla superior y CSV; 4 con consultas Support directas. 0 creados, 0 medidos. PK/UNIQUE existentes se reutilizan; no índice por cada campo ni B-tree para LIKE de prefijo abierto.

==================================================
RIESGOS
==================================================

CRITICAL: 1.
HIGH: 10.
MEDIUM: 7.
LOW: 2.

==================================================
PETER 3
==================================================

Acceso directo PostgreSQL recomendado: NO.
Datos enviados por DTO: identificador pseudonimizado, período, notas/escalas, asistencia agregada, intereses declarados y especialidad mínima; pregunta/query en sus contratos. Sin CI/credenciales/identidad completa/Kardex.
Persistencia de resultados: local orientación existente versionada; PETER3_RESULT FUTURO solo si conservación científica está aprobada. Tutor NO_PERSIST; HISTORY_OPT_IN futuro. Sin tabla genérica nueva.

==================================================
SUPPORT
==================================================

Supports que consultan BD directamente: 10 de 22 clases; 98 sitios de acceso estáticos, incluidos algunos writers. 19 inteligentes + tres utilidades preservados.
Consultas a optimizar: 8 grupos.
Índices requeridos: 10 propuestos globales, 4 por Support directo; necesidad de rendimiento pendiente de medición, no certificada.
Regresiones: 0 archivos Support cambiados en esta fase; no nueva certificación runtime.

==================================================
105 VENTANAS
==================================================

Bloqueos DB actuales: V022/V035/V038/V055/V063/V064/V082/V083/V098/V102 (10).
Bloqueos que desaparecen con modelo objetivo: solo precondición persistencia de esos 10, tras aprobación, writers, RBAC y pruebas. No desaparece ninguno AHORA. Los cuatro institucionales permanecen; 91 PARTIAL permanecen.

==================================================
POSTGRESQL
==================================================

Modificado: NO.
Migrations ejecutadas: 0.
Seeders ejecutados: 0.
Base de testing creada/existente/aprobada: NO.
SQL ejecutado: NO.

==================================================
DECISIÓN
==================================================

MODELO ACTUAL APTO: CON CAMBIOS.
6 MIGRATIONS APTAS: 2/6 como diseño sin modificación; ninguna autorizada para ejecución.
REQUIEREN REDISEÑO: 4.
NUEVAS MIGRATIONS REALMENTE NECESARIAS: 8 paquetes propuestos para siete necesidades, no ocho archivos físicos; correcciones condicionadas de CI/cupos/períodos/Peter3 no se inventan en el número.
LISTO PARA CREAR BD DE TESTING: NO, falta revisión/autorización del plan y bootstrap histórico seguro.
LISTO PARA CONTINUAR IMPLEMENTACIÓN: NO en esta fase; detenerse para revisión arquitectónica.

NO EJECUTAR MIGRATIONS.
NO COMMIT.
NO PUSH.
DETENIDO PARA REVISIÓN ARQUITECTÓNICA SEGÚN LA SOLICITUD DEL USUARIO.
'''
write('31-AUDITORIA-BD-MAESTRA.md',summary)
