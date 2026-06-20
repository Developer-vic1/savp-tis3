<?php

namespace App\Services\AulaVirtual;

use App\Models\AulaVirtual\ClaseVirtual;

class ProgresoCursoService
{
    public function porcentaje(ClaseVirtual $curso): int
    {
        $total = $curso->tareas->where('est_tar', 'PUBLICADA')->count();

        if ($total === 0) {
            return 0;
        }

        $entregadas = $curso->tareas->sum(fn ($tarea) => $tarea->entregas->whereIn('est_ent', ['ENTREGADO', 'ENTREGADO_TARDE', 'CALIFICADO'])->count());

        return (int) min(100, round(($entregadas / $total) * 100));
    }
}
