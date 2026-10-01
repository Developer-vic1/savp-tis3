# Implementación RIASEC

## Problema que resuelve

Transformar respuestas completas a un instrumento legalmente utilizable en un perfil RIASEC reproducible sin inferir intereses desde notas o especialidad técnica.

## Evidencia / fuente

O*NET® Mini‑IP 2.0 español y manual oficial. Véase `RIASEC_RESEARCH.md`.

## Componentes

- `data/instruments/onet_mini_ip_v2_es.json`: reactivos literales, dimensión, escala, fuente y licencia.
- `app/riasec/instrument.py`: carga y validación del artefacto.
- `app/riasec/scoring.py`: suma determinista.
- `app/riasec/interpretation.py`: orden estable, empates y lenguaje exploratorio.

## Contrato

Entrada: 30 pares `{item_id, value}`, una vez cada ID, con entero 0–4. Salida: seis sumas
brutas 0–20, seis valores normalizados 0–100, cobertura de reactivos, orden estable por
score descendente y orden RIASEC para desempate, `top_codes`, empate superior y texto
seguro. No se publica puntaje parcial del instrumento: una respuesta faltante invalida el
cálculo y la cobertura de una ejecución válida es 1.0.

La web y el servicio API oficial de O*NET presentan respuestas **1–5**. El contrato
actual de SAVP V1 recibe **0–4**. Un consumidor que capture la escala oficial debe
convertirla explícitamente con `official_web_value_to_internal`: `1→0`, `2→1`,
`3→2`, `4→3`, `5→4`. La función rechaza valores fuera de 1–5 y booleanos. No hay
consumidor Laravel integrado que aplique todavía esta conversión; enviarla sin
convertir puede dar un rechazo 422 o una puntuación desplazada.

## Tests

Cobertura de instrumento completo, faltantes, duplicados, rango, extremos, perfil plano, empate, orden, versión y reproducibilidad.

## Nota científica

Los tests demuestran conformidad del algoritmo con la regla de suma. No demuestran validez del instrumento en Bolivia.
