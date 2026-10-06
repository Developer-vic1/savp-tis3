# Revisión arquitectónica de migrations — 2026-10-01

**Prevalece esta revisión sobre la justificación inicial que se conserva debajo.** Los seis archivos físicos permanecen CREADA_NO_EJECUTADA, sin modificación. Una decisión APPROVE_AS_IS indica admisibilidad arquitectónica del archivo, **no autorización de ejecución ni PASS funcional**. PostgreSQL no fue consultado ni modificado. Detalle del modelo y MIG-007: [35-MODELO-DATOS-OBJETIVO.md](35-MODELO-DATOS-OBJETIVO.md).

| ID | Decisión | Consecuencia |
|---|---|---|
| MIG-001 | **APPROVE_WITH_CHANGES** | Añadir momento del hecho y registro; decidir plan obligatorio para incidentes generales; edición publicada de cinco catálogos y coherencia tipo/categoría/medida; autor/responsable y revisiones inmutables. Cinco tablas tipadas son justificables, pero no se aprueban sus valores. |
| MIG-002 | **APPROVE_AS_IS** | Estructura organizativa y FK compuesta evitan unidad ajena. Nullable conserva recursos. UNIQUE(id,cod_cla) respalda FK, no es índice duplicado eliminable. Falta implementación de edición y prueba concurrente, no otra tabla. |
| MIG-003 | **APPROVE_WITH_CHANGES** | Infraestructura Notifiable correcta y PK textual. Fijar contrato del discriminador de User/morph map, política de retención y productores idempotentes; no admitir tipos polimórficos que no cumplen FK a users. |
| MIG-004 | **APPROVE_AS_IS** | Meta/acción personal no son PlanAsignatura ni Tarea. Revisiones y estado satisfacen mínimo; sin contador de progreso. API de histórico y retención requieren revisión funcional. |
| MIG-005 | **APPROVE_WITH_CHANGES** | Evento institucional distinto de plazo de tarea/gestión/horario. Definir tipos/efectos y duración parcial o multidiaria/hora/zona; no ejecutar suspensiones de clases por simple evento. Revisión y scope temporal siguen pendientes. |
| MIG-006 | **SPLIT** | Separar integridad estructural/retención de checks de escala; NOT VALID también restringe filas actualizadas. No imponer 0..100/1..1000 al histórico sin perfilado ni equivalencia. Reversión actual restaura CASCADE: no rollback de producción seguro. |

Admisibles sin cambio de schema: **2/6** (MIG-002, MIG-004). Requieren rediseño: **4/6** (MIG-001/003/005 y separación MIG-006). Ninguna propuesta se rechaza por duplicar una tabla existente en este checkout; eso no certifica ausencia en PostgreSQL institucional. MIG-001 no debe CREATE sobre seguimiento ya presente en una futura base; primero comparar columnas/keys. Cinco catálogos tipados mantienen FK específicas; publicación/relaciones/valores requieren aprobación. Un catálogo genérico solo se adoptaría garantizando su discriminador, no porque tenga menos tablas.

## Necesidad adicional y paquetes futuros

**MIG-007:** PROPUESTA, NO_CREADA, Orientación local versionada; V037/V099/V100 y V101 si consume el resultado. Una nueva entidad `orientacion_instrumento_versiones` y extensiones nullable de preguntas/intentos/resultados. Schema actual carece de edición, escala/algoritmo/hashes congelados: un resultado finalizado no se reproduce de forma fiable si se editan preguntas. Sus columnas, PK/FK, CHECK, índices, privacidad, ownership y rollback conservador se especifican en el apartado MIG-007 del modelo objetivo. No añade RIASEC científico ni guarda resultados de Aporte Ingenieril SAVP indiscriminadamente.

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

Tablas duplicadas de Tarea/Entrega/Asistencia/Nota, tablas de sugerencias/completitud/porcentaje/contador de widget, otra bitácora, segundo Role, chat automático, biblioteca de fuentes efímeras y resultados Aporte Ingenieril SAVP universales: NO propuestas. Reutilizar schema existente; corregir contrato/callers antes de extender.

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

# Migrations propuestas para revisión de Peter 1

Estado al 2026-09-30: **6 archivos CREADA_NO_EJECUTADA**. No hay base PostgreSQL de pruebas aprobada. No se crearon bases, no se consultó PostgreSQL institucional, no se ejecutaron migrations, seeders, rollback ni pretend. Los nombres de estados propuestos no equivalen a aprobación institucional.

