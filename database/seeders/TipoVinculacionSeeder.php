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
                'des_tve' => 'Estudiante inscrito de forma regular en la institución.',
                'est_tve' => 'ACTIVO',
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'cod_tve' => 'TVE_0002',
                'nom_tve' => 'Traslado',
                'des_tve' => 'Estudiante proveniente de otra unidad educativa.',
                'est_tve' => 'ACTIVO',
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'cod_tve' => 'TVE_0003',
                'nom_tve' => 'Reincorporado',
                'des_tve' => 'Estudiante que retorna a la institución después de una interrupción académica.',
                'est_tve' => 'ACTIVO',
                'created_at' => $now,
                'updated_at' => $now,
            ],
        ]);
    }
}