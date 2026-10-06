<?php

namespace App\Policies;

use App\Models\Oficial\Academico\Curso;
use App\Models\Oficial\Sistema\User;
use App\Services\AcademicAccessService;
use App\Services\RoleDashboardResolver;

class CursoPolicy
{
    public function view(User $user, Curso $course): bool
    {
        if (app(RoleDashboardResolver::class)->roleFor($user) === 'Docente' && \App\Support\AccesoGestionCursos::permite($user)) return true;
        return app(AcademicAccessService::class)->canViewCourse($user, $course);
    }

    public function update(User $user, Curso $course): bool
    {
        return app(RoleDashboardResolver::class)->roleFor($user) === 'Administrador'
            ? $user->canAny(['cursos.gestionar.global', 'Cursos'])
            : \App\Support\AccesoGestionCursos::permite($user);
    }
}
