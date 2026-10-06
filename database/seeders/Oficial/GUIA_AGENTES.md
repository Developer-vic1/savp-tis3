# GUÍA DE MIGRACIONES Y SEEDERS PARA AGENTES

> **Política vigente desde el 04/10/2026:** `SAVPTIS3-OFICIAL` se conserva. Los apartados inferiores con `migrate:fresh` y carga masiva registran la creación inicial de la base; **no son comandos para repetir sobre la base oficial**. Consulta [operación actual](../../../docs/sistema/03-DATOS-Y-OPERACION.md) y el [índice de documentación](../../../docs/sistema/00-INDICE.md). Los conteos aquí son instantáneas fechadas.

Verificado el **04/10/2026**, zona horaria **America/La_Paz**, en `C:\laragon\www\savp-reestructuracion`, rama `Fusion_Sistema`.

## Resultado confirmado

La instantánea de solo lectura del **04/10/2026 a las 16:26:56 -04:00** confirma que `SAVPTIS3-OFICIAL`, PostgreSQL `127.0.0.1:5432`, tiene **104 tablas y 7.801.976 filas**, incluidas **108 filas de `migrations`**. El cierre original de seeders tenía **7.801.820 filas y 107 migraciones**. La diferencia de 156 filas corresponde a 62 permisos, 93 relaciones de permisos autorizadas y una migración correctiva; los conteos académicos se conservan. Las filas de diferentes entidades no representan personas distintas.

El conteo se obtuvo con `COUNT(*)` de las 104 tablas, dentro de una transacción de solo lectura y una misma instantánea. La evidencia actual y su comparación con el cierre están en [CONTEO_ACTUAL_2026_10_04.json](CONTEO_ACTUAL_2026_10_04.json), que registra fecha, conexión y desglose por tabla. No se volvió a vaciar la base institucional para realizar esta comprobación.

| Entidad | Filas |
|---|---:|
| Estudiantes distintos, incluyendo egresados e inactivos | 612 |
| Personas: personal, estudiantes y responsables | 1.280 |
| Usuarios: 56 oficiales y 612 estudiantiles | 668 |
| Docentes oficiales | 48 |
| Personal institucional oficial, incluyendo docentes | 56 |
| Inscripciones anuales | 2.144 |
| Tareas | 118.735 |
| Entregas de tareas | 1.313.319 |
| Calificaciones de tareas | 1.235.470 |
| Calificaciones oficiales trimestrales | 64.856 |
| Registros de asistencia por clase/sesión | 2.365.208 |
| Resultados/cierres anuales | 1.846 |
| Respuestas de orientación, incluyendo ORAV histórico | 28.290 |
| Resultados de orientación, incluyendo ORAV histórico | 943 |

Los 48 docentes forman parte de los 56 integrantes de personal; no sumar ambos como personas independientes. Las 2.144 inscripciones incluyen reinscripciones de los mismos estudiantes en distintas gestiones.

Los 943 resultados se distinguen por instrumento: **785 ORAV históricos** y **158 O*NET Mini-IP (`onet-mini-ip-2.0-es`)**. El reporte RIASEC vigente utiliza estos últimos 158 resultados registrados; no infiere intereses desde notas ni mezcla ORAV con el instrumento vigente.

| Gestión | Nuevos | Inscritos | Tareas | Entregas | Notas de tareas | Notas trimestrales | Asistencias | Cierres anuales |
|---|---:|---:|---:|---:|---:|---:|---:|---:|
| 2020 | 312 | 312 | 1.388 | 15.548 | 0 | 0 | 57.161 | 312 |
| 2021 | 50 | 310 | 20.633 | 233.265 | 222.337 | 11.607 | 411.203 | 310 |
| 2022 | 50 | 308 | 20.671 | 229.526 | 218.685 | 11.506 | 399.241 | 308 |
| 2023 | 50 | 306 | 20.903 | 228.500 | 217.461 | 11.432 | 413.082 | 306 |
| 2024 | 50 | 305 | 20.578 | 228.322 | 217.157 | 11.424 | 409.495 | 305 |
| 2025 | 50 | 305 | 20.963 | 229.104 | 217.990 | 11.439 | 409.100 | 305 |
| 2026 | 50 | 298 | 13.599 | 149.054 | 141.840 | 7.448 | 265.926 | 0 |

El desglose por cada tabla, gestión y estado está en [CONTEOS_VERIFICADOS.md](CONTEOS_VERIFICADOS.md). Los conteos son una referencia del dataset, no cantidades que deban forzarse si el usuario autoriza modificar su alcance.

## Reconstrucción histórica en base inicialmente vacía (no repetir en la oficial)

Terminal **PowerShell**, desde el proyecto:

```powershell
cd C:\laragon\www\savp-reestructuracion
php artisan migrate:fresh
php artisan db:seed
```

1. `migrate:fresh` elimina las tablas de la conexión configurada y crea las 104 tablas. **No carga estudiantes ni ejecuta seeders** cuando no se añade `--seed`.
2. `db:seed` ejecuta `DatabaseSeeder`, que carga automáticamente el historial completo 2020–2026 y configura la cuenta administrativa. No requiere `--class`, `--path`, ejecutar carpetas por separado ni llamadas manuales a los seeders antiguos.

La alternativa equivalente es `php artisan migrate:fresh --seed`.

**Ese procedimiento ya se ejecutó para crear `SAVPTIS3-OFICIAL`.** El historial tiene una protección deliberada: ejecutar `db:seed` sobre una base que ya tiene estudiantes se detiene, sin eliminarlos ni duplicarlos. No quitar esta protección para ocultar un problema. En adelante, solo usar reconstrucción sobre una base de prueba aislada.

