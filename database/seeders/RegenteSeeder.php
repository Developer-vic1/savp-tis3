<?php

namespace Database\Seeders;

use App\Models\Regente;
use Illuminate\Database\Seeder;
use Carbon\Carbon;

class RegenteSeeder extends Seeder
{
    public function run(): void
    {
        $now = Carbon::now();

        Regente::updateOrCreate(
            ['cod_reg' => 'REG_0001'],
            [
                'cod_pin' => 'PIN_0003',
                'est_reg' => 'ACTIVO',
                'created_at' => $now,
                'updated_at' => $now,
            ]
        );
    }
}