<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class InstitucionProcedenciaSeeder extends Seeder
{
    public function run(): void
    {
        $now = Carbon::now();

        DB::table('institucion_procedencia')->insert([

            [
                'cod_ipe' => 'IPE_0001',
                'nom_ipe' => 'Unidad Educativa Franz Tamayo N° 3',
                'tip_ipe' => 'Pública',
                'ciu_ipe' => 'La Paz',
                'est_ipe' => 'ACTIVO',
                'created_at' => $now,
                'updated_at' => $now,
            ],

            [
                'cod_ipe' => 'IPE_0002',
                'nom_ipe' => 'Unidad Educativa Sagrado Corazón de Jesús',
                'tip_ipe' => 'Convenio',
                'ciu_ipe' => 'La Paz',
                'est_ipe' => 'ACTIVO',
                'created_at' => $now,
                'updated_at' => $now,
            ],

            [
                'cod_ipe' => 'IPE_0003',
                'nom_ipe' => 'Unidad Educativa Italia',
                'tip_ipe' => 'Pública',
                'ciu_ipe' => 'La Paz',
                'est_ipe' => 'ACTIVO',
                'created_at' => $now,
                'updated_at' => $now,
            ],

            [
                'cod_ipe' => 'IPE_0004',
                'nom_ipe' => 'Unidad Educativa Simón Bolívar',
                'tip_ipe' => 'Pública',
                'ciu_ipe' => 'La Paz',
                'est_ipe' => 'ACTIVO',
                'created_at' => $now,
                'updated_at' => $now,
            ],

        ]);
    }
}