<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('inscripcion_estudiante')->orderBy('cod_ins')->each(function ($inscripcion) {
            if (DB::table('inscripcion_vigencia')->where('cod_ins', $inscripcion->cod_ins)->exists()) {
                return;
            }
            $estado = $inscripcion->est_ins;
            $fin = null;
            $cierre = null;
            if (! in_array($estado, ['ACTIVA', 'CONFIRMADA', 'OBSERVADA', 'RETIRADA'], true)) {
                if (in_array($estado, ['ANULADA', 'ARCHIVADA'], true)) {
                    Log::warning('PREVENCIONES_BACKFILL_REVISION', ['cod_ins' => $inscripcion->cod_ins, 'estado' => $estado, 'motivo' => 'Revisar actividad y fechas históricas; no se presume una vigencia válida ni una fecha de cierre.']);
                }

                return;
            }
            if ($estado === 'RETIRADA') {
                $fin = $inscripcion->fec_ret_ins ? substr($inscripcion->fec_ret_ins, 0, 10) : null;
                $cierre = 'RETIRO';
                if (! $fin || $fin < $inscripcion->fei_ins || trim($inscripcion->mot_ret_ins ?? '') === '' || $inscripcion->fec_anu_ins) {
                    Log::warning('PREVENCIONES_BACKFILL_REVISION', ['cod_ins' => $inscripcion->cod_ins, 'motivo' => 'Retiro con fecha/motivo incompleto o anulación simultánea.']);

                    return;
                }
            }
            DB::table('inscripcion_vigencia')->insert([
                'cod_ivg' => 'IVG_'.substr(hash('sha256', $inscripcion->cod_ins), 0, 16),
                'cod_ins' => $inscripcion->cod_ins,
                'cod_cur' => $inscripcion->cod_cur,
                'cod_par' => $inscripcion->cod_par,
                'cod_tur' => $inscripcion->cod_tur,
                'cod_esp_tec' => $inscripcion->cod_esp_tec,
                'fii_ivg' => $inscripcion->fei_ins,
                'ffi_ivg' => $fin,
                'tip_ivg' => 'INICIAL',
                'cie_ivg' => $cierre,
                'mot_ivg' => $cierre ? trim($inscripcion->mot_ret_ins) : null,
                'est_ivg' => $fin ? 'CERRADA' : 'ACTIVA',
                'created_at' => now(), 'updated_at' => now(),
            ]);
        });
    }

    public function down(): void
    {
        throw new RuntimeException('El backfill histórico no se elimina.');
    }
};
