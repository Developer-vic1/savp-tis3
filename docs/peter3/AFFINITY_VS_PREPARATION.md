# Afinidad frente a preparación

## Invariante

```text
afinidad vocacional/técnica ≠ preparación académica actual ≠ probabilidad de éxito
```

RIASEC aporta evidencia de intereses. Las notas aportan evidencia académica contextual. Ninguna se usa para reescribir la otra.

## Evidencia y configuración

El catálogo boliviano inicial y el puente curricular ya existen como artefactos versionados.
Los hechos oficiales son carreras, áreas, especialidades y asignaturas. Los perfiles RIASEC,
pesos, umbrales y correspondencias son **CONFIGURACIÓN EXPERIMENTAL / NEEDS_EXPERT_REVIEW**.

## Contrato de afinidad

`v1_experimental` combina 60% similitud RIASEC, 25% BTH y 15% interés declarado. Si un
componente falta, no se trata como cero: se informa cobertura y se renormalizan únicamente
los pesos observados. Las calificaciones nunca participan en afinidad.

## Contrato de preparación

Cada requisito de carrera compara evidencia académica normalizada con una competencia y
umbral del puente. Se informa cobertura. Con menos de 40% del peso de requisitos observado,
`preparation_score = null`. El score combina conocimiento observado, consistencia y cobertura
temporal con pesos 80/10/10, renormalizando componentes disponibles.

## Compatibilidad y ranking

Cuando afinidad y preparación existen, compatibilidad = 55% afinidad + 45% preparación.
No es probabilidad de éxito. El ranking ordena score descendente y `career_id` ascendente para
desempatar; un LLM nunca puede modificarlo. Carreras sin preparación suficiente no se
incluyen, pero se reportan como evidencia insuficiente.

## Pruebas

Se prueban separación de notas/RIASEC, faltantes como nulos, pesos versionados, desempate,
brechas `max(0, required-current)`, secuencia de ruta y sensibilidad a pesos/umbrales.

## Sensibilidad ejecutada sobre el fixture canónico

Comando: `python scripts/sensitivity.py` (Python 3.12.14).

| Variante | Primer resultado | Score | ¿Cambia el orden? |
|---|---|---:|---|
| afinidad/preparación 55/45 | Ingeniería de Sistemas UCB | 82.82 | base |
| 80/20 | Ingeniería de Sistemas UCB | 85.14 | no |
| 20/80 | Ingeniería de Sistemas UCB | 79.58 | no |
| cobertura mínima 0.70 | sin ranking | — | se suprime por insuficiencia |

En las tres combinaciones de peso, las dos identidades de Ingeniería Civil conservan el
mismo score y el desempate estable por ID. Estos resultados describen un solo fixture
sintético; no validan los pesos para población real.
