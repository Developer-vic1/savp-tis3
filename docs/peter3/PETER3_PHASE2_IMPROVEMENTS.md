# Aporte Ingenieril SAVP — Fase 2 y cierre técnico de Fase 2.1

Rama `work/peter3-mejoras-fase2`, base histórica
`ae386c741c850a89adc586131c9f974ac458e628` y checkpoint de
transferencia `388890c2e004116e16374582da810406f4125508`.
`feature/APORTE`, Laravel y la rama de respaldo no se modificaron.

## Mejoras de Fase 2

- RIASEC oficial de 30 reactivos, escala pública 1–5, conversión interna
  0–4 y scoring compartido por la API V2 y Analysis V2. El texto y orden
  se cotejaron con el asset oficial español de O*NET; se documentó su
  atribución y límite de uso.
- Analysis V2 separa intereses, rendimiento académico, preparación
  técnica y calidad de evidencia. Recommendation V2 no entrega score
  compuesto, ranking global ni probabilidad de éxito.
- Learning Analytics, Bridge, Crosswalk y gobernanza de parámetros y
  fuentes tienen contratos y verificaciones de integridad.
- La API rechaza configuración de producción sin clave, claves erróneas,
  JSON inválido y payloads fuera de contrato con errores trazables y
  sin traceback expuesto.
- Los 12 snapshots locales y 11 referencias respaldan el corpus de 773
  chunks. Cuatro snapshots HTML UCB se renovaron desde fuentes oficiales
  y se corrigió metadata de 265 chunks ministeriales con hash verificado.
- El tutor estructurado utiliza evidencia y citas, se abstiene sin
  evidencia suficiente y rechaza decisiones de carrera absolutas en los
  escenarios evaluados. El LLM local sigue siendo opcional.
- El contrato para PETER 2 está en `PETER2_INTEGRATION_CONTRACT.md`.
  No se implementó cliente Laravel en esta rama.

## Cierre técnico de Fase 2.1 — 2026-10-01

Windows Code Integrity rechazó Torch 2.14.0 y un módulo nativo de
scikit-learn 1.9.1. En un entorno aislado se probaron wheels oficiales
CPU y codificación real con E5 y MiniLM antes de fijar Torch
2.13.0+cpu, torchvision 0.28.0+cpu y scikit-learn 1.8.0 en
`pyproject.toml`/`uv.lock`. El entorno principal se reprodujo con
`uv sync --locked --group dev`; no se utilizó CUDA, contenedor ni
bypass de seguridad.

El corpus conserva SHA-256
`ebd98f3fb35058af6ff074673cccc56053d9f2ee064ee31a86ed8d25c3e4c5ce`.
E5 y MiniLM se reconstruyeron con 773 embeddings reales de dimensión
384 cada uno. Los tests de integridad pasaron; DEV comparó ambos,
se congeló E5 sin cambiar parámetros y TEST se ejecutó una vez.
`verify_retrieval_phase21.py` pasó. Las métricas completas, incluida
la latencia fría observada, están en `RETRIEVAL_EVALUATION.md`.

Knowledge y Tutor respondieron HTTP 200 en el smoke con `TestClient`;
Analysis E2E y Full Aporte Ingenieril SAVP E2E pasaron. Los 13 escenarios de prompts
pasaron con 0 éxitos de inyección observados. La suite completa terminó
en **127 passed, 1 skipped, 0 failed**, cobertura **92 %**; el skip
corresponde al OCR real opcional (`RUN_REAL_OCR=1`). Ruff y Mypy
pasaron; Mypy informó 0 errores en 82 archivos de `app scripts` y
111 archivos del repositorio.

## Alcance científico

RIASEC mide intereses y carece de validación psicométrica boliviana.
Bridge requiere revisión experta; Crosswalk no establece equivalencia.
El catálogo no es exhaustivo. El soporte semántico de cada cita sigue
`NOT_EVALUATED`. No existe predictor validado de éxito ni se implementó
XGBoost; la fase 4 histórica permanece `NOT_IMPLEMENTED_AS_WRITTEN`
donde corresponde. La integración PHP real corresponde a PETER 2.
