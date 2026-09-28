<?php

namespace App\Support;

final class PermissionLabel
{
    public static function describe(string $name): array
    {
        $parts = explode('.', $name);
        $domain = str_replace(['_', '-'], ' ', $parts[0]);
        $action = $parts[1] ?? 'acceder';
        $scope = $parts[2] ?? '';

        return [
            'domain' => ucfirst($domain),
            'action' => $action,
            'scope' => $scope,
            'label' => ucfirst(str_replace('_', ' ', $action)).' '.mb_strtolower($domain),
            'scope_label' => match ($scope) {
                'global' => 'Toda la plataforma',
                'institucional' => 'Institución',
                'curso', 'asignados' => 'Cursos asignados',
                'propio', 'propia', 'propios', 'propias' => 'Información propia',
                default => count($parts) === 1 ? 'Compatibilidad con módulo existente' : 'Según autorización de la operación',
            },
            'critical' => in_array($name, ['Panel_Administrador', 'Gestion_Roles_Permisos', 'Gestion_Usuarios', 'Bitacora', 'roles-permisos.gestionar', 'usuarios.asignar_roles', 'bitacora.ver.global', 'calificaciones.gestionar.global', 'calificaciones.rectificar', 'orientacion.configurar'], true)
                || str_starts_with($name, 'roles.')
                || in_array($action, ['desactivar', 'reset_password', 'eliminar'], true),
        ];
    }
}
