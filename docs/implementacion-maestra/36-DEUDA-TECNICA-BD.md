# Deuda técnica de datos

Estado: auditoría estática, sin cambios de schema ni correcciones de aplicación. Riesgo potencial no equivale a incidente confirmado.

## CRITICAL — 1

### DB-001 — Migration histórica borra entregas y dependencias

Evidencia: `database/migrations/2026_06_20_144115_add_unique_constraint_to_entrega_tarea_table.php`.

Acción propuesta: Prohibir replay/importación ciega; exportar duplicados íntegros y aprobar conciliación sin borrado. Usa alias global DB sin import explícito: verificar resolución en bootstrap autorizado, no error runtime confirmado. down no recupera filas.

Validación futura: datos sintéticos y transacciones concurrentes en PostgreSQL aislado aprobado; conciliación del histórico por el responsable. No se ejecuta en esta fase.

## HIGH — 10

### DB-002 — Cascadas de identidad y nota oficial amenazan histórico

Evidencia: `persona -> users/personal/estudiante; calificacion.cod_est`.

Acción propuesta: Estados en dominio y FK restrictivas futuras; ninguna cascada corregida ahora.

Validación futura: datos sintéticos y transacciones concurrentes en PostgreSQL aislado aprobado; conciliación del histórico por el responsable. No se ejecuta en esta fase.

### DB-003 — Morph Sanctum bigint incompatible con cod_usu textual

Evidencia: `personal_access_tokens.tokenable_id; vendor Schema Builder/Blueprint`.

Acción propuesta: Alter conservador después de perfilado y contrato de morph; no migrar User a bigint.

Validación futura: datos sintéticos y transacciones concurrentes en PostgreSQL aislado aprobado; conciliación del histórico por el responsable. No se ejecuta en esta fase.

### DB-004 — CI UNIQUE solo número contradice posible CI/complemento

Evidencia: `persona.ci_per/com_per; PersonaInteligente.buscarCoincidencias`.

Acción propuesta: Resolver regla institucional y normalización; no quitar UNIQUE ni crear compuesto sin perfilado.

Validación futura: datos sintéticos y transacciones concurrentes en PostgreSQL aislado aprobado; conciliación del histórico por el responsable. No se ejecuta en esta fase.

### DB-005 — Generación secuencial por último código no serializa escritores

Evidencia: `Models Persona/Docente/planes/Bitacora::creating`.

Acción propuesta: Conservar PK; usar generador único/transacción o ID aleatorio compatible; probar carrera en PG aislado.

Validación futura: datos sintéticos y transacciones concurrentes en PostgreSQL aislado aprobado; conciliación del histórico por el responsable. No se ejecuta en esta fase.

### DB-006 — hasOne de perfiles no garantizado por UNIQUE

Evidencia: `personal_institucional.cod_per; docente/director/secretaria/regente/administrador.cod_pin`.

Acción propuesta: Decidir vínculo temporal frente a perfil único; reconciliar duplicates sin borrarlos.

Validación futura: datos sintéticos y transacciones concurrentes en PostgreSQL aislado aprobado; conciliación del histórico por el responsable. No se ejecuta en esta fase.

### DB-007 — Checks NOT VALID no equivalen a conservación sin efectos

Evidencia: `MIG-006 y GradeService/EntregaService/OrientacionService`.

Acción propuesta: Separar restricciones; perfilar rangos y actualizaciones históricas antes de autorizar.

Validación futura: datos sintéticos y transacciones concurrentes en PostgreSQL aislado aprobado; conciliación del histórico por el responsable. No se ejecuta en esta fase.

### DB-008 — Orientación sin edición/instrumento/algoritmo reproducibles

Evidencia: `orientacion_preguntas/respuestas/resultados; OrientacionService.finalizar`.

Acción propuesta: Versionar instrumento y algoritmo; congelar preguntas de intento finalizado; no convertir Likert 1..5 en RIASEC.

Validación futura: datos sintéticos y transacciones concurrentes en PostgreSQL aislado aprobado; conciliación del histórico por el responsable. No se ejecuta en esta fase.

### DB-009 — Reporte legado fabrica compatibilidad y perfiles a partir de notas

Evidencia: `DatosReporteVocacionalService.calcularCompatibilidad / riasecPorEspecialidad`.

Acción propuesta: No persistir ni publicar como medición; reemplazo funcional mediante contrato especializado aprobado y evidencia válida.

Validación futura: datos sintéticos y transacciones concurrentes en PostgreSQL aislado aprobado; conciliación del histórico por el responsable. No se ejecuta en esta fase.

### DB-010 — Writers directos de auditoría eluden BitacoraService

Evidencia: `TurnoInteligente:2249; GestionCurso:3648; GestionAcademica:1722; GestionTurnos:2317; GestionInscripciones:2762`.

Acción propuesta: Unificar cinco writers legacy con BitacoraService y redactar PII; nunca agregar segunda bitácora.

Validación futura: datos sintéticos y transacciones concurrentes en PostgreSQL aislado aprobado; conciliación del histórico por el responsable. No se ejecuta en esta fase.

### DB-011 — Kardex todavía obliga plan/fecha sin hora para todo hecho

Evidencia: `MIG-001; ScopedKardexRepository`.

