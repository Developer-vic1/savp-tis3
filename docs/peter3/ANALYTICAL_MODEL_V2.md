# Modelo analítico V2 de PETER 3

## Resultado

`v2_evidence_based` es un análisis determinista y multidimensional. Describe evidencia
observada, relaciones documentales y faltantes; no calcula compatibilidad global, no ordena
carreras y no estima éxito universitario.

V1 se conserva sin cambios de contrato como `LEGACY_EXPERIMENTAL_BASELINE`. Su score y
ranking sirven para comparación histórica y sensibilidad, no fundamentan V2.

## Decisión de arquitectura

**Estado:** aceptada para el núcleo V2.
**Fecha:** 2026-09-29.

### Contexto

V1 mezcla targets RIASEC manuales, umbrales académicos y pesos experimentales. No existe un
outcome real ni ground truth que permita interpretar ese agregado como capacidad o
probabilidad. A la vez, sí existen observaciones académicas, un instrumento RIASEC,
catálogos, fuentes y relaciones documentales útiles.

### Opciones consideradas

| Opción | Ventaja | Riesgo |
|---|---|---|
| Mantener el agregado V1 | Continuidad visual | Convierte decisiones experimentales en una cifra aparente |
| Perfil V2 multidimensional | Conserva evidencia y procedencia sin fabricar ganador | Exige que el usuario interprete trade-offs |
| Modelo predictivo | Podría evaluarse contra outcomes | No existe dataset con `X`, `Y`, ground truth ni control de leakage |

### Decisión

Se adopta el perfil multidimensional. XGBoost u otro predictor queda fuera hasta que existan
outcomes reales, propietario del ground truth, baseline, split defendible y métricas.

### Consecuencias

- `POST /api/v2/analysis` tiene request y response propios.
- falta de evidencia se representa con `null`, listas vacías y estados explícitos, nunca con 0;
- preparación y calidad de evidencia son objetos separados;
- RIASEC se conserva como interés vocacional;
- carrera y ocupación se relacionan mediante crosswalk trazable, sin equivalencia automática;
- ninguna dimensión por sí sola se convierte en recomendación prescriptiva.

## Flujo ejecutable

```text
AnalysisV2Request
  -> validación Pydantic estricta
  -> RIASEC + estadística académica + actividad/asistencia
  -> snapshot de evidencia y faltantes
  -> bridge secundaria-universidad V2
  -> crosswalk carrera-ocupación
  -> perfiles de evidencia por oferta
  -> AnalysisV2Response + trazabilidad
```

No participan Laravel, PostgreSQL, servicios de PETER 2, internet ni LLM.

## Snapshot del estudiante

`StudentAnalyticalSnapshotV2` separa:

- evidencia académica;
- intereses vocacionales;
- evidencia técnica/BTH;
- actividad de aprendizaje;
- asistencia;
- interés declarado;
- evidencia histórica;
- calidad de evidencia y componentes faltantes.

Cada bloque utiliza, cuando corresponde, `AVAILABLE`, `PARTIAL`, `INSUFFICIENT` o
`UNAVAILABLE`.

## Learning Analytics

Una observación se normaliza a 0–100 con:

```text
100 * (score - scale_min) / (scale_max - scale_min)
```

Se calculan media aritmética, mínimo, máximo y desviación estándar poblacional. Hay resúmenes
por asignatura, área y período, pendiente lineal por períodos ordenados, cobertura temporal,
asistencia, entrega, atraso, puntualidad y estadística de tareas calificadas.

`consistency_ratio = 1 - pstdev/50` permanece únicamente en el contrato legacy V1 por
compatibilidad. V2 no lo usa para aumentar o disminuir preparación; reporta desviación
estándar como descripción.

## Preparación basada en evidencia

Cada `PreparationEvidence` registra competencia, asignaturas y áreas observadas, valores
normalizados, conocimiento universitario relacionado, asignaturas iniciales, `relation_id`,
fuentes, estado documental y base del match.

El matching académico compara `record.subject` y `record.area` de forma independiente. En
esta versión solo se ejecutan `SUBJECT_LABEL_CONTAINMENT` y `AREA_LABEL_CONTAINMENT`; no hay
fuzzy matching oculto.

Cuando existe contenido relacionado sin observación comparable se produce
`REINFORCEMENT_AREA`. `numeric_gaps` permanece vacío porque no hay un requisito cuantitativo
oficial en la misma escala y alcance.

## Perfiles de carrera

Cada oferta conserva identidad institucional y cuatro relaciones independientes:

- interés vocacional;
- evidencia técnica;
- interés declarado;
- ocupación.

Además contiene preparación observada, calidad de evidencia, áreas observadas, áreas sin
evidencia, refuerzos, fuentes y limitaciones. No existe un ganador global.

## RIASEC y crosswalk ocupacional

El scoring del O*NET Mini-IP usa 30 ítems, escala 0–4, dimensiones `RIASEC`, suma por código,
orden determinista, Holland code y empates. Sus resultados son intereses.

`career_occupation_v1.json` permite mostrar juntos el Holland code observado y los perfiles
ocupacionales documentados. Esta presentación no calcula distancia ni score. Si el crosswalk
falta, el estado vuelve a `UNAVAILABLE_PENDING_OCCUPATIONAL_CROSSWALK` sin bloquear el resto.

## Calidad de evidencia

No existe un porcentaje sintético. El objeto reporta conteos de registros, asignaturas, áreas
y períodos; cobertura temporal; disponibilidad de asistencia y actividad; completitud RIASEC;
conteos técnicos, declarados e históricos; y faltantes.

## Marco de investigación

DSR/DSRM organiza identificación del problema, diseño del artefacto, demostración y evaluación.
ICONIX pertenece al desarrollo general del sistema. AHP, Delphi, CRISP-DM, Prompt Engineering
e ISO 25010 no se presentan como metodología principal del aporte analítico.
