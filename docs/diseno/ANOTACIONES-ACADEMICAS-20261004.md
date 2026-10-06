# Ajustes a las anotaciones del calendario y nueva gestión

Trabajo de interfaz en `C:\laragon\www\savp-reestructuracion`, rama `Fusion_Sistema`, 04/10/2026. Se preservaron las modificaciones concurrentes, el estudio ministerial existente, permisos y operaciones institucionales. No se crearon pruebas ni se ejecutaron migraciones, seeders, registros, copias o cambios de datos.

## Anotaciones atendidas

1. Se retiró del calendario administrativo el listado individual de tareas del aula virtual y su consulta. El analista conserva los conteos agregados relevantes al impacto.
2. Se corrigió el espaciado de los paneles del calendario. El balance presenta jornadas estimadas, base laborable, ajuste por eventos y referencia curricular con explicación. No acredita cumplimiento ni días efectivos.
3. Nueva gestión utiliza un diálogo con cabecera compacta y tres pasos: datos, fechas/fuentes, base/trimestres. Vista previa lateral, pie accesible, scroll del cuerpo y adaptación a ancho estrecho.
4. El bloqueo de planificación muestra la ventana temporal, el mes con nombre y una explicación desplegable. Permite consultar la preparación mientras el registro sigue bloqueado por el Support y el servidor.
5. La propuesta de reutilización explica su alcance, muestra planes y horarios de la base activa y permite seleccionar categorías y redactar adecuaciones. Se puede añadir a la descripción. La copia continúa siendo una operación pendiente de validación institucional; no se simula una copia efectiva.
6. La preparación de periodos muestra fechas propuestas y fechas de la gestión consultada por orden de trimestre. Explica qué haría la operación existente: crear nombres/órdenes base solo con catálogo vacío, sin reemplazar registros ni configurar sus fechas. Su opción permanece deshabilitada cuando hay periodos consultados, falta respaldo normativo o la ventana de planificación está bloqueada. Las adecuaciones requieren explicación antes de añadirse a la descripción, con límite total de 500 caracteres.
7. Campos con etiquetas vinculadas, componentes compartidos de fecha y selector, validación visible, contador, agrupación por tarea y vista previa de fechas legibles. El estudio ministerial previo se conservó en el paso de fuentes.

Se corrigió además el cambio de tema durante `wire:navigate`: se recupera la preferencia con `themeManager.init()` en lugar de guardar el tema de la nueva estructura HTML.

## Comprobación

- Compilación de recursos correcta: `npm.cmd run build`, 93 módulos.
- Sintaxis PHP del calendario y compilación/sintaxis Blade de las seis vistas afectadas.
- QA manual en navegador integrado: tres pasos, configuración de categorías, motivo vacío bloqueado, motivo válido incorporado a la descripción, registro deshabilitado en octubre para 2027, salida sin guardar, estudio ministerial visible, comparación de fechas de los tres trimestres, retiro del listado de tareas y balance 218/208/200 con la BD consultada en esta revisión.
- No se interpretan los conteos anteriores de otros registros como los datos actuales: el checkout y la BD son compartidos con otros chats.
- Modo claro/oscuro y navegación entre Gestión y Calendario conservando el tema. Ancho CSS efectivo de 546 px: diálogo de 507 px sin desbordamiento horizontal, campos en columna y pie visible. No se afirma cobertura completa de dispositivos.
- Recarga limpia y apertura del diálogo sin nuevos errores de consola. La personalización es una propuesta de interfaz que se incorpora al campo de descripción; no configura ni copia entidades por sí misma.

Capturas: `output/diseno/nueva-gestion-anotaciones-20261004.png` y `output/diseno/calendario-anotaciones-20261004.png`.
