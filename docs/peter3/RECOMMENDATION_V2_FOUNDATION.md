# Recommendation V2 — fundamento basado en evidencia

## 1. Problema que resuelve

La versión experimental `v1` utiliza pesos, targets RIASEC de carrera, umbrales académicos y un score de compatibilidad. Aunque están etiquetados como experimentales, esos valores no poseen todavía una fundamentación suficiente para presentarlos como medidas exactas.

`v2_evidence_based` introduce una etapa de transición metodológica: conserva el valor analítico de SAVP, pero elimina del núcleo las cifras que no tienen una procedencia defendible.

## 2. Principios

1. **NINGÚN NÚMERO SIN PROCEDENCIA.**
2. **AFINIDAD ≠ PREPARACIÓN.**
3. **INTERÉS ≠ APTITUD.**
4. **PREPARACIÓN ≠ CAPACIDAD.**
5. **FALTA DE EVIDENCIA ≠ VALOR CERO.**
6. **COMPATIBILIDAD ≠ PROBABILIDAD DE ÉXITO.**
7. Una relación curricular experimental no se presenta como equivalencia oficial.
8. RIASEC se usa como señal de intereses del estudiante, no como perfil psicológico global.
9. No se genera un score RIASEC-carrera hasta disponer de un crosswalk carrera→ocupación→perfil ocupacional documentado.
10. El LLM no participa en cálculos analíticos.

## 3. Qué elimina V2

V2 no utiliza como verdad de decisión:

- `affinity_weights`;
- `preparation_component_weights`;
- `compatibility_weights`;
- targets RIASEC de carrera escritos manualmente;
- `required=65/70/75` sin requisito cuantitativo oficial;
- brechas numéricas construidas desde dichos umbrales;
- ranking global basado en un score compuesto.

La versión `v1_experimental` debe conservarse para comparación histórica y análisis de sensibilidad, no como criterio definitivo.

## 4. Qué produce V2

Para cada oferta académica se construye un **perfil de evidencia**:

- identidad de la carrera y universidad;
- señal RIASEC del estudiante, sin transformarla en afinidad de carrera;
- relaciones secundaria–universidad aplicables;
- evidencia académica observada asociada a relaciones trazables;
- cobertura descriptiva de evidencia;
- relaciones BTH observadas;
- coincidencias de intereses declarados contra términos documentados del catálogo;
- calidad de evidencia;
- fuentes utilizadas;
- limitaciones.

La cobertura es descriptiva: `relaciones con evidencia / relaciones aplicables`. No representa preparación, aptitud ni probabilidad de éxito.

## 5. Preparación

V2 reemplaza el esquema:

`nota actual → umbral inventado → gap`

por:

`contenido/relación documentada → evidencia académica observada → cobertura → áreas sin evidencia suficiente`

Solo se permitirá una brecha numérica cuando exista un requerimiento cuantitativo oficial y una escala comparable.

## 6. RIASEC

El scoring O*NET permanece sin modificaciones.

La comparación estudiante↔carrera queda deshabilitada en V2 hasta construir y documentar:

`carrera boliviana → ocupaciones relacionadas → clasificación ocupacional → perfil RIASEC ocupacional`

La correspondencia carrera→ocupación nunca se tratará como equivalencia automática.

## 7. Calidad de evidencia

La calidad de evidencia se muestra mediante variables observables, no mediante un score arbitrario:

- número de registros académicos;
- materias distintas;
- áreas curriculares;
- períodos ordenados;
- cobertura temporal;
- asistencia disponible;
- instrumento RIASEC completo;
- observaciones técnicas;
- intereses declarados.

## 8. Matching académico

La primera política V2 es deliberadamente conservadora:

- coincidencia por contención del nombre de asignatura con `secondary_content`; o
- coincidencia por contención del área curricular con `secondary_content`.

No existe un umbral semántico oculto. Las coincidencias más flexibles deberán incorporarse como un componente separado y medido antes de utilizarse.

Este cambio corrige además el problema de V1 donde `record.area` no era comparado realmente contra las áreas esperadas.

## 9. Evaluación

La evaluación inicial debe demostrar invariantes:

- no existen scores compuestos en V2;
- no existen umbrales académicos inventados;
- ausencia no se convierte en cero;
- RIASEC no determina carrera;
- toda evidencia de puente conserva `relation_id` y fuentes;
- mismo input produce mismo output.

Posteriormente se incorporará evaluación de robustez sin declarar un “peso verdadero”. Si se estudian combinaciones de pesos, los resultados se reportarán como estabilidad frente a escenarios, no como probabilidad de éxito.

## 10. Estado

**CONFIGURACIÓN EXPERIMENTAL / FASE DE RECONSTRUCCIÓN.**

V2 todavía no constituye validación psicológica, pedagógica ni predictiva. Su objetivo es proporcionar una base técnicamente reproducible y metodológicamente defendible para las siguientes fases de investigación.
