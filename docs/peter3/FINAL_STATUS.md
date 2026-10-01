> **Estado histórico previo a Fusion_Sistema.** Para la procedencia corregida, métricas actuales e integración Laravel/FastAPI validada el 2026-10-01, consultar [FUSION_VALIDATION.md](FUSION_VALIDATION.md) y [FUSION_INTEGRATION.md](FUSION_INTEGRATION.md). Los resultados de este documento se conservan como antecedentes.

# Estado final de PETER 3 — Fase 2.1

Verificación local del 2026-10-01. Rama
`work/peter3-mejoras-fase2`; checkpoint de partida
`388890c2e004116e16374582da810406f4125508`.
La base histórica protegida es
`ae386c741c850a89adc586131c9f974ac458e628`.
`b02a392` es solo una referencia histórica previa.

## Gates ejecutados

| Gate | Resultado |
|---|---|
| Fuentes y corpus | PASS: 12 snapshots, 11 referencias, 773 chunks únicos y no vacíos |
| SHA-256 del corpus | `ebd98f3fb35058af6ff074673cccc56053d9f2ee064ee31a86ed8d25c3e4c5ce` |
| FAISS E5 y MiniLM | PASS: 773 vectores reales, dimensión 384 y SHA actual por índice |
| Retrieval | DEV ejecutado; E5 congelado sin ajuste; TEST ejecutado; verificador PASS |
| RIASEC y Recommendation V2 | PASS |
| Knowledge y Tutor | HTTP 200 en `TestClient`, con trace IDs |
| Prompts | 13/13 PASS; 0 éxitos de inyección en los escenarios evaluados |
| Analysis E2E / Full E2E | PASS / PASS |
| Suite y cobertura | 127 passed, 1 skipped (OCR real opcional), 0 failed; 92 % |
| Ruff | PASS |
| Mypy | PASS: 0 errores en 82 archivos de `app scripts` y 111 del repositorio |
| Seguridad | PASS: producción sin key falla cerrada; key errónea, JSON y payload inválidos rechazados sin traceback |
| Smoke y concurrencia | PASS: seis endpoints HTTP 200 y 8/8 solicitudes RIASEC concurrentes |
| `verify_peter3.py` | PASS: todos los gates completados en la repetición global |
| `git diff --check` | PASS |

Torch 2.14.0 y scikit-learn 1.9.1 fueron bloqueados por Windows Code
Integrity. Con wheels oficiales CPU, Torch 2.13.0+cpu, torchvision
0.28.0+cpu y scikit-learn 1.8.0 cargaron, codificaron textos reales con
E5/MiniLM y reprodujeron los índices desde `uv.lock`. No se cambió
la arquitectura ni se usaron vectores antiguos.

TEST híbrido E5 obtuvo Recall@1 0.3810, Recall@3 0.8690, Recall@5
0.8929, MRR 0.6548 y nDCG@5 0.7043. Todas las cifras y latencias DEV,
TEST y de proceso caliente están en `RETRIEVAL_EVALUATION.md` y
`PERFORMANCE.md`. La carga fría de E5 en VicDev fue lenta; no se
presentan estas mediciones como SLA.

El soporte semántico de citas sigue `NOT_EVALUATED`. RIASEC mide
intereses, no aptitud; no hay validación psicométrica boliviana ni
predictor validado de éxito. Bridge requiere revisión experta, Crosswalk
no establece equivalencias y el catálogo no es exhaustivo.
La integración real Laravel/PETER 2 queda para su fase posterior.

Estado técnico de integración del servicio: `PETER3_INTEGRATION_READY`.
El cliente Laravel/PETER 2 deberá validarse en su etapa posterior.