En la configuración comprobada `APP_ENV=local`, por lo que los dos comandos anteriores no necesitan `--force`. Laravel puede pedir confirmación en producción; no cambiar el entorno para eludir esa protección. Si se cambia `.env` y existe configuración cacheada, ejecutar antes `php artisan config:clear` y verificar la conexión efectiva.

## Requisitos para que la ejecución sea válida

- PostgreSQL disponible y `.env` apuntando a la base realmente autorizada. Comprobar también la configuración efectiva: no asumir que `.env` prevalece sobre una caché o `DB_URL`.
- PHP compatible con el proyecto, extensiones `pdo_pgsql`, `mbstring` y `zip`, y dependencias Composer instaladas. El runtime comprobado fue PHP 8.4.13 de Laragon, con límite de memoria de 512 MB.
- Mantener el registro de las tres carpetas en `AppServiceProvider::boot()` mediante `loadMigrationsFrom`. `migrate:fresh` y `migrate:status` deben descubrirlas sin opciones `--path`.
- Conservar las siete migraciones correctivas independientes del 04/10/2026. No omitirlas ni reemplazarlas por SQL manual: el historial requiere aulas PAS/PES, secuencia de auditoría, trayectos paralelos, membresías temporales, fecha académica, cabecera técnica y protección de hechos históricos.
- Conservar también `database/migrations/0000_01_01_000001_preparar_recreacion_funciones_oficiales.php`, que permite repetir `migrate:fresh` sin colisiones con funciones residuales de PostgreSQL. No modifica reglas académicas ni añade tablas o columnas; actúa únicamente cuando no hay tablas públicas salvo `migrations`.
- Conservar nueve fuentes `REALES/FUENTES/*.local.php`, cuyo SHA-256 verifica `REALES/MANIFIESTO_FUENTES.json`.
- Conservar `storage/app/private/integracion/PERSONAL_OFICIAL.json`, cuyo SHA-256 verifica `Fuentes/personal_oficial_manifest.json`. Incluye la nómina oficial y sus cuentas; no fabricar docentes cuando falte.
- Conservar `Documentos/ACTIVIDAD_2_CPM_PERT.pdf`. Se adjunta a los hechos previstos sin leer ni reinterpretar su contenido.

Las nueve fuentes locales y la nómina privada están excluidas de Git. Un clone solo del commit de seeders no contiene esas fuentes. Además, al verificar esta guía las siete migraciones correctivas y la preparación adicional de reconstrucción siguen en el checkout sin formar parte de aquel commit, según la separación solicitada por el usuario. Para trasladar el sistema a otro equipo se necesita el conjunto de código revisado y las fuentes privadas autorizadas; no afirmar que un clone incompleto pueda reconstruirlo sin errores.

No ejecutar comandos destructivos en una base con datos del operador solo para comprobar este documento. Las comprobaciones adicionales de reconstrucción se hacen en PostgreSQL aislado.

## Organización y punto único de entrada

| Ubicación | Función |
|---|---|
| `database/seeders/DatabaseSeeder.php` | Entrada de `php artisan db:seed`; llama al historial y al administrador, y actualiza el conteo final. |
| `Oficial/HistorialInstitucionalSeeder.php` | Coordina las siete gestiones, bloquea cargas simultáneas y valida la integridad. |
| `Oficial/2020` … `Oficial/2026` | Seis fases por gestión: Gestion, Inscripciones, AulaVirtual, Asistencias, Orientacion y Cierre. |
| `Oficial/Soporte` | Generación del historial, códigos, consultas y exportaciones. |
| `Oficial/REALES` | 25 seeders originales preservados y sus fuentes; no invocarlos indiscriminadamente. |
| `Oficial/REALES/ADMINISTRADOR/AdministradorSistemaSeeder.php` | Adaptador canónico del administrador, reutilizando su persona y cuenta. |
| `Oficial/Fuentes` | Instrumentos y catálogos existentes, además del manifiesto del personal. |

Los archivos de las carpetas numéricas son closures cargadas explícitamente; no convertir `2020` en un namespace PSR-4. Mantener el orden anual y de fases, porque las relaciones dependen de las fases anteriores.

| Dominio | Migraciones originales | Correctivas | Archivos de migración actuales | Modelos de dominio |
|---|---:|---:|---:|---:|
| Academico | 51 | 5 | 56 | 51 |
| AulaVirtual | 22 | 2 | 24 | 22 |
| AporteAcademicoVocacional | 16 | 0 | 16 | 16 |

Hay además 12 migraciones en la raíz: **108 archivos actuales no significan 108 tablas**. La carga institucional registrada inicialmente ejecutó 107 archivos. La preparación adicional descubierta al probar una segunda reconstrucción eleva a 108 el registro de una futura instalación; no se aplicó a la base institucional durante esta revisión. Las correctivas alteran restricciones, funciones o columnas existentes; los archivos del framework pueden crear varias tablas. La cantidad física confirmada sigue siendo 104. No crear tablas adicionales para igualar el número de archivos.

## Por qué una migración puede crear varias tablas

`php artisan migrate:status` muestra **archivos de migración**, no una lista de tablas. El nombre del archivo describe su propósito principal; su método `up()` puede crear varias tablas, modificar una existente o preparar funciones sin crear ninguna tabla.

Estos son los **cuatro archivos activos que crean más de una tabla**:

