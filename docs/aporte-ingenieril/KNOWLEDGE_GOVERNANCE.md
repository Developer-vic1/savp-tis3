# Gobierno del conocimiento universitario

## Propósito

El tutor no se entrena con URLs arbitrarias. Su conocimiento se incorpora mediante un corpus documental trazable: instantánea local, SHA-256, registro de fuente y reconstrucción del índice BM25. Esto evita que una página externa se presente como conocimiento validado.

## Acceso

La pantalla `/conocimiento/fuentes` exige el actor `Administrador` o `Director`. Ambos pueden ver fuentes y proponer URLs. Solo `Administrador` puede aprobar o rechazar una propuesta. Estudiantes, docentes, secretaría y regencia no tienen ruta, formulario ni API Laravel para esta cola.

## Flujo de análisis

La ficha exige título, universidad declarada, tipo documental, URL, alcance académico, versión, campus, ciudad y aporte esperado. Fecha y limitaciones son opcionales: no se inventan datos ausentes. El soporte del formulario revisa en tiempo real completitud, HTTPS, institución y dominio, fechas de calendario, longitudes y límites de líneas.

La vista compartida por ambos actores vive en `resources/views/workspaces/conocimiento-universitario.blade.php`; su comportamiento está en `resources/js/gestion-conocimiento.js` y los estilos en `resources/css/gestion-conocimiento.css`. No utiliza un archivo genérico `index.blade.php` ni scripts incrustados en la vista.

El modal de casos de prueba carga una ficha de demostración sin enviarla: dominio reconocido, duplicado, universidad extranjera e institución inconsistente. Los ejemplos no incorporan conocimiento, ni acreditan contenido o vigencia. Las respuestas de análisis caducan a los diez minutos; editar durante una solicitud cancela el resultado anterior. Los modales permiten cierre con Escape y navegación de foco con teclado. La búsqueda de fuentes tiene estado vacío; si el servicio está caído los indicadores muestran `No disponible`, no ceros ficticios.

El botón **Analizar fuente** envía la ficha al endpoint interno de análisis. El resultado muestra preparación, riesgo, dominio, controles y recomendaciones. Si la fuente puede proponerse, Laravel entrega un token temporal ligado al contenido exacto de la ficha; cualquier edición invalida el token y obliga a analizar de nuevo. El servidor repite la validación y no depende solamente de JavaScript.

Estados del análisis:

- `LISTA_PARA_PROPONER`: no hay bloqueos y puede pasar a revisión humana.
- `REQUIERE_REVISION`: faltan comprobaciones y no se incorpora automáticamente.
- `BLOQUEADA`: existe una condición incompatible, como dominio extranjero o universidad declarada que no coincide.

## Validación geográfica y de dominio

`ai-service/app/knowledge/governance.py` acepta únicamente HTTPS sin credenciales, IP ni puertos no estándar. Reconoce como dominios de universidades bolivianas UCB, UMSA, UPB, UNIFRANZ y EMI, incluyendo subdominios oficiales.

- Un dominio reconocido queda en `PENDIENTE_REVISION`.
- Un dominio `.bo` desconocido queda en `PENDIENTE_VERIFICACION_DE_DOMINIO`; no puede aprobarse ni entrar al corpus.
- Un dominio extranjero, como uno de California, se rechaza y no se guarda.

La lista es deliberadamente conservadora: un sufijo `.bo` por sí solo no prueba que una página pertenezca a una universidad. Para ampliar la cobertura se verifica primero el dominio institucional y se agrega explícitamente al registro confiable con su evidencia.

## Incorporación posterior

Cuando Administración aprueba, el estado es `APROBADA_PENDIENTE_INGESTA`, no publicación automática. El operador debe obtener la instantánea desde el dominio aprobado, calcular y registrar su SHA-256 en `data/sources/sources.json`, ejecutar `python scripts/build_corpus.py --source-id <ID> --merge-existing`, y ejecutar `python scripts/verify_sources.py`. Solo entonces los fragmentos quedan disponibles para la recuperación lexical y el tutor.
