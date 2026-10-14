# SAVP TIS 3

Sistema institucional de gestión académica, aula virtual y orientación basada en evidencia. Laravel/Livewire presenta y protege los flujos; PostgreSQL guarda la historia; `ai-service/` aporta análisis y consulta documental mediante un contrato HTTP.

## Abrir SAVP en Windows

Después de clonar o descargar este repositorio, haz doble clic en **`ABRIR-SAVP.cmd`**. No necesitas escribir los comandos de preparación. El programa:

1. Comprueba PHP, Composer, Node y Python; instala lo que falta y prepara las versiones de dependencias registradas en los archivos lock.
2. Solicita el **nombre exacto de PostgreSQL**, servidor, puerto, usuario y contraseña; comprueba la conexión antes de cambiar `.env`.
3. Permite abrir una **base existente sin migrarla ni cargar datos**, o crear una **base nueva de demostración**, cuyo nombre empieza por `savp_demo_`.
4. Compila estilos e iconos, inicia Laravel y el Aporte en puertos libres de localhost y abre el navegador en el acceso normal del sistema.

La primera preparación requiere Internet. Windows puede pedir permisos para los instaladores; PostgreSQL solicita elegir su contraseña. El iniciador continúa pidiendo esa contraseña, sin inventarla. Las ejecuciones siguientes reutilizan las dependencias preparadas mientras sus archivos lock no cambien. Mantén abierta la consola; pulsa Enter en ella para detener únicamente los servicios que abrió el programa.

La demostración contiene **seis gestiones consecutivas**, terminando en el año actual: **2021–2026** al prepararla en 2026. Incluye 12 estudiantes de primero a sexto, 36 docentes, dos turnos, seis cursos, tres materias de ejemplo, 14 bloques contando recreos, 72 horarios y 2.160 asignaciones semanales. Los dos estudiantes de sexto tienen trayectoria en las seis gestiones; los de grados inferiores tienen la trayectoria correspondiente a su avance. Todo es **sintético y separado de la base institucional**.

Al crearla, eliges una contraseña de al menos 12 caracteres para sus cuentas:

| Acceso de demostración | Correo |
|---|---|
| Estudiante de sexto, mañana | `estudiante6.m@savp.test` |
| Estudiante de sexto, tarde | `estudiante6.t@savp.test` |
| Administrador de demostración | `administrador@savp.test` |

No existe una contraseña pública fija ni un acceso que omita la autenticación. Las cuentas institucionales conservan sus contraseñas. El programa nunca reconstruye `SAVPTIS3-OFICIAL`, borra bases ni repite un seeder sobre registros existentes.

Consulta [la guía del iniciador](docs/sistema/INICIADOR-LOCAL.md) para requisitos, diagnóstico, reinstalación y límites de los datos públicos.

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