| Archivo en `database/migrations/` | Tablas creadas por su `up()` | Cantidad |
|---|---|---:|
| `0001_01_01_000001_create_users_table.php` | `users`, `password_reset_tokens`, `sessions`, `user_status_logs` | 4 |
| `0001_01_01_000001_create_cache_table.php` | `cache`, `cache_locks` | 2 |
| `0001_01_01_000002_create_jobs_table.php` | `jobs`, `job_batches`, `failed_jobs` | 3 |
| `2026_04_09_025343_create_permission_tables.php` | `permissions`, `roles`, `model_has_permissions`, `model_has_roles`, `role_has_permissions` | 5 |
| **Total creado por estos cuatro archivos** | **14 tablas distintas** | **14** |

Los nombres de las cinco tablas Spatie proceden de `config/permission.php`; los de esta guía coinciden con la configuración del proyecto y el inventario PostgreSQL registrado.

| Tabla | Qué representa |
|---|---|
| `users` | Cuentas de acceso vinculadas a personas. |
| `password_reset_tokens` | Tokens del proceso de recuperación de contraseña. |
| `sessions` | Sesiones de acceso almacenadas en base de datos. |
| `user_status_logs` | Historial de cambios de estado de las cuentas. |
| `cache` | Valores temporales del caché de Laravel. |
| `cache_locks` | Bloqueos del caché para coordinar operaciones. |
| `jobs` | Trabajos pendientes en la cola de Laravel. |
| `job_batches` | Agrupaciones de trabajos y su progreso. |
| `failed_jobs` | Trabajos de cola que fallaron y su diagnóstico. |
| `permissions` | Catálogo de permisos. |
| `roles` | Catálogo de roles. |
| `model_has_permissions` | Permisos asignados directamente a los modelos autorizables. |
| `model_has_roles` | Roles asignados a los modelos autorizables. |
| `role_has_permissions` | Permisos que componen cada rol. |

### Caso compartido: `user_status_logs` se cuenta una sola vez

El archivo `create_users_table.php` crea inicialmente `user_status_logs`. Más adelante, `Academico/2026_10_03_000072_crear_user_status_logs_oficial.php` llama a `InstalacionCanonica::crear()` para adaptar **esa misma tabla** al contrato institucional. El helper comprueba que existe y que está vacía antes de transformar su estructura; no crea otra tabla con el mismo nombre ni duplica el historial. Si contiene registros, se detiene y requiere una transición aprobada.

Por eso `user_status_logs` aparece tanto en el archivo de usuarios como en las 51 entradas académicas, pero físicamente existe **una sola tabla**.

### Cómo se obtienen exactamente las 104 tablas

| Conjunto | Tablas físicas distintas |
|---|---:|
| Dominio académico, incluyendo `persona` y `user_status_logs` | 51 |
| Aula virtual | 22 |
| Aporte académico vocacional | 16 |
| Tablas adicionales de Laravel, Sanctum y Spatie, excluyendo `user_status_logs` ya contado | 14 |
| `migrations`, creada automáticamente por el repositorio de migraciones de Laravel | 1 |
| **Total** | **104** |

Las 14 tablas adicionales son `users`, `password_reset_tokens`, `sessions`, `cache`, `cache_locks`, `jobs`, `job_batches`, `failed_jobs`, `personal_access_tokens`, `permissions`, `roles`, `model_has_permissions`, `model_has_roles` y `role_has_permissions`.

El cálculo también puede expresarse como **89 tablas de dominio + 15 tablas creadas por los archivos de soporte − 1 tabla compartida (`user_status_logs`) + 1 tabla técnica (`migrations`) = 104**. Sanctum crea `personal_access_tokens` en su propio archivo de una sola tabla; `migrations` no tiene un archivo `create_migrations_table.php` en este proyecto.

El [INVENTARIO_104_TABLAS.md](INVENTARIO_104_TABLAS.md) enumera las **104 tablas distintas**, con su dominio y el archivo que las crea o adapta. La comparación se realiza contra el conteo PostgreSQL guardado, sin ejecutar migraciones ni modificar datos.

### Archivos que no agregan tablas al total

| Migración o grupo | Operación |
|---|---|
| `0000_01_01_000000_preparar_esquema_oficial.php` | Prepara la instalación canónica; no crea una tabla académica adicional. |
| `0000_01_01_000001_preparar_recreacion_funciones_oficiales.php` | Prepara la recreación de funciones residuales cuando el esquema está vacío; no crea tablas. |
| `2026_04_09_025251_add_two_factor_columns_to_users_table.php` | Agrega campos de doble factor a `users`. |
| `2026_06_02_022909_add_google_auth_fields_to_users_table.php` | Agrega campos de autenticación a `users`. |
| `2026_06_20_120000_normalize_google_auth_provider_default.php` | Ajusta el valor predeterminado de `users.auth_provider` y normaliza valores nulos. |
| `2026_10_01_000001_extend_orientation_peter3_snapshots.php` | En PostgreSQL no ejecuta cambios; conserva compatibilidad auxiliar SQLite. |
| `2026_10_03_999999_vincular_integridad_oficial.php` | Vincula la integridad del esquema mediante restricciones y funciones; no agrega tablas. |
| Las siete correctivas del 04/10/2026 | Modifican columnas, restricciones, secuencia o funciones/triggers de tablas existentes; no agregan tablas. |

`Oficial/Canonico/*/estructura.php` contiene las definiciones SQL que consumen las entradas de dominio: no es un conjunto adicional de migraciones para volver a ejecutar. La carpeta `Legado` tampoco forma parte de las tres rutas registradas para la instalación PostgreSQL oficial. No sumar sus archivos al inventario activo ni ejecutarlos para intentar alcanzar 104.

Un estado `Pending` significa que ese **archivo** aún no fue registrado como ejecutado. Si corresponde a la preparación de funciones, no significa que falten tablas. `migrate:status` y el inventario físico responden preguntas distintas.

