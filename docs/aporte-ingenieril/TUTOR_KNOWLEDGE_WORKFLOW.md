# Carga de conocimiento del tutor

## Principio

El tutor no aprende ni descarga modelos en tiempo de ejecución. Su conocimiento proviene del
corpus local trazable. El baseline actual tiene 16 fuentes oficiales y 814 fragmentos; cada fuente
conserva URL, institución, versión, fecha de recuperación y SHA-256.

## Incorporar una fuente

1. Seleccionar una fuente oficial con autoridad explícita y descargar un snapshot local en
   `ai-service/data/raw/`. Se aceptan HTML, TXT, Markdown y PDF con texto digital.
2. Registrar la fuente en `ai-service/data/sources/sources.json`, incluyendo `source_id`,
   institución, título, URL, tipo, vigencia, ruta local, SHA-256, versión, estado y limitaciones.
3. Para una fuente nueva, construir todo el corpus; para actualizar una existente, preservar las
   demás con `--merge-existing`:

   ```powershell
   cd ai-service
   .\.venv\Scripts\python.exe scripts\build_corpus.py
   .\.venv\Scripts\python.exe scripts\build_corpus.py --source-id <SOURCE_ID> --merge-existing
   ```

4. Verificar procedencia, hashes, cardinalidad y ausencia de fuentes externas en el corpus:

   ```powershell
   .\.venv\Scripts\python.exe scripts\verify_sources.py
   ```

5. Reiniciar FastAPI para recargar el corpus BM25 en memoria y consultar la nueva fuente desde el
   tutor. No se reconstruye ningún índice vectorial ni se descarga un modelo.

## Conversación

Editar `ai-service/data/tutor/conversation_catalog.json` para ajustar saludo, explicación de
capacidades o mensaje cuando falta conocimiento. El archivo es validado al usarse; debe mantener
las entradas `SOCIAL`, `THANKS`, `FAREWELL`, `WELLBEING`, `CAPABILITIES` y `KNOWLEDGE_GAP` con una respuesta y temas sugeridos. Los saludos no ocultan preguntas académicas: «Hola, ¿qué carrera?» sigue la recuperación documental. Agradecimientos y despedidas tienen respuestas propias, sin modelos generativos.

## Restricciones

Un PDF escaneado sin texto digital no entra automáticamente al corpus: se requiere una versión
digital oficial o una transcripción revisada y registrada. Las URLs externas no verificadas se
mantienen fuera del corpus y nunca deben respaldar una respuesta institucional.
