# Auditoría del modelo y schema declarado

Este documento conserva el snapshot de preparación. La auditoría maestra vigente está en [31-AUDITORIA-BD-MAESTRA.md](31-AUDITORIA-BD-MAESTRA.md), con inventarios completos de tablas/columnas, comparación DDL del volcado histórico, revisión superior de las seis proposals en [25](25-MIGRATIONS-PROPUESTAS.md) y modelo objetivo propuesto en [35](35-MODELO-DATOS-OBJETIVO.md). No interpretar las propuestas iniciales inferiores como aprobación arquitectónica ni de ejecución.

Revisión estática de 52 migrations previas y 51 modelos del snapshot de preparación (ya incluía Role canónico), con relaciones/casts/estados/servicios. Se añadieron seis migrations propuestas y cuatro modelos de dominio preparados (SeguimientoAcademico, UnidadClase, MetaAcademica, CalendarioEvento): hay 58 migrations y 55 modelos actuales. No se inspeccionaron filas/schema desplegado.

Evidencia: cierre-schema-baseline.csv (hashes previos), cierre-schema-referencias.txt (FK/check/índices), cierre-modelos-relaciones.txt y cierre-modelos-schema.csv (tabla/migration/relaciones/casts por modelo). Role hereda tabla configurada Spatie; RoleRequest usa convención role_requests. No concluir tabla ausente solo porque un modelo no declara $table.

| Dominio | Reutiliza | Preparación necesaria |
|---|---|---|
| Administración | Persona, User/Role, personal y catálogos, documento_inscripcion_estudiante | Ninguna tabla duplicada; privacidad de nuevos PDFs |
| Académico | GestionAcademica, Curso, PlanAsignatura, Inscripcion, PeriodoEvaluacion | Contexto cod_pas/regente_asignaciones ya declarado antes; comprobar aplicación futura |
| LMS | Clase/ClaseEstudiante/Publicacion/Material/Tarea/Entrega/Archivo | MIG-002 unidades/relación, conservando recursos con NULL |
| Asistencia | AsistenciaClase/Estudiante/EstadoAsistencia | Ninguna tabla nueva; locks y alcance en servicio |
| Calificaciones | Calificacion oficial y CalificacionTarea LMS | MIG-006 CHECK NOT VALID y FK restrictiva de nota oficial |
| Seguimiento/Kardex | No hay raíz equivalente completa en este checkout | MIG-001 raíz seguimiento/catálogos versionados/revisiones/evidencias |
| Orientación | Actividad/Pregunta/Respuesta/Resultado/Carrera locales | MIG-004 solo metas propias; no trasladar Likert a Peter 3 |
| Calendario | Fechas de Tarea actuales | MIG-005 eventos institucionales/revisiones |
| Notificaciones | User Notifiable actual, sin tabla notifications | MIG-003; no usar Bitácora como avisos leídos |
| Peter 3 | HTTP DTO/fallback, respuesta efímera minimizada | Ninguna tabla preventiva de preguntas/resultados sin contrato de retención |

Hallazgos: FK calificacion.cod_est originalmente CASCADE DELETE; propuesta MIG-006 la restringe. Escalas actuales solo protegidas en aplicación: CHECK NOT VALID conserva históricos y condiciona nuevas escrituras. Migration histórica 2026_06_20_144115 elimina entregas duplicadas e hijos: no ejecutada/modificada, requiere reconciliación autorizada antes de cualquier carga futura. PK string(20) se conserva en FK nuevas, incluida identidad de notificaciones; no usar morph bigint sobre cod_usu.

Riesgos legacy: generación secuencial de algunos códigos por último registro necesita revisar concurrencia; un índice/FK declarado no acredita integridad ni rendimiento desplegados. No se infiere cod_pas/gestión de created_at. No se transformaron datos ni archivos históricos públicos. Rollbacks de tablas propuestas solo admiten tablas vacías, evitando borrar registros.
