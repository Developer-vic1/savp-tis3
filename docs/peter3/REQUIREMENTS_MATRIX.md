# Matriz de requisitos PETER 3

Fecha: 2026-09-30. Estados basados en el working tree; la ejecución funcional sigue sin verificar por falta de dependencias. Detalle: `PETER3_FINAL_AUDIT.md`.

| Bloque | Evidencia actual | Estado |
|---|---|---|
| Rama y frontera de archivos | `feature/APORTE`; snapshot inicial limpio | PASS inicial |
| V1 perfil, RIASEC y Learning Analytics | Código y tests presentes; ejecución pendiente | PARTIAL |
| V1 recomendación | Ranking y umbrales experimentales presentes | LEGACY_EXPERIMENTAL_BASELINE |
| V2 basada en evidencia | Sin ruta, contratos, motor ni pruebas | NOT_IMPLEMENTED |
| Fuentes y catálogo | 11 fuentes, 5 ofertas; 4 hashes HTML fallan | FAIL |
| Puente | 12 relaciones V1 con pesos y revisión experta pendiente; V2 ausente | PARTIAL |
| Crosswalk ocupacional | Archivo y lógica ausentes | NOT_IMPLEMENTED |
| Gobernanza de parámetros | `data/parameters/registry.json` ausente | NOT_IMPLEMENTED |
| Corpus/OCR | 689 fragmentos, 35 OCR; procedencia de HTML pendiente | PARTIAL |
| FAISS | Dos índices de 689 vectores; hash de corpus desactualizado | FAIL |
| Retrieval | 20 consultas maestras y resultados históricos; sin DEV/TEST | PARTIAL |
| Prompts/inyección | Sin `app/prompts/**` ni evaluación; guardas parciales en tutor | PARTIAL |
| Tutor | Ruta y proveedor estructurado presentes; runtime no ejecutado | UNVERIFIED |
| LLM local | Adaptador opcional; runtime y calidad no evaluados | NOT_EVALUATED |
| E2E completo | Test de pipeline V2/crosswalk/citas ausente | NOT_IMPLEMENTED |
| Rendimiento | Solo mediciones históricas de análisis V1 | NOT_EVALUATED_CURRENT |
| Documentación | Hallazgos actuales en auditoría; informe académico original ausente | PARTIAL |
| Git pre-commit | No hay commit; decisión pendiente de correcciones | NOT_SAFE_TO_COMMIT |

Los estados `PASS` históricos no se transfieren automáticamente a esta fecha.