Acción propuesta: Definir contexto de incidente general, evento vs captura, versión coherente, permisos y revisiones append-only.

Validación futura: datos sintéticos y transacciones concurrentes en PostgreSQL aislado aprobado; conciliación del histórico por el responsable. No se ejecuta en esta fase.

## MEDIUM — 7

### DB-012 — Estado global del período no expresa calendario anual

Evidencia: `periodo_evaluacion; GradeService`.

Acción propuesta: Extensión anual condicionada a contrato, sin inventar fechas ni reescribir notas históricas.

Validación futura: datos sintéticos y transacciones concurrentes en PostgreSQL aislado aprobado; conciliación del histórico por el responsable. No se ejecuta en esta fase.

### DB-013 — Asistencia UNIQUE con bloque nullable y estado

Evidencia: `asistencia_clase uq_asistencia_clase_bloque_estado; AsistenciaService.firstOrCreate`.

Acción propuesta: Diseñar identidad de sesión estable y política NULL; el lock de clase ayuda, no sustituye integridad fuera del Service.

Validación futura: datos sintéticos y transacciones concurrentes en PostgreSQL aislado aprobado; conciliación del histórico por el responsable. No se ejecuta en esta fase.

### DB-014 — Campos de contexto redundantes carecen de garantía cruzada

Evidencia: `calificacion.cod_asi/cod_pas; calificacion_tarea; orientacion_respuestas.cod_est`.

Acción propuesta: Mantener compatibilidad; comprobar coincidencia en Service, considerar FK compuestas solo después de reconciliación.

Validación futura: datos sintéticos y transacciones concurrentes en PostgreSQL aislado aprobado; conciliación del histórico por el responsable. No se ejecuta en esta fase.

### DB-015 — Capacidad referencial y consultas de tablas/columnas no declaradas

Evidencia: `InscripcionAcademica capacidadParalelo/cap_esp_tec; GestionAcademicaInteligente reporte/calificacion_estudiante`.

Acción propuesta: Capacidad es configuración anual, no dato derivable; no crear tabla reporte para satisfacer fallback; corregir JOIN a cod_pas y alias.

Validación futura: datos sintéticos y transacciones concurrentes en PostgreSQL aislado aprobado; conciliación del histórico por el responsable. No se ejecuta en esta fase.

### DB-016 — ProgresoCurso mezcla entregas de distintos estudiantes

Evidencia: `ProgresoCursoService.porcentaje`.

Acción propuesta: Definir métrica por estudiante y total de tareas elegibles; no persistir valor agregado defectuoso.

Validación futura: datos sintéticos y transacciones concurrentes en PostgreSQL aislado aprobado; conciliación del histórico por el responsable. No se ejecuta en esta fase.

### DB-017 — Exportación SQL parcial no es respaldo restaurable

Evidencia: `GeneradorSqlAcademicoService.tablas/generar`.

Acción propuesta: Faltan catálogos/planes y orden FK completo, DDL/consistencia; separar exportación de backup validado sin importar el archivo.

Validación futura: datos sintéticos y transacciones concurrentes en PostgreSQL aislado aprobado; conciliación del histórico por el responsable. No se ejecuta en esta fase.

### DB-018 — Factories y suite SQLite no certifican schema PostgreSQL

Evidencia: `UserFactory.name/Team; phpunit.xml DB_CONNECTION sqlite force`.

Acción propuesta: Plan separado para PG aislado, Persona/cod_usu requeridos; no cambiar guardas ahora.

Validación futura: datos sintéticos y transacciones concurrentes en PostgreSQL aislado aprobado; conciliación del histórico por el responsable. No se ejecuta en esta fase.

## LOW — 2

### DB-019 — Índices aislados de baja selectividad y checks de rangos ausentes

Evidencia: `estado_asistencia flags/horarios/estados`.

Acción propuesta: Priorizar planes reales y compuestos; no agregar o retirar índices automáticamente.

Validación futura: datos sintéticos y transacciones concurrentes en PostgreSQL aislado aprobado; conciliación del histórico por el responsable. No se ejecuta en esta fase.

### DB-020 — Fotos y campos framework sin contrato de precedencia

Evidencia: `persona.fot_per; users.avatar/profile_photo_path/current_team_id`.

Acción propuesta: Mantener; current_team_id candidato de retiro de columna, no tabla ni pérdida de fotos.

Validación futura: datos sintéticos y transacciones concurrentes en PostgreSQL aislado aprobado; conciliación del histórico por el responsable. No se ejecuta en esta fase.

## Límites de las conclusiones

No se declaran tablas retirables por ausencia de caller. Se incluyen modelos, Services, Support, controllers, Livewire, rutas, vistas, tests, factories y seeders; llamadas dinámicas y contratos de framework se señalan. Solo current_team_id es candidato de retiro **de columna**, sujeto a demostrar que equipos siguen desactivados y a revisar integraciones. No se propone borrar ninguna tabla ni migration histórica.

No se han medido cardinalidades, duplicados, tiempos, planes EXPLAIN, tamaño de índices, rangos históricos ni retención real en PostgreSQL. Cualquier UNIQUE/CHECK nuevo necesita perfilado autorizado posterior. El volcado histórico se analiza solo como DDL, nunca como permiso para importar datos personales.
