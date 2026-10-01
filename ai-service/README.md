# SAVP AI Service — PETER 3

Servicio FastAPI autónomo para análisis académico-vocacional basado en evidencia. No depende de
Laravel, PostgreSQL, OpenAI ni GPU. El LLM local es opcional y está deshabilitado por defecto.

## Arquitectura

El servicio separa contratos HTTP, dominio analítico, Learning Analytics, RIASEC, recomendación,
registro documental, ingestión, retrieval híbrido y tutor. `api/v1/analysis` conserva el baseline
legacy experimental para compatibilidad y comparación; no debe interpretarse como modelo
predictivo. `api/v2/analysis` es la interfaz principal futura: devuelve perfiles de evidencia por
carrera, sin score compuesto, ganador global ni probabilidad de éxito.

La base documental usa snapshots oficiales con SHA-256, un corpus trazable, embeddings E5,
FAISS exacto, BM25 y fusión RRF. El tutor estructurado cita solo evidencia recuperada y se abstiene
ante soporte insuficiente. La metodología del artefacto es DSRM; ICONIX corresponde al proceso de
desarrollo global. No se implementa XGBoost.

## Entorno y calidad

```powershell
cd ai-service
python -m pip install uv
python -m uv sync --locked --group dev
.\.venv\Scripts\python.exe -m uvicorn app.main:app --host 127.0.0.1 --port 8001
.\.venv\Scripts\python.exe scripts\verify_peter3.py
```

Python 3.12 es la versión verificada. `uv.lock` fija las dependencias directas y transitivas.
El entorno `.venv` y `.cache` son locales y no se versionan. Para repetir los gates por separado:
`python -m pytest`, `python -m pytest --cov=app`, `python -m ruff check .`,
`python -m mypy app scripts`, `python -m mypy .` y `python scripts/verify_sources.py`
desde el intérprete del entorno.

## Endpoints

- `GET /health`: salud y versión, sin datos sensibles.
- `GET /api/v2/riasec/instrument`: instrumento y escala pública 1–5.
- `POST /api/v2/riasec/score`: scoring oficial 0–20 por dimensión de 30 respuestas.
- `POST /api/v1/analysis`: baseline legacy experimental.
- `POST /api/v2/analysis`: análisis principal basado en evidencia.
- `POST /api/v1/knowledge/search`: recuperación oficial con citas y suficiencia.
- `POST /api/v1/tutor/query`: tutor estructurado, trazable y abstencionista.

Si `SAVP_AI_API_KEY` está configurada, los endpoints de análisis/conocimiento/tutor requieren el
header `X-SAVP-AI-Key`. Todas las respuestas HTTP incluyen `X-Trace-Id`; los errores usan un
envelope estable con código, mensaje, trace y detalles de validación.

También se protegen ambos endpoints RIASEC. En `SAVP_AI_ENV=production`, el servicio rechaza
solicitudes protegidas con `503` si falta `SAVP_AI_API_KEY`. Variables adicionales:
`SAVP_AI_HOST`, `SAVP_AI_PORT`, `SAVP_AI_INDEX_PATH`, `SAVP_AI_SEMANTIC_ENABLED`,
`SAVP_AI_LOCAL_LLM_ENABLED`, `SAVP_AI_LOCAL_LLM_URL` y
`SAVP_AI_LOCAL_LLM_TIMEOUT_SECONDS`. Véase
`../docs/peter3/PETER2_INTEGRATION_CONTRACT.md` para ejemplos, errores y timeouts.

## Reproducción Peter 3

```powershell
.\.venv\Scripts\python.exe scripts\evaluate_retrieval.py
.\.venv\Scripts\python.exe scripts\evaluate_prompts.py
.\.venv\Scripts\python.exe scripts\run_peter3_full_demo.py
.\.venv\Scripts\python.exe scripts\benchmark_peter3.py --repetitions 20
```

`run_peter3_full_demo.py` y `run_analysis_v2_demo.py` son `TECHNICAL_CLI_SCRIPT`:
ejercitan casos controlados y no ofrecen una interfaz de usuario. Antes de reconstruir corpus e
índices se debe pasar `scripts/verify_sources.py`. El chunking usa 1200 **caracteres** con
solapamiento de 180 caracteres, no tokens. E5 aplica `query: ` a consultas y `passage: ` a
documentos; FAISS almacena vectores normalizados. Los resultados de retrieval y rendimiento
dependen del corpus y el entorno que se haya evaluado.

Los reactivos españoles de O*NET® Mini-IP se reproducen literalmente bajo CC BY-ND 4.0. O*NET®
es una marca de USDOL/ETA, entidad que no aprueba ni respalda SAVP. El instrumento mide intereses
vocacionales, no inteligencia, aptitud ni probabilidad de éxito.

## Límites

El catálogo cubre cinco ofertas de dos universidades y no es exhaustivo. Bridge y crosswalk son
inferencias documentales pendientes de revisión experta; no existe validación psicométrica
boliviana ni estudio longitudinal. El benchmark de retrieval es local al dataset versionado. El
runtime LLM local no estuvo disponible para evaluación. La integración consumidora en Laravel,
base de datos y UI queda fuera de este repositorio Python y no fue iniciada.
