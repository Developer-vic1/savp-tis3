<?php

namespace App\Services\Kardex;

use App\Contracts\KardexRepository;
use App\Models\Estudiante;
use App\Models\User;

class UnavailableKardexRepository implements KardexRepository
{
    public function available(): bool
    {
        return false;
    }

    public function timeline(User $user, Estudiante $student): array
    {
        throw new \LogicException('Faltan el contrato institucional, los catálogos y la persistencia autorizada de Kardex.');
    }
}
