<?php

namespace App\Support\Reportes;

use App\Support\Core\SoporteInteligenteBase;

class ReporteAdministrativoInteligente extends SoporteInteligenteBase
{
    public function diagnostico(array $metricas): array
    {
        $sinDatos = collect($metricas)->filter(fn ($valor) => (int) $valor === 0)->keys()->values()->all();

        return [
            'estado' => count($sinDatos) <= 2 ? 'Operativo' : 'Requiere atención',
            'completitud' => $metricas === [] ? 0 : (int) round(((count($metricas) - count($sinDatos)) / count($metricas)) * 100),
            'advertencias' => $sinDatos,
        ];
    }
}
