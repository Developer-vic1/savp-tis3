# E2E integral de PETER 3

`tests/integration/test_peter3_full_e2e.py` recorre sin stubs:

```text
fixture sintético completo
  -> POST /api/v2/analysis
  -> RIASEC + academia + BTH + calidad de evidencia
  -> cinco perfiles de carrera + fuentes + versiones
  -> POST /api/v1/knowledge/search sobre índice seleccionado real
  -> POST /api/v1/tutor/query con retrieval real
  -> respuesta estructurada + citas + trace_id
```

La prueba aprobó el 2026-09-29. `scripts/run_peter3_full_demo.py` expone el mismo recorrido en 14
bloques JSON: fixture, RIASEC, academia, BTH, calidad, perfiles, refuerzo, limitaciones, fuentes,
consulta, tutor, citas, trace y versiones. El fixture está marcado como sintético.

```powershell
.venv312\Scripts\python.exe -m pytest tests\integration\test_peter3_full_e2e.py -q
.venv312\Scripts\python.exe scripts\run_peter3_full_demo.py
```

Un E2E aprobado demuestra integración y trazabilidad del software; no demuestra validez
predictiva, causalidad del bridge ni exhaustividad del catálogo.
