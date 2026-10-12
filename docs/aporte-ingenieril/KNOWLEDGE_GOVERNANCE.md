# Gobierno del conocimiento universitario

La interfaz administrativa presenta la biblioteca en lenguaje educativo y separa universidad/normativa. El asistente documental y su entrenamiento local están descritos en [Conversación documental](CONVERSACION-DOCUMENTAL.md); las frecuencias, detección de cambios y notificaciones en [Actualizaciones de fuentes](ACTUALIZACIONES-FUENTES.md). La revisión de controles está en [Seguridad del conocimiento](SEGURIDAD-CONOCIMIENTO.md).

La ejecución de UNIFRANZ y sus tiempos, documentos y consultas de control están en [Resultado de preparación documental](RESULTADO-PREPARACION-UNIFRANZ.md). El índice preparado conserva el estado pendiente de revisión antes de publicación.

La revisión posterior de materias por regiones, el clasificador supervisado y las carpetas por semestre se documentan en [Mejora de lectura curricular](MEJORA-LECTURA-CURRICULAR.md). El nuevo análisis trabaja con las copias guardadas y conserva el original.

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

La cola de Administración sigue cuatro etapas: **propuesta → aprobación → extracción preliminar → revisión de evidencia**. Solo un administrador con `conocimiento.revisar` puede pedir **Extraer vista previa** para una propuesta `APROBADA_PENDIENTE_INGESTA`. Laravel consulta de nuevo el estado antes de llamar al servicio Python y comprueba que el identificador y la URL de la respuesta pertenezcan a esa propuesta. No acepta una URL enviada por el navegador. Una aprobación no publica la fuente.

La extracción obtiene una instantánea limitada del documento público aprobado, conserva su SHA-256 y deja sus fragmentos en el área de preparación de Python. La pantalla muestra recuentos de páginas, fragmentos y datos candidatos; cada materia, duración o nombre de carrera lleva texto de respaldo y ubicación cuando existe. Los enlaces a otras mallas o carreras son **candidatos sin leer**: se comprueban y proponen como fuentes separadas. Una respuesta `NO_DISPONIBLE` informa el motivo y permite reintentar; no equivale a cero materias. El contrato indica `NO_GENERADOS` para embeddings mientras no haya vectores verificables. Ni la extracción ni un enlace detectado modifican el catálogo o la comparación del estudiante.

Para incorporar el material revisado al corpus publicado, el operador debe verificar la instantánea y su procedencia, registrar la fuente y su huella en `data/sources/sources.json`, ejecutar `python scripts/build_corpus.py --source-id <ID> --merge-existing` y `python scripts/verify_sources.py`. Solo después de esos controles la recuperación lexical y el tutor pueden usarla. Los costos, becas, duración o plan de estudios se muestran únicamente si una fuente oficial vigente los declara con sede y fecha; ausencia de un dato no se rellena por analogía entre universidades.

### Puntos de entrada para la revisión inicial

Estas páginas oficiales son **candidatas para comprobar en el formulario**; su presencia en esta lista no significa que hayan sido propuestas, aprobadas, extraídas ni incorporadas. Registrar por separado cada malla publicada, con fecha y sede, después de comprobar su URL y título:

| Institución | Página inicial | Buscar y comprobar |
| --- | --- | --- |
| UMSA | <https://transparencia.umsa.bo/es/ofertaacademica> | Carreras ofertadas y enlaces de facultad o plan. |
| UNIFRANZ | <https://unifranz.edu.bo/carreras/psicologia/> | Psicología, duración y malla vigente por sede. |
| UCB | <https://lpz.ucb.edu.bo/pregrado/psicologia/> | Psicología y plan de La Paz. |
| UPB | <https://www.upb.edu/postula-a-la-upb> | Oferta y páginas académicas por campus. |
| EMI | <https://www.emi.edu.bo/universidad/licenciatura/ingenieria-de-sistemas> | Sistemas y malla; si el servidor restringe el acceso, registrar `NO_DISPONIBLE` y comprobar manualmente. |

