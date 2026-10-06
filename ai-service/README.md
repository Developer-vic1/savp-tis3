# Servicio de Aporte Ingenieril SAVP

Servicio FastAPI para análisis académico-vocacional y tutoría documental. No ejecuta LLM,
embeddings, FAISS, OCR automático ni descarga modelos. El tutor responde con un catálogo
conversacional editable y con evidencia del corpus local validado.

## Tutor de conocimiento

`POST /api/v1/tutor/query` maneja saludos y preguntas sobre sus capacidades sin buscar el corpus.
Las preguntas académicas, vocacionales e institucionales usan recuperación léxica BM25 sobre el
corpus oficial. Cada afirmación documental se acompaña de fuente; si no hay evidencia suficiente,
el tutor lo indica y solicita precisar la consulta o incorporar una fuente oficial. No inventa
datos ni toma decisiones vocacionales por el estudiante.

El catálogo para saludos, capacidades y respuestas de falta de conocimiento está en
`data/tutor/conversation_catalog.json`. Las fuentes y el procedimiento de carga están documentados
en `../docs/aporte-ingenieril/TUTOR_KNOWLEDGE_WORKFLOW.md`.

## Entorno y calidad

```powershell
cd ai-service
python -m pip install uv
python -m uv sync --locked --group dev
.\.venv\Scripts\python.exe -m uvicorn app.main:app --host 127.0.0.1 --port 8001
.\.venv\Scripts\python.exe scripts\verify_aporte.py
powershell -ExecutionPolicy Bypass -File scripts/check_tutor_stack.ps1
```

Python 3.12 es la versión verificada. Para gates individuales usar
`python -m pytest`, `python -m ruff check .`, `python -m mypy app scripts` y
`python scripts/verify_sources.py` desde el entorno virtual.

## Endpoints

- `GET /health`: salud y versión, sin datos sensibles.
- `GET /api/v2/riasec/instrument`: instrumento y escala pública 1–5.
- `POST /api/v2/riasec/score`: scoring oficial 0–20 por dimensión.
- `POST /api/v1/analysis`: baseline legacy experimental.
- `POST /api/v2/analysis`: análisis principal basado en evidencia.
- `POST /api/v1/knowledge/search`: búsqueda BM25 con citas y suficiencia.
- `POST /api/v1/tutor/query`: conversación y tutoría documental.

Si `SAVP_AI_API_KEY` está configurada, los endpoints protegidos requieren el header
`X-SAVP-AI-Key`. Todas las respuestas HTTP incluyen `X-Trace-Id`.

Laravel requiere `APORTE_INGENIERIL_ENABLED=true` y
`APORTE_INGENIERIL_BASE_URL=http://127.0.0.1:8001`.
El cliente solo envía contexto pedagógico autorizado e historial reciente; nunca identidad civil
ni calificaciones crudas.

## Límites

El corpus no es exhaustivo y su vigencia depende de las fuentes cargadas. Las fuentes externas no
validadas no se convierten en evidencia. RIASEC describe intereses, no inteligencia, aptitud ni
probabilidad de éxito.
