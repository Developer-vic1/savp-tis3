<?php

namespace Database\Seeders\Oficial\REALES;

use App\Models\Paralelo;
use Illuminate\Database\Seeder;

class ParalelosOficialesSeeder extends Seeder
{
    /**
     * Registra los paralelos institucionales oficiales de SAVP (A, B, C, D).
     */
    public function run(): void
    {
        $paralelos = [
            [
                'cod_par' => 'PAR_0001',
                'nom_par' => 'A',
                'est_par' => 'ACTIVO',
            ],
            [
                'cod_par' => 'PAR_0002',
                'nom_par' => 'B',
                'est_par' => 'ACTIVO',
            ],
            [
                'cod_par' => 'PAR_0003',
                'nom_par' => 'C',
                'est_par' => 'ACTIVO',
            ],
            [
                'cod_par' => 'PAR_0004',
                'nom_par' => 'D',
                'est_par' => 'ACTIVO',
            ],
        ];

        foreach ($paralelos as $paralelo) {
            Paralelo::updateOrCreate(
                ['cod_par' => $paralelo['cod_par']],
                $paralelo
            );
        }
    }
}
