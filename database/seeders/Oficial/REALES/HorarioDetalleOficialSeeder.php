<?php

namespace Database\Seeders\Oficial\REALES;

use App\Models\Oficial\Academico\Horario;
use App\Models\Oficial\Academico\HorarioDetalle;
use Illuminate\Database\Seeder;

class HorarioDetalleOficialSeeder extends Seeder
{
    /**
     * Carga las celdas de detalle de horario a partir de la transcripción celda por celda
     * del documento oficial 2021_02_07_HORARIOS_2021.pdf.
     *
     * Reglas estrictas:
     * - Celdas con valor '0' (sin clase) son OMITIDAS (no se inserta horario_detalle).
     * - NO genera horarios algorítmicamente (ni por rotación, ni por módulo, ni round-robin).
     * - Asigna el docente actual de la nómina 2024/2026 para la materia y curso correspondientes.
     * - Conserva la referencia histórica en obs_hde (ej: FUENTE_HORARIO=MAT-L.V.).
     * - Si no existe la fuente de transcripción o los bloques exactos, permanece pendiente sin inventar.
     */
    public function run(): void
    {
        $archivoFuente = __DIR__.'/FUENTES/horario_matriz_2021.local.php';

        if (! file_exists($archivoFuente)) {
            // Sin la fuente transcrita celda por celda, no se inventan horarios algorítmicos.
            return;
        }

        $matriz = require $archivoFuente;

        if (! is_array($matriz) || empty($matriz)) {
            return;
        }

        $count = 1;

        foreach ($matriz as $celda) {
            // Si la celda es '0' o indica sin clase, omitir obligatoriamente
            if (empty($celda['materia_fuente']) || $celda['materia_fuente'] === '0') {
                continue;
            }

            $codHor = $celda['cod_hor'];
            $codHbl = $celda['cod_hbl'];
            $dia = strtoupper($celda['dia']);

            $codHde = 'HDE_FT3_'.str_pad((string) $count, 5, '0', STR_PAD_LEFT);

            HorarioDetalle::updateOrCreate(
                [
                    'cod_hor' => $codHor,
                    'cod_hbl' => $codHbl,
                    'dia_hde' => $dia,
                ],
                [
                    'cod_hde' => $codHde,
                    'cod_pas' => $celda['cod_pas'] ?? null,
                    'cod_pes' => $celda['cod_pes'] ?? null,
                    'aul_hde' => $celda['aula'] ?? null,
                    'obs_hde' => 'FUENTE_HORARIO='.($celda['codigo_fuente'] ?? $celda['materia_fuente']),
                    'est_hde' => 'ACTIVO',
                ]
            );

            $count++;
        }
    }
}
