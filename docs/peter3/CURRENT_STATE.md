> **Estado histórico previo a Fusion_Sistema.** Para la procedencia corregida, métricas actuales e integración Laravel/FastAPI validada el 2026-10-01, consultar [FUSION_VALIDATION.md](FUSION_VALIDATION.md) y [FUSION_INTEGRATION.md](FUSION_INTEGRATION.md). Los resultados de este documento se conservan como antecedentes.

> **Nota histórica (2026-09-30):** Este documento describe la línea base de esa fecha. El estado vigente de corpus, FAISS y gates está en `FINAL_STATUS.md`.

# Estado actual de Aporte Ingenieril SAVP

Fecha: 2026-09-29. Rama: `feature/APORTE`.

El núcleo Python está implementado y verificable: análisis V2 sin puntaje compuesto, RIASEC
Mini-IP, Learning Analytics descriptivo, evidencia académica/técnica/declarada, catálogo de cinco
ofertas, bridge, crosswalk, robustez, 12 fuentes oficiales, corpus de 773 chunks, dos índices
FAISS, retrieval híbrido, tutor estructurado, defensas de prompt injection, API, E2E y demo.

## Estado por capa

- RIASEC: 30 reactivos, escala interna 0–4, cinco reactivos por dimensión, suma 0–20.
- Recomendación V2: perfiles de evidencia; no ranking global ni probabilidad de éxito.
- Fuentes: 12 snapshots con hash válido y 11 referencias externas/internas registradas.
- Bridge: 15 inferencias documentales y 2 hipótesis; ninguna relación directa.
- Crosswalk: 15 inferencias carrera–ocupación con O*NET 31.0 e ISCO-08.
- Retrieval: E5 seleccionado; híbrido Recall@5 0.6667, MRR 0.5159, nDCG@5 0.5161.
- Prompts: 13/13 escenarios del benchmark aprobados; tres atacan evidencia recuperada.
- LLM local: integración opcional implementada, runtime no disponible; no evaluado.
- Integración Laravel/PETER 2: fuera de alcance y no realizada.

La suite, cobertura, benchmark integral y estado Git se actualizan en `FINAL_STATUS.md`. Las
limitaciones científicas permanecen: no hay validación psicométrica boliviana, estudio
longitudinal, calibración predictiva, revisión experta final del bridge ni catálogo exhaustivo.
