<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class EstudianteSeeder extends Seeder
{
    public function run(): void
    {
        $now = Carbon::now();

        DB::table('estudiante')->insert([

            [
                'cod_est' => 'EST_0001',
                'rud_est' => '202600000001',
                'cod_per' => 'PER_0002',
                'cod_tve' => 'TVE_0003',
                'cod_ipe' => 'IPE_0002',
                'cod_esp' => 'ESP_0003',
                'est_est' => 'ACTIVO',
                'created_at' => $now,
                'updated_at' => $now,
            ],

            [
                'cod_est' => 'EST_0002',
                'rud_est' => '202600000002',
                'cod_per' => 'PER_0007',
                'cod_tve' => 'TVE_0001',
                'cod_ipe' => 'IPE_0001',
                'cod_esp' => 'ESP_0005',
                'est_est' => 'ACTIVO',
                'created_at' => $now,
                'updated_at' => $now,
            ],

            [
                'cod_est' => 'EST_0003',
                'rud_est' => '202600000003',
                'cod_per' => 'PER_0008',
                'cod_tve' => 'TVE_0003',
                'cod_ipe' => 'IPE_0003',
                'cod_esp' => 'ESP_0001',
                'est_est' => 'ACTIVO',
                'created_at' => $now,
                'updated_at' => $now,
            ],

            [
                'cod_est' => 'EST_0004',
                'rud_est' => '202600000004',
                'cod_per' => 'PER_0009',
                'cod_tve' => 'TVE_0001',
                'cod_ipe' => 'IPE_0001',
                'cod_esp' => 'ESP_0004',
                'est_est' => 'ACTIVO',
                'created_at' => $now,
                'updated_at' => $now,
            ],

            [
                'cod_est' => 'EST_0005',
                'rud_est' => '202600000005',
                'cod_per' => 'PER_0010',
                'cod_tve' => 'TVE_0002',
                'cod_ipe' => null,
                'cod_esp' => null,
                'est_est' => 'ACTIVO',
                'created_at' => $now,
                'updated_at' => $now,
            ],

            [
                'cod_est' => 'EST_0006',
                'rud_est' => '202600000006',
                'cod_per' => 'PER_0011',
                'cod_tve' => 'TVE_0001',
                'cod_ipe' => 'IPE_0001',
                'cod_esp' => 'ESP_0005',
                'est_est' => 'ACTIVO',
                'created_at' => $now,
                'updated_at' => $now,
            ],

            [
                'cod_est' => 'EST_0007',
                'rud_est' => '202600000007',
                'cod_per' => 'PER_0012',
                'cod_tve' => 'TVE_0003',
                'cod_ipe' => 'IPE_0004',
                'cod_esp' => 'ESP_0001',
                'est_est' => 'ACTIVO',
                'created_at' => $now,
                'updated_at' => $now,
            ],

            [
                'cod_est' => 'EST_0008',
                'rud_est' => '202600000008',
                'cod_per' => 'PER_0013',
                'cod_tve' => 'TVE_0001',
                'cod_ipe' => 'IPE_0001',
                'cod_esp' => 'ESP_0007',
                'est_est' => 'ACTIVO',
                'created_at' => $now,
                'updated_at' => $now,
            ],

            [
                'cod_est' => 'EST_0009',
                'rud_est' => '202600000009',
                'cod_per' => 'PER_0014',
                'cod_tve' => 'TVE_0002',
                'cod_ipe' => null,
                'cod_esp' => null,
                'est_est' => 'ACTIVO',
                'created_at' => $now,
                'updated_at' => $now,
            ],

            [
                'cod_est' => 'EST_0010',
                'rud_est' => '202600000010',
                'cod_per' => 'PER_0015',
                'cod_tve' => 'TVE_0001',
                'cod_ipe' => 'IPE_0001',
                'cod_esp' => 'ESP_0008',
                'est_est' => 'ACTIVO',
                'created_at' => $now,
                'updated_at' => $now,
            ],

            [
                'cod_est' => 'EST_0011',
                'rud_est' => '202600000011',
                'cod_per' => 'PER_0016',
                'cod_tve' => 'TVE_0001',
                'cod_ipe' => 'IPE_0001',
                'cod_esp' => 'ESP_0001',
                'est_est' => 'ACTIVO',
                'created_at' => $now,
                'updated_at' => $now,
            ],

            [
                'cod_est' => 'EST_0012',
                'rud_est' => '202600000012',
                'cod_per' => 'PER_0017',
                'cod_tve' => 'TVE_0001',
                'cod_ipe' => 'IPE_0001',
                'cod_esp' => 'ESP_0006',
                'est_est' => 'ACTIVO',
                'created_at' => $now,
                'updated_at' => $now,
            ],

            [
                'cod_est' => 'EST_0013',
                'rud_est' => '202600000013',
                'cod_per' => 'PER_0018',
                'cod_tve' => 'TVE_0002',
                'cod_ipe' => null,
                'cod_esp' => null,
                'est_est' => 'ACTIVO',
                'created_at' => $now,
                'updated_at' => $now,
            ],

            [
                'cod_est' => 'EST_0014',
                'rud_est' => '202600000014',
                'cod_per' => 'PER_0019',
                'cod_tve' => 'TVE_0001',
                'cod_ipe' => 'IPE_0001',
                'cod_esp' => 'ESP_0004',
                'est_est' => 'ACTIVO',
                'created_at' => $now,
                'updated_at' => $now,
            ],

            [
                'cod_est' => 'EST_0015',
                'rud_est' => '202600000015',
                'cod_per' => 'PER_0020',
                'cod_tve' => 'TVE_0001',
                'cod_ipe' => 'IPE_0001',
                'cod_esp' => 'ESP_0001',
                'est_est' => 'ACTIVO',
                'created_at' => $now,
                'updated_at' => $now,
            ],

            [
                'cod_est' => 'EST_0016',
                'rud_est' => '202600000016',
                'cod_per' => 'PER_0021',
                'cod_tve' => 'TVE_0001',
                'cod_ipe' => 'IPE_0001',
                'cod_esp' => 'ESP_0003',
                'est_est' => 'ACTIVO',
                'created_at' => $now,
                'updated_at' => $now,
            ],

        ]);
    }
}