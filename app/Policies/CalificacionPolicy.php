<?php

namespace App\Policies;

use App\Models\Calificacion;
use App\Models\User;
use App\Services\AcademicAccessService;

class CalificacionPolicy
{
    public function view(User $user, Calificacion $grade): bool
    {
        return app(AcademicAccessService::class)->canViewGrade($user, $grade);
    }

    public function update(User $user, Calificacion $grade): bool
    {
        return app(AcademicAccessService::class)->canManageGrade($user, $grade->cod_est, $grade->cod_asi, $grade->cod_pas);
    }
}
