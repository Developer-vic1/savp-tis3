# Handoff Aporte Ingenieril SAVP ↔ artefactos de datos

## Artefactos consumidos sin modificación

Aporte Ingenieril SAVP lee, pero no edita:

- `ai-service/data/bridge/secondary_university_v2.json`;
- `ai-service/data/crosswalk/career_occupation_v1.json`;
- `ai-service/data/catalog/careers.json`;
- `ai-service/data/sources/sources.json`.

El loader del bridge exige las relaciones y metadatos actualmente publicados, incluidos
`secondary_competency`, `evidence_label`, gobierno y estados permitidos. Si el bridge V2 no
existe, usa un adaptador explícito V1 y emite warning; no fabrica un bridge final.

El crosswalk ocupacional habilita referencias RIASEC solo cuando una relación contiene perfil
ocupacional documentado. Si falta, el resto del análisis continúa con estado `UNAVAILABLE`.

## Contratos que deben conservarse

- IDs de carrera deben coincidir con el catálogo.
- `source_ids` deben resolverse en el manifiesto.
- estados presentes del bridge: 15 `DOCUMENT_SUPPORTED_INFERENCE` y 2 `HYPOTHESIS`; no hay
  relaciones `DIRECTLY_DOCUMENTED`;
- tipos ocupacionales: `RELATED_OCCUPATION`, `POSSIBLE_OCCUPATION`,
  `INSUFFICIENT_EVIDENCE`;
- toda relación debe conservar versión, justificación y limitaciones.

Un cambio de schema o versión debe acompañarse de fixture y prueba de loader. Aporte Ingenieril SAVP no
requiere cambios Laravel, migraciones ni coordinación con PETER 2 para ejecutar su E2E.
