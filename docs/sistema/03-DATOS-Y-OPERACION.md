# Datos, modelos y operación segura

## 1. Base y modelos

PostgreSQL es la base canónica. La base institucional actual `SAVPTIS3-OFICIAL` contiene historia que debe conservarse. `app/Models/Oficial` contiene **104 modelos para 104 tablas**: `Academico` 51, `AulaVirtual` 22, `AporteAcademicoVocacional` 16 y `Sistema` 15. El [inventario](../../database/seeders/Oficial/INVENTARIO_104_TABLAS.md) indica tabla, migración y clase exacta. No crear duplicados en `app/Models` raíz para resolver imports antiguos.

Las migraciones se distribuyen en `database/migrations/Sistema`, `Academico`, `AulaVirtual`, `AporteAcademicoVocacional` y raíz para Laravel/paquetes/correcciones. `AppServiceProvider::boot` registra las cuatro carpetas. Las 104 tablas corresponden a la instalación histórica; las ampliaciones de accesos se describen en el inventario y en `ROLES-Y-ACCESOS-PROGRAMADOS.md`. Hay migraciones que crean varias tablas y otras que corrigen restricciones sin crear una tabla.

## 2. Historia académica

| Hecho | Tabla principal | Regla |
|---|---|---|
| Alumno e identidad | `persona`, `estudiante`, `users` | Relaciones separadas; no duplicar persona. |
| Gestión y curso | `gestion_academica`, `grupo_academico` | El grupo fija el contexto académico. |
| Inscripción anual | `inscripcion_estudiante` | Una inscripción por trayectoria anual correspondiente. |
| Contexto temporal | `inscripcion_vigencia` | Regular con `cod_esp_tec` NULL; técnico con especialidad exacta. |
| Plan y clase | `plan_asignatura` / `plan_especialidad`, `clase_virtual` | La clase corresponde a exactamente un tipo de plan. |
| Membresía LMS | `clase_estudiante` | Permite tramos históricos sucesivos; no usar alumno+aula como clave eterna. |
| Nota oficial | `calificacion` | Validar plan, período, fecha académica y trayecto correspondiente. |
| Tarea y nota de tarea | `tarea`, `entrega_tarea`, `calificacion_tarea` | Los intentos son hechos distintos; no reemplazar historia. |
| Asistencia | `sesion_academica`, `asistencia_clase`, `asistencia_estudiante` | Depende de fecha, horario y contexto válido. |
| Cierre | `resultado_anual` | Decisión institucional histórica vinculada a inscripción. |

El ingreso o traslado no convierte notas de otra institución en notas oficiales locales: `expediente_traslado` y `nota_traslado` conservan procedencia separada. Para una regla fina consulta migración, servicio y prueba relacionados; esta tabla resume el modelo, no reemplaza las restricciones PostgreSQL.

## 3. Seeders y documentos privados

`DatabaseSeeder` llama al historial institucional, reconcilia roles/permisos y configura el administrador. `database/seeders/Oficial/REALES` conserva las fuentes institucionales; carpetas `2020` a `2026` guardan el historial sintético documentado. Algunos insumos y archivos resultantes están en `storage/app/private/integracion` y no viajan con Git. El [README de seeders](../../database/seeders/Oficial/README.md) describe contenido; [GUIA_AGENTES.md](../../database/seeders/Oficial/GUIA_AGENTES.md) documenta procedencia y comprobaciones. Nunca presentar datos sintéticos como registros personales verificados.

## 4. Operación vigente

**Conservar `SAVPTIS3-OFICIAL`: no ejecutar sobre esa conexión `migrate:fresh`, `migrate:refresh`, `db:wipe`, `schema:drop` ni `db:seed` masivo.** La instrucción anterior de reconstruir una base nueva ya se cumplió y es histórica. También evitar comandos de `composer setup` que ejecutan migraciones automáticamente cuando apunten a la base oficial.

Para investigar, usar lecturas de `migrate:status`, introspección SQL y conteos; confirmar el destino de conexión antes de cualquier operación. Para un cambio de esquema: nueva migración incremental, revisar dependencias y datos existentes, respaldo verificable, prueba y rollback en PostgreSQL aislado, luego autorización concreta para aplicarla al entorno institucional. Para cargar datos: seeder acotado e idempotente, revisión del efecto en filas existentes y autorización específica. Nunca inferir que un comando es seguro por llamarse “seeder”.

La migración correctiva de Sanctum `2026_10_04_000009_compatibilizar_tokens_con_clave_de_usuario.php` se preparó y probó de forma aislada; comprobar `migrate:status` antes de considerarla aplicada a la base oficial. Los reportes JSON del directorio de seeders son instantáneas, no sustituyen una consulta actual.
