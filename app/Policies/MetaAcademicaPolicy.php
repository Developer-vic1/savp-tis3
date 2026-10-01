<?php

namespace App\Policies;

use App\Models\MetaAcademica;
use App\Models\User;
use App\Services\AulaVirtual\CursoVirtualService;
use App\Services\RoleDashboardResolver;

class MetaAcademicaPolicy
{
    public function view(User $user, MetaAcademica $goal): bool
    {
        return app(RoleDashboardResolver::class)->roleFor($user) === 'Estudiante' && $user->can('Perfil_Academico')
            && app(CursoVirtualService::class)->estudianteDeUsuario($user)?->cod_est === $goal->cod_est;
    }

    public function update(User $user, MetaAcademica $goal): bool
    {
        return $this->view($user, $goal);
    }
}
