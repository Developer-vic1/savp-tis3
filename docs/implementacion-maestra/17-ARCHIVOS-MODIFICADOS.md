# Auditoría de archivos actuales

evidencia/cierre-auditoria-archivos.csv inventaría cada archivo modificado/nuevo con origen, estado, referencias estáticas, observaciones y SHA256. Estados: USED, BLOCKED, DOCUMENTATION, TEST, REMOVED_NOT_ALLOWED; sin eliminar archivos legacy. REFERENCIA estática no equivale a cobertura funcional; revisar autoload/registros antes de retirar algo no referenciado literalmente.

evidencia/cierre-baseline.csv preserva estado previo; cierre-schema-baseline.csv cubre declaraciones previas. Las vistas explorador-vocacional.blade.php y secretaria/cuentas.blade.php y PETER2_REESTRUCTURACION.patch conservan hashes de la fase anterior. No se aplicó el patch.

.env, resources/css/app.css, composer.lock, package-lock.json y migrations/seeders previos no se modificaron. app.js sí tiene cambios autorizados de toasts/tema/charts; no se presenta como preservado sin diff. No commit, push, stage, borrado ni modificación de otros worktrees.

Docs/evidencias anteriores se conservan; support-* es evidencia vigente. cierre-* describe la fase anterior, salvo cierre-auditoria-archivos.csv, regenerado con el inventario final. support-preservacion.txt y support-conciliacion-verificacion.txt registran hashes preservados y 105 IDs/orígenes sin discrepancias. git-status/diff-stat antiguos no representan el estado actual.
