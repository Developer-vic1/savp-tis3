# Consulta de Gestión académica

Actualización visual del 04/10/2026 en `Fusion_Sistema`. Conserva las acciones, permisos, validaciones y reglas institucionales existentes. No aplica migraciones ni crea gestiones, trimestres o eventos para la revisión.

## Componentes reutilizables

- `calendario-academico`: consulta mensual del año seleccionado, colores por trimestre, selección de fecha, eventos registrados y navegación con flechas del teclado. Las fechas superpuestas se identifican para revisión. Los rangos incluyen fines de semana; no son un cálculo de días efectivos.
- `distribucion-trimestres`: ubicación de los rangos en el año, fechas y avance temporal. Una fecha ausente se presenta como pendiente.
- `recorrido-academico`: hexágonos con conteos y accesos a inscripciones, estructura, trimestres, calendario, trayectorias y alertas. Las conexiones corresponden al expediente; no representan el rendimiento de los estudiantes ni causalidad.
- `comparativa-academica`: conteos reales de inscripciones, planes y eventos por año, selección de indicador, diferencia respecto al registro anterior y tabla accesible. Los años en curso pueden estar incompletos. Los valores ausentes no se convierten en cero.

Los componentes reciben datos ya consultados; no contienen consultas ni escrituras a la BD. Sus estilos están en `resources/css/panel-academico.css`, sus interacciones en `resources/js/panel-academico.js`, importados desde el punto de entrada existente. Reutilizan los tokens `--ui-*`, el tema institucional y movimiento reducido.

## Revisión realizada

Compilación Vite y sintaxis de JavaScript y vistas Blade comprobadas. En el navegador se revisaron la selección de mes y día, cambio de indicador, acceso a trimestres y bloqueo cuando ya existen tres, apertura del formulario de nueva gestión con foco en el diálogo y guardar deshabilitado por la planificación vigente. El acceso al calendario conservó la gestión seleccionada. Se comprobó el ancho en móvil y su renderizado en modo oscuro, restaurando después el tamaño y tema iniciales.

No se enviaron formularios de creación ni se certificaron todas las acciones de los módulos conectados. La validación de servidor y el Support del dominio continúan siendo independientes de esta presentación.

Para revisar: abrir `http://127.0.0.1:8000/admin/gestion-academica` con una cuenta autorizada. Para recompilar recursos: PowerShell en `C:\laragon\www\savp-reestructuracion`, `npm.cmd run build`; el servidor local existente se conserva.
