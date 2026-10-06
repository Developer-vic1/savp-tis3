# Registro de grados y clases

Cursos y Paralelos comparten el indicador `fases-institucionales` y la guía `requisitos-respaldo-academico`: definición, motivo/PDF, comprobación y confirmación. Un PDF leído que no cumple las coincidencias conserva un intento bloqueado y su archivo privado en bitácora; abrir o llenar el formulario no genera ese intento. La lectura no certifica firmas, sellos ni vigencia jurídica: la autoridad debe comprobarlos.

`CursoInteligente` reconoce séptimo, octavo y órdenes adicionales como solicitudes extraordinarias. `RespaldoCursoInstitucional` exige mención expresa del grado, gestión, decisión de autorización y norma de ampliación. El grado sigue bloqueado después del inicio de clases o si hay notas. El registro confirmado conserva motivo, documento, huella, norma y actor en una transacción. No se cambia la base institucional durante las pruebas.

`config/academico.php` define los turnos incorporados para **nuevos grupos y clases**. Actualmente solo `manana`, por indicación institucional del usuario. Un turno presente en catálogos o datos importados no se considera habilitado automáticamente. Su incorporación requiere la aprobación institucional correspondiente antes de actualizar esta configuración; se conservan los registros de consulta.

En Horario, un bloque libre abre materia y docente. `PlanificacionClaseInteligente` consulta el grupo canónico, preserva los planes existentes, bloquea recreos/historia y cruces por horas reales y fechas, y comprueba la carga semanal. Al guardar bloquea las filas del docente, horario y grupo y repite la revisión. Una planificación nueva requiere permiso del módulo y confirmación del alcance curricular. No se borran clases desde Agregar clase.

Verificación aislada: PHP con `pdo_sqlite` y `sqlite3`, `vendor/phpunit/phpunit/phpunit --filter "CreacionCursoPorFasesTest|GradosAdicionalesInstitucionalesTest|PlanificacionClaseInteligenteTest|ProteccionExpedienteParaleloTest"`. La comprobación `scripts/academico/comprobar_asignaturas_y_horarios.php` utiliza PostgreSQL en una transacción de solo lectura y termina con rollback. El guardado real no se prueba sobre `SAVPTIS3-OFICIAL`.
