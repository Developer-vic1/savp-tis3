# Gestión del conocimiento: recorrido administrativo

## Entrada y prevención

`/conocimiento/fuentes` conserva el shell institucional, los tokens `--ui-*` y los controles existentes. Su recorrido visible es URL oficial → comprobación de institución y duplicados → vista previa de datos → incorporación supervisada. Los pasos muestran únicamente condiciones confirmadas de la ficha actual: un formulario completo no equivale a evidencia verificada ni a una fuente publicada.

La conexión se comprueba al cargar, sin abrir un modal ni exigir un botón previo. Su estado abre el detalle diagnóstico; solo un fallo permite reintentar desde ese detalle. Los campos y resultados recibidos se conservan. La comprobación de una URL muestra una animación durante la petición real, sin porcentajes inventados y respetando movimiento reducido.

## Biblioteca y asistente

Los filtros separan fuentes universitarias y ministeriales por autoridad/institución registrada, con conteos reales. La biblioteca agrupa las fuentes universitarias por institución y sede, después por carrera y finalmente por publicación: malla y perfil de una misma carrera se leen juntos y conservan trazabilidad propia. Las fuentes ministeriales se presentan fuera de las carreras universitarias. La búsqueda filtra institución, carrera y documento sin convertir propuestas o fuentes pendientes en evidencia activa.

La ficha muestra una guía breve junto a la URL y el siguiente paso según los datos presentes. Sus controles explican qué falta al expandirse; no ocupan una tarjeta permanente cuando aún no hay contenido. El panel visible de permisos se retiró porque el rol ya figura en el shell; el servidor mantiene las comprobaciones de actor y permiso en cada operación.

El indicador superior cuenta dominios web contrastados por el servicio, no universidades vigentes. Una institución puede tener más de un dominio; este número no se compara directamente con el conteo de instituciones del directorio nacional.

El asistente documental saluda con `usuarioNombre` provisto por el controlador y presenta respuestas por párrafos, títulos y listas. Todo texto remoto se coloca con `x-text`; no se interpreta HTML. Los enlaces se validan como HTTPS sin credenciales. Las preguntas sugeridas son neutrales respecto de la universidad. La conversación se conserva solo en memoria de la página; no se promete recuerdo entre sesiones. Un fallo conserva pregunta, historial y última respuesta confirmada.

La pantalla comienza en **Lenguaje claro**. El administrador puede alternar **Detalle técnico** desde el encabezado y el asistente; ambos controles comparten el estado de presentación y no modifican respuestas, documentos ni evidencias del servidor. Los identificadores de citas se ocultan en el texto claro únicamente cuando corresponden a fuentes conservadas en la lista visible de documentos. Las referencias desconocidas se mantienen. La falta de evidencia se explica en ambos modos; proveedor/versión e identificadores se muestran solo cuando el servidor los informa y el modo técnico está seleccionado.

Cuando retirar una cita conocida deja únicamente «Fuente:», se omite esa línea incompleta. El texto significativo y las referencias desconocidas permanecen. La biblioteca, la cola de incorporación y el detalle muestran etiquetas como «Documento revisado», «Malla curricular» y «Aprobada, pendiente incorporación» en lenguaje claro; el modo técnico conserva los códigos originales. Los tipos o estados no reconocidos no se traducen a una afirmación inventada.

`x-avatar-documental` dibuja un libro con rostro mediante SVG propio y colores institucionales. Su tamaño normal es 96 px y `compacto` reduce a 56 px; acepta clases y atributos del contexto. `kg-avatar-consultando` anima las líneas del libro únicamente mientras hay una consulta; `kg-avatar-escuchando` conserva un estado de reposo. La animación se desactiva con movimiento reducido. El avatar es decorativo (`aria-hidden`) y no promete emociones, memoria ni decisiones autónomas.

## Vigencia y actualización

El panel de actualización requiere `conocimiento.revisar`. Consulta cadencias y estados al cargar mediante `conocimiento.actualizaciones.index`. Cada fuente permite seleccionar manual, mensual, semestral o anual; `Guardar frecuencia` utiliza `conocimiento.actualizaciones.configurar` y `Revisar publicación ahora` utiliza `conocimiento.actualizaciones.revisar`.

La autorización de interfaz utiliza `$canReview` calculado por el controlador, que conserva la restricción del actor. No se sustituye por una consulta de permiso aislado que pudiera permitir una petición administrativa desde el actor Dirección. `Último intento de revisión` distingue un intento no disponible de una comprobación exitosa.

