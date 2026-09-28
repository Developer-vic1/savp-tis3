# PETER 2 — Auditoría de reestructuración, Fase 1

## Fase 2 — Gobernanza institucional de roles (2026-09-28)

Se amplió `/admin/roles-permisos` sobre el módulo existente. La matriz y `RolePermissionService` siguen operativos, ahora con autorización granular para asignar permisos, protección de permisos críticos y bloqueo de auto retiro de permisos. Los seis roles institucionales no se eliminan desde este módulo. La protección del último Administrador en `GestionUsuarios` permanece.

`InstitutionalRoleGovernance` contiene reglas determinísticas versionadas (`SAVP-INSTITUCIONAL-2026-1`). Distingue duplicados nominales y funcionales, nombres reservados, justificación insuficiente, permisos críticos/legados/globales, coherencia del dominio y alcance menor disponible. Son **reglas del sistema**, no afirmaciones normativas del Ministerio. Devuelve estado, explicación, razones, sugerencia, permisos permitidos/bloqueados y advertencias. No usa LLM.

`RoleRequestService` ejecuta solicitudes, revisión independiente y creación transaccional. El adjunto se valida por contenido MIME, extensión y tamaño (10 MB); se guarda con nombre aleatorio en el disco privado `local`, con SHA-256 y metadatos. Un hash reutilizado requiere explicación expresa. La descarga pasa por `actor:Administrador`, los permisos `roles-permisos.gestionar` y `roles.documentos.ver`, y nunca revela la ruta de almacenamiento. Bitácora registra solicitud, documento, revisión/rechazo/cancelación y creación sin copiar el contenido del archivo.

`InstitutionalAuthorityService` exige exactamente un Director activo relacionado con personal institucional activo y Persona. Si falta o hay varios, bloquea la solicitud. El nombre aparece desde BD y no es editable. `InstitutionalDocumentAnalyzer` deja un contrato versionado de evidencia; la integración Python de Peter 3 está **pendiente**. No se afirma autenticidad de firma ni sello. En ausencia de analizador se exige revisión documental por **otro** Administrador: identidad coincidente, legibilidad y presencia aparente de firma y sello, más fundamento escrito. Revisión y creación revalidan autoridad; creación además verifica hash/archivo, permisos y duplicados dentro de la transacción. El solicitante no puede aprobar su propia evidencia.

La migration `2026_09_28_000001_create_role_requests_table.php` crea `role_requests` con solicitud, permisos JSON, análisis, autoridad, documento, revisor, estado y rol creado. **No se ejecutó**. `RolSeeder` idempotente añade `roles.solicitudes.{ver,crear,analizar,cancelar}`, `roles.{crear,editar,desactivar}`, `roles.permisos.asignar` y `roles.documentos.ver`; `roles-permisos.gestionar` ya existía. **No se ejecutó ningún seeder ni se modificó la BD.** Tras revisión y respaldo institucional, aplicar `php artisan migrate` y `php artisan db:seed --class=RolSeeder` en el entorno autorizado. El seeder también sincroniza su catálogo preexistente de permisos, por lo que debe revisarse ese efecto antes de ejecutarlo.

Se ejecutaron 13 pruebas unitarias sin BD (29 aserciones) del analizador de roles, autoridad activa/ausente/ambigua, autorización granular y fallback documental; compilación Blade, listado de rutas y análisis sintáctico. Quedan pendientes pruebas de integración con BD aislada (incluida manipulación Livewire y carrera de creación), pruebas visuales autenticadas móvil/oscuro, y el contrato/servicio Python de Peter 3. `app.css` y `app.js` no se modificaron. Riesgo operativo: la revisión manual depende de la diligencia del segundo Administrador; no hay verificación criptográfica de firma ni OCR y por diseño no se habilita creación automática por el documento.

---

Fecha: 2026-09-28. Worktree: `C:\laragon\www\savp-reestructuracion`.
Rama: `feature/REESTRUCTURACION`.
Base original: `a5fb7eac5efafa0ac9530cc41b77852869a740ae`.

