# Colección académica de UNIFRANZ

## 1. Pantalla y propósito

`/conocimiento/universidades/unifranz` ofrece a Administración una pantalla propia para descubrir las carreras del índice oficial `https://unifranz.edu.bo/carreras/`, descargar cada ficha y sus enlaces a mallas PDF, y recuperar lo guardado al volver. Se accede desde Gestión del conocimiento y desde la ficha de UNIFRANZ del directorio nacional. El formulario de una fuente individual conserva su flujo de propuesta y revisión.

## 2. Qué se conserva

La colección se guarda en `ai-service/data/knowledge/colecciones/unifranz/`: manifiesto, instantáneas por SHA-256 y fragmentos JSONL con URL, página/sección, método y fecha. Las fichas conservan descripción, duración explícitamente publicada y sedes identificadas en el contenido académico. Una sede ausente permanece pendiente. Cada PDF conserva páginas totales, páginas con texto digital, páginas leídas mediante reconocimiento de imágenes, extracto y advertencias. El texto de una malla con columnas puede requerir revisión de su orden; los fragmentos no se presentan como una lista validada de asignaturas.

No se añaden tablas. La colección utiliza el área documental del Aporte, donde ya se guardan instantáneas y fragmentos. Tampoco modifica por sí sola el catálogo publicado, el corpus del tutor ni los resultados estudiantiles. «Revisar esta fuente» abre la URL exacta en el formulario de fuentes para realizar esa revisión.

## 3. Ejecución, avance y recuperación

Laravel autoriza actor Administrador y `conocimiento.revisar` en la pantalla y en cada consulta/inicio. El servidor usa una URL fija; el navegador no puede enviar otra fuente ni un estado de aprobación. Python comienza un hilo de colección con bloqueo entre procesos; el inicio devuelve inmediatamente el estado. El navegador consulta cada cuatro segundos mientras hay trabajo, evita dos inicios y deja de consultar al terminar o al salir.

Las escrituras son atómicas. La colección reanuda únicamente documentos pendientes o fallidos, conserva descargas anteriores y detecta una ejecución interrumpida. Antes de reutilizar una instantánea comprueba SHA-256 y existencia, cardinalidad y procedencia de sus fragmentos. Un archivo dañado se marca individualmente para recuperación; no obliga a descargar otra vez las mallas sanas. Los errores de conexión, sesión o frecuencia preservan la última respuesta confirmada y ofrecen consultar el estado guardado antes de otro inicio.

## 4. Límites y procedencia

Se admiten solamente páginas y PDF públicos de UNIFRANZ. El transporte mantiene HTTPS, validación de todos los destinos DNS, conexión fijada a una IP pública, evaluación de redirecciones y exclusión de portales privados. La colección dispone de hasta 30 segundos por descarga, 10 por lectura, dos intentos limitados y una pausa entre documentos. El segundo intento puede usar otra IP pública comprobada; los límites interactivos de la vista previa individual permanecen en 12 segundos y 4 por lectura.

Cada documento tiene un máximo de 12 MiB y la lectura de un PDF se limita a 80 páginas. La identidad de carrera se comprueba mediante la ruta final y el H1 publicado, con normalización de mayúsculas y acentos. Una redirección hacia otra carrera o la portada no atribuye el contenido a la carrera original. La duración se toma de etiquetas explícitas como «Programa de Estudios» o «Duración»; una edad o experiencia de un testimonio no sirve de duración académica. La falta de texto se comunica por página; no se inventan materias, costos ni equivalencias entre sedes.

## 5. Verificación

Pruebas focales cubren autorización, URL fija, contrato de respuesta, doble inicio, sondeo, recuperación de interrupciones, enlaces ajenos, PDF parcial, procedencia y protección de instantáneas anteriores ante errores. La revisión visual comprueba carga, búsqueda, plegables, enlaces de revisión, pantalla estrecha y tema oscuro. Los resultados reales de red se distinguen de las pruebas sintéticas y de la aprobación editorial.

Ejecución manual autorizada: desde `ai-service`, `.\.venv\Scripts\python.exe scripts/extraer_unifranz.py`. `--recolectar` exige la intención explícita de descargar versiones nuevas, incluidas las que ya se habían leído correctamente.

## 6. Lectura digital, OCR y revisión de materias

Una malla puede mezclar texto digital e imágenes. El flujo distingue ambos métodos: utiliza texto digital recuperable y aplica OCR a las páginas que lo necesitan, con límites explícitos de procesamiento y advertencias ante errores. Obtener texto de todas las páginas describe cobertura de lectura; no acredita todavía el orden de columnas, los nombres de asignaturas, sus requisitos o la vigencia curricular.

Los fragmentos y candidatos de materias deben conservar página, método (`DIGITAL` u `OCR`), URL y SHA-256 de la instantánea. Cada materia propuesta se revisa contra la página correspondiente, especialmente en diagramas, tablas, nombres partidos y errores de reconocimiento. Una lista de palabras detectadas no se convierte automáticamente en materias oficiales ni en objetivos del plan del estudiante. La asociación con sede, semestre y versión exige evidencia del propio documento; un dato ausente permanece pendiente.

Si OCR no está instalado, falla o alcanza un límite, la interfaz identifica qué páginas siguen pendientes y conserva el PDF y la lectura obtenida. La recuperación permite completar el procesamiento local sin atribuir una descarga nueva ni sustituir la instantánea por una versión diferente. El estado de procesamiento y el de revisión académica se presentan por separado.

La implementación utiliza Tesseract mediante PyMuPDF y un modelo local `spa.traineddata`. Busca el modelo en `SAVP_TESSDATA_DIR`, `TESSDATA_PREFIX`, `data/knowledge/herramientas/tessdata/`, `.models/tessdata/` o la ubicación de PyMuPDF. La ausencia del modelo se informa expresamente. Cada PDF dispone de 120 segundos para reconocimiento, cada página de hasta 35 segundos y la imagen se acota a 12 millones de píxeles. El proceso de colección no descarga ni instala modelos por su cuenta.

## 7. Descarga de la malla guardada

El botón de descarga entrega el PDF de la instantánea verificada mediante Laravel y el Aporte. La descarga exige el mismo actor Administrador y permiso `conocimiento.revisar`; no recibe una ruta local ni una URL arbitraria. El Aporte resuelve el documento registrado, comprueba su SHA-256 y devuelve el contenido público guardado. Laravel confirma tipo PDF, tamaño y huella antes de entregar el archivo como adjunto.

El nombre visible identifica institución, carrera y título publicado de la malla; si ese título declara sede, la conserva: por ejemplo, «UNIFRANZ - Derecho - La Paz.pdf». La huella queda como identificador interno y evidencia de integridad; no es el nombre que tiene que interpretar el administrador. El Aporte transmite el nombre codificado para URL en `X-SAVP-Document-Name`; Laravel lo decodifica y valida antes de crear el adjunto. La ruta del servicio, los directorios internos y las credenciales del Aporte no se exponen al navegador.

El navegador elige la carpeta de destino según su configuración; normalmente utiliza Descargas o pregunta dónde guardar. La aplicación confirma la entrega del archivo, pero no afirma haber escrito en una carpeta local que no controla. Abrir la fuente oficial y descargar la instantánea guardada son acciones distintas, con procedencia y fecha visibles.
