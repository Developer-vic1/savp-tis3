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

        $cursos = [
            'CUR_0001',
            'CUR_0002',
            'CUR_0003',
            'CUR_0004',
            'CUR_0005',
            'CUR_0006',
        ];

        $paralelos = ['A', 'B', 'C', 'D'];

        $data = [];
        $contador = 1;

        foreach ($cursos as $codCur) {
            foreach ($paralelos as $paralelo) {
                $data[] = [
                    'cod_par' => 'PAR_' . str_pad($contador, 4, '0', STR_PAD_LEFT),
                    'nom_par' => $paralelo,
                    'cod_cur' => $codCur,
                    'est_par' => 'ACTIVO',
                    'created_at' => $now,
                    'updated_at' => $now,
                ];
                $contador++;
            }
        }

        DB::table('paralelo')->insert($data);
    }
}
