# API analítica V2

## Endpoint

`POST /api/v2/analysis`

El endpoint usa la misma clave interna opcional `X-SAVP-AI-Key` que V1. V1 continúa disponible
en `/api/v1/analysis` como baseline legacy.

## Request

Ejemplo mínimo:

```json
{"schema_version":"2.0","student_id":"EST-001"}
```

Los bloques opcionales son `academic`, `attendance`, `vocational`, `technical`,
`learning_activity`, `declared_interests` e `history`. `declared_interests` acepta texto en el
wire y lo normaliza al tipo explícito `DeclaredInterest`. `history` usa
`HistoricalAcademicPeriod`. Campos desconocidos producen 422 porque los modelos usan
`extra="forbid"`.

El bloque legacy `vocational` requiere 30 respuestas únicas con valor entero 0–4. Desde
Fusion_Sistema también se acepta `riasec_public` con `instrument_version` y 30 respuestas
`item_id`/`value` en escala pública 1–5. Aporte Ingenieril SAVP valida y transforma mediante el mismo adaptador
usado por `/api/v2/riasec/score`; Laravel no transforma la escala. Ambos bloques juntos producen
422. Un bloque ausente no es un instrumento con cero respuestas.
Detalles y prueba de integración: [FUSION_INTEGRATION.md](FUSION_INTEGRATION.md).

## Response

La respuesta contiene:

```json
{
  "schema_version": "2.0",
  "engine_version": "2.0.0",
  "criteria_version": "v2_evidence_based",
  "trace_id": "...",
  "student_ref": "EST-001",
  "analysis_status": "PARTIAL",
  "student_snapshot": {
    "academic_evidence": {"status": "UNAVAILABLE", "summary": null},
    "vocational_interest_evidence": {"status": "UNAVAILABLE", "riasec": null},
    "evidence_quality": {"academic_record_count": 0},
    "missing_components": ["academic", "vocational_interest"]
  },
  "career_evidence_profiles": [],
  "traceability": {
    "input_hash": "sha256-en-hex",
    "engine_version": "2.0.0",
    "criteria_version": "v2_evidence_based",
    "bridge_version": "...",
    "bridge_source_mode": "V2_DOCUMENT",
    "catalog_version": "...",
    "crosswalk_version": "...",
    "instrument_version": null,
    "generated_at": "...",
    "trace_id": "..."
  },
  "warnings": [],
  "limitations": [],
  "sources_used": []
}
```

El ejemplo es abreviado: el schema OpenAPI generado por FastAPI es la referencia de campos.

## Estados y faltantes

Los componentes usan `AVAILABLE`, `PARTIAL`, `INSUFFICIENT` y `UNAVAILABLE`. Un dato faltante
se expresa como `null`, lista vacía o estado; nunca se fuerza a 0.

`analysis_status` describe disponibilidad general, no calidad humana. `COMPLETE` requiere los
dos núcleos académico y vocacional; las dimensiones adicionales conservan su propio estado.

## Trazabilidad

El hash usa JSON canónico del request validado. Las versiones de bridge, catálogo, crosswalk e
instrumento vienen de los artefactos cargados. Una versión no disponible se devuelve `null` y
produce warning; nunca se inventa.

## Ausencias deliberadas

No existen `compatibility_score`, ranking global, probabilidad de éxito, inteligencia inferida
ni brecha numérica sin requisito oficial. Las explicaciones son deterministas y no usan LLM.
# RIASEC V2 para PETER 2 (fase 2)

`GET /api/v2/riasec/instrument` entrega los 30 reactivos oficiales, el orden, las opciones
públicas 1–5, la versión, licencia, atribución y limitaciones. `POST /api/v2/riasec/score` exige
la versión exacta y una respuesta para cada ID; convierte `1→0` a `5→4` y usa el mismo
`score_riasec` que Analysis V2. Devuelve seis puntajes 0–20, empates, Holland code y `trace_id`.
El campo `vocational` de `POST /api/v2/analysis` conserva por ahora la escala interna 0–4; el
contrato de integración explica esta distinción. Véase `PETER2_INTEGRATION_CONTRACT.md`.
