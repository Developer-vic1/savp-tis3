<?php

namespace Database\Seeders\Oficial\REALES;

use App\Models\PeriodoEvaluacion;
use Illuminate\Database\Seeder;

class PeriodosEvaluacionOficialSeeder extends Seeder
{
    public function run(): void
    {
        $periodos = [
            [
                'cod_pev' => 'PEV_0001',
                'nom_pev' => 'Primer Trimestre',
                'ord_pev' => 1,
                'cod_gea' => 'GEA_0001',
                'fii_pev' => '2026-02-02',
                'ffi_pev' => '2026-05-15',
                'est_pev' => 'ACTIVO',
            ],
            [
                'cod_pev' => 'PEV_0002',
                'nom_pev' => 'Segundo Trimestre',
                'ord_pev' => 2,
                'cod_gea' => 'GEA_0001',
                'fii_pev' => '2026-05-18',
                'ffi_pev' => '2026-08-28',
                'est_pev' => 'ACTIVO',
            ],
            [
                'cod_pev' => 'PEV_0003',
                'nom_pev' => 'Tercer Trimestre',
                'ord_pev' => 3,
                'cod_gea' => 'GEA_0001',
                'fii_pev' => '2026-09-01',
                'ffi_pev' => '2026-12-04',
                'est_pev' => 'ACTIVO',
            ],
        ];

        foreach ($periodos as $periodo) {
            PeriodoEvaluacion::updateOrCreate(
                ['cod_pev' => $periodo['cod_pev']],
                $periodo
            );
        }
    }
}
