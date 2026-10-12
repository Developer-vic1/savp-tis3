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

## 5. Navegación y recursos compilados

En PowerShell, desde `C:\laragon\www\savp-reestructuracion`, `npm.cmd run build` prepara los recursos de `public/build`. Con `SAVP_RECURSOS_COMPILADOS=true` en `.env`, el proveedor usa esos archivos aunque exista `public/hot`. Esto evita solicitar la cascada de módulos de Vite al abrir cada pantalla. La configuración exige que exista el manifiesto; no detiene el servidor Vite ni elimina su archivo de control.

Después de editar CSS o JavaScript en este modo, ejecuta `npm.cmd run build` y recarga la página. Para volver a recarga automática durante desarrollo, usa `SAVP_RECURSOS_COMPILADOS=false` y `npm.cmd run dev`. Si hay configuración compilada, aplica `php artisan config:clear`. `php artisan view:cache` prepara las vistas Blade; no modifica la base de datos. No ejecutar `config:cache` durante este diagnóstico ni migraciones para acelerar la interfaz.

Calificaciones reutiliza lecturas durante un máximo de 20 segundos, con separación por cuenta, permiso de orientación, vista y filtros. «Actualizar datos» invalida las cuatro lecturas de esa cuenta; las exportaciones y la ficha consultan directamente los datos actuales. La autorización se verifica en cada petición. La lectura se guarda como JSON en la caché privada de archivos, sin caché de permisos. La campana cerrada consulta sólo el contador autorizado; carga la bandeja y las vigencias al abrirse.

Diagnóstico reproducible: `php scripts/medir-navegacion.php` mide el render de Inicio, Calificaciones, Personas, Cursos, Reportes administrativos, Gestión Académica y Turnos; `php scripts/medir-calificaciones.php` separa SQL, cantidad de consultas y render de cada pestaña. Requieren PostgreSQL disponible y una cuenta administradora existente con permisos para las lecturas. Los scripts usan una transacción de solo lectura y sesión en memoria; no crean cuentas ni registros. Sus tiempos no incluyen red, recursos del navegador ni toda la cadena HTTP. La primera carga puede incluir compilación y calentamiento de archivos; comparar lecturas posteriores además de la inicial. Gestión Académica puede asegurar el trabajador local de estudios pendientes: su caché privada de archivos es independiente de los registros institucionales.

Para servir localmente con OPcache desde PowerShell en esa misma carpeta, ejecuta `./scripts/servir-optimizado.ps1`. Abre `http://127.0.0.1:8000/admin`; usa la sesión y la base existentes. El script exige que 8000 esté libre, usa el router oficial de Laravel y la extensión OPcache del PHP seleccionado por `Get-Command php`. La primera petición prepara el código; las siguientes lo reutilizan en memoria. `opcache.validate_timestamps=1` y `opcache.revalidate_freq=0` conservan la detección de cambios PHP. No edita el `php.ini` global. Ctrl+C detiene el servidor iniciado en esa terminal. El servidor optimizado iniciado en segundo plano durante el diagnóstico se detiene con `./scripts/detener-servidor-optimizado.ps1`, que verifica el proceso, puerto, router del proyecto y argumento OPcache; sus logs están en `storage/logs/servidor-optimizado.*.log`.

La transición entre páginas mantiene la navegación HTTP normal: barra de progreso sin bloquear pantalla ni demorar la solicitud, y fundido de 140 ms en navegadores con [transición entre documentos](https://developer.mozilla.org/en-US/docs/Web/CSS/Reference/At-rules/%40view-transition). El movimiento reducido desactiva la animación; descargas, anclas y enlaces externos conservan su comportamiento. El inicio del trabajador de calendario usa [ShellExecute de Windows](https://learn.microsoft.com/en-us/dotnet/api/system.diagnostics.processstartinfo.useshellexecute) sin redirecciones de proceso para no conservar el socket del servidor. Los estados del estudio quedan en `storage/app/private/estudio-calendario` y sus errores en el logger de Laravel.

El gráfico de roles consulta el morfotipo de `User::getMorphClass()`. El de especialidades cuenta estudiantes distintos desde las vigencias activas de `inscripcion_vigencia.cod_esp_tec`, dentro de la gestión actual y la fecha de control. No usa el campo histórico vacío `estudiante.cod_esp`. La historia ORAV y el RIASEC O*NET vigente se mantienen como instrumentos separados; corregir las consultas no requiere repetir seeders ni generar análisis individuales.

Reportes administrativos agrupa inscripciones y módulos directamente en SQL y carga sólo los campos de actividad que presenta. Turnos agrupa conteos de bloques, horarios y detalles; reutiliza catálogos y mapas únicamente durante el render. Al terminar descarta esas lecturas: las acciones y las validaciones preventivas vuelven a consultar los datos actuales. Los metadatos de tablas se reutilizan dentro de la instancia, sin guardar registros entre peticiones.

Medición del 07/10/2026 con la base existente: Turnos pasó de 2.789 consultas y 2.805 ms a 114 consultas y 233 ms, incluyendo el shell en el diagnóstico CLI. Las siete páginas medidas quedaron entre 157 y 527 ms de render. Son mediciones locales de esa lectura, no tiempos garantizados de navegación completa. La petición HTTP de acceso, ya caliente y con OPcache, respondió en 125–136 ms; no equivale a medir una página administrativa autenticada. Verificación final: 83 pruebas PHP (518 aserciones), 10 pruebas de gráficos/navegación y compilación Vite correctas. La inspección visual del navegador quedó pendiente por fallo de su herramienta de control.
