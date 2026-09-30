# Afinidad, interés y preparación

## Invariantes

```text
interés vocacional != aptitud
afinidad != preparación
preparación != capacidad
calidad de evidencia != preparación
compatibilidad != probabilidad de éxito
```

V2 no usa la palabra afinidad para esconder un agregado. Presenta relaciones separadas y sus
limitaciones.

## Qué conserva V1

`v1_experimental` conserva targets RIASEC manuales, pesos 60/25/15, preparación 80/10/10,
compatibilidad 55/45, umbrales y ranking. Su clasificación es
`LEGACY_EXPERIMENTAL_BASELINE`. No se eliminó porque permite reproducir el estado histórico y
estudiar sensibilidad; no es evidencia de validez científica.

## Relaciones V2

`vocational_interest_relation` usa RIASEC únicamente si el crosswalk ocupacional contiene un
perfil trazable. Muestra códigos y fuentes, pero no una distancia ni porcentaje.

`technical_relation` requiere observación técnica/BTH y relación del bridge. Una especialidad
relacionada es evidencia técnica, no una instrucción de carrera.

`declared_interest_relation` usa solo `declared_interests` y etiquetas del catálogo. Las notas
nunca se transforman en interés.

`occupational_relation` conserva tipo, ocupación, taxonomía, estado de evidencia, fuentes,
justificación y límites. Carrera y ocupación no son equivalentes.

Cada dimensión expone `status`, `evidence`, `sources` y `limitations`. Ausencia es
`UNAVAILABLE` o `INSUFFICIENT`, no 0.

## Preparación V2

Preparación describe evidencia académica/conceptual observada asociada a relaciones del bridge.
No incorpora asistencia, regularidad, cobertura temporal ni calidad como bonificación o
penalización. Los metadatos temporales pueden mejorar la descripción longitudinal, pero no
cambian por sí solos el contenido observado.

No se usan `required=65/70/75`. Sin requisito cuantitativo oficial comparable se crea un área
de refuerzo textual y no una brecha numérica.

## Calidad de evidencia

La calidad se mantiene fuera de `PreparationEvidenceProfile`. Sus dimensiones permiten saber
cuánto y qué tipo de dato respaldó el análisis sin reinterpretarlo como preparación.

## Lenguaje de salida

El sistema puede decir que Matemática tiene el mayor promedio observado entre los registros.
No puede convertir ese máximo local en una fortaleza definitiva, inferir inteligencia, ordenar
qué estudiar ni afirmar probabilidad de éxito.