La autorización del usuario permite preparar archivos necesarios y validarlos estáticamente; la ejecución requiere otra aprobación explícita de Peter 1. Fuente primaria: las 52 migrations y 51 modelos que existían antes de esta preparación, registrados con hashes en `evidencia/cierre-schema-baseline.csv`. Los seis archivos posteriores son los únicos nuevos de schema. La presencia de archivos no confirma su aplicación en ninguna base real.

## Reutilización y límites de la evidencia

Se leyeron los archivos de `database/migrations/` y `app/Models/`, incluidos los de `migrations/aula_virtual`, que registra AppServiceProvider. Las referencias de FK, CHECK, índices y UNIQUE están en `evidencia/cierre-schema-referencias.txt`; modelos, relaciones y casts en `cierre-modelos-relaciones.txt` y `cierre-modelos-schema.csv`.

NovedadEstudiante y SeguimientoAcademico **no estaban presentes en este checkout**. Sí se encontraron sus modelos y la migration `2026_09_09_100000_create_prevenciones_academicas_tables.php` en `C:/laragon/www/savp-tis3`, consultado solo en lectura. Novedad trata ausencia/novedad administrativa y no soporta autor docente, contexto de plan, visibilidad ni revisión de observaciones. Seguimiento soporta motivo, responsable, fechas y cierre: se mantiene ese concepto y nombre para Kardex, ampliado selectivamente. No se copió su paquete de cuatro tablas, ni se creó un segundo Kardex paralelo.

## Convenciones comunes

Los identificadores institucionales siguen `varchar(20)` y referencian las PK actuales. Los IDs internos nuevos son `bigint` autogenerado o `uuid`, según el detalle. `timestampsTz` propone `timestamp with time zone`; fechas puras son `date`, horas locales `time`. Nullable y defaults se indican debajo. Ninguna migration rellena datos institucionales, carga catálogos, concede permisos o habilita flags.

MIG-001 a MIG-005 rechazan down cuando sus tablas tienen filas: la inversión estructural solo se permite sin histórico. Con datos se requiere exportación y una migration correctiva aprobada. No es un rollback operativo certificado. Los estados visibles, retención, protección de datos y roles que puedan cambiarlos requieren revisión de Peter 1.

## MIG-001 — Kardex y seguimiento formativo

- **ARCHIVO:** `database/migrations/2026_09_30_100001_prepare_kardex_seguimiento_structure.php`.
- **ESTADO:** CREADA_NO_EJECUTADA. **MÓDULO:** Kardex/Seguimiento. **TIPO:** CREATE y ALTER.
- **VENTANAS:** V022, V035, V036, V038, V055, V063, V064, V066, V082, V083, V098. Prevención/alertas reutilizan la evidencia; sus reglas no se crean por esta migration.
- **PROBLEMA:** no hay observación formativa persistida en este checkout con contexto verificable, catálogos ni revisiones/evidencias.
- **SCHEMA EXISTENTE INSUFICIENTE:** Tarea/Entrega/Asistencia/Calificacion representan evidencia académica; convertirlas en observaciones duplicaría o alteraría su significado. Bitácora registra operaciones, no acuerdos educativos. OrientacionActividad conserva cuestionarios, no seguimiento de incidentes.
- **TABLAS REVISADAS:** estudiante, users, plan_asignatura, inscripcion_estudiante, tarea, entrega_tarea, asistencia_estudiante, calificacion, orientacion_actividades, bitacora; seguimiento_academico y novedad_estudiante en la referencia operativa.

**TABLAS/COLUMNAS:**

