$ErrorActionPreference='Stop'
$doc='C:/laragon/www/savp-reestructuracion/docs/implementacion-maestra'
$sections=@{
'00-ESTADO.md'=@'
# Estado de implementación y preparación

Rama: feature/REESTRUCTURACION. HEAD: 2f9d5e8dc5a85983b244f32efcd30b14ebd4c16b. Trabajo local sin stage, commit ni push. Otros worktrees se consultaron solo en lectura.

CATÁLOGO ORIGINAL LOCALIZADO: SÍ. Ruta: C:/Users/LOQ/.codex/worktrees/61ea/savp-reestructuracion/docs/arquitectura-maestra-savp. Archivos encontrados: 43 (26 Markdown: LEEME y 25 entregables). Ventanas: 105. Numeración original recuperada: SÍ. Conciliación: realizada con IDs, actor, nombre, modalidad y ruta propuesta intactos.

La matriz distingue 91 PARTIAL, 10 BLOCKED_EXTERNALLY_DB y 4 BLOCKED_EXTERNALLY_PETER1; PASS: 0. PARTIAL no significa necesidad de tablas nuevas: mantiene a la vista QA y trabajo interno. No se declara implementación integral finalizada.

Se prepararon seis migrations necesarias, documentadas en 25-MIGRATIONS-PROPUESTAS.md y validadas con php -l. NO se ejecutaron. Flags de nuevas persistencias false por defecto; no se habilitaron ni poblaron catálogos. Reportes Regente y documentación de inscripción reutilizan schema.

Validación actual: 120 tests PASS (101 Unit y 19 Feature seguros), 287 aserciones, 45 SKIP por persistencia/driver; cero fallos. Vite, Composer, Pint, lint PHP y compilación Blade: evidencias cierre-* en evidencia/. Ninguno certifica DB o UX autenticada.

Base testing PostgreSQL: NO. Institucional conectado/modificado: NO. Migrations/seeders/rollback/DDL ejecutados: NO. Ver 21–25 para alcance, pendientes y revisión Peter 1.
'@
'01-RUTAS.md'=@'
# Rutas actuales

evidencia/cierre-routes.json es el inventario actual de route:list: 162 rutas registradas incluyendo paquetes, alias y redirects. No representa 162 ventanas ni cobertura funcional. MATRIZ-CONCILIACION-105.csv conserva la ruta original y documenta la adaptación efectiva.

routes/actors.php incluye direccion, secretaria, regencia, docente, estudiante y workspace_domains. Todos bajo auth y middleware actor estricto; componentes/services reautorizan las acciones posteriores. Las URLs heredadas del Aula siguen disponibles para compatibilidad.

Se registraron calendarios para los seis actores, documentación privada Admin/Secretaria, reportes administrativos Secretaria y reportes paginados/PDF Regente. /actor/perfil redirige al perfil Jetstream propio. Las rutas de dominios bloqueados muestran disponibilidad; no se declaran CRUD por existir una URL.

Filtros se validan en servidor. IDs de curso/clase no reemplazan ownership ni correlación de inscripción. Reporte Regente vuelve a obtener el plan desde el scope antes de generar PDF.
'@
'02-WORKSPACES.md'=@'
# Seis workspaces canónicos

Administrador, Director, Secretaria, Regente, Docente y Estudiante usan una identidad institucional activa inequívoca. RoleDashboardResolver y EnsureActorRole cierran acceso para cero/dos actores o cuenta inactiva; permisos complementarios no cambian el workspace.

Un shell reutilizado: layouts.app y aula-virtual.layouts.app. WorkspaceNavigation filtra rutas existentes y permisos; ModuleSearch comparte esos enlaces. Sidebar, navegación móvil, perfil y tema usan las utilidades existentes. NotificationCenter está preparado pero cerrado por flag hasta aprobación de persistencia/productores.

Director conserva consulta institucional, Secretaria operación administrativa autorizada, Regente solo gestión/grado asignados. Docente posee el plan/clase y Estudiante posee perfil/vínculo/inscripción. Ningún dashboard presenta datos de prueba en producción.

Pendiente: comprobar con sesiones reales seis actores, permisos revocados, sesiones móviles y estados de recuperación. Los tests con mocks prueban fronteras, no todo el workspace con datos.
'@
'03-ADMIN.md'=@'
# Administrador — V001–V026

Reutiliza CRUD institucional existente; InstitutionalAuthorization revalida actor activo y permiso tanto al montar como al invocar acciones Livewire. RolePermissionService conserva la única escritura operacional de rol; protege último Administrador y mutaciones de la propia identidad. app/Models/Role.php es la identidad Role configurada en Spatie.

GestionDocente usa conteos y estado de especialidad real; se quitó el porcentaje artificial de completitud. GestionInscripciones conserva documento_inscripcion_estudiante y prepara nuevos PDFs en disco privado, con hash/tamaño/descarga autorizada. Los documentos históricos públicos no se movieron.

V023 consulta LMS real; V016 tiene plazos existentes y lectura futura de eventos. V022 parámetros/auditoría Kardex necesita MIG-001 y catálogo formal. V024/V025 necesitan definición de parámetros, permisos y reglas; no se crea un almacén genérico de opciones.

Pendientes internos: revisar exhaustivamente acciones/modalidades de cada CRUD legacy y uniformar modales, filtros y manejo de errores. El hook común no acredita autorización campo por campo de todos los auxiliares.
'@
'04-DIRECTOR.md'=@'
# Director — V027–V043

WorkspaceController/InstitutionalDashboardService presentan indicadores reales según permisos. InstitutionalQueryService incluye gestión académica, cursos, estudiantes, docentes, asistencia, rendimiento, orientación local, LMS y reportes autorizados.

Notas oficiales conservan cod_pas y contexto; históricos sin contexto no se reconstruyen. HistoricalReportAccessService limita familias/formato/permiso y rutas privadas; un permiso amplio de reportes no permite SQL/ZIP ni familias desconocidas.

V042 calendario muestra plazos registrados; eventos institucionales esperan MIG-005. V035/V038 requieren seguimiento persistente MIG-001. Prevención V036 no calcula scores/umbrales sin reglas institucionales aprobadas.

Pendientes: análisis institucional/filtros/modalidades completos de la fuente, pruebas funcionales con datos y QA visual. Consultas existentes no se convierten en edición docente ni administrativa.
'@
'05-SECRETARIA.md'=@'
# Secretaria — V044–V057

Reutiliza Personas, Estudiantes, Inscripciones, procedencia/vinculación, Cursos, Paralelos y Turnos mediante autorización común. OperationalAccountService limita cuentas operativas al ámbito autorizado; no concede roles administrativos por una operación de cuenta.

V048 usa documento_inscripcion_estudiante: consulta filtrada/paginada y descarga privada, Policy de documento y de inscripción, nosniff, hash y bitácora. No necesita tabla de expediente duplicada. Migrar históricos públicos requiere conciliación de archivos autorizada posterior.

V056 lista/descarga exclusivamente PDFs administrativos generados y autorizados; SQL/ZIP y familias académicas se excluyen. V054 reutiliza plazos de Tarea y prepara eventos detrás de flag. V055 Kardex ofrece solo disponibilidad/metadatos contractuales; no se abre contenido pedagógico sensible.

La vista cuentas.blade.php conservó exactamente el trabajo previo del usuario. No se certifica su CRUD sin DB/UX aisladas. Todas las modalidades del catálogo siguen pendientes de revisión funcional.
'@
'06-REGENTE.md'=@'
# Regente — V058–V069

RegencyAccessService correlaciona gestión Y grado asignados, perfil/personal/regente/gestión activos. Si falta regente_asignaciones cierra el scope, nunca consulta global. Esa estructura ya está declarada en migration del 2026-09-28: no se preparó otra tabla de asignaciones.

V059 Mis grados consulta asignaciones propias paginadas. Cursos, estudiantes, inscripciones, asistencia y LMS se limitan por contexto. Estudiantes requieren estado e inscripción activos.

V068 tiene RegencyReportService/Controller: consulta paginada por plan asignado y PDF privado efímero. Inscripciones correlacionan gestión/grado/paralelo/turno. Conteo/promedio de notas oficiales solo si existe cod_pas y permiso de calificaciones; no se infiere nota histórica. No necesita migration nueva. Tres tests SQL/negación sin DB; PDF Regente con datos reales aún pendiente.

V063/V064 requieren MIG-001/catálogos; V066 reglas formativas y destinatarios aprobados. V067 ya muestra fechas de tareas del alcance; eventos institucionales requieren MIG-005.
'@
'07-DOCENTE.md'=@'
# Docente — V070–V086

MisCursos y CourseWorkspace reutilizan Aula: búsqueda, gestión, paginación, tabs y contexto Locked. El propietario es Docente activo del plan. CursoEstudiante/inscripción deben coincidir en cuatro dimensiones; StudentDrawer no acepta un estudiante global por ID.

Publicaciones, materiales, actividades tip_tar, tareas, entregas, asistencia y notas reutilizan modelos existentes. EntregaPolicy exige alcance vigente incluso para entrega propia; devolver y calificar tienen permisos distintos. Revisión muestra instrucción, respuesta y feedback existentes; no mezcla puntaje LMS con nota oficial.

Reportes usan conteos reales SQL y PDF privado de curso autorizado; el test PDF genera bytes reales con datos simulados exclusivamente en pruebas. Orientación muestra actividades/progreso local reales, no estados fabricados de cursos.

V073 unidades prepara lectura/MIG-002; editor/orden/enlace aún internos. V082/V083 escritura/histórico/evidencias Kardex esperan MIG-001 y reglas aprobadas; no se registran observaciones falsas. QA y transacciones reales pendientes.
'@
'08-ESTUDIANTE.md'=@'
# Estudiante — V087–V105

Materias, resumen y tabs consultan perfil y vínculo propios con inscripción activa completa. No se muestra progreso 100% cuando no hay tareas. Mi progreso diferencia notas oficiales de puntajes LMS y conserva históricos sin contexto como tales.

Mis intereses usa Likert LOCAL 1–5 y tablas de orientación existentes; no es el test científico RIASEC de Peter 3. GET del explorador ya no crea actividad; guardar/finalizar es explícito y no finaliza con cero preguntas.

Mi preparación muestra pendientes reales. Mi plan contiene AcademicPlan/AcademicGoalService/Policy y MIG-004: creación/edición con motivo, ID Locked, revisiones y dueño propio preparados; flag false impide consultar tabla/guardar. No se guardan metas en Tarea ni OrientacionRespuesta.

Fuentes y asistente usan contrato real 1.0 del aporte, provenance validada y fallback sin inventar respuestas. No conservan preguntas/PII; no necesitan tabla Peter 3 preventiva. Mi futuro no presenta % de carrera fabricado. Servicio externo y QA científica aún pendientes.
'@
'09-LMS.md'=@'
# LMS: reutilización e integridad

ClaseVirtual/ClaseEstudiante, PublicacionClase, MaterialClase, Tarea, EntregaTarea/Archivo, CalificacionTarea y AsistenciaClase/Estudiante se conservan. Tarea.tip_tar representa práctica/proyecto/etc.; ActividadClase es log, no otra actividad curricular. No hay segundo LMS.

Services Material/Tarea/Entrega/Asistencia/Publicacion validan actor, permiso y clase autorizada; locks de clase preceden hijos para reducir conflictos. Archivos privados, validación de extensión/tamaño/hash, cleanup al fallar, bitácora y estados explícitos. UI con errores/loading/empty no sustituye autorización en servidor.

MIG-002 añade unidades_clase y unidad_id nullable en recursos existentes, sin copiar/backfill. FK compuesta unidad+clase evita referencias cruzadas; archivo conserva recursos. UnitContentService y vista de lectura preparados; editor/orden/asignación de recursos y efectos sobre visibilidad pendientes antes de habilitar.

MIG-006 añade restricciones de escala con NOT VALID, conservando anomalías para revisión. Concurrencia, FK, índices/planes SQL y DDL requieren PostgreSQL aislado aprobado.
'@
'10-KARDEX.md'=@'
# Kardex y seguimiento — propuesta concreta

Auditoría: este checkout no contiene NovedadEstudiante ni SeguimientoAcademico previos. Se contrastó la referencia conceptual solo lectura; novedad administrativa no sustituye observación pedagógica con responsable, revisión, rectificación, evidencia y contexto. No se creó tabla Novedad duplicada.

MIG-001 prepara seguimiento_academico como raíz canónica de Kardex, cinco catálogos versionados y revisiones/evidencias privadas. Los campos, FK, índices, CHECK, riesgos y rollback conservador están en 25-MIGRATIONS-PROPUESTAS.md. Sin filas de catálogo, seeds ni concesión de permisos.

SeguimientoAcademico, KardexPolicy, KardexRepository/ScopedKardexRepository, KardexService y DomainReadinessService preparados. El repositorio recibe usuario+estudiante, filtra cada seguimiento: Regente gestión/grado; Docente plan propio+inscripción correlacionada; Estudiante visible/NORMAL; Admin/Secretaria metadata mínima. Flag false evita consultas a tabla futura.

Register sigue cerrado (409); no se implementa una transición disciplinaria inventada. Escrituras, revisiones/evidencias/descarga y UI de timeline/form necesitan desarrollo bajo contrato formal; son pendientes internos explícitos, además de schema/catálogos externos. No presentar estas pantallas como CRUD completo.
'@
'11-LIVEWIRE.md'=@'
# Livewire y acciones

InstitutionalAuthorization revalida componentes Admin/Aula/Secretaria al boot y call, con módulos/actores permitidos. SharedCourseList, CourseWorkspace, StudentDrawer, ModuleSearch, AcademicSources, StudyAssistant, NotificationCenter y AcademicPlan reutilizan servicios canónicos.

IDs de clase, selección de expediente y respuestas externas son Locked; búsqueda/estado/paginación se validan. Shared no queda protegido por el hook de Admin: cada componente debe aplicar su propio permiso/ownership en mount/render/acción. AcademicGoalService vuelve a buscar meta por estudiante propio antes de editar.

Se corrigieron dos fallos comprobados en tests HTTP Livewire: colisión de método privado authorize con Component y propiedad message eliminada por @error. Tutor/fuentes usan statusMessage.

Tests verifican revocación, contexto Locked y fallback/contrato. Faltan flujos completos con persistencia y auditoría visual de todos los modales legacy; montaje correcto no acredita autorización de todos los campos.
'@
'12-UX-UI.md'=@'
# UX/UI actual

Un shell y tokens existentes: layouts.app, aula-virtual.layouts.app, actor-menu, app.css y app.js. app.css preservado. Navegación móvil/backdrop/Escape, grupos por permiso, búsqueda de módulos, breadcrumbs y tabla overflow preparados.

StudentDrawer carga ficha contextual real con búsqueda autorizada, Escape, retorno de foco y tab trap preparados. Calendarios y listas tienen filtros/limpiar/empty; formularios de tareas/metas/tutor validación/loading. Sesiones status/error/warning y toasts usan textos seguros y tokens.

SweetAlert usa colores CSS variables. Chart.js destruye instancias del canvas reemplazado y actualiza tema; no se agregan gráficos de datos inventados. No todos los modales legacy fueron uniformados.

Vite y Blade validan construcción, no visual. No había sesión autenticada disponible; no hay capturas ni PASS responsive/foco/contraste de seis actores. QA requerida: 375/768/1440 px, light/dark, teclado, validación, offline, empty y errores reales en entorno aislado.
'@
'13-RBAC.md'=@'
# RBAC e identidad

Una identidad Role: app/Models/Role.php extiende Spatie Role y config/permission.php la configura. INSTITUTIONAL contiene los seis actores canónicos; alias antiguos no crean séptimo workspace. No se inventó columna est_rol.

RolePermissionService es el único escritor operacional de roles; RoleRequestService conserva el mismo role_id al resolver solicitud, con locks y transición autorizada. Se preservan permisos complementarios, último Admin y protección de identidad propia. Seeders existentes no fueron modificados ni ejecutados.

Permiso de módulo no sustituye actor/ownership/contexto. Regente necesita asignación por gestión/grado. Director no se vuelve Docente por permiso amplio. EntregaPolicy separa returnForCorrection de grade; historial PDF exige familia/formato autorizados. Kardex/metas tienen Policy específica.

Permisos nuevos/propuestos no fueron creados ni concedidos. Se probaron negativas/contratos con mocks; verificar catálogo aprobado, revocación/cache, scopes y operaciones bajo datos aislados antes de conceder permisos reales.
'@
'14-DATOS.md'=@'
# Auditoría del modelo y schema declarado

Revisión estática de 52 migrations previas y 51 modelos del snapshot de preparación (ya incluía Role canónico), con relaciones/casts/estados/servicios. Se añadieron seis migrations propuestas y cuatro modelos de dominio preparados (SeguimientoAcademico, UnidadClase, MetaAcademica, CalendarioEvento): hay 58 migrations y 55 modelos actuales. No se inspeccionaron filas/schema desplegado.

Evidencia: cierre-schema-baseline.csv (hashes previos), cierre-schema-referencias.txt (FK/check/índices), cierre-modelos-relaciones.txt y cierre-modelos-schema.csv (tabla/migration/relaciones/casts por modelo). Role hereda tabla configurada Spatie; RoleRequest usa convención role_requests. No concluir tabla ausente solo porque un modelo no declara $table.

| Dominio | Reutiliza | Preparación necesaria |
|---|---|---|
| Administración | Persona, User/Role, personal y catálogos, documento_inscripcion_estudiante | Ninguna tabla duplicada; privacidad de nuevos PDFs |
| Académico | GestionAcademica, Curso, PlanAsignatura, Inscripcion, PeriodoEvaluacion | Contexto cod_pas/regente_asignaciones ya declarado antes; comprobar aplicación futura |
| LMS | Clase/ClaseEstudiante/Publicacion/Material/Tarea/Entrega/Archivo | MIG-002 unidades/relación, conservando recursos con NULL |
| Asistencia | AsistenciaClase/Estudiante/EstadoAsistencia | Ninguna tabla nueva; locks y alcance en servicio |
| Calificaciones | Calificacion oficial y CalificacionTarea LMS | MIG-006 CHECK NOT VALID y FK restrictiva de nota oficial |
| Seguimiento/Kardex | No hay raíz equivalente completa en este checkout | MIG-001 raíz seguimiento/catálogos versionados/revisiones/evidencias |
| Orientación | Actividad/Pregunta/Respuesta/Resultado/Carrera locales | MIG-004 solo metas propias; no trasladar Likert a Peter 3 |
| Calendario | Fechas de Tarea actuales | MIG-005 eventos institucionales/revisiones |
| Notificaciones | User Notifiable actual, sin tabla notifications | MIG-003; no usar Bitácora como avisos leídos |
| Peter 3 | HTTP DTO/fallback, respuesta efímera minimizada | Ninguna tabla preventiva de preguntas/resultados sin contrato de retención |

Hallazgos: FK calificacion.cod_est originalmente CASCADE DELETE; propuesta MIG-006 la restringe. Escalas actuales solo protegidas en aplicación: CHECK NOT VALID conserva históricos y condiciona nuevas escrituras. Migration histórica 2026_06_20_144115 elimina entregas duplicadas e hijos: no ejecutada/modificada, requiere reconciliación autorizada antes de cualquier carga futura. PK string(20) se conserva en FK nuevas, incluida identidad de notificaciones; no usar morph bigint sobre cod_usu.

Riesgos legacy: generación secuencial de algunos códigos por último registro necesita revisar concurrencia; un índice/FK declarado no acredita integridad ni rendimiento desplegados. No se infiere cod_pas/gestión de created_at. No se transformaron datos ni archivos históricos públicos. Rollbacks de tablas propuestas solo admiten tablas vacías, evitando borrar registros.
'@
'15-TESTS.md'=@'
# Validación ejecutada sin PostgreSQL

Resultado JUnit cierre-tests.xml: 165 tests, 120 PASS (101 Unit y 19 Feature), 45 SKIP, 287 aserciones, 0 failures/errors. Los skips corresponden a tests que requieren persistencia/driver. No se presentan como aprobados ni como flujos DB probados.

TestCase exige SQLite :memory: y URL vacía antes de cualquier trait de DB; falta pdo_sqlite, por lo que tests RefreshDatabase/Migrations/etc. saltan antes de ejecutar migrations. No se habilitó el driver. Builder toSql compila consultas, no las ejecuta. HTTP/Livewire mocks y Storage/HTTP fakes ejercitan fronteras sin tocar datos institucionales.

Cobertura significativa: actor equivocado/inactivo/ambiguo, guest, curso ajeno, entrega propia con alcance revocado, devolución sin calificar, permiso de nota independiente, filtros correlacionados de Regencia/inscripción, privados/historial, flags cerrados, metas ajenas, Locked y fallback Peter 3. El PDF Docente genera bytes PDF reales con conteos fake de prueba; no es reporte institucional validado. Reporte Regente prueba SQL scoped y denegación, no PDF con datos.

Composer validate, Pint, lint de PHP cambiado/nuevo, php -l de seis migrations, route:list, Blade compilado/lint y npm run build: evidencias cierre-*. No se ejecutó up/down/pretend. NPM ci conserva lockfile; audit informó 11 vulnerabilidades (2 critical), ver 20-DEUDA-TECNICA.

Pendientes: PG aprobado, DDL/rollback/transacciones/concurrencia/FKs y pruebas completas por ventana; servicio Peter 3 real y UX autenticada seis actores/light-dark/responsive. El TestCase actual bloquea PG: preparar clase/config de testing independiente revisada por Peter 1 antes de cambiarlo; no basta ajustar DB_DATABASE en .env.
'@
'16-PENDIENTES-PETER1.md'=@'
# Revisión requerida de Peter 1

1. Revisar seis propuestas de 25-MIGRATIONS-PROPUESTAS.md: necesidad, FK/tipos, índices, checks, historia y rollback; ninguna ejecutada.
2. Aprobar entorno PostgreSQL aislado, credenciales exclusivas y carga de schema/snapshot autorizada; no existe hoy. No usar institucional ni cambiar TestCase a ciegas.
3. Resolver migration histórica destructiva de entrega duplicada antes de cargar datos. Confirmar contexto del 2026-09-28 y despliegue real, sin inferir cod_pas histórico.
4. Aprobar catálogos/versiones/visibilidad/transiciones/responsables/revisión de Kardex y alcance mínimo por actor. No cargar semillas inventadas ni otorgar permisos preventivos.
5. Aprobar unidades/orden/archivo, destinatarios/dedupe/retención de notificaciones, metas personales, tipos/efectos/calendario editorial y reglas de prevención/configuración.
6. Con Peter 3, validar payload/provenance y esquema 1.0 real, semántica científica, corte y retención si se necesita persistir análisis. Likert local no es RIASEC.
7. Proporcionar datos/sesiones aislados para pruebas funcionales y UX; revisar roles/permisos aprobados y legacy. Ninguna disponibilidad de schema autoriza habilitar todos los flags.

Pendientes internos están separados en 24-BLOQUEOS-EXTERNOS.md; no se atribuyen a Peter 1 productores, editor LMS o formularios aún no implementados.
'@
'17-ARCHIVOS-MODIFICADOS.md'=@'
# Auditoría de archivos actuales

evidencia/cierre-auditoria-archivos.csv inventaría cada archivo modificado/nuevo con origen, estado, referencias estáticas, observaciones y SHA256. Estados: USED, BLOCKED, DOCUMENTATION, TEST, REMOVED_NOT_ALLOWED; sin eliminar archivos legacy. REFERENCIA estática no equivale a cobertura funcional; revisar autoload/registros antes de retirar algo no referenciado literalmente.

evidencia/cierre-baseline.csv preserva estado previo; cierre-schema-baseline.csv cubre declaraciones previas. Las vistas explorador-vocacional.blade.php y secretaria/cuentas.blade.php y PETER2_REESTRUCTURACION.patch conservan hashes de la fase anterior. No se aplicó el patch.

.env, resources/css/app.css, composer.lock, package-lock.json y migrations/seeders previos no se modificaron. app.js sí tiene cambios autorizados de toasts/tema/charts; no se presenta como preservado sin diff. No commit, push, stage, borrado ni modificación de otros worktrees.

Docs/evidencias de fases anteriores se conservan; cierre-* es evidencia actual. git-status/diff-stat antiguos describen el punto anterior y no deben usarse como estado actual.
'@
'18-CONEXIONES.md'=@'
# Conexiones y fronteras externas

Peter 3: SpecializedAcademicClient/AporteIngenierilClient valida URL http(s)/endpoint relativo, sin redirects; limita timeout/connect_timeout, API key X-SAVP-AI-Key, schema_version 1.0 y respuesta. Tutor envía question/version sin identidad; conocimiento query/top_k/official_only; análisis minimiza identidad por HMAC y exige datos/escalas válidos. No convertir Likert local en RIASEC ni fabricar notas/records.

Se leyó contrato de C:/laragon/www/savp-tis3-aporte/ai-service/app/api/v1 solo en lectura. Tutor/fuentes tienen offline/timeout/response-invalid fallback sin afirmar diagnóstico. HTTP fake verifica protocolo; servicio vivo no conectado/certificado. No persistencia de preguntas/respuestas sin retención aprobada.

Notificaciones/metas/unidades/Kardex/calendario institucional: services, models y migrations preparados; config/features.php false por defecto. No se modificó .env real ni se hizo disponibilidad consultando PG institucional. Flags no sustituyen aprobación, pruebas ni terminación de productores/editor/forms.

Reportes PDF utilizan disco local privado y nombres UUID; histórico pasa familia/formato/permiso/hash. SQL/ZIP administrativo existente conserva restricciones por actor. Documentación usa storage privado; histórico público pendiente de conciliación autorizada.
'@
'19-DARK-MODE.md'=@'
# Tema claro/oscuro

Se preserva resources/css/app.css y sus tokens. Shell único, sidebar, búsqueda, tablas y componentes nuevos usan ui-* y CSS variables. ThemeManager/evento theme-changed se reutilizan; SweetAlert/toasts usan surface/text/border/primary, sin segunda paleta.

Chart.js actualiza colores por tema y destruye instancias cuyo canvas se reemplaza en navegación Livewire. Vite valida compilación; no acredita contraste, legibilidad o ausencia de fallos JS en navegación autenticada.

Pendiente QA light/dark de seis actores: topbar/sidebar móvil, foco/tooltip, drawer/escape/retorno, dropdown/campana, formularios/validaciones, tablas y modales legacy a 375/768/1440. No se marca PASS visual en la matriz por detectar una clase CSS.
'@
'20-DEUDA-TECNICA.md'=@'
# Deuda y límites verificables

Trabajo interno pendiente: editor/reordenamiento/enlace de unidades y su visibilidad; productores académicos de notificaciones post-commit/dedupe/revocación; escrituras/revisión/evidencias/timeline Kardex bajo contrato; CRUD editorial calendario; revisión exhaustiva de CRUD/modales legacy, filtros/modalidades de cada ventana y recuperación de errores. No son bloqueos de testing DB por sí solos.

Testing externo: PG aislado aún inexistente/no aprobado, catálogos/reglas institucionales pendientes y servicio Peter 3 real. No certificar transacciones/scopes SQL/DDL por tener mocks/build. QA autenticada y responsive/dark pendiente; no hay capturas de sesiones reales.

Auditoría NPM actual: 11 paquetes vulnerables, 1 low/2 moderate/6 high/2 critical; critical: shell-quote transitivo y concurrently directo vía shell-quote. Otros directos: axios, postcss, vite. evidencia/cierre-npm-audit.json y resumen.csv registran avisos completos. npm ci preservó lockfiles; no npm audit fix ni actualización arbitraria. Plan separado: revisar actualizaciones compatibles, ejecutar regresión y documentar diff de dependencias antes de publicar.

Legacy: algunos códigos usan último registro para secuencia y pueden colisionar concurrentemente; documentación histórica pública no movida; migration histórica de entregas elimina duplicados e hijos y no recupera datos en down; periodos actuales no aportan por sí solos calendario/gestión de cierre. Registrar y resolver con revisión de dominio/datos, sin modificación institucional automática.
'@
'21-CIERRE-105-VENTANAS.md'=@'
# Entrega de conciliación — cierre funcional pendiente

105 IDs originales V001–V105 preservados, sin V106 ni renumeración. Catálogo fuente 43 archivos intactos. Matriz CSV incluye origen, ruta adaptada, código/vista/service/policy, scope, datos/acciones/filtros, estados UI, prueba, status, blocker, migration y pendiente interno.

Estado verificable: 91 PARTIAL; 10 BLOCKED_EXTERNALLY_DB; 4 BLOCKED_EXTERNALLY_PETER1; 0 PASS. Una frontera de disponibilidad no cuenta como CRUD. Calendarios/unidades combinan reutilización actual y componentes nuevos pendientes: no se bloquea la ventana entera si existe utilidad con schema actual. Fuentes/tutor tienen fallback y contratos, sin respuesta científica real certificada.

Seis migrations físicamente preparadas y sintaxis válida, sin ejecución; informe específico 25. Código útil añadido sin tablas nuevas: calendarios de tareas, consulta de gestión, documentación privada, reportes Secretaria/Regente, curso/estudiante contextuales, filtros, políticas de entregas y perfiles, HTTP Peter 3 y estados UX comunes.

No se declara IMPLEMENTACIÓN INTEGRAL FINALIZADA: faltan trabajo interno enumerado y pruebas completas en entorno autorizado. La meta de PARTIAL=0 debe conseguirse terminando y probando, no renombrando estados.
'@
'22-ARQUITECTURA-FINAL.md'=@'
# Arquitectura conciliada con la aprobada

Se conserva catálogo original: identidad única User/Role → actor y permiso → workspace → Controller/Livewire → Policy/Service → consulta scoped → modelo existente → datos reales. Reportes/documentos pasan scope/policy antes de disco privado. SQL/ZIP requieren permiso específico y actor correspondiente.

Administración/academia conservan servicios actuales. Docente/Estudiante reutilizan el mismo Aula, CourseWorkspace y clases/material/tarea/entrega/asistencia. Regencia comparte RegencyAccessService en consultas/reportes/calendarios. Orientación local conserva su semántica, separada del aporte científico HTTP Peter 3.

Extensiones mínimas propuestas: seguimiento como raíz de Kardex con catálogos versionados e historia; unidades enlazan recursos existentes; notifications respeta cod_usu string; metas propias separadas de plan docente; calendario institucional de referencia selectiva; checks académicos conservadores. No carpetas copiadas ni tablas paralelas de actividades/documentos/asignaciones.

Flags cerrados no son arquitectura completada. Services/DTOs/fallback y contratos se preparan mientras se resuelven persistencia/reglas; escritura institucional nueva permanece deshabilitada. Ver 24 para límites y 25 para schema propuesto.
'@
'23-CHECKLIST-FUNCIONAL.md'=@'
# Checklist verificable

| Verificación | Resultado actual | Evidencia / límite |
|---|---|---|
| IDs/nombres/actores/tipos/rutas propuestas originales | Conservados 105/105 | script conciliar-catalogo.ps1 valida literalmente |
| Catálogo fuente preservado | 43/43 hashes iguales | inventario-arquitectura-original.csv / cierre-preservacion.txt |
| Actor único activo/permiso complementario | Tests de frontera PASS | mocks, sin catálogo de BD real |
| Curso/estudiante ajeno y contexto Locked | Negativas PASS | WorkspaceHttpBoundary/WorkspaceIntegrity |
| Regencia gestión+grado/inscripción cuatro dimensiones | SQL de builder PASS | InstitutionalScopeContract/RegencyReportScope, sin ejecutar SQL |
| Entrega propia con alcance revocado/calificar vs devolver | Contratos PASS | SubmissionAuthorization |
| Reportes históricos/familia/path | Negativas PASS | HistoricalReportAuthorization |
| PDF Docente privado | PDF real de prueba PASS | conteos simulados; PG pendiente |
| Nuevas persistencias deshabilitadas | Tests PASS | PreparedPersistenceBoundary, no queries de tabla futura |
| Peter 3/fallback/Locked | HTTP fake/Livewire PASS | sin servicio real ni ciencia certificada |
| Sintaxis seis migrations | php -l PASS | no DDL/up/down/rollback ejecutado |
| Build/Blade/Pint/Composer | PASS de construcción | evidencia cierre-* |
| CRUD/transacciones/FK/concurrencia con PG | PENDIENTE | base aislada no existe |
| Toda acción/modalidad de 105 ventanas | PENDIENTE | matriz 91 PARTIAL, no claim PASS |
| Productores notificaciones / editor unidades / Kardex CRUD / calendario editorial | PENDIENTE INTERNO | describir y terminar antes de habilitar |
| UX real seis actores claro/oscuro/responsive | PENDIENTE | sin sesiones/capturas autenticadas |
| Institucional/migrations/seeders/commit/push | NO MODIFICADO / NO EJECUTADO | cierre-preservacion y Git |
'@
'24-BLOQUEOS-EXTERNOS.md'=@'
# Bloqueos externos y pendientes internos

## BLOCKED_EXTERNALLY_DB — solo persistencia nueva

V022, V035, V038, V055, V063, V064, V082, V083, V098: MIG-001 seguimiento/Kardex y catálogos versionados. V102: MIG-004 metas personales. Estado de escritura/lectura nueva: no habilitado, no aplicado. Además de DDL requieren catálogo/reglas y pruebas. No existe base PG testing aprobada.

V073/V090 (MIG-002), calendarios V016/V042/V054/V067/V081/V096 (MIG-005) y campana transversal (MIG-003) tienen componentes nuevos que necesitan DDL, mientras publicaciones/plazos/workspaces existentes siguen desarrollándose. Por ello su ventana completa conserva PARTIAL; no se atribuye bloqueo total a PG testing.

## BLOCKED_EXTERNALLY_PETER1 — definición institucional

V024/V025: parámetros/configuración formal y permisos; no se crea tabla de opciones genéricas. V036/V066: reglas preventivas/alertas, responsables, destinatarios, evidencia y revisión humana; no se inventa score ni sanción automática.

Peter 3 limita validación científica real de V100/V103/V104; adapter/fallback/contrato HTTP 1.0 están preparados y testeados con fakes. No requiere ahora tabla nueva. Snapshot persistente solo después de contrato/version/hash/corte/retención aprobados.

## Pendientes internos — no ocultarlos como externos

1. Escribir, rectificar/anular, revisar y descargar evidencia/timeline Kardex tras reglas aprobadas; actualmente register devuelve 409 y pantallas son disponibilidad.
2. Editor/reordenamiento/asignación y visibilidad de recursos de unidades; lectura preparada no es gestión curricular terminada.
3. Productores/notificaciones post-commit con dedupe y alcance revocable; campana/listado/leído no acreditan eventos reales.
4. CRUD editorial y reglas de solapamiento/calendario; consulta de tareas/eventos no es gestión editorial.
5. Certificar cada acción/modalidad de CRUD legacy, filtros y modales/drawers completos, recuperación/loading y acceso de teclado.
6. Pruebas reales de transacciones/concurrencia/históricos y QA autenticada clara/oscura/responsive; mocks y build no las reemplazan.

No marcar 91 PARTIAL como BLOCKED_EXTERNALLY_DB únicamente porque falta entorno de pruebas; completar código y conservar trazabilidad de validación pendiente. Flags continúan false. No pedir aprobación para ejecutar algo no preparado/revisable.
'@
}
foreach($entry in $sections.GetEnumerator()){ $entry.Value | Set-Content -LiteralPath (Join-Path $doc $entry.Key) -Encoding utf8 }
Write-Output "Documentos actualizados: $($sections.Count); 25 se conserva con detalle de seis propuestas."
