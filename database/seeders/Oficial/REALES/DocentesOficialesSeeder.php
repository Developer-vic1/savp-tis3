<?php

namespace Database\Seeders\Oficial\REALES;

use App\Models\Docente;
use App\Models\PersonalInstitucional;
use Illuminate\Database\Seeder;

class DocentesOficialesSeeder extends Seeder
{
    /**
     * Reconstruye de forma determinística e idempotente los 48 docentes oficiales
     * (DOC_0001 a DOC_0048) de la institución con sus especialidades y cargas horarias.
     */
    public function run(): void
    {
        $fuente = __DIR__.'/FUENTES/personal_institucional.local.php';
        if (! file_exists($fuente)) {
            throw new \RuntimeException("Fuente no encontrada: {$fuente}");
        }

        $nomina = require $fuente;

        foreach ($nomina as $item) {
            $docData = $item['subtipo']['docente'] ?? null;
            if (! $docData) {
                continue;
            }

            Docente::updateOrCreate(
                ['cod_doc' => $docData['cod_doc']],
                [
                    'cod_pin' => $docData['cod_pin'],
                    'esp_doc' => $docData['esp_doc'] ?? null,
                    'num_mod_doc' => $docData['num_mod_doc'] ?? 0,
                    'est_doc' => $docData['est_doc'] ?? 'ACTIVO',
                ]
            );
        }
    }
}