| Tabla | Columnas, tipo, nulabilidad/default y significado |
|---|---|
| kardex_tipos, kardex_categorias, kardex_niveles, kardex_estados, kardex_medidas | `codigo varchar(20)` y `version integer` NOT NULL, sin default: identidad versionada aprobada; `nombre varchar(120)` NOT NULL; `descripcion text` NULL; `activo boolean` NOT NULL default true; created_at/updated_at timestampsTz. No se insertan valores ni niveles disciplinarios. |
| seguimiento_academico | `cod_seg varchar(20)` PK; `cod_est,cod_gea,cod_cur,cod_pas,cod_usu_aut,cod_usu_res varchar(20)` NOT NULL sin default: estudiante, gestión, grado, plan, autor y responsable derivados del servidor. `version_catalogo integer` NOT NULL; `tip_seg,niv_ape_seg,est_seg varchar(20)` NOT NULL referencian la misma versión de catálogos. `cod_categoria,cod_medida varchar(20)` NULL. `ori_seg varchar(100)` y `mot_seg text` NOT NULL. `vis_seg varchar(20)` NOT NULL default RESTRINGIDO; `visible_estudiante boolean` default false. `fec_ape_seg date` NOT NULL; `fec_pro_seg,fec_cie_seg date` NULL; `res_seg,pro_acc_seg,obs_seg text` NULL. timestampsTz. |
| seguimiento_revisiones | `id bigint` PK; `cod_seg,cod_usu varchar(20)` NOT NULL; `tipo varchar(30)`, `motivo text`, `datos jsonb` NOT NULL: operación, razón y snapshot protegido; `created_at timestamptz` NOT NULL default CURRENT_TIMESTAMP. Sin updated_at: revisión histórica. |
| seguimiento_evidencias | `id bigint` PK; `cod_seg,cod_usu varchar(20)` NOT NULL; `ruta varchar(255),nombre varchar(180),mime varchar(100)` NOT NULL; `bytes bigint`, `sha256 char(64)` NOT NULL; `created_at timestamptz` default CURRENT_TIMESTAMP. Puntero a archivo privado, nunca contenido público. |
| plan_asignatura ALTER | Solo UNIQUE `(cod_pas,cod_gea,cod_cur)` para la FK compuesta de contexto; no se cambia ningún registro. Su PK cod_pas ya impide duplicados. |

**PK:** catálogos `(codigo,version)`; seguimiento cod_seg; revisiones/evidencias id. **FK:** estudiante y users; contexto completo de plan `(cod_pas,cod_gea,cod_cur)`; catálogos por `(código,version_catalogo)`; revisiones/evidencias → seguimiento y users. **ON DELETE:** RESTRICT en todas. **ON UPDATE:** CASCADE para identidad/contexto; RESTRICT para catálogo versionado.

**ÍNDICES:** timeline `(cod_est,cod_gea,fec_ape_seg)`, revisión Regencia `(cod_gea,cod_cur,est_seg,fec_pro_seg)`, plan, autor/responsable, cada catálogo+versión, revisiones/evidencias `(cod_seg,created_at)` y autor. **UNIQUE:** PK compuestas y contexto del plan; no se impide tener varias observaciones legítimas.

**CHECKS:** versión positiva, motivos no vacíos, fechas de revisión/cierre posteriores a apertura, visibilidad NORMAL/RESTRINGIDO y publicación a estudiante solo NORMAL; operación de revisión permitida y datos objeto JSON; evidencia bytes positivos, SHA hexadecimal de 64 caracteres y prefijo kardex/. PrivateFilePath y autorización deben validar además el path y la descarga; el CHECK de prefijo no basta.

**HISTÓRICO:** revisiones y evidencias no se eliminan en cascada. Cambios de catálogo deben añadir versiones, nunca reinterpretar registros anteriores. **SOFT DELETE:** NO; anulación/cierre mediante revisión. **AUDITORÍA:** autor/responsable, revisor, uploaded_by representado por cod_usu y Bitácora operacional.

**MODELOS:** SeguimientoAcademico (preparado; fechas/boolean/version casteados). **SERVICES:** KardexService, ScopedKardexRepository, DomainReadinessService, RegencyAccessService. **POLICIES:** KardexPolicy y AcademicAccessService; permisos propuestos sin creación/concesión. Repositorio recibe usuario+estudiante, no solo un ID; Regencia filtra cada registro por gestión/grado, Docente por plan/inscripción, estudiante por visibilidad; Secretaria y Admin reciben metadata mínima.

**RIESGOS:** valores/estados iniciales y transiciones aún deben aprobarse; registro docente continúa cerrado. Las revisiones/evidencias requieren servicio de escritura, UI y pruebas de persistencia antes de habilitar. No hay trigger que garantice inmutabilidad ante SQL externo. Si la tabla ya existe en una base futura, detener y conciliar su estructura; no usar CREATE como sustitución del histórico.

