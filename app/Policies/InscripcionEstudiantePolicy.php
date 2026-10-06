<?php

namespace App\Policies;

use App\Models\Oficial\Academico\InscripcionEstudiante;
use App\Models\Oficial\Sistema\User;
use App\Services\AcademicAccessService;
use App\Services\RegencyAccessService;
use App\Services\RoleDashboardResolver;

class InscripcionEstudiantePolicy
{
    public function view(User $user, InscripcionEstudiante $enrollment): bool
    {
        if (! app(RoleDashboardResolver::class)->roleFor($user)) {
            return false;
        }
        if ($user->hasRole('Regente') && ! $user->hasAnyRole(['Administrador', 'Director', 'Secretaria'])) {
            return $user->can('inscripciones.ver.institucional')
                && app(RegencyAccessService::class)
                    ->constrain(InscripcionEstudiante::whereKey($enrollment->getKey()), $user, 'inscripcion_estudiante')->exists();
        }

        return $enrollment->estudiante
            && app(AcademicAccessService::class)->canViewStudent($user, $enrollment->estudiante);
    }

    public function update(User $user, InscripcionEstudiante $enrollment): bool
    {
        return in_array(app(RoleDashboardResolver::class)->roleFor($user), ['Administrador', 'Secretaria'], true) && $user->can('inscripciones.gestionar.institucional');
    }
}
