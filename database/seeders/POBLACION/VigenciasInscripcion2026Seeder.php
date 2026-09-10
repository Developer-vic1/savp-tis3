<?php

namespace Database\Seeders\POBLACION;

use App\Models\InscripcionVigencia;
use Illuminate\Database\Seeder;

class VigenciasInscripcion2026Seeder extends Seeder
{
    /**
     * Reconstruye las vigencias de inscripción oficiales registradas en el sistema.
     */
    public function run(): void
    {
        $fuente = __DIR__.'/FUENTES/vigencias_data.local.php';
        if (! file_exists($fuente)) {
            return;
        }

        $vigencias = require $fuente;

        foreach ($vigencias as $row) {
            InscripcionVigencia::updateOrCreate(
                ['cod_ivg' => $row['cod_ivg']],
                [
                    'cod_ins' => $row['cod_ins'],
                    'cod_cur' => $row['cod_cur'],
                    'cod_par' => $row['cod_par'],
                    'cod_tur' => $row['cod_tur'],
                    'cod_esp_tec' => $row['cod_esp_tec'] ?? null,
                    'fii_ivg' => $row['fii_ivg'],
                    'ffi_ivg' => $row['ffi_ivg'] ?? null,
                    'tip_ivg' => $row['tip_ivg'],
                    'cie_ivg' => $row['cie_ivg'] ?? null,
                    'mot_ivg' => $row['mot_ivg'] ?? null,
                    'est_ivg' => $row['est_ivg'],
                ]
            );
        }
    }
}
