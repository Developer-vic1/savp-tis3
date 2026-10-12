# Prevención de fuentes universitarias

La extracción de todas las carreras y mallas de UNIFRANZ utiliza una [colección con pantalla propia, avance y recuperación](../diseno/COLECCION-UNIVERSITARIA.md). La lectura de una URL individual conserva el flujo descrito a continuación; la colección guarda evidencia preliminar sin sustituir la revisión de fuentes.

## Alcance

`/conocimiento/fuentes` prepara propuestas de páginas universitarias, PDF de mallas o planes curriculares y PDF de reglamentos. Usa reglas locales, BeautifulSoup y PyMuPDF; no llama modelos de IA ni aplica OCR. La detección no guarda una propuesta ni acredita autenticidad, vigencia o incorporación al estudio. La revisión administrativa se conserva.

## Support y contratos

`app/Support/Conocimiento/FuenteUniversitariaInteligente.php` centraliza límites, tipos documentales, catálogo local, reglas de textos, URL e institución. `contratoParaInterfaz()` entrega campos, universidades, tipos y límites de líneas a Blade; `validar()` se ejecuta independientemente antes del HTTP al analizar y guardar. `analizar()` devuelve `puede_continuar`, `estado`, `bloqueos` por campo, `errores`, `advertencias`, `sugerencias` y `verificacion_remota=false`.

