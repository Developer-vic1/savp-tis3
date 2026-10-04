<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. Limpiar duplicados de entrega_tarea
        // Conservar la más avanzada o más reciente. Prioridad estado: CALIFICADO > ENTREGADO > ENTREGADO_TARDE > DEVUELTO > PENDIENTE > ANULADO
        $duplicados = DB::table('entrega_tarea')
            ->select('cod_tar', 'cod_est')
            ->groupBy('cod_tar', 'cod_est')
            ->havingRaw('COUNT(*) > 1')
            ->get();

        $prioridadEstado = [
            'CALIFICADO' => 6,
            'ENTREGADO' => 5,
            'ENTREGADO_TARDE' => 4,
            'DEVUELTO' => 3,
            'PENDIENTE' => 2,
            'ANULADO' => 1,
        ];

        foreach ($duplicados as $dup) {
            $entregas = DB::table('entrega_tarea')
                ->where('cod_tar', $dup->cod_tar)
                ->where('cod_est', $dup->cod_est)
                ->get();

            // Ordenar por prioridad de estado (descendente) y fecha de entrega (descendente)
            $entregas = $entregas->sort(function ($a, $b) use ($prioridadEstado) {
                $prioA = $prioridadEstado[$a->est_ent] ?? 0;
                $prioB = $prioridadEstado[$b->est_ent] ?? 0;
                
                if ($prioA !== $prioB) {
                    return $prioB <=> $prioA;
                }
                
                return $b->fec_ent <=> $a->fec_ent;
            });

            $aConservar = $entregas->first();
            $aEliminar = $entregas->where('cod_ent', '!=', $aConservar->cod_ent)->pluck('cod_ent');

            if ($aEliminar->isNotEmpty()) {
                // Eliminar calificaciones dependientes (si existen)
                DB::table('calificacion_tarea')->whereIn('cod_ent', $aEliminar)->delete();
                // Eliminar archivos dependientes
                DB::table('entrega_archivo')->whereIn('cod_ent', $aEliminar)->delete();
                // Eliminar las entregas duplicadas
                DB::table('entrega_tarea')->whereIn('cod_ent', $aEliminar)->delete();
            }
        }

        // 2. Agregar unique constraint
        Schema::table('entrega_tarea', function (Blueprint $table) {
            $table->unique(['cod_tar', 'cod_est'], 'uq_entrega_tarea_estudiante');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('entrega_tarea', function (Blueprint $table) {
            $table->dropUnique('uq_entrega_tarea_estudiante');
        });
    }
};
