<?php

namespace App\Contracts;

use App\Models\Estudiante;
use App\Models\User;

/** Una implementación debe preservar registro, evidencia, rectificación y anulación. */
interface KardexRepository
{
    public function available(): bool;

    public function timeline(User $user, Estudiante $student): array;
}
