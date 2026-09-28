# Plan de evaluación

## Software

- Contrato: schemas, códigos HTTP, JSON estable y ausencia de tracebacks.
- RIASEC: rango, completitud, duplicados, extremos, empates, orden y versión.
- Perfil: completo, parcial, insuficiente, `null` y faltantes.
- Analítica: límites 0/100, tendencias, dispersión y asistencia ausente.
- Determinismo: mismo input → mismo hash y mismos cálculos (excepto fecha/trace).

## Método futuro

- Retrieval: dataset manual, Recall@K, MRR@K, latencia y RAM.
- Pesos experimentales: perturbación ±5% y ±10%, Jaccard Top‑K, Spearman y Kendall.
- LLM local: groundedness, adherencia a citas, español, latencia y recursos.

Ninguna prueba de software se presentará como validación científica o precisión predictiva.
