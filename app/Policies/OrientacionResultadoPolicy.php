<?php

namespace App\Policies;

use App\Models\AulaVirtual\OrientacionResultado;
use App\Models\InscripcionEstudiante;
use App\Models\User;
use App\Services\AcademicAccessService;
use App\Services\RegencyAccessService;
use App\Services\RoleDashboardResolver;

class OrientacionResultadoPolicy
{
    public function view(User $user, OrientacionResultado $result): bool
    {
        if (! app(RoleDashboardResolver::class)->roleFor($user)) {
            return false;
        }
        if ($user->hasRole('Regente') && ! $user->hasAnyRole(['Administrador', 'Director'])) {
            $year = $result->actividad?->cod_gea;

            return $year && $user->can('orientacion.ver.institucional')
                && app(RegencyAccessService::class)->constrain(
                    InscripcionEstudiante::where('cod_est', $result->cod_est)->where('cod_gea', $year), $user, 'inscripcion_estudiante')->exists();
        }

        return $user->est_usu === 'ACTIVO' && $user->canAny(['orientacion.ver.propia', 'orientacion.ver.curso', 'orientacion.ver.institucional']) && $result->estudiante
            && app(AcademicAccessService::class)->canViewStudent($user, $result->estudiante);
    }

    public function update(User $user, OrientacionResultado $result): bool
    {
        return app(RoleDashboardResolver::class)->roleFor($user) === 'Administrador' && $user->can('orientacion.configurar');
    }
}
