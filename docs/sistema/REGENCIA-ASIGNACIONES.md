# Asignaciones de Regencia

Ruta: `/admin/asignaciones-regencia`. Solo Administración con permiso `regencia.asignaciones.gestionar` registra o retira responsabilidades.

## Designación confirmada

En la gestión 2026, desde el 02/02/2026 hasta el 02/12/2026: Virginia Huañapaco Aruquipa tiene primero y segundo; Fanny Uriarte Gutierrez, tercero y cuarto; Sergio Valencia Molina, quinto y sexto. Se reutilizaron sus cuentas y registros de personal. Se agregaron el actor Regente, el cargo, los vínculos y seis asignaciones mediante los servicios existentes, con bitácora. La fuente es la confirmación del usuario; no se recibió número de documento ni se certificó una orden firmada.

El respaldo previo está en `storage/app/private/respaldos/regencia-20261005-045555.dump`. Se verificó el inventario del archivo; no se probó su restauración.

## Operación

La pantalla permite consultar gestión, regente, grado y situación; cambiar entre tarjetas y lista; observar grados, docentes y conexiones. Los docentes provienen de planes de asignatura y especialidad vigentes y se deduplican por regente. Un docente puede trabajar con varios regentes, por lo que sus subtotales no se suman para obtener el total institucional.

Cada asignación necesita vínculo y cuenta activos, gestión activa, grado activo, fechas contenidas en la gestión y en el vínculo, y motivo comprensible. Se bloquean duplicados y más de dos grados simultáneos. La comprobación se repite en el servidor. El análisis del motivo es una prevención heurística, no una certificación documental.

## Rotación anual

Los pares 1–2 avanzan a 3–4, 3–4 a 5–6 y 5–6 a 1–2. Una designación existente para el regente en la nueva gestión, incluso retirada, impide aplicar la rotación automáticamente. Una distribución irregular o grados ocupados tampoco se infieren. No se cambia la historia anterior.

En PowerShell, desde `C:\laragon\www\savp-reestructuracion`, `php artisan regencia:rotar` no escribe. `php artisan regencia:rotar --aplicar` ejecuta la regla autorizada dentro de la gestión activa y con una autoridad administrativa única habilitada. Cada nueva asignación queda en bitácora y explica su origen automático.

La tarea está declarada diariamente a las 00:10, zona America/La_Paz. Requiere que el planificador de Laravel esté funcionando; declarar la tarea no instala ni confirma el servicio del sistema operativo. Las propuestas de 2027 se muestran antes de crear sus asignaciones.

## Verificación

Pruebas focalizadas: `php -d extension=pdo_sqlite -d extension=sqlite3 vendor/phpunit/phpunit/phpunit tests/Unit/RegenciaPrevencionTest.php tests/Unit/RegencyReportScopeTest.php tests/Unit/AcademicSecurityTest.php`.

El script `scripts/academico/registrar_regencia_confirmada.php` consulta por defecto. Su opción `--aplicar` está limitada a las identidades y distribución confirmadas para 2026 y evita sobrescribir registros diferentes.
