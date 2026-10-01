# Seis workspaces canónicos

Administrador, Director, Secretaria, Regente, Docente y Estudiante usan una identidad institucional activa inequívoca. RoleDashboardResolver y EnsureActorRole cierran acceso para cero/dos actores o cuenta inactiva; permisos complementarios no cambian el workspace.

Un shell reutilizado: layouts.app y aula-virtual.layouts.app. WorkspaceNavigation filtra rutas existentes y permisos; ModuleSearch comparte esos enlaces. Sidebar, navegación móvil, perfil y tema usan las utilidades existentes. NotificationCenter está preparado pero cerrado por flag hasta aprobación de persistencia/productores.

Director conserva consulta institucional, Secretaria operación administrativa autorizada, Regente solo gestión/grado asignados. Docente posee el plan/clase y Estudiante posee perfil/vínculo/inscripción. Ningún dashboard presenta datos de prueba en producción.

Pendiente: comprobar con sesiones reales seis actores, permisos revocados, sesiones móviles y estados de recuperación. Los tests con mocks prueban fronteras, no todo el workspace con datos.
