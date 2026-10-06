# Catálogo universitario inicial

## Problema

Un ranking por nombres inventados o por una lista genérica de profesiones no es auditable.
Aporte Ingenieril SAVP necesita identidades institucionales estables, vigencia y evidencia curricular real.

## Evidencia

El catálogo inicial usa exclusivamente páginas y documentos oficiales preservados en
`ai-service/data/raw/`, registrados en `data/sources/sources.json` con URL, fecha de
recuperación y SHA-256. Cubre dos universidades de La Paz y cinco identidades de carrera:
cuatro de la UCB y una de la UMSA. No pretende representar toda Bolivia.

## Componente

- `data/catalog/careers.json`: universidades, carreras, alias, vigencia, áreas oficiales,
  primeras asignaturas y fuentes.
- `data/sources/sources.json`: gobierno y versiones de fuente.
- `data/raw/`: snapshots binarios/HTML inmutables de esta versión.

## Contrato

`career_id` identifica una carrera dentro de una universidad y sede; por eso las dos
Ingenierías Civiles no se fusionan. Los alias solo ayudan a homologar entradas. Cada carrera
debe tener al menos una fuente oficial activa, `valid_from`, `status` e `initial_subjects`.
Un perfil de ingreso o profesional no publicado en las fuentes revisadas permanece `null`.

## Pruebas previstas

- IDs únicos y referencias de universidad/fuente existentes;
- hash local igual al manifiesto;
- vigencia y al menos una asignatura inicial por carrera;
- ninguna fuente no oficial en el catálogo operativo;
- conservación de identidades institucionales aunque compartan el mismo nombre.

## Alcance y revisión

Las descripciones oficiales se resumen sin convertirlas en criterios de afinidad. Los pesos,
perfiles RIASEC y prerrequisitos académicos se incorporan en artefactos separados como
**CONFIGURACIÓN EXPERIMENTAL / NEEDS_EXPERT_REVIEW**.
