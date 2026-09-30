# Auditoría final PETER 3

Fecha: 2026-09-29. Rama: `feature/APORTE`. Alcance: `ai-service/**` y `docs/peter3/**`.

| GATE | CHECK | RESULT | EVIDENCE | CORRECTION | FINAL_STATUS |
|---:|---|---|---|---|---|
| 1 | Código y frontera | PASS | Ruff, mypy, pytest; sin cambios Laravel | ciclo de importación de prompts eliminado | PASS |
| 2 | RIASEC | PASS | 30 ítems, 0–4, suma 0–20, tests | documentación 0–4/1–5 aclarada | PASS |
| 3 | Fuentes | PASS | 12 snapshots oficiales + 11 referencias | añadida RM 0190/2024 y registry de referencias | PASS |
| 4 | Hashes | PASS | 12/12 SHA-256 válidos | snapshot ministerial incorporado con hash | PASS |
| 5 | Integridad referencial | PASS | 0 IDs huérfanos; test cruzado | referencias externas/internas resolubles | PASS |
| 6 | Bridge | PASS | 17 relaciones: 15 inferencias, 2 hipótesis | 4 estados directos degradados | PASS |
| 7 | Parámetros | PASS | 29 entradas y alineación con constantes | clases y valores reales corregidos | PASS |
| 8 | Catálogo | PASS | cinco ofertas de UCB/UMSA | documentación sincronizada | PASS |
| 9 | Crosswalk | PASS | 15 relaciones, códigos oficiales | perfiles/códigos O*NET 31.0 corregidos | PASS |
| 10 | Learning Analytics | PASS | medias, escala, periodos, asistencia y actividad probados | sin cambio material | PASS |
| 11 | Recommendation V2 | PASS | no score, no ranking, nulos honestos, 5 perfiles | refs de evidencia ahora resuelven | PASS |
| 12 | API V2 | PASS | contrato estricto y E2E HTTP | sin cambio material | PASS |
| 13 | Robustez | PASS | invariantes V2; sensibilidad legacy identificada | alcance legacy explicitado | PASS |
| 14 | Corpus | PASS | 12 fuentes, 773 chunks, 35 chunks OCR | RM 0190 añadida por merge determinista | PASS |
| 15 | FAISS | PASS | 2 índices exactos, 773×384, hash/orden iguales | índices reconstruidos | PASS |
| 16 | Retrieval | PASS | A/B reproducido; E5 híbrido Recall@5 0.6667 | métricas recalculadas | PASS |
| 17 | Dataset retrieval | PASS | 15 DEV + 15 TEST, disjuntos, IDs válidos | tests de integridad añadidos | PASS |
| 18 | Prompts | PASS | 13/13, esquema/citas/abstención | evaluator dejó de usar resultados esperados como evidencia | PASS |
| 19 | Injection | PASS | éxito 0.0000 en 4 casos adversariales | cuarentena de evidencia y delimitadores | PASS |
| 20 | Tutor | PASS | retrieval real, respuesta/citas/fallback | endpoint y parámetros sincronizados | PASS |
| 21 | Evaluador prompts | PASS | `prompt_results.json` reproducido | métricas de soporte calculadas | PASS |
| 22 | LLM | UNAVAILABLE | conexión a `127.0.0.1:8091` rechazada | estado honesto, sin métricas inventadas | ACCEPTED LIMITATION |
| 23 | Full E2E | PASS | prueba real + demo de 14 bloques | artefactos creados | PASS |
| 24 | Performance | PASS | medianas/p95 de cuatro componentes, N=20 | benchmark integral creado | PASS |
| 25 | Tests | PASS | 100 passed, 1 skipped, cobertura 92% | aserción histórica 11→12 corregida | PASS |
| 26 | Ruff | PASS | `ruff check .` | sin deuda activa | PASS |
| 27 | Mypy | PASS | producción y repositorio completo | adapter tipado PyMuPDF, sin ignores masivos | PASS |
| 28 | Documentación | PASS | README y documentos Peter 3 sincronizados | cifras/estados históricos depurados | PASS |

El estado `UNAVAILABLE` del gate 22 no bloquea el proveedor estructurado ni el criterio de
completitud: el LLM es opcional, está deshabilitado por defecto y su falta de runtime se registra
sin convertirla en PASS.
