<?php

namespace App\Services\AulaVirtual;

use App\Models\AulaVirtual\ClaseVirtual;

class ReporteAulaVirtualService
{
    public function consolidadoCurso(ClaseVirtual $curso): array
    {
        return [
            'curso' => $curso,
            'estudiantes' => app(CursoVirtualService::class)->estudiantesVigentes($curso)->count(),
            'materiales' => (int) $curso->materiales_publicados_count,
            'tareas' => $curso->tareas()->whereIn('est_tar', ['PUBLICADA', 'CERRADA'])->count(),
            'asistencias' => $curso->asistencias()->where('est_asi_cla', 'CERRADA')->count(),
        ];
    }
}
