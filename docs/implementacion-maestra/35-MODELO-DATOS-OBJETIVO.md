# Modelo de datos objetivo de SAVP-TIS3

ADR-BD-001. Fecha: 2026-10-01. **Estado: PROPUESTO, pendiente de revisión arquitectónica.** No equivale a aceptación ni autorización de ejecutar. Decisión global: **CON CAMBIOS**. Se preserva el núcleo canónico y las 105 ventanas originales; el dominio define persistencia y luego se cruza con las ventanas.

## Contexto, opciones y decisión

El checkout declara 64 tablas históricas y propone otras 14 en seis archivos no ejecutados. El volcado histórico contiene 33 tablas, una de ellas `migrations` creada por infraestructura. No conocemos el schema ni las filas actuales de la institución. El inventario une fuentes con origen explícito; no afirma que 79 tablas estén desplegadas.

Se compararon tres opciones: conservar todo y añadir tablas por pantalla; sustituir el núcleo por un schema nuevo; evolucionar las entidades actuales, añadiendo solo hechos faltantes. Se propone la tercera. Permite conservar identidad, notas históricas, matrícula y ownership del LMS, y evita duplicar Curso/Asignatura/Tarea/Bitácora. Exige conciliar datos y garantizar compatibilidad de lectores durante cada extensión. No se propone un reemplazo masivo ni un nuevo CRUD Aporte Ingenieril SAVP sobre PostgreSQL.

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

## Aporte Ingenieril SAVP: frontera de datos y resultados

Laravel controla lectura/escritura de PostgreSQL, ownership, Policy, validación y auditoría. Aporte Ingenieril SAVP recibe DTO y devuelve datos de un contrato; acceso directo DB: **NO**. AcademicAnalysisData contiene student_id, período, notas con escala, asistencia agregada, intereses declarados y especialidad; AporteIngenierilClient sustituye student_id por HMAC antes del HTTP. No envía Persona, CI, dirección, teléfono, contraseña ni contexto Kardex. La clave HMAC actual proviene de app.key: rotación altera pseudónimo y exige política de integración.

| Funcionalidad | Origen / DTO | Personal/agregado | Persistencia objetivo |
|---|---|---|---|
| Análisis académico | GradeService/contexto → AcademicAnalysisData → Peter3Contract | Pseudónimo; notas/escala por período, asistencia agregada, intereses y especialidad mínimos | FUTURO PETER3_RESULT solo si hay resultado validado que deba conservarse: operación/algoritmo/modelo/versiones, input_hash, fecha, trace_id, cobertura y resultado revisado. No tabla genérica anticipada. |
| Fuentes académicas | query/top_k/official_only → KnowledgeService | Texto de búsqueda; evitar PII en consulta | Respuesta efímera/corpus especializado; corpus/model/retrieval_version recibidos no exigen tabla Laravel nueva. Guardar favorito/cita solo si se aprueba necesidad. |
| Asistente de estudio | question/schema_version → TutorService | Texto libre potencialmente personal; no historial automático | NO_PERSIST por defecto; SESSION efímera opcional; HISTORY_OPT_IN futura requiere consentimiento/retención y contrato. Sin tabla de chat ahora. |
| RIASEC/carreras/cognitivo | Instrumento aprobado y escala específica aún no equivalente | Respuestas sensibles solo por consentimiento/contrato mínimo | FUTURO, no convertir respuestas locales ni guardar diagnóstico inferido del promedio. |

Validar contratos, autorización, resultado insuficiente y fallback; Aporte Ingenieril SAVP no dicta Role, sanción, nota oficial, evento ni matrícula. Laravel revisa decisión humana antes de convertir una recomendación en hecho. Las tablas de orientación local se extienden cuando el concepto coincide; no se duplica `orientacion_resultados` con `peter3_results` universal.

## Plan de aplicación futura y testing

