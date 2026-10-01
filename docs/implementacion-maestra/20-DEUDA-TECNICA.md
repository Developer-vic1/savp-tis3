# Deuda y límites verificables

Trabajo interno pendiente: editor/reordenamiento/enlace de unidades y su visibilidad; productores académicos de notificaciones post-commit/dedupe/revocación; escrituras/revisión/evidencias/timeline Kardex bajo contrato; CRUD editorial calendario; revisión exhaustiva de CRUD/modales legacy, filtros/modalidades de cada ventana y recuperación de errores. No son bloqueos de testing DB por sí solos.

Testing externo: PG aislado aún inexistente/no aprobado, catálogos/reglas institucionales pendientes y servicio Peter 3 real. No certificar transacciones/scopes SQL/DDL por tener mocks/build. QA autenticada y responsive/dark pendiente; no hay capturas de sesiones reales.

Auditoría NPM actual: 11 paquetes vulnerables, 1 low/2 moderate/6 high/2 critical; critical: shell-quote transitivo y concurrently directo vía shell-quote. Otros directos: axios, postcss, vite. evidencia/cierre-npm-audit.json y resumen.csv registran avisos completos. npm ci preservó lockfiles; no npm audit fix ni actualización arbitraria. Plan separado: revisar actualizaciones compatibles, ejecutar regresión y documentar diff de dependencias antes de publicar.

Legacy: algunos códigos usan último registro para secuencia y pueden colisionar concurrentemente; documentación histórica pública no movida; migration histórica de entregas elimina duplicados e hijos y no recupera datos en down; periodos actuales no aportan por sí solos calendario/gestión de cierre. Registrar y resolver con revisión de dominio/datos, sin modificación institucional automática.