Para la comparación económica, levantar además los documentos oficiales de aranceles, becas y costos de cada universidad cuando sean públicos y actuales. La ausencia de tarifas publicadas se comunica como **dato por verificar**; nunca se deduce un precio de que la universidad sea pública o privada.

## Comprobación del flujo del 08/10/2026

Se mantuvieron sesiones independientes de Administrador (`localhost:8000`) y Estudiante (`localhost.:8000`). Se comprobó una cuenta estudiantil existente y vinculada; no se modificaron credenciales ni se añadieron tablas.

La ficha oficial de Psicología de UNIFRANZ, disponible en Cochabamba, El Alto, La Paz y Santa Cruz, se propuso y aprobó desde Administración. Su instantánea revisada produjo 31 fragmentos, el nombre de la carrera y la duración publicada de cuatro años. Se incorporó únicamente ese perfil al corpus léxico y al catálogo de La Paz, conservando fecha de edición y perfil de ingreso sin confirmar. La malla PDF permanece separada en revisión: ocho páginas, seis con texto digital, algunas líneas con codificación dañada y dos páginas que requieren lectura visual/OCR. No se trasladaron materias, aranceles o referencias Holland de otra universidad.

El catálogo contiene 12 programas de seis universidades; cuatro programas externos siguen excluidos del cálculo. El corpus incluye 17 fuentes revisadas y 845 fragmentos. La incorporación del perfil conserva las 16 fuentes previas mediante `build_corpus.py --merge-existing`; `verify_sources.py` comprueba sus huellas. Los respaldos anteriores a esta operación están en `ai-service/.cache/publicacion-psicologia-20261008/`.

Se generaron 31 embeddings reales de 384 dimensiones para la instantánea `sha256:541f1e56aab02ff523d2a75eef3c6fe17c0d718f3cc60160bede3d499d6d720d`, con `intfloat/multilingual-e5-small`, prefijo `passage:`, normalización y sin truncamiento (máximo observado: 200 tokens). Modelo y dependencias residen en `.models/` y `.venv312/`, separados del servidor. El manifiesto verifica modelo, texto completo, orden de fragmentos y SHA-256 del NPY. **Estos vectores están pendientes de publicación en el índice**; la recuperación activa sigue siendo BM25. La ingesta documental y la generación de embeddings no equivalen a entrenar o validar un predictor de elección de carrera.

`Ver extracción guardada` realiza un GET autenticado y comprueba la instantánea sin descargar otra versión del sitio. `Extraer vista previa` realiza un POST y necesita revisar una instantánea nueva: una página dinámica puede cambiar de huella aunque conserve el contenido académico. Los límites por operación son independientes; leer, analizar o aprobar no consume el cupo de tres extracciones por minuto. Se corrigió además la aceptación de carreras por una palabra común («Ingeniería») y la pérdida de duración/áreas en encabezados o bloques HTML.

### Extracción preliminar aprobada

`POST /api/v1/knowledge/governance/proposals/{proposal_id}/extract` acepta únicamente una propuesta en `APROBADA_PENDIENTE_INGESTA`. Descarga una sola fuente pública aprobada con las restricciones HTTPS, DNS y redirecciones del verificador; amplía solo para esta ingesta aprobada el límite documental de 4 a 12 MiB. Conserva la instantánea, el SHA-256, los fragmentos JSONL y un manifiesto en `ai-service/data/knowledge/staged/<proposal_id>/`. Para pruebas, el directorio sigue el padre de `SAVP_AI_SOURCE_INTAKE_PATH`. Un PDF procesa como máximo 80 páginas y reporta si hay más. La respuesta presenta hasta ocho fragmentos y candidatos explícitos de carrera, duración o materias con su página o sección. Los enlaces académicos relacionados (máximo tres por respuesta) quedan como propuestas independientes; su contenido no se confunde con el de la página aprobada. Una consulta cubre **un documento** de un máximo de cuatro previstos para la primera ronda editorial.

