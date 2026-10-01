# SAVP AI Service

Servicio FastAPI autónomo para PETER 3 de SAVP‑TIS3. La API V1 es una base experimental con ranking y umbrales configurados; **no es un motor validado de predicción ni de probabilidad de éxito**. No hay API V2 ni integración con Laravel/PostgreSQL. La búsqueda y el tutor usan un corpus local y un índice FAISS; el LLM local es opcional.

**Estado pre-commit (2026-09-30): `NOT_SAFE_TO_COMMIT`.** Véase [auditoría](../docs/peter3/PETER3_FINAL_AUDIT.md). Los hashes de cuatro snapshots HTML no coinciden con el registro y el índice conserva un hash de corpus anterior. La suite no pudo ejecutarse en el entorno actual por falta de dependencias.

## Entorno

```powershell
python -m venv .venv
.\.venv\Scripts\python -m pip install uv
.\.venv\Scripts\uv sync --dev
.\.venv\Scripts\uv run uvicorn app.main:app --host 127.0.0.1 --port 8001
```

Pruebas y calidad:

```powershell
.\.venv\Scripts\uv run pytest
.\.venv\Scripts\uv run ruff check .
.\.venv\Scripts\uv run mypy app
```

## Endpoints

- `GET /health`
- `POST /api/v1/analysis` (V1 experimental)
- `POST /api/v1/knowledge/search`
- `POST /api/v1/tutor/query`

El contrato completo está en `../docs/peter3/API_CONTRACT.md`.

No existe `POST /api/v2/analysis`. Los endpoints `/api/v1/*` exigen `X-SAVP-AI-Key` cuando se configura `SAVP_AI_API_KEY`. Sin la clave configurada, el servicio permite solicitudes locales sin autenticación; configure la clave antes de cualquier exposición de red.

## Escala RIASEC

El contrato V1 recibe valores enteros **0–4**. La web/API oficial de O*NET usa **1–5**; convierta cada respuesta con `official_web_value_to_internal` antes de construir la solicitud. La conversión 1→0, 2→1, 3→2, 4→3, 5→4 está cubierta por pruebas, pendientes de ejecución en el entorno actual.

## Conocimiento y tutor

El repositorio incluye 11 fuentes registradas, 689 fragmentos y dos índices FAISS de 689 vectores cada uno. El índice seleccionado usa `intfloat/multilingual-e5-small`. La recuperación requiere las dependencias y el modelo de embeddings disponibles localmente o descargables; el tutor estructurado está diseñado para funcionar sin LLM local. La integridad de fuentes e índices sigue bloqueada según la auditoría.

## RIASEC y licencia

Los reactivos españoles de O*NET® Mini‑IP se reproducen literalmente bajo CC BY‑ND 4.0. Incluye información de O*NET® Career Exploration Tools del U.S. Department of Labor, Employment and Training Administration (USDOL/ETA). O*NET® es marca de USDOL/ETA. USDOL/ETA no ha aprobado ni respaldado SAVP.
