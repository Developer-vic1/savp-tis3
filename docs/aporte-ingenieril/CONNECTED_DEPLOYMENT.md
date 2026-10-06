# Arranque conectado y despliegue

## Qué significa reiniciar FastAPI

Laravel ya llama al servicio desde `AporteIngenierilClient`. Un proceso Python iniciado antes de editar código conserva módulos y catálogos en memoria. Reiniciar después de una publicación carga la nueva versión; no exige reconectar manualmente los usuarios ni pulsar botones en cada consulta.

En desarrollo se utiliza `--reload`. En producción no se utiliza: el servicio se inicia al arrancar el servidor y se recupera con un gestor de procesos. Una publicación reinicia el servicio como paso de despliegue controlado. No existe una garantía de continuidad sin un supervisor y monitorización del entorno real.

## Acceso con cuentas institucionales

La página es `/conocimiento/fuentes`. Está en el menú **Conocimiento universitario** de Administración y Dirección. Conserva login, sesión, CSRF y autorización reales.

Se utiliza la conexión de BD configurada en `.env` y el login institucional existente. No se crean cuentas de demostración, no se sustituyen contraseñas y no se omite la autorización. Cada persona debe ingresar sus credenciales directamente en la pantalla de acceso. Las contraseñas almacenadas como hashes no se recuperan como texto; la recuperación autorizada debe utilizar el flujo del sistema.

En desarrollo, iniciar FastAPI desde `ai-service` con `.venv/Scripts/python.exe -m uvicorn app.main:app --host 127.0.0.1 --port 8001 --reload` y Laravel con `php artisan serve --host=127.0.0.1 --port=8000`. Las claves internas deben coincidir en ambos archivos `.env`. Estos comandos no ejecutan migraciones ni seeders. Para abrir dos actores simultáneamente en el mismo navegador, utilizar sesiones independientes; dos pestañas con la misma cookie comparten la identidad.

En la página:

1. **Comprobar conexión** verifica Laravel, salud FastAPI, credencial interna, recuperación BM25 y respuesta del tutor. Un fallo muestra un estado pendiente, no un verde ficticio.
2. **Probar tutor** permite un saludo, agradecimiento o pregunta académica usando el servicio real, sin contexto ni identidad de estudiantes. El historial de la prueba sólo permanece en la página abierta.
3. **Ver casos de prueba** completa una ficha, sin enviarla: dominio reconocido, duplicado, página extranjera e institución inconsistente.
4. **Analizar fuente** verifica metadatos, procedencia y duplicidad; no descarga ni acredita automáticamente el contenido de la web.
5. **Enviar a revisión** requiere permiso y un análisis vigente ligado a la ficha. Sólo Administración puede aprobar. Aprobar deja la fuente pendiente de incorporación mediante instantánea y reconstrucción del corpus.

## Permisos oficiales

`PermisosGestionConocimientoSeeder` es idempotente y forma parte de `RolSeeder`, invocado por `DatabaseSeeder`. Crea `conocimiento.ver` y `conocimiento.proponer` para Administrador y Director; `conocimiento.revisar` sólo para Administrador. El menú y las rutas exigen permiso además del actor. Con una copia de seguridad y autorización de cambios sobre la BD de destino, puede aplicarse sólo este seeder, sin recrear usuarios ni ejecutar el catálogo completo:

```sh
php artisan db:seed --class='Database\Seeders\PermisosGestionConocimientoSeeder' --force
```

El 2 de octubre de 2026 se ejecutó únicamente este seeder sobre la BD PostgreSQL configurada, con autorización explícita del responsable. Se verificaron los tres permisos efectivos del Administrador existente. No se ejecutaron seeders de usuarios ni migraciones, ni se modificaron contraseñas. Sin los permisos correspondientes, el acceso al módulo permanece bloqueado aunque el usuario tenga el rol correcto.

## Nube: perfil Linux en un mismo servidor

Se incluye `deployment/systemd/savp-aporte.service` para un VPS Linux con Laravel/PHP y FastAPI en el mismo servidor. No es un despliegue ejecutado ni una receta para hosting exclusivamente PHP.

- Navegador → HTTPS del sitio → Laravel → `127.0.0.1:8001` → FastAPI → corpus validado.
- El puerto 8001 no se publica. La clave permanece en las variables de los servidores.
- Un solo worker protege la cola JSON actual; antes de escalar a varios escritores, se necesita persistencia transaccional autorizada.
- `StateDirectory` mantiene la cola en `/var/lib/savp-aporte`, fuera de las versiones del código. Debe respaldarse y conservarse entre publicaciones.
- La unidad comprueba hashes del corpus antes de arrancar, se inicia con el servidor y configura reinicio ante fallos. No puede garantizar recuperación si el sistema operativo, las dependencias o los archivos están dañados.

Pasos del operador en el servidor de destino:

1. Instalar PHP/extensiones y Python compatibles; instalar Composer y dependencias Python desde los locks. Preparar el proyecto en `/srv/savp/current` y el entorno virtual en `ai-service/.venv`.
2. Copiar `deployment/systemd/aporte.env.example` a `/etc/savp/aporte.env`. Colocar una clave aleatoria real, restringir permisos del archivo y usar el mismo valor como `APORTE_INGENIERIL_API_KEY` en Laravel. No publicar datos de pruebas ni secretos locales.
3. En Laravel, habilitar `APORTE_INGENIERIL_ENABLED=true`, `APORTE_INGENIERIL_BASE_URL=http://127.0.0.1:8001` y `APORTE_INGENIERIL_ALLOWED_HOSTS=127.0.0.1`. Configurar la BD institucional autorizada, sesiones persistentes, `APP_ENV=production`, `APP_DEBUG=false`, URL pública HTTPS y cookies seguras. Proteger `.env` y `storage/app/private` fuera de la raíz pública.
4. Configurar HTTPS y el servidor web con raíz `public/`. No servir la raíz del repositorio ni usar el servidor de desarrollo PHP en producción.
5. Copiar la unidad a `/etc/systemd/system/savp-aporte.service`, ejecutar `systemctl daemon-reload` y `systemctl enable --now savp-aporte`. Adaptar usuario/rutas si el hosting difiere del perfil descrito.
6. Ejecutar `php artisan config:cache`, `php artisan view:cache` y `php artisan aporte:comprobar`. El último comando debe devolver código 0 antes de habilitar el tráfico.
7. Verificar login con los actores aprobados, bloqueo de otros actores, análisis, tutor, propuesta y revisión en staging. Comprobar respaldos y persistencia tras reiniciar el servicio. No publicar si estos controles fallan.

### Publicación y rollback

Publicar una versión preservando `.env`, datos privados, corpus y cola. Ejecutar `systemctl restart savp-aporte`, comprobar `systemctl is-active savp-aporte` y `php artisan aporte:comprobar`. El gestor de procesos hace el arranque; el reinicio de publicación carga el código nuevo.

Si falla la autenticación interna, el corpus, el tutor o el acceso autorizado, detener la publicación y volver a la versión anterior sin resetear la cola ni ejecutar seeders de usuarios. Revisar los logs sin registrar claves o cuerpos académicos. El dominio, TLS, usuario del servicio y conexión con la BD de la nube deben comprobarse en el servidor elegido; no fueron validados desde este equipo Windows.

Referencias: [conceptos de despliegue de FastAPI](https://fastapi.tiangolo.com/deployment/concepts/) y [orden de arranque y comprobaciones de salud de Docker Compose](https://docs.docker.com/compose/how-tos/startup-order/). El `compose.yaml` existente sigue siendo de desarrollo Sail y no se presenta como configuración de producción.
