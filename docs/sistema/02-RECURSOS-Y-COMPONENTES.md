# Resources y componentes

## 1. Mapa de `resources/`

| Directorio | Responsabilidad | Ejemplos reales |
|---|---|---|
| `resources/views/layouts` | Shell autenticado e invitado. | `app.blade.php`, `guest.blade.php`. |
| `resources/views/components` | Componentes Blade compartidos. | `actor-menu`, `icono-institucional`, `calendario-institucional`, `paginacion-institucional`, `selector-institucional`. |
| `resources/views/livewire/admin` | Presentación de módulos administrativos. | Gestión académica, personas, usuarios, estudiantes, docentes. |
| `resources/views/livewire/aula-virtual` | Presentación reactiva de cursos, tareas y asistencia. | Cursos docente/estudiante, entregar tarea, registrar asistencia. |
| `resources/views/livewire/shared` | Piezas compartidas entre ámbitos. | `teacher-grade-form`, `course-workspace`, `academic-plan`. |
| `resources/views/aula-virtual` | Páginas, layout y componentes del LMS. | Orientación, cursos, materiales. |
| `resources/views/estudiante/orientacion` | Experiencia académica y vocacional del estudiante. | Intereses, futuro, preparación, plan, asistente. |
| `resources/views/workspaces` | Áreas de trabajo según actor y dominio. | Consulta, reportes, calendario. |
| `resources/css` | Tokens globales y estilos por módulo. | `app.css`, `componentes-institucionales.css`, `gestion-academica.css`. |
| `resources/js` | Tema, interacción y módulos JS. | `app.js`, `componentes-institucionales.js`, `gestion-conocimiento.js`. |
| `resources/markdown` | Texto mostrado al usuario. | Política y términos. |

## 2. Relación entre archivos

Una ruta apunta a controlador o componente Livewire. El controlador entrega una vista Blade. Si es Livewire, la clase en `app/Livewire/` contiene estado, acciones y validación de servidor; su plantilla vive en `resources/views/livewire/`. Un componente Blade en `resources/views/components/` ofrece presentación reusable, no sustituye políticas ni servicios. `@vite(['resources/css/app.css', 'resources/js/app.js'])` carga la entrada global desde el layout.

Para localizar una pantalla, busca primero su ruta, luego `return view(...)` o `render()`. Busca el nombre de la plantilla antes de crear otro componente. No presupongas que dos vistas parecidas comparten el mismo contrato de datos.

## 3. Identidad visual

`resources/css/app.css` define variables `--ui-*` de claro y oscuro. `resources/js/app.js` expone `themeManager`, inicializa el tema y vuelve a hacerlo tras navegación Livewire; también configura avisos y gráficos con esos tokens. Los CSS de cada módulo extienden esa base. `resources/views/layouts/app.blade.php` y el layout de aula virtual conservan la identidad compartida. `WorkspaceNavigation` y `actor-menu` filtran navegación por actor y permisos; la política del servidor sigue siendo independiente.

Antes de editar una pantalla, lee [DESIGN.md](../../DESIGN.md), su vista actual, su clase Livewire o controlador y los recursos CSS/JS que ya importa. Mantén estados de carga, vacío, error, móvil y oscuro. Conserva validación inmediata y Support Inteligente, pero verifica el contrato real de cada módulo: error crítico, advertencia y sugerencia tienen significados distintos.

## 4. Receta de cambio de interfaz

1. Identifica ruta, actor, permiso y modelo consultado.
2. Abre pantalla análoga y componentes reutilizables.
3. Cambia vista y recurso específico; usa tokens `--ui-*` existentes.
4. Conserva validación en servicio/controlador y autorización en ruta/política.
5. Comprueba estados afectados; indica si hubo prueba automatizada, compilación o QA visual real.

Referencia específica: [DESIGN.md](../../DESIGN.md). Los documentos de [docs/diseno](../diseno/) sirven para módulos concretos, pero el código vigente manda si una propuesta antigua difiere.
