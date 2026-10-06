# Registro de decisiones

## D-000 — La evidencia ejecutada prevalece sobre el porcentaje narrativo

- Fecha: 2026-09-28.
- Decisión: ningún endpoint, pipeline o evaluación se considera terminado por tener
  contrato, fixture o stub. El estado se controla mediante
  `REQUIREMENTS_MATRIX.md` y solo cambia a verificado después de ejecutar sus pruebas
  y registrar evidencia reproducible.
- Motivo: la ampliación de alcance convierte en obligatorios varios componentes que el
  núcleo inicial documentaba como diferidos.

## D-001 — Aislamiento mediante worktree

- Tipo: **DECISIÓN DE DISEÑO**.
- Estado: aceptada.
- Decisión: usar `C:\laragon\www\savp-tis3-aporte` para `feature/APORTE`.
- Motivo: el worktree original tenía cambios ajenos que Git no podía conservar al cambiar a la base requerida.
- Consecuencia: no se toca ni oculta el trabajo original; Aporte Ingenieril SAVP queda basado exactamente en `integration/savp-consolidado`.

## D-002 — Núcleo sin servicios comerciales ni ML supervisado

- Tipo: **DECISIÓN DE DISEÑO**.
- Estado: aceptada.
- Decisión: FastAPI y cálculo determinista con biblioteca estándar; OpenAI, XGBoost y LLM quedan fuera.
- Motivo: no existe variable objetivo defendible y el MVP debe operar offline.

## D-003 — O*NET® Mini‑IP 2.0 español

- Tipo: **DECISIÓN DE DISEÑO**, respaldada por **HECHOS DOCUMENTADOS**.
- Estado: aceptada para exploración; **PENDIENTE DE VALIDACIÓN** en población boliviana.
- Decisión: reproducir literalmente los 30 reactivos oficiales en español, respuesta 0–4 y suma de cinco reactivos por RIASEC.
- Licencia: CC BY‑ND 4.0 con atribución, sin modificaciones.
- Motivo: versión oficial vigente, breve, disponible en español, con scoring y evidencia psicométrica documentados.

## D-004 — Estado del análisis

- Tipo: **CONFIGURACIÓN EXPERIMENTAL**.
- Estado: aceptada para contrato v1 preliminar.
- Regla: `COMPLETE` requiere evidencia académica y RIASEC completa; `PARTIAL` requiere al menos uno; `INSUFFICIENT` cuando ambos faltan.
- Nota: asistencia y formación técnica aumentan cobertura, pero no sustituyen los dos componentes centrales.

## D-005 — Tendencia descriptiva

- Tipo: **DECISIÓN DE DISEÑO**.
- Estado: aceptada.
- Decisión: calcular pendiente de regresión lineal solo con al menos dos períodos ordenados; devolver `null` en otro caso.
- Interpretación: describe cambio observado, no causalidad ni capacidad futura.

## D-006 — Endpoints sin corpus

- Tipo: **DECISIÓN DE DISEÑO**.
- Estado: aceptada para el primer hito.
- Decisión inicial, ya superada: `/knowledge/search` devolvía 503 mientras no existía índice. En el
  estado actual usa el índice seleccionado; conserva 503 solo para indisponibilidad de carga.
  `/tutor/query` responde de forma estructurada y se abstiene sin evidencia suficiente.