**ROLLBACK:** solo tablas vacías; elimina hijos, seguimiento, catálogos y la UNIQUE añadida. **DEPENDENCIAS:** tablas base, contexto académico, catálogo versionado, permisos y contrato formal de visibilidad. **AUTORIZACIÓN PETER 1 NECESARIA PARA EJECUTAR:** SÍ.

## MIG-002 — Unidades curriculares sin duplicar recursos

- **ARCHIVO:** `database/migrations/2026_09_30_100002_prepare_lms_units_structure.php`.
- **ESTADO:** CREADA_NO_EJECUTADA. **MÓDULO:** LMS. **TIPO:** CREATE y ALTER. **VENTANAS:** V073, V090; enlaces de V074–V076, V091–V092.
- **PROBLEMA/SCHEMA INSUFICIENTE:** PublicacionClase permite anuncios y contenido; no expresa unidades ordenadas ni relaciones con material/tarea. ClaseVirtual y PlanAsignatura no son una unidad curricular. Tarea ya representa PRACTICA/PROYECTO/etc.; no se crea tabla de actividades.
- **TABLAS REVISADAS:** clase_virtual, publicacion_clase, material_clase, tarea, plan_asignatura, users, actividad_clase (log de actividad, no unidad).
- **TABLA NUEVA:** unidades_clase. **ALTER:** material_clase, tarea, publicacion_clase añaden unidad_id nullable; históricos permanecen sin asignación inferida.

**COLUMNAS:** unidades: `id bigint` PK; `cod_cla varchar(20)` NOT NULL; `titulo varchar(180)` NOT NULL; `descripcion text` NULL; `orden integer` NOT NULL; `publicada boolean` default false; `archivada_at timestamptz` NULL; `created_by varchar(20)` NOT NULL; timestampsTz. En cada recurso `unidad_id bigint` NULL sin default, mantiene cod_cla existente.

**FK:** clase/users; recurso `(unidad_id,cod_cla)` → unidad `(id,cod_cla)`, impide asociar unidades de otra clase incluso si se manipula el cliente. **ON DELETE/UPDATE:** RESTRICT/CASCADE. **UNIQUE:** `(id,cod_cla)` y `(cod_cla,orden)`. **ÍNDICES:** clase/publicación/archivo, autor y unidad/clase por recurso. **CHECKS:** orden positivo, título no vacío, unidad archivada no publicada.

**HISTÓRICO:** recursos existentes no se copian ni reescriben; unidad se archiva. **SOFT DELETE:** NO; archivada_at es estado explícito. **AUDITORÍA:** created_by y Bitácora para futuras mutaciones. **MODELOS:** UnidadClase y recursos actuales. **SERVICES:** UnitContentService, CursoVirtualService, CourseWorkspace. **POLICIES:** AulaVirtualCursoPolicy/ownership y permisos LMS. Consulta futura por actor, clase propia y publicación; UI de lectura preparada. Edición/orden curricular sigue pendiente antes de activar.

**RIESGOS:** reordenamiento requiere transacción y resolución temporal de UNIQUE; clases cerradas y enlaces cruzados necesitan Feature sobre PG. **ROLLBACK:** solo sin unidades; quitar FK/índice/columnas nuevas, después tabla; conserva recursos. **DEPENDENCIAS:** migrations LMS previas, aprobación de unidad/orden. **AUTORIZACIÓN PETER 1 NECESARIA PARA EJECUTAR:** SÍ.

## MIG-003 — Notificaciones Laravel

- **ARCHIVO:** `database/migrations/2026_09_30_100003_prepare_database_notifications.php`.
- **ESTADO:** CREADA_NO_EJECUTADA. **MÓDULO:** Notificaciones. **TIPO:** CREATE.
- **VENTANAS:** shell transversal V001, V027, V044, V058, V070, V087; eventos de V074–V079 y V091–V095. No se añade un V106.
- **PROBLEMA/SCHEMA INSUFICIENTE:** User usa Notifiable y existe recuperación de contraseña, pero no hay tabla notifications ni productores académicos persistentes. Bitácora y avisos JavaScript no conservan leído/no leído.
- **TABLAS REVISADAS:** users, bitacora, publicacion_clase, material_clase, tarea, entrega_tarea, calificacion_tarea; no hay notifications en migrations previas.
- **TABLA NUEVA:** notifications.

