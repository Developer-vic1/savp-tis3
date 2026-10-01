<?php

namespace App\Policies;

use App\Models\Persona;
use App\Models\User;
use App\Services\RoleDashboardResolver;

class PersonaPolicy
{
    public function viewAny(User $user): bool
    {
        return in_array(app(RoleDashboardResolver::class)->roleFor($user), ['Administrador', 'Secretaria'], true)
            && $user->can('Registro_Personas');
    }

    public function view(User $user, Persona $persona): bool
    {
        return $this->viewAny($user);
    }

    public function create(User $user): bool
    {
        return $this->viewAny($user);
    }

    public function update(User $user, Persona $persona): bool
    {
        return $this->viewAny($user);
    }
}
