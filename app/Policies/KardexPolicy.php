<?php

namespace App\Policies;

use App\Models\Oficial\Academico\Estudiante;
use App\Models\Oficial\Sistema\User;
use App\Services\AcademicAccessService;
use App\Services\RoleDashboardResolver;

/** Permisos propuestos: no se crean ni conceden automáticamente. */
class KardexPolicy
{
    public function view(User $user, Estudiante $student): bool
    {
        $actor = app(RoleDashboardResolver::class)->roleFor($user);
        $permission = match ($actor) {
            'Administrador' => 'kardex.ver.global', 'Director' => 'kardex.ver.institucional', 'Secretaria' => 'kardex.ver.administrativo',
            'Regente' => 'kardex.ver.grado', 'Docente' => 'kardex.ver.curso', 'Estudiante' => 'kardex.ver.propio', default => null,
        };

        return $permission && $user->can($permission) && app(AcademicAccessService::class)->canViewStudent($user, $student);
    }

    public function create(User $user, Estudiante $student): bool
    {
        return app(RoleDashboardResolver::class)->roleFor($user) === 'Docente' && $user->can('kardex.registrar.curso')
            && app(AcademicAccessService::class)->canViewStudent($user, $student);
    }
}
