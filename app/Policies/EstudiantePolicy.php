<?php

namespace App\Policies;

use App\Models\Estudiante;
use App\Models\User;
use App\Services\AcademicAccessService;

class EstudiantePolicy
{
    public function view(User $user, Estudiante $student): bool
    {
        return app(AcademicAccessService::class)->canViewStudent($user, $student);
    }

    public function update(User $user, Estudiante $student): bool
    {
        return $user->est_usu === 'ACTIVO' && $user->hasAnyRole(['Administrador', 'Secretaria']) && $user->can('estudiantes.gestionar.institucional');
    }
}
