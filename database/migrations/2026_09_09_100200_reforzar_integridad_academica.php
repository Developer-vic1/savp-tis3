<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('periodo_evaluacion', function (Blueprint $table) {
            $table->string('cod_gea', 20)->nullable();
            $table->date('fii_pev')->nullable();
            $table->date('ffi_pev')->nullable();
            $table->timestamp('fec_cie_pev')->nullable();
            $table->foreign('cod_gea')->references('cod_gea')->on('gestion_academica')->restrictOnDelete();
            $table->unique(['cod_gea', 'ord_pev'], 'uq_periodo_gestion_orden');
        });
        Schema::table('calificacion', function (Blueprint $table) {
            $table->string('cod_pas', 20)->nullable();
            $table->foreign('cod_pas')->references('cod_pas')->on('plan_asignatura')->restrictOnDelete();
            $table->dropForeign(['cod_est']);
        });
        Schema::table('calificacion', function (Blueprint $table) {
            $table->foreign('cod_est')->references('cod_est')->on('estudiante')->restrictOnDelete();
        });
        // Los periodos anteriores son catálogos globales sin fechas ni gestión.
        // No se infiere una identidad académica desde created_at o desde la matrícula actual.
        $pendientes = DB::table('calificacion')->whereNull('cod_pas')->count();
        if ($pendientes) {
            Log::warning('PREVENCIONES_CALIFICACIONES_REVISION', ['cantidad' => $pendientes, 'motivo' => 'Asignar plan y gestión/fechas del periodo desde respaldo académico; la estructura previa no permite reconstrucción inequívoca.']);
        }
        Schema::table('calificacion', fn (Blueprint $table) => $table->unique(['cod_est', 'cod_pas', 'cod_pev'], 'uq_calificacion_est_plan_periodo'));

        $reglas = [
            'inscripcion_estado_fechas_integral' => "(fec_ret_ins IS NULL OR fec_anu_ins IS NULL) AND (est_ins <> 'RETIRADA' OR (fec_ret_ins IS NOT NULL AND NULLIF(TRIM(mot_ret_ins), '') IS NOT NULL AND fec_anu_ins IS NULL)) AND (est_ins <> 'ANULADA' OR (fec_anu_ins IS NOT NULL AND NULLIF(TRIM(mot_anu_ins), '') IS NOT NULL AND fec_ret_ins IS NULL))",
        ];
        foreach ($reglas as $nombre => $regla) {
            DB::statement("ALTER TABLE inscripcion_estudiante ADD CONSTRAINT {$nombre} CHECK ({$regla}) NOT VALID");
            $inconsistencias = DB::table('inscripcion_estudiante')->whereRaw("NOT ({$regla})")->pluck('cod_ins');
            if ($inconsistencias->isEmpty()) {
                DB::statement("ALTER TABLE inscripcion_estudiante VALIDATE CONSTRAINT {$nombre}");
            } else {
                Log::warning('PREVENCIONES_INSCRIPCIONES_REVISION', ['codigos' => $inconsistencias->all()]);
            }
        }
        DB::statement('ALTER TABLE periodo_evaluacion ADD CONSTRAINT pev_fechas CHECK (ffi_pev IS NULL OR fii_pev IS NULL OR ffi_pev >= fii_pev)');
        DB::statement('ALTER TABLE calificacion ADD CONSTRAINT cal_rango CHECK (not_cal >= 0 AND not_cal <= 100) NOT VALID');
        if (! DB::table('calificacion')->where(fn ($q) => $q->where('not_cal', '<', 0)->orWhere('not_cal', '>', 100))->exists()) {
            DB::statement('ALTER TABLE calificacion VALIDATE CONSTRAINT cal_rango');
        }
        $duplicados = DB::table('asistencia_clase')->select('cod_cla', 'cod_hbl', 'fec_asi_cla')->groupBy('cod_cla', 'cod_hbl', 'fec_asi_cla')->havingRaw('count(*) > 1')->get();
        if ($duplicados->isNotEmpty()) {
            throw new RuntimeException('Existen asistencias duplicadas por clase/bloque/fecha. Se requiere revisión preservando evidencias antes de agregar unicidad.');
        }
        Schema::table('asistencia_clase', function (Blueprint $table) {
            $table->dropUnique('uq_asistencia_clase_bloque_estado');
        });
        DB::statement('CREATE UNIQUE INDEX uq_asistencia_clase_bloque_fecha ON asistencia_clase (cod_cla,cod_hbl,fec_asi_cla) NULLS NOT DISTINCT');
    }

    public function down(): void
    {
        throw new RuntimeException('La integridad académica debe revertirse mediante una migración correctiva sin pérdida de datos.');
    }
};
