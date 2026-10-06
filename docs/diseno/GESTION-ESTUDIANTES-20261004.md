# Estudiantes y ayuda ante errores

## Alcance
Interfaz en Fusion_Sistema. Se mantienen rutas, permisos, identidad visual y registros existentes. Sin migraciones, seeders, escrituras de registros, envío de mensajes, commits ni pruebas automatizadas.

## Campos y navegación
Persona aporta identidad, nacimiento y contacto. Estudiante aporta RUDE, vinculación, procedencia, especialidad registrada y situación académica. Inscripción aporta gestión, curso, paralelo, fecha y situación. El acceso de la cuenta se consulta por separado de la situación académica; no se borra trayectoria al consultar usuarios inactivos.

Vistas: cursos, especialidades, tarjetas, lista y tabla. Los exploradores de cursos y especialidades están plegados inicialmente y se abren mediante botón. Todas las vistas mantienen filtros; se puede ampliar por situación de inscripción, académica, vinculación y procedencia, y ordenar por apellidos, fecha de registro o RUDE. Ficha lateral con trayecto, historial de inscripciones completo disponible, identidad/contacto y cuenta. No se publican códigos internos.

## Indicadores
Mapa de burbujas por curso/paralelo, anillo de inscripción y mosaico proporcional de especialidades, con cantidades reales de la selección completa, no solo la página. Cada estudiante se cuenta una vez en el gráfico y se toma su última inscripción de la gestión. Las especialidades siguen estudiante.cod_esp; no se asigna una automáticamente. Datos leídos: 612 estudiantes, 600 inscripciones ACTIVA, siete especialidades con estudiantes. Se conservan cifras globales diferenciadas de gráficos filtrados.

## Correcciones de lectura
La relación antigua de calificaciones requería calificacion.cod_ins, ausente en la base consultada. Se retiró esa carga y conteo de este directorio, sin editar el modelo oficial; la pantalla no es un historial de notas. La fecha real de inscripción es fei_ins. Se reconocen ACTIVA y ACTIVO como inscripciones vigentes. Curso y paralelo se filtran en una misma inscripción y gestión. Los helpers reciben Persona canónica, conservando los modelos de consulta existentes.

## Formularios
Selector, calendario y paginación institucionales reutilizados. Validación inmediata, errores junto a campos y vista previa. Fecha de inscripción: formato válido, desde 1900-01-01 y hasta hoy; bloqueo de fecha futura en interfaz y regla de servidor. Se mantienen operaciones y bitácora existentes. El catálogo de procedencias está vacío: se explica el requisito y el formulario permanece bloqueado si no se completa. No se inventaron opciones ni registros. Las escrituras de inscripción conservan el flujo previo; la evolución del contrato oficial pertenece al chat de base de datos.

## Errores institucionales
Respuesta HTML común para 400, 401, 403, 404, 405, 408, 419, 429, 500, 502, 503 y 504, conservando el código HTTP. No se muestran consultas ni trazas. La pantalla funciona sin cargar el shell que podría haber fallado. Recupera sesión web para personalizar el 404, cuyo middleware normal no recorre una ruta inexistente; tolera fallo de lectura de identidad. Respuestas JSON/Livewire de servidor reciben mensaje genérico. No se cambian permisos ni decisiones de acceso.

Regreso por última página válida de la misma pestaña/origen, referencia del mismo origen o inicio; no se sustituye la última página por otra de error. Soporte: +59175836807, enlace wa.me. Saludo calculado por hora local del navegador, sin mencionar zona horaria. Mensaje inicial con nombre completo, roles, página sin parámetros y tipo/código de error. Texto editable, actualizado en el enlace, bloqueado si está vacío. No se transmite nada automáticamente; el usuario abre WhatsApp y decide enviarlo.

## Verificación realizada
Compilación Vite y lint PHP. Inspección manual en navegador de 404 y 403 reales, identidad/rol, regreso a estudiantes y modificación del mensaje de WhatsApp. Directorio, filtro conjunto 4to/C (25 estudiantes), ficha lateral y trayectoria, grupos plegables. Fecha futura manual en formulario: error visible y botón deshabilitado, cancelada sin guardar. Revisión móvil de ficha y controles, y modo oscuro del directorio. No se ejecutaron pruebas automatizadas ni cambios de base de datos. No se indujo un 500 en producción: su respuesta se inspeccionó en código.

## Incidencia del formateador
Pint con --dirty aplicó formato también a archivos ajenos a esta pantalla, pese a indicar rutas. Normalizó espacios, finales de línea y organización de imports; no se hizo un rollback contra cambios concurrentes porque no existía una copia anterior fiable de esos archivos. Se evitó repetir el comando global.
