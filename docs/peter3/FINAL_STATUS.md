# Estado final de PETER 3, fase 2

## Verificación local del 2026-10-01

El bloqueo de Torch persiste; ambos índices FAISS siguen `STALE`. En un entorno limpio creado
desde `uv.lock`, Mypy 1.20.2 sí completó `mypy app scripts` (82 archivos) y `mypy .`
(111 archivos) sin errores, tras corregir tres errores de tipos reales. La suite completa
terminó con **125 passed, 2 failed, 1 skipped** y **92 %** de cobertura (2997 sentencias,
253 sin cubrir). Los dos fallos siguen clasificados `EXPECTED_STALE_INDEX`. Ruff y
`git diff --check` pasan; `verify_peter3.py` falla por los índices y los gates derivados.
El único skip es OCR real: requiere descarga/carga del modelo y se activa con `RUN_REAL_OCR=1`.
Esta verificación no incluye ejecución en la segunda laptop. El commit `b02a392` ya está
publicado como checkpoint; no existe commit final de integración.

`PETER3_NOT_READY` — verificación del 2026-10-01 en la rama `work/peter3-mejoras-fase2`, basada en `ae386c7`.

Los 12 snapshots locales y el corpus de 773 chunks pasan la verificación de procedencia. Cuatro snapshots HTML UCB se renovaron desde páginas oficiales y el registro quedó en `2.1.0`. Se corrigió la versión de 265 chunks ministeriales cuyos hashes de origen ya coincidían. Los 773 IDs y textos conservan su orden, pero 26 títulos incluidos en la entrada de embeddings cambiaron. Los índices FAISS E5 y MiniLM siguen asociados al corpus anterior y ambos son `STALE`. Requieren reconstrucción de vectores y nueva evaluación DEV/TEST; cambiar solo sus manifiestos sería incorrecto.

El smoke HTTP real devuelve 200 en health, cuestionario RIASEC, score RIASEC y análisis V2; 8 de 8 peticiones RIASEC concurrentes devuelven 200. Búsqueda y tutor devuelven 503 por el índice. Los 13 escenarios de prompts estructurados pasan; soporte semántico de citas permanece `NOT_EVALUATED`. Recommendation V2 pasa invariantes sobre fixture sintético. Se midió latencia local del análisis V2, pero no rendimiento actual de retrieval ni E2E con el corpus renovado.

No se crea un commit final nuevo ni se hace un push nuevo mientras fallen los gates críticos. La integración PHP real, validación psicométrica boliviana, revisión experta de inferencias, LLM local y estudio longitudinal siguen pendientes. Los resultados de fase 1 son históricos y no validan la fase 2.
