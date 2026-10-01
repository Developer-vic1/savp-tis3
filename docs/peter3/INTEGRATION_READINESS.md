> **Estado histórico previo a Fusion_Sistema.** Para la procedencia corregida, métricas actuales e integración Laravel/FastAPI validada el 2026-10-01, consultar [FUSION_VALIDATION.md](FUSION_VALIDATION.md) y [FUSION_INTEGRATION.md](FUSION_INTEGRATION.md). Los resultados de este documento se conservan como antecedentes.

# Preparación de integración — PETER 3

Revalidación del 2026-10-01 en `work/peter3-mejoras-fase2`.
Los índices E5 y MiniLM corresponden al corpus actual de 773 chunks.
DEV, freeze y TEST se ejecutaron en orden; el índice E5 quedó
seleccionado. Knowledge y Tutor respondieron HTTP 200 con `trace_id`
en el smoke con `TestClient`. Analysis E2E, Full E2E, prompts,
suite completa, Ruff y Mypy pasaron. La suite obtuvo 127 passed,
1 skipped (OCR real opcional) y 92 % de cobertura.
`FINAL_STATUS.md` recoge el estado de todos los gates.

Estado del servicio FastAPI para integración: `PETER3_INTEGRATION_READY`.

La preparación es del servicio FastAPI y su contrato. PETER 2 debe
implementar y probar el cliente Laravel en la siguiente etapa; no se
modificó PHP, UI ni base de datos aquí.

## Ejecución reproducible

Desde `ai-service`, usar Python 3.12 x64 y
`python -m uv sync --locked --group dev`. El lock fija los wheels CPU
oficiales de Torch/torchvision que cargaron en VicDev y scikit-learn
1.8.0. El arranque de producción y las credenciales deben configurarse
para el host real; los tests de esta fase usaron ASGI `TestClient`,
sin servidor persistente.

Variables relevantes: `SAVP_AI_ENV`, `SAVP_AI_API_KEY`,
`SAVP_AI_HOST`, `SAVP_AI_PORT`,
`SAVP_AI_LOCAL_LLM_ENABLED`, `SAVP_AI_LOCAL_LLM_URL`,
`SAVP_AI_LOCAL_LLM_MODEL` y
`SAVP_AI_LOCAL_LLM_TIMEOUT_SECONDS`. En `production`, faltar la
clave configurada produce 503; una clave de cliente errónea produce
401. El LLM local es opcional: el tutor estructurado funciona sin él.

## Contrato HTTP

| Método y ruta | Uso |
|---|---|
| `GET /health` | Estado del servicio. |
| `GET /api/v2/riasec/instrument` | 30 reactivos oficiales y escala 1–5. |
| `POST /api/v2/riasec/score` | Scoring de 30 respuestas públicas. |
| `POST /api/v2/analysis` | Análisis V2 basado en evidencia. |
| `POST /api/v1/knowledge/search` | Evidencia oficial recuperada y citas. |
| `POST /api/v1/tutor/query` | Tutor estructurado, citas o abstención. |
| `POST /api/v1/analysis` | Contrato legacy experimental. |

Los POST aceptan y devuelven JSON. Si hay clave configurada,
enviar `X-SAVP-AI-Key`. Cada respuesta incluye `X-Trace-Id`;
los contratos funcionales incluyen también `trace_id` en el cuerpo.
Los errores usan `{"error":{"code","message","trace_id","details"}}`.
El contrato detallado y ejemplos están en
`PETER2_INTEGRATION_CONTRACT.md`.

PETER 2 debe preservar estados `null`/`INSUFFICIENT`, evitar recalcular
RIASEC, propagar trazas y aplicar timeouts medidos en su despliegue.
La carga fría de E5 tardó 135 s en el smoke de VicDev, frente a
latencias calientes mucho menores; planificar calentamiento y un
timeout de primera consulta. Las cifras locales no son SLA.
La validación psicométrica boliviana, soporte semántico de citas y la
integración PHP real siguen fuera de este gate técnico.
