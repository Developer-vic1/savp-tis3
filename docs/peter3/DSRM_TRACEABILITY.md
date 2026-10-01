# Trazabilidad DSRM de PETER 3

| Fase DSRM | Evidencia o límite |
|---|---|
| 1. Identificación y motivación | `REQUIREMENTS_MATRIX.md`, separación entre interés, preparación y calidad de evidencia. |
| 2. Objetivos | `ARCHITECTURE.md`, `DATA_CONTRACT.md`, `AFFINITY_VS_PREPARATION.md`. |
| 3. Diseño y desarrollo | FastAPI, instrumentos, learning analytics, bridge, Recommendation V2, corpus, retrieval y tutor. |
| 4. Demostración | Fixtures, scripts CLI técnicos y `test_peter3_full_e2e.py`. No existe una UI demo para estudiantes. |
| 5. Evaluación | Pytest, gates de integridad, evaluaciones de retrieval y prompts, benchmark; sus resultados deben reejecutarse tras cambiar fuentes o corpus. |
| 6. Comunicación | README, contrato de integración, limitaciones y reporte de fase 2. |

La demostración DSRM es una ejecución controlada del artefacto; no acredita una demostración con usuarios ni validación educativa o psicométrica.
