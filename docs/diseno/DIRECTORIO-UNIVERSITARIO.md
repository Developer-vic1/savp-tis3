# Directorio universitario nacional

La presentación para Administración se centra en organizar fuentes universitarias, mientras que la del estudiante conserva la exploración de su futuro. El avatar documental local acompaña la introducción, comparte los tokens institucionales y no representa una persona ni una memoria conversacional.

## 1. Propósito y organización

La pantalla `/conocimiento/directorio-nacional` conserva el acceso de Administrador y Director con `conocimiento.ver`. El mismo directorio tiene versión estudiantil en `/estudiante/directorio-nacional`, con perfil estudiantil vinculado y sin acciones administrativas. Consultar filtros, gráficos y fichas no genera un estudio ni modifica datos.

La interfaz ordena la consulta en cuatro partes: orientación inicial, cobertura del registro, filtros y fichas universitarias. Cada ficha separa identidad web, vigencia legal, programas documentados, sedes del registro histórico y procedencia. Las acciones del Aporte solo aparecen para el administrador autorizado en la ficha de UNIFRANZ.

## 2. Gráficos y alcance de los datos

La cifra de instituciones representa el universo nominal de la guía ministerial de 2016. No se presenta como número actual de universidades vigentes. La identidad del sitio web contrastada y la vigencia legal actual son estados diferentes.

El gráfico de identidad web cuenta las instituciones del registro completo con dominio contrastado y sin cuarentena, las que tienen identidad pendiente y los sitios en cuarentena. Un sitio en cuarentena no se cuenta como fuente utilizable por tener un dominio conocido. Las cantidades y etiquetas se presentan como texto accesible; el anillo es decorativo.

El gráfico departamental cuenta presencia en los departamentos del registro. Una institución puede aparecer en varios departamentos; sumar las barras duplicaría instituciones. Cada barra es un enlace que aplica el departamento y conserva los otros filtros. El gráfico describe el registro completo, mientras las fichas representan la consulta filtrada. Ambos ámbitos se indican en pantalla.

Los programas son los publicados con evidencia en el catálogo recibido. Los documentos con texto extraído y enlaces pendientes proceden de la recolección nacional; no equivalen a carreras aprobadas ni a documentos incorporados al estudio. Si el servicio está indisponible se muestra recuperación y se conservan filtros, sin dibujar ceros ficticios.

## 3. Filtros, fichas y recuperación

Se conservan los seis filtros GET existentes: `buscar`, `departamento`, `regimen`, `duracion`, `economia` y `orden`. Nombre, departamento y régimen son visibles; duración, economía y orden se agrupan en un plegable institucional que se abre cuando esos criterios están aplicados. Los selectores reutilizan `x-selector-institucional` y su navegación por teclado. Las etiquetas de filtros permiten quitar uno sin borrar los demás.

La consulta conserva validación del controlador, estado de envío y errores junto a los campos. Un resultado vacío explica la cobertura parcial y ofrece ampliar o limpiar filtros. El criterio de montos verificados no transforma los costos pendientes en cero.

Las fichas muestran programas de esta consulta, documentos extraídos y enlaces pendientes. Las sedes se consultan en un plegable propio con ciudad, departamento, fecha y fuente del registro; la sede histórica no se atribuye como oferta actual de todos los programas. No hay una relación campus/programa inferida en la vista. Un enlace institucional en cuarentena no se ofrece como acción hacia un portal académico confiable.

La presentación limita a doce fichas por página mediante Alpine, conservando los resultados completos recibidos del servidor. Cambiar página dirige el foco al título de resultados y usa desplazamiento inmediato; no genera nuevas solicitudes ni pierde los filtros. Sin JavaScript, las fichas permanecen disponibles completas.

## 4. Sistema visual y accesibilidad

Se conserva shell SAVP, Figtree, Phosphor Duotone, tokens `--ui-*`, selectores y plegables compartidos. Los gráficos HTML/CSS responden automáticamente al tema sin paletas paralelas ni otra biblioteca. Se utilizan figuras con títulos, valores visibles y enlaces con nombres accesibles; el color complementa el texto de cada estado.

La distribución pasa a una columna en pantallas estrechas y mantiene acciones, nombres largos y foco visible. El movimiento decorativo se limita a hover de bordes y fondo dentro de `prefers-reduced-motion: no-preference`. La paginación no emplea animación.

## 5. Seguridad y necesidades del contrato

Los nombres, títulos, limitaciones y demás datos documentales se imprimen con escape de Blade; no se usan `x-html`, HTML crudo ni manejadores concatenados. Las URLs externas pasan por `DirectorioNacional::enlacePublico`, conservando HTTPS sin credenciales, y abren con `noopener noreferrer`. Los parámetros de enlaces de filtros usan `http_build_query` y escape de atributos. La acción administrativa mantiene la condición del actor y `@can`.

El endpoint actual no acredita oferta vigente por sede, autorización legal actual ni montos comparables. Esos estados no se fabrican en la interfaz. El catálogo trae datos extra de campus históricos, pero su contrato de respuesta todavía necesita validar formalmente nombre, ciudad, departamento, año, estado y URL de evidencia. La vista escapa esos valores y no los considera acreditación actual. Para mostrar oferta por sede se requiere un contrato específico con fuente, fecha y estado comprobados por carrera/campus; no basta el departamento de la institución.

## 6. Verificación

Las pruebas frontend cubren paginación y límites, resultados vacíos, devolución de foco, conservación de filtros, selectores existentes, alcance histórico y ausencia de HTML dinámico sin escape. Las pruebas PHP existentes cubren acceso de actores, perfil estudiantil, filtros, indisponibilidad y rechazo de enlaces inseguros. La compilación de vistas y recursos comprueba integración, pero la revisión visual de móvil/tema oscuro debe declararse por separado de esos checks.

El 11/10/2026 se inspeccionaron gráficos, introducción con avatar y búsqueda de UNIFRANZ en la sesión administrativa real. La búsqueda devolvió una institución; sus sedes conservaron la etiqueta histórica. La vista estrecha mostró 487 px de contenido frente a 507 px de ventana observada, sin desbordamiento horizontal. Se comprobó tema oscuro y se restauraron tema claro y tamaño habitual. Las capturas están referenciadas en [Gestión del conocimiento](GESTION-CONOCIMIENTO.md).