**COLUMNAS:** `id uuid` PK; `type,notifiable_type varchar(255)` NOT NULL; `notifiable_id varchar(20)` NOT NULL, compatible con users.cod_usu (no bigint morphs); `event_key varchar(64)` NULL, deduplicación opcional; `data jsonb` NOT NULL objeto de preview mínimo/enlace permitido; `read_at timestamptz` NULL; timestampsTz. Ningún contador persistido artificial.

**FK:** notifiable_id → users.cod_usu. **ON DELETE/UPDATE:** RESTRICT/CASCADE. La infraestructura preparada está destinada a User; otro tipo polimórfico requiere revisión adicional, no lo habilita esta FK. **ÍNDICES:** tipo/destinatario/read_at/created_at. **UNIQUE:** `(notifiable_id,event_key)`; NULL permite avisos Laravel convencionales. **CHECKS:** data debe ser objeto JSON.

**HISTÓRICO:** avisos y estados de lectura conservados; política de retención pendiente. **SOFT DELETE:** NO. **AUDITORÍA:** relación del destinatario y Bitácora al cambiar leído/no leído. **MODELOS:** User, Illuminate Notifications DatabaseNotification. **SERVICES:** NotificationService. **POLICIES:** actor único y relación notifications() del usuario, ruta/params limitados a WorkspaceNavigation. **UX:** NotificationCenter, conteo real, lista paginada, loading/empty; desactivada por defecto sin badge 0 falso.

**RIESGOS:** productores, dedupe, envío después de commit y revocación de alcance necesitan terminarse y probarse antes de activar; no se marca esto PASS por crear la tabla. **ROLLBACK:** solo tabla vacía. **DEPENDENCIAS:** users, eventos/privacidad/retención aprobados. **AUTORIZACIÓN PETER 1 NECESARIA PARA EJECUTAR:** SÍ.

## MIG-004 — Metas personales

- **ARCHIVO:** `database/migrations/2026_09_30_100004_prepare_student_academic_goals.php`.
- **ESTADO:** CREADA_NO_EJECUTADA. **MÓDULO:** Orientación/plan personal. **TIPO:** CREATE. **VENTANA:** V102.
- **PROBLEMA/SCHEMA INSUFICIENTE:** no hay objetivos/acciones personales. PlanAsignatura organiza docencia; Tarea es una actividad del curso; OrientacionRespuesta mide intereses. Guardar metas allí alteraría esos dominios.
- **TABLAS REVISADAS:** plan_asignatura, tarea, orientacion_actividades, orientacion_respuestas, orientacion_resultados, estudiante, gestion_academica, users.
- **TABLAS NUEVAS:** metas_academicas y meta_academica_revisiones.

**COLUMNAS:** meta: `id uuid` PK; `cod_est varchar(20)` NOT NULL; `cod_gea varchar(20)` NULL, no se infiere gestión histórica; `titulo varchar(180),objetivo text` NOT NULL; `accion text,fecha_objetivo date` NULL; `estado varchar(20)` default BORRADOR; `created_by varchar(20)` NOT NULL; timestampsTz. Revisión: `id bigint` PK, `meta_id uuid,cod_usu varchar(20),motivo text,datos jsonb` NOT NULL; created_at timestamptz default CURRENT_TIMESTAMP.

**FK:** estudiante, gestión, autor; revisión→meta/usuario. **ON DELETE/UPDATE:** RESTRICT/CASCADE. **ÍNDICES:** estudiante/estado/fecha, gestión, autor, meta/fecha de revisión. **UNIQUE:** PK; se permiten varios objetivos personales. **CHECKS:** BORRADOR/ACTIVA/COMPLETADA/CANCELADA, título/objetivo/motivo no vacío, revisión objeto JSON. Son estados técnicos propuestos, no aprobación de reglas institucionales.

**HISTÓRICO:** snapshot antes/después en cada guardado, motivo al editar; cancelación conserva registro. **SOFT DELETE:** NO. **AUDITORÍA:** created_by, revisor y Bitácora mínima (sin texto sensible). **MODELOS:** MetaAcademica. **SERVICES:** AcademicGoalService. **POLICIES:** MetaAcademicaPolicy, estudiante propio+Perfil_Academico; ningún Docente/Secretaria obtiene acceso por un permiso amplio. **UX:** AcademicPlan dentro de V102, ID Locked, formularios/validación/loading/listado/edición con motivo; flag false no escribe ni consulta tabla faltante.

