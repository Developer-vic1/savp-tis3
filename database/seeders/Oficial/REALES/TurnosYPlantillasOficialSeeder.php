<?php

namespace Database\Seeders\Oficial\REALES;

use App\Models\Oficial\Academico\HorarioBloque;
use App\Models\Oficial\Academico\PlantillaHoraria;
use Illuminate\Database\Seeder;

class TurnosYPlantillasOficialSeeder extends Seeder
{
    /**
     * Reconstruye de forma determinística e idempotente las 6 plantillas horarias
     * y los 51 bloques horarios oficiales de la institución.
     */
    public function run(): void
    {
        $fuente = __DIR__.'/FUENTES/turnos_plantillas_bloques_data.local.php';
        if (! file_exists($fuente)) {
            throw new \RuntimeException("Fuente no encontrada: {$fuente}");
        }

        $data = require $fuente;

        // 1. Plantillas Horarias (6)
        foreach ($data['plantilla_horaria'] as $row) {
            PlantillaHoraria::updateOrCreate(
                ['cod_pho' => $row['cod_pho']],
                [
                    'cod_tur' => $row['cod_tur'],
                    'nom_pho' => $row['nom_pho'],
                    'tip_pho' => $row['tip_pho'],
                    'des_pho' => $row['des_pho'] ?? null,
                    'fec_ini_pho' => $row['fec_ini_pho'],
                    'fec_fin_pho' => $row['fec_fin_pho'],
                    'dur_blo_pho' => $row['dur_blo_pho'],
                    'ord_pho' => $row['ord_pho'],
                    'act_pho' => (bool)$row['act_pho'],
                    'est_pho' => $row['est_pho'] ?? 1,
                ]
            );
        }

        // 2. Horario Bloques (51)
        foreach ($data['horario_bloque'] as $row) {
            HorarioBloque::updateOrCreate(
                ['cod_hbl' => $row['cod_hbl']],
                [
                    'cod_pho' => $row['cod_pho'],
                    'num_hbl' => $row['num_hbl'],
                    'hor_ini_hbl' => $row['hor_ini_hbl'],
                    'hor_fin_hbl' => $row['hor_fin_hbl'],
                    'nom_hbl' => $row['nom_hbl'] ?? null,
                    'tip_hbl' => $row['tip_hbl'] ?? 'PEDAGOGICO',
                    'obs_hbl' => $row['obs_hbl'] ?? null,
                    'est_hbl' => $row['est_hbl'] ?? 1,
                ]
            );
        }
    }
}
