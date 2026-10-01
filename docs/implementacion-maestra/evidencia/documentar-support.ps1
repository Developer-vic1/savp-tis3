$ErrorActionPreference='Stop'
$root=(Resolve-Path (Join-Path $PSScriptRoot '../../..')).Path
Set-Location -LiteralPath $root
$docRoot=Join-Path $root 'docs/implementacion-maestra'
function WriteDoc([string]$name,[string[]]$lines) {
    [IO.File]::WriteAllText((Join-Path $docRoot $name), ($lines -join "`n")+"`n", [Text.UTF8Encoding]::new($false))
}
function Cell($text) { ([string]$text).Replace('|','/').Replace("`r",'').Replace("`n",' ') }
$inventory=@(Import-Csv (Join-Path $PSScriptRoot 'support-inventario.csv'))
$local=@($inventory | Where-Object TIPO -eq 'INTELIGENTE_LOCAL')
$original=@($local | Where-Object EXISTIA_EN_HEAD -eq 'True')
$lines=@('# Support inteligente local — preservación y conexiones','',
    'REUSE → PRESERVE → CONNECT → CORRECT → REFACTOR → EXTEND. Support conserva prevención, normalización, coincidencias y recomendaciones. Policies y Services conservan autorización, alcance y escritura. Peter 3 complementa esta capa; las reglas locales no dependen de Python, FastAPI, Internet ni sus flags.', '',
    "Inventario completo: $($inventory.Count) clases; $($local.Count) Supports inteligentes locales ($($original.Count) existentes en HEAD y KardexInteligente nuevo); 3 utilidades. Todas tienen referencias de producción. Caller significa referencia estática/importación/herencia; no certifica ejecución de cada rama en PostgreSQL.", '',
    'La comparación se realizó con git diff, git diff HEAD y git show HEAD:<ruta>. No se restauraron directorios ni se borraron Supports. Evidencia detallada, métodos completos, referencias directas/transitivas y hashes: evidencia/support-inventario.csv. La matriz de 105 conserva IDs originales y añade SUPPORT_ASOCIADO, SUPPORT_CONECTADO, SUGERENCIAS_UI y REGRESION_SUPPORT.', '',
    '| SUPPORT | RUTA | MÓDULO | MÉTODOS | BLOQUEOS | ADVERTENCIAS | SUGERENCIAS | COINCIDENCIAS | RESUMEN | ACCIÓN RECOMENDADA | VENTANA | CALLER ORIGINAL | CALLER ACTUAL | ESTADO | REGRESIÓN | ACCIÓN |',
    '|---|---|---|---|---|---|---|---|---|---|---|---|---|---|---|---|')
