# Analítica del aprendizaje

## Problema que resuelve

Describir rendimiento observado y posibles áreas de seguimiento sin diagnosticar capacidad ni atribuir causalidad.

En particular, evita dos errores del perfil inicial: comparar notas que podrían venir en
escalas distintas y presentar una pendiente o una regularidad cuando no existe cobertura
temporal suficiente.

## Evidencia y criterio de diseño

- Las métricas son descriptivas y se calculan únicamente con registros observados.
- Cada nota declara su escala; el valor comparable es
  `100 × (score - scale_min) / (scale_max - scale_min)`.
- La tendencia se estima sobre promedios por período para que un período con más materias
  no pese más solo por tener más filas.
- Dispersión, consistencia, regularidad y pendientes permanecen `null` si sus mínimos de
  evidencia no se cumplen.

Estas reglas son **criterios ingenieriles experimentales**, no baremos psicométricos ni
predictores de éxito universitario.

## Componentes

- `app/contracts/requests.py`: notas con escala, área y tareas observables.
- `app/learning_analytics/academic_performance.py`: normalización y agregaciones.
- `app/learning_analytics/activity.py`: cumplimiento, atraso, calificaciones y regularidad.
- `app/contracts/responses.py`: evidencia, cobertura temporal y métricas auditables.

## Cálculos

- Promedio global, por asignatura y por área con observaciones normalizadas presentes.
- Mínimo, máximo, cantidad y dispersión poblacional.
- Consistencia experimental `max(0, 1 - desviación_estándar / 50)` con al menos dos notas.
- Tendencia por pendiente de regresión lineal sobre el promedio de cada `period_order`,
  solo con al menos dos períodos distintos.
- Cobertura temporal: períodos observados, intervalo y proporción de órdenes cubiertos.
- Asistencia relativa únicamente si se reciben `attended_classes` y `total_classes` válidos.
- Actividad: entrega, puntualidad y atraso a partir de totales; notas y regularidad solo
  a partir de tareas detalladas.

## Contrato y evidencia insuficiente

Cada `AcademicRecord` acepta `score`, `scale_min` y `scale_max`; los dos últimos son 0 y
100 por defecto. Un valor fuera de su escala se rechaza. La salida usa 0–100.

`evidence_status` es `SUFFICIENT`, `PARTIAL` o `INSUFFICIENT`. Ausencia de períodos no
impide promedios, pero sí tendencia/cobertura temporal; ausencia de tareas detalladas no
impide ratios agregados, pero sí promedio de tareas y regularidad. Todo faltante produce
`null` y advertencia explícita; nunca `0` implícito.

## Pruebas

- escalas 0–100, 0–20 y límites;
- escalas inválidas y notas fuera de rango;
- media global, materia y área;
- pendiente ascendente/estable/descendente y cobertura con huecos;
- una sola observación (variabilidad y tendencia nulas);
- asistencia y actividad completa, agregada, ausente e inconsistente;
- invariancia de normalización y determinismo.

## Interpretación

La pendiente es descriptiva. No prueba causalidad, esfuerzo, inteligencia, aptitud ni éxito futuro. Una nota no excluye carreras.
