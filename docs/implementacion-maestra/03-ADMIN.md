# Administrador — V001–V026

Reutiliza CRUD institucional existente; InstitutionalAuthorization revalida actor activo y permiso tanto al montar como al invocar acciones Livewire. RolePermissionService conserva la única escritura operacional de rol; protege último Administrador y mutaciones de la propia identidad. app/Models/Role.php es la identidad Role configurada en Spatie.

GestionDocente usa conteos y estado de especialidad real; se quitó el porcentaje artificial de completitud. GestionInscripciones conserva documento_inscripcion_estudiante y prepara nuevos PDFs en disco privado, con hash/tamaño/descarga autorizada. Los documentos históricos públicos no se movieron.

V023 consulta LMS real; V016 tiene plazos existentes y lectura futura de eventos. V022 parámetros/auditoría Kardex necesita MIG-001 y catálogo formal. V024/V025 necesitan definición de parámetros, permisos y reglas; no se crea un almacén genérico de opciones.

Pendientes internos: revisar exhaustivamente acciones/modalidades de cada CRUD legacy y uniformar modales, filtros y manejo de errores. El hook común no acredita autorización campo por campo de todos los auxiliares.
