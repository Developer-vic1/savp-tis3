# Seguridad de incorporación, conversación y revisiones documentales

## Alcance

Revisión focal del 11/10/2026 con la skill Laravel OWASP: Laravel/Blade/Alpine, sin React/Inertia detectado en las dependencias de este módulo. Abarca las nuevas revisiones periódicas y la interacción administrativa. No es una certificación de seguridad de toda la aplicación ni una prueba de explotación en producción.

## Controles y evidencia

| Área | Resultado | Evidencia y alcance |
|---|---|---|
| Actor y permisos | Comprobado | Las rutas de revisión exigen autenticación, actor Administrador y `conocimiento.revisar`. Director, otros actores e invitados permanecen bloqueados. |
| Solicitudes falsas | Comprobado | Solo identificadores de la biblioteca universitaria validada y frecuencias enumeradas. No se aceptan URL, destinatarios ni rutas de archivo para revisar versiones. |
| SSRF | Comprobado en código y pruebas | Se reutiliza HTTPS con DNS público fijado, bloqueo de IP privada/credenciales/puertos, redirecciones de la misma institución, tamaño y plazo acotados. Se limita la concurrencia. |
| CSRF y abuso | Comprobado | Rutas web con CSRF, cabecera al enviar formularios y límites separados para lectura, configuración, consulta y extracción. No hay nuevas excepciones CSRF. |
| XSS | Comprobado en cambios | Blade escapado y `x-text` para textos/documentos/respuestas. Los enlaces conservan validación de esquema/dominio. El avatar es SVG local, sin contenido remoto ejecutable. |
| Integridad | Comprobado | Contratos independientes en cliente y servicio; ID devuelto debe coincidir, una revisión verificada necesita referencia válida. Estado privado atómico con bloqueo; cambios no sustituyen el corpus validado. |
| Avisos | Comprobado en pruebas | Productor interno, destinatarios derivados del servidor, permisos actuales y evento deduplicado. Un fallo conserva el aviso pendiente para reintento. |
| Conversación y privacidad | Comprobado | Administración no envía contexto de estudiantes; historial acotado recibido durante la página, sin memoria persistente ni ejecución de instrucciones documentales. El nombre proviene del usuario autenticado y no se envía a un tercero. |
| Dependencias/credenciales | Alcance limitado | No se agregaron dependencias, modelos generativos ni servicios externos. No se alteraron contraseñas, permisos, CORS o protección del sistema. No se ejecutó auditoría global de todas las dependencias en este encargo. |

## Hallazgos corregidos

1. Una comparación solo textual omitía cambios de enlace de malla y marcadores de sede. La nueva referencia incluye esos datos semánticos, con pruebas de regresión.
2. Una redirección a otra publicación o portal podía parecer una versión válida. Se comprueba destino, formato y finalidad pública antes de confirmar su lectura.
3. La consulta semestral descartaba encabezados numerados del PDF procesado y podía reutilizar materias iniciales sin semestre. Se corrigió con pruebas sobre el recuperador y documentos locales reales.
4. Un panel oculto intentaba contar materias antes de recibir una extracción y producía un error de Alpine. El estado vacío ahora protege esa lectura; se comprobó recargando la pantalla.

La protección de `SAVPTIS3-OFICIAL` se conservó: ninguna reconstrucción, migración, seeder masivo ni usuario ficticio. Las pruebas PHP que requieren conexión utilizaron SQLite en memoria, habilitado únicamente en el proceso de prueba.
