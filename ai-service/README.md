# SAVP AI Service

Servicio FastAPI autónomo para el aporte analítico de SAVP‑TIS3. No usa Laravel, PostgreSQL, OpenAI, GPU ni XGBoost.

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
- `POST /api/v1/analysis`
- `POST /api/v1/knowledge/search`
- `POST /api/v1/tutor/query`

El contrato completo está en `../docs/peter3/API_CONTRACT.md`.

## RIASEC y licencia

Los reactivos españoles de O*NET® Mini‑IP se reproducen literalmente bajo CC BY‑ND 4.0. Incluye información de O*NET® Career Exploration Tools del U.S. Department of Labor, Employment and Training Administration (USDOL/ETA). O*NET® es marca de USDOL/ETA. USDOL/ETA no ha aprobado ni respaldado SAVP.

