# Docente — V070–V086

Notas oficiales V079: TeacherGradeForm reutiliza CalificacionInteligente/GradeService con debounce 500 ms, bloqueos, completitud, desempeño y observación sugerida aplicable explícitamente en creación/revisión. Curso, análisis e ID de nota son Locked; actor/permiso e inscripción gestión/curso/paralelo/turno se comprueban antes de duplicidad. Revisar busca nota dentro del plan propio y conserva estudiante/período, excluyendo solo el ID propio. Cancelar restablece creación. Guardar vuelve a comprobar contexto en el Service. Nota fuera de rango, estudiante/nota ajenos y cambio indebido de período tienen pruebas sin BD; escala LMS variable permanece separada de la oficial 0..100. El guardado HTTP manual anterior permanece.

MisCursos y CourseWorkspace reutilizan Aula: búsqueda, gestión, paginación, tabs y contexto Locked. El propietario es Docente activo del plan. CursoEstudiante/inscripción deben coincidir en cuatro dimensiones; StudentDrawer no acepta un estudiante global por ID.

Publicaciones, materiales, actividades tip_tar, tareas, entregas, asistencia y notas reutilizan modelos existentes. EntregaPolicy exige alcance vigente incluso para entrega propia; devolver y calificar tienen permisos distintos. Revisión muestra instrucción, respuesta y feedback existentes; no mezcla puntaje LMS con nota oficial.

Reportes usan conteos reales SQL y PDF privado de curso autorizado; el test PDF genera bytes reales con datos simulados exclusivamente en pruebas. Orientación muestra actividades/progreso local reales, no estados fabricados de cursos.

V073 unidades prepara lectura/MIG-002; editor/orden/enlace aún internos. V082/V083 escritura/histórico/evidencias Kardex esperan MIG-001 y reglas aprobadas; no se registran observaciones falsas. QA y transacciones reales pendientes.
