# Supports de SAVP: prevención, integridad y recuperación

## Propósito y alcance

Cada operación debe impedir errores previsibles y solicitudes manipuladas antes de modificar datos institucionales. El Support ayuda al usuario a completar correctamente el proceso; el servidor decide si la operación está autorizada y la base conserva la integridad.

Este documento establece el contrato que deben cumplir los Supports de Administración, Aula virtual y Aporte. No certifica que todas las pantallas ya lo implementan ni promete impedir fallos desconocidos. Una regla solo se considera cubierta cuando tiene validación del servidor, pruebas pertinentes y, cuando corresponde, restricción de base de datos. Detectar texto extraño es una ayuda de calidad; no reemplaza autorización, consultas parametrizadas ni escape de contenido.

Referencia visual: [DESIGN.md](../../DESIGN.md). Referencia operativa: [03-DATOS-Y-OPERACION.md](03-DATOS-Y-OPERACION.md). Gobernanza concreta: [ROLES-Y-ACCESOS-PROGRAMADOS.md](ROLES-Y-ACCESOS-PROGRAMADOS.md).

## Tres capas obligatorias

| Capa | Responsabilidad | No debe hacer |
|---|---|---|
| Interfaz preventiva | Guiar campos, ofrecer catálogos, mostrar errores al interactuar, vista previa, bloquear acciones incompletas y doble clic. | Conceder permisos, certificar documentos o asumir que ocultar un botón protege la operación. |
| Servidor y Support del dominio | Validar tipos, límites, catálogo, identidad, autorización, relaciones y reglas actuales. Revalidar dentro de la operación de escritura. | Confiar en identificadores, estado, cálculos, actor, archivo o análisis enviados por el cliente. |
| Base de datos | FK, claves únicas, restricciones de valores e intervalos y escritura transaccional. | Sustituir el motivo, la política de acceso o la revisión documental. |

Las tres capas se complementan. Una petición fabricada debe recibir el mismo bloqueo que el formulario; desactivar JavaScript no puede permitir una escritura inválida.

## Contrato del Support

Mantener los contratos existentes. No intercambiar `valido`, `puede_continuar` y `puede_crear`: cada servicio conserva el significado que sus consumidores utilizan. Para una nueva regla, documentar como mínimo:

| Dato | Contenido |
|---|---|
| Identificador y versión | Regla estable y versión del conocimiento que la respalda. |
| Operación y contexto | Acción, actor, usuario objetivo, gestión y recurso; el servidor obtiene los datos sensibles. |
| Resultado | Bloqueo, advertencia o sugerencia; un resultado incierto no habilita una escritura sensible. |
| Campo y código | Campo que corregir y código estable que puede verificarse en pruebas. |
| Explicación | Causa concreta y siguiente acción posible, sin detalles privados o de infraestructura. |
| Respaldo | Modelo, restricción, norma o evidencia institucional aplicable. |
| Vigencia | Condiciones cuya modificación invalida el resultado, como rol, archivo, fechas o gestión. |

No persistir un simple `validado=true` para autorizar operaciones futuras. La vista previa caduca al cambiar datos relevantes y el servidor vuelve a consultar las condiciones actuales al guardar. Los atributos Livewire sensibles se bloquean para impedir su modificación directa desde el navegador.

## Campos: elegir primero, escribir cuando sea necesario

