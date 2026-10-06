> **Nota histórica (2026-09-30):** Este documento describe la línea base de esa fecha. El estado vigente de corpus, FAISS y gates está en `FINAL_STATUS.md`.

# Matriz de requisitos Aporte Ingenieril SAVP

Estado auditado: 2026-09-29.

| Bloque | Estado | Evidencia |
|---|---|---|
| Aislamiento y frontera Python/docs | VERIFICADO | diff final limitado a `ai-service/**` y `docs/peter3/**` |
| Perfil, RIASEC y Learning Analytics | VERIFICADO | contratos, unidades e integración |
| Catálogo, bridge y crosswalk | VERIFICADO CON LIMITACIONES | 5 ofertas, 17 relaciones, 15 ocupaciones; inferencias explícitas |
| Recommendation V2 | VERIFICADO | perfiles sin score/ranking global y con nulos honestos |
| Robustez | VERIFICADO | invariantes, dominancia y perturbaciones versionadas |
| Fuente/hash/referencias | VERIFICADO | 12 snapshots + 11 referencias, integridad cruzada |
| Ingesta/OCR/chunking | VERIFICADO | 773 chunks; 35 OCR; metadatos y hashes |
| Índices y retrieval | VERIFICADO | 2 FAISS exactos; benchmark A/B de 30 consultas |
| Prompt engineering y seguridad | VERIFICADO EN BENCHMARK | 13 escenarios; inyección en pregunta y evidencia |
| Tutor estructurado | VERIFICADO | retrieval, abstención, citas y fallback |
| LLM local | NO EVALUADO | runtime no disponible; no se inventan métricas |
| API V2 y búsqueda/tutor V1 | VERIFICADO | contratos HTTP y errores estables |
| Full E2E y demo | VERIFICADO | índice/corpus reales, sin stubs |
| Rendimiento local | VERIFICADO CON ALCANCE | medianas/p95 por componente, proceso caliente |
| Integración Laravel/PETER 2 | PENDIENTE EXTERNO | deliberadamente fuera de este aporte |
| Validación científica boliviana | PENDIENTE CIENTÍFICO | requiere muestra, expertos y seguimiento longitudinal |

`VERIFICADO` significa que el comportamiento de software es reproducible; no convierte una
inferencia documental en verdad causal ni una medición local en SLA de producción.
