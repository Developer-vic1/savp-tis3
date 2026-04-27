<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class GestionAcademicaSeeder extends Seeder
{
    public function run(): void
    {
        $now = Carbon::now();

        DB::table('gestion_academica')->insert([

            [
                'cod_gea' => 'GEA_0001',
                'ani_gea' => 2023,
                'fii_gea' => '2023-02-01',
                'ffi_gea' => '2023-11-30',
                'est_gea' => 'FINALIZADO',
                'created_at' => $now,
                'updated_at' => $now,
            ],

            [
                'cod_gea' => 'GEA_0002',
                'ani_gea' => 2024,
                'fii_gea' => '2024-02-01',
                'ffi_gea' => '2024-11-30',
                'est_gea' => 'FINALIZADO',
                'created_at' => $now,
                'updated_at' => $now,
            ],

            [
                'cod_gea' => 'GEA_0003',
                'ani_gea' => 2025,
                'fii_gea' => '2025-02-01',
                'ffi_gea' => '2025-11-30',
                'est_gea' => 'FINALIZADO',
                'created_at' => $now,
                'updated_at' => $now,
            ],

            [
                'cod_gea' => 'GEA_0004',
                'ani_gea' => 2026,
                'fii_gea' => '2026-02-01',
                'ffi_gea' => '2026-11-30',
                'est_gea' => 'ACTIVO',
                'created_at' => $now,
                'updated_at' => $now,
            ],

        ]);
    }
}