**RIESGOS:** estados/transiciones y conservación de revisiones requieren aprobación y pruebas transaccionales. Marcar COMPLETADA es declaración del usuario, no KPI de rendimiento. **ROLLBACK:** solo tablas vacías, hijo antes de meta. **DEPENDENCIAS:** identidad, tabla estudiante, definición institucional de metas. **AUTORIZACIÓN PETER 1 NECESARIA PARA EJECUTAR:** SÍ.

## MIG-005 — Calendario institucional

- **ARCHIVO:** `database/migrations/2026_09_30_100005_prepare_institutional_calendar.php`.
- **ESTADO:** CREADA_NO_EJECUTADA. **MÓDULO:** Calendario. **TIPO:** CREATE.
- **VENTANAS:** V016, V042, V054, V067; complementa V081 y V096.
- **PROBLEMA/SCHEMA INSUFICIENTE:** plazos de Tarea ya alimentan calendario real; no representan suspensión, evento institucional ni revisión/cancelación con motivo. Los períodos de evaluación actuales no contienen calendario completo.
- **TABLAS REVISADAS:** tarea, gestion_academica, periodo_evaluacion, horario/horario_detalle, curso/paralelo/turno y calendario_evento del árbol operativo (solo lectura).
- **TABLAS NUEVAS:** calendario_evento y calendario_evento_revisiones. Reutiliza nombre/campos/reglas de la referencia, sin copiar inscripción_vigencia ni novedades.

**COLUMNAS:** evento: `cod_cae varchar(20)` PK, `cod_gea varchar(20)` NOT NULL; `nom_cae varchar(180),tip_cae varchar(40)` NOT NULL; `fii_cae,ffi_cae date` NOT NULL; `hoi_cae,hof_cae time` NULL ambos; `cod_tur,cod_cur,cod_par varchar(20)` NULL, alcance general si vacíos; `est_cae varchar(20)` default PREALERTA, `efe_cae varchar(30)` default INFORMATIVO; `cod_cae_ori varchar(20)` NULL; `mot_cae text` NOT NULL, `fue_cae text` NULL; `created_by varchar(20)` NOT NULL; timestampsTz. Revisión: id bigint PK, cod_cae/cod_usu varchar(20), motivo text, datos jsonb NOT NULL; created_at timestamptz default CURRENT_TIMESTAMP.

**FK:** gestión, grado, paralelo, turno, autor; origen→mismo calendario; revisión→evento/usuario. **ON DELETE/UPDATE:** RESTRICT/CASCADE. **ÍNDICES:** gestión/fechas, gestión/grado/estado, cada FK, evento/fecha de revisión. **UNIQUE:** PK; permite eventos diferentes coincidentes sin decidir reglas de solapamiento.

**CHECKS:** fin>=inicio, ambas horas presentes o ausentes y fin>inicio, paralelo requiere grado, origen distinto del propio ID, motivo no vacío; estados PREALERTA/CONFIRMADO/CANCELADO/FINALIZADO y efectos de la referencia operativa. Revisión requiere motivo/objeto JSON. **HISTÓRICO:** origen/revisiones preservan cambio y cancelación. **SOFT DELETE:** NO. **AUDITORÍA:** autor/revisor/Bitácora futura.

**MODELOS:** CalendarioEvento. **SERVICES:** CalendarService/CalendarController; consulta preparada por gestión y dimensiones de planes propios o asignados. **POLICIES:** actor+permiso explícito y RegencyAccessService para lectura; la gestión editorial no está habilitada ni certificada. **RIESGOS:** aprobación de tipos/efectos, CRUD editorial, solapamientos, zona horaria local y coherencia de paralelo/grado; la FK simple no demuestra pertenencia de paralelo al grado.

**ROLLBACK:** solo tablas vacías; retirar revisiones, FK de origen y evento. **DEPENDENCIAS:** tablas base, reglas oficiales/editor responsable. **AUTORIZACIÓN PETER 1 NECESARIA PARA EJECUTAR:** SÍ.

## MIG-006 — Integridad académica correctiva

