# Estado del corpus — Aporte Ingenieril SAVP

Verificación del 2026-10-01: `verify_sources.py --upstream-only` y la
verificación completa pasaron. Hay **12 snapshots locales**, **11 referencias**
y **773 chunks**. Los 773 `chunk_id` son únicos, sus `source_id` resuelven,
los textos no están vacíos y los hashes SHA-256 de las 12 fuentes coinciden
con los archivos conservados.

SHA-256 definitivo de `data/processed/corpus.jsonl`:

`ebd98f3fb35058af6ff074673cccc56053d9f2ee064ee31a86ed8d25c3e4c5ce`

El corpus usa la versión `bo-official-corpus-1.0.0`. Incluye 35 chunks
obtenidos mediante OCR conservado; los demás proceden de extracción digital o
HTML estructurado. El chunking apunta a 1200 caracteres y 180 de solapamiento
(caracteres, no tokens). Cada chunk conserva ID, fuente, institución, título,
URL, hash, versión, página o sección, método de extracción y texto.

## Índices actuales

`scripts/rebuild_indexes_phase21.py` generó embeddings reales para E5 y
MiniLM desde este corpus. Ambos índices FAISS `IndexFlatIP` tienen 773
vectores de dimensión 384, 773 filas de metadata y el mismo orden de
`chunk_id` que `corpus.jsonl`. Sus manifests registran el SHA actual;
`test_index_integrity.py` pasó 3/3 y `verify_sources.py` confirmó ambos.
Los vectores están normalizados en L2; el producto interno equivale a
similitud coseno.

El SHA anterior `bda50b487fd2b7d974a9201ddfdd6f87a48aefe8b0dad38b508e5e112e369d3d`
identifica solo los índices históricos reemplazados. No debe usarse para
seleccionar un índice del corpus actual. La selección vigente de E5 y su
evaluación DEV/TEST están documentadas en `RETRIEVAL_EVALUATION.md`.

`scripts/build_corpus.py --merge-existing` permite incorporar una fuente
seleccionada sin repetir OCR sobre todos los PDF. Cualquier cambio posterior
al corpus requiere regenerar los embeddings e índices, y revalidar retrieval.