## Administrador y fuentes oficiales

El administrador oficial ya se importa desde la fuente como **`PER_0001` / `USU_0001`**, antes de las cuentas estudiantiles. El adaptador administrativo posterior conserva esas claves y aplica los datos confirmados por el titular. No crear otro administrador porque pgAdmin muestre cero usuarios mientras la carga está sin confirmar.

La cuenta tiene el rol existente `Administrador`, con 35 permisos heredados comprobados; no crear permisos ni modificar la matriz por deducción. No invocar el `AdministradorOficialSeeder` antiguo como adaptador del esquema canónico: ese archivo se conserva como fuente histórica y contiene referencias al modelo antiguo.

Mantener docentes, personal, materias, especialidades, planes, turnos, plantillas y horarios oficiales. La actualización autorizada de los datos del administrador está documentada separadamente. No renumerar los códigos originales para uniformar su longitud; los códigos nuevos siguen los formatos del proyecto y el relleno mínimo aprobado, que puede crecer por encima de seis dígitos.

## Invariantes que los agentes deben conservar

- Los 612 estudiantes pertenecen a un dataset sintético autorizado para estudio; no presentar su historial como documentación institucional real. Antony conserva su nombre, RUDE y 22 notas aportadas; sus otros datos completados son sintéticos documentados.
- 2020 conserva la clausura pandémica y promoción excepcional: sin notas oficiales ni calificaciones de tareas inventadas.
- 2026 tiene notas y actividad hasta el segundo trimestre; no cargar notas del tercero ni cierres anuales.
- Una inscripción anual admite un trayecto regular y hasta uno técnico simultáneo. El alumno de mañana con formación técnica complementaria de tarde continúa siendo la misma persona, estudiante e inscripción.
- PAS valida el trayecto regular; PES valida la especialidad exacta. No basta coincidir en grupo.
- Las calificaciones usan `fea_cal`, fecha académica efectiva. No reemplazarla por `created_at` ni reinterpretar notas previas cuando cambie la especialidad.
- Las membresías LMS y vigencias conservan intervalos históricos. No reabrir o sobrescribir filas anteriores ni acortar intervalos dejando hechos huérfanos.
- La asistencia sintética usa detalles horarios reales. Hay cero detalles PES en la fuente: se conservan cero asistencias técnicas; no inventar bloques técnicos. Las aulas técnicas sí tienen membresías, tareas y notas compatibles.
- No crear participantes técnicos externos sin fuente oficial ni una inscripción definida. Las procedencias de traslados existentes no autorizan inventar ese caso.
- No fabricar capturas, evidencias universitarias ni recomendaciones de carrera sin su contrato y evidencia. Los ceros correspondientes y los estados técnicos vacíos tienen significado; no son una licencia para llenar todas las tablas arbitrariamente.
- Conservar PK, FK, UNIQUE, CHECK, EXCLUDE, índices y triggers. No deshabilitarlos para acelerar o hacer pasar los seeders.
- Conservar la convención de atributos y el modelo normalizado aprobado. Una operación de seeding no autoriza cambiar tablas, relaciones o catálogos.

## Duración, visibilidad y recuperación

La carga integral comprobada en la base autorizada tardó aproximadamente **70 minutos**. El seeder muestra cada gestión y fase. El volumen y los triggers explican fases largas; no reiniciar la carga al ver una fase prolongada.

Los hechos del historial se cargan en una transacción. En otra conexión, pgAdmin muestra las tablas pero no los registros hasta el **COMMIT** final. Una segunda inserción del mismo administrador puede quedar esperando esa transacción; no ejecutar dos cargas concurrentes ni cancelar procesos de otro agente sin autorización.

Si hay una excepción, se revierte el historial. Los archivos privados generados durante un intento fallido no prueban por sí solos que los datos estén confirmados. Consultar la salida final del comando, la conexión efectiva y los conteos SQL.

Si faltan fuentes, migraciones o permisos, corregir esa causa. No fabricar registros, borrar el guard de estudiantes existentes ni transformar la excepción en éxito aparente. La base institucional poblada se conserva; probar reconstrucciones en una base aislada.

## Verificación y archivos de evidencia

Tras los dos comandos, `php artisan migrate:status` debe mostrar las migraciones ejecutadas. En pgAdmin se pueden comprobar cantidades sin modificar datos:

```sql
SELECT current_database();
SELECT count(*) AS tablas FROM pg_tables WHERE schemaname = 'public';
SELECT count(*) AS estudiantes FROM estudiante;
SELECT count(*) AS usuarios FROM users;
SELECT g.ani_gea AS gestion, count(i.cod_ins) AS inscritos
FROM gestion_academica g
LEFT JOIN inscripcion_estudiante i ON i.cod_gea = g.cod_gea
GROUP BY g.ani_gea
ORDER BY g.ani_gea;
```

Las pruebas realizadas se distinguen de una garantía sobre fuentes ausentes o código modificado posteriormente:

