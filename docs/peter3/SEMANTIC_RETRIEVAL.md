# Recuperación semántica

> Estado auditado el 2026-09-30: hay dos índices FAISS de 689 vectores, pero sus manifiestos referencian un hash de corpus anterior. Las métricas guardadas son históricas y no se pudieron repetir en el entorno actual. Véase `PETER3_FINAL_AUDIT.md`.

## Problema

La búsqueda por coincidencia literal falla ante paráfrasis como “materias al iniciar Sistemas”
frente a “primer ciclo / Introducción a la Programación”. Se necesita comparar dos modelos en
el corpus boliviano real antes de seleccionar uno.

## Evidencia y diseño

Los candidatos son `sentence-transformers/paraphrase-multilingual-MiniLM-L12-v2` e
`intfloat/multilingual-e5-small`. Ambos producen vectores de 384 dimensiones y tienen soporte
multilingüe; E5 usa prefijos `query:` y `passage:` según su model card. La selección depende
del dataset local, no de popularidad externa.

FAISS usa `IndexFlatIP` con vectores L2-normalizados: producto interno equivale a similitud
coseno y la búsqueda es exacta para este corpus pequeño. Índices aproximados no aportan una
ventaja defendible con 689 chunks en el corpus actual (auditoría 2026-09-30). Las métricas históricas requieren reevaluación antes de usarse como resultado vigente.

## Componentes

- `data/evaluation/retrieval_queries.json`: consultas y fuentes relevantes versionadas.
- `app/retrieval/embeddings.py`: adaptadores y prefijos por modelo.
- `app/retrieval/index.py`: construcción, persistencia, carga, filtros y búsqueda FAISS.
- `app/retrieval/metrics.py`: Recall@k, MRR y nDCG.
- `scripts/evaluate_retrieval.py`: experimento A/B reproducible y selección.

## Contrato

Cada resultado conserva `chunk_id`, texto, score coseno y todos los metadatos de fuente. La
consulta se normaliza pero no se traduce. Los filtros oficial/institución/tipo se aplican antes
de devolver `top_k`. Una consulta sin evidencia suficiente debe producir resultados vacíos o
una advertencia, nunca una cita inventada.

## Pruebas

- identidad y dimensión del índice;
- persistencia y correspondencia ordinal chunk–vector;
- ranking exacto con embeddings sintéticos;
- métricas con casos conocidos y sin relevantes;
- evaluación A/B real sobre el dataset completo;
- endpoint con consulta, filtros y fuentes trazables.

Los resultados y el criterio de selección históricos están en `data/evaluation/retrieval_results.json` y `data/indexes/selected.json`; deben reejecutarse con los datos íntegros y particiones DEV/TEST antes de declararlos actuales.