| Información | Control requerido | Comprobación independiente |
|---|---|---|
| Motivo | Selector institucional por operación, con «Otro». | Valor pertenece al catálogo; el servidor construye el texto del motivo elegido. «Otro» exige explicación válida. |
| Rol, permiso, tarea, responsabilidad | Catálogo real con búsqueda, selección y alcance visible. | Existencia, guard, estado, autorización y ámbito; no aceptar una opción inventada. |
| Gestión, curso, paralelo, turno, docente | Relaciones vigentes elegibles, sin texto libre de identificadores. | Pertenecen al contexto y se mantiene la misma relación al escribir. |
| Fecha y hora | Calendario y selectores institucionales con rango y zona horaria explícitos. | Fecha real, hora válida, límites de gestión e intervalo correcto. |
| Nombre nuevo | Texto limitado con formato del dominio y comprobación de duplicidad. | Normalización definida, caracteres permitidos, coherencia y unicidad real. |
| Importes, porcentajes, horas y notas | Entrada numérica con unidad, rango y precisión. | Tipo numérico, límites, escala y cálculo del servidor. |
| Observaciones necesarias | Texto acotado y pertinente. | Longitud, formato, escape al mostrar; no interpretar texto como código. |
| Respaldo | Carga privada con requisitos visibles y lectura controlada. | Tipo real, tamaño, legibilidad, contenido aplicable, huella y revisión cuando corresponda. |

«Otro» no crea un permiso, estado o catálogo nuevo. Los valores cerrados se amplían mediante un proceso autorizado. No sustituir el idioma o los componentes SAVP por selectores, calendarios o mensajes genéricos.

## Matriz de errores y solicitudes manipuladas

