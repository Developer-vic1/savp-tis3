# Continuidad de UI/UX de SAVP

Estas instrucciones se aplican al crear o modificar interfaces en este repositorio. La referencia indicada por el usuario son los paneles de administrador y su prevención en tres capas.

- Lee `DESIGN.md` y la pantalla existente más cercana antes de diseñar una vista o componente. Extiende la identidad actual, incluidos shell, tokens, tipografía, iconos y vocabulario educativo.
- Los colores y el comportamiento del tema se basan en los recursos existentes de `resources/css` y `resources/js`. Reutiliza sus tokens y la adaptación claro/oscuro; no introduzcas paletas paralelas en vistas o scripts.
- Una pantalla nueva dentro de SAVP conserva su sistema visual. Las skills de diseño complementan esa base; sus catálogos no autorizan reemplazarla por una plantilla, otra paleta o un framework distinto.
- En formularios y acciones conserva la validación inmediata, las vistas previas pertinentes y los bloqueos explicados. La validación del servidor sigue siendo independiente de la interfaz.
- Reutiliza el Support Inteligente del dominio y sus contratos reales. Distingue errores críticos, advertencias y sugerencias; conserva el respaldo de las reglas y no conviertas una recomendación en una decisión automática.
- Mantén actor, permisos, rutas y ámbito de datos existentes. Una mejora visual no amplía autorización ni permite mostrar datos ajenos.
- Verifica los estados afectados, incluidos errores, carga, ausencia de datos, móvil y modo oscuro. Distingue inspección de código, pruebas y QA visual; declara qué se comprobó realmente.

Esta guía no solicita rediseñar pantallas existentes ni modifica las restricciones de Git, base de datos o publicación del encargo activo.

## Migraciones y seeders institucionales

Empieza por `docs/sistema/00-INDICE.md` y lee solo la guía del área afectada. Para migraciones, modelos y seeders consulta `docs/sistema/03-DATOS-Y-OPERACION.md` y, después, el apartado necesario de `database/seeders/Oficial/GUIA_AGENTES.md`.

`SAVPTIS3-OFICIAL` ya es la base institucional con historia. **No ejecutar `migrate:fresh`, `migrate:refresh`, `db:wipe`, `schema:drop` ni el seeder masivo sobre ella.** Los ejemplos anteriores de reconstrucción en la guía de seeders son evidencia histórica. Las pruebas que vacían tablas van exclusivamente a PostgreSQL aislado; cualquier cambio institucional requiere evaluar datos, respaldo y autorización concreta.
