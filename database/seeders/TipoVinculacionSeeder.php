<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class TipoVinculacionSeeder extends Seeder
{
    public function run(): void
    {
        $now = Carbon::now();

        DB::table('tipo_vinculacion_estudiante')->insert([

            [
                'cod_tve' => 'TVE_0001',
                'nom_tve' => 'Regular',
                'des_tve' => 'Estudiante que continúa sus estudios dentro de la misma institución.',
                'est_tve' => 'ACTIVO',
                'created_at' => $now,
                'updated_at' => $now,
            ],

            [
                'cod_tve' => 'TVE_0002',
                'nom_tve' => 'Nuevo',
                'des_tve' => 'Estudiante que ingresa por primera vez a la institución.',
                'est_tve' => 'ACTIVO',
                'created_at' => $now,
                'updated_at' => $now,
            ],

            [
                'cod_tve' => 'TVE_0003',
                'nom_tve' => 'Transferencia',
                'des_tve' => 'Estudiante que proviene de otra institución educativa.',
                'est_tve' => 'ACTIVO',
                'created_at' => $now,
                'updated_at' => $now,
            ],

            [
                'cod_tve' => 'TVE_0004',
                'nom_tve' => 'Reincorporación',
                'des_tve' => 'Estudiante que retoma sus estudios después de haber estado inactivo.',
                'est_tve' => 'ACTIVO',
                'created_at' => $now,
                'updated_at' => $now,
            ],

        ]);
    }
}