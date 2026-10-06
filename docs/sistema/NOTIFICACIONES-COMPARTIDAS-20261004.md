# Notificaciones institucionales compartidas

Implementación del 04/10/2026 en `C:\laragon\www\savp-reestructuracion`, rama `Fusion_Sistema`. Se aplicó exclusivamente `database/migrations/Sistema/2026_10_04_230000_crear_notificaciones_institucionales.php` a `SAVPTIS3-OFICIAL`. No se ejecutaron seeders ni reconstrucciones. `SAVP_NOTIFICATIONS_ENABLED=true` habilita la bandeja.

## Modelo y normalización

Los modelos se encuentran en `app/Models/Oficial/Sistema`. `notificacion` contiene un hecho y su contenido, tipo, origen, estado, publicación, vencimiento y referencias opcionales al emisor y la gestión. `notificacion_usuario` relaciona el hecho con una cuenta y conserva su lectura y archivo. Un aviso compartido no duplica su mensaje por destinatario. Una persona sin cuenta no es destinataria; una cuenta con varios permisos no recibe filas duplicadas.

- Códigos `NOT_000001` y `NUS_000001`: generador institucional con transacción y bloqueo PostgreSQL. No se muestran en la interfaz.
- Claves foráneas reales a `users.cod_usu`, `gestion_academica.cod_gea` y `notificacion.cod_not`, con eliminación restringida y actualización en cascada.
- Un hecho por `(origen, clave_evento)`; un destinatario por `(cod_not, cod_usu)`. Una clave existente no permite reemplazar el contenido que ya se entregó.
- Orígenes: `ADMINISTRATIVO`, `AULA_VIRTUAL`, `APORTE`, `SISTEMA`. Tipos: `INFORMACION`, `ADVERTENCIA`, `ACCION`, `RECORDATORIO`. Estado del hecho: `ACTIVA` o `CANCELADA`.
- Lectura y archivo son independientes. Se derivan de `leida_en` y `archivada_en`; no hay indicadores redundantes que puedan contradecirse. Restaurar conserva la lectura.
- Restricciones PostgreSQL sobre códigos, valores permitidos, contenido no vacío, longitud y orden de fechas. Índice compuesto para la bandeja de cada cuenta.
- `down()` rechaza eliminar tablas con historial. Una base vacía permite revertir ambas tablas en orden de dependencias.

## Selección y acceso

`NotificationService` exige que el usuario consultado coincida con el autenticado y sea un actor activo válido. La consulta, conteo, lectura, archivo y restauración comienzan desde `User::avisos()` con `cod_usu` del autenticado. Cambiar el identificador del aviso no permite actuar sobre otro destinatario. No se aceptan identificadores de usuario desde el centro Livewire.

Solo se muestran hechos activos, publicados y no vencidos. Los permisos se contrastan nuevamente con la navegación autorizada en cada petición, también al cambiar la lectura o el archivo. Los enlaces se limitan a rutas existentes del menú del destinatario y su permiso correspondiente; no se aceptan enlaces externos ni parámetros para abrir registros ajenos. La consulta de la bandeja no marca automáticamente los avisos como leídos.

La campana es común al shell administrativo y Aula Virtual. Incluye Bandeja, No leídas, Leídas, Archivadas y filtros por área, paginación de cinco avisos, carga y controles deshabilitados mientras se procesa una acción. La apertura se conserva con una propiedad Livewire; consulta periódica de 60 segundos solamente cuando el panel está visible. La actividad local de pantalla continúa separada y no se copia indiscriminadamente a la BD.

Las acciones de lectura y archivo registran bitácora con nombres de acción menores de 20 caracteres. No se almacenan contraseñas, conversación del agente ni datos privados en un mensaje automático.

## Productores conectados

`publicar()` es un servicio interno, no un endpoint del agente ni una acción pública. Recibe destinatarios determinados por el dominio en el servidor; consulta sus cuentas y permisos reales. Ignora cuentas inactivas o sin el acceso correspondiente y evita duplicados bajo concurrencia.

1. Administración: una gestión creada correctamente avisa a la cuenta que la registró, con referencia a esa gestión.
2. Aula Virtual: publicar una tarea avisa únicamente a cuentas estudiantiles vinculadas por membresía e inscripción vigentes a la clase visible y a una gestión activa. Los borradores no notifican. Se usa `CursoVirtualService::estudiantesVigentes()` y el permiso del aula del destinatario.
3. Aporte: una propuesta de fuente aceptada avisa a la cuenta que realizó esa propuesta, con acceso a Conocimiento universitario. No anuncia que la propuesta esté aprobada o indexada.

Los demás eventos del sistema y resultados del agente pueden utilizar el mismo servicio al incorporar su productor y su regla de destinatarios. No se generaron avisos históricos ni se simularon acontecimientos en la base oficial. Laravel `Notifiable` y sus notificaciones de acceso por correo se conservan.

## Respaldo y comprobación

Respaldo completo en `storage/app/private/respaldos/oficial-antes-notificaciones-20261004-220555.dump`, 67.830.985 bytes, generado con PostgreSQL 18. SHA-256: `8b2686ddf546b71a444a27d2d5834c437622c4bc3c4e95fd0b91124310d267e5`. El catálogo se verificó mediante `pg_restore --list`; no se restauró sobre la base oficial.

En PostgreSQL aislado se comprobó creación, rollback, protección del historial, códigos, rechazo de destinatarios duplicados y tipos desconocidos, lectura y restauración del archivo. También se comprobó con el servicio real que una cuenta solo obtiene su destinatario y que intentar modificar el destinatario de otra cuenta se rechaza; en esta comprobación se sustituyeron únicamente los catálogos de rol y navegación por adaptadores aislados. La base aislada se eliminó al terminar. Es una comprobación operacional acotada, no una nueva suite de pruebas.

`output/diseno/estado-notificaciones.php` confirmó las dos tablas, migración aplicada, habilitación y cero avisos iniciales en la base oficial. La campana vacía y sus filtros se revisaron en el navegador; los productores quedan para uso real, sin publicar tareas, crear gestiones o enviar propuestas artificiales para comprobarlos.

Para otra instalación: realizar respaldo, verificar conexión y aplicar únicamente esta migración con PHP de Laragon. No ejecutar todas las migraciones pendientes de otros trabajos ni usar `migrate:fresh`. Activar el indicador después de aplicar la estructura y limpiar la caché de configuración.

## Ajustes de comparación académica

La comparación admite varias gestiones mediante el selector institucional múltiple, con perfil radial y cantidades al costado. Se comprobó selección de 2021, 2022, 2025 y 2026; la línea también permite limitarse a las gestiones elegidas. La personalización tiene icono de ajustes, contador, flecha animada y estado expandido diferenciado. El gráfico conserva el aviso de gestión incompleta y no transforma conteos en rendimiento. Se revisaron teclado, ausencia de desbordamiento en tamaño móvil, modo oscuro y consola sin errores; compilación Vite y Blade correctas. Evidencia en `output/diseno/comparativa-personalizable-20261004.png`.