foreach($row in $inventory) {
    $reg=if($row.SUPPORT -eq 'DocenteInteligente'){'REG-SUP-001 corregida'}elseif($row.SUPPORT -eq 'PersonaInteligente'){'DEF-SUP-002 corregido; previo a esta fase'}else{'Sin pérdida de caller directo frente a HEAD'}
    $action=if($row.SUPPORT -eq 'KardexInteligente'){'EXTEND: borrador docente, sin persistencia ni sanciones'}elseif($row.SUPPORT -eq 'CalificacionInteligente'){'PRESERVE + CONNECT: formulario y GradeService contextual autorizado'}elseif($row.TIPO -eq 'UTILIDAD'){'Utilidad conservada; no se presenta como motor preventivo'}else{'PRESERVE; contratos originales y análisis de edición conservados'}
    $values=@($row.SUPPORT,$row.RUTA,$row.MODULO,$row.METODOS,$row.BLOQUEOS,$row.ADVERTENCIAS,$row.SUGERENCIAS,$row.COINCIDENCIAS,$row.RESUMEN,$row.ACCION_RECOMENDADA,$row.VENTANAS,$row.CALLER_ORIGINAL,$row.CALLER_ACTUAL,$row.ESTADO,$reg,$action)
    $lines+='| '+(($values | ForEach-Object {Cell $_}) -join ' | ')+' |'
}
$lines+=@('', '## Lectura de contratos y alcance','',
    'Las columnas de capacidades identifican familias de salida y reglas presentes, no un conteo de mensajes ni una autorización. Asignatura, Curso y Paralelo bloquean mediante valido/puede_crear/estado aunque no siempre devuelven una clave bloqueos. CatalogoInteligenteBase aporta normalización, similitud y completitud a los catálogos; no se instancia solo. ReporteAcademicoInteligente clasifica y sugiere orientación local, sin resultado científico externo.', '',
    'Persona conserva identidad, contacto, dirección, edad, duplicidad, vinculaciones, coincidencias, riesgo, completitud y acción recomendada. En edición se excluye solamente el cod_per seleccionado por el servidor; nunca se borran todos los bloqueos. La búsqueda de coincidencias se comparte en el análisis, evitando repetirla. Reactivar una persona sigue siendo una acción autorizada y registrada en Bitácora; la sugerencia no la ejecuta.', '',
    'Los callers de Persona son administración/Secretaría con Registro_Personas, reautorizados por PersonaPolicy y el hook institucional. Se deniega la asistencia administrativa a Estudiante, Docente, Regente y Director. Los Supports de catálogo solo se invocan dentro de sus módulos autorizados. El análisis de Calificacion en GradeService ocurre después de comprobar el plan, inscripción, docente/administrador y rectificación; no expone coincidencias de planes ajenos.', '',
    'Las ayudas originales de Personas, Asignaturas, Cursos, Paralelos, Turnos, Inscripción y planificación de Gestión se mantienen. Secretaría conserva Personas, inscripción, procedencia, vinculación y Paralelos; Cursos y Turnos son consultas mínimas de lectura conforme a V051/V053. No se conceden permisos adicionales para mostrar ayudas.', '',
    'CalificacionInteligente usa la escala oficial 0..100; no se aplica directamente a puntajes LMS con máximo variable. Esa reutilización requiere adaptación explícita de escala; EntregaService conserva sus validaciones 0..máximo. TeacherGradeForm lo conecta a creación y revisión docente con debounce de 500 ms, completitud, bloqueos, desempeño y aplicación explícita de observación sugerida. GradeService verifica actor, permiso, curso propio e inscripción correlacionada antes de consultar duplicidad; la escritura vuelve a comprobar contexto y período. La nota a revisar se busca dentro del plan autorizado y conserva estudiante/período; solo ese ID propio se excluye. Curso, análisis, ID de nota y selección administrativa de edición son Locked. Cancelar restablece creación. La UI docente no llena automáticamente observaciones; los endpoints y guardado manual anteriores se conservan. No se duplica un catálogo ni se reduce el Support a reglas Laravel.', '',
    'KardexInteligente reutiliza CatalogoInteligenteBase. Sugiere contexto y acompañamiento, avisa sobre descripción breve y coincidencias que entregue un caller autorizado. Su borrador Livewire no consulta eventos institucionales ni conserva texto. La comparación de recurrencia productiva requiere repositorio habilitado y scope autorizado; no se simulan eventos. Nunca determina sanción, medida o nivel institucional.', '',
    'UI común: components/asistencia-inteligente.blade.php, usada por Calificaciones, catálogos y borrador Kardex. Distingue bloqueos danger, advertencias warning, sugerencias info, coincidencias y completitud. Se conservan los paneles ricos originales de Persona y otros módulos. Usa clases SAVP y --ui-*; ningún CSS/framework nuevo. Inputs existentes conservan debounce 300–500 ms; Kardex usa 500 ms.', '',
    'Los errores técnicos no se convierten en coincidencias vacías ni en permiso para guardar. Bloqueos críticos de identidad/integridad conservan su efecto; no se registra cada sugerencia en Bitácora. Falta validar contra datos aislados y hacer QA autenticada en light/dark y móvil; estos límites no se presentan como regresiones ya verificadas.', '',
    '## Migraciones y Support','',
    'Se revisaron los seis archivos sin ejecutar up/down. MIG-001/003/004/005 crean estructuras propuestas; MIG-002 agrega unidades y claves nullable, preservando contenidos existentes; MIG-006 agrega CHECK NOT VALID e índice y sustituye la FK de notas para impedir borrado en cascada. No elimina columnas útiles ni reemplaza Supports. Los down contienen eliminaciones exclusivamente de estructura propuesta y guardias ante datos; MIG-006 restituye la FK original, riesgo documentado para Peter 1. Nada autoriza ejecutarlos.', '',
    'No se creó tabla de sugerencias ni otra migration. Kardex preventivo y metas en borrador funcionan sin persistencia nueva. La escritura real, catálogos institucionales, histórico, concurrencia y QA con PostgreSQL continúan pendientes donde corresponde.')
