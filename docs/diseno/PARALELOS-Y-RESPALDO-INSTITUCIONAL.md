# Paralelos y respaldo institucional

Revisión: 4 de octubre de 2026. Contexto: Bolivia, SAVP de Franz Tamayo N°3. No se aplica el MINEDU de otro país.

## Qué documento corresponde

No se identificó en las fuentes consultadas un formulario nacional llamado «comunicado para agregar un grado». Una solicitud enviada no es la aprobación. Se debe distinguir entre registrar una letra en el catálogo, abrir un grupo de un año de escolaridad y ampliar un nivel educativo.

La [RM 0001/2026, Educación Regular](https://www.minedu.gob.bo/files/documentos-normativos/resoluciones-ministeriales/1_RM_0001_EDUCACIN_REGULAR.pdf), artículo 21, regula la apertura durante inscripción según infraestructura y demanda: mínimo general de 21 estudiantes, informe del director con visto bueno distrital y flexibilización documentada del supuesto del parágrafo II. El parágrafo III limita la creación a tres paralelos por año de escolaridad; el IV exige, para fiscales y convenio, SICH actualizado, informe técnico distrital y resolución administrativa departamental. El artículo 23 establece hasta 35 estudiantes para secundaria urbana; el contexto rural tiene reglas distintas. No se transforma una capacidad registrada en prueba de autorización.

Para ampliación de niveles de una unidad **fiscal**, la [RM 0020/2024 y su reglamento](https://www.minedu.gob.bo/files/documentos-normativos/VER/2024/03643981.pdf), artículos 6, 8 y 10, exige el procedimiento institucional: solicitud justificada del director ante el distrito, informe técnico de demanda, presupuesto e infraestructura, RUE FORM 002 generado en el portal oficial, verificación de Educación Regular, actualización de infraestructura en SIE y certificación municipal de ambientes; el supuesto modular requiere su informe específico. La resolución y el certificado RUE respaldan el funcionamiento. Este reglamento no debe aplicarse por analogía a privados o convenio. La dependencia y el alcance se verifican con el expediente institucional.

No se fabrica un FORM 002 ni una resolución con membrete oficial desde SAVP. El trámite sigue el conducto distrital y departamental correspondiente. Antes de aplicar una gestión posterior a 2026 hay que revisar su normativa anual.

## Comportamiento implementado

- Creación en tres fases: nombre reconocido, motivo y PDF, y verificación final con la autoridad. Ninguna fase intermedia crea el paralelo. La guía distingue membrete, resolución, institución, gestión, parte resolutiva y firma verificable; un logotipo no prueba autenticidad.
- Si un PDF legible no supera las reglas de contenido, se conserva su archivo privado, hash, discrepancias e intento en bitácora. Repetir la misma revisión no duplica el registro. Una lectura fallida sin texto reconocible muestra el error; no se confunde con una resolución rechazada. Solo la confirmación final guarda el catálogo y su expediente aceptado.
- Filtros combinados por grado, turno, estado, uso, capacidad y documentación. La matriz de grupos y los anillos de ocupación presentan ámbitos explícitos. Los nombres desconocidos y las justificaciones formadas por una cadena de letras o repeticiones se bloquean; esta comprobación no certifica la veracidad del motivo.
- `/admin/gestion-paralelos`: catálogo separado del mapa de grupos por grado y turno. Inscripciones activas de la gestión vigente, miembros por vigencia regular/técnica, planes activos y horarios actualmente vigentes. El técnico no duplica el total anual de personas; los miembros de distintos grupos no se suman como personas nuevas.
- Ficha: capacidad, miembros, planes por grupo y última actuación documental cuando existe. Una actuación antigua sin expediente no se presenta como verificada.
- Crear/editar/cambiar estado: permiso `Paralelos` comprobado en servidor, motivo, justificación, gestión solicitada, número, fecha, emisor, PDF legible y referencia de comprobación con la autoridad. Se vuelve a leer el PDF al guardar. Se archiva en disco privado `local`, con hash y metadatos en bitácora, dentro del flujo transaccional; un fallo de archivo o bitácora revierte la operación.
- Registrar catálogo no crea grupos ni traslada estudiantes. Un expediente para el próximo año deja el catálogo inactivo; no se activa ni replica automáticamente. La identidad compartida no admite variantes por año: una corrección menor afecta sus referencias históricas y se advierte; cambiar la identidad de un paralelo utilizado se bloquea. No se desactiva un catálogo con grupos activos o uso vigente.
- Cursos: exige también motivo clasificado, emisor y referencia de verificación. La lectura exige resolución administrativa y parte resolutiva; una norma general o una denegación explícita no sirve. El contexto incluye grupos y turno, y la revisión se repite en servidor.

## Límites de Support Inteligente

Las coincidencias del texto detectan omisiones y discrepancias, no certifican firmas, autenticidad ni legalidad integral. Los PDF escaneados sin texto requieren una copia legible; no hay OCR en este flujo. No se envían documentos al Ministerio ni se altera SIE/SICH.

La alerta de capacidad usa `cap_gac`. La diferencia de diez estudiantes entre grupos del mismo grado y turno es una señal interna de revisión, no una prohibición normativa. El aviso de más de tres letras se limita a la norma 2026; conserva grupos heredados y pide revisar su expediente, sin ejecutar fusiones.

## Verificación reproducible

Desde `C:\laragon\www\savp-reestructuracion`, PowerShell:

```powershell
php scripts/medir_rendimiento_paralelos.php
php -d extension=pdo_sqlite -d extension=sqlite3 vendor/phpunit/phpunit/phpunit tests/Unit/ExpedienteParaleloInstitucionalTest.php tests/Feature/ConsultaParalelosInstitucionalesTest.php tests/Feature/ProteccionExpedienteParaleloTest.php
npm.cmd run build
```

El medidor abre una transacción PostgreSQL de solo lectura y revierte al terminar. Las pruebas de datos verifican explícitamente SQLite `:memory:` antes de crear tablas mínimas. No ejecutan migraciones ni seeders institucionales. La persistencia con un expediente institucional real queda a cargo del operador autorizado; no se registran cambios de prueba en la base oficial.

## Autollenado documental y ejemplos

El botón «Leer PDF y completar» requiere solo el PDF y el paralelo elegido. Detecta referencias explícitas de resolución administrativa, fecha única válida, gestión y autoridad departamental. Si faltan datos, existen varias referencias o se detecta una denegación, conserva los valores del formulario y registra el rechazo leído con sus discrepancias en bitácora. Si cumple, sobrescribe los cinco campos documentales, sin guardar catálogo ni inventar motivo o comprobación humana. La fecha no puede ser futura y la gestión debe ser la vigente o próxima; la próxima deja el catálogo inactivo.

Los ejemplos de formato son ayudas visibles y placeholders, nunca valores precargados ni evidencia. La validación al cambiar cada campo es independiente de la revisión y se repite al guardar. PDF sin texto, protegido o ilegible continúa rechazado por el lector; no se presenta como contenido verificado.

## Separación entre grados y clases

Registrar grado modifica únicamente el catálogo `curso` y su respaldo en bitácora. No exige ni crea grupos por paralelo o turno. La organización de grupos y la asignación de materia/docente son operaciones distintas. Agregar clase requiere un bloque del horario seleccionado; el servidor impide preparar o guardar clases mientras está abierto el formulario del grado. La interfaz cierra estados anteriores y no renderiza el modal de clase sin contexto de horario.
