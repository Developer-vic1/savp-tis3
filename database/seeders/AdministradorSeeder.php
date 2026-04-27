<?php

namespace Database\Seeders;

use App\Models\Administrador;
use Illuminate\Database\Seeder;
use Carbon\Carbon;

class AdministradorSeeder extends Seeder
{
    public function run(): void
    {
        $now = Carbon::now();

        Administrador::updateOrCreate(
            ['cod_adm' => 'ADM_0001'],
            [
                'cod_pin' => 'PIN_0001',
                'est_adm' => 'ACTIVO',
                'created_at' => $now,
                'updated_at' => $now,
            ]
        );
    }
}