Los estados locales son `BLOQUEADA`, `REQUIERE_REVISION` y `VALIDA_LOCALMENTE`. No equivalen a `VERIFICADA` del documento remoto. El catálogo local refleja las seis universidades de `ai-service/app/knowledge/governance.py`, incluida UDABOL (`udabol.edu.bo`, Universidad de Aquino Bolivia); una prueba contrasta ambos registros. Su identidad se corroboró en el [sitio oficial](https://www.udabol.edu.bo/). Cambiarlo exige mantenerlos sincronizados, sin declarar como universidad cualquier dominio `.bo`.

## Flujo y recuperación

1. Elegir página, PDF de malla/plan o PDF de reglamento. Los tres usan una única URL canónica; elegir un PDF no adjunta un segundo documento oculto.
2. Al escribir, comprobar formato, dominio y metadatos inmediatamente en el navegador. Las reglas del servidor se repiten incluso si se manipula JavaScript.
3. Consultar el documento después de una pausa de 1,2 segundos o por acción explícita. URLs bloqueadas, falta de permiso y servicio no disponible no generan consultas automáticas. Máximo seis intentos de comprobación por minuto; respetar `Retry-After` del servidor.
4. Mostrar datos publicados y hasta seis enlaces PDF académicos encontrados en el HTML descargado. Se resuelven enlaces relativos, se eliminan duplicados y se exige HTTPS de la misma universidad. Se conserva el título del enlace; no se descargan todos los PDF ni se inventan carreras, sede, ciudad, versión o justificación.
5. Desplegar la ficha al encontrar una publicación utilizable o elegir completar manualmente. Conservar los campos en el formulario aunque el desplegable esté cerrado. Los datos existentes no se reemplazan sin selección explícita.
6. Separar el resumen de la ficha del modal de la publicación. Los diálogos se trasladan a `body`, con cabecera y acciones visibles, contenido con desplazamiento interno y foco recuperable.
7. Analizar la ficha y enviarla sólo con documento comprobado y token vigente ligado a usuario, actor y contenido. La vista previa dura cinco minutos y el análisis diez. Un cambio descarta respuestas anteriores y exige analizar de nuevo.

Si el servicio está apagado, la validación local y el catálogo siguen disponibles; no se simula una comprobación. La ficha se conserva ante desconexión, límite de frecuencia o error. Los controles de país y aprobación no se eliminan para ocultar una falla.

## Matriz de casos extremos

| Caso | Respuesta | Respaldo y límite |
|---|---|---|
| HTTPS válido de universidad conocida | Reconocer dominio; consultar documento | Registro real del proyecto; no certifica la URL sólo por su dominio |
| Dominio extranjero o `upb.edu.evil.com` | Bloquear antes de consultar | Política vigente de fuentes universitarias bolivianas y comparación exacta de dominio/subdominio |
| `.bo` desconocido | Advertir; exigir revisión humana | El sufijo nacional no prueba pertenencia universitaria |
| IP, localhost, credenciales, puerto personalizado, espacios/controles o escapes malformados | Bloquear URL | Contrato público HTTPS del módulo |
| Texto invisible, campos breves con saltos, texto sólo de signos o longitud excesiva | Error junto al campo y 422 del servidor | Criterio preventivo de legibilidad; no es comprensión semántica ni norma educativa |
| Universidad distinta del dominio | Corregir institución; permitir sólo nombre oficial o alias registrados | Catálogo institucional existente |
| Fecha imposible, 11 limitaciones o línea fuera de 3–500 caracteres | Bloquear; no truncar silenciosamente | Reglas `FechaPublicacionAcademica` y contrato previo de limitaciones |
| Tipo alterado o PDF declarado que devuelve HTML | Rechazar catálogo o exigir corregir formato | Tipos cerrados y formato real del documento; extensión sola no prueba PDF |
| Enlace PDF extranjero, duplicado o no académico | Excluir de los documentos ofrecidos | Mismo registro de universidad y texto publicado; no visitar enlaces excluidos |
| Cambiar URL durante respuesta o hacer doble envío | Cancelar/descartar respuesta vieja y bloquear envío repetido | Versión de petición, AbortController y token de un solo uso en sesión |
| 429, sesión vencida o dependencia apagada | Explicar espera/reingreso y conservar entradas | Límites existentes por ruta y manejo de error; no emitir token utilizable |
| DNS privado/reservado o redirección fuera de universidad | Bloquear descarga | Verificador existente: DNS público, IP fijada, TLS y evaluación de cada salto |
| Documento excesivo, protegido o PDF escaneado sin texto | Rechazar con explicación | 4 MiB, 12 segundos, primera página digital; no IA ni OCR |
| Página que impide mostrarse dentro del modal | Mostrar extracto comprobado y enlace oficial | Evaluar encabezados recibidos; no retirar CSP ni usar proxy que ejecute su HTML |
| Portal estudiantil o formulario de autenticación de una universidad conocida | Explicar que no es una publicación académica utilizable | No extraer menús como malla ni devolver texto privado, fragmentos, sugerencias o PDF candidatos |

El modal externo usa `sandbox="allow-scripts"`, título accesible y `referrerpolicy="no-referrer"`; no habilita formularios, navegación superior ni acceso al origen. Sus límites están descritos en la [referencia de iframe de MDN](https://developer.mozilla.org/en-US/docs/Web/HTML/Reference/Elements/iframe). No se puede garantizar que toda universidad permita mostrarse embebida.

## Identificador documental y conexión rechazada

La respuesta remota agrega `document_kind` (`PUBLIC_DOCUMENT`, `STUDENT_PORTAL`, `AUTHENTICATION`, `UNKNOWN`) y `embedding` (`allowed: true|false|null`, `reason: string|null`). El cliente Laravel valida estos campos. `embedding.allowed=false` no impide usar un documento público comprobado; `STUDENT_PORTAL` y `AUTHENTICATION` sí impiden usarlo como fuente académica y deben devolver `can_use=false`, extracto vacío y sin campos sugeridos, fragmentos ni documentos candidatos.

El enlace entregado por el usuario [Solicitud de información UDABOL](https://academico.udabol.edu.bo/estudiante/solicitud/solicitud_info/mostrar_interface) respondió HTTP 200 y su título fue «Estudiante». Es un portal de solicitudes con navegación hacia notas, deudas y matrícula. La mención de «Malla Curricular» en un menú no acredita que esta página contenga una malla. Se identifica su universidad y se explica qué enlace público falta; no se visitan sus datos personales ni se intenta iniciar sesión.

El descargador conserva sus límites y consulta `X-Frame-Options` y `Content-Security-Policy` del documento final. `DENY`, `SAMEORIGIN` y `frame-ancestors 'none'/'self'` permiten anticipar bloqueos dentro de SAVP. Ausencia de esos encabezados no prueba que el navegador pueda incrustar la página. Políticas con listas de orígenes requieren evaluación del navegador y permanecen indeterminadas cuando no se conoce su autorización.

La API interna de análisis y propuestas añade `PUBLIC_SOURCE_ROUTE`: bloquea rutas operativas explícitas, incluidas variantes escapadas de acceso, solicitudes, notas, deudas y datos personales. No hace otra descarga durante el análisis. Una ruta pública de carrera no se rechaza sólo por incluir la palabra «estudiante». Esta regla de rutas no inspecciona contenido servido detrás de una URL disfrazada. Laravel conserva la exigencia de comprobación vigente; una propuesta de un caller interno con clave sigue requiriendo revisión administrativa e ingesta independiente.

`academic_pages` ofrece hasta seis enlaces con `url`, `title` publicado y `kind` (`carrera`, `plan`, `reglamento`); `documents` conserva hasta seis PDF. Ambos proceden de un único parseo del HTML ya descargado. Se excluyen destinos ajenos, acceso, estudiantes, solicitudes, notas, deudas, cuentas y URLs con tokens. Los candidatos no se descargan ni se presentan como fuentes verificadas. La selección explícita reemplaza la URL canónica, descarta el análisis anterior y comprueba sólo el destino elegido. Los enlaces de un portal no generan candidatos.

Al cambiar de publicación se retiran únicamente los campos autocompletados por la comprobación anterior que siguen sin editar. Los datos manuales o confirmados por selección de sugerencias se conservan. Esto evita que el título de una portada aparezca como título de una carrera seleccionada, sin borrar el trabajo del usuario.

El extracto y el alcance académico tienen finalidades distintas. `scope` se sugiere sólo desde párrafos publicados de malla curricular, plan de estudios, perfil profesional o reglamento académico; noticias, aniversarios, eventos, metadescripciones y enlaces no lo rellenan. La página pública puede leerse y permanecer utilizable como página institucional, pero el campo de alcance debe completarse con evidencia pertinente. No se inventan carreras ni se convierte una noticia en un plan curricular.

Si hay un bloqueo conocido, el modal evita crear el iframe y presenta el extracto de la comprobación vigente. En otros casos conserva el botón «Ver lectura del documento» y el enlace oficial. La lectura exige URL actual, `can_use=true`, hash `sha256:` válido y texto recibido; no usa campos manuales ni HTML remoto. El extracto está limitado a 1500 caracteres y puede omitir imágenes, tablas y secciones. Un iframe externo puede fallar sin disparar un evento de error detectable: no se interpreta su evento `load` como prueba de contenido visible.

## Selector institucional compartido

Modalidad de publicación y tipo documental usan `x-selector-institucional`. El buscador se muestra sólo cuando hay **más de diez opciones originales**; no desaparece al filtrar resultados. Con diez o menos, el control conserva el foco y la navegación por teclado. Las opciones deshabilitadas se omiten. Los campos de formulario mantienen su nombre y valor para `FormData`; buscar una opción no invalida un análisis, elegir un valor sí lo invalida.

## Calibración sin modelos de IA

Aquí «entrenar» significa ajustar reglas y ejecutar un banco de regresión etiquetado. No hay pesos entrenados, llamadas a modelos, OCR, promesas de comprensión de cualquier web ni procesos de entrenamiento en segundo plano. BeautifulSoup interpreta HTML; PyMuPDF obtiene texto digital de PDF; las reglas contrastan URL, título, estructura y señales del documento. Las páginas públicas no se bloquean sólo por tener un enlace de acceso a estudiantes.

Para ampliar el banco sirven casos públicos con URL, universidad, título y tipo esperado; HTML reducido sin datos personales; PDF digital con texto; resultados esperados de malla/reglamento/portal/login; redirecciones y cabeceras de incrustación; páginas sin contenido útil o dependientes de JavaScript. Cada caso debe indicar qué datos sí están publicados y cuáles deben quedar pendientes. No incorporar sesiones, contraseñas, notas privadas ni expedientes de estudiantes.

Medición directa local del 07/10/2026, sin visitar enlaces privados: UDABOL solicitudes, 56 161 bytes, descarga **936,71 ms**, extracción y clasificación **23,70 ms**; cien procesamientos en memoria, p50 **19,55 ms**, p95 **23,96 ms**. Página principal UDABOL, 1602 bytes, descarga **1890,56 ms**, procesamiento **2,99 ms**; cien repeticiones, p50 **2,64 ms**, p95 **4,07 ms**. Son mediciones de esos documentos y esa conexión; no equivalen al tiempo total de Laravel ni garantizan tiempos de otras universidades. La red tiene mayor coste que el parser. No había encabezados de bloqueo recibidos en esos dos casos, por lo que `embedding.allowed` quedó en `null`.

Esas lecturas corresponden al diagnóstico de extracción; la regla final también detecta una aplicación JavaScript sin contenido académico legible y exige revisión, aunque haya respondido 200 con título. No basta con reconocer un dominio o un título para habilitar una ficha.

Banco reproducible: desde `ai-service`, ejecutar `.\.venv\Scripts\python.exe scripts/medir_vista_previa.py`. Usa únicamente nueve documentos sintéticos en memoria, incluidos PDF generados, y cien repeticiones por caso. Incluye extracción, sugerencia de alcance, clasificación, evaluación de incrustación y selección de enlaces para documentos públicos. Ejecución final: **1507,27 ms** para 900 procesamientos; los nueve resultados esperados coincidieron. El caso con diez enlaces conservó sólo tres páginas candidatas y un PDF. Esta coincidencia se limita al banco etiquetado, no es una tasa de acierto en páginas desconocidas. La medición de esta ejecución quedó en `storage/logs/medicion-fuentes.json`; es un artefacto local, no evidencia institucional.

| Caso | Resultado esperado | p50 / p95 (ms) |
|---|---|---|
| Portal estudiantil | `STUDENT_PORTAL` | 0,977 / 1,782 |
| Acceso con contraseña | `AUTHENTICATION` | 0,926 / 1,723 |
| Universidad con enlace externo de acceso | `PUBLIC_DOCUMENT` | 1,953 / 3,403 |
| Carrera con menú estudiantil | `PUBLIC_DOCUMENT` | 1,669 / 2,802 |
| Aplicación JavaScript vacía | `UNKNOWN` | 0,951 / 2,106 |
| Diez enlaces con candidatos y destinos operativos | 3 páginas y 1 PDF candidatos | 4,131 / 6,107 |
| Título académico con formulario de acceso | `AUTHENTICATION` | 1,110 / 1,922 |
| PDF digital | Documento público, texto legible | 1,626 / 2,848 |
| PDF sin texto digital | Documento PDF, lectura bloqueada | 0,425 / 0,758 |

La duración de una nueva calibración depende de recopilar y revisar casos etiquetados. Ejecutar las pruebas toma segundos; preparar evidencia válida requiere revisión humana. El banco no descarga todas las universidades ni todos sus PDF: inspecciona el documento seleccionado y ofrece hasta seis enlaces publicados para una elección explícita.

## Operación local

El servicio documental existente se inicia desde `ai-service` con `./.venv/Scripts/python.exe -m uvicorn app.main:app --host 127.0.0.1 --port 8001`. La clave interna procede de la configuración existente y no se imprime ni se incorpora a la ficha. `/health` confirma disponibilidad del proceso, no éxito de una lectura universitaria. Los logs del proceso iniciado para esta revisión están en `storage/logs/documentacion-servicio.*.log`.

Después de editar CSS/JS en modo compilado: `npm.cmd run build`; para Blade, `php artisan view:cache`, luego recargar. No ejecutar seeders ni migraciones para recuperar la detección de fuentes.

## Verificación

Pruebas pertinentes: `FuenteUniversitariaInteligenteTest`, `AutorizacionConocimientoUniversitarioTest`, `ClienteGestionConocimientoTest`, `AporteIngenierilClientTest`, `tests/Frontend/gestion-conocimiento.test.mjs` y Python `test_source_preview`, `test_knowledge_governance`, `test_knowledge_governance_api`. Incluyen validación directa antes del HTTP, respuestas tardías, frecuencia, selección de documentos y URLs/contratos manipulados. Las lecturas visuales usan la sesión existente y no envían propuestas.

`PermisosGestionConocimientoTest` utiliza migraciones que actualmente requieren PostgreSQL: su ejecución en SQLite falla antes de probar las reglas. Esto no se resuelve cambiando la base oficial ni debilitando esa restricción; se distingue de las pruebas focales de este cambio.

Primera entrega del 07/10/2026: 81 pruebas PHP (560 aserciones), 23 pruebas de interfaz y 67 pruebas Python correctas. QA de esa entrega: lectura HTTPS de UCB, bloqueo de Berkeley, contradicción de institución, ficha sobre el encabezado, móvil, modo oscuro y teclado.

Ampliación UDABOL/selectores y calibración terminada el 08/10/2026: **87 pruebas PHP (608 aserciones), 36 pruebas de interfaz y 101 pruebas Python correctas**; Ruff y mypy focales correctos. Vite y vistas Blade compiladas. QA real: catálogo de seis universidades, enlace original UDABOL identificado como portal y sin extracto académico, selector de tres opciones sin buscador, selección PDF reflejada en tipo documental, sugerencia de tipo UCB reflejada en selector y lectura alternativa UCB dentro del modal. EMI respondió HTTP 200: seis enlaces públicos de carreras ofrecidos y alcance pendiente sin usar noticias de portada. Seleccionar Ingeniería Civil reemplazó la URL y retiró el título autocompletado de portada; su lectura posterior agotó el tiempo de conexión y mostró el mensaje específico. El iframe de esa carrera rechazó la conexión; el botón alternativo eliminó el marco fallido y explicó la ausencia de extracto. En móvil y modo oscuro se comprobaron foco en Cerrar, ausencia de desbordamiento y límites del modal mediante DOM; la captura de la herramienta móvil aparece recortada y no se toma como prueba visual de todo el ancho. Se restauraron tema, tamaño y borrador del usuario. Las políticas de bloqueo de iframe se comprobaron además con respuestas controladas; no se afirma que toda universidad permita incrustación. Los PDF se verificaron con documentos sintéticos y HTML controlado, sin atribuir éxito a un PDF externo no consultado. No se registró ni aprobó una propuesta.

La comprobación posterior de UCB falló antes de obtener HTML: una lectura diagnóstica reprodujo `TimeoutError` en el establecimiento de TLS, antes del parser. No se interpreta como contenido inválido ni como regresión demostrada del identificador. El sistema ahora explica que la universidad tardó demasiado, conserva la ficha y permite comprobar de nuevo por acción explícita; no añade reintentos automáticos que aumenten la espera. La primera lectura UCB sí había sido verificada y visible; ambas observaciones se distinguen.

Comandos focales de esta ampliación:

```powershell
# Desde la raíz de Laravel, SQLite sólo para las pruebas aisladas indicadas.
php -d extension=pdo_sqlite -d extension=sqlite3 vendor/phpunit/phpunit/phpunit tests/Feature/FuenteUniversitariaInteligenteTest.php tests/Feature/AutorizacionConocimientoUniversitarioTest.php tests/Unit/ClienteGestionConocimientoTest.php tests/Unit/AporteIngenierilClientTest.php
node --test tests/Frontend/gestion-conocimiento.test.mjs tests/Frontend/selector-institucional.test.mjs

# Desde ai-service.
.\.venv\Scripts\python.exe -m pytest tests/unit/test_source_preview.py tests/unit/test_knowledge_governance.py tests/integration/test_knowledge_governance_api.py --no-cov
.\.venv\Scripts\python.exe scripts/medir_vista_previa.py
```
