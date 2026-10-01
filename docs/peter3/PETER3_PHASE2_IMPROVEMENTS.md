# PETER 3 — fase 2: mejoras y validación

Actualización fase 2.1: se confirmó el bloqueo de Code Integrity para Torch y mypy mediante
eventos 3033/3077; se añadieron scripts separados para reconstrucción, DEV, freeze y TEST en una
segunda laptop. Su ejecución con embeddings reales y los gates finales siguen pendientes. Véase
`PHASE21_WIP_CHECKPOINT.md`.

Fecha de trabajo: 2026-09-30. Rama `work/peter3-mejoras-fase2`, creada desde `ae386c7`. `feature/APORTE` y la rama de respaldo no se modificaron. El estado final y los gates se detallan en `FINAL_STATUS.md`.

## Cambios realizados

- API `GET /api/v2/riasec/instrument` y `POST /api/v2/riasec/score`: 30 respuestas públicas 1–5, conversión a la escala interna 0–4 y una única función de scoring con Analysis V2. Incluye validación estricta, desempate determinista, atribución, límites y `trace_id`.
- Comparación literal de los 30 pares área/texto, en orden, contra el asset oficial español de O*NET. La licencia distingue copia literal CC BY-ND 4.0 de adaptaciones sujetas a licencia de desarrollador y validación. Se añadieron pruebas de fingerprint y casos adversos del contrato.
- Producción falla de forma cerrada si falta `SAVP_AI_API_KEY`; un valor desconocido de `SAVP_AI_ENV` se rechaza.
- Las métricas de soporte semántico de citas de prompts quedan `NOT_EVALUATED`: la presencia de un ID de cita no prueba que respalde una afirmación.
- Cuatro snapshots nuevos de páginas oficiales UCB, con URL final, fecha UTC, content-type y SHA-256 real; originales preservados. Registro `2.1.0`, corpus reconstruido para esos HTML y metadatos de versión reparados en 265 chunks ministeriales con hash verificado.
- `verify_sources.py` comprueba la cadena fuente–corpus–índice; la carga de índices rechaza hash/modelo/chunks desactualizados y la API responde 503.
- Verificador central, smoke HTTP, benchmarks, contrato de integración PETER 2, recomendación de CI, mapas DSRM/ICONIX y alineación con fases del informe.

## Verificación y bloqueo

El registro contiene 12 fuentes y 11 referencias; el catálogo tiene 5 ofertas. Los IDs revisados en catálogo, bridge, crosswalk, parámetros y corpus son válidos. Fuentes y corpus de 773 chunks pasan. Los dos índices FAISS están `STALE`: 26 títulos de los perfiles cambiaron y forman parte de la entrada de embeddings. Se requiere reconstrucción real y repetición de retrieval DEV/TEST.

Suite completa: **125 passed, 2 failed, 1 skipped; cobertura 92 %**. Las dos fallas corresponden al hash del índice y al E2E de búsqueda, que recibe 503. Ruff y `git diff --check` pasan. Mypy no inicia porque Windows bloquea `librt.internal`; Torch instalado no puede cargar su DLL nativa. FAISS sí carga. Los 13 escenarios de prompts pasan, con soporte semántico `NOT_EVALUATED`; Recommendation V2 supera invariantes sintéticos. El smoke HTTP real pasa health, RIASEC, análisis V2 y 8/8 peticiones RIASEC concurrentes; búsqueda y tutor responden 503. El benchmark local caliente de análisis y la medición de inicio de app están registrados; retrieval actual, OCR real y LLM local no fueron revalidados.

## Alcance científico e integración

RIASEC describe intereses y carece de validación psicométrica boliviana. Bridge y crosswalk son inferencias documentales, no equivalencias ni requisitos de admisión. Recommendation V2 separa intereses, rendimiento académico, preparación observada y calidad de evidencia; no predice éxito. El catálogo no es exhaustivo. No se implementó XGBoost: faltan dataset longitudinal, objetivo, ground truth y evaluación. La fase 4 del informe sigue `NOT_IMPLEMENTED_AS_WRITTEN`; se recomienda `UPDATE_REPORT`. DSRM dispone de demostración técnica por casos, sin UI estudiantil; no se reclama ICONIX completo.

PETER 2 debe consumir el contrato en `PETER2_INTEGRATION_CONTRACT.md`, sin recalcular RIASEC en PHP. La integración PHP real está pendiente. Dado que fallan gates críticos, no hay commit final ni push. Estado: `PETER3_NOT_READY`.
