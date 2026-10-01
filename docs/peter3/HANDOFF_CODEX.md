> **Nota histórica (2026-09-30):** Este documento describe la línea base de esa fecha. El estado vigente de corpus, FAISS y gates está en `FINAL_STATUS.md`.

# Handoff técnico PETER 3

Estado: 2026-09-29.

## Artefactos canónicos

| Artefacto | Estado |
|---|---|
| `data/sources/sources.json` | 12 fuentes oficiales con snapshots y SHA-256 válido |
| `data/sources/references.json` | 11 referencias externas/internas resolubles |
| `data/processed/corpus.jsonl` | 773 chunks trazables |
| `data/indexes/selected.json` | E5 seleccionado, FAISS exacto de 773 vectores |
| `data/bridge/secondary_university_v2.json` | 15 inferencias documentales + 2 hipótesis |
| `data/crosswalk/career_occupation_v1.json` | 15 inferencias con O*NET 31.0/ISCO-08 |
| `data/parameters/registry.json` | 29 parámetros clasificados |
| `data/evaluation/retrieval_results.json` | benchmark A/B de 30 consultas |
| `data/evaluation/prompt_results.json` | benchmark de 13 escenarios |
| `data/evaluation/performance_results.json` | mediana/p95 por componente |

Analysis V2 produce perfiles de evidencia por carrera y prohíbe score compuesto, ranking global y
probabilidad de éxito. Knowledge V1 devuelve chunks oficiales con localizador, versión del corpus
y modelo. Tutor V1 responde desde evidencia recuperada, conserva abstención y devuelve citas.

```powershell
.venv312\Scripts\python.exe -m pytest
.venv312\Scripts\python.exe -m ruff check .
.venv312\Scripts\python.exe -m mypy .
.venv312\Scripts\python.exe scripts\run_peter3_full_demo.py
```

No se realizó integración Laravel. El LLM local continúa sin evaluación por falta de runtime; el
proveedor estructurado es el comportamiento operativo verificado.
