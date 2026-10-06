<?php

namespace App\Policies;

use App\Models\Oficial\Academico\Estudiante;
use App\Models\Oficial\Sistema\User;
use App\Services\AcademicAccessService;
use App\Services\RoleDashboardResolver;

class EstudiantePolicy
{
    public function view(User $user, Estudiante $student): bool
    {
        return app(AcademicAccessService::class)->canViewStudent($user, $student);
    }

    public function update(User $user, Estudiante $student): bool
    {
        return in_array(app(RoleDashboardResolver::class)->roleFor($user), ['Administrador', 'Secretaria'], true) && $user->can('estudiantes.gestionar.institucional');
    }
}