WriteDoc '28-SUPPORT-INTELIGENTE.md' $lines
$regressions=@('# Regresiones y defectos preventivos revisados','',
    'La clasificación distingue pérdida comprobada frente a HEAD de defectos que ya existían. No se atribuye al trabajo actual una eliminación que el diff no demuestra.', '',
    '| ID / prioridad | Support | Comportamiento original / caller | Cambio o defecto | Efecto | Corrección aplicada | Test | Estado |',
    '|---|---|---|---|---|---|---|---|',
    '| REG-SUP-001 / P1 | DocenteInteligente | analizarEspecialidad devolvía completitud 100/30; GestionDocente y su vista la consumían | El diff anterior reemplazó completitud por estado_especialidad y la vista eliminó el porcentaje | Contrato y asistencia visual degradados | Se reincorporó completitud y porcentaje; se conserva el nuevo estado adicional y se aclara que es un indicador preventivo | SupportPreventiveTest::test_docente_preserves_original_completeness_and_normalization | CORREGIDA |',
    '| DEF-SUP-002 / P0 | PersonaInteligente / GestionPersonas | Identidad, fecha futura y duplicidad generaban bloqueos; caller de edición ya contenía bypass en HEAD | Si la persona principal coincidía con el ID editado, se borraba todo bloqueos y se permitía guardar; ID editable dentro de formEditar | Un bloqueo distinto de duplicidad podía desaparecer y otra coincidencia quedar ocultada | Excluir solo el ID propio en todas las consultas, identidad Locked del servidor, conservar bloqueos; Policy y revisión final antes de validación/persistencia | SupportPreventiveTest::test_persona_edit_excludes_self_in_each_query_and_bounds_results; SupportLivewireIntegrationTest::test_edit_preserves_non_duplicate_blockers_and_uses_server_identity | CORREGIDO |',
    '| DEF-SUP-003 / P1 | GestionAcademicaInteligente / GestionAcademica | sugerirPeriodosEvaluacion es una ayuda local, no fechas persistidas por período | getPeriodosProperty mezclaba sugerencias con filas del catálogo global | Fechas estimadas/progreso se mostraban como registros reales | Catálogo muestra fechas no registradas; sugerencias originales permanecen en planificación | AcademicPeriodPresentationTest (2 casos); SupportPreventiveTest::test_gestion_invalid_range_blocks_while_suggestions_remain_distinct | CORREGIDO |', '',
    'Regresiones funcionales conocidas frente a HEAD: 1 detectada, 1 corregida, 0 abiertas. Defectos previos adicionales: 2 detectados y corregidos. P0 abiertas: 0; P1 abiertas: 0; P2 abiertas detectadas: 0, dentro del alcance de comparación/revisión documentado. QA visual y prueba con PostgreSQL siguen pendientes y no permiten afirmar ausencia universal de defectos.', '',
    'No se detectó eliminación de archivos Support originales ni pérdida de caller directo. No se hizo git restore/reset/clean/stash ni reemplazo masivo de carpetas. Workspaces, Services, Policies, scopes, rutas, mejoras LMS, tema y seis migrations propuestas permanecen.', '',
    '## Pruebas de regresión — subconjunto, no suma al total','',
    'Subconjunto de 8 casos: SupportPreventiveTest::test_docente_preserves_original_completeness_and_normalization y ::test_persona_edit_excludes_self_in_each_query_and_bounds_results; los cuatro primeros casos de SupportLivewireIntegrationTest (fecha futura, duplicado, sugerencia, edición); los dos casos de AcademicPeriodPresentationTest. Los tests usan dobles en la frontera de consultas/transacción; no acreditan escrituras reales. No se suman otra vez al total de PHPUnit.', '',
    '## Continuación del cierre de PARTIALS','',
    'Después de preservar Support se corrigió la escritura pedagógica expuesta a Secretaría en V051/V053; se mantuvieron las URLs en solo lectura. V051 incorpora nivel registrado, paralelo/turno/gestión correlacionados en un mismo plan y drawer mínimo; V053 estado, franja y drawer de turno/plantillas. Consultas institucionales usan filtros Livewire, contexto en URL, paginación de 20 y dependencias que se limpian al cambiar gestión. Los drawers reautorizan apertura/render, muestran empty y datos limitados, y preparan Escape/foco/teclado. V079 incorpora Support en la creación docente de notas oficiales, separada de notas LMS; tests rechazan estudiante ajeno antes de Support. Las metas V102 permiten validar borrador sin guardar, con estados de creación restringidos y errores que desaparecen al corregir; la persistencia sigue bloqueada por MIG-004. La campana presenta un fallback comprensible sin badge inventado. Ninguno de estos cambios certifica por sí solo PASS integral de la ventana.')
