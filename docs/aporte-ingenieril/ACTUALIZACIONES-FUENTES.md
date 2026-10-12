# Revisión periódica de publicaciones universitarias

## 1. Flujo para Administración

En `/conocimiento/fuentes`, la biblioteca separa publicaciones universitarias y ministeriales. El panel de revisión permite elegir una frecuencia para cada publicación universitaria validada: manual, cada mes, cada seis meses o cada gestión de doce meses. La selección queda guardada después de la confirmación del servidor.

La primera lectura establece una referencia de comparación. Las siguientes detectan diferencias. Un cambio produce un aviso institucional para administradores activos con permiso de revisión; conserva la versión vigente y requiere comprobar la publicación antes de incorporar nuevos datos. No aprueba, publica ni entrena hechos universitarios automáticamente.

## 2. Datos y justificación del almacenamiento

No se agregaron tablas ni atributos institucionales. `ActualizacionesFuentes` utiliza un archivo privado en `storage/app/private/conocimiento/actualizaciones.json` para frecuencias, intentos, próximas fechas, referencias internas y avisos pendientes. Son metadatos operativos de las publicaciones existentes, por lo que no corresponden a un nuevo expediente escolar.

El archivo se sustituye de forma atómica bajo un bloqueo compartido. Un registro ilegible falla explícitamente, sin borrarlo ni reconstruir fechas ficticias. La huella técnica permanece interna: Administración ve el título del documento y su estado. Las notificaciones reutilizan `notificacion` y `notificacion_usuario`, con sus filtros institucionales de actor, permisos y navegación.

## 3. Comparación de versiones

Laravel envía únicamente el identificador registrado a `/api/v1/knowledge/governance/check-update`; Python resuelve la URL desde el manifiesto validado. No acepta una dirección arbitraria del navegador.

- HTML: compara texto principal normalizado, enlaces académicos publicados y marcadores explícitos de disponibilidad por sede. Una actualización del menú no cambia la referencia; un nuevo enlace de malla o cambio de marcador de oferta sí puede cambiarla.
- PDF: compara los bytes de la publicación; cambios de metadatos también pueden producir un aviso y requieren revisión.
- Redirecciones a otra ruta, portales privados, acceso, documentos vacíos o formato inesperado no se confirman como una revisión académica válida.
- Un error de lectura conserva la referencia anterior y programa un reintento al día siguiente cuando la frecuencia es automática. Un tiempo de espera no acredita invalidez de la universidad.

La comparación no certifica que la modificación sea correcta ni que toda la oferta esté vigente. Los indicadores de sede que carecen de evidencia explícita requieren revisión contra la página oficial.

## 4. Ejecución periódica

El comando `php artisan conocimiento:revisar-actualizaciones` procesa hasta tres publicaciones vencidas por ejecución. Las frecuencias usan meses de calendario sin desbordar el último día del mes; «gestión» significa doce meses desde la referencia y no supone una fecha escolar desconocida.

El calendario de próxima revisión usa `America/La_Paz`, independientemente de que un intento se almacene con hora UTC.

El scheduler Laravel registra el comando cada hora, con protección de solapamiento. En servidores que ya ejecutan `schedule:run`, no hace falta otro mecanismo.

En este ordenador Windows se instaló `SAVP-Conocimiento-Actualizaciones` mediante `scripts/programar-actualizaciones-fuentes.ps1`. Ejecuta exclusivamente este comando cada hora, con el usuario conectado, permisos limitados y máximo de cuatro minutos. No ejecuta otras tareas institucionales ni almacena credenciales. Si el equipo está apagado, no hay sesión o el servicio documental está detenido, la comprobación no puede realizarse; las fuentes vencidas se conservan para una ejecución posterior.

`./scripts/programar-actualizaciones-fuentes.ps1 -Consultar` muestra el estado de la tarea. La primera ejecución registrada el 11/10/2026 a las 15:28:48 (Bolivia) terminó con código 0; en ese momento no había frecuencias automáticas configuradas. Cada publicación empieza en modo manual hasta que Administración elija su frecuencia. En producción debe utilizarse un único programador, evitando registrar simultáneamente esta tarea Windows y otra que ejecute el mismo comando.

En la revisión de navegador se guardó frecuencia mensual para la publicación registrada **Psicología · UNIFRANZ**. Su primera lectura confirmó la referencia el 11/10/2026 a las 15:42:29 (Bolivia), con próxima revisión el 11/11/2026 a la misma hora. No produjo un aviso de cambio porque era la lectura inicial.

## 5. Controles comprobados

Se verificaron: acceso exclusivo de Administrador con `conocimiento.revisar`, rutas autenticadas y limitadas, rechazo de identificadores y frecuencias inválidas, exclusión de fuentes ministeriales, separación de referencia y publicación, cambios sin avisos duplicados, conservación tras fallo, fechas mensuales/semestrales/anuales, y bloqueo de redirecciones privadas.

La lectura conserva el transporte HTTPS existente: credencial interna entre Laravel y Python, dominio institucional contrastado, destino DNS público fijado, redirecciones de la misma institución, límites de tamaño/tiempo y dos lecturas concurrentes. La interfaz usa texto escapado y conserva los datos ante una respuesta incompleta.

Las pruebas de cambios y notificaciones usan respuestas controladas y almacenamiento temporal. No se modificó una página universitaria ni se fabricó un cambio para generar un aviso en la base institucional.

## 6. Documentos relacionados

- [Conversación documental: dataset, tiempos y límites](CONVERSACION-DOCUMENTAL.md).
- [Diseño de gestión del conocimiento](../diseno/GESTION-CONOCIMIENTO.md).
- [Directorio: gráficos y procedencia histórica](../diseno/DIRECTORIO-UNIVERSITARIO.md).
