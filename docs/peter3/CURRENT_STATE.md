# Estado actual de PETER 3

> Auditoría ampliada 2026-09-28: la solicitud vigente exige también Learning
> Analytics completo, conocimiento oficial, catálogo universitario, puente curricular,
> ingesta/OCR, recuperación semántica, tutor basado en evidencia y evaluación real de
> un LLM local. Esos bloques se controlan en `REQUIREMENTS_MATRIX.md`. Las secciones
> históricas de este documento describen el núcleo inicial y no deben interpretarse
> como una declaración de completitud del aporte total.

Fecha: 2026-09-28.

## Qué se investigó

- Repositorio, ramas, worktrees, Markdown, informes históricos y frontera Laravel/Python.
- O*NET® Interest Profiler: versión web vigente, idioma español, licencia, escala, scoring, confiabilidad y validez reportada.

## Qué se implementó

- Worktree aislado sobre `feature/APORTE` desde `integration/savp-consolidado`.
- Documentación inicial de continuidad y contrato v1.
- Servicio FastAPI, contratos Pydantic, envelope estable de errores, fixtures y pruebas.
- Scoring determinista O*NET® Mini‑IP 2.0 español.
- Snapshot de perfil y analítica académica descriptiva.

## Qué funciona

- `GET /health` sin datos sensibles.
- `POST /api/v1/analysis` para perfiles completos, parciales e insuficientes.
- Diferenciación explícita de afinidad/intereses frente a preparación académica.
- Manejo de faltantes, empates RIASEC, hash de entrada, versiones y `trace_id`.

## Qué no funciona todavía

- Catálogo validado de carreras bolivianas, afinidad, preparación por carrera, ranking, brechas y rutas.
- Corpus oficial, ingestión, OCR, embeddings, retrieval, tutor con fuentes y LLM local.
- Integración Laravel/PostgreSQL (deliberadamente no iniciada).

## Entorno real

- OS: Microsoft Windows NT 10.0.26200.0.
- Python del host: 3.14.0 (`C:\Python314\python.exe`).
- `uv` no estaba instalado al auditar; se instala dentro de `ai-service/.venv` y se registra después de resolver el entorno.

## Dependencias instaladas

Lock generado con `uv 0.12.19`. Directas: FastAPI `0.141.1`, Pydantic `2.13.5`, pydantic-settings `2.15.0`, Uvicorn `0.54.0`; desarrollo: HTTPX `0.28.1`, pytest `9.1.1`, pytest-asyncio `1.4.0`, pytest-cov `7.1.0`, coverage `7.16.2`, Ruff `0.16.9`, mypy `1.20.2`, psutil `7.2.2`. `ai-service/uv.lock` fija también todas las transitivas.

## Resultados reales y mediciones

- 16 pruebas aprobadas; cobertura de líneas 97%.
- Ruff: sin hallazgos. Mypy estricto: sin hallazgos en 23 archivos fuente.
- Benchmark sintético N=1,000: media `0.3740 ms`, p95 `0.7061 ms` para cálculo directo; importación fría media `1,216.3399 ms` (N=5).
- Advertencia no bloqueante: FastAPI `TestClient` emite una deprecación de Starlette sobre `httpx`; no afecta respuestas ni producción.
- No hay métricas predictivas ni afirmaciones de validación boliviana.

## Archivos creados/modificados

Solo `ai-service/**` y `docs/peter3/**` en el worktree aislado. No se modificó ningún archivo Laravel, SQL, PHP, Node ni del worktree original.

## Decisiones

Mini‑IP 2.0 español literal; core offline; estados por evidencia; afinidad/preparación separadas; conocimiento sin corpus falla de forma explícita. Detalle en `DECISIONS.md`.

## Pendientes

Fuentes bolivianas, catálogo universitario, puente de competencias, afinidad, preparación, ranking, gaps, rutas, ingestion/OCR, retrieval y evaluación de LLM local.

## Dependencias para PETER 2

Validar DTO, nullability, timeout, autenticación interna y fallback. Véanse `PETER2_INTEGRATION.md` y `PETER2_INTEGRATION_REQUEST.md`.

## Riesgos

- El contrato de PETER 2 todavía no existe; el contrato aquí es preliminar.
- Python 3.14 es reciente y puede limitar compatibilidad de paquetes científicos posteriores.
- La traducción O*NET® se reproduce literalmente; no se localiza para Bolivia sin un proceso de validación.
- El Laravel actual mezcla promedio y “compatibilidad”; integrar ambos resultados sin corregir semántica confundiría preparación con afinidad.

## Próximo paso recomendado

Validar el contrato con PETER 2 y comenzar gobernanza de fuentes oficiales bolivianas antes de calcular afinidad o preparación por carrera.