| Riesgo | Prevención y decisión requerida | Evidencia mínima para verificarlo |
|---|---|---|
| Campos vacíos, demasiado cortos o largos | Límites por dominio; impedir avanzar o guardar sin campos requeridos. | Vacío, límite inferior/superior y exceso. |
| Texto `SJKANMKJSANDJKNAKJN`, repeticiones o relleno | Advertir secuencias incoherentes y pedir palabras completas; preferir motivo y responsabilidades seleccionables. | Ejemplo del usuario, repeticiones y texto institucional válido. |
| Identificadores, estados o motivos inventados | Lista permitida, tipo estricto y consulta del catálogo en servidor. | Petición alterada aunque el selector no ofrezca el valor. |
| Arrays anidados, claves extra, booleanos y números usados como texto | Validar estructura y elementos antes de procesarlos; usar únicamente datos validados. | Cargas con tipos incorrectos; devolver validación, no error interno. |
| Duplicados con acentos, espacios o mayúsculas | Normalización propia del dominio y comprobación de duplicidad; índice único donde corresponda. | Variante equivalente y dos solicitudes simultáneas. |
| SQL, HTML o scripts dentro de texto | Consultas parametrizadas y salida escapada; listas permitidas para orden/columna; no usar `eval` ni HTML crudo. | Comillas, etiquetas y contenido que deba mostrarse como texto. |
| Otro usuario, curso, expediente o notificación | Derivar identidad desde la sesión; política sobre el recurso solicitado y consulta acotada por asignación. | Usuario A intenta leer o modificar recurso de B. |
| Escalar permisos o cambiar el actor propio | Actor institucional único, autoridad permanente y límites de delegación. | Autoconcesión, permiso crítico, global o superior al autorizador. |
| Revocar el último administrador válido | Protección institucional dentro de transacción, reconsultando cuentas y roles. | Operación que dejaría al sistema sin autoridad válida. |
| Editar por accidente | Consulta por defecto, botón Edición, selección pendiente y resumen antes de guardar. | Control deshabilitado fuera de edición y rechazo directo del servidor. |
| Rol retirado o cuenta desactivada durante la operación | Revalidar al escribir; bloquear filas relevantes en orden consistente. | Autoridad válida al abrir y retirada antes de guardar. |
| Reutilizar una autorización temporal | La concesión no autoriza otra concesión; permisos delegados proceden de autoridad permanente. | Usuario con acceso temporal intenta delegarlo. |
| Fechas inexistentes, pasado o fin anterior al inicio | Validación estricta, límites visibles y reloj del servidor. | Día imposible, hora 25, fin igual/anterior e inicio vencido. |
| Diferencia UTC/Bolivia | Mostrar zona, convertir de forma explícita y almacenar intervalos coherentes. | Activación y expiración en el minuto exacto indicado. |
| Acceso que sigue después del fin | Evaluar reloj en cada petición, independientemente de los avisos del scheduler. | Acceso justo antes y después de `fin`; conservar permisos permanentes. |
| Tarea incompatible con rol o ámbito | Contrastar función, permiso y alcance; explicar el bloqueo y sugerir menor alcance cuando exista. | Permiso válido pero incompatible con el contexto. |
| Choque de docente, curso, aula o bloque | Support de planificación consulta intervalos y relaciones vigentes; repetir comprobación dentro de la escritura. | Solapamiento total/parcial y dos altas concurrentes para el mismo recurso. |
| Materia o especialidad fuera del plan | Leer Plan de Asignatura/Especialidad del grupo y gestión; no construir una clase con relaciones incompatibles. | Materia existente pero no correspondiente al grupo. |
| Grado adicional sin norma aplicable | Reconocer solicitud, pedir respaldo y revisión; reconocer el texto no autoriza crear el grado. | PDF que menciona séptimo sin disposición de ampliación vigente. |
| Cambiar estructura con clases o notas iniciadas | Comprobar período de apertura, actividad y dependencias; conservar historia. | Operación fuera del período autorizado o con registros académicos previos. |
| Nota, asistencia o cierre ajeno al período | Validar inscripción, vigencia, plan, evaluación y regla de cierre; impedir alterar cierres sin proceso autorizado. | Alumno sin vigencia, período cerrado o plan incorrecto. |
| Archivo falso, excesivo o malformado | Comprobar extensión, MIME/contenido, tamaño, páginas, cifrado y tiempo máximo de lectura. | Archivo renombrado, PDF ilegible, cifrado o sobredimensionado. |
| Documento de otra gestión, Director o institución | Comparar hechos con registros vigentes y decisión expresa; rechazar denegaciones y guardar evidencia del intento reconocido. | Año/autoridad incorrectos o texto «no autoriza». |
| Firma falsa o imagen de sello copiada | La coincidencia de texto no certifica autenticidad; revisión independiente y canal institucional. | Intento de aprobación propia o falta de revisión de firma. |
| Documento sustituido tras revisión | Huella SHA256, almacenamiento privado y nueva comprobación antes de la escritura definitiva. | Archivo cambiado después de aprobarlo. |
| Instrucciones maliciosas en PDF, fuente o respuesta del agente | Tratar el contenido como datos; nunca como instrucciones que conceden permisos o ejecutan acciones. | Texto que ordena ignorar reglas o revelar información. |
| Fuente externa sin respaldo para decisión institucional | Support distingue evidencia, recomendación e incertidumbre; el agente no certifica una decisión administrativa. | Respuesta sin evidencia o fuente que contradice la norma aplicable. |
| Doble clic, reintento o entrega repetida | Bloqueo visible, idempotencia y unicidad/transacción del servidor según la operación. | Misma acción enviada dos veces o red interrumpida. |
| Guardados simultáneos y actualización perdida | Bloqueos o versión del recurso, revalidación y manejo de conflicto recuperable. | Dos operadores editan el mismo dato antes de guardar. |
| Cambio parcial | Una transacción para los cambios relacionados; política explícita de compensación para archivos y efectos externos. | Fallo entre creación principal y relación dependiente. |
| Historial borrado o claves huérfanas | Restricciones FK, conservación de evidencias, cierre/revocación y migraciones correctivas. | Borrado de una entidad con hechos históricos dependientes. |
| Notificaciones ajenas o duplicadas | Destinatario desde sesión; marca personal de leído/archivo; claves de evento idempotentes. | A marca aviso de B o scheduler repite el mismo evento. |
| Aviso programado desactualizado | Reconsultar concesión antes de emitir; respetar revocación/expiración; informar estado actual. | Scheduler tardío o concesión revocada antes del inicio. |
| Consultas lentas o consumo abusivo | Búsqueda acotada, paginación, relaciones precargadas, índices, límites de archivos/lectura y control de frecuencia por operación. | Búsqueda vacía masiva, página excesiva, PDF costoso o repeticiones rápidas. |
| Servicio, base, parser o agente no disponible | Conservar formulario; explicar causa recuperable; bloquear escrituras sin respaldo necesario. | Dependencia ausente o tiempo de espera agotado. |
| Error que filtra datos privados | Respuesta humana sin consultas SQL, rutas privadas, credenciales, identificadores ajenos ni trazas. | Excepción en entorno real y acceso al log restringido. |
| Exponer expedientes al LLM o a un tercero | Enviar solo contexto permitido y mínimo; prohibir identidad, PII y objetos institucionales completos en el tutor. | Inspección del contrato de salida y pruebas de ausencia de datos personales. |

