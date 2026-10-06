# Limitaciones reales — Aporte Ingenieril SAVP

Revalidación técnica del 2026-10-01: Torch 2.14.0 y el módulo nativo de
scikit-learn 1.9.1 fueron rechazados por Windows Code Integrity en VicDev.
La combinación oficial CPU fijada en `pyproject.toml`/`uv.lock`
(Torch 2.13.0+cpu, torchvision 0.28.0+cpu, scikit-learn 1.8.0)
sí cargó y generó embeddings reales. Esta compatibilidad se verificó
en Python 3.12.10 x64; otros hosts deben reproducir `uv sync --locked`
y los gates. No se cambió la arquitectura ni se debilitó la política
de Windows.

- RIASEC mide intereses, no aptitud, inteligencia ni probabilidad de éxito.
  No tiene validación psicométrica específica para población boliviana.
- Bridge requiere revisión experta. Crosswalk relaciona clasificaciones,
  pero no establece equivalencia ni requisitos de admisión.
- El catálogo incluye cinco ofertas de dos universidades y no es exhaustivo.
- Retrieval puede omitir evidencia: TEST híbrido E5 obtuvo Recall@5
  0.8929 en las consultas positivas etiquetadas; el benchmark es pequeño.
- Los 13 escenarios de prompts, incluida inyección, no prueban seguridad
  universal. La existencia de una cita se comprobó, pero su soporte
  semántico para cada afirmación sigue `NOT_EVALUATED`.
- El LLM local es opcional y no se evaluó como dependencia operativa.
  El tutor estructurado funciona sin él.
- No existe predictor validado de éxito. XGBoost no se implementó por
  falta de dataset longitudinal, objetivo y evaluación. La fase 4
  histórica permanece `NOT_IMPLEMENTED_AS_WRITTEN` donde corresponde.
- Los parámetros de retrieval se seleccionaron con DEV y se congelaron
  antes de TEST. No están calibrados con resultados estudiantiles.
- La carga fría de E5 fue lenta en VicDev. Las mediciones locales de
  proceso caliente y el smoke en proceso no constituyen SLA ni prueba
  de capacidad de red o producción.
- La integración real Laravel/PETER 2, base de datos y UI corresponde
  a una etapa posterior; este cierre valida el contrato FastAPI.
