<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class PeriodoEvaluacionSeeder extends Seeder
{
    public function run(): void
    {
        $now = Carbon::now();

        DB::table('periodo_evaluacion')->insert([

            [
                'cod_pev' => 'PEV_0001',
                'nom_pev' => 'Primer Bimestre',
                'ord_pev' => 1,
                'est_pev' => 'ACTIVO',
                'created_at' => $now,
                'updated_at' => $now,
            ],

            [
                'cod_pev' => 'PEV_0002',
                'nom_pev' => 'Segundo Bimestre',
                'ord_pev' => 2,
                'est_pev' => 'ACTIVO',
                'created_at' => $now,
                'updated_at' => $now,
            ],

            [
                'cod_pev' => 'PEV_0003',
                'nom_pev' => 'Tercer Bimestre',
                'ord_pev' => 3,
                'est_pev' => 'ACTIVO',
                'created_at' => $now,
                'updated_at' => $now,
            ],

            [
                'cod_pev' => 'PEV_0004',
                'nom_pev' => 'Cuarto Bimestre',
                'ord_pev' => 4,
                'est_pev' => 'ACTIVO',
                'created_at' => $now,
                'updated_at' => $now,
            ],

        ]);
    }
}