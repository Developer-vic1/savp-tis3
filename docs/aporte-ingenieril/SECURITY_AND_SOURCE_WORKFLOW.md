# Permisos, validación e incorporación de fuentes

## Alcance y límites

No se detecta React/Inertia; la revisión aplica a Laravel, Blade/Alpine y al contrato FastAPI de gestión documental. Se preserva el diseño institucional. No es una auditoría completa de todos los módulos ni una garantía de invulnerabilidad.

El login conserva Fortify y la verificación bcrypt. Se accedió a la cuenta administrativa existente mediante su clave inicial ya definida en el seeder, comprobada contra el hash, sin descifrarlo, cambiarlo ni crear sesiones por impersonación. Esa credencial inicial continúa vigente: su titular debe rotarla por el flujo normal de perfil antes de publicar. No se documenta su valor.

## Permisos oficiales

| Acción | Administrador | Director | Docente y otros actores |
| --- | --- | --- | --- |
| Ver fuentes, conexión y probar tutor | conocimiento.ver | conocimiento.ver | Denegado |
| Analizar y proponer | conocimiento.proponer | conocimiento.proponer | Denegado |
| Aprobar o rechazar | conocimiento.revisar | Denegado | Denegado |

Se exige actor y permiso en el servidor. El panel muestra las capacidades efectivas de la sesión, no una autorización inventada en JavaScript. El 2026-10-02 se aplicó sólo `PermisosGestionConocimientoSeeder` con autorización; no se ejecutó `DatabaseSeeder` ni se crearon cuentas.

## Proceso real

1. Iniciar sesión con una cuenta activa y los permisos oficiales.
2. Pegar la URL: comprobar con el botón o tras diez segundos sin escribir. Sólo se consultan universidades registradas con HTTPS. Se comprueba respuesta HTTP, contenido HTML/PDF y duplicidad por URL original/final y hash del corpus. La vista previa es texto, nunca un iframe ni HTML remoto ejecutable. Un duplicado puede identificarse sin descargarlo.
3. Confirmar el título publicado y los datos sugeridos. Se completan sólo campos vacíos; reemplazar datos requiere pulsar «Usar datos detectados». Sede, ciudad, versión y justificación no se inventan. La fecha desconocida permanece vacía. Una respuesta 200 no certifica autenticidad, vigencia ni pertinencia del contenido; los títulos de error comunes se rechazan.
4. Completar la ficha y analizar. Laravel exige una comprobación de URL ligada a sesión, usuario y actor, de hasta cinco minutos; si falta o venció vuelve a comprobar. Rechaza títulos arbitrarios y formatos incompatibles con HTML/PDF, aunque se omita JavaScript. Un dominio .bo desconocido debe verificarse y registrarse antes de habilitar la propuesta. UPB utiliza upb.edu y no se rechaza sólo por carecer de .bo.
5. Enviar: el servidor exige un token de análisis ligado al usuario, actor y ficha, con vigencia de diez minutos y un solo uso. Cambiar los datos exige un nuevo análisis.
6. Revisar con Administración y fundamento escrito. La aprobación genera APROBADA_PENDIENTE_INGESTA, no una fuente activa. Dirección no puede saltar este paso.
7. Ingesta por el operador: obtener una instantánea oficial, revisar pertinencia y contenido, registrar procedencia y SHA-256 en data/sources/sources.json, guardar el archivo local y reconstruir el corpus para ese source_id con scripts/build_corpus.py --source-id ID --merge-existing. No editar sólo el manifiesto ni sustituir el corpus por un índice ajeno.
8. Ejecutar scripts/verify_sources.py; sólo activar la versión si valida procedencia y cardinalidad. Recargar el servicio supervisado y comprobar recuperación y citas. Conservar respaldo para rollback. El formulario no ejecuta automáticamente esta publicación ni añade carreras o porcentajes sin un contrato aprobado.

No se añadieron usuarios ni fuentes ficticias al entorno operativo. Los payloads sintéticos de regresión viven únicamente en tests aislados; las fichas ilustrativas del modal no se envían automáticamente.

Se recorrió el login, análisis, propuesta y aprobación con la cuenta institucional autorizada y la portada oficial https://www.upb.edu/, consultada en la web. La propuesta KGI-8F17F315DC7B queda APROBADA_PENDIENTE_INGESTA: aún no está en el corpus ni se usa para calcular afinidad. El registro operativo no se versiona en Git y debe conservarse en el almacenamiento persistente de cada entorno.