La selección local no cambia la cadencia confirmada hasta recibir el estado del servidor. El primer control establece una referencia para comparar futuras revisiones. Un cambio detectado necesita revisión; esta pantalla no lo publica automáticamente. La próxima fecha mostrada pertenece a la respuesta del servidor y no demuestra que exista un trabajador programado ejecutándola. Los fallos mantienen el último estado y orientan a consultarlo antes de reintentar.

## Verificación

Las pruebas frontend cubren clasificación de fuentes, pasos reales, conexión automática sin modal, renderizado textual de respuestas y persistencia confirmada de cadencias. La revisión visual de móvil, tema oscuro y estados de servidor corresponde a la integración ejecutada en navegador; inspección y pruebas unitarias no la sustituyen.

### Revisión ejecutada el 11/10/2026

El saludo utiliza el primer nombre de `nom_per` y el primer apellido de `ape_pat_per` del usuario autenticado; si solo consta apellido materno, conserva ese apellido disponible. No concatena segundos nombres ni utiliza el correo como nombre. Para la cuenta revisada se muestra «Victor Asturizaga».

Se recorrió la sesión real de Administración: carga automática del servicio, separación universitaria/ministerial, asistente con nombre autenticado y avatar, modos claro/técnico y respuesta del primer semestre UCB/Sistemas con cinco materias y publicación oficial. El detalle técnico conserva la referencia; el modo claro conserva nombres, institución y enlaces. La referencia de Psicología/UNIFRANZ se guardó con frecuencia mensual y se confirmó su próxima fecha.

El directorio se inspeccionó visualmente en escritorio y pantalla estrecha, con búsqueda de UNIFRANZ, gráficos y tema oscuro. El asistente se inspeccionó en modo oscuro estrecho: ancho de panel/contenido medidos en 469/469 px, sin desbordamiento horizontal. La herramienta de navegador escaló el viewport solicitado; no se certifica un dispositivo físico de 390 px. Se restauró la presentación habitual en claro.

La revisión de consola detectó y corrigió una lectura prematura de `extraction.facts` cuando todavía no había documento. Después de recargar, no se registraron nuevos errores/advertencias en esa carga. Un fallo transitorio de captura al cambiar tamaño se resolvió para la inspección; no se tomó como fallo del servicio documental.

Validación focal final: 108 pruebas Python, 76 pruebas PHP (419 aserciones), 49 pruebas de interfaz y compilación Vite/Blade. Después del ajuste de calendario a Bolivia se repitieron sus tres pruebas (14 aserciones). Estas cifras no representan una ejecución completa de todas las pruebas del repositorio.

Capturas locales: `output/playwright/asistente-educativo-20261011.png`, `asistente-materias-20261011.png`, `directorio-avatar-20261011.png` y `directorio-movil-oscuro-20261011.png`. La conversación se dejó abierta para revisión del usuario.

### Comprobación focal posterior del 11/10/2026

Se recargó la sesión real del Administrador después de compilar la vista: desapareció la tarjeta de permisos, apareció la guía contextual junto a la URL y la biblioteca agrupó las dos publicaciones de Ingeniería de Sistemas UCB bajo una sola carrera. El buscador y las categorías conservan los 17 documentos activos y separan los ministeriales. Se probó una búsqueda sin resultados: el mensaje y los contadores bajaron a cero y se recuperaron al limpiar el campo. El asistente respondió las dos consultas reportadas por el usuario: para UNIFRANZ mostró únicamente dos carreras documentadas, y para sus materias de Sistemas mostró los nueve semestres verificados con enlaces a las páginas 3 y 4 de la malla. La sesión independiente de Estudiante mostró el directorio reactivo con una coincidencia para UNIFRANZ. La biblioteca se inspeccionó en modo claro y oscuro en escritorio y con emulación de ancho móvil; se restauraron el tema y el tamaño inicial. Esta inspección corresponde al navegador local; no equivale a verificar todos los dispositivos ni toda la oferta nacional.

La verificación de código de esta revisión pasó 58 pruebas Python, 54 pruebas frontend, 41 pruebas PHP, Ruff, mypy, compilación Vite y caché Blade. Las pruebas PHP de integración que requieren una base aislada no se ejecutaron sobre la base institucional.
