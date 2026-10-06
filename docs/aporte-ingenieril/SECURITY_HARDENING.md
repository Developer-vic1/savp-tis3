# Endurecimiento de Aporte Ingenieril

Ningún sistema puede declararse invulnerable. Este módulo aplica defensa en profundidad y debe mantenerse con auditorías, rotación de secretos, copias de seguridad y actualización continua.

## Fronteras activas

- Laravel conserva autenticación, sesión, CSRF y autorización por actor.
- Director y Administrador pueden consultar y proponer; solo Administrador puede revisar.
- FastAPI exige `X-SAVP-AI-Key` en todas las rutas `/api`, incluso en desarrollo.
- `APORTE_INGENIERIL_ALLOWED_HOSTS` limita los destinos del cliente servidor a servidor.
- HTTP solo se permite contra loopback; cualquier host remoto debe usar HTTPS.
- Las rutas de gobierno tienen límites por minuto y el servicio rechaza cuerpos mayores a 64 KiB, incluyendo solicitudes sin `Content-Length`. El límite se comprueba durante la recepción, antes del parseo JSON.

## Fuentes y aprobación

- Las URLs rechazan credenciales, IP, puertos no estándar y dominios extranjeros.
- La institución declarada debe coincidir exactamente con la universidad del dominio.
- Los fragmentos y el orden de parámetros no permiten evadir la detección de duplicados.
- Las fechas imposibles, campos excesivos y colas saturadas se bloquean.
- El token de análisis es de un solo uso, dura 10 minutos y queda ligado al usuario, rol y contenido exacto.
- Una aprobación nunca descarga ni incorpora automáticamente contenido al corpus.

## Operación segura

1. Generar una clave aleatoria de al menos 32 caracteres y colocar el mismo valor en `SAVP_AI_API_KEY` y `APORTE_INGENIERIL_API_KEY`.
2. No registrar, enviar al navegador ni versionar esa clave. Rotarla ante cualquier sospecha de exposición.
3. Mantener FastAPI en loopback o red privada y terminar TLS en el proxy si se separan los servicios.
4. Ejecutar periódicamente `composer audit --locked`, `npm audit --omit=dev` y `pip-audit` sobre el entorno Python.
5. Revisar logs por respuestas 401, 403, 413 y 429, sin almacenar cuerpos académicos ni credenciales.
6. Respaldar y controlar permisos de `ai-service/data/knowledge/source_intake.json`.
7. Mantener un solo proceso escritor para la cola JSON. El bloqueo actual protege hilos dentro del proceso, no coordina múltiples workers. Antes de escalar, sustituir esta persistencia por un almacén transaccional autorizado.
8. Configurar límites y tiempos de lectura también en el proxy para conexiones lentas. La validación del dominio no acredita contenido, vigencia ni ausencia de compromiso del sitio; la revisión humana e incorporación mediante instantáneas siguen siendo obligatorias.
