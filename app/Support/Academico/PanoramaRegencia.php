<?php

namespace App\Support\Academico;

use Illuminate\Support\Collection;

class PanoramaRegencia
{
    /** Cuenta personas únicas; las notas administrativas no son incidentes disciplinarios. */
    public static function contar(Collection $inscripciones, Collection $grados): array
    {
        $registros = $inscripciones->filter(fn ($i) => $grados->contains(fn ($g) => $g->cod_cur === $i->cod_cur && $g->cod_gea === $i->cod_gea));
        $total = $registros->pluck('cod_est')->unique()->count();
        $observados = $registros->filter(fn ($i) => trim((string) $i->obs_ins) !== '' || trim((string) $i->mot_obs_ins) !== '')->pluck('cod_est')->unique()->count();

        return ['estudiantes' => $total, 'observados' => $observados, 'sin_observaciones' => $total - $observados];
    }
}
