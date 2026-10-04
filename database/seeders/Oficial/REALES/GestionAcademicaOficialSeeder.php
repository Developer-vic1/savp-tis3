<?php

namespace Database\Seeders\Oficial\REALES;

use App\Models\ConfiguracionCalendarioGestion;
use App\Models\GestionAcademica;
use App\Models\PeriodoEvaluacion;
use Illuminate\Database\Seeder;

class GestionAcademicaOficialSeeder extends Seeder
{
    /**
     * Reconstruye la gestión académica oficial 2026, configuración de calendario
     * trimestral y periodos de evaluación.
     */
    public function run(): void
    {
        $fuente = __DIR__.'/FUENTES/catalogos_academicos_data.local.php';
        if (! file_exists($fuente)) {
            throw new \RuntimeException("Fuente no encontrada: {$fuente}");
        }

        $data = require $fuente;

        // 1. Gestión Académica (GEA_0001)
        foreach ($data['gestion_academica'] as $row) {
            GestionAcademica::updateOrCreate(
                ['cod_gea' => $row['cod_gea']],
                [
                    'ani_gea' => $row['ani_gea'],
                    'fii_gea' => $row['fii_gea'],
                    'ffi_gea' => $row['ffi_gea'],
                    'est_gea' => $row['est_gea'],
                ]
            );
        }

        // 2. Configuración Calendario Gestión (CCG_2026_T1..T3)
        foreach ($data['configuracion_calendario_gestion'] as $row) {
            ConfiguracionCalendarioGestion::updateOrCreate(
                ['cod_ccg' => $row['cod_ccg']],
                [
                    'cod_gea' => $row['cod_gea'],
                    'num_tri_ccg' => $row['num_tri_ccg'],
                    'dias_req_ccg' => $row['dias_req_ccg'],
                    'fii_tri_ccg' => $row['fii_tri_ccg'],
                    'ffi_tri_ccg' => $row['ffi_tri_ccg'],
                    'est_ccg' => $row['est_ccg'],
                ]
            );
        }

        // 3. Periodos de Evaluación (PEV_0001..PEV_0003)
        foreach ($data['periodo_evaluacion'] as $row) {
            PeriodoEvaluacion::updateOrCreate(
                ['cod_pev' => $row['cod_pev']],
                [
                    'cod_gea' => $row['cod_gea'],
                    'nom_pev' => $row['nom_pev'],
                    'ord_pev' => $row['ord_pev'] ?? 1,
                    'fii_pev' => $row['fii_pev'] ?? null,
                    'ffi_pev' => $row['ffi_pev'] ?? null,
                    'fec_cie_pev' => $row['fec_cie_pev'] ?? null,
                    'est_pev' => $row['est_pev'],
                ]
            );
        }
    }
}
