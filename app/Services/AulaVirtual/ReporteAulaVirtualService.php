<?php

namespace App\Services\AulaVirtual;

use App\Models\AulaVirtual\ClaseVirtual;

class ReporteAulaVirtualService
{
    public function consolidadoCurso(ClaseVirtual $curso): array
    {
        return [
            'curso' => $curso,
            'estudiantes' => $curso->estudiantes->where('est_cla_est', 'ACTIVO')->count(),
            'materiales' => $curso->materiales->where('est_mat', 'ACTIVO')->count(),
            'tareas' => $curso->tareas->whereIn('est_tar', ['PUBLICADA', 'CERRADA'])->count(),
            'asistencias' => $curso->asistencias->where('est_asi_cla', 'CERRADA')->count(),
        ];
    }
}
