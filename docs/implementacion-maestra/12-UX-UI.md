# UX/UI actual

Un shell y tokens existentes: layouts.app, aula-virtual.layouts.app, actor-menu, app.css y app.js. app.css preservado. Navegación móvil/backdrop/Escape, grupos por permiso, búsqueda de módulos, breadcrumbs y tabla overflow preparados.

StudentDrawer carga ficha contextual real con búsqueda autorizada, Escape, retorno de foco y tab trap preparados. Calendarios y listas tienen filtros/limpiar/empty; formularios de tareas/metas/tutor validación/loading. Sesiones status/error/warning y toasts usan textos seguros y tokens.

SweetAlert usa colores CSS variables. Chart.js destruye instancias del canvas reemplazado y actualiza tema; no se agregan gráficos de datos inventados. No todos los modales legacy fueron uniformados.

Vite y Blade validan construcción, no visual. No había sesión autenticada disponible; no hay capturas ni PASS responsive/foco/contraste de seis actores. QA requerida: 375/768/1440 px, light/dark, teclado, validación, offline, empty y errores reales en entorno aislado.

asistencia-inteligente diferencia bloqueos, advertencias, sugerencias, coincidencias y completitud con clases/tokens SAVP. Los paneles ricos anteriores permanecen. Borrador Kardex y notas oficiales Docente usan 500 ms; Personas conserva 300 ms. Los nuevos drawers de Secretaría preparan Escape, tab trap y retorno de foco; no se certifica el comportamiento visual por tests de servidor.
