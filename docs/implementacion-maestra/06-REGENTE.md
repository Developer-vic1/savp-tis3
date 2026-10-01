# Regente — V058–V069

RegencyAccessService correlaciona gestión Y grado asignados, perfil/personal/regente/gestión activos. Si falta regente_asignaciones cierra el scope, nunca consulta global. Esa estructura ya está declarada en migration del 2026-09-28: no se preparó otra tabla de asignaciones.

V059 Mis grados consulta asignaciones propias paginadas. Cursos, estudiantes, inscripciones, asistencia y LMS se limitan por contexto. Estudiantes requieren estado e inscripción activos.

V068 tiene RegencyReportService/Controller: consulta paginada por plan asignado y PDF privado efímero. Inscripciones correlacionan gestión/grado/paralelo/turno. Conteo/promedio de notas oficiales solo si existe cod_pas y permiso de calificaciones; no se infiere nota histórica. No necesita migration nueva. Tres tests SQL/negación sin DB; PDF Regente con datos reales aún pendiente.

V063/V064 requieren MIG-001/catálogos; V066 reglas formativas y destinatarios aprobados. V067 ya muestra fechas de tareas del alcance; eventos institucionales requieren MIG-005.
