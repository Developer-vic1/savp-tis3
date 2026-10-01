# Estado final de PETER 3, fase 2

Actualización fase 2.1: el diagnóstico individual confirmó que las dos fallas son
`EXPECTED_STALE_INDEX`. `verify_sources.py --upstream-only` pasa, pero la verificación integral
sigue fallando por ambos índices. Code Integrity de Windows registró eventos 3033/3077 para
`torch._C` y `librt.internal`, ambos rechazados por la política de firma empresarial. Se preparó
un checkpoint WIP y un procedimiento DEV → freeze → TEST en `PHASE21_WIP_CHECKPOINT.md` para
continuar en otro entorno. Todavía no se han reconstruido índices ni ejecutado retrieval nuevo.

`PETER3_NOT_READY` — verificación del 2026-09-30 en la rama `work/peter3-mejoras-fase2`, basada en `ae386c7`.

Los 12 snapshots locales y el corpus de 773 chunks pasan la verificación de procedencia. Cuatro snapshots HTML UCB se renovaron desde páginas oficiales y el registro quedó en `2.1.0`. Se corrigió la versión de 265 chunks ministeriales cuyos hashes de origen ya coincidían. Los 773 IDs y textos conservan su orden, pero 26 títulos incluidos en la entrada de embeddings cambiaron. Los índices FAISS E5 y MiniLM siguen asociados al corpus anterior y ambos son `STALE`. Requieren reconstrucción de vectores y nueva evaluación DEV/TEST; cambiar solo sus manifiestos sería incorrecto.

La suite completa arroja **125 passed, 2 failed, 1 skipped**, con **92 %** de cobertura (2995 sentencias, 253 sin cubrir). Fallan la prueba de integridad del hash del índice y el E2E de búsqueda real, que ahora devuelve 503 ante un índice desactualizado. Ruff y `git diff --check` pasan. Mypy 1.20.2 no inicia porque Control de aplicaciones de Windows bloquea `librt.internal`. FAISS sí importa; Torch instalado no carga su DLL nativa en este equipo, por lo que no se pueden reconstruir los índices aquí. WSL y el daemon Linux de Docker no están disponibles.

El smoke HTTP real devuelve 200 en health, cuestionario RIASEC, score RIASEC y análisis V2; 8 de 8 peticiones RIASEC concurrentes devuelven 200. Búsqueda y tutor devuelven 503 por el índice. Los 13 escenarios de prompts estructurados pasan; soporte semántico de citas permanece `NOT_EVALUATED`. Recommendation V2 pasa invariantes sobre fixture sintético. Se midió latencia local del análisis V2, pero no rendimiento actual de retrieval ni E2E con el corpus renovado.

No se crea el commit final ni se hace push mientras fallen los gates críticos. La integración PHP real, validación psicométrica boliviana, revisión experta de inferencias, LLM local y estudio longitudinal siguen pendientes. Los resultados de fase 1 son históricos y no validan la fase 2.
