# Plan de evaluación de Aporte Ingenieril SAVP V2

## Alcance

La evaluación demuestra corrección de software, trazabilidad, determinismo y cumplimiento de
invariantes. No demuestra validez predictiva, causalidad ni que una alternativa sea correcta
para una persona.

## Pirámide

- Unitarias: fórmulas, RIASEC, matching, estados, dominancia y métricas de robustez.
- Integración: validación, analytics, bridges/crosswalk y construcción del response.
- E2E HTTP: fixture → `/api/v2/analysis` → schema y trazabilidad.

## Escenarios sintéticos

`tests/fixtures/v2_scenarios.json` cataloga `complete_profile`, `insufficient_profile`,
`partial_without_riasec`, el baseline de alta afinidad/baja preparación y los escenarios V2
sin academia, RIASEC, BTH o interés declarado; un período; escalas mixtas; empate y perfil
RIASEC plano; áreas faltantes; múltiples ofertas; y bridge insuficiente.

Todos se clasifican `SYNTHETIC_TEST_FIXTURE`. No son validación científica.

## Invariantes automatizados

1. cambiar notas no cambia RIASEC;
2. cambiar RIASEC no cambia evidencia académica;
3. missing no se convierte en zero;
4. preparación no contiene calidad de evidencia;
5. metadatos temporales no alteran contenido por sí solos;
6. misma entrada y metadatos de ejecución fijos producen el mismo output;
7. reordenar registros equivalentes no cambia el resultado sustantivo;
8. escalas equivalentes normalizan al mismo valor;
9. una carrera sin evidencia no recibe conclusión fuerte;
10. el LLM no participa;
11. no existe `probability_of_success`;
12. no existe inteligencia inferida.

## Cálculos conocidos

Las pruebas manuales cubren normalización 15/20 = 75/100, media, mínimo, máximo, desviación
estándar poblacional, pendiente lineal, cobertura temporal, asistencia, entregas, atrasos,
puntualidad y tareas. RIASEC cubre 30 IDs, rango, completitud, extremos, orden y empates.

## Comparación V1/V2

`scripts/evaluate_recommendation_v2.py` informa lo que produce cada versión. Se comparan
trazabilidad, ausencia de parámetros ocultos e invariantes. No se afirma que V2 “predice mejor”.

## Criterios de aceptación

```powershell
.venv312\Scripts\python.exe -m pytest tests/unit/test_recommendation_v2.py -v
.venv312\Scripts\python.exe -m pytest tests/unit/test_analysis_v2_invariants.py -v
.venv312\Scripts\python.exe -m pytest tests/unit/test_learning_analytics.py tests/unit/test_riasec.py -v
.venv312\Scripts\python.exe -m pytest tests/integration/test_analysis_v2_e2e.py -v
.venv312\Scripts\python.exe -m ruff check .
.venv312\Scripts\python.exe -m mypy .
```

La suite completa se ejecuta al cierre. Los accesos de pruebas a PyMuPDF usan un adaptador con
`Protocol`; `mypy .` es un gate estricto y debe aprobar sin una exclusión global.
