# LMS: reutilización e integridad

ClaseVirtual/ClaseEstudiante, PublicacionClase, MaterialClase, Tarea, EntregaTarea/Archivo, CalificacionTarea y AsistenciaClase/Estudiante se conservan. Tarea.tip_tar representa práctica/proyecto/etc.; ActividadClase es log, no otra actividad curricular. No hay segundo LMS.

Services Material/Tarea/Entrega/Asistencia/Publicacion validan actor, permiso y clase autorizada; locks de clase preceden hijos para reducir conflictos. Archivos privados, validación de extensión/tamaño/hash, cleanup al fallar, bitácora y estados explícitos. UI con errores/loading/empty no sustituye autorización en servidor.

MIG-002 añade unidades_clase y unidad_id nullable en recursos existentes, sin copiar/backfill. FK compuesta unidad+clase evita referencias cruzadas; archivo conserva recursos. UnitContentService y vista de lectura preparados; editor/orden/asignación de recursos y efectos sobre visibilidad pendientes antes de habilitar.

MIG-006 añade restricciones de escala con NOT VALID, conservando anomalías para revisión. Concurrencia, FK, índices/planes SQL y DDL requieren PostgreSQL aislado aprobado.
