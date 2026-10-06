# Roles, cuenta personal y accesos con vigencia

## Interfaz y conteos

Ruta: `/admin/roles-permisos`. Reutiliza los selectores, calendarios, plegables, iconos y tokens de SAVP. Las vistas de lectura, matriz, selección y cuenta personal cambian localmente; las operaciones autorizadas se comprueban nuevamente en el servidor.

La tarjeta **Tu cuenta** cuenta la unión de permisos permanentes y temporales vigentes, sin duplicados. El catálogo general es una cifra separada. Los conteos por rol consultan el morph histórico `App\Models\User`, conservado por `User::getMorphClass()`; contar con el nombre de la clase nueva produce falsos ceros.

## Datos e historia

Migraciones incrementales independientes: `2026_10_05_000000_crear_concesiones_temporales.php` crea únicamente las tres tablas de vigencias; `2026_10_05_000100_crear_gobernanza_y_accesos_programados.php` crea las dos tablas de solicitudes de roles. Ambas están en `database/migrations/Sistema`.

| Tabla | Responsabilidad |
|---|---|
| `solicitud_rol` | Carta privada, huella SHA256, solicitante, Director, gestión, revisión independiente y estado. |
| `solicitud_rol_permiso` | Permisos mínimos solicitados, sin lista desnormalizada. |
| `concesion_acceso` | Autoridad, motivo, intervalo, revocación y avisos emitidos. |
| `concesion_acceso_usuario` | Destinatarios concretos; incorporar un grupo toma sus cuentas actuales. |
| `concesion_acceso_permiso` | Permisos aprobados congelados; ampliar después un rol no amplía concesiones existentes. |

Las claves foráneas restringen borrados y las claves compuestas evitan duplicados. PostgreSQL comprueba códigos institucionales, estados, intervalo y coherencia del tipo de concesión. El rollback se bloquea cuando existe historia. No se escriben roles o permisos temporales en los pivotes permanentes de Spatie.

El 05/10/2026 se aplicó, con autorización del usuario, **únicamente la migración de las tres tablas de concesiones** a `SAVPTIS3-OFICIAL`. Se realizó un respaldo privado (`respaldos-accesos/20261005-053452`, SHA256 `5985bc8a3046c72eedd1ff6ae81b2151e4b346a770f7648a9de618e77ca2513e`), se verificó su listado y se ensayó la migración y rollback en una copia de esquema de PostgreSQL aislado. No se ensayó una restauración completa de los datos del respaldo. Las dos tablas de solicitudes de roles siguen pendientes; esta operación no las aplica.

Los modelos correspondientes son `ConcesionAcceso`, `ConcesionAccesoUsuario` y `ConcesionAccesoPermiso`, todos en `app/Models/Oficial/Sistema`. Las relaciones tienen claves foráneas y los modelos de enlace conservan ambas columnas de la clave compuesta. `AppServiceProvider::boot` registra `database/migrations/Sistema`, por lo que el migrador normal descubre los archivos también en una instalación nueva. **`migrate:fresh` recrea la estructura, pero borra los datos de acceso y la historia; no se ejecuta sobre la base oficial.** No se ejecutaron migraciones destructivas ni seeders masivos.

`scripts/seguridad/ensayar_instalacion_accesos.php` verificó el migrador normal, sin `--path`, en una base PostgreSQL nueva y aislada: las 115 migraciones de la instalación completaron y las tres tablas de accesos quedaron vacías. No ejecuta seeders ni `migrate:fresh`, y elimina únicamente la base desechable que crea. Esto comprueba la reconstrucción de la estructura, no la conservación de datos después de un borrado.

## Autorización y documentos

Cada cuenta mantiene su actor institucional único y cada módulo sus ámbitos de datos. Los accesos temporales permiten tareas delegables o roles complementarios; excluyen actor institucional, permisos críticos, alcance global y autoconcesiones, salvo la delegación acotada de Cursos descrita abajo. La autoridad solo puede delegar permisos permanentes propios. Una revocación conserva evidencia y no retira permisos permanentes.

