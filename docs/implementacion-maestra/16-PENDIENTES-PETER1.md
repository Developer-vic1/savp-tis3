# Revisión requerida de Peter 1

1. Revisar seis propuestas de 25-MIGRATIONS-PROPUESTAS.md: necesidad, FK/tipos, índices, checks, historia y rollback; ninguna ejecutada.
2. Aprobar entorno PostgreSQL aislado, credenciales exclusivas y carga de schema/snapshot autorizada; no existe hoy. No usar institucional ni cambiar TestCase a ciegas.
3. Resolver migration histórica destructiva de entrega duplicada antes de cargar datos. Confirmar contexto del 2026-09-28 y despliegue real, sin inferir cod_pas histórico.
4. Aprobar catálogos/versiones/visibilidad/transiciones/responsables/revisión de Kardex y alcance mínimo por actor. No cargar semillas inventadas ni otorgar permisos preventivos.
5. Aprobar unidades/orden/archivo, destinatarios/dedupe/retención de notificaciones, metas personales, tipos/efectos/calendario editorial y reglas de prevención/configuración.
6. Con Peter 3, validar payload/provenance y esquema 1.0 real, semántica científica, corte y retención si se necesita persistir análisis. Likert local no es RIASEC.
7. Proporcionar datos/sesiones aislados para pruebas funcionales y UX; revisar roles/permisos aprobados y legacy. Ninguna disponibilidad de schema autoriza habilitar todos los flags.

Pendientes internos están separados en 24-BLOQUEOS-EXTERNOS.md; no se atribuyen a Peter 1 productores, editor LMS o formularios aún no implementados.