Las propuestas sobre frecuencia, control de versión y concurrencia de otros módulos deben comprobarse en cada dominio; esta matriz es un requisito, no una declaración de implementación global.

## Experiencia preventiva

1. Al abrir: indicar qué se requiere; no mostrar una lista de errores como si el usuario ya hubiera fallado.
2. Al interactuar: validar el campo y mostrar la causa junto a él. Las advertencias deben distinguirse de los errores que bloquean.
3. Al elegir: ofrecer solo opciones elegibles; si una condición cambia, retirar la elegibilidad y explicar el cambio.
4. Al revisar: mostrar destinatarios, tareas, alcance, período y efecto exacto. Contar permisos únicos; separar catálogo, roles, cuentas y permisos propios.
5. Al confirmar: bloquear duplicados, mostrar espera y conservar datos. La operación requiere validación independiente del servidor.
6. Al fallar: conservar entradas recuperables, identificar qué corregir y no simular éxito. La bitácora de una escritura fallida no debe declarar un cambio realizado.
7. Al terminar: comunicar el resultado real y conservar la historia. Salir de edición al guardar correctamente.

Las lecturas, cambios de vista y filtros locales deben ser inmediatos. El estado de carga no sustituye investigar una consulta lenta. Móvil, tema oscuro, teclado, foco, etiquetas accesibles y reducción de movimiento son estados de verificación obligatorios.

## Roles: implementación de esta entrega

| Componente real | Cobertura |
|---|---|
| `SupportRolesInstitucionales` | Catálogos de motivos por operación, «Otro», responsabilidades y ámbito; detección de secuencias, repetición y formato de texto. Es una heurística explícita de calidad, no comprensión absoluta del lenguaje. |
| `InstitutionalRoleGovernance` | Nombres reservados/duplicados, coherencia de funciones y permisos, autoridad elevada y sugerencias de menor alcance. |
| `RolesPermisos` + `roles-institucionales.js` | Cuenta personal, consulta por defecto, edición explícita bloqueada en servidor, prevención inmediata, plegables, matriz y filtros separados por espacio. |
| `RolePermissionService` | Escritura protegida de permisos y actor, protección de autoridad institucional, bloqueo transaccional y bitácora del motivo. |
| `RoleRequestService` | Carta privada, gestión y Director, revisión independiente, huella, rechazo conservado y creación posterior autorizada. |
| `InstitutionalDocumentAnalyzer` | Coherencia del texto y denegaciones; requiere autenticación humana de la firma. |
| `AccesoProgramadoService` | Destinatarios concretos, permisos congelados, reloj, revocación, prevención de solapamientos y avisos personales. |
| `NotificationService` | Bandeja y estados por destinatario, enlaces autorizados y eventos idempotentes. |

Administración y Aula virtual son agrupaciones de presentación del catálogo existente. Separarlas no crea permisos ni reemplaza políticas. «Consulta compatible» identifica el puente de lecturas históricas; no es una concesión nueva. «Edición» describe una capacidad del permiso, pero el recurso y la operación siguen sujetos a su política.

La migración de gobernanza está preparada y aún no aplicada a la base oficial. Las funciones que necesitan esas tablas no están operativas allí hasta realizar el procedimiento autorizado. Las verificaciones SQLite no prueban restricciones PostgreSQL.

## Bitácora y privacidad

Registrar actor real, operación, recurso, gestión, motivo, resultado y cambios pertinentes. Para un documento rechazado reconocido: conservar huella, ubicación privada, razones y estado; no copiar todo el contenido del PDF al log. Para una revocación: mantener concesión, destinatarios, intervalo y motivo.

