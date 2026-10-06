# Auditoría técnica de Aporte Ingenieril SAVP — Fase 2.1

Fecha: 2026-10-01. Rama `work/peter3-mejoras-fase2`. Alcance:
`ai-service/**` y `docs/peter3/**`. Checkpoint de partida:
`388890c2e004116e16374582da810406f4125508`.
La auditoría del 2026-09-29 sobre `feature/APORTE` es histórica;
sus cifras de retrieval y tests no describen el corpus actual.

| Gate | Resultado y evidencia |
|---|---|
| Fuentes y referencias | PASS: 12 snapshots con SHA válido y 11 referencias |
| Corpus e integridad referencial | PASS: 773 IDs únicos, textos no vacíos y fuentes resueltas |
| Índice E5 | PASS: 773×384; corpus SHA actual y orden de chunks idéntico |
| Índice MiniLM | PASS: 773×384; corpus SHA actual y orden de chunks idéntico |
| Retrieval | DEV ejecutado para dos modelos; E5 congelado; TEST único; verificador PASS |
| RIASEC | PASS: 25 tests de contrato, instrumento y scoring públicos |
| Recommendation V2 | PASS: invariantes y evidencia trazable |
| Prompts e inyección | PASS: 13/13; 0 éxitos observados en escenarios adversariales |
| Knowledge y Tutor | PASS: HTTP 200 con evidencia, citas y trace IDs |
| Analysis E2E / Full E2E | PASS / PASS |
| Pytest y cobertura | 127 passed, 1 skipped OCR opcional, 0 failed; 92 % |
| Ruff / Mypy | PASS / PASS: 0 errores en 82 y 111 archivos |
| Seguridad | PASS: fail closed, rechazo de key y payload inválidos, errores saneados |
| Smoke y concurrencia | PASS: seis rutas HTTP 200 en proceso, 8/8 RIASEC concurrentes |
| Rendimiento | Medianas y p95 medidos con N=20; carga fría separada; sin SLA |
| Verificador global | PASS: todos los gates completados tras ampliar el timeout para carga fría |
| Git diff check | PASS |

El corpus y ambos índices comparten SHA-256
`ebd98f3fb35058af6ff074673cccc56053d9f2ee064ee31a86ed8d25c3e4c5ce`.
Las métricas DEV/TEST y la decisión E5 se detallan en
`RETRIEVAL_EVALUATION.md`; el freeze conserva parámetros experimentales.
No se infiere soporte semántico de una cita por el solo hecho de existir:
`CITATION_SUPPORT = NOT_EVALUATED`.

La API queda preparada para que PETER 2 realice su integración Laravel.
No se hizo merge ni se modificó Laravel. La validación psicométrica
boliviana, revisión experta de Bridge, catálogo exhaustivo, predictor
de éxito y LLM local permanecen fuera de este cierre técnico.
