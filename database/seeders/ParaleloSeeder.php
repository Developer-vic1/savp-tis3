<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class ParaleloSeeder extends Seeder
{
    public function run(): void
    {
        $now = Carbon::now();

        $paralelos = [
            [
                'cod_par' => 'PAR_0001',
                'nom_par' => 'A',
                'est_par' => 'ACTIVO',
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'cod_par' => 'PAR_0002',
                'nom_par' => 'B',
                'est_par' => 'ACTIVO',
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'cod_par' => 'PAR_0003',
                'nom_par' => 'C',
                'est_par' => 'ACTIVO',
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'cod_par' => 'PAR_0004',
                'nom_par' => 'D',
                'est_par' => 'ACTIVO',
                'created_at' => $now,
                'updated_at' => $now,
            ],
        ];

        foreach ($paralelos as $paralelo) {
            DB::table('paralelo')->updateOrInsert(
                ['cod_par' => $paralelo['cod_par']],
                $paralelo
            );
        }
    }
}
