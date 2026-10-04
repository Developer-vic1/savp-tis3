# HISTORIAL ESTUDIANTIL 2020–2026

Los archivos están dentro del proyecto. `DatabaseSeeder` invoca `Oficial/HistorialInstitucionalSeeder`, que carga seis fases por gestión: gestión/calendario, inscripciones, aula virtual, asistencias, orientación y cierre. Las carpetas numéricas contienen closures PHP cargadas explícitamente; no usan namespaces PSR-4 numéricos.

## Organización

| Carpeta | Contenido |
|---|---|
| `REALES` | 25 seeders originales conservados, con namespace organizado; nueve fuentes auxiliares verificadas por SHA-256. |
| `2020` a `2026` | Seis archivos PHP y un calendario JSON por año. |
| `Soporte` | Motor del historial, formatos de códigos e informes/exportaciones. |
| `Fuentes` | Instrumentos existentes RIASEC, catálogo universitario existente y manifiesto de personal. |
| `Documentos` | PDF proporcionado por el usuario, adjuntado sin leer su contenido. |

`REALES/ADMINISTRADOR/AdministradorSistemaSeeder.php` configura la cuenta `asturizagavictor@gmail.com` con los datos y contraseña confirmados por su titular. Reutiliza `PER_0001` y `USU_0001` cuando están presentes en la fuente oficial; no duplica persona, usuario ni personal. Guarda los datos personales en MAYÚSCULAS, la fecha de nacimiento como `2006-06-11` y la contraseña mediante hash. Asigna el rol oficial `Administrador`, heredando sus permisos existentes sin crear permisos ni tablas adicionales. `DatabaseSeeder` lo ejecuta después del historial; el seeder original de administrador permanece intacto como fuente histórica. La actualización autorizada del nombre, dirección y cuenta de este administrador es una excepción explícita a la conservación literal de su registro original.

## Naturaleza de los registros

Son **612 estudiantes distintos de un dataset sintético para estudio y validación**, con identidad, contacto, responsable familiar, usuario y trayectorias variadas. La apariencia plausible de nombres, RUDE, CI, teléfonos y direcciones no acredita personas reales. Los valores de Antony y sus 22 notas fueron aportados por el usuario; su CI, contacto, responsable y fecha académica son sintéticos autorizados. No se envía correo ni se crean cuentas externas.

Los nombres, direcciones y textos nuevos se generan en MAYÚSCULAS. Los correos de acceso se guardan en minúsculas porque Fortify normaliza el usuario antes de autenticar y PostgreSQL compara el correo de forma sensible a mayúsculas. Se conserva el formato de correo aprobado y la información oficial existente.

Los 48 docentes y 56 integrantes de personal institucional se recuperan del respaldo privado oficial. Sus identidades, cuentas y asignaciones no se regeneran. Las fuentes de materias, especialidades, planes, turnos, plantillas, bloques y horarios se conservan. Para gestiones anteriores se crean copias históricas sintéticas de esa configuración, sin afirmar que sean horarios oficiales históricos.

2020 conserva la excepción pandémica: actividad limitada, sin notas oficiales ni notas de tareas y cierre/promoción excepcional documentados. 2021–2025 tienen tres trimestres. 2026 conserva solo hechos y notas de los primeros dos trimestres; el tercero está planificado y no tiene notas ni resultado anual.

