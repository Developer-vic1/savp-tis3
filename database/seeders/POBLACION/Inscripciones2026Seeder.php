<?php

namespace Database\Seeders\POBLACION;

use App\Models\InscripcionEstudiante;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class Inscripciones2026Seeder extends Seeder
{
    /**
     * Reconstruye de forma determinística e idempotente las 600 inscripciones oficiales
     * existentes en la base de datos para la gestión 2026.
     */
    public function run(): void
    {
        $fuente = __DIR__.'/FUENTES/inscripciones_data.local.php';
        if (! file_exists($fuente)) {
            throw new \RuntimeException("Fuente de inscripciones no encontrada: {$fuente}");
        }

        $inscripciones = require $fuente;

        foreach ($inscripciones as $row) {
            InscripcionEstudiante::updateOrCreate(
                ['cod_ins' => $row['cod_ins']],
                [
                    'cod_est' => $row['cod_est'],
                    'cod_gea' => $row['cod_gea'],
                    'cod_cur' => $row['cod_cur'],
                    'cod_par' => $row['cod_par'],
                    'cod_tur' => $row['cod_tur'],
                    'fei_ins' => $row['fei_ins'],
                    'tip_ins' => $row['tip_ins'] ?? 'REGULAR',
                    'con_ins' => $row['con_ins'] ?? 'NORMAL',
                    'est_ins' => $row['est_ins'] ?? 'ACTIVA',
                    'pro_ins' => $row['pro_ins'] ?? null,
                    'obs_ins' => $row['obs_ins'] ?? null,
                    'mot_obs_ins' => $row['mot_obs_ins'] ?? null,
                    'doc_com_ins' => (bool)($row['doc_com_ins'] ?? false),
                    'sob_aut_ins' => (bool)($row['sob_aut_ins'] ?? false),
                    'sie_ins' => (bool)($row['sie_ins'] ?? false),
                    'fec_sie_ins' => $row['fec_sie_ins'] ?? null,
                    'cod_esp_tec' => $row['cod_esp_tec'] ?? null,
                    'est_esp_tec_ins' => $row['est_esp_tec_ins'] ?? 'NO_APLICA',
                    'obs_esp_tec_ins' => $row['obs_esp_tec_ins'] ?? null,
                    'fec_con_ins' => $row['fec_con_ins'] ?? null,
                    'fec_anu_ins' => $row['fec_anu_ins'] ?? null,
                    'mot_anu_ins' => $row['mot_anu_ins'] ?? null,
                    'fec_ret_ins' => $row['fec_ret_ins'] ?? null,
                    'mot_ret_ins' => $row['mot_ret_ins'] ?? null,
                ]
            );
        }
    }
}
