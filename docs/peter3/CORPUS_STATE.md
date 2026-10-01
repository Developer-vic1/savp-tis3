> **Estado de fase 2 (2026-09-30):** Las cifras y los PASS fechados el 2026-09-29 describen la l?nea base hist?rica. Se reconstruy? el corpus con cuatro snapshots HTML UCB nuevos y se corrigi? metadata de versi?n respaldada por hash; ambos ?ndices FAISS y sus m?tricas siguen `STALE` hasta reconstrucci?n y revalidaci?n. V?ase `FINAL_STATUS.md`.

# Estado del corpus — PETER 3

Fase 2.1: `verify_sources.py --upstream-only` pasa para 773 chunks con IDs únicos, textos no
vacíos y SHA-256 `ebd98f3fb35058af6ff074673cccc56053d9f2ee064ee31a86ed8d25c3e4c5ce`.
Los dos índices conservan el hash anterior y siguen `STALE`; véase `PHASE21_WIP_CHECKPOINT.md`.

Estado reconstruido y verificado: 2026-09-29.

- Versión: `bo-official-corpus-1.0.0`.
- Fuentes: 12.
- Chunks únicos: 773.
- Extracción OCR conservada: 35 chunks; las demás páginas útiles usan extracción digital o
  HTML estructurado.
- Chunking real: objetivo de 1.200 caracteres y solapamiento de 180 caracteres. Son caracteres,
  no tokens.
- Metadatos obligatorios: `chunk_id`, `source_id`, institución, título, URL, hash, versión,
  página/sección, método de extracción y texto.

## Índices

Los dos índices FAISS `IndexFlatIP` contienen 773 vectores de dimensión 384 y exactamente el
mismo orden de `chunk_id` que el corpus. Los manifiestos registran el SHA-256 del corpus:

- `intfloat/multilingual-e5-small` (seleccionado);
- `sentence-transformers/paraphrase-multilingual-MiniLM-L12-v2` (comparador).

Los vectores se normalizan en L2, por lo que el producto interno representa similitud coseno.
BM25 se construye en memoria y ambos rankings se fusionan con RRF `k=60`.

`scripts/build_corpus.py --merge-existing` permite incorporar una fuente seleccionada sin volver
a ejecutar OCR sobre todos los PDFs. Una reconstrucción completa sigue siendo la prueba de
reproducibilidad de referencia y puede ser costosa en CPU.