- **ARCHIVO:** `database/migrations/2026_09_30_100006_prepare_academic_integrity_checks.php`.
- **ESTADO:** CREADA_NO_EJECUTADA. **MÓDULO:** Calificaciones/LMS/Orientación. **TIPO:** ALTER.
- **VENTANAS:** V017, V030, V078–V079, V094–V095, V097, V099 y flujos de V075–V077/V092–V093.
- **PROBLEMA/SCHEMA INSUFICIENTE:** los servicios validan escalas, pero las declarations previas no incluyen esos CHECKs. La FK calificacion.cod_est tiene CASCADE DELETE y puede destruir notas cuando se elimina un estudiante.
- **TABLAS REVISADAS/ALTERADAS:** calificacion, tarea, calificacion_tarea, orientacion_respuestas y orientacion_actividades. No crea columnas ni tablas.
- **COLUMNAS:** conserva not_cal numeric(5,2), pun_max_tar/pun_obt/pun_max según migrations LMS, valor_likert y avance actuales, sin nuevos defaults/nullable. **PK/UNIQUE:** sin cambios.
- **FK:** sustituye calificacion.cod_est→estudiante por RESTRICT DELETE/CASCADE UPDATE. El nombre esperado proviene del archivo base; contrastarlo en la inspección futura aprobada, no adivinar si el despliegue difiere.
- **ÍNDICE:** `(cod_est,est_cal)` para consultas del alumno.
- **CHECKS PostgreSQL NOT VALID:** nota oficial 0..100, máximo de tarea 1..1000, puntaje LMS 0..pun_max con máximo positivo, Likert LOCAL 1..5 (no RIASEC de Aporte Ingenieril SAVP), avance 0..100. Nuevas escrituras quedan sujetas a restricción; históricos anómalos se conservan y requieren revisión antes de VALIDATE CONSTRAINT.
- **HISTÓRICO:** no UPDATE/DELETE/backfill; no se infiere cod_pas de una nota antigua. **SOFT DELETE/AUDITORÍA:** NO APLICA; servicios/Bitácora existentes.
- **MODELOS:** Calificacion, Tarea, CalificacionTarea, OrientacionRespuesta, OrientacionActividad. **SERVICES/POLICIES:** GradeService/CalificacionPolicy, EntregaService/AulaVirtualEntregaPolicy, TareaService/AulaVirtualTareaPolicy, OrientacionService/OrientacionResultadoPolicy.
- **RIESGOS:** las escalas deben corresponder a reglas aprobadas; NOT VALID no acredita limpieza histórica. Reversión restaura el CASCADE anterior, lo que reabre su riesgo; requiere revisión explícita.
- **ROLLBACK:** elimina exclusivamente CHECKs/índice propuestos y restaura la FK original; no elimina filas. **DEPENDENCIAS:** migrations base/contexto/LMS/orientación. **AUTORIZACIÓN PETER 1 NECESARIA PARA EJECUTAR:** SÍ.

## Migrations no necesarias por ahora

Actividades usan Tarea.tip_tar; no se duplica actividad_clase, que es un log. Material/Entrega/Archivo/Asistencia/CalificacionTarea ya existen. Notas oficiales reutilizan calificacion.cod_pas de la migration de contexto académico del 2026-09-28; no se crea otra. Regencia ya tiene regente_asignaciones en esa misma migration; no se crea otra asignación. Documentación reutiliza documento_inscripcion_estudiante y almacenamiento privado; no precisa otra tabla de expediente. Intereses/resultados locales reutilizan las cinco tablas de orientación.

Tutor y búsqueda Aporte Ingenieril SAVP se responden sin conservación de preguntas/respuestas: no requieren nueva tabla ni retención inventada. Un snapshot científico persistente de análisis solo se propondrá cuando Peter 1/Peter 3 aprueben su contenido, hash, versión, corte y retención; no se crea una tabla preventiva. Alertas/prevención reutilizarían seguimiento/notificaciones tras aprobar reglas; no se crea un score ni tabla de castigos. Configuración institucional/LMS requiere catálogo formal; no se crea almacén JSON genérico por si acaso.

## Orden de ejecución futura propuesto — no ejecutado

