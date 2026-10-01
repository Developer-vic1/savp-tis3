# Evaluación de robustez

## Objeto evaluado

V2 no agrega dimensiones, por lo que no tiene pesos que “optimizar”. La exploración de pesos
se aplica únicamente al baseline `v1_experimental` para observar cuánto cambia su ranking.
Los resultados son `ROBUSTNESS_OVER_WEIGHT_SPACE`, nunca probabilidad.

## Métricas implementadas

- estabilidad Top-1: frecuencia del primer resultado más frecuente;
- frecuencia Top-k por alternativa;
- Jaccard Top-k entre conjuntos;
- Spearman rho entre órdenes completos;
- Kendall tau entre pares;
- tasa de reversión: `(1 - tau) / 2` respecto del ranking de referencia;
- estabilidad del frente de dominancia.

Las fórmulas están en `app/recommendation/robustness.py` y tienen casos manuales para identidad
y reversión total.

## Dominancia/Pareto

`dominates(A, B)` exige que ambas alternativas tengan todas las dimensiones declaradas; A debe
ser igual o mejor en todas y mejor en al menos una. Si faltan datos, no declara dominancia. Los
trade-offs permanecen como alternativas no dominadas.

V2 no ejecuta Pareto sobre estados heterogéneos porque interés, evidencia técnica, interés
declarado y preparación no son un vector numérico comparable. Su response declara
`NOT_APPLIED_NONCOMPARABLE_CONSTRUCTS`.

## Ejecución reproducible

```powershell
.venv312\Scripts\python.exe scripts/evaluate_robustness.py
```

Sobre `complete_profile.json`, marcado sintético, se exploraron 11 combinaciones
afinidad/preparación desde 0/1 hasta 1/0. En la ejecución local del 2026-09-29:

- estabilidad Top-1: 0.8182;
- Jaccard Top-3 medio: 1.0;
- Spearman medio: 0.8;
- Kendall medio: 0.6970;
- tasa de reversión: 0.1515;
- estabilidad del frente de dominancia: 1.0.

Estos valores describen solo ese fixture y espacio discreto de parámetros. “Top-3 en X% de las
configuraciones” no significa “X% de probabilidad de carrera correcta”.
