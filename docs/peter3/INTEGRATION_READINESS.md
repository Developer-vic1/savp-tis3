# Integration readiness — PETER 3

Checkpoint de fase 2.1: la verificación de fuentes/corpus pasa por separado, pero ambos índices
siguen `STALE`. La reconstrucción está preparada para otro equipo en `PHASE21_WIP_CHECKPOINT.md`;
no hay aprobación de integración ni commit final.

Estado: `PETER3_NOT_READY` mientras se reconstruyen los índices y se reejecutan todos los
gates de fase 2. Este aporte no contiene cliente Laravel, migraciones, UI ni escrituras en base de
datos. El contrato detallado para PETER 2 está en `PETER2_INTEGRATION_CONTRACT.md`.

## Arranque

```powershell
cd ai-service
.\.venv\Scripts\python.exe -m uvicorn app.main:app --host 127.0.0.1 --port 8001
```

Variables relevantes: `SAVP_AI_ENV`, `SAVP_AI_API_KEY`, `SAVP_AI_HOST`, `SAVP_AI_PORT`,
`SAVP_AI_LOCAL_LLM_ENABLED`, `SAVP_AI_LOCAL_LLM_URL`, `SAVP_AI_LOCAL_LLM_MODEL` y
`SAVP_AI_LOCAL_LLM_TIMEOUT_SECONDS`. En `production` la API protegida exige una clave
configurada; con configuración vacía devuelve `503`. Usar red privada y logging sin cuerpos ni
datos de estudiante.

## Contrato HTTP

| Método y ruta | Uso | Entrada | Salida |
|---|---|---|---|
| `GET /health` | salud | ninguna | servicio, versión y schema `1.0` |
| `GET /api/v2/riasec/instrument` | cuestionario | ninguna | 30 reactivos, escala 1–5 y atribución |
| `POST /api/v2/riasec/score` | scoring | versión y 30 respuestas públicas 1–5 | seis scores 0–20, códigos y trace |
| `POST /api/v1/analysis` | legacy | `AnalysisRequest`, schema `1.0` | `AnalysisResponse` experimental |
| `POST /api/v2/analysis` | principal futuro | `AnalysisV2Request`, schema `2.0` | `AnalysisV2Response` basada en evidencia |
| `POST /api/v1/knowledge/search` | búsqueda | query, top-k y filtros opcionales | resultados, suficiencia, corpus/modelo/retrieval |
| `POST /api/v1/tutor/query` | tutor | pregunta y contexto allowlisted opcional | respuesta, modo, citas, suficiencia y versiones |

Todos los POST aceptan y devuelven JSON. Si existe una key configurada, enviar
`X-SAVP-AI-Key`. Cada respuesta incluye `X-Trace-Id`; el cuerpo también contiene `trace_id` en
los contratos funcionales.

## Versionado y semántica

V1 es legacy y conserva puntuaciones experimentales para compatibilidad. V2 es el contrato que
debe consumir una integración nueva: presenta constructos por separado, estados de evidencia,
fuentes y limitaciones. Una ausencia debe conservarse como `null`/`INSUFFICIENT`, nunca convertirse
en cero. Los consumidores no deben fabricar un score o ganador a partir de perfiles V2.

## Errores y resiliencia

Los errores tienen forma `{"error":{"code","message","trace_id","details"}}`. Los estados
esperables incluyen 401 por key inválida/ausente, 422 por contrato, 503 por índice indisponible y
500 saneado para errores imprevistos. La integración debe registrar el trace, no el payload
completo, y tratar 4xx como error de contrato y 5xx como recuperable.

Timeout sugerido inicial: 5 s para health/RIASEC y 30 s para análisis/knowledge/tutor estructurado,
validándolo con carga real. Si se habilita LLM local, el presupuesto debe ser mayor que el timeout
interno configurado (45 s) y contar con circuit breaker; el servicio mantiene fallback
estructurado. Estos valores son recomendaciones operativas, no SLA medidos.

## Checklist para PETER 2

1. Acordar DTOs V2 y política de nullability.
2. Proveer secret y rotación de `X-SAVP-AI-Key`.
3. Implementar cliente con timeout, retry acotado solo para fallos transitorios y propagación de
   trace ID.
4. Mapear estados de evidencia sin reinterpretar constructos.
5. Añadir pruebas de contrato entre repositorios y observabilidad.
6. Ejecutar pruebas de carga y privacidad antes de habilitar usuarios.