Crear un rol requiere carta PDF legible de hasta 8 MB y 60 páginas, autorización expresa, nombre del rol, Director vigente y gestión actual. La extracción de texto detecta incoherencias y denegaciones, **no certifica la autenticidad de una firma**. Otro administrador debe verificar identidad, firma y autenticidad institucional antes de aprobar. El sistema vuelve a comprobar gestión, Director, huella y permisos antes de crear. Los intentos rechazados después de leer un PDF se conservan con su evidencia y bitácora al registrar la solicitud.

## Reloj y notificaciones

La interfaz utiliza `America/La_Paz`; los intervalos se almacenan en UTC. La autorización comprueba `inicio <= ahora < fin` en cada petición y no depende del cron para finalizar. No modifica datos ajenos.

Los avisos usan `NotificationService` y las tablas comunes `notificacion` / `notificacion_usuario`. Cada destinatario recibe únicamente su propia programación, inicio, fin o revocación. El autorizador recibe un aviso privado separado, con persona, tareas y duración. Los eventos son idempotentes. Si el proceso se ejecuta tarde, comunica el estado actual sin anunciar una activación ya expirada.

La bandeja muestra «Empieza en» antes del inicio, «Tiempo restante» durante la vigencia y «Finalizado» al vencer. El contador utiliza el reloj del servidor, se actualiza localmente cada segundo y se detiene al cerrar la campana; no consulta la base por cada segundo. El fin se presenta en español y hora de Bolivia. Las concesiones revocadas muestran «Revocado». Los permisos personales permanentes notifican las tareas concedidas o retiradas y «Sin fecha de fin», sin contador ni fechas.

En PostgreSQL, los modelos de concesiones y notificaciones conservan el desplazamiento horario al guardar y las consultas envían fechas con zona explícita. Así se conserva el instante incluso cuando PHP opera en UTC y la sesión PostgreSQL usa `America/La_Paz`. El ensayo de instalación aislado comprueba esta conversión.

Para avisos puntuales, ejecutar el scheduler Laravel cada minuto. En desarrollo, desde PowerShell en `C:\laragon\www\savp-reestructuracion`:

```powershell
& 'C:/laragon/bin/php/php-8.4.13-nts-Win32-vs17-x64/php.exe' artisan schedule:work
```

Detener con `Ctrl+C`. El comando individual `artisan accesos:avisar` procesa avisos pendientes y escribe notificaciones; ejecutarlo solo en el entorno autorizado. No se creó una tarea del sistema operativo.

El 05/10/2026 se inició un programador local en segundo plano y se comprobó la ejecución de `accesos:avisar`. Su salida queda en `storage/logs/programador-accesos-salida.log` y sus errores en `storage/logs/programador-accesos-error.log`. Este proceso es local a esta sesión de desarrollo; no equivale a configurar su arranque permanente después de reiniciar Windows.

## Verificación reproducible

Desde el mismo directorio y terminal:

```powershell
& 'C:/laragon/bin/php/php-8.4.13-nts-Win32-vs17-x64/php.exe' scripts/seguridad/comprobar_roles.php
& 'C:/laragon/bin/php/php-8.4.13-nts-Win32-vs17-x64/php.exe' -d extension=pdo_sqlite -d extension=sqlite3 vendor/phpunit/phpunit/phpunit --filter 'GobernanzaAccesosProgramadosTest|SupportRolesInstitucionalesTest|CartaRolInstitucionalTest|InstitutionalRoleGovernanceTest|RoleRequestAuthorizationTest|RoleDashboardResolverTest'
npm.cmd run build
& 'C:/laragon/bin/php/php-8.4.13-nts-Win32-vs17-x64/php.exe' artisan view:cache
```

El diagnóstico abre una transacción de solo lectura. Las pruebas de escritura exigen SQLite `:memory:`; no prueban las restricciones específicas de PostgreSQL ni sustituyen su ensayo aislado. Las pruebas de cartas verifican reglas sobre texto extraído; no constituyen una certificación documental.

