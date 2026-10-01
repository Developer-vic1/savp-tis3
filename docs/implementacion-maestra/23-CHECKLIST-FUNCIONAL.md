# Checklist verificable

| Verificación | Resultado actual | Evidencia / límite |
|---|---|---|
| IDs/nombres/actores/tipos/rutas propuestas originales | Conservados 105/105 | script conciliar-catalogo.ps1 valida literalmente |
| Catálogo fuente preservado | 43/43 hashes iguales | inventario-arquitectura-original.csv / cierre-preservacion.txt |
| Actor único activo/permiso complementario | Tests de frontera PASS | mocks, sin catálogo de BD real |
| Curso/estudiante ajeno y contexto Locked | Negativas PASS | WorkspaceHttpBoundary/WorkspaceIntegrity |
| Regencia gestión+grado/inscripción cuatro dimensiones | SQL de builder PASS | InstitutionalScopeContract/RegencyReportScope, sin ejecutar SQL |
| Entrega propia con alcance revocado/calificar vs devolver | Contratos PASS | SubmissionAuthorization |
| Reportes históricos/familia/path | Negativas PASS | HistoricalReportAuthorization |
| PDF Docente privado | PDF real de prueba PASS | conteos simulados; PG pendiente |
| Nuevas persistencias deshabilitadas | Tests PASS | PreparedPersistenceBoundary, no queries de tabla futura |
| Peter 3/fallback/Locked | HTTP fake/Livewire PASS | sin servicio real ni ciencia certificada |
| Sintaxis seis migrations | php -l PASS | no DDL/up/down/rollback ejecutado |
| Build/Blade/Pint/Composer | PASS de construcción | evidencia cierre-* |
| CRUD/transacciones/FK/concurrencia con PG | PENDIENTE | base aislada no existe |
| Toda acción/modalidad de 105 ventanas | PENDIENTE | matriz 91 PARTIAL, no claim PASS |
| Productores notificaciones / editor unidades / Kardex CRUD / calendario editorial | PENDIENTE INTERNO | describir y terminar antes de habilitar |
| UX real seis actores claro/oscuro/responsive | PENDIENTE | sin sesiones/capturas autenticadas |
| Institucional/migrations/seeders/commit/push | NO MODIFICADO / NO EJECUTADO | cierre-preservacion y Git |
