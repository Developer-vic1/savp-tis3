<?php

namespace App\Support\Comunidad;

use Illuminate\Support\Str;

class DocenteInteligente
{
    public function analizarEspecialidad(?string $especialidad): array
    {
        $normalizada = Str::of((string) $especialidad)->squish()->lower()->title()->toString();
        $bloqueos = mb_strlen($normalizada) < 3 ? ['La especialidad profesional es incompleta.'] : [];

        return [
            'especialidad' => $normalizada,
            'completitud' => $bloqueos === [] ? 100 : 30,
            'bloqueos' => $bloqueos,
            'puede_guardar' => $bloqueos === [],
        ];
    }
}