La asistencia excluye fines de semana, vacaciones y feriados: incluye los traslados dominicales previstos en el [DS 2750](https://www.gacetaoficialdebolivia.gob.bo/app/webroot/archivos/Reglamentos/RM4.pdf), el [7 de agosto de 2025 por el Bicentenario](https://mintrabajo.gob.bo/index.php/nota_prensa/gobierno-nacional-dispone-feriado-nacional-excepcional-para-el-jueves-7-de-agosto/) y los feriados adicionales del [DS 5521 para 2026](https://intranet.mineria.gob.bo/wp-content/uploads/2026/01/DS-5521.pdf). Estos filtros afectan únicamente los hechos sintéticos, sin redistribuir horarios oficiales.

Cada aula con alumnado contiene 15–25 tareas por trimestre ordinario, con carátula inicial, temática de materia, entregas, omisiones, retrasos, notas y retroalimentaciones. La excepción de 2020 tiene cuatro actividades sin calificación. Los cuestionarios incluyen desarrollo y selección, respuestas diversas e intentos sucesivos; algunos estudiantes no participan. La nota trimestral no se presenta como promedio automático de tareas.

Una inscripción anual puede tener un trayecto regular y un trayecto técnico complementario. No se duplican estudiante, persona ni inscripción por asistir a formación técnica. La inscripción técnica utiliza la cabecera oficial, el plan exacto, el grupo y las fechas. **La asistencia requiere detalle horario real**: las fuentes tienen 4.120 detalles generales y cero detalles técnicos. Se generan asistencias variadas sobre esos detalles existentes; no se inventan días ni bloques técnicos, ni participantes externos sin fuente oficial. Las aulas técnicas sí contienen membresías, tareas y notas cuando hay alumnado y plan válidos.

El resultado RIASEC conserva respuestas, seis dimensiones, versión e instrumento. No se inventa una compatibilidad con carreras ni capturas o validaciones universitarias oficiales: esas tablas reciben registros únicamente cuando existe su evidencia y contrato. Las tablas técnicas de Laravel también pueden quedar vacías por su ciclo de vida; cero no implica un seeder pendiente.

## Fuentes locales requeridas

Estas fuentes **ya están dentro de este proyecto** y sobreviven a `migrate:fresh`:

- `database/seeders/Oficial/REALES/FUENTES/*.local.php`: nueve archivos originales, comprobados mediante `MANIFIESTO_FUENTES.json`.
- `storage/app/private/integracion/PERSONAL_OFICIAL.json`: copia privada cuyo SHA figura en `Fuentes/personal_oficial_manifest.json`.
- `database/seeders/Oficial/Documentos/ACTIVIDAD_2_CPM_PERT.pdf`.

Las fuentes con datos personales y los accesos se excluyen de Git. Si se copia el proyecto a otro equipo, deben copiarse también esas fuentes privadas por un medio autorizado. Un clone de Git por sí solo no contiene la nómina privada. Si falta una fuente o cambia su hash, la carga se detiene y no fabrica docentes ni reemplaza los registros.

## Ejecución posterior autorizada por el operador

Terminal PowerShell, directorio `C:\laragon\www\savp-reestructuracion`. Verificar primero `.env`, la conexión PostgreSQL elegida y el respaldo previo. `migrate:fresh` elimina todas las tablas de **esa conexión**. El usuario autorizó la creación y carga de su nueva base vacía `SAVPTIS3-OFICIAL`, PostgreSQL `127.0.0.1:5432`; esa autorización no comprende eliminar datos de otra base.

```powershell
php artisan migrate:fresh --seed
```

También puede separarse la creación y la carga:

```powershell
php artisan migrate:fresh
php artisan db:seed
```

La carga completa tiene millones de hechos. La ejecución integral comprobada en PostgreSQL aislado tardó 3.618 segundos, aproximadamente 60 minutos; el tiempo depende del equipo y de PostgreSQL. Muestra la gestión y fase actual. No interrumpirla al ver una fase de asistencia o aula virtual prolongada. Conserva FK, CHECK, EXCLUDE y triggers; no deshabilita restricciones. Ante un error revierte la transacción completa. Los archivos generados por un intento fallido no acreditan una carga confirmada; el informe final solo se obtiene después de validar.

El historial se carga en una sola transacción: las tablas se ven inmediatamente después de las migraciones, pero desde pgAdmin los registros no son visibles hasta el COMMIT final. No se deben volver a lanzar los seeders por ver tablas temporalmente vacías durante una carga activa.

La ejecución requiere las siete migraciones correctivas independientes preparadas en `Academico` y `AulaVirtual` (2026-10-04): aulas PAS/PES, secuencia de bitácora, trayectos simultáneos, membresías históricas, fecha académica, cabecera técnica e integridad de hechos. Permanecen separadas del commit de seeders. Las rutas de migraciones están registradas en `AppServiceProvider`.

## Archivos generados

En `storage/app/private/integracion` quedan `ACCESOS_ESTUDIANTES.csv` (contraseñas sintéticas aleatorias, sin publicación), `RESULTADO_SEEDERS.json` (conteos SQL reales por tabla y año), `FICHAS_ESTUDIANTES_COMPLETAS.json` (las 612 identidades y sus trayectorias) y siete `HISTORIAL_<AÑO>.zip`. Cada ZIP incluye hechos académicos/LMS/orientación, fichas estudiantiles sin hashes ni contraseñas, el PDF adjunto y un manifiesto de alcance. Esos archivos requieren acceso institucional autorizado; generar el archivo no modifica los controladores ni autoriza una descarga pública.

Las identidades numéricas importadas se sincronizan al finalizar para admitir registros nuevos. Los códigos nuevos respetan los prefijos del proyecto y el relleno mínimo aprobado; al superar seis posiciones continúan sin truncarse. Los códigos oficiales existentes se conservan exactamente.

La verificación de este dataset valida PostgreSQL y seeders. La adaptación y prueba funcional de consumidores Laravel/UI son una etapa distinta, como indicó el usuario.

## Ejecución confirmada en SAVPTIS3-OFICIAL

Se ejecutó `migrate:fresh --seed --force` en la nueva base vacía autorizada `SAVPTIS3-OFICIAL`, PostgreSQL `127.0.0.1:5432`, con salida 0. Duración de creación y carga: 4.205 segundos, aproximadamente 70 minutos. Después se aplicó el seeder administrativo solicitado durante la carga, reutilizando `PER_0001` / `USU_0001` y comprobando la autenticación con Fortify. Las futuras ejecuciones de `DatabaseSeeder` ya incluyen ese paso automáticamente.

La consulta posterior al COMMIT confirmó 104 tablas, 612 estudiantes distintos, 48 docentes y 56 integrantes de personal institucional. Se verificaron los conteos reales, las 22 notas aportadas de Antony, las 612 fichas, los siete ZIP anuales y sus manifiestos, las secuencias y la ausencia de restricciones sin validar o triggers deshabilitados. No se modificó la base anterior.

El desglose completo por tabla, gestión y estado está en [CONTEOS_VERIFICADOS.md](CONTEOS_VERIFICADOS.md). La evidencia de esta ejecución está en [EJECUCION_SAVPTIS3_OFICIAL.json](EJECUCION_SAVPTIS3_OFICIAL.json); la prueba integral anterior aislada se conserva en [VALIDACION_POSTGRESQL_AISLADO.json](VALIDACION_POSTGRESQL_AISLADO.json). Los datos personales completos y las contraseñas permanecen en los archivos privados del proyecto.
