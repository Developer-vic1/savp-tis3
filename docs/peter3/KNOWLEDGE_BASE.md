# Base de conocimiento

Estado auditado el 2026-09-30: el repositorio tiene **11** fuentes registradas, un corpus de **689** fragmentos y dos índices FAISS. La ruta `POST /api/v1/knowledge/search` está implementada en código. No se verificó su ejecución HTTP en el entorno actual.

La ingesta y OCR se describen en `OCR_PIPELINE.md`; gobierno y versiones en `SOURCE_GOVERNANCE.md`; selección de embeddings en `SEMANTIC_RETRIEVAL.md`. El corpus se construye desde snapshots locales, no desde URLs durante cada consulta.

La integridad **no** está aprobada: cuatro HTML UCB no coinciden con sus hashes y los índices conservan el hash de un corpus anterior. Hasta resolverlo, los resultados recuperados no deben presentarse como evidencia completamente trazable. Si el índice no puede cargarse, el código devuelve un error 503 tipado; ese comportamiento no se comprobó con HTTP en esta auditoría.
