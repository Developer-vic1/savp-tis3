<?php

namespace App\Policies;

use App\Models\InscripcionEstudiante;
use App\Models\User;
use App\Services\AcademicAccessService;

class InscripcionEstudiantePolicy
{
    public function view(User $user, InscripcionEstudiante $enrollment): bool
    {
        if ($user->est_usu !== 'ACTIVO') { return false; }
        if ($user->hasRole('Regente') && ! $user->hasAnyRole(['Administrador', 'Director', 'Secretaria'])) {
            return $user->can('inscripciones.ver.institucional')
                && app(\App\Services\RegencyAccessService::class)
                    ->constrain(InscripcionEstudiante::whereKey($enrollment->getKey()), $user, 'inscripcion_estudiante')->exists();
        }
        return $enrollment->estudiante
            && app(AcademicAccessService::class)->canViewStudent($user, $enrollment->estudiante);
    }

    public function update(User $user, InscripcionEstudiante $enrollment): bool
    {
        return $user->est_usu === 'ACTIVO' && $user->hasAnyRole(['Administrador', 'Secretaria']) && $user->can('inscripciones.gestionar.institucional');
    }
}
