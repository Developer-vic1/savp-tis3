<?php

namespace Database\Seeders\OFICIAL;

use App\Models\PlantillaHoraria;
use Illuminate\Database\Seeder;

class PlantillasHorariasOficialesSeeder extends Seeder
{
    /**
     * Registra las plantillas horarias institucionales oficiales de SAVP.
     *
     * Nota: En esta etapa se establecen las cabeceras de plantillas oficiales sin
     * inventar bloques de periodos o recreos no confirmados.
     */
    public function run(): void
    {
        $plantillas = [
            [
                'cod_pho' => 'PHO_0001',
                'cod_tur' => 'TUR_0001',
                'nom_pho' => 'Horario Normal Mañana',
                'tip_pho' => 'REGULAR',
                'des_pho' => 'Horario regular institucional del turno mañana (07:45 – 13:20).',
                'dur_blo_pho' => null,
                'ord_pho' => 1,
                'act_pho' => true,
                'est_pho' => true,
            ],
            [
                'cod_pho' => 'PHO_0002',
                'cod_tur' => 'TUR_0001',
                'nom_pho' => 'Horario Invierno Mañana',
                'tip_pho' => 'INVIERNO',
                'des_pho' => 'Horario de invierno del turno mañana (08:30 – 13:30).',
                'dur_blo_pho' => null,
                'ord_pho' => 2,
                'act_pho' => false,
                'est_pho' => true,
            ],
            [
                'cod_pho' => 'PHO_0003',
                'cod_tur' => 'TUR_0002',
                'nom_pho' => 'Horario Especialidades Tarde',
                'tip_pho' => 'REGULAR',
                'des_pho' => 'Horario de especialidades técnicas del turno tarde (14:00 – 18:00).',
                'dur_blo_pho' => null,
                'ord_pho' => 1,
                'act_pho' => true,
                'est_pho' => true,
            ],
        ];

        foreach ($plantillas as $plantilla) {
            PlantillaHoraria::updateOrCreate(
                ['cod_pho' => $plantilla['cod_pho']],
                $plantilla
            );
        }
    }
}
