<?php

namespace App\Services;

use App\Models\User;

class RoleDashboardResolver
{
    private const ROUTES = [
        'Administrador' => 'admin.dashboard',
        'Director' => 'direccion.dashboard',
        'Secretaria' => 'secretaria.dashboard',
        'Regente' => 'regencia.dashboard',
        'Docente' => 'docente.dashboard',
        'Estudiante' => 'estudiante.dashboard',
    ];

    public function routeFor(User $user): ?string
    {
        foreach (self::ROUTES as $role => $route) {
            if ($user->hasRole($role)) {
                return $route;
            }
        }

        return null;
    }

    public function roleFor(User $user): ?string
    {
        foreach (array_keys(self::ROUTES) as $role) {
            if ($user->hasRole($role)) {
                return $role;
            }
        }

        return null;
    }
}
