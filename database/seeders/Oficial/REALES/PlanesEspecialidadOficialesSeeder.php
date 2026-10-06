<?php

namespace Database\Seeders\Oficial\REALES;

use App\Models\Oficial\Academico\PlanEspecialidad;
use Illuminate\Database\Seeder;

class PlanesEspecialidadOficialesSeeder extends Seeder
{
    /**
     * Reconstruye de forma determinística e idempotente los 68 planes de especialidad
     * técnica oficiales (PES_0001 a PES_0068) de la institución.
     */
    public function run(): void
    {
        $fuente = __DIR__.'/FUENTES/planes_especialidad_data.local.php';
        if (! file_exists($fuente)) {
            throw new \RuntimeException("Fuente no encontrada: {$fuente}");
        }

        $planes = require $fuente;

        foreach ($planes as $row) {
            PlanEspecialidad::updateOrCreate(
                ['cod_pes' => $row['cod_pes']],
                [
                    'cod_esp' => $row['cod_esp'],
                    'cod_doc' => $row['cod_doc'],
                    'cod_cur' => $row['cod_cur'],
                    'cod_par' => $row['cod_par'],
                    'cod_tur' => $row['cod_tur'],
                    'cod_gea' => $row['cod_gea'],
                    'hor_pes' => $row['hor_pes'],
                    'est_pes' => $row['est_pes'] ?? 'ACTIVO',
                ]
            );
        }
    }
}
