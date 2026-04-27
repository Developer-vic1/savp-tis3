<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class InscripcionEstudianteSeeder extends Seeder
{
    public function run(): void
    {
        $now = Carbon::now();
        $fechaInscripcion = '2026-01-20';

        DB::table('inscripcion_estudiante')->insert([

            [
                'cod_ins' => 'INS_0001',
                'cod_est' => 'EST_0001',
                'cod_cur' => 'CUR_0004',
                'cod_par' => 'PAR_0014',
                'cod_tur' => 'TUR_0001',
                'cod_gea' => 'GEA_0004',
                'fei_ins' => $fechaInscripcion,
                'est_ins' => 'ACTIVO',
                'created_at' => $now,
                'updated_at' => $now,
            ],

            [
                'cod_ins' => 'INS_0002',
                'cod_est' => 'EST_0002',
                'cod_cur' => 'CUR_0003',
                'cod_par' => 'PAR_0009',
                'cod_tur' => 'TUR_0001',
                'cod_gea' => 'GEA_0004',
                'fei_ins' => $fechaInscripcion,
                'est_ins' => 'ACTIVO',
                'created_at' => $now,
                'updated_at' => $now,
            ],

            [
                'cod_ins' => 'INS_0003',
                'cod_est' => 'EST_0003',
                'cod_cur' => 'CUR_0002',
                'cod_par' => 'PAR_0006',
                'cod_tur' => 'TUR_0001',
                'cod_gea' => 'GEA_0004',
                'fei_ins' => $fechaInscripcion,
                'est_ins' => 'ACTIVO',
                'created_at' => $now,
                'updated_at' => $now,
            ],

            [
                'cod_ins' => 'INS_0004',
                'cod_est' => 'EST_0004',
                'cod_cur' => 'CUR_0004',
                'cod_par' => 'PAR_0016',
                'cod_tur' => 'TUR_0001',
                'cod_gea' => 'GEA_0004',
                'fei_ins' => $fechaInscripcion,
                'est_ins' => 'ACTIVO',
                'created_at' => $now,
                'updated_at' => $now,
            ],

            [
                'cod_ins' => 'INS_0005',
                'cod_est' => 'EST_0005',
                'cod_cur' => 'CUR_0001',
                'cod_par' => 'PAR_0001',
                'cod_tur' => 'TUR_0001',
                'cod_gea' => 'GEA_0004',
                'fei_ins' => $fechaInscripcion,
                'est_ins' => 'ACTIVO',
                'created_at' => $now,
                'updated_at' => $now,
            ],

            [
                'cod_ins' => 'INS_0006',
                'cod_est' => 'EST_0006',
                'cod_cur' => 'CUR_0003',
                'cod_par' => 'PAR_0010',
                'cod_tur' => 'TUR_0001',
                'cod_gea' => 'GEA_0004',
                'fei_ins' => $fechaInscripcion,
                'est_ins' => 'ACTIVO',
                'created_at' => $now,
                'updated_at' => $now,
            ],

            [
                'cod_ins' => 'INS_0007',
                'cod_est' => 'EST_0007',
                'cod_cur' => 'CUR_0004',
                'cod_par' => 'PAR_0013',
                'cod_tur' => 'TUR_0001',
                'cod_gea' => 'GEA_0004',
                'fei_ins' => $fechaInscripcion,
                'est_ins' => 'ACTIVO',
                'created_at' => $now,
                'updated_at' => $now,
            ],

            [
                'cod_ins' => 'INS_0008',
                'cod_est' => 'EST_0008',
                'cod_cur' => 'CUR_0003',
                'cod_par' => 'PAR_0011',
                'cod_tur' => 'TUR_0001',
                'cod_gea' => 'GEA_0004',
                'fei_ins' => $fechaInscripcion,
                'est_ins' => 'ACTIVO',
                'created_at' => $now,
                'updated_at' => $now,
            ],

            [
                'cod_ins' => 'INS_0009',
                'cod_est' => 'EST_0009',
                'cod_cur' => 'CUR_0001',
                'cod_par' => 'PAR_0002',
                'cod_tur' => 'TUR_0001',
                'cod_gea' => 'GEA_0004',
                'fei_ins' => $fechaInscripcion,
                'est_ins' => 'ACTIVO',
                'created_at' => $now,
                'updated_at' => $now,
            ],

            [
                'cod_ins' => 'INS_0010',
                'cod_est' => 'EST_0010',
                'cod_cur' => 'CUR_0004',
                'cod_par' => 'PAR_0015',
                'cod_tur' => 'TUR_0001',
                'cod_gea' => 'GEA_0004',
                'fei_ins' => $fechaInscripcion,
                'est_ins' => 'ACTIVO',
                'created_at' => $now,
                'updated_at' => $now,
            ],

            [
                'cod_ins' => 'INS_0011',
                'cod_est' => 'EST_0011',
                'cod_cur' => 'CUR_0002',
                'cod_par' => 'PAR_0007',
                'cod_tur' => 'TUR_0001',
                'cod_gea' => 'GEA_0004',
                'fei_ins' => $fechaInscripcion,
                'est_ins' => 'ACTIVO',
                'created_at' => $now,
                'updated_at' => $now,
            ],

            [
                'cod_ins' => 'INS_0012',
                'cod_est' => 'EST_0012',
                'cod_cur' => 'CUR_0003',
                'cod_par' => 'PAR_0012',
                'cod_tur' => 'TUR_0001',
                'cod_gea' => 'GEA_0004',
                'fei_ins' => $fechaInscripcion,
                'est_ins' => 'ACTIVO',
                'created_at' => $now,
                'updated_at' => $now,
            ],

            [
                'cod_ins' => 'INS_0013',
                'cod_est' => 'EST_0013',
                'cod_cur' => 'CUR_0001',
                'cod_par' => 'PAR_0003',
                'cod_tur' => 'TUR_0001',
                'cod_gea' => 'GEA_0004',
                'fei_ins' => $fechaInscripcion,
                'est_ins' => 'ACTIVO',
                'created_at' => $now,
                'updated_at' => $now,
            ],

            [
                'cod_ins' => 'INS_0014',
                'cod_est' => 'EST_0014',
                'cod_cur' => 'CUR_0002',
                'cod_par' => 'PAR_0008',
                'cod_tur' => 'TUR_0001',
                'cod_gea' => 'GEA_0004',
                'fei_ins' => $fechaInscripcion,
                'est_ins' => 'ACTIVO',
                'created_at' => $now,
                'updated_at' => $now,
            ],

            [
                'cod_ins' => 'INS_0015',
                'cod_est' => 'EST_0015',
                'cod_cur' => 'CUR_0003',
                'cod_par' => 'PAR_0009',
                'cod_tur' => 'TUR_0001',
                'cod_gea' => 'GEA_0004',
                'fei_ins' => $fechaInscripcion,
                'est_ins' => 'ACTIVO',
                'created_at' => $now,
                'updated_at' => $now,
            ],

            [
                'cod_ins' => 'INS_0016',
                'cod_est' => 'EST_0016',
                'cod_cur' => 'CUR_0004',
                'cod_par' => 'PAR_0014',
                'cod_tur' => 'TUR_0001',
                'cod_gea' => 'GEA_0004',
                'fei_ins' => $fechaInscripcion,
                'est_ins' => 'ACTIVO',
                'created_at' => $now,
                'updated_at' => $now,
            ],

        ]);
    }
}
