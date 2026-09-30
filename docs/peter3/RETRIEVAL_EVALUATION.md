# Evaluación de retrieval — PETER 3

Ejecución: 2026-09-29 sobre 773 chunks, CPU, 30 consultas con relevancia manual a nivel de
chunk. Los conjuntos `retrieval_dev.json` y `retrieval_test.json` tienen 15 consultas cada uno,
son disjuntos y su unión coincide con `retrieval_queries.json`. Las consultas `NO_ANSWER` tienen
lista de relevantes vacía.

| Modelo / método | Recall@1 | Recall@3 | Recall@5 | MRR | nDCG@5 | Media consulta |
|---|---:|---:|---:|---:|---:|---:|
| MiniLM semántico | 0.1500 | 0.2611 | 0.2944 | 0.2847 | 0.2507 | 48.08 ms |
| MiniLM híbrido RRF | 0.1722 | 0.3778 | 0.4667 | 0.3591 | 0.3572 | 84.45 ms |
| E5 semántico | 0.2167 | 0.3222 | 0.5444 | 0.4112 | 0.3992 | 33.98 ms |
| E5 híbrido RRF | 0.2556 | 0.5944 | 0.6667 | 0.5159 | 0.5161 | 44.46 ms |

Desglose del modelo seleccionado:

| Split / método | Recall@1 | Recall@3 | Recall@5 | MRR | nDCG@5 |
|---|---:|---:|---:|---:|---:|
| DEV semántico | 0.1444 | 0.2333 | 0.3778 | 0.3395 | 0.2881 |
| DEV híbrido | 0.1556 | 0.4111 | 0.5222 | 0.4206 | 0.3929 |
| TEST semántico | 0.2889 | 0.4111 | 0.7111 | 0.4829 | 0.5104 |
| TEST híbrido | 0.3556 | 0.7778 | 0.8111 | 0.6111 | 0.6393 |

Los valores provienen de `data/evaluation/retrieval_results.json`; E5 queda seleccionado en
`data/indexes/selected.json`. La selección es empírica para este dataset, no una superioridad
universal ni un SLA. El Recall@5 híbrido de 0.6667 implica que todavía existen consultas donde
el corpus o el ranking no recuperan toda la evidencia etiquetada.

Reproducción:

```powershell
.venv312\Scripts\python.exe scripts\evaluate_retrieval.py
.venv312\Scripts\python.exe -m pytest tests\unit\test_index_integrity.py tests\unit\test_retrieval.py
```
