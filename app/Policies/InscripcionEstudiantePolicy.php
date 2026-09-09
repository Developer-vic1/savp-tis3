<?php

namespace App\Policies;

use App\Models\InscripcionEstudiante;
use App\Models\User;

class InscripcionEstudiantePolicy
{
    public function gestionar(User $user, InscripcionEstudiante $inscripcion): bool
    {
        return $user->can('Inscripciones');
    }

    public function corregir(User $user, InscripcionEstudiante $inscripcion): bool
    {
        return $user->hasAnyRole(['Administrador', 'Director']);
    }

    public function delete(User $user, InscripcionEstudiante $inscripcion): bool
    {
        return false;
    }
}
