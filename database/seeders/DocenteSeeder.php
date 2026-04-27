<?php

namespace Database\Seeders;

use App\Models\Docente;
use Illuminate\Database\Seeder;
use Carbon\Carbon;

class DocenteSeeder extends Seeder
{
    public function run(): void
    {
        $now = Carbon::now();

        $docentes = [
            [
                'cod_doc' => 'DOC_0001',
                'cod_pin' => 'PIN_0002', // Luis Fernando Rojas en personal_institucional
                'esp_doc' => 'Sistemas Informáticos',
                'est_doc' => 'ACTIVO',
            ],
        ];

        foreach ($docentes as $data) {
            Docente::updateOrCreate(
                ['cod_doc' => $data['cod_doc']],
                [
                    'cod_pin' => $data['cod_pin'],
                    'esp_doc' => $data['esp_doc'],
                    'est_doc' => $data['est_doc'],
                    'created_at' => $now,
                    'updated_at' => $now,
                ]
            );
        }
    }
}
