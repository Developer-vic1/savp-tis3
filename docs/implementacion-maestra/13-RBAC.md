# RBAC e identidad

Una identidad Role: app/Models/Role.php extiende Spatie Role y config/permission.php la configura. INSTITUTIONAL contiene los seis actores canónicos; alias antiguos no crean séptimo workspace. No se inventó columna est_rol.

RolePermissionService es el único escritor operacional de roles; RoleRequestService conserva el mismo role_id al resolver solicitud, con locks y transición autorizada. Se preservan permisos complementarios, último Admin y protección de identidad propia. Seeders existentes no fueron modificados ni ejecutados.

Permiso de módulo no sustituye actor/ownership/contexto. Regente necesita asignación por gestión/grado. Director no se vuelve Docente por permiso amplio. EntregaPolicy separa returnForCorrection de grade; historial PDF exige familia/formato autorizados. Kardex/metas tienen Policy específica.

Permisos nuevos/propuestos no fueron creados ni concedidos. Se probaron negativas/contratos con mocks; verificar catálogo aprobado, revocación/cache, scopes y operaciones bajo datos aislados antes de conceder permisos reales.

PersonaPolicy reutiliza Registro_Personas y limita análisis/acciones a Administrador o Secretaría activos. No borra bloqueos al editar; el ID propio es Locked y se excluye de las tres consultas de coincidencias. Secretaría no monta CRUD de Cursos/Turnos/Gestión; authorizeQuery limita sus lecturas. Los nuevos drawers y previews docentes comprueban actor/módulo antes de consultas. Ninguna sugerencia crea o concede un permiso.
