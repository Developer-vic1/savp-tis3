<?php

namespace Database\Seeders\OFICIAL;

use App\Models\Horario;
use App\Models\HorarioDetalle;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class HorariosOficialesSeeder extends Seeder
{
    /**
     * Reconstruye de forma determinística e idempotente los 136 horarios de cabecera
     * y las 4,120 celdas de horario detalle de la institución.
     */
    public function run(): void
    {
        $fuenteHorarios = __DIR__.'/FUENTES/horarios_data.local.php';
        $fuenteDetalles = __DIR__.'/FUENTES/horario_detalle_data.local.php';

        if (! file_exists($fuenteHorarios) || ! file_exists($fuenteDetalles)) {
            throw new \RuntimeException("Fuentes de horarios no encontradas.");
        }

        $horarios = require $fuenteHorarios;
        $detalles = require $fuenteDetalles;

        // 1. Horarios de cabecera (136)
        foreach ($horarios as $row) {
            Horario::updateOrCreate(
                ['cod_hor' => $row['cod_hor']],
                [
                    'cod_gea' => $row['cod_gea'],
                    'cod_cur' => $row['cod_cur'],
                    'cod_par' => $row['cod_par'],
                    'cod_pho' => $row['cod_pho'],
                    'obs_hor' => $row['obs_hor'] ?? null,
                    'est_hor' => $row['est_hor'] ?? 'ACTIVO',
                ]
            );
        }

        // 2. Horario Detalles (4,120) en lotes de 500 para máximo rendimiento e idempotencia
        $chunks = array_chunk($detalles, 500);
        foreach ($chunks as $chunk) {
            $formattedChunk = array_map(function ($row) {
                return [
                    'cod_hde' => $row['cod_hde'],
                    'cod_hor' => $row['cod_hor'],
                    'cod_hbl' => $row['cod_hbl'],
                    'dia_hde' => $row['dia_hde'],
                    'cod_pas' => $row['cod_pas'] ?? null,
                    'cod_pes' => $row['cod_pes'] ?? null,
                    'aul_hde' => $row['aul_hde'] ?? null,
                    'obs_hde' => $row['obs_hde'] ?? null,
                    'est_hde' => $row['est_hde'] ?? 'ACTIVO',
                ];
            }, $chunk);

            DB::table('horario_detalle')->upsert(
                $formattedChunk,
                ['cod_hde'],
                ['cod_hor', 'cod_hbl', 'dia_hde', 'cod_pas', 'cod_pes', 'aul_hde', 'obs_hde', 'est_hde']
            );
        }
    }
}
