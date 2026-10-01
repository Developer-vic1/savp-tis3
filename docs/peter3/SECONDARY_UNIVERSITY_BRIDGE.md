# Puente secundaria–universidad

> Estado auditado el 2026-09-30: existe un puente V1 de 12 relaciones experimentales, pendiente de revisión experta. No existe puente V2 ni clasificación `DIRECTLY_DOCUMENTED`/`DOCUMENT_SUPPORTED_INFERENCE`/etc. Véase `PETER3_FINAL_AUDIT.md`.

## Problema

Una asignatura secundaria y una universitaria no son equivalentes por compartir palabras. El
motor necesita relaciones explícitas para explicar qué evidencia alimenta preparación y por
qué, sin presentar una asociación manual como hecho científico.

## Evidencia

Las fuentes ministeriales documentan áreas de secundaria y especialidades BTH; las mallas
universitarias documentan asignaturas iniciales. La relación intermedia —contenido,
competencia y conocimiento requerido— es una **CONFIGURACIÓN EXPERIMENTAL** que exige
revisión pedagógica experta.

## Componente

`ai-service/data/bridge/secondary_university_v1.json` representa:

```text
contenido secundaria → competencia → conocimiento universitario → asignatura → carrera
```

## Contrato

Cada relación guarda `relation_id`, `source_secondary`, `source_university`, justificación,
peso, confianza/estado, `review_status` y versión. Además conserva el contenido, competencia,
conocimiento, asignatura y `career_ids`. Todas las relaciones iniciales están marcadas
`NEEDS_EXPERT_REVIEW`; ninguna declara convalidación, equivalencia ni continuidad obligatoria.

## Pruebas

- IDs únicos y carreras/fuentes existentes;
- pesos en 0–1 y suma de requisitos por carrera igual a 1;
- cada requisito de preparación referencia una relación aplicable a esa carrera;
- etiquetas limitadas a `HECHO DOCUMENTADO`, `DECISIÓN DE DISEÑO`,
  `CONFIGURACIÓN EXPERIMENTAL` o `HIPÓTESIS`;
- determinismo al cargar la misma versión.
