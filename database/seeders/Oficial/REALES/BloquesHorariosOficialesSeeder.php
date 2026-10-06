<?php

namespace Database\Seeders\Oficial\REALES;

use App\Models\Oficial\Academico\HorarioBloque;
use Illuminate\Database\Seeder;

class BloquesHorariosOficialesSeeder extends Seeder
{
    /**
     * Carga los bloques horarios oficiales si existe la fuente local de campanas exactas.
     * Si no existe la fuente confirmada de campana por período, NO inventa horas intermedias
     * y mantiene el estado PENDIENTE DE FUENTE.
     */
    public function run(): void
    {
        $archivoFuente = __DIR__.'/FUENTES/bloques_horarios_2026.local.php';

        if (! file_exists($archivoFuente)) {
            // No inventar datos de bloques sin fuente institucional exacta
            return;
        }

        $bloques = require $archivoFuente;

        if (! is_array($bloques)) {
            return;
        }

        foreach ($bloques as $b) {
            HorarioBloque::updateOrCreate(
                ['cod_hbl' => $b['cod_hbl']],
                $b
            );
        }
    }
}