## Consulta y edición preventiva

La pantalla comienza en consulta. Edición requiere autoridad permanente, habilita las tareas seleccionables y conserva un borrador hasta Guardar. Salir descarta el borrador; para cambiar de rol debes guardar o descartar primero. El servidor bloquea Guardar sin ese modo habilitado; la propiedad está bloqueada frente a modificaciones del cliente. Consulta, Edición, Operación autorizada y Sin permiso describen la tarea y su alcance, sin ampliar los permisos. Administración y Aula virtual tienen filtros locales propios.

Los motivos se seleccionan por contexto; Otro exige una explicación. Las responsabilidades se eligen del catálogo. La interfaz bloquea entradas incompletas y texto aparentemente incoherente, y el servidor valida de nuevo. Estas heurísticas no garantizan comprender todos los textos: el contrato completo y sus límites están en `05-SUPPORTS-Y-PREVENCION-DE-ERRORES.md`.

## Permisos por usuario

Por usuario busca cuentas vigentes por nombre (hasta 20 coincidencias) y separa permisos heredados, personales y temporales, sin contar dos veces una tarea. Edición habilita únicamente los permisos personales delegables; no cambia el actor ni los roles. Salir descarta el borrador. Guardar exige motivo, autoridad permanente, una cuenta distinta del operador y una huella vigente de sus accesos. Bloquea permisos críticos/globales, opciones inexistentes y duplicación de tareas heredadas. El servidor revalida bajo bloqueo y guarda permisos, bitácora y aviso privado en una transacción. Si falla el aviso, se revierte el cambio. Usa las tablas existentes; no requiere la migración de vigencias.

El gráfico de tu cuenta muestra permisos propios respecto al catálogo real. «Sin asignación» no implica que se puedan conceder: siguen vigentes las reglas de autoridad y ámbito. Crear roles conserva el flujo de carta y revisión independiente; los nombres nuevos de permisos solo tienen efecto cuando existe una tarea implementada y protegida que los usa, por lo que no se ofrecen como texto libre.

## Catálogo y ventanas reales

`CatalogoVentanasPermisos` reutiliza `WorkspaceNavigation::catalogo`, requisitos de middleware, destinos de redirección, consultas compatibles y requisitos conocidos de servicios. El catálogo muestra todos los registros de `permissions`, incluidos otros guards si existen, con identificador, nombre técnico y roles de `role_has_permissions`. La edición se limita al guard web.

Seleccionar una ventana filtra sus requisitos y operaciones vinculadas. El detalle distingue entrada, consulta y edición; los permisos históricos de entrada no se presentan como garantía de solo lectura. Las rutas sin correspondencia conocida se indican como tales. Los enlaces Ir conservan el actor y solo se habilitan para el operador autorizado; las políticas y ámbitos se comprueban al entrar.

## Delegación de Cursos por un día

`cursos.gestionar.global` permite a una cuenta Docente entrar a `/docente/gestion-cursos`, reutilizando el módulo de Cursos y sus validaciones, respaldo PDF, planificación y bitácora. No cambia su rol ni abre otras rutas de Administrador. La autorización se comprueba en ruta, componente Livewire, operaciones y servicio de planificación; los permisos adicionales de planificación se conservan.

La interfaz ofrece esta delegación exclusivamente como tarea temporal, a Docentes vigentes, por un máximo de 24 horas. El backend rechaza más duración, destinatarios Estudiante y roles complementarios que pretendan delegarla. No se escribe en `model_has_permissions` ni `role_has_permissions`. La caducidad bloquea la siguiente petición incluso si la ventana estaba abierta; el cron solo entrega los avisos. Un día significa 24 horas desde el inicio elegido, usando hora de Bolivia.

Preparación: `scripts/seguridad/preparar_accesos_temporales.php` hace un respaldo privado con SHA256, verifica su listado y ensaya la migración de las tres tablas y su rollback sobre una copia del esquema en PostgreSQL aislado, sin datos institucionales. No aplica cambios en la base institucional. La aplicación de esa migración y la concesión real requieren autorización concreta; no ejecutar todo `artisan migrate` como sustituto del archivo acotado.

