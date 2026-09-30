# Contexto maestro PETER 3

PETER 3 es el núcleo analítico y documental Python de SAVP-TIS3. Su diseño actual es un sistema
basado en evidencia, no un predictor de éxito. DSRM estructura investigación/diseño/evaluación
del artefacto; ICONIX pertenece al desarrollo global del software.

El flujo V2 valida el perfil, calcula RIASEC y analítica descriptiva, conserva evidencia BTH e
intereses declarados y produce cinco perfiles de carrera separados. No agrega constructos en un
score global, no decide una carrera y no infiere aptitud, inteligencia o probabilidad.

La capa documental dispone de snapshots con hash, OCR donde fue necesario, corpus, embeddings,
FAISS, BM25/RRF y tutor estructurado. Funciona sin OpenAI, GPU, XGBoost o LLM obligatorio. El LLM
local es una verbalización opcional deshabilitada por defecto y no evaluada en este entorno.

La frontera autorizada es `ai-service/**` y `docs/peter3/**`. PETER 2/Laravel, PostgreSQL y UI no
forman parte de esta entrega. Los contratos para una integración posterior se describen en
`INTEGRATION_READINESS.md`.