Esta revisión sustituye el informe provisional de freeze. La continuación, el commit y el push están autorizados por el encargo actual. No se mezclaron otras ramas ni se modificaron sus worktrees. El patch antiguo queda fuera del commit.

## Estado verificable

La fase implementa los flujos descritos abajo. Ningún rol se declara COMPLETO: falta ejecutar pruebas de integración con una base aislada y verificar la interfaz autenticada en escritorio/móvil y modo oscuro. La publicación de la rama es para auditoría de Peter 1; no representa un despliegue.

| Rol | Estado y evidencia | Pendiente |
|---|---|---|
| Administrador | Rutas exclusivas, operación de usuarios, permisos, asignaciones de Regencia y notas contextualizadas | Pruebas funcionales integrales y visuales |
| Director | Consultas paginadas de estudiantes, docentes, cursos, inscripciones, rendimiento, asistencia, orientación y reportes; búsqueda por código/nombre y filtro de gestión donde existe relación | Seguimiento integral no existe como entidad en esta base; reconciliación posterior por Peter 1 |
| Secretaría | Componentes operativos existentes y nueva pantalla de cuentas limitada a Estudiante/Docente | Validación funcional/visual y revisión de navegación interna de componentes compartidos |
| Regente | Alcance por asignación explícita gestión/grado, consultas de lectura y métricas filtradas | Aplicación autorizada del esquema; reportes históricos sin contexto permanecen inaccesibles |
| Docente | Cursos propios, asistencia, materiales, tareas, entregas y notas oficiales por plan/periodo | Pruebas completas con dos docentes y base aislada |
| Estudiante | Historial oficial propio separado de tareas, preparación con tareas pendientes, fuentes con materiales activos y navegación de orientación existente | Futuro/plan/asistente dependen del contrato y aporte de Peter 3; no se fabrican resultados |

## Regencia

`RegenteAsignacion` conserva una fila por Regente/gestión/grado y su estado. FK string de 20 caracteres hacia `regente`, `gestion_academica` y `curso`, timestamps y clave única. No se impone exclusividad global de grado ni una cantidad fija de Regentes.

`RegencyAccessService` centraliza la correlación simultánea de gestión y grado. Requiere cuenta, Regente y personal institucional activos para lectura. Asignación administrativa con permiso específico; valida entidades existentes y grado/perfil activos al activar. Bloquea la fila del Regente dentro de una transacción para serializar altas; máximo dos asignaciones activas por gestión. Una combinación existente se actualiza, no se duplica. Retirar conserva la fila y permite liberar capacidad incluso si el perfil fue desactivado.

La pantalla ofrece gestión activa inicial, selección de Regente/grado, estado, retiro con confirmación, paginación, fecha del último cambio y mensajes. Los cambios quedan en Bitácora mediante el servicio existente. El historial detallado de activaciones/retiros reside en Bitácora; no se presenta la fila actual como historial completo de eventos.

Se corrigió la Policy de inscripciones: validar solamente la identidad del estudiante permitía saltar a otra gestión. Ahora Regencia valida la inscripción concreta. Orientación verifica la gestión de la actividad. Los archivos de reportes generados no contienen contexto gestión/grado verificable; su Policy no concede acceso global a Regencia.

## Calificaciones oficiales

La identidad de nuevas notas es estudiante + plan de asignatura + periodo. El plan aporta gestión, grado, paralelo, turno, materia y docente. La FK nullable preserva históricos sin asignarles un plan inventado. Notas legacy sin plan quedan en lectura.

`GradeService` verifica cuenta activa, rol y permiso granular, perfil/docente titular, inscripción en las cuatro dimensiones académicas, rango 0–100, estado, duplicados e identidad inmutable al editar. Bloquea plan, periodo y nota durante la transacción. Revalida el estado después de bloquear la nota. Se registra antes/después en Bitácora.

Gestión/plan/periodo inactivos bloquean escrituras ordinarias. Rectificación de una nota existente requiere Administrador, permiso `calificaciones.rectificar` y motivo de al menos 10 caracteres. Con esa autorización puede corregirse una inscripción histórica existente, sin exigir que siga activa. Cambiar estado también requiere motivo y autorización. No se permite crear una nota nueva en un periodo cerrado.