Los errores de escritura y análisis sensible necesitan auditoría; los errores de tecleo ordinarios no deben producir millones de eventos ni conservar texto innecesario. La bitácora no es una bandeja de notificaciones y no debe convertirse en un registro indiscriminado de información privada.

## Criterios para declarar una regla terminada

- Regla y respaldo identificados, con ejemplos válidos e inválidos del dominio.
- Prevención inmediata sin depender de una petición lenta para cada tecla.
- Servidor bloquea la petición manipulada con JavaScript desactivado y tipo/valor alterado.
- Autorización verificada sobre la persona y recurso correctos, incluida pérdida de acceso entre apertura y guardado.
- Casos límite, duplicidad, reintentos y concurrencia relevantes comprobados.
- Fallo de dependencia no produce escritura parcial ni éxito simulado.
- Persistencia y auditoría correctas; no se exponen registros ajenos ni secretos.
- Estado vacío, error, carga, teclado, móvil, oscuro y movimiento reducido revisados cuando afectan la pantalla.
- Evidencia distingue prueba aislada, inspección, QA visual y comprobación de operación real.

No desactivar una regla para que una prueba pase ni ampliar autorización para resolver un error de presentación. Si aparece una variante del mismo defecto, buscar sus otros consumidores y añadir una regresión pertinente.

## Verificación y mantenimiento

Terminal: PowerShell. Directorio: `C:\laragon\www\savp-reestructuracion`.

```powershell
& 'C:/laragon/bin/php/php-8.4.13-nts-Win32-vs17-x64/php.exe' -d extension=pdo_sqlite -d extension=sqlite3 vendor/phpunit/phpunit/phpunit --filter 'SupportRolesInstitucionalesTest|GobernanzaAccesosProgramadosTest|CartaRolInstitucionalTest|InstitutionalRoleGovernanceTest|RoleRequestAuthorizationTest|RoleDashboardResolverTest'
npm.cmd run build
& 'C:/laragon/bin/php/php-8.4.13-nts-Win32-vs17-x64/php.exe' artisan view:cache
```

Las pruebas de escritura usan SQLite `:memory:` con guard de destino. PostgreSQL institucional se conserva; no ejecutar reconstrucciones o seeders masivos. Para restricciones PostgreSQL nuevas: respaldo verificable, ensayo aislado de migración/rollback y autorización concreta antes de aplicarlas.

Cada nueva pantalla incorpora únicamente las reglas que le corresponden y mantiene un registro de cobertura: `regla → interfaz → servicio → restricción → prueba → estado`. Actualizar este documento cuando cambia una regla o su cobertura; no usar «el Support lo previno» como evidencia sin comprobar el caso.

## Asignación personal de permisos

`PermisosPersonalesService` valida autoridad permanente, destinatario vigente, catálogo real, motivo y huella del borrador. No cambia roles ni actor; protege accesos críticos y globales, no permite autoconcesión ni duplica permisos heredados. La interfaz comienza en consulta, habilita Edición de forma explícita, muestra cantidades por agregar/retirar y bloquea Guardar ante un motivo incompleto o incoherente. Los permisos directos, la bitácora y el aviso exclusivo al destinatario se guardan en una transacción. Las pruebas aisladas verifican también que un fallo del aviso revierta los permisos y la bitácora. No se usaron cuentas institucionales para probar escrituras.


### Delegación diaria de Cursos

La excepción `cursos.gestionar.global` se concede temporalmente a Docentes por un máximo de 24 horas. No transforma el actor ni permite administrar Usuarios o Roles. `AccesoProgramadoService` vuelve a comprobar autoridad permanente del emisor, destinatario, duración, gestión, solapamiento y aviso privado; `AccesoGestionCursos` y el hook de Livewire reevalúan la vigencia antes de cada operación. La ausencia de las tablas de vigencias bloquea Guardar, sin recurrir a un permiso permanente. Las pruebas aisladas comprueban el corte exacto a las 24 horas y la imposibilidad de continuar guardando después del vencimiento.