WriteDoc '29-REGRESIONES-SUPPORT.md' $regressions

[xml]$before=Get-Content (Join-Path $PSScriptRoot 'cierre-tests.xml')
[xml]$after=Get-Content (Join-Path $PSScriptRoot 'support-full-tests.xml')
$nowCases=@($after.SelectNodes('//testcase'))
$skippedLines=@('# Tests omitidos — revisión individual de los 45 originales','',
    'Fuente anterior: evidencia/cierre-tests.xml (45 SKIP). Fuente actual: evidencia/support-full-tests.xml. La guardia verifica SQLite :memory: antes de traits y deniega configuración institucional; PDO SQLite no se instaló ni habilitó. No existe PostgreSQL aislado aprobado. Ninguna prueba habilita migrations.', '',
    'Se reescribieron nueve casos de WorkspaceAuthorizationTest con identidades mock, cubriendo revocación de permisos en seis actores y rutas ajenas. Los redirects ya están cubiertos en WorkspaceHttpBoundaryTest. AuthenticationTest prueba el GET login sin BD; las credenciales originales se conservaron en AuthenticationPersistenceTest. RegistrationTest y FortifyScreenTest separan registro, recuperación y confirmación de contraseña de sus operaciones persistentes; el caso alternativo de registro deshabilitado mantiene SKIP porque la feature está habilitada. Ninguna prueba de pantalla certifica un token válido ni un POST. No se aumenta el conteo con pruebas duplicadas triviales.', '',
    '| Archivo / test original | Motivo original | Dependencia | ¿PostgreSQL? | ¿Sin BD? | Prioridad | Estado actual / acción |',
    '|---|---|---|---|---|---|---|')