- [EJECUCION_SAVPTIS3_OFICIAL.json](EJECUCION_SAVPTIS3_OFICIAL.json): creación y carga completas, salida 0, conexión autorizada; administrador comprobado sin duplicar.
- [VALIDACION_POSTGRESQL_AISLADO.json](VALIDACION_POSTGRESQL_AISLADO.json): ejecución integral anterior en PostgreSQL aislado.
- [VALIDACION_MIGRATE_FRESH_SIN_SEED.json](VALIDACION_MIGRATE_FRESH_SIN_SEED.json): reconstrucción con `migrate:fresh` sin `--seed`, probada repetidamente en una base aislada; 104 tablas y cero estudiantes/usuarios.
- [VALIDACION_PREPARACION_RECONSTRUCCION.json](VALIDACION_PREPARACION_RECONSTRUCCION.json): cinco casos de protección de una base instalada, rollback, dependencia ajena, limpieza acotada y recreación de funciones.
- [CONTEO_ACTUAL_2026_10_04.json](CONTEO_ACTUAL_2026_10_04.json): verificación posterior de las 104 tablas; cero restricciones sin validar y cero triggers deshabilitados.
- [CONTEOS_VERIFICADOS.md](CONTEOS_VERIFICADOS.md): cantidades por tabla, año y estado.

### Corrección de reconstrucción repetida

Una primera instalación terminaba correctamente, pero una segunda ejecución aislada de `migrate:fresh` fallaba con `SQLSTATE 42723`: la función `ofi_validar_trayecto()` ya existía. PostgreSQL elimina los triggers con sus tablas y las funciones que dependen de tipos fila, pero conserva otras funciones PL/pgSQL sin esa dependencia catalogada. Las migraciones posteriores intentaban crearlas nuevamente.

La [nueva preparación independiente](../../migrations/0000_01_01_000001_preparar_recreacion_funciones_oficiales.php) retira exclusivamente una lista cerrada de diez firmas propias residuales, antes de crear las tablas. Usa `DROP FUNCTION ... RESTRICT`, sin `CASCADE`. Si encuentra tablas públicas distintas de `migrations`, no actúa: una ejecución normal de `migrate` en una base instalada conserva todas las funciones y datos. Su `down()` tampoco retira funciones activas. No se alteraron destructivamente migraciones ya aplicadas.

Con esta preparación, dos ejecuciones consecutivas de `php artisan migrate:fresh`, sin opciones adicionales, terminaron con salida 0, 104 tablas y 108 registros de migraciones en PostgreSQL aislado. La base `SAVPTIS3-OFICIAL` se mantuvo intacta durante estas pruebas.

Se comprobó también que aplicar `up()` y `down()` con una base instalada conserva sus filas y definiciones de funciones; una dependencia ajena bloquea el DROP con `2BP01` y revierte toda la limpieza parcial; una función ajena sin dependencia permanece; y una nueva reconstrucción vuelve a crear las diez rutinas esperadas.

En `storage/app/private/integracion` quedan `RESULTADO_SEEDERS.json`, las 612 fichas de `FICHAS_ESTUDIANTES_COMPLETAS.json`, siete `HISTORIAL_<AÑO>.zip` y `ACCESOS_ESTUDIANTES.csv`. Cada ZIP conserva los hechos detallados y su manifiesto. No publicar contraseñas, hashes ni la nómina privada. La guía comprueba BD/seeders; la adaptación funcional de controladores y UI es una etapa separada.

## Adaptación de consumidores al contrato de 104 tablas

Trabajo en `Fusion_Sistema`, sobre los archivos existentes. `app/Models` contiene únicamente los 104 archivos oficiales: 51 académicos, 22 de aula virtual, 16 del aporte y 15 del sistema/framework. Se retiraron los duplicados y modelos de entidades ajenas al esquema, actualizando sus consumidores; no utilizar imports antiguos.

La auditoría de solo lectura comprobó 104 tablas y 104 modelos canónicos, sin atributos `fillable`, casts ni relaciones declaradas apuntando a columnas inexistentes. Los generadores automáticos conservan `USU_000001`, `PAR001`, los demás formatos registrados y el crecimiento más allá de 999999. Spatie, Sanctum, caché, sesiones y trabajos conservan sus claves técnicas nativas; las tres pivotes respetan su PK compuesta.

Los consumidores de planes resuelven gestión, curso, paralelo y turno por `grupo_academico`. Las notas oficiales se guardan por `cod_ins`, PAS/PES, periodo y `fea_cal`, y utilizan los estados reales del CHECK: `VIGENTE`, `RECTIFICADA`, `ANULADA`. La pertenencia LMS se resuelve por intervalo; el trayecto técnico exige la especialidad exacta. El formulario de horario reutiliza asignaciones institucionales existentes: no crea automáticamente planes ni asignaciones docentes. Una cabecera nueva solicita su fecha efectiva explícita.

Las escrituras de asistencia usan `sesion_academica` y un detalle de horario real del plan, día y bloque. Regencia depende de `vinculo_personal` y `cargo_institucional`, con `cod_ras` e intervalo; ya no consulta una tabla `regente` inexistente. Asignar un rol no inventa RUDE, procedencias ni perfiles, ni desactiva trayectorias académicas previas.

### Diferencia verificada de permisos

En la fixture aislada, el seeder de REALES restaura 49 permisos y 128 relaciones entre rol y permiso. El `RolSeeder.php` existente incorpora la matriz actual de módulos, acciones y ámbitos que exigen los controladores. Ejecutarlo dentro de una transacción aislada produce 221 relaciones: agrega 93 y no retira ninguna de las 128 originales. La prueba inicial hizo rollback. Tras la autorización explícita del usuario, la reconciliación institucional se ejecutó el 04/10/2026: 49 → 111 permisos y 128 → 221 relaciones, preservando todas las anteriores. Evidencia: [RECONCILIACION_PERMISOS_2026_10_04.json](RECONCILIACION_PERMISOS_2026_10_04.json).

La ausencia de esos permisos provoca 403 legítimos, por ejemplo al guardar notas con `calificaciones.gestionar.global`. No se sustituye una autorización por un permiso más amplio ni se omite una Policy. La reconciliación institucional fue aprobada y ejecutada. No se modifica la fuente privada de REALES.

