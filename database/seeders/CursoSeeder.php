<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class CursoSeeder extends Seeder
{
    public function run(): void
    {
        $now = Carbon::now();

        DB::table('curso')->insert([
            [
                'cod_cur' => 'CUR_0001',
                'nom_cur' => '1ro de Secundaria',
                'niv_cur' => 'Secundaria',
                'est_cur' => 'ACTIVO',
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'cod_cur' => 'CUR_0002',
                'nom_cur' => '2do de Secundaria',
                'niv_cur' => 'Secundaria',
                'est_cur' => 'ACTIVO',
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'cod_cur' => 'CUR_0003',
                'nom_cur' => '3ro de Secundaria',
                'niv_cur' => 'Secundaria',
                'est_cur' => 'ACTIVO',
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'cod_cur' => 'CUR_0004',
                'nom_cur' => '4to de Secundaria',
                'niv_cur' => 'Secundaria',
                'est_cur' => 'ACTIVO',
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'cod_cur' => 'CUR_0005',
                'nom_cur' => '5to de Secundaria',
                'niv_cur' => 'Secundaria',
                'est_cur' => 'ACTIVO',
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'cod_cur' => 'CUR_0006',
                'nom_cur' => '6to de Secundaria',
                'niv_cur' => 'Secundaria',
                'est_cur' => 'ACTIVO',
                'created_at' => $now,
                'updated_at' => $now,
            ],
        ]);
    }
}
