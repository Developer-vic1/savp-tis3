# Preparación para una integración posterior con PETER 2

Fecha: 2026-09-30. Estado: **NOT_READY**. Esta página describe el contrato visible en código; los endpoints no pasaron una prueba HTTP en esta auditoría.

| Ruta | Estado en código | Nota |
|---|---|---|
| `GET /health` | registrada | Sin clave; declara `service_version=0.1.0`, `schema_version=1.0` |
| `POST /api/v1/analysis` | registrada | V1 experimental; `schema_version=1.0`, ranking y umbrales no validados |
| `POST /api/v2/analysis` | ausente | No integrar ni anunciar todavía |
| `POST /api/v1/knowledge/search` | registrada | Depende de FAISS y del modelo de embeddings; integridad del índice pendiente |
| `POST /api/v1/tutor/query` | registrada | Respuesta estructurada sin LLM por defecto; dependencia del índice |

## Seguridad y errores

- Las rutas `/api/v1/*` verifican `X-SAVP-AI-Key` solo cuando `SAVP_AI_API_KEY` tiene valor; un valor ausente deja la autenticación desactivada. `/health` no requiere clave.
- El middleware genera `X-Trace-Id`; los errores usan un envelope con `trace_id`, código, mensaje y detalles. Validaciones de solicitud devuelven 422, clave inválida 401 e índice indisponible 503 según el código. Los valores HTTP no se verificaron en vivo.
- El LLM local está desactivado por defecto. Si se activa y falla, el código conserva la respuesta estructurada. El timeout por defecto del LLM es 45 s; no hay presupuesto de tiempo integral verificado para la búsqueda ni para la llamada HTTP completa.
- El análisis V1 incluye `input_hash`; no implica deduplicación ni persistencia. La versión de criterios V1 es `v1_experimental`. No existe esquema V2 con versiones de bridge, crosswalk y catálogo en la respuesta.

## Condiciones previas

Resolver hashes de fuentes e índice, instalar el entorno fijado, ejecutar pruebas y smoke tests, acordar DTOs y escalas (RIASEC 1–5 en web oficial frente a 0–4 en API V1), fijar autenticación y timeouts, y definir el contrato V2. No se implementó consumidor Laravel.
