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
python -m venv .venv312
.\.venv312\Scripts\python.exe -m pip install uv
.\.venv312\Scripts\python.exe -m uv sync --dev
.\.venv312\Scripts\python.exe -m uvicorn app.main:app --host 127.0.0.1 --port 8001
.\.venv312\Scripts\python.exe -m pytest
.\.venv312\Scripts\python.exe -m ruff check .
.\.venv312\Scripts\python.exe -m mypy .
```

## Endpoints

- `GET /health`: salud y versión, sin datos sensibles.
- `POST /api/v1/analysis`: baseline legacy experimental.
- `POST /api/v2/analysis`: análisis principal basado en evidencia.
- `POST /api/v1/knowledge/search`: recuperación oficial con citas y suficiencia.
- `POST /api/v1/tutor/query`: tutor estructurado, trazable y abstencionista.

Si `SAVP_AI_API_KEY` está configurada, los endpoints de análisis/conocimiento/tutor requieren el
header `X-SAVP-AI-Key`. Todas las respuestas HTTP incluyen `X-Trace-Id`; los errores usan un
envelope estable con código, mensaje, trace y detalles de validación.

## Reproducción Peter 3

```powershell
.\.venv312\Scripts\python.exe scripts\evaluate_retrieval.py
.\.venv312\Scripts\python.exe scripts\evaluate_prompts.py
.\.venv312\Scripts\python.exe scripts\run_peter3_full_demo.py
.\.venv312\Scripts\python.exe scripts\benchmark_peter3.py --repetitions 20
```

Los reactivos españoles de O*NET® Mini-IP se reproducen literalmente bajo CC BY-ND 4.0. O*NET®
es una marca de USDOL/ETA, entidad que no aprueba ni respalda SAVP. El instrumento mide intereses
vocacionales, no inteligencia, aptitud ni probabilidad de éxito.

## Límites

El catálogo cubre cinco ofertas de dos universidades y no es exhaustivo. Bridge y crosswalk son
inferencias documentales pendientes de revisión experta; no existe validación psicométrica
boliviana ni estudio longitudinal. El benchmark de retrieval es local al dataset versionado. El
runtime LLM local no estuvo disponible para evaluación. La integración consumidora en Laravel,
base de datos y UI queda fuera de este repositorio Python y no fue iniciada.