**Hoy:** base aislada aprobada NO EXISTE. No se crea ni se conecta. No se ejecutan migrations, seeders, SQL, rollback ni pretend. La suite existente fuerza SQLite :memory: y DB_URL vacío; se conserva esa protección. Los resultados previos de tests no prueban PG ni estas propuestas.

1. Revisión arquitectónica de este ADR, ocho paquetes propuestos, reglas CI/perfiles, instrumentos, Kardex/eventos, privacidad y retención. Peter 1 autoriza expresamente el siguiente paso, sin credenciales institucionales.
2. Definir servidor aislado y base `savp_tis3_testing`, usuario sin privilegios ni acceso al servidor institucional. Verificar host/db/url/schema allowlist y APP_ENV testing mediante guardas independientes. No heredar .env, DB_URL o caches productivos. No cambiar el phpunit actual para usar PG indiscriminadamente.
3. Revisar las 52 migrations históricas y el ledger, incluidas rutas aula_virtual, limpieza destructiva de entregas y actualización auth_provider. Aprobar manifiesto de bootstrap limpio o replay corregido explícito en entorno aislado. No importar el volcado con datos ni borrar migrations históricas.
4. Crear schema de prueba base autorizado; fixtures/factories sintéticos con Persona y User.cod_usu/cod_per y catálogos mínimos. UserFactory actual usa name y Team inexistente; corregir solo en fase autorizada, sin datos de personas reales. Seeders institucionales no se ejecutan por defecto.
5. Aplicar futura corrección estructural aprobada MIG-006A (incluye compatibilidad tokens/FK/índices priorizados); después los checks MIG-006B con datos sintéticos inválidos y válidos. Perfilado de datos reales requerirá otro acceso autorizado de solo lectura; no declararlo hecho ahora.
6. Catálogos/seguimiento/revisiones/evidencias MIG-001 corregida; unidades y enlaces MIG-002; notificaciones MIG-003 corregida; metas/revisión MIG-004; eventos/revisión MIG-005 corregida; edición orientación MIG-007. Cada paquete debe conciliar preexistencia y dependencias; no usar CREATE para sobrescribir una tabla descubierta.
7. Pruebas PG de PK textual/morphs, UNIQUE y NULL, FK cruzadas, rangos CHECK, histórico sin cod_pas, actualización de fila histórica bajo NOT VALID, concurrencia de códigos/entrega/notas, rollback vacío y rechazo con historia, append-only, scope RBAC por seis actores y revocación. API fakes para Aporte Ingenieril SAVP y pruebas de ausencia de PII en payload/logs.
8. Pruebas de lectura/escritura real y UX autenticada; habilitar flags solo con evidencia y contrato aprobado. Un archivo de migration o php -l nunca certifica PASS de una ventana.

Paquetes objetivo: MIG-001, MIG-002, MIG-003, MIG-004, MIG-005, MIG-006A estructural, MIG-006B escalas y MIG-007 orientación versionada: **8 propuestas de aplicación futura**, derivadas de siete necesidades de dominio/integridad; no son ocho archivos creados. Los seis archivos físicos anteriores se preservan como evidencia. Al revisar, decidir nombres y sustitución de MIG-006 sin registrar dos veces sus constraints. Cambios de CI, perfiles, cupos, períodos anuales y resultados científicos futuros pueden requerir paquetes adicionales según perfilado/contrato: no se incluyen a ciegas en el conteo cerrado de esta fase.

## Consecuencias y revisión requerida

Se conservan nombres/IDs de dominio y compatibilidad; se agregan solo hechos persistentes identificados. El costo es resolver versiones, propiedad de cambios y restricciones que el schema actual no garantiza. No se afirma modelo institucional final aceptado sin revisión de reglas y datos. Tablas candidatas de retiro: ninguna con evidencia suficiente. Columnas candidatas se preservan. Acciones siguientes: revisión del responsable de arquitectura/Peter 1 y aprobación del plan aislado. **DETENER implementación dependiente de BD hasta esa revisión.**
