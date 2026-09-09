<?php

namespace App\Policies;

use App\Models\CalendarioEvento;
use App\Models\User;

class CalendarioEventoPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('Gestion_Academica');
    }

    public function view(User $user, CalendarioEvento $evento): bool
    {
        return $this->viewAny($user);
    }

    public function create(User $user): bool
    {
        return $this->viewAny($user) && $user->hasAnyRole(['Administrador', 'Director', 'Secretaria']);
    }

    public function update(User $user, CalendarioEvento $evento): bool
    {
        $preventivo = in_array($evento->est_cae, ['BORRADOR', 'PREALERTA', 'PENDIENTE_APROBACION'], true) && $evento->fii_cae?->greaterThanOrEqualTo(today());

        return $this->create($user) && ($preventivo || $this->correct($user, $evento));
    }

    public function confirmar(User $user, CalendarioEvento $evento): bool
    {
        return $this->confirm($user, $evento);
    }

    public function confirm(User $user, CalendarioEvento $evento): bool
    {
        return $this->viewAny($user) && $user->hasAnyRole(['Administrador', 'Director']);
    }

    public function cancel(User $user, CalendarioEvento $evento): bool
    {
        return $this->confirm($user, $evento);
    }

    public function correct(User $user, CalendarioEvento $evento): bool
    {
        return $this->confirm($user, $evento);
    }

    public function delete(User $user, CalendarioEvento $evento): bool
    {
        return false;
    }
}
