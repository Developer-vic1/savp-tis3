$ErrorActionPreference = 'Stop'
$root = 'C:/laragon/www/savp-reestructuracion'
$doc = Join-Path $root 'docs/implementacion-maestra'
$source = Import-Csv (Join-Path $doc 'evidencia/catalogo-original-105.csv')
$rows = Import-Csv (Join-Path $doc 'MATRIZ-CONCILIACION-105.csv')
if ($source.Count -ne 105 -or $rows.Count -ne 105) { throw 'Se requieren 105 filas originales y 105 conciliadas.' }
$byId = @{}
foreach ($row in $rows) { $byId[$row.'ID ORIGINAL'] = $row }
function Ref([string]$path) { return "$root/$path" }
function Locate([string]$id, [string]$route, [string]$file, [string]$view, [string]$service, [string]$policy, [string]$note) {
    $row = $byId[$id]
    $row.'RUTA ACTUAL ADAPTADA' = $route
    $row.'ARCHIVO ACTUAL' = Ref $file
    $row.'LIVEWIRE/CONTROLLER' = Ref $file
    $row.VISTA = Ref $view
    $row.SERVICE = Ref $service
    $row.POLICY = $policy
    $row.'OBSERVACIONES' = $note
    $row.'NO ENCONTRADA' = 'NO'
    $row.'CLASIFICACIÓN' = 'REUTILIZADA / CONECTADA'
    $row.REUTILIZADA = 'SÍ'
    $row.REFACTORIZADA = 'SÍ'
    $row.'FUSIONADA COMO TAB' = 'NO'
}
foreach ($original in $source) {
    $row = $byId[$original.id]
    if (!$row -or $row.ACTOR -cne $original.actor -or $row.'VENTANA ORIGINAL' -cne $original.nombre -or $row.'RUTA PROPUESTA' -cne $original.ruta_objetivo -or $row.'TIPO ORIGINAL' -cne $original.modales) { throw "Origen alterado: $($original.id)" }
    $row.'ESTADO ACTUAL' = 'PARTIAL'
    $row.BLOQUEADA = 'NO'
    $row.IMPLEMENTADA = 'PARCIAL; NO CERTIFICADA'
    $row.POLICY = 'EnsureActorRole + permiso de módulo; InstitutionalAuthorization en CRUD Livewire reutilizado; ver revisión específica'
    $row.OBSERVACIONES = 'Código reutilizado/localizado; falta certificar acciones y modalidades del catálogo en entorno aislado y UX autenticada. No se considera PASS por tener ruta.'
    foreach ($pair in @{
        'DATOS ORIGINALES'=$original.datos; 'ACCIONES ORIGINALES'=$original.acciones; 'FILTROS ORIGINALES'=$original.filtros;
        'SCOPE ACTUAL'=$original.scope; 'DATOS REALES'='Consultas del modelo existente, sin filas simuladas en producción; resultados no verificados en BD';
        'DARK MODE'='Tokens existentes en shell y componentes nuevos; contraste autenticado pendiente';
        'RESPONSIVE'='Shell móvil y overflow de tablas preparados; QA 375/768/1440 pendiente';
        'LOADING'='Pendiente revisión por acción'; 'EMPTY'='Pendiente revisión por componente'; 'ERROR'='Pendiente revisión de recuperación';
        'VALIDACIÓN'='Servidor en código; persistencia y concurrencia sin verificar';
        'TEST'='WorkspaceHttpBoundary/WorkspaceIntegrity: contratos transversales; no cobertura integral de esta ventana';
        'STATUS'='PARTIAL'; 'BLOCKER'='QA funcional y visual pendiente; no implica necesidad de schema nuevo';
        'MIGRATIONS'='NINGUNA NUEVA'; 'PENDIENTE INTERNO'='Auditar flujo/modalidad completa del catálogo y uniformar legacy donde corresponda'
    }.GetEnumerator()) { $row | Add-Member -NotePropertyName $pair.Key -NotePropertyValue $pair.Value -Force }
}
$calendars = @{'V016'='admin'; 'V042'='direccion'; 'V054'='secretaria'; 'V067'='regencia'; 'V081'='docente'; 'V096'='estudiante'}
foreach ($id in $calendars.Keys) {
    Locate $id "/$($calendars[$id])/calendario" 'app/Http/Controllers/CalendarController.php' 'resources/views/workspaces/calendario.blade.php' 'app/Services/CalendarService.php' 'Actor+CalendarService::PERMISSIONS+scope académico' 'Calendario real de plazos de tareas; consulta de eventos institucionales preparada y cerrada por flag. No hay eventos ficticios. Gestión editorial pendiente.'
    $byId[$id].MIGRATIONS = 'MIG-005 para eventos institucionales; plazos existentes no requieren migration'
    $byId[$id].'PENDIENTE INTERNO' = 'CRUD editorial institucional, reglas de solapamiento y QA; no bloquear calendario de tareas por ello'
}
Locate 'V023' '/admin/consultas/lms' 'app/Http/Controllers/InstitutionalQueryController.php' 'resources/views/workspaces/consulta.blade.php' 'app/Services/InstitutionalQueryService.php' 'Administrador+cursos.ver.institucional' 'Supervisión LMS consulta clases y conteos reales; no duplica aula docente.'
Locate 'V029' '/direccion/consultas/gestion' 'app/Http/Controllers/InstitutionalQueryController.php' 'resources/views/workspaces/consulta.blade.php' 'app/Services/InstitutionalQueryService.php' 'Director+Gestion_Academica' 'Consulta de gestiones reales; no concede edición administrativa.'
Locate 'V048' '/secretaria/documentacion' 'app/Http/Controllers/EnrollmentDocumentController.php' 'resources/views/workspaces/documentacion.blade.php' 'app/Support/PrivateFilePath.php' 'DocumentoInscripcionEstudiantePolicy+InscripcionEstudiantePolicy' 'Reutiliza expediente/documento de inscripción. Nuevos uploads privados, descarga autorizada con nosniff; históricos públicos no se movieron.'
Locate 'V056' '/secretaria/reportes' 'app/Http/Controllers/HistoricalReportController.php' 'resources/views/workspaces/reportes.blade.php' 'app/Services/HistoricalReportAccessService.php' 'ReporteGeneradoPolicy: familia administrativa PDF autorizada' 'Listado y descarga de reportes administrativos generados; no SQL/ZIP ni familia académica por permiso amplio.'
Locate 'V059' '/regencia/mis-grados' 'app/Http/Controllers/RegencyAssignmentsController.php' 'resources/views/workspaces/mis-grados.blade.php' 'app/Services/RegencyAccessService.php' 'Regente+cursos.ver.institucional+asignación propia activa' 'Consulta gestión/grado de asignaciones explícitas; no usar cursos globales como fallback. Estructura ya declarada en migration de 2026-09-28.'
Locate 'V068' '/regencia/reportes' 'app/Http/Controllers/RegencyReportController.php' 'resources/views/workspaces/reportes-regencia.blade.php' 'app/Services/RegencyReportService.php' 'Regente+reportes.ver.institucional+cursos.ver.institucional+scope gestión/grado; notas exigen calificaciones.ver.institucional' 'Consulta paginada/PDF privado por plan asignado. Inscripciones correlacionan cuatro dimensiones; notas sin cod_pas no se infieren. No precisa tabla nueva.'
$byId['V068'].TEST = 'RegencyReportScopeTest: 3 contratos sin BD; PDF Regente y persistencia/UX pendientes'
$domains = @{
    'V022'=@('admin','kardex-parametros','MIG-001','BLOCKED_EXTERNALLY_DB'); 'V024'=@('admin','lms-configuracion','NINGUNA: definición pendiente','BLOCKED_EXTERNALLY_PETER1');
    'V025'=@('admin','configuracion','NINGUNA: definición pendiente','BLOCKED_EXTERNALLY_PETER1'); 'V035'=@('direccion','kardex','MIG-001','BLOCKED_EXTERNALLY_DB');
    'V036'=@('direccion','prevencion','MIG-001/MIG-003 tras reglas aprobadas','BLOCKED_EXTERNALLY_PETER1'); 'V038'=@('direccion','seguimientos','MIG-001','BLOCKED_EXTERNALLY_DB');
    'V055'=@('secretaria','kardex','MIG-001','BLOCKED_EXTERNALLY_DB'); 'V063'=@('regencia','kardex','MIG-001','BLOCKED_EXTERNALLY_DB');
    'V064'=@('regencia','seguimientos','MIG-001','BLOCKED_EXTERNALLY_DB'); 'V066'=@('regencia','alertas','MIG-001/MIG-003 tras reglas aprobadas','BLOCKED_EXTERNALLY_PETER1');
    'V082'=@('docente','kardex','MIG-001','BLOCKED_EXTERNALLY_DB'); 'V083'=@('docente','seguimientos','MIG-001','BLOCKED_EXTERNALLY_DB');
    'V098'=@('estudiante','seguimientos','MIG-001','BLOCKED_EXTERNALLY_DB')
}
foreach ($id in $domains.Keys) {
    $d=$domains[$id]
    Locate $id "/$($d[0])/$($d[1])" 'app/Http/Controllers/DomainBoundaryController.php' 'resources/views/workspaces/domain-boundary.blade.php' 'app/Services/DomainReadinessService.php' 'Actor+permiso mínimo; KardexPolicy para repositorio; readiness no consulta expedientes' 'Ruta de condiciones y bloqueo preparada; no es CRUD ni timeline terminado. Repositorio Kardex scoped futuro; escritura cerrada. No se inventaron catálogos.'
    $row=$byId[$id]; $row.'ESTADO ACTUAL'=$d[3]; $row.STATUS=$d[3]; $row.BLOQUEADA='SÍ'; $row.IMPLEMENTADA='NO; FRONTERA PREPARADA'; $row.MIGRATIONS=$d[2]
    $row.'CLASIFICACIÓN'='FRONTERA PREPARADA; DOMINIO BLOQUEADO'; $row.REUTILIZADA='NO'
    $row.BLOCKER='Aplicación de schema/catálogos y aprobación de reglas/visibilidad, sin testing autorizado'
    $row.'PENDIENTE INTERNO'='Kardex: escrituras, revisiones, evidencias/descarga, forms y timeline; configuración/prevención: desarrollo tras contrato aprobado'
    $row.TEST='KardexBoundary/PreparedPersistenceBoundary: cierre y scopes; sin pruebas CRUD/Persistencia'
    $row.'DATOS REALES'='NINGÚN EXPEDIENTE CONSULTADO; solo descripción de disponibilidad'
}
$byId['V102'] | ForEach-Object {
    $_.'ARCHIVO ACTUAL'=Ref 'app/Livewire/Shared/AcademicPlan.php'; $_.'LIVEWIRE/CONTROLLER'=Ref 'app/Livewire/Shared/AcademicPlan.php'; $_.VISTA=Ref 'resources/views/livewire/shared/academic-plan.blade.php'; $_.SERVICE=Ref 'app/Services/AcademicGoalService.php'; $_.POLICY='MetaAcademicaPolicy: Estudiante propio+Perfil_Academico'; $_.'ESTADO ACTUAL'='BLOCKED_EXTERNALLY_DB'; $_.STATUS='BLOCKED_EXTERNALLY_DB'; $_.BLOQUEADA='SÍ'; $_.IMPLEMENTADA='PREPARADA; NO HABILITADA'; $_.MIGRATIONS='MIG-004'; $_.BLOCKER='Persistencia nueva, estados/revisiones y pruebas PG autorizadas'; $_.'PENDIENTE INTERNO'='Probar concurrencia/transiciones y UX con datos aislados'; $_.OBSERVACIONES='Formulario/listado/edición con motivo, ID Locked y revisiones preparados; flag false evita consultar tabla y guardar.'
    $_.'CLASIFICACIÓN'='PREPARADA; PERSISTENCIA NO HABILITADA'; $_.REFACTORIZADA='SÍ'
}
foreach ($id in @('V073','V090')) { $byId[$id].MIGRATIONS='MIG-002 para unidades; publicaciones existentes reutilizadas'; $byId[$id].OBSERVACIONES='Contenido publicado existente funcional en código; consulta de unidades preparada. Edición/orden/enlace de recursos a unidades pendientes y requiere schema autorizado.'; $byId[$id].'PENDIENTE INTERNO'='Editor, reordenamiento transaccional, visibilidad de recursos de unidades y QA' }
foreach ($id in @('V001','V027','V044','V058','V070','V087')) { $byId[$id].MIGRATIONS='MIG-003 solo campana persistente'; $byId[$id].OBSERVACIONES='Dashboard y enlaces por permiso con datos reales. Campana/listado/leído preparados, cerrados por flag; productores académicos pendientes. No se inventa badge 0.' }
foreach ($id in @('V074','V091')) { $byId[$id].POLICY='AulaVirtualMaterialPolicy+CourseVirtualService: clase propia, permiso, estado y archivos privados'; $byId[$id].OBSERVACIONES='Materiales reutilizados; transacción, validación y descarga privada. Unidades nuevas opcionales requieren MIG-002.' }
foreach ($id in @('V075','V076','V092')) { $byId[$id].POLICY='AulaVirtualTareaPolicy+CursoVirtualService'; $byId[$id].OBSERVACIONES='Actividades reutilizan tip_tar de Tarea; creación/edición contextual y estados; no otra tabla de actividades.' }
foreach ($id in @('V077','V079','V093','V094')) { $byId[$id].POLICY='AulaVirtualEntregaPolicy+AulaVirtualTareaPolicy'; $byId[$id].OBSERVACIONES='Entregas propias/en clase autorizada; revisar/retroalimentar/calificar y devolver son permisos separados. Puntaje LMS no sobrescribe nota oficial.'; $byId[$id].TEST='SubmissionAuthorization/AcademicSecurity: revocación/ownership; persistencia y UX pendientes' }
foreach ($id in @('V078','V095')) { $byId[$id].POLICY='AulaVirtualAsistenciaPolicy+CursoVirtualService'; $byId[$id].OBSERVACIONES='Sesiones/asistencia existentes, alcance por clase y alumno, locks para cerrar/editar; no se infiere Kardex de ausencias.' }
foreach ($id in @('V017','V030','V078','V079','V094','V095','V097','V099')) { $byId[$id].MIGRATIONS += '; MIG-006 integridad (ALTER, no ejecutada)' }
foreach ($id in @('V026','V043','V057','V069','V086','V105')) { $byId[$id].POLICY='Jetstream/Fortify auth+usuario propio'; $byId[$id].OBSERVACIONES='Perfil existente reutilizado y alias /actor/perfil; no segundo módulo de identidad.' }
Locate 'V103' '/estudiante/fuentes' 'app/Livewire/Shared/AcademicSources.php' 'resources/views/livewire/shared/academic-sources.blade.php' 'app/Services/AporteIngenieril/KnowledgeService.php' 'Estudiante+Materiales_Aula+validación Peter3Contract' 'Contrato real 1.0 del aporte consultado solo lectura; búsqueda oficial, provenance y fallback; respuesta real/servicio externo pendientes.'
$byId['V103'].TEST='SpecializedSupportBoundary/AporteIngenierilClient: HTTP fake/fallback/contrato; sin servicio vivo'
$byId['V104'].POLICY='Estudiante+Perfil_Academico+Peter3Contract'; $byId['V104'].OBSERVACIONES='Tutor HTTP minimizado sin datos identificatorios, timeouts limitados y fallback; answer Locked, sin persistencia preventiva.'; $byId['V104'].TEST=$byId['V103'].TEST
foreach ($id in @('V100','V103','V104')) { $byId[$id].BLOCKER='Servicio/semántica científica Peter 3 real pendiente; fallback preparado. No se simulan carreras/provenance/diagnósticos' }
foreach ($row in $rows) {
    $row.STATUS=$row.'ESTADO ACTUAL'
    if ($row.VISTA -and (Test-Path -LiteralPath $row.VISTA)) {
        $view=Get-Content -LiteralPath $row.VISTA -Raw
        $row.LOADING=if($view -match 'wire:loading'){'Declarado en vista; ejecución UX pendiente'}else{'HTTP o componente hijo; revisión por acción pendiente'}
        $row.EMPTY=if($view -match '@empty|@forelse'){'Empty declarado; resultado real pendiente'}else{'Componente hijo/legacy; revisar caso vacío'}
        $row.ERROR=if($view -match '@error|session\('){'Mensaje/validación declarado; recuperación pendiente'}else{'Layout/handler compartido; revisar por acción'}
    }
    if ($row.ACTOR -eq 'Regente') { $row.'SCOPE ACTUAL'='RegencyAccessService: gestión Y grado asignados, perfil/personal/gestión activos; cierre si falta estructura' }
    if ($row.ACTOR -eq 'Docente' -and $row.'ID ORIGINAL' -notin @('V070','V086')) { $row.'SCOPE ACTUAL'='Docente activo propietario de plan/clase; vínculo de estudiante e inscripción correlacionan gestión/grado/paralelo/turno' }
    if ($row.ACTOR -eq 'Estudiante' -and $row.'ID ORIGINAL' -ne 'V105') { $row.'SCOPE ACTUAL'='Perfil estudiante propio; clase/vínculo/inscripción activos correlacionados; orientación/metas propias' }
}
$rows | Export-Csv -NoTypeInformation -Encoding utf8 (Join-Path $doc 'MATRIZ-CONCILIACION-105.csv')
$md=@('# Conciliación de las 105 ventanas originales','','IDs, actor, nombre, tipo/modalidad y ruta propuesta conservados literalmente. Las rutas adaptadas señalan el código actual, no aprobación ni PASS. IMPLEMENTADA distingue preparado/parcial de certificado.','','| ID | Actor | Ventana original | Ruta actual | Estado | Migration vinculada |','|---|---|---|---|---|---|')
foreach($row in $rows) { $md+='| '+(($row.'ID ORIGINAL',$row.ACTOR,$row.'VENTANA ORIGINAL',$row.'RUTA ACTUAL ADAPTADA',$row.STATUS,$row.MIGRATIONS | ForEach-Object { $_ -replace '\|','/' }) -join ' | ')+' |' }
$md | Set-Content -Encoding utf8 (Join-Path $doc 'MATRIZ-105-VENTANAS.md')
$rows | Group-Object STATUS | Select-Object Name,Count | Format-Table