**Límite real del esquema:** `periodo_evaluacion` es un catálogo global con nombre, orden y estado, sin `cod_gea`, fechas ni cierre por año. Se valida ese estado y el de la gestión del plan. No se inventó un cierre anual de periodo ni se importó el modelo de la otra rama. La reconciliación institucional debe resolver cierres de periodo independientes por gestión.

La pantalla Admin muestra gestión/curso y permite filtrar por gestión; sus métricas responden a los filtros y se agregan en SQL. Docente opera notas del plan del curso de la URL. Estudiante consulta solo sus notas oficiales y las distingue del promedio de tareas.

## Autorización y cuentas

Middleware por actor conserva separación de workspaces. `InstitutionalAuthorization` reautoriza componentes Admin al iniciar cada petición y al ejecutar acciones Livewire. Secretaría conserva acceso únicamente a los componentes operativos enumerados; GestiónUsuarios y matriz de permisos quedan exclusivos del Administrador.

`OperationalAccountService` y `Secretaria/Cuentas` implementan cuentas operativas con permisos separados de lectura, creación, edición, activación, desactivación y cambio de contraseña. Solo crea Estudiante/Docente si existe un perfil institucional activo y no hay otra cuenta para la persona. No acepta asignar roles en edición. Excluye cuentas con otros roles o permisos directos, nunca administra Spatie ni permite operar su propia cuenta. No registra contraseñas en Bitácora. Cambios de correo retiran su verificación; contraseñas pasan por el cast hashed existente.

GestiónUsuarios conserva protección del último Administrador mediante bloqueo del rol compartido y verificación de otra cuenta activa, además de permisos por operación. RolesPermisos conserva búsqueda, filtros, selección visible, preview, confirmación, bitácora y preservación de permisos críticos del rol Administrador. La ruta también exige el permiso de administración de la matriz.

Policies académicas rechazan cuentas inactivas. Se corrigió comparación null/null que podía conceder lectura de nota a un docente sin perfil activo. Policies de tareas bloquean entrega de borradores. Descargas de materiales respetan estado/curso; entrega docente requiere curso titular.

## Aula Virtual y datos estudiantiles

La selección de cursos del estudiante exige membresía activa y una inscripción activa en la misma gestión, grado, paralelo y turno. La carga de relaciones filtra materiales visibles, tareas publicadas/cerradas y entregas del propio estudiante. Asistencia valida el usuario contra el perfil docente recibido, titularidad, clase activa, pertenencia de cada estudiante y estado de sesión ABIERTA antes de persistir.

Se reutiliza Aula Virtual. No se unifican calificaciones de tareas con las oficiales. Fuentes y preparación muestran materiales y tareas reales. Futuro/plan/asistente informan ausencia de resultados; no se inventan promedio, afinidad, ranking o RIASEC.

## Peter 3

Cliente y DTO existentes preservados. URL, versión, paths y timeouts son configurables; configuración sin credenciales. Fallback seguro ante error HTTP, timeout o JSON inválido/vacío. Pruebas cubren 200, 422, 503, conexión, timeout, JSON inválido, respuesta parcial y ausencia de campos de identidad en el DTO de ejemplo.

Contrato PROVISIONAL. No se implementó el motor ni se contactó el servicio externo. Pendientes: contrato final de payload/respuesta, mapper y verificación de minimización de datos por cada consumidor. El DTO admite arrays: su prueba no demuestra sanitización recursiva de cualquier payload arbitrario.

## Migration pendiente y compatibilidad

`database/migrations/2026_09_28_000001_add_reestructuracion_academic_context.php`:

- Crea `regente_asignaciones` con FK restrictivas, clave única e índice gestión/grado/activa.
- Añade `calificacion.cod_pas` nullable con FK restrictiva, índice por plan y unique estudiante/plan/periodo.
- No transforma notas antiguas ni elimina registros en `up()`; históricos nulos pueden coexistir.
- `down()` retira índices/FK/columna y tabla. Es estructuralmente inversa, pero elimina el contexto/asignaciones nuevos: requiere respaldo previo, no debe ejecutarse sobre datos institucionales sin procedimiento autorizado.
- El máximo de dos grados es una regla transaccional del servicio, no un CHECK de conteo en SQL. Evitar escrituras fuera del servicio.
- Las FK legadas de estudiantes todavía pueden tener borrado en cascada. No se alteró masivamente el esquema anterior; revisar política institucional de borrado antes de producción.

