<?php

namespace App\Services;

use App\Models\Oficial\Sistema\Role;
use App\Models\Oficial\Sistema\User;

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
        $role = $this->roleFor($user);

        return $role ? self::ROUTES[$role] : null;
    }

    public function roleFor(User $user): ?string
    {
        if ($user->est_usu !== 'ACTIVO') {
            return null;
        }

        $actors = [];
        foreach (Role::INSTITUTIONAL as $role) {
            if ($user->hasRole($role)) {
                $actors[] = $role;
            }
        }

        return count($actors) === 1 ? $actors[0] : null;
    }
}
