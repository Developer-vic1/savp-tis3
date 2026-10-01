> **Nota histórica (2026-09-30):** Este documento describe la línea base de esa fecha. El estado vigente de corpus, FAISS y gates está en `FINAL_STATUS.md`.

# Base de conocimiento

La base operativa contiene 12 snapshots oficiales gobernados por hash, 773 chunks trazables y
dos índices FAISS exactos. `intfloat/multilingual-e5-small` es el modelo seleccionado por el
benchmark A/B; BM25 y RRF complementan la recuperación semántica.

`POST /api/v1/knowledge/search` devuelve evidencia con fuente, chunk, institución, página o
sección, URL, relevancia y versiones. Si no hay evidencia suficiente lo declara explícitamente;
si el índice no carga, devuelve el error tipado 503. El corpus no contiene datos de estudiantes.

El alcance no es exhaustivo: cubre normativa ministerial y cinco ofertas universitarias. La
vigencia de documentos debe revisarse antes de despliegue y en cada actualización institucional.