Los candidatos, la versión y la sede requieren revisión humana contra cada documento. La extracción no cambia `sources.json`, `careers.json`, el corpus publicado ni el estado de la propuesta. La respuesta informa `embeddings.status=NO_GENERADOS` y `vectors=0`: el servicio operativo usa BM25 y su entorno no incluye actualmente `sentence-transformers`, FAISS ni NumPy. Para crear un índice vectorial real se requiere un proceso separado que verifique de nuevo SHA-256, modelo, dimensión, cardinalidad y correspondencia de IDs antes de publicar. Una página de admisión con formulario, un PDF solo imagen, una versión curricular sin vigencia confirmada o un costo sin gestión/alcance se mantienen pendientes de revisión.

Tras revisar manualmente la fuente y disponer de un modelo SentenceTransformer descargado localmente, `python scripts/vectorizar_fuente_revisada.py <ID> --modelo-directorio <RUTA_LOCAL> --revisor <NOMBRE> --nota-revision "<JUSTIFICACION>"` puede generar vectores **solo en staging**. El comando comprueba de nuevo propuesta, snapshot, SHA-256 y todos los IDs de chunks; exige pesos locales y registra hash de pesos, dimensión, cardinalidad y hash de vectores en `embedding_manifest.json`. No descarga modelos ni incorpora esos vectores a la búsqueda. Si faltan dependencias o modelo local, falla explícitamente sin atribuir vectores a la fuente.

## Colección universitaria, OCR y descarga de mallas

La colección UNIFRANZ tiene una pantalla propia en `/conocimiento/universidades/unifranz`, reservada a Administrador con `conocimiento.revisar`. Descubre fichas y mallas desde el índice oficial, conserva instantáneas y fragmentos en `data/knowledge/colecciones/unifranz/` y separa la recopilación documental de la aprobación y publicación. Su flujo y los criterios de recuperación se detallan en [COLECCION-UNIVERSITARIA.md](../diseno/COLECCION-UNIVERSITARIA.md).

El OCR de las mallas complementa la lectura digital cuando las páginas contienen imágenes o texto no recuperable. Debe conservar el método y la página de cada fragmento y avisar de las páginas sin lectura suficiente. La cobertura de lectura, la detección de candidatos de materias y la aprobación curricular son estados diferentes. Un texto reconocido por OCR requiere revisión contra el PDF antes de incorporarse como asignatura oficial, requisito, semestre o preparación recomendada; tampoco prueba sede o vigencia si el documento no las declara.

Una relectura con OCR usa la instantánea cuyo SHA-256 está verificado. Los fragmentos se reemplazan coherentemente al completar el procesamiento; un fallo conserva la lectura anterior y no se informa como estudio terminado. Esto permite ampliar evidencia de un PDF de imagen sin afirmar que se volvió a consultar la universidad ni alterar la fuente aprobada.

La descarga de una malla guardada pasa por un endpoint autenticado de Laravel hacia el Aporte, con actor y permiso comprobados. El navegador identifica un documento registrado, nunca proporciona una ruta de archivo ni un destino remoto. El Aporte resuelve y verifica la instantánea; Laravel entrega únicamente bytes PDF acotados que coincidan con la huella esperada, como adjunto con nombre humano de institución/carrera/título y sin exponer directorios o credenciales internos. El encabezado interno `X-SAVP-Document-Name` lleva el nombre codificado para URL; Laravel lo decodifica y valida antes de entregarlo. La ubicación final del archivo depende de la configuración del navegador.

Extraer, reconocer texto, descargar y detectar materias no publican por sí solos el documento en el catálogo, el corpus ni el índice vectorial. La revisión de procedencia, exactitud y vigencia sigue siendo necesaria para incorporar el material al estudio del estudiante.