1. Aprobar entorno aislado, credenciales exclusivas y snapshot/schema de base; verificar que realmente coincida con las declaraciones. Registrar 52 migrations previas y las de migrations/aula_virtual.
2. **Resolver antes de cargar cualquier histórico** la migration `2026_06_20_144115_add_unique_constraint_to_entrega_tarea_table.php`: elimina entregas duplicadas, archivos y notas dependientes. No se modificó ni ejecutó. No hay rollback que recupere esas filas. Preparar carga sin destrucción/reconciliación autorizada.
3. Revisar contexto académico preexistente del 2026-09-28 (regente_asignaciones/cod_pas) y role_requests. Confirmar su aplicación; no aplicarlas de nuevo a ciegas.
4. MIG-006 sobre tablas existentes; revisar anomalías sin corregirlas automáticamente. Validación de CHECKs solo después de revisión aprobada.
5. MIG-001; cargar únicamente catálogos/versiones aprobadas y probar contexto/visibilidad/revisión/evidencias. Sin semilla automática.
6. MIG-002; probar pertenencia de recursos y orden/archivo, conservando recursos con unidad_id NULL.
7. MIG-003; terminar y probar productores, envío post-commit, dedupe y revocación antes de activar campana persistente.
8. MIG-004 y MIG-005, independientes entre sí una vez listas las tablas base; aprobar reglas y completar pruebas de sus flujos.
9. Pruebas transaccionales/HTTP/UX aisladas de seis actores. Activar cada flag explícitamente después de aprobación; no activar todos por haber aplicado DDL.

## Validación actual y PostgreSQL

`php -l` valida sintaxis de los seis archivos; no prueba DDL, FK, transacciones ni rollback en PostgreSQL. Pint aplica formato a archivos nuevos. Se contrastaron tipos de PK/FK y orden desde código; no se consultó schema desplegado ni se ejecutó up/down en un mock.

Base de testing existente: **NO**. PostgreSQL institucional modificado: **NO**. Migrations ejecutadas: **NO**. Seeders ejecutados: **NO**. Schema modificado: **NO**. Listo para revisión de las propuestas por Peter 1: **SÍ**. Listo para ejecución o habilitación: **NO**.

## MIGRATIONS NECESARIAS

Total: **6**. Las ventanas, motivo, columnas, PK/FK/checks/índices, histórico, riesgos, dependencias y rollback de cada una están detallados arriba.

| ID | Archivo (database/migrations/) | Tabla nueva o alterada | Tipo | Creada físicamente | Ejecutada |
|---|---|---|---|---|---|
| MIG-001 | 2026_09_30_100001_prepare_kardex_seguimiento_structure.php | seguimiento_academico, cinco catálogos, revisiones/evidencias, plan_asignatura | CREATE/ALTER | SÍ | NO |
| MIG-002 | 2026_09_30_100002_prepare_lms_units_structure.php | unidades_clase, material_clase, tarea, publicacion_clase | CREATE/ALTER | SÍ | NO |
| MIG-003 | 2026_09_30_100003_prepare_database_notifications.php | notifications | CREATE | SÍ | NO |
| MIG-004 | 2026_09_30_100004_prepare_student_academic_goals.php | metas_academicas, meta_academica_revisiones | CREATE | SÍ | NO |
| MIG-005 | 2026_09_30_100005_prepare_institutional_calendar.php | calendario_evento, calendario_evento_revisiones | CREATE | SÍ | NO |
| MIG-006 | 2026_09_30_100006_prepare_academic_integrity_checks.php | calificacion, tarea, calificacion_tarea, orientacion_respuestas, orientacion_actividades | ALTER | SÍ | NO |

## MIGRATIONS NO NECESARIAS

Se reutilizan actividades/Tarea, Material, Entrega/Archivo, Asistencia, calificación oficial/LMS, orientación local, documento de inscripción y asignaciones de Regencia. Tutor/fuentes no conservan consultas; no se crea otra tabla Aporte Ingenieril SAVP ni configuración genérica preventiva. Véase la justificación de reutilización anterior.

## ORDEN PROPUESTO DE EJECUCIÓN FUTURA

Preparación y aprobación de entorno/snapshot → revisión de migration destructiva histórica → tablas/contexto existentes → MIG-006 → MIG-001 → MIG-002 → MIG-003 → MIG-004/MIG-005 → pruebas/QA/aprobación de cada flag. No se ejecutó ninguna etapa de DB. Aplicar solo después de confirmar dependencias/FKs y revisión Peter 1; detalle del orden anterior.

## POSTGRESQL

Base de testing existente: **NO**. PostgreSQL institucional modificado: **NO**. Migrations ejecutadas: **NO**. Seeders ejecutados: **NO**. Schema modificado: **NO**. Listo para que Peter 1 revise las migrations: **SÍ**.
