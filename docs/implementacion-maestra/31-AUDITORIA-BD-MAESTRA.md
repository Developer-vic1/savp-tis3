# SAVP-TIS3 — Auditoría maestra de base de datos

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

| ID | Tabla / columnas | Consulta que lo justifica |
|---|---|---|
| IDX-001 | `inscripcion_estudiante(cod_gea,cod_cur,cod_par,cod_tur,est_ins,cod_est)` | GradeService.teacherContext/save; CursoVirtualService; InscripcionAcademica |
| IDX-002 | `plan_asignatura(cod_doc,est_pas,cod_gea,cod_cur,cod_par,cod_tur)` | CursoVirtualService.teacherQuery; CalendarService |
| IDX-003 | `calificacion(cod_pas,est_cal,cod_pev,cod_est)` | GradeService.gradesForTeacherCourse; InstitutionalQueryService |
| IDX-004 | `orientacion_actividades(cod_est,created_at DESC)` | OrientacionService.resumen/actividadExistente |
| IDX-005 | `persona(LOWER(ema_per))` | PersonaInteligente.buscarCoincidencias |
| IDX-006 | `tarea(cod_cla,est_tar,fec_lim_tar)` | CalendarService.query |
| IDX-007 | `personal_institucional(cod_per,est_pin)` | RolePermissionService.assignActor; CursoVirtualService.docenteDeUsuario |
| IDX-008 | `docente(cod_pin,est_doc)` | RolePermissionService.assignActor; CursoVirtualService.docenteDeUsuario |
| IDX-009 | `gestion_academica(ani_gea)` | GestionAcademicaInteligente.existeAnioGestion/existeGestionAnteriorSinCerrar; Gestión Académica |
| IDX-010 | `plan_asignatura(cod_gea,cod_cur,cod_par,cod_tur,cod_asi,cod_doc)` | PlanAsignaturaInteligente.analizar; InstitutionalQueryService |

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
