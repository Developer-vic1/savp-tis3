<?php

namespace Database\Seeders\OFICIAL;

use App\Models\ConfiguracionCalendarioGestion;
use Illuminate\Database\Seeder;

class ConfiguracionCalendario2026Seeder extends Seeder
{
    /**
     * Registra la configuración de días lectivos y fechas por trimestre para la gestión 2026 (200 días).
     */
    public function run(): void
    {
        $distribucion = [
            [
                'cod_ccg' => 'CCG_2026_T1',
                'cod_gea' => 'GEA_0001',
                'num_tri_ccg' => 1,
                'dias_req_ccg' => 66,
                'fii_tri_ccg' => '2026-02-02',
                'ffi_tri_ccg' => '2026-05-15',
                'est_ccg' => 'ACTIVO',
            ],
            [
                'cod_ccg' => 'CCG_2026_T2',
                'cod_gea' => 'GEA_0001',
                'num_tri_ccg' => 2,
                'dias_req_ccg' => 68,
                'fii_tri_ccg' => '2026-05-18',
                'ffi_tri_ccg' => '2026-08-28',
                'est_ccg' => 'ACTIVO',
            ],
            [
                'cod_ccg' => 'CCG_2026_T3',
                'cod_gea' => 'GEA_0001',
                'num_tri_ccg' => 3,
                'dias_req_ccg' => 66,
                'fii_tri_ccg' => '2026-09-01',
                'ffi_tri_ccg' => '2026-12-04',
                'est_ccg' => 'ACTIVO',
            ],
        ];

        foreach ($distribucion as $item) {
            ConfiguracionCalendarioGestion::updateOrCreate(
                [
                    'cod_gea' => $item['cod_gea'],
                    'num_tri_ccg' => $item['num_tri_ccg'],
                ],
                $item
            );
        }
    }
}
