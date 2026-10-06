# Guía de lectura de SAVP

Este índice indica qué leer para cada tarea. Empieza aquí; abre solo el documento y los archivos de código relacionados con tu cambio. El código vigente prevalece sobre informes históricos.

## Primera lectura

| Necesidad | Leer | Luego consultar |
|---|---|---|
| Entender todo el sistema en diez minutos | [01-MAPA-FUNCIONAL.md](01-MAPA-FUNCIONAL.md) | Ruta o servicio del flujo concreto. |
| Cambiar vistas, estilos o componentes | [02-RECURSOS-Y-COMPONENTES.md](02-RECURSOS-Y-COMPONENTES.md) | [DESIGN.md](../../DESIGN.md), vista y CSS/JS cercanos. |
| Consultar entidades, inscripción, notas o datos | [03-DATOS-Y-OPERACION.md](03-DATOS-Y-OPERACION.md) | [Inventario de 104 tablas](../../database/seeders/Oficial/INVENTARIO_104_TABLAS.md). |
| Cambiar orientación, tutor o integración Python | [04-APORTE-Y-METODOLOGIA.md](04-APORTE-Y-METODOLOGIA.md) | Contrato y prueba del caso concreto. |
| Rendimiento, gráficos de carreras y estudiantes relacionados | [Rendimiento de estudiantes](../diseno/RENDIMIENTO-ESTUDIANTES.md) | Contrato del aporte y evidencia del estudiante en la gestión seleccionada. |
| Ver reglas para agentes | [AGENTS.md](../../AGENTS.md) | Guía correspondiente de esta tabla. |
| Roles, cuenta personal y vigencias | [ROLES-Y-ACCESOS-PROGRAMADOS.md](ROLES-Y-ACCESOS-PROGRAMADOS.md) | Servicios de autorización y migración incremental de Sistema. |
| Prevención y Supports por dominio | [05-SUPPORTS-Y-PREVENCION-DE-ERRORES.md](05-SUPPORTS-Y-PREVENCION-DE-ERRORES.md) | Reglas, tres capas, amenazas y criterios de cobertura. |

## Documentos existentes: uso preciso

| Documento o carpeta | Para qué sirve | Cuándo abrirlo |
|---|---|---|
| [README.md](../../README.md) | Entrada breve y mapa del repositorio. | Al llegar al proyecto. |
| [DESIGN.md](../../DESIGN.md) | Identidad visual, comportamiento y patrones de prevención. | Antes de editar interfaz. |
| [GUIA_AGENTES.md](../../database/seeders/Oficial/GUIA_AGENTES.md) | Historial de carga, fuentes privadas, relaciones, límites y verificaciones. | Antes de tocar seeders o migraciones; seguir el aviso actual de conservación. |
| [INVENTARIO_104_TABLAS.md](../../database/seeders/Oficial/INVENTARIO_104_TABLAS.md) | Relación tabla, migración y único modelo oficial. | Al buscar una entidad concreta. |
| [CONTEOS_VERIFICADOS.md](../../database/seeders/Oficial/CONTEOS_VERIFICADOS.md) | Conteos de la carga observada. | Al comparar una instantánea; revalidar datos vivos. |
| [docs/diseno](../diseno/) | Decisiones y fichas de pantallas administrativas. | Solo ficha del módulo visual que se modifica. |
| [docs/implementacion-maestra](../implementacion-maestra/) | Auditorías, planificación y antecedentes de 105 ventanas. | Para reconstruir una decisión antigua; no tomarla como estado actual sin revisar código. |
| [docs/reestructuracion-bd](../reestructuracion-bd/) | Propuesta original de estructura. | Para motivación de diseño; contrastar con migraciones actuales. |
| [docs/peter3](../peter3/) | Contratos, investigación, evaluación y evolución de Python. | Solo el contrato, metodología o evaluación requerida. Muchos archivos son hitos históricos. |
| [docs/aporte-ingenieril](../aporte-ingenieril/) | Operación e integración específica del servicio. | Para conexión, fuentes, tutor o despliegue. |
| [ai-service/README.md](../../ai-service/README.md) | Ejecución del servicio Python. | Al trabajar en `ai-service/`. |

## Búsqueda rápida

1. Ubica la ruta en `routes/`; comprueba actor y permiso.
2. Sigue controlador o componente Livewire a `app/Services/` y al modelo oficial.
3. Abre la vista en `resources/views/` y sus recursos CSS/JS si cambias interfaz.
4. Para Python, sigue `AporteIngenierilClient` hasta endpoint, contrato y prueba.
5. Lee documentación extensa solo si el caso no queda claro con esas fuentes.

**Base oficial:** `SAVPTIS3-OFICIAL` se conserva. No ejecutar `migrate:fresh`, `db:wipe`, `schema:drop`, seeders masivos ni restauraciones encima de ella. Una nueva migración o carga necesita revisión de efectos, respaldo y autorización concreta. Las pruebas destructivas usan una base aislada identificada.
