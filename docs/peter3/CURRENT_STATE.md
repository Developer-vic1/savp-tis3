# Estado actual de PETER 3

Auditoría del 2026-09-30, rama `feature/APORTE`. Decisión: **NOT_SAFE_TO_COMMIT**. El detalle y los comandos están en `PETER3_FINAL_AUDIT.md`.

## Implementación encontrada

- Servicio FastAPI V1, contratos Pydantic, perfil estudiantil, RIASEC, Learning Analytics y ranking experimental.
- Cuatro rutas V1 y `GET /health`; **no existe** `POST /api/v2/analysis`.
- 11 fuentes registradas; cinco ofertas de carrera en dos universidades; 12 relaciones experimentales del puente V1.
- Corpus de 689 fragmentos y dos índices FAISS de 689 vectores; el índice seleccionado es E5 multilingüe.
- Búsqueda híbrida y tutor estructurado por código; LLM local opcional. No hay consumidor Laravel.

## Bloqueos comprobados

1. Cuatro HTML UCB no coinciden con el SHA-256 del registro. El test existente de hashes fallaría con estos bytes.
2. Ambos manifiestos de índice señalan el hash `216b088a…`, mientras el corpus actual tiene `fe64464f…`. El texto y el orden de los 689 fragmentos coinciden; faltan metadatos OCR en las copias del índice.
3. V2, crosswalk de carrera a ocupación, registro de parámetros, conjuntos DEV/TEST y sistema de prompts versionados no existen.
4. No había entorno Python del proyecto; el Python 3.12 disponible carece de FastAPI, pytest, Ruff, mypy, psutil y FAISS. No se obtuvieron resultados nuevos de tests, cobertura, API, retrieval ni rendimiento.
5. No se encontró el informe académico actual en PDF/DOCX/ODT dentro del repositorio. DSRM, ICONIX y las cuatro fases se contrastan solo con los enunciados provistos para esta auditoría.

La API V1 conserva umbrales, pesos, un índice global de compatibilidad y `consistency_ratio` como componente de preparación. Son **heurísticas experimentales heredadas** y no deben presentarse como V2, aptitud o probabilidad de éxito. Los documentos históricos de 2026-09-28 no son evidencia de ejecución actual.

## Próximo paso

Recuperar la procedencia de los cuatro HTML, reconstruir o conciliar el corpus e índices, implementar o replanificar explícitamente V2 y el resto del alcance, instalar el entorno fijado por `uv.lock` y ejecutar todos los gates. No se realizó commit.
