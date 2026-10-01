# Recuperación semántica — PETER 3

Fase 2.1 revalidada el 2026-10-01. Los modelos vigentes son
`intfloat/multilingual-e5-small` y
`sentence-transformers/paraphrase-multilingual-MiniLM-L12-v2`. Ambos
codificaron los 773 chunks reales del corpus actual en dimensión 384.
E5 aplica `query: ` a consultas y `passage: ` a documentos; MiniLM no usa
esos prefijos. No se cambió ninguno de los modelos para resolver el entorno.

Los índices usan FAISS `IndexFlatIP` con vectores normalizados en L2;
la comparación exacta por producto interno equivale a similitud coseno.
El modo híbrido combina BM25 y resultados semánticos mediante RRF
(`k=60`, pesos 1.0/1.0). Los filtros de fuente oficial, institución y
tipo se aplican al devolver resultados. Cada hit conserva `chunk_id`,
score y metadatos para trazabilidad.

El bloqueo de Windows Code Integrity afectaba a Torch 2.14.0 y al módulo
nativo de scikit-learn 1.9.1. Se verificaron wheels oficiales de Torch
2.13.0+cpu, torchvision 0.28.0+cpu y scikit-learn 1.8.0 en Python 3.12.10
x64. Los imports, `torch.rand`, la codificación real de dos textos con
cada modelo y una búsqueda FAISS temporal pasaron antes de cambiar
`pyproject.toml` y `uv.lock`. El lock usa el índice CPU oficial de
PyTorch solo para Torch/torchvision; el runtime final volvió a pasar esos
imports y la suite del proyecto.

`scripts/rebuild_indexes_phase21.py` escribió vectores y manifests desde
el código; no se copiaron vectores previos ni se editaron hashes a mano.
Los dos manifests tienen SHA del corpus
`ebd98f3fb35058af6ff074673cccc56053d9f2ee064ee31a86ed8d25c3e4c5ce`.
`verify_sources.py` y `test_index_integrity.py` pasaron. DEV comparó
ambos modelos y los dos modos; E5 híbrido obtuvo mejores métricas de
relevancia y fue congelado antes de ejecutar TEST una sola vez.
Cifras y latencias: `RETRIEVAL_EVALUATION.md`.

Los manifests registran Python 3.12.10, Torch 2.13.0+cpu,
sentence-transformers 6.1.0, transformers 5.17.0 y faiss-cpu 1.15.1.
La revisión exacta del modelo no estuvo disponible desde este runtime;
se registra `model_revision: null` y `UNAVAILABLE_FROM_RUNTIME`.

Una consulta sin evidencia suficiente debe producir una advertencia o
abstención en el tutor; no se debe inventar una cita. Los escenarios
`NO_ANSWER`, `AMBIGUOUS` y `ADVERSARIAL` se evaluaron en el
alcance de los tests y prompts existentes. El soporte semántico de cada
cita frente a cada afirmación permanece `NOT_EVALUATED`.
