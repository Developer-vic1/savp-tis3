<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class TurnoSeeder extends Seeder
{
    public function run(): void
    {
        $now = Carbon::now();

        DB::table('turno')->insert([
            [
                'cod_tur' => 'TUR_0001',
                'nom_tur' => 'Mañana',
                'hor_ini_tur' => '08:00',
                'hor_fin_tur' => '13:20',
                'est_tur' => 'ACTIVO',
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'cod_tur' => 'TUR_0002',
                'nom_tur' => 'Tarde',
                'hor_ini_tur' => '14:00',
                'hor_fin_tur' => '18:00',
                'est_tur' => 'ACTIVO',
                'created_at' => $now,
                'updated_at' => $now,
            ],
        ]);
    }
}