Implementación: `DatabaseSeeder` reutiliza `RolSeeder` después de REALES/historial y antes de `AdministradorSistemaSeeder` para futuras reconstrucciones. Conservar personas, estudiantes, docentes, horarios, planes y todas las relaciones actuales. Sin tablas nuevas ni seeder duplicado.

Relaciones que agrega la matriz ya existente:

| Rol | Permiso |
|---|---|
| Administrador | usuarios.ver.global |
| Secretaria | usuarios.ver.institucional |
| Administrador | usuarios.crear |
| Secretaria | usuarios.crear |
| Administrador | usuarios.editar |
| Secretaria | usuarios.editar |
| Administrador | usuarios.activar |
| Secretaria | usuarios.activar |
| Administrador | usuarios.desactivar |
| Secretaria | usuarios.desactivar |
| Administrador | usuarios.reset_password |
| Secretaria | usuarios.reset_password |
| Administrador | usuarios.asignar_roles |
| Administrador | roles-permisos.gestionar |
| Administrador | roles.solicitudes.ver |
| Administrador | roles.solicitudes.crear |
| Administrador | roles.solicitudes.analizar |
| Administrador | roles.solicitudes.cancelar |
| Administrador | roles.crear |
| Administrador | roles.editar |
| Administrador | roles.desactivar |
| Administrador | roles.permisos.asignar |
| Administrador | roles.documentos.ver |
| Administrador | regencia.asignaciones.gestionar |
| Administrador | calificaciones.rectificar |
| Administrador | personas.ver.institucional |
| Director | personas.ver.institucional |
| Secretaria | personas.ver.institucional |
| Administrador | personas.gestionar.institucional |
| Secretaria | personas.gestionar.institucional |
| Administrador | estudiantes.ver.global |
| Director | estudiantes.ver.institucional |
| Secretaria | estudiantes.ver.institucional |
| Regente | estudiantes.ver.institucional |
| Docente | estudiantes.ver.curso |
| Estudiante | estudiantes.ver.propio |
| Administrador | estudiantes.gestionar.institucional |
| Secretaria | estudiantes.gestionar.institucional |
| Administrador | cursos.ver.global |
| Director | cursos.ver.institucional |
| Secretaria | cursos.ver.institucional |
| Regente | cursos.ver.institucional |
| Docente | cursos.ver.asignados |
| Estudiante | cursos.ver.propios |
| Administrador | cursos.gestionar.global |
| Administrador | inscripciones.ver.institucional |
| Director | inscripciones.ver.institucional |
| Secretaria | inscripciones.ver.institucional |
| Regente | inscripciones.ver.institucional |
| Docente | inscripciones.ver.curso |
| Estudiante | inscripciones.ver.propias |
| Administrador | inscripciones.gestionar.institucional |
| Secretaria | inscripciones.gestionar.institucional |
| Administrador | calificaciones.ver.global |
| Director | calificaciones.ver.institucional |
| Regente | calificaciones.ver.institucional |
| Docente | calificaciones.ver.curso |
| Estudiante | calificaciones.ver.propias |
| Administrador | calificaciones.gestionar.global |
| Docente | calificaciones.gestionar.curso |
| Administrador | asistencia.ver.institucional |
| Director | asistencia.ver.institucional |
| Regente | asistencia.ver.institucional |
| Docente | asistencia.ver.curso |
| Estudiante | asistencia.ver.propia |
| Docente | asistencia.gestionar.curso |
| Docente | aula.materiales.gestionar.curso |
| Docente | aula.tareas.gestionar.curso |
| Estudiante | aula.entregas.gestionar.propias |
| Docente | aula.entregas.revisar.curso |
| Estudiante | orientacion.realizar |
| Estudiante | orientacion.ver.propia |
| Docente | orientacion.ver.curso |
| Administrador | orientacion.ver.institucional |
| Director | orientacion.ver.institucional |
| Regente | orientacion.ver.institucional |
| Administrador | orientacion.configurar |
| Administrador | reportes.ver.institucional |
| Director | reportes.ver.institucional |
| Secretaria | reportes.ver.institucional |
| Regente | reportes.ver.institucional |
| Administrador | reportes.exportar.institucional |
| Director | reportes.exportar.institucional |
| Administrador | bitacora.ver.global |
| Administrador | integraciones.peter3.usar |
| Director | integraciones.peter3.usar |
| Docente | integraciones.peter3.usar |
| Estudiante | integraciones.peter3.usar |
| Administrador | conocimiento.ver |
| Director | conocimiento.ver |
| Administrador | conocimiento.proponer |
| Director | conocimiento.proponer |
| Administrador | conocimiento.revisar |

## Adaptación comprobada de modelos y consumidores

La auditoría de solo lectura comparó **104 tablas y 104 modelos canónicos**, sin tablas sin modelo, columnas inexistentes en `fillable`/casts ni errores en las consultas de relaciones inspeccionadas. Organización: `app/Models/Oficial/Academico` (51), `AulaVirtual` (22), `AporteAcademicoVocacional` (16) y `Sistema` (15). [INVENTARIO_104_TABLAS.md](INVENTARIO_104_TABLAS.md) enlaza cada tabla, su migración y su modelo. Desde la limpieza, no quedan modelos alternativos, wrappers ni carpeta `Legado` en `app/Models`; las cuatro utilidades compartidas están en `app/Support/Modelos` y no son modelos.

Los modelos propios generan sus códigos mediante `CodigoInstitucional` y `FormatoCodigoInstitucional`: reserva e inserción en una misma transacción, bloqueo PostgreSQL para concurrencia y relleno mínimo aprobado, con crecimiento sin truncamiento. `BIT` utiliza su secuencia, con saltos normales por rollback. Las claves nativas de Laravel, Sanctum y Spatie conservan su formato; las PK compuestas operan usando todos sus componentes.

