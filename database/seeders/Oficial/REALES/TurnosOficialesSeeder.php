<?php

namespace Database\Seeders\Oficial\REALES;

use App\Models\Turno;
use Illuminate\Database\Seeder;

class TurnosOficialesSeeder extends Seeder
{
    /**
     * Registra los turnos institucionales oficiales de SAVP.
     */
    public function run(): void
    {
        $turnos = [
            [
                'cod_tur' => 'TUR_0001',
                'nom_tur' => 'Mañana',
                'hor_ini_tur' => '07:45',
                'hor_fin_tur' => '13:20',
                'est_tur' => 'ACTIVO',
            ],
            [
                'cod_tur' => 'TUR_0002',
                'nom_tur' => 'Tarde',
                'hor_ini_tur' => '14:00',
                'hor_fin_tur' => '18:00',
                'est_tur' => 'ACTIVO',
            ],
        ];

        foreach ($turnos as $turno) {
            Turno::updateOrCreate(
                ['cod_tur' => $turno['cod_tur']],
                $turno
            );
        }
    }
}
