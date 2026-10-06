<?php

namespace Database\Seeders\Oficial\REALES;

use App\Models\Oficial\Academico\PlanAsignatura;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PlanesAsignaturaOficialesSeeder extends Seeder
{
    /**
     * Reconstruye de forma determinística e idempotente los 300 planes de asignatura
     * oficiales (PAS_0001 a PAS_0300) de la institución.
     */
    public function run(): void
    {
        $fuente = __DIR__.'/FUENTES/planes_asignatura_data.local.php';
        if (! file_exists($fuente)) {
            throw new \RuntimeException("Fuente no encontrada: {$fuente}");
        }

        $planes = require $fuente;

        foreach ($planes as $row) {
            PlanAsignatura::updateOrCreate(
                ['cod_pas' => $row['cod_pas']],
                [
                    'cod_asi' => $row['cod_asi'],
                    'cod_doc' => $row['cod_doc'],
                    'cod_cur' => $row['cod_cur'],
                    'cod_par' => $row['cod_par'],
                    'cod_tur' => $row['cod_tur'],
                    'cod_gea' => $row['cod_gea'],
                    'hor_pas' => $row['hor_pas'] ?? null,
                    'est_pas' => $row['est_pas'] ?? 'ACTIVO',
                ]
            );
        }
    }
}