| Consumidor | Adaptación |
|---|---|
| `GradeService`, políticas y formularios de notas | Inscripción anual, PAS o PES exacto, `fea_cal`, periodo, grupo y especialidad histórica. Estados oficiales `VIGENTE`, `RECTIFICADA`, `ANULADA`; rectificación justificada sin cambiar el contexto anterior. |
| `InscripcionAcademicaService`, `GestionInscripciones`, `GestionEstudiantes` | Una inscripción anual; regular de mañana y técnica complementaria; membresías de aulas existentes. Cambio con fecha explícita: cerrar intervalo y crear otro. A→B→A conserva tres vigencias y nuevas membresías. Reactivación con fecha explícita sin reabrir filas históricas. |
| `SesionAcademicaService`, `AsistenciaService` | Sesión, fecha, bloque y detalle horario oficial; vigencia y membresía correspondientes. No fabricar horarios técnicos. |
| `EntregaService`, tareas y entregas | Nuevo intento en nueva fila; conservar entregas, archivos, notas y retroalimentaciones anteriores. Respetar apertura/cierre y límite de intentos. |
| Planes, cursos, carga y regencia | Contexto desde `grupo_academico` y vínculos reales; no consultar columnas duplicadas retiradas ni tablas inexistentes de roles. |
| Reportes y consulta institucional | Filtros por gestión/curso/estudiante, notas técnicas y resultados anuales oficiales. RIASEC desde resultados del instrumento vigente. |
| PDF y ZIP | PDF individual filtrado hasta 1.500 notas. ZIP con listado íntegro en partes de hasta 1.000 notas y archivos distintos, sin descartar el resto del historial. |
| Respaldo SQL | `pg_dump` nativo reemplaza el exportador parcial de 18 tablas; conserva estructura, datos, secuencias, funciones, triggers y restricciones. |

### Corrección independiente de intentos, aplicada con autorización

[2026_10_04_000008_conservar_intentos_de_entrega_tarea.php](../../migrations/AulaVirtual/2026_10_04_000008_conservar_intentos_de_entrega_tarea.php) está en `AulaVirtual`, separado de seeders y de migraciones ya aplicadas.

Antes coexistían `UNIQUE(cod_tar,cod_est)` y `UNIQUE(cod_tar,cod_est,int_ent)`: la primera impedía un segundo intento. Se reutilizan la segunda y el `CHECK(int_ent > 0)` existentes, se establece `int_ent NOT NULL` y se elimina únicamente la unicidad antigua. La prevalidación detiene intentos ausentes, inválidos o duplicados sin reescribirlos. El rollback bloquea si hay varios intentos históricos de una tarea/estudiante.

Fue aplicada únicamente esta corrección autorizada en `SAVPTIS3-OFICIAL`: **1.313.319 entregas conservadas**, huella agregada idéntica antes/después y mismos conteos de adjuntos, notas y retroalimentaciones. Evidencia: [CORRECCION_INTENTOS_2026_10_04.json](CORRECCION_INTENTOS_2026_10_04.json).

`0000_01_01_000001_preparar_recreacion_funciones_oficiales` permanece pendiente en esa instalación. No crea tablas: en una instalación con tablas su `up()` retorna sin retirar funciones. No se ejecutó junto con la corrección. Una reconstrucción nueva ejecuta **109 archivos de migración** y crea **104 tablas**, comprobado dos veces con el comando normal en PostgreSQL aislado: [VALIDACION_MIGRATE_FRESH_SIN_SEED.json](VALIDACION_MIGRATE_FRESH_SIN_SEED.json).

### Verificación y límites

`tests/Feature/Authorization/AcademicContextTest.php`: **13 pruebas y 76 aserciones**, PostgreSQL aislado, rollback y HTTP/correo simulados. Cubre los 104 modelos, PK compuestas, PAS/PES, fecha académica obligatoria, segundo intento y rollback, RIASEC vigente, inscripción con LMS, A→B→A, retiro/reactivación, técnica complementaria, PDF filtrado real y conservación de todas las notas al dividir el ZIP. La división intercepta la generación de cada parte; el PDF filtrado sí se renderiza realmente con mPDF.

Además: **19 comprobaciones de escritura correctas**, códigos concurrentes en dos procesos, lectura de relaciones y restauración nativa aislada con **104 tablas, 109 migraciones y un dato sintético conservado**. La restauración medida utiliza un esquema vacío y ese dato de prueba; no es una restauración medida de los 7,8 millones de registros. No se volvió a ejecutar `migrate:fresh` ni el historial de seeders en la base institucional. No presentar estos resultados como QA visual completo ni como ejecución de toda la suite antigua.

Repetir en una fixture poblada separada, puerto distinto del institucional y nombre protegido `savp_revision_*` o `savp_historial_integral_*`:

```powershell
cd C:\laragon\www\savp-reestructuracion
$env:SAVP_TEST_PG_DATABASE='savp_historial_integral_e82950d4'
$env:SAVP_TEST_PG_PORT='5543'
$env:SAVP_TEST_PG_USER='revision_savp'
php vendor/phpunit/phpunit/phpunit tests/Feature/Authorization/AcademicContextTest.php
```

Las pruebas rechazan `SAVPTIS3-OFICIAL` y el puerto 5432. Sin configuración explícita quedan omitidas. [VALIDACION_ADAPTACION_2026_10_04.json](VALIDACION_ADAPTACION_2026_10_04.json) reúne la evidencia sin datos personales.

