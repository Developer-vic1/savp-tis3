<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class PersonalInstitucionalSeeder extends Seeder
{
    public function run(): void
    {
        $now = Carbon::now();

        /*
        |--------------------------------------------------------------------------
        | PERSONAL INSTITUCIONAL BASE
        |--------------------------------------------------------------------------
        | Estas personas tienen cargo funcional dentro de la institución.
        | Los estudiantes NO entran aquí.
        |--------------------------------------------------------------------------
        */
        DB::table('personal_institucional')->updateOrInsert(
            ['cod_pin' => 'PIN_0001'],
            [
                'cod_per' => 'PER_0001', // Victor Asturizaga
                'car_pin' => 'Administrador del sistema',
                'est_pin' => 'ACTIVO',
                'created_at' => $now,
                'updated_at' => $now,
            ]
        );

        DB::table('personal_institucional')->updateOrInsert(
            ['cod_pin' => 'PIN_0002'],
            [
                'cod_per' => 'PER_0004', // Ana Lucía Vargas
                'car_pin' => 'Regente',
                'est_pin' => 'ACTIVO',
                'created_at' => $now,
                'updated_at' => $now,
            ]
        );

        DB::table('personal_institucional')->updateOrInsert(
            ['cod_pin' => 'PIN_0003'],
            [
                'cod_per' => 'PER_0003', // Luis Fernando Rojas
                'car_pin' => 'Docente',
                'est_pin' => 'ACTIVO',
                'created_at' => $now,
                'updated_at' => $now,
            ]
        );

        DB::table('personal_institucional')->updateOrInsert(
            ['cod_pin' => 'PIN_0004'],
            [
                'cod_per' => 'PER_0006', // Edgar Rios
                'car_pin' => 'Director',
                'est_pin' => 'ACTIVO',
                'created_at' => $now,
                'updated_at' => $now,
            ]
        );

        DB::table('personal_institucional')->updateOrInsert(
            ['cod_pin' => 'PIN_0005'],
            [
                'cod_per' => 'PER_0007', // Diego Alejandro Huanca
                'car_pin' => 'Secretaria',
                'est_pin' => 'ACTIVO',
                'created_at' => $now,
                'updated_at' => $now,
            ]
        );

        /*
        |--------------------------------------------------------------------------
        | PERFIL ADMINISTRADOR
        |--------------------------------------------------------------------------
        */
        DB::table('administrador')->updateOrInsert(
            ['cod_adm' => 'ADM_0001'],
            [
                'cod_pin' => 'PIN_0001',
                'est_adm' => 'ACTIVO',
                'created_at' => $now,
                'updated_at' => $now,
            ]
        );

        /*
        |--------------------------------------------------------------------------
        | PERFIL DOCENTE
        |--------------------------------------------------------------------------
        */
        DB::table('docente')->updateOrInsert(
            ['cod_doc' => 'DOC_0001'],
            [
                'cod_pin' => 'PIN_0003',
                'esp_doc' => 'Matemática y Física',
                'num_mod_doc' => 0,
                'est_doc' => 'ACTIVO',
                'created_at' => $now,
                'updated_at' => $now,
            ]
        );

        /*
        |--------------------------------------------------------------------------
        | PERFIL DIRECTOR
        |--------------------------------------------------------------------------
        */
        DB::table('director')->updateOrInsert(
            ['cod_dir' => 'DIR_0001'],
            [
                'cod_pin' => 'PIN_0004',
                'est_dir' => 'ACTIVO',
                'created_at' => $now,
                'updated_at' => $now,
            ]
        );

        /*
        |--------------------------------------------------------------------------
        | PERFIL SECRETARIA
        |--------------------------------------------------------------------------
        */
        DB::table('secretaria_general')->updateOrInsert(
            ['cod_sge' => 'SGE_0001'],
            [
                'cod_pin' => 'PIN_0005',
                'est_sge' => 'ACTIVO',
                'created_at' => $now,
                'updated_at' => $now,
            ]
        );

        /*
        |--------------------------------------------------------------------------
        | PERFIL REGENTE
        |--------------------------------------------------------------------------
        | Esto asume que tu tabla regente usa:
        | cod_reg, cod_pin, est_reg.
        | Si tu migración usa otros nombres, ajustamos este bloque.
        |--------------------------------------------------------------------------
        */
        DB::table('regente')->updateOrInsert(
            ['cod_reg' => 'REG_0001'],
            [
                'cod_pin' => 'PIN_0002',
                'est_reg' => 'ACTIVO',
                'created_at' => $now,
                'updated_at' => $now,
            ]
        );
    }
}
