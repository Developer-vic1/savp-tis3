# Bloqueos externos y pendientes internos

## BLOCKED_EXTERNALLY_DB — solo persistencia nueva

V022, V035, V038, V055, V063, V064, V082, V083, V098: MIG-001 seguimiento/Kardex y catálogos versionados. V102: MIG-004 metas personales. Estado de escritura/lectura nueva: no habilitado, no aplicado. Además de DDL requieren catálogo/reglas y pruebas. No existe base PG testing aprobada.

V073/V090 (MIG-002), calendarios V016/V042/V054/V067/V081/V096 (MIG-005) y campana transversal (MIG-003) tienen componentes nuevos que necesitan DDL, mientras publicaciones/plazos/workspaces existentes siguen desarrollándose. Por ello su ventana completa conserva PARTIAL; no se atribuye bloqueo total a PG testing.

## BLOCKED_EXTERNALLY_INSTITUTIONAL — definición institucional

V024/V025: parámetros/configuración formal y permisos; no se crea tabla de opciones genéricas. V036/V066: reglas preventivas/alertas, responsables, destinatarios, evidencia y revisión humana; no se inventa score ni sanción automática.

Aporte Ingenieril SAVP limita validación científica real de V100/V103/V104; adapter/fallback/contrato HTTP 1.0 están preparados y testeados con fakes. No requiere ahora tabla nueva. Snapshot persistente solo después de contrato/version/hash/corte/retención aprobados.

## Pendientes internos — no ocultarlos como externos

1. Escribir, rectificar/anular, revisar y descargar evidencia/timeline Kardex tras reglas aprobadas; actualmente register devuelve 409 y pantallas son disponibilidad.
2. Editor/reordenamiento/asignación y visibilidad de recursos de unidades; lectura preparada no es gestión curricular terminada.
3. Productores/notificaciones post-commit con dedupe y alcance revocable; campana/listado/leído no acreditan eventos reales.
4. CRUD editorial y reglas de solapamiento/calendario; consulta de tareas/eventos no es gestión editorial.
5. Certificar cada acción/modalidad de CRUD legacy, filtros y modales/drawers completos, recuperación/loading y acceso de teclado.
6. Pruebas reales de transacciones/concurrencia/históricos y QA autenticada clara/oscura/responsive; mocks y build no las reemplazan.

No marcar 91 PARTIAL como BLOCKED_EXTERNALLY_DB únicamente porque falta entorno de pruebas; completar código y conservar trazabilidad de validación pendiente. Flags continúan false. No pedir aprobación para ejecutar algo no preparado/revisable.
