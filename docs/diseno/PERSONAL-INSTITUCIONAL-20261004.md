# Personal institucional

La pantalla consulta todos los integrantes de `personal_institucional`, no solamente los docentes. Conserva la ruta y el permiso `Personal_Institucional`. Gestión de docentes conserva su ruta y vista propia.

El panel presenta cargos registrados, acceso de la cuenta vinculada y contacto. Directorio y tabla usan selector y paginación compartidos. No muestra códigos de Persona, Usuario o Personal; siguen siendo claves internas para operar.

La ficha reúne vinculación, historial disponible y documentos. La pestaña de carga académica aparece únicamente cuando el integrante tiene perfil docente. Sus acciones de especialidad y asignación reutilizan las operaciones existentes, sus límites y bitácora. El formulario ofrece validación inmediata de horas enteras y disponibles y vista previa, sin sustituir validación del servidor. No incorpora edición de fechas; las fechas de nacimiento en Personas conservan el bloqueo de valores futuros.

Indicadores: cargos de toda la institución; proporción de horas de materias y especialidades; gráfico de puntos por materia/especialidad; mapa de horas por docente. Horas se calculan sobre asignaciones activas del personal filtrado. No se alteran las cargas reales que exceden el límite actual: se identifican para revisión. Tener 19–24 horas no equivale a exceder el límite de 24.

El checkout actual dispone del cargo en `car_pin`. Cuando esté listo el contrato oficial de cargos/vínculos, debe conectarse su fuente al selector y al resumen. Historial y documentos consultan las relaciones oficiales únicamente cuando sus tablas están disponibles. No se inventan documentos ni antigüedad laboral a partir de la fecha de creación.

Cambios de presentación en `resources/views/livewire/admin/personal/`, `resources/css/personal-institucional.css` y `resources/js/personal-institucional.js`. Cálculos de lectura en `IndicadoresPersonal`. Se conserva el Support `DocenteInteligente` para el diagnóstico de especialidad y su contrato `puede_guardar`, sin aplicar sugerencias automáticamente.

Por instrucción del usuario no se crean ni ejecutan pruebas para este rediseño. Se compilan recursos y se revisan interacciones en navegador sin registrar asignaciones, editar cuentas ni migrar PostgreSQL.

El estado visible procede exclusivamente de `persona.usuario.est_usu`, sin selector de estado docente o institucional. La edición profesional conserva `est_doc`, `est_pin`, asignaciones y trayectoria; registra la descripción en bitácora. No representa presencia en tiempo real. La asignación conserva los bloqueos de la operación y comprueba además que la cuenta vinculada esté activa.

Carga académica reutiliza `x-horario-institucional`: semana y agenda diaria, turno, detalle de clase y coincidencias, desde bloques reales del horario vigente. Conserva debajo todas las cargas asignadas, incluidas las que aún no tienen horario. El componente es reutilizable y usa tokens del tema.

El panel usa dos columnas de indicadores independientes, sin estirar tarjetas. La tabla ocupa toda su superficie y mantiene desplazamiento horizontal dentro del contenedor en móvil. Los nombres personales se muestran en mayúsculas sin transformar la base de datos.

Filtros principales: búsqueda y cargo. Ordenación por apellido, nombre o fecha del registro (ambas direcciones), con clave interna de desempate para paginación estable. Más filtros combina tipo de asignación, materia, especialidad, curso, rango de horas y contacto pendiente. Los filtros académicos limitan a docentes, relacionan curso y materia/especialidad en la misma asignación activa; no amplían el ámbito autorizado. Etiquetas visibles permiten retirar cada filtro, y el contador conserva el contexto al plegar el panel. No se confunde registro más antiguo con antigüedad laboral.

El horario permite consultar por plantilla/período y abre la plantilla aplicada, evitando superponer el horario de invierno y el de retorno. Las coincidencias se evalúan dentro de una misma plantilla. Las variantes históricas siguen disponibles, sin eliminar registros.

La opción Comprimir bloques agrupa sesiones consecutivas de la misma asignación, día, turno, curso, paralelo y aula, incluyendo únicamente recreos registrados que cubren exactamente la pausa entre bloques. No agrupa solapamientos, clases intermedias ni huecos sin respaldo. El detalle conserva cada intervalo original y cada recreo; la vista sin compresión sigue disponible. La preferencia es compartida por el componente y no altera datos.

Revisión visual realizada: orden inverso por apellidos, filtro de más de 24 h con 18 resultados, materia, ficha real de Daniela, 24 bloques reducidos a 12 sesiones, retorno a vista sin comprimir, agenda a 390 px y contraste de ficha en modo oscuro. Compilación Vite y sintaxis PHP correctas. No se ejecutaron pruebas automatizadas ni se guardaron cambios en registros reales.

Vistas adicionales Lista y Carga horaria conservan búsqueda, filtros, orden y paginación. Carga horaria desglosa horas y cantidad de asignaciones activas de materias/especialidades; personal sin perfil docente conserva su contexto institucional sin inventar horas laborales. Las cuatro vistas tienen acceso directo a horario/carga cuando existe perfil docente. Lista y Carga incluyen además ficha e historial/documentos. La preferencia de vista persiste, y abrir Carga directamente selecciona la sección de la ficha mediante una lista permitida en servidor.

Revisión de vistas adicionales: renderizado de Lista y Carga horaria, personal administrativo sin carga inventada, acceso directo de Daniela a la sección de horario, escritorio a 1280 px y contraste oscuro. Sin errores nuevos de consola; build correcto. No se ejecutaron tests ni se guardaron datos reales.
