<?php

namespace App\Policies;

use App\Models\AulaVirtual\OrientacionResultado;
use App\Models\User;
use App\Services\AcademicAccessService;

class OrientacionResultadoPolicy
{
    public function view(User $user, OrientacionResultado $result): bool
    {
        if ($user->hasRole('Regente') && ! $user->hasAnyRole(['Administrador', 'Director'])) {
            $year = $result->actividad?->cod_gea;
            return $year && $user->can('orientacion.ver.institucional')
                && app(\App\Services\RegencyAccessService::class)->constrain(
                    \App\Models\InscripcionEstudiante::where('cod_est', $result->cod_est)->where('cod_gea', $year), $user, 'inscripcion_estudiante')->exists();
        }
        return $user->est_usu === 'ACTIVO' && $user->canAny(['orientacion.ver.propia', 'orientacion.ver.curso', 'orientacion.ver.institucional']) && $result->estudiante
            && app(AcademicAccessService::class)->canViewStudent($user, $result->estudiante);
    }

    public function update(User $user, OrientacionResultado $result): bool
    {
        return $user->est_usu === 'ACTIVO' && $user->hasRole('Administrador') && $user->can('orientacion.configurar');
    }
}
