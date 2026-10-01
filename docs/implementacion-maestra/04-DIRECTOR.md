# Director — V027–V043

WorkspaceController/InstitutionalDashboardService presentan indicadores reales según permisos. InstitutionalQueryService incluye gestión académica, cursos, estudiantes, docentes, asistencia, rendimiento, orientación local, LMS y reportes autorizados.

Notas oficiales conservan cod_pas y contexto; históricos sin contexto no se reconstruyen. HistoricalReportAccessService limita familias/formato/permiso y rutas privadas; un permiso amplio de reportes no permite SQL/ZIP ni familias desconocidas.

V042 calendario muestra plazos registrados; eventos institucionales esperan MIG-005. V035/V038 requieren seguimiento persistente MIG-001. Prevención V036 no calcula scores/umbrales sin reglas institucionales aprobadas.

Pendientes: análisis institucional/filtros/modalidades completos de la fuente, pruebas funcionales con datos y QA visual. Consultas existentes no se convierten en edición docente ni administrativa.
