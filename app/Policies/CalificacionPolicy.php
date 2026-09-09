<?php

namespace App\Policies;

use App\Models\PlanAsignatura;
use App\Models\User;
use App\Services\AulaVirtual\CursoVirtualService;

class CalificacionPolicy
{
    public function registrar(User $user, PlanAsignatura $plan): bool
    {
        if (! $user->can('Calificaciones')) {
            return false;
        }
        if ($user->hasAnyRole(['Administrador', 'Director'])) {
            return true;
        }

        return $user->hasRole('Docente') && $plan->cod_doc === app(CursoVirtualService::class)->docenteDeUsuario($user)?->cod_doc;
    }

    public function rectificar(User $user, PlanAsignatura $plan): bool
    {
        return $user->hasAnyRole(['Administrador', 'Director']);
    }
}