## Controles y evidencia de la revisión acotada

| Área OWASP | Resultado en este alcance |
| --- | --- |
| A01 Acceso | Actor más permiso en rutas; pruebas de invitados, actores ajenos y permisos revocados. |
| A02 Credenciales | bcrypt y clave de servicio privada. Advertencia: clave administrativa inicial aún válida; rotación pendiente del titular. |
| A03 Entrada y salida | Campos permitidos; Blade y x-text escapan salida; roles no se aceptan desde el formulario. |
| A04 Diseño | Análisis, propuesta y aprobación separados; dominio desconocido no puede aprobarse. |
| A05 Configuración | API local privada, sin Swagger público y respuestas sensibles no-store. Producción requiere HTTPS, debug deshabilitado y supervisión. |
| A06 Dependencias | No se realizó una nueva auditoría completa de dependencias en esta revisión acotada. |
| A07 Sesión | Login original sin bypass; throttling de operaciones. No se reutiliza la sesión demo. |
| A08 Integridad | CSRF en formularios; token ligado a ficha y de un solo uso. Ingesta conserva hash y verificación del corpus. |
| A09 Trazabilidad | Servicio devuelve trace_id y estado documental. Falta una auditoría persistente por identidad de revisor en la cola JSON; el rol por sí solo no identifica a la persona. |
| A10 SSRF | La vista previa valida DNS público y fija la conexión a la IP verificada con TLS/SNI del host. Revalida cada redirección, exige misma institución, no envía cookies/credenciales y no usa proxies del entorno. Máximo tres redirecciones, 4 MiB, doce segundos de red y dos descargas concurrentes por proceso. La resolución DNS tiene concurrencia y espera acotadas. |

## Conocimiento sin modelos y uso sin internet

El tutor usa el corpus local, reglas conversacionales y recuperación léxica. Incorporar conocimiento significa curar documentos, registrar procedencia, reconstruir el corpus y verificarlo; no entrenar ni instalar un modelo. No requiere Llama, LM Studio, Ollama ni servicios de inferencia externos. Laravel y FastAPI deben estar disponibles localmente para funcionar sin internet. Una caída del servicio local no se confunde con falta de internet.

Consultar documentos ya incorporados puede funcionar sin conexión a internet. Verificar una URL nueva sí necesita conexión; un fallo de red se muestra como no verificado, nunca como prueba de que la universidad o página no existe. La vista previa no añade documentos al corpus ni permite saltar aprobación/ingesta. PDF escaneados no reciben OCR ni títulos inventados. Los límites de descarga no equivalen a una sandbox completa para PDFs adversarios; la activación sigue requiriendo revisión humana.

Correcciones realizadas: fechas reales también en Laravel, limitaciones rechazadas sin truncar líneas, controles codificados y barras invertidas bloqueados, URL malformada devuelta como bloqueo y no error 500, y fecha opcional ausente normalizada antes del contrato FastAPI.

Las regresiones PHP comprueban permisos, entradas y tokens; las Python prueban URL y estados documentales; las frontend cubren ayuda y validación. El corpus actual se verificó con 16 fuentes y 814 fragmentos. Las pruebas PHP usan SQLite en memoria y las pruebas de escritura Python un directorio temporal. No son prueba de despliegue en nube.

## Presentación al 100%

La cabecera compartida usa filas flexibles en lugar de apilar todos los controles hasta xl; el buscador ya no tiene ancho fijo de 800px. El módulo conserva campos legibles, columnas adaptables y modales limitados al alto disponible. No se utiliza zoom CSS ni se deshabilita el zoom del navegador. Las páginas extensas requieren scroll vertical; no se comprimen todos los campos hasta hacerlos ilegibles.

Referencias: [validación de entradas OWASP](https://cheatsheetseries.owasp.org/cheatsheets/Input_Validation_Cheat_Sheet.html), [prevención de SSRF OWASP](https://cheatsheetseries.owasp.org/cheatsheets/Server_Side_Request_Forgery_Prevention_Cheat_Sheet.html) y [guía de interfaces web](https://github.com/vercel-labs/web-interface-guidelines).