Migrations ejecutadas: **NO**. Seeders modificados: `RolSeeder`, `AulaVirtualPermissionSeeder`; ejecutados: **NO**. PostgreSQL institucional: **sin modificaciones**. Las pantallas detectan la ausencia del esquema nuevo y bloquean las operaciones que lo requieren.

## Validación

- PHPUnit directo: **33 pruebas, 89 aserciones, PASS**, sin conexión a base de datos. Incluye resolución de seis roles, cliente Peter 3, límites de autorización, cuentas inactivas, material oculto, entrega ajena, tarea borrador, revocación de permisos, enlaces y middleware de workspaces.
- El primer `php artisan test` informó 21 warnings por `.env` inexistente en el worktree. La ejecución directa `php vendor/phpunit/phpunit/phpunit --testsuite Unit --no-progress` usa el mismo `phpunit.xml` y termina sin warnings. No se copió el `.env` institucional.
- `composer validate --no-check-publish`: PASS.
- `php artisan route:list --except-vendor`: PASS. Los comandos de aplicación de validación se ejecutan con SQLite `:memory:`, caché/sesión array para evitar conexiones institucionales.
- Blade `view:cache` y `view:clear`: PASS.
- `npm run build`: PASS; aviso informativo de tiempos de plugins de Vite.
- Sintaxis PHP y `git diff --check`: PASS.
- `resources/css/app.css` y `resources/js/app.js`: diff vacío. No se cambió identidad visual.

**No ejecutado:** suite Feature y pruebas autenticadas de navegador. PHP 8.4.13 disponible no tiene `pdo_sqlite`; no se instaló. `phpunit.xml` fuerza `DB_CONNECTION=sqlite`, `DB_DATABASE=:memory:` y `DB_URL` vacío. No hay config cache. No se usó PostgreSQL para pruebas.

`AcademicContextTest` contiene seis pruebas de servicios con esquema mínimo en memoria: límite/duplicados de asignación, retiro/historial, aislamiento por gestión del mismo estudiante, Regente inactivo, notas de dos años/duplicado y periodo cerrado sin motivo. CREADAS/NO EJECUTADAS. Verifica configuración SQLite antes de crear tablas y no ejecuta migrations del proyecto. No sustituye pruebas de FK/migrations reales ni pruebas de concurrencia en PostgreSQL aislado.

`WorkspaceAuthorizationTest` existente cubre redirects de seis roles y bloqueo de rutas cruzadas. CREADA/NO EJECUTADA por la misma limitación.

## Pendientes para Peter 1

1. Ejecutar Feature en una base de testing aislada disponible, revisar migrations sobre copia vacía y probar concurrencia de asignaciones/último administrador/notas.
2. Verificar flujos autenticados de los seis roles, revocación durante sesiones Livewire, IDOR completos, formularios, móvil y dark mode. Compilar vistas no demuestra UX visual.
3. Reconciliar deliberadamente con prevenciones integrales y APORTE, sin asumir que esta rama contiene sus entidades. Seguimiento integral y cierres por periodo/gestión requieren esa revisión.
4. Autorizar aparte aplicación de migration y permisos. No se aplicaron automáticamente.
5. Revisar exportaciones históricas y sus permisos/gestión antes de ampliarlas a Regencia; la consulta limitada no autoriza archivos globales.
6. Confirmar contrato de Peter 3 y probar integración real; futuro/plan/asistente siguen parciales.
7. Revisar volumen de catálogos y carga de cursos: varias pantallas heredadas siguen cargando listas completas para selectores. Nuevas tablas institucionales y de cuentas están paginadas; no se afirma auditoría de rendimiento exhaustiva.

Lista para auditoría técnica de Peter 1: **SÍ**, con limitaciones explícitas. Lista para producción o roles declarados COMPLETOS: **NO**.
