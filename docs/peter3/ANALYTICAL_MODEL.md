# Modelo analítico

## Núcleo descriptivo `core-criteria-0.1.0`

El modelo actual no es predictivo. Combina dos salidas visibles e independientes:

1. intereses RIASEC: suma oficial por dimensión;
2. rendimiento observado: estadística descriptiva de notas normalizadas.

## Orientación `v1_experimental`

Sobre el núcleo descriptivo, el motor determinista puede calcular afinidad, preparación,
compatibilidad, ranking, fortalezas, brechas y ruta. Los artefactos responsables son el
catálogo oficial, el puente experimental y `data/criteria/recommendation_v1.json`.

Compatibilidad es un índice orientativo, no probabilidad de éxito. Una carrera sin cobertura
académica mínima conserva preparación `null` y se excluye del orden, sin convertir ausencia
en cero.

**CONFIGURACIÓN EXPERIMENTAL.** El estado `COMPLETE/PARTIAL/INSUFFICIENT` evalúa cobertura de los dos componentes centrales; no evalúa la calidad humana del estudiante.
