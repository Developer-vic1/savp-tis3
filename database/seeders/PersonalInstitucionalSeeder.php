<?php

namespace Database\Seeders;

use App\Models\PersonalInstitucional;
use Illuminate\Database\Seeder;
use Carbon\Carbon;

class PersonalInstitucionalSeeder extends Seeder
{
    public function run(): void
    {
        $now = Carbon::now();

        $personal = [
            [
                'cod_pin' => 'PIN_0001',
                'cod_per' => 'PER_0001',
                'car_pin' => 'Administrador',
                'est_pin' => 'ACTIVO',
            ],
            [
                'cod_pin' => 'PIN_0002',
                'cod_per' => 'PER_0003',
                'car_pin' => 'Docente',
                'est_pin' => 'ACTIVO',
            ],
            [
                'cod_pin' => 'PIN_0003',
                'cod_per' => 'PER_0004',
                'car_pin' => 'Regente',
                'est_pin' => 'ACTIVO',
            ],
            [
                'cod_pin' => 'PIN_0004',
                'cod_per' => 'PER_0006',
                'car_pin' => 'Director',
                'est_pin' => 'ACTIVO',
            ],
            [
                'cod_pin' => 'PIN_0005',
                'cod_per' => 'PER_0007',
                'car_pin' => 'Secretaria',
                'est_pin' => 'ACTIVO',
            ],
        ];

        foreach ($personal as $data) {
            PersonalInstitucional::updateOrCreate(
                ['cod_pin' => $data['cod_pin']],
                [
                    'cod_per' => $data['cod_per'],
                    'car_pin' => $data['car_pin'],
                    'est_pin' => $data['est_pin'],
                    'created_at' => $now,
                    'updated_at' => $now,
                ]
            );
        }
    }
}
