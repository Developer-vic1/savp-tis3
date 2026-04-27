<?php

namespace Database\Seeders;

use App\Models\SecretariaGeneral;
use Illuminate\Database\Seeder;
use Carbon\Carbon;

class SecretariaSeeder extends Seeder
{
    public function run(): void
    {
        $now = Carbon::now();

        SecretariaGeneral::updateOrCreate(
            ['cod_sge' => 'SGE_0001'],
            [
                'cod_pin' => 'PIN_0005', // 👈 Asegúrate de crear este en PersonalInstitucionalSeeder
                'est_sge' => 'ACTIVO',
                'created_at' => $now,
                'updated_at' => $now,
            ]
        );
    }
}