### Operación del respaldo nativo

Requiere `pg_dump` de la misma versión mayor del servidor. Busca en PATH y en la instalación estándar de PostgreSQL en Windows; otra ubicación se configura con `PG_DUMP_BINARY` y `php artisan config:clear`. La contraseña pasa en el entorno del proceso, nunca en argumentos ni en el SQL. Usa la conexión efectiva de Laravel. Un fallo elimina el archivo parcial.

El SQL crea el esquema: restaurarlo **en otra base vacía autorizada**, con `psql --set ON_ERROR_STOP=1 --file respaldo.sql`; no encima de una instalación creada con `migrate:fresh` ni sobre la base institucional para probarlo. La exportación grande necesita tiempo y espacio. La BD conserva referencias a documentos; la restauración de adjuntos requiere conservar también su almacenamiento privado. El respaldo no publica ni envía documentos a terceros.

## Limpieza definitiva de modelos — 04/10/2026

`app/Models` contiene exclusivamente **104 archivos de modelo**, uno por tabla oficial:

| Carpeta | Archivos |
|---|---:|
| `app/Models/Oficial/Academico` | 51 |
| `app/Models/Oficial/AulaVirtual` | 22 |
| `app/Models/Oficial/AporteAcademicoVocacional` | 16 |
| `app/Models/Oficial/Sistema` | 15 |
| **TOTAL** | **104** |

Consultar [INVENTARIO_104_TABLAS.md](INVENTARIO_104_TABLAS.md) para el archivo exacto de cada tabla. Se retiraron **55 duplicados/compatibilidades y 7 modelos sin tabla oficial**; no se eliminaron tablas ni registros. Los cuatro auxiliares de claves y contexto viven en `app/Support/Modelos`, fuera del inventario de modelos. No volver a crear modelos raíz ni carpetas `Legado` para resolver imports antiguos: actualizar el consumidor al namespace canónico.

Ejemplos de imports válidos:

```php
use App\Models\Oficial\Academico\Persona;
use App\Models\Oficial\Academico\Estudiante;
use App\Models\Oficial\AulaVirtual\Tarea;
use App\Models\Oficial\Sistema\User;
```

Los generadores oficiales siguen en los modelos y auxiliares existentes. `UserFactory` apunta explícitamente al único modelo User y utiliza esos generadores; no genera claves aleatorias ni equipos ajenos al esquema. Se actualizaron consumidores, configuración, factories y pruebas para resolver los modelos oficiales.

Los modelos retirados sin entidad oficial fueron `Administrador`, `Director`, `Regente`, `SecretariaGeneral`, `MetaAcademica`, `RoleRequest` y `UnidadClase`. Los cargos/roles se representan mediante personal institucional y permisos existentes. Las operaciones de solicitudes de roles/metas que exigían tablas ausentes devuelven un bloqueo explícito, conservando la autorización; no se fabricaron tablas para mantenerlas funcionando. Las secciones LMS usan `SeccionClase`.

**Identidad polimórfica histórica:** `User::getMorphClass()` conserva el texto `App\Models\User` y `AppServiceProvider` lo resuelve mediante `Relation::morphMap()` al modelo oficial. Ese texto es una identidad guardada en relaciones polimórficas, no otro archivo/modelo PHP. No cambiarlo sin una migración autorizada de las identidades históricas. Así se conservan las asociaciones existentes de Spatie.

### Corrección independiente de Sanctum

Migración completa: `database/migrations/2026_10_04_000009_compatibilizar_tokens_con_clave_de_usuario.php`. La migración original de Sanctum queda intacta. `personal_access_tokens.tokenable_id` era BIGINT y rechazaba claves `USU_000001`; la corrección adopta exactamente el VARCHAR físico de `users.cod_usu`. El modelo oficial `PersonalAccessToken` interpreta esa referencia como texto.

Se conservan PK, índices, obligatoriedad y filas. Al ser una relación polimórfica, no se agrega una FK exclusiva hacia users. Antes de alterar el tipo, se bloquean tokens históricos de usuario sin correspondencia y valores que excedan la longitud permitida. No se infieren propietarios. El rollback solo admite referencias representables exactamente en BIGINT; si existen tokens USU se bloquea y explica el motivo, sin eliminarlos.

**Preparada y probada únicamente en PostgreSQL aislado; no ejecutada en SAVPTIS3-OFICIAL durante esta limpieza.** Las tres pruebas verifican creación real de token y resolución del usuario canónico, conservación del propietario numérico de otro tipo, rollback permitido y bloqueado, y rechazo de identidades históricas sin correspondencia.

### Evidencia actual de limpieza

- **28 pruebas, 129 aserciones correctas**, incluyendo modelos, generadores, relaciones, autorización, contexto académico y tokens. Ejecutadas en la fixture protegida de PostgreSQL, con rollback y servicios externos simulados.
- Auditoría de metadatos: **104 tablas, 104 modelos, ninguna tabla sin modelo, ningún error de atributos/relaciones**.
- Dos ejecuciones consecutivas de `php artisan migrate:fresh` sin opciones en otra base aislada: **104 tablas y 110 registros de migración**, sin errores. No se ejecutaron seeders en esta prueba de reconstrucción; no constituye una repetición medida del dataset completo.
- Evidencia: [VALIDACION_MIGRATE_FRESH_SIN_SEED.json](VALIDACION_MIGRATE_FRESH_SIN_SEED.json).
- No hubo cambios de datos institucionales ni ejecución institucional de migraciones durante esta limpieza. Las cifras anteriores de pruebas/restauración son snapshots históricos, no nuevos resultados de esta comprobación.