## Edición del catálogo y avisos de acceso

El catálogo completo permite editar las asignaciones del rol elegido, mediante el mismo modo Edición, motivo y validación del servidor que la vista por ventanas. Conserva todos los registros técnicos, pero solo ofrece cambios del guard web dentro de la autoridad del operador. El borrador del rol tiene una huella protegida: si otra persona cambia sus permisos, se rechaza el guardado para evitar sobrescribirlos.

En consulta no se muestran casillas. En edición personal, las tareas heredadas o protegidas mantienen su candado; las casillas aparecen únicamente para tareas modificables. El resumen se muestra arriba, solo cuando hay diferencias, y se conserva al filtrar. La delegación de Cursos a Docentes ofrece Autorizar por un día, que abre una concesión con vigencia en lugar de un permiso personal permanente.

Cuando las notificaciones institucionales están habilitadas, el cambio de permisos del rol genera un aviso para sus cuentas activas y para el operador. Si falla la publicación, se revierte la transacción. Las concesiones temporales notifican privadamente al destinatario y al autorizador al programarse, iniciarse, revocarse y finalizar; el aviso del autorizador no se distribuye al resto. El centro actualiza el contador al cambiar accesos y consulta cada minuto mientras la campana está visible. La entrega de avisos futuros requiere schedule:work o el scheduler desplegado; la caducidad de permisos sigue dependiendo del reloj, nunca de ese proceso.

La revisión final identifica destinatarios, tareas, motivo y fechas. Cambiar una selección invalida la revisión y bloquea Confirmar tanto en la interfaz como en el servidor. Las horas se validan con reglas regex en arreglo, para conservar su alternativa de 00 a 23 sin que Laravel la divida como reglas distintas. Los destinatarios inactivos no reciben avisos ni detienen el procesamiento de las demás cuentas.

Un día es un modo fijo de 24 horas: muestra el inicio y el fin como lectura, sin calendarios ni selectores de hora. Personalizado habilita esos controles y obliga a revisar de nuevo. El servidor rechaza una duración distinta de 24 horas que se presente como Un día. Las concesiones de esta ventana siempre tienen fin; los permisos personales permanentes se gestionan sin fechas en su apartado y no convierten la delegación de Cursos en indefinida.

El 05/10/2026 se comprobó la concesión autorizada `CAC_000001` para Félix Mendoza Quispe: edición de Cursos desde el 5 de octubre, 01:53, hasta el 6 de octubre, 01:53 (Bolivia), sin permiso permanente ni cambio de rol. Se verificaron en sesiones separadas los avisos del docente y administrador, el contador, la ventana delegada y el formulario de edición con sus bloqueos académicos; no se modificaron cursos. Paralelos respondió 403. Una comprobación de solo lectura sobre la concesión real simuló el reloj: admite acceso un segundo antes del fin y lo deniega en el instante exacto, incluso reutilizando el servicio. Se corrigió el desplazamiento de cuatro horas detectado en el primer registro, conservando el intervalo autorizado y una entrada de bitácora.

Las fechas se leen en español, con día de la semana, mes y hora local institucional, conservando America/La_Paz. La ventana y la lectura del plazo tienen transiciones breves. Tras guardar correctamente se muestra un aviso de éxito con persona o rol, cierre manual y pausa al pasar el cursor. El aviso no sustituye la notificación persistente ni se muestra si falla la operación. Las animaciones respetan la preferencia de movimiento reducido.

Cada revisión descarta su resultado anterior antes de validar. Una fecha inexistente, un formato con zona horaria alterada o un día incompleto se rechazan independientemente en el servidor. La confirmación de un rol complementario también compara sus tareas actuales con las revisadas dentro de la transacción: si cambiaron, no se concede ni se avisa hasta revisar de nuevo. Los destinatarios malformados se responden como errores de validación y no como excepciones de tipo.
