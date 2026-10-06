<?php

namespace App\Services;

use App\Models\Oficial\Sistema\User;

class ReportAccessService
{
    public function authorize(?User $user, array $permissions): void
    {
        abort_unless($user && (new RoleDashboardResolver)->roleFor($user) === 'Administrador', 403);
        foreach ($permissions as $permission) {
            abort_unless($user->can($permission), 403, 'No tienes autorización para exportar este reporte.');
        }
    }
}
