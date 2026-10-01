# Evaluación de retrieval — fase 2.1

Evaluación ejecutada el 2026-10-01 sobre el corpus de 773 chunks con SHA-256
`ebd98f3fb35058af6ff074673cccc56053d9f2ee064ee31a86ed8d25c3e4c5ce`.
Los índices E5 y MiniLM se reconstruyeron con embeddings reales: 773 vectores
L2 normalizados de dimensión 384 por modelo. `test_index_integrity.py` y
`verify_sources.py` pasaron.

Los datasets DEV y TEST contienen 15 consultas cada uno, con relevancia manual a
nivel de chunk. Las métricas de relevancia se calculan sobre las consultas con
chunks relevantes. Se compararon búsqueda semántica y fusión híbrida BM25 + RRF.
Las latencias son de este host Windows y pueden incluir la carga inicial del
modelo; no constituyen un SLA.

## DEV — antes del freeze

| Modelo / método | Recall@1 | Recall@3 | Recall@5 | MRR | nDCG@5 | Mediana ms | p95 ms |
|---|---:|---:|---:|---:|---:|---:|---:|
| MiniLM semántico | 0.0897 | 0.2051 | 0.2821 | 0.2718 | 0.2173 | 78.0101 | 48720.2379 |
| MiniLM híbrido | 0.1410 | 0.2821 | 0.3846 | 0.3489 | 0.3066 | 96.1011 | 360.4241 |
| E5 semántico | 0.1667 | 0.2692 | 0.5128 | 0.3943 | 0.3621 | 157.5853 | 192863.5674 |
| E5 híbrido | 0.2564 | 0.4744 | 0.6026 | 0.5238 | 0.4817 | 64.2446 | 32082.0153 |

E5 híbrido superó a MiniLM híbrido en las cinco métricas de relevancia DEV.
Se conservó E5 y no se ajustaron parámetros para casos individuales.

## Freeze

`retrieval_freeze_phase21.json` fija E5, corpus SHA, hash del resultado DEV,
`top_k=10`, `rrf_k=60`, pesos semántico/léxico `1.0/1.0` y BM25
`k1=1.5`, `b=0.75`. Estado: `EXPERIMENTAL_UNCHANGED`.
Se generó antes de ejecutar TEST. El archivo `selected.json` señala al
índice E5 actual.

## TEST — una ejecución tras el freeze

| Método | Recall@1 | Recall@3 | Recall@5 | MRR | nDCG@5 | Mediana ms | p95 ms |
|---|---:|---:|---:|---:|---:|---:|---:|
| E5 semántico | 0.3095 | 0.5119 | 0.7262 | 0.5156 | 0.5399 | 88.1478 | 110613.4571 |
| E5 híbrido | 0.3810 | 0.8690 | 0.8929 | 0.6548 | 0.7043 | 59.0309 | 548.1240 |

`verify_retrieval_phase21.py` pasó: DEV, freeze, TEST, selección y manifests
apuntan al mismo corpus. Las latencias p95 extremadamente altas en algunas
series incluyen cargas frías del runtime/modelo en Windows; el benchmark de
proceso caliente se informa por separado en `PERFORMANCE.md`.

El conjunto contiene categorías `NO_ANSWER`, `AMBIGUOUS` y `ADVERSARIAL`.
Las pruebas de tutor y los 13 escenarios de prompts comprobaron abstención,
limitaciones y rechazo de instrucciones adversariales en sus casos evaluados.
Estas pruebas no demuestran cobertura universal. El soporte semántico de cada
cita frente a cada afirmación sigue `NOT_EVALUATED`.

Artefactos reproducibles: `ai-service/data/evaluation/retrieval_dev_phase21.json`,
`retrieval_freeze_phase21.json` y `retrieval_test_phase21.json`.