foreach($case in @($before.SelectNodes('//testcase[skipped]'))) {
    $file=$case.file.Replace($root+'\','').Replace('\','/')
    $class=($case.class -split '\\')[-1]
    $match=@($nowCases | Where-Object {$_.name -eq $case.name})
    $without='PARCIAL: pantalla/hook con identidad mock; persistencia requiere entorno'
    $dependency='User/Persona y relaciones o credenciales del flujo'
    $needs='SÍ para certificar persistencia real; no autoriza conexión institucional'
    $priority='P1'
    $status='SKIP; separar consulta de pantalla de operación persistente cuando corresponda'
    if($class -eq 'WorkspaceAuthorizationTest') {$without='SÍ';$dependency='Middleware/actor/permiso simulado';$needs='NO para HTTP de autorización';$status='PASS; nueve casos reescritos sin RefreshDatabase'}
    elseif($class -eq 'AuthenticationTest' -and $case.name -eq 'test_login_screen_can_be_rendered'){$without='SÍ';$needs='NO';$dependency='GET /login y Blade';$status='PASS; pantalla separada de los dos tests de credenciales'}
    elseif($class -eq 'AcademicContextTest'){$without='NO para FK, unicidad, histórico o transacciones reales';$dependency='Schema/contexto, asignación, inscripción y nota por gestión';$status='SKIP; necesita sustituir DDL SQLite de fixture por schema PostgreSQL aprobado'}
    elseif($class -match 'ApiToken'){$dependency='Sanctum/Jetstream y User';$priority='P2';$status='SKIP; verificar feature API además de persistencia antes de ejecutar'}
    elseif($class -eq 'RegistrationTest'){$dependency='Feature de registro Fortify; creación User solo en último caso';$priority='P2';$needs='NO para pantallas; SÍ para creación de cuenta';$without='SÍ para pantallas, conservando condición feature';$status='SKIP; creación permanece en RegistrationPersistenceTest; caso alternativo requiere feature deshabilitada'}
    elseif($case.name -match 'screen|current_profile'){$needs='NO para pantalla con mock; SÍ para el flujo de tokens/datos';$priority='P2'}
    if($match.Count -eq 1 -and !$match[0].SelectSingleNode('skipped') -and !$match[0].SelectSingleNode('failure') -and !$match[0].SelectSingleNode('error')) {
        $status='PASS; '+(($match[0].class -split '\\')[-1])+' sin persistencia';$without='SÍ';$needs='NO para este contrato probado'
    }
    if($match.Count -eq 1 -and $match[0].SelectSingleNode('skipped')) { $status+='; motivo actual: '+$match[0].SelectSingleNode('skipped').InnerText.Trim() }
    $skippedLines+='| '+((@("$file / $($case.name)",'Guardia DB original: falta PDO SQLite, sin usar PostgreSQL',$dependency,$needs,$without,$priority,$status) | ForEach-Object {Cell $_}) -join ' | ')+' |'
}
$skippedLines+=@('', '## Estrategia PostgreSQL futura','',
    'Peter 1 debe aprobar una instancia/base aislada, usuario exclusivo y snapshot/schema autorizado. Antes de cualquier fixture, comprobar host/base/usuario y prohibir el destino institucional. Revisar las 52 migrations previas y la eliminación de duplicados de entrega en la migration histórica; no hacer migrate:fresh como preparación automática. Reescribir factories User/Persona con claves cod_usu/cod_per y seis roles canónicos; ejecutar grupos por scope/FK/concurrencia/histórico. Los tests de autenticación/Jetstream heredados no certifican compatibilidad PostgreSQL por haber sido omitidos. Credenciales se proporcionarán de forma controlada fuera de estos documentos.')
WriteDoc '26-TESTS-SKIPPED.md' $skippedLines
$audit=Get-Content (Join-Path $PSScriptRoot 'cierre-npm-audit.json') -Raw | ConvertFrom-Json
$lock=Get-Content package-lock.json -Raw | ConvertFrom-Json -AsHashtable
$npmLines=@('# Auditoría npm — sin modificar dependencias','',
    'Fuente: evidencia/cierre-npm-audit.json, obtenida con npm audit --json sobre el lock actual. 11 paquetes afectados: 1 low, 2 moderate, 6 high y 2 critical. Audit y build no equivalen a explotar todas las vulnerabilidades en el producto; varias afectan tooling Node. No se ejecutó npm audit fix/--force ni se modificó el lock. Los avisos y las versiones candidatas deben confirmarse antes de una actualización futura.', '',
    '| Paquete | Versión bloqueada | Severidad | Directa/transitiva | Fix informado | Riesgo de actualización | Aviso primario |',
    '|---|---|---|---|---|---|---|')
foreach($entry in $audit.vulnerabilities.PSObject.Properties) {
    $v=$entry.Value
    $node=$lock.packages['node_modules/'+$entry.Name]
    $version=if($node){$node.version}else{'Ver árbol del lock'}
    $fix=if($v.fixAvailable -is [bool]){if($v.fixAvailable){'Disponible; audit no fija aquí versión candidata'}else{'Sin fix informado'}}else{"$($v.fixAvailable.name) $($v.fixAvailable.version); SemVer major: $($v.fixAvailable.isSemVerMajor)"}
    $risk=if($entry.Name -eq 'axios'){'Verificar adapter HTTP, cabeceras y uso de navegador/Node'}elseif($entry.Name -in @('vite','postcss','browserslist','baseline-browser-mapping','postcss-selector-parser','nanoid')){'Revisar build, CSS, plugins y compatibilidad Node; cambios transitivos requieren build completo'}elseif($entry.Name -in @('concurrently','shell-quote')){'Revisar scripts dev y escapado de comandos; riesgo en tooling, no ejecutar entradas arbitrarias'}else{'Verificar redirecciones/multipart y árbol consumidor; actualizar transitivas a través de su dependencia'}
    $advisory=@($v.via | Where-Object {$_ -isnot [string] -and $_.url} | Select-Object -First 1)
    $link=if($advisory.Count){'['+(Cell $advisory[0].title)+']('+ $advisory[0].url +')'}else{'Severidad propagada desde '+(($v.via | ForEach-Object {if($_ -is [string]){$_}else{$_.name}}) -join ', ')}
    $npmLines+='| '+((@($entry.Name,$version,$v.severity,$(if($v.isDirect){'DIRECTA'}else{'TRANSITIVA'}),$fix,$risk,$link) | ForEach-Object {Cell $_}) -join ' | ')+' |'
}
$npmLines+=@('', 'Una corrección futura debe ser selectiva, con versiones y diff concretos, revisión de cambios incompatibles, npm ci y npm run build. No aplicar --force de forma automática. Se mantiene la evidencia de los once paquetes y todas sus cadenas/advisories en el JSON; la tabla muestra un aviso representativo por paquete.')
WriteDoc '27-NPM-VULNERABILITIES.md' $npmLines
