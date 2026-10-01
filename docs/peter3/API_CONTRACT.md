# Contrato API v1 preliminar

Base path: `/api/v1`. Esquema: `1.0`. Este contrato debe ser revisado por PETER 2 antes de integrar.

## `GET /health`

Respuesta 200 exacta:

```json
{"status":"ok","service":"savp-ai","service_version":"0.1.0","schema_version":"1.0"}
```

## `POST /api/v1/analysis`

Mínimo válido:

```json
{"schema_version":"1.0","student_id":"EST-001"}
```

Bloques opcionales implementados: `academic_period`, `course`, `academic`, `attendance`, `vocational`, `technical`, `declared_interests`, `history`, `learning_activity`.

`vocational`, si existe, usa:

```json
{
  "instrument_id":"onet-mini-ip-2.0-es",
  "instrument_version":"2.0-es-2025",
  "responses":[{"item_id":1,"value":0}]
}
```

Para puntuar deben estar los 30 `item_id` únicos, cada valor entero entre 0 y 4. Un bloque incompleto produce 422 `SAVP_AI_INCOMPLETE_INSTRUMENT`; la ausencia total del bloque produce 200 con `PARTIAL` o `INSUFFICIENT`.

La respuesta incluye: `status`, `trace_id`, `generated_at`, versiones, `input_hash`, `coverage`, `missing_components`, `data_quality_status`, `vocational_profile`, `academic_profile`, `strengths`, `areas_to_reinforce`, `warnings` y `sources_used`.

## `POST /api/v1/knowledge/search`

Entrada: `schema_version`, `query`, `top_k` (1–20) y filtros opcionales. Consulta el corpus
versionado y devuelve evidencia oficial; si el índice no puede cargarse responde 503
`SAVP_AI_KNOWLEDGE_INDEX_UNAVAILABLE`. No se fabrican fuentes.

## `POST /api/v1/tutor/query`

Entrada: `schema_version`, `question`, `subject`, `level`, `academic_context`, `student_context`. El primer hito devuelve modo `STRUCTURED`, evidencia insuficiente y advertencias; nunca inventa material sin fuentes.

## Envelope de error

```json
{
  "error": {
    "code": "SAVP_AI_INVALID_REQUEST",
    "message": "La solicitud no cumple el contrato.",
    "trace_id": "uuid",
    "details": []
  }
}
```

## Autenticación interna

Si `SAVP_AI_API_KEY` tiene valor, todos los endpoints `/api/v1/*` requieren `X-SAVP-AI-Key`. `/health` permanece sin secretos. En desarrollo la clave puede dejarse vacía; nunca se versiona.
