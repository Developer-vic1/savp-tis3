<?php

namespace App\Policies;

use App\Models\Curso;
use App\Models\User;
use App\Services\AcademicAccessService;

class CursoPolicy
{
    public function view(User $user, Curso $course): bool
    {
        return app(AcademicAccessService::class)->canViewCourse($user, $course);
    }

    public function update(User $user, Curso $course): bool
    {
        return $user->est_usu === 'ACTIVO' && $user->hasRole('Administrador') && $user->canAny(['cursos.gestionar.global', 'Cursos']);
    }
}
