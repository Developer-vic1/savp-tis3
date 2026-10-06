<?php

namespace App\Services;

use App\Models\Oficial\Academico\ReporteGenerado;
use App\Models\Oficial\Sistema\User;
use App\Support\PrivateFilePath;
use Illuminate\Database\Eloquent\Builder;

/** Sólo PDFs de familias con permiso explícito; el histórico carece de gestión/grado. */
class HistoricalReportAccessService
{
    private const FAMILIES = [
        'Reporte Administrativo' => ['Reportes_Administrativos', 'administrativos'],
        'Reporte Académico General' => ['Reportes_Academicos', 'academicos'],
        'Reporte de Calificaciones' => ['Reportes_Academicos', 'academicos'],
    ];

    public function families(User $user): array
    {
        $actor = app(RoleDashboardResolver::class)->roleFor($user);
        if (! in_array($actor, ['Administrador', 'Director', 'Secretaria'], true)) {
            return [];
        }

        return array_filter(self::FAMILIES, fn ($rules, $type) => ($actor !== 'Secretaria' || $type === 'Reporte Administrativo')
            && $user->can($rules[0]), ARRAY_FILTER_USE_BOTH);
    }

    public function query(User $user): Builder
    {
        $types = $this->families($user);
        $query = ReporteGenerado::query()->where('formato', 'pdf')->where('estado', 'generado');
        if (! $types) {
            return $query->whereRaw('1 = 0');
        }

        return $query->where(function ($q) use ($types) {
            foreach ($types as $type => $rules) {
                $q->orWhere(fn ($family) => $family->where('tipo_reporte', $type)->where('ruta_archivo', 'like', 'reportes/'.$rules[1].'/%'));
            }
        });
    }

    public function canRead(User $user, ReporteGenerado $report): bool
    {
        $family = $this->families($user)[$report->tipo_reporte] ?? null;

        return $family && $report->formato === 'pdf' && $report->estado === 'generado'
            && PrivateFilePath::valid($report->ruta_archivo, 'reportes/'.$family[1])
            && str_ends_with(strtolower($report->ruta_archivo), '.pdf');
    }
}
