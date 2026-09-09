<?php

namespace App\Policies;

use App\Models\SeguimientoAcademico;
use App\Models\User;

class SeguimientoAcademicoPolicy
{
    public function create(User $user): bool
    {
        return $user->hasAnyRole(['Administrador', 'Director', 'Regente', 'Secretaria']);
    }

    public function view(User $user, SeguimientoAcademico $seguimiento): bool
    {
        return $this->create($user) && ($seguimiento->vis_seg !== 'RESTRINGIDO' || $user->hasAnyRole(['Administrador', 'Director']));
    }

    public function update(User $user, SeguimientoAcademico $seguimiento): bool
    {
        return $this->view($user, $seguimiento);
    }
}
