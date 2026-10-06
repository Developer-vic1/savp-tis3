# SAVP TIS 3

Sistema institucional de gestión académica, aula virtual y orientación basada en evidencia. Laravel/Livewire presenta y protege los flujos; PostgreSQL guarda la historia; `ai-service/` aporta análisis y consulta documental mediante un contrato HTTP.

## Leer primero

- [Índice de documentación](docs/sistema/00-INDICE.md): indica qué MD leer para cada tarea, sin abrir todos los informes.
- [Mapa funcional](docs/sistema/01-MAPA-FUNCIONAL.md): administración, aula virtual y conexiones.
- [Resources y componentes](docs/sistema/02-RECURSOS-Y-COMPONENTES.md): Blade, Livewire, CSS, JS, tema y diseño.
- [Datos y operación](docs/sistema/03-DATOS-Y-OPERACION.md): 104 modelos, historia y conservación de PostgreSQL.
- [Aporte y metodología](docs/sistema/04-APORTE-Y-METODOLOGIA.md): Laravel, FastAPI, RIASEC, DSRM e ICONIX.

## Organización

| Ruta | Contenido |
|---|---|
| `routes/` | Entrada HTTP y control de actor/permiso. |
| `app/Http/Controllers/`, `app/Livewire/` | Adaptadores de solicitudes y acciones de pantalla. |
| `app/Services/`, `app/Policies/`, `app/Support/` | Reglas de negocio, autorización y soporte. |
| `app/Models/Oficial/` | Un modelo por cada tabla oficial. |
| `resources/` | Blade, componentes, estilos y JavaScript. |
| `database/migrations/`, `database/seeders/` | Esquema e historia de datos. |
| `ai-service/` | Servicio Python de análisis y evidencia. |
| `docs/` | Guías vigentes e informes históricos. |

## Base de datos institucional

`SAVPTIS3-OFICIAL` ya contiene la historia institucional. **Conservarla.** No ejecutar `php artisan migrate:fresh`, `migrate:refresh`, `db:wipe` ni `db:seed` masivo contra esa conexión. La preparación antigua de base vacía está documentada como antecedente. Para cualquier cambio, confirmar conexión, evaluar datos y migración incremental, probar en PostgreSQL aislado y obtener autorización concreta.

## Desarrollo

Este repositorio requiere PHP compatible con `composer.json`, dependencias Composer, Node para recursos Vite y PostgreSQL para el modelo canónico. Configurar un entorno local separado antes de ejecutar comandos que escriban datos. `composer.json` define dependencias y scripts; `ai-service/README.md` explica el servicio Python. Para una pantalla, seguir la ruta, controlador/Livewire, servicio, modelo y vista usando el índice. Las credenciales y fuentes personales quedan fuera de la documentación pública.
