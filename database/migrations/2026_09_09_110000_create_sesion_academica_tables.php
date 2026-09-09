<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Sesiones académicas y configuración de días por gestión.
     *
     * sesion_academica
     *   Representa la ocurrencia real de un horario_detalle en una fecha concreta.
     *   La generación es IDEMPOTENTE: (cod_hde, fec_ses) tiene restricción UNIQUE.
     *
     * configuracion_calendario_gestion
     *   Almacena los días lectivos objetivo por gestión y trimestre, reemplazando
     *   constantes dispersas. Para 2026: Trim1=66, Trim2=68, Trim3=66 (total=200).
     */
    public function up(): void
    {
        // ─── Configuración de días por gestión ───────────────────────────────
        Schema::create('configuracion_calendario_gestion', function (Blueprint $table) {
            $table->string('cod_ccg', 20)->primary();
            $table->string('cod_gea', 20);
            $table->smallInteger('num_tri_ccg')->comment('Número de trimestre (1,2,3…)');
            $table->smallInteger('dias_req_ccg')->comment('Días lectivos requeridos para este trimestre');
            $table->date('fii_tri_ccg')->nullable()->comment('Fecha inicio del trimestre base');
            $table->date('ffi_tri_ccg')->nullable()->comment('Fecha fin del trimestre base');
            $table->string('est_ccg', 20)->default('ACTIVO');
            $table->timestamps();

            $table->foreign('cod_gea')->references('cod_gea')->on('gestion_academica')->restrictOnDelete()->cascadeOnUpdate();
            $table->unique(['cod_gea', 'num_tri_ccg'], 'uq_ccg_gestion_trimestre');
            $table->index('cod_gea');
        });

        DB::statement('ALTER TABLE configuracion_calendario_gestion ADD CONSTRAINT ccg_dias CHECK (dias_req_ccg > 0 AND dias_req_ccg <= 365)');
        DB::statement("ALTER TABLE configuracion_calendario_gestion ADD CONSTRAINT ccg_estado CHECK (est_ccg IN ('ACTIVO','INACTIVO'))");
        DB::statement('ALTER TABLE configuracion_calendario_gestion ADD CONSTRAINT ccg_trimestre CHECK (num_tri_ccg > 0)');

        // ─── Sesión académica ────────────────────────────────────────────────
        Schema::create('sesion_academica', function (Blueprint $table) {
            $table->string('cod_ses', 30)->primary();
            // Vínculo con el horario real
            $table->string('cod_hde', 20)->comment('horario_detalle que origina la sesión');
            $table->string('cod_gea', 20)->comment('Gestión académica de la sesión');
            // Fecha y horas
            $table->date('fec_ses')->comment('Fecha en que ocurre la sesión');
            $table->decimal('hor_pla_ses', 5, 2)->default(1)->comment('Horas planificadas según horario');
            $table->decimal('hor_rea_ses', 5, 2)->nullable()->comment('Horas efectivamente realizadas');
            // Estado
            $table->string('est_ses', 20)->default('PROGRAMADA')
                ->comment('PROGRAMADA|REALIZADA|SUSPENDIDA|PARCIAL|REPROGRAMADA|RECUPERADA|CANCELADA');
            // Evento causante de la afectación (si aplica)
            $table->string('cod_cae', 20)->nullable()->comment('Evento del calendario que causó la afectación');
            // Sesión origen cuando es recuperación
            $table->string('cod_ses_ori', 30)->nullable()->comment('Sesión original que esta sesión recupera');
            $table->text('obs_ses')->nullable()->comment('Observaciones adicionales');
            $table->timestamps();

            // FK
            $table->foreign('cod_hde')->references('cod_hde')->on('horario_detalle')->restrictOnDelete()->cascadeOnUpdate();
            $table->foreign('cod_gea')->references('cod_gea')->on('gestion_academica')->restrictOnDelete()->cascadeOnUpdate();
            $table->foreign('cod_cae')->references('cod_cae')->on('calendario_evento')->restrictOnDelete()->cascadeOnUpdate();

            // IDEMPOTENCIA: no puede haber dos sesiones del mismo horario_detalle en la misma fecha
            $table->unique(['cod_hde', 'fec_ses'], 'uq_sesion_hde_fecha');

            // Índices para consultas frecuentes
            $table->index(['cod_gea', 'fec_ses', 'est_ses'], 'idx_ses_gestion_fecha');
            $table->index(['cod_hde', 'est_ses'], 'idx_ses_hde_estado');
            $table->index('cod_cae', 'idx_ses_evento');
            $table->index('cod_ses_ori', 'idx_ses_origen');
        });

        // Auto-referencia para sesión origen
        Schema::table('sesion_academica', function (Blueprint $table) {
            $table->foreign('cod_ses_ori')->references('cod_ses')->on('sesion_academica')->restrictOnDelete();
        });

        // Restricciones de integridad
        DB::statement("ALTER TABLE sesion_academica ADD CONSTRAINT ses_estado CHECK (est_ses IN ('PROGRAMADA','REALIZADA','SUSPENDIDA','PARCIAL','REPROGRAMADA','RECUPERADA','CANCELADA'))");
        DB::statement('ALTER TABLE sesion_academica ADD CONSTRAINT ses_horas_reales CHECK (hor_rea_ses IS NULL OR hor_rea_ses >= 0)');
        DB::statement('ALTER TABLE sesion_academica ADD CONSTRAINT ses_horas_planificadas CHECK (hor_pla_ses > 0)');
        // Una sesión de recuperación/reprogramación no puede tenerse a sí misma como origen
        DB::statement('ALTER TABLE sesion_academica ADD CONSTRAINT ses_origen_coherente CHECK (cod_ses_ori IS NULL OR cod_ses_ori <> cod_ses)');
        // El evento causante solo aplica cuando hay una afectación real
        DB::statement("ALTER TABLE sesion_academica ADD CONSTRAINT ses_evento_coherente CHECK (cod_cae IS NULL OR est_ses IN ('SUSPENDIDA','PARCIAL','REPROGRAMADA','CANCELADA','RECUPERADA'))");
    }

    public function down(): void
    {
        throw new RuntimeException('Las sesiones académicas forman parte del historial institucional. Revierta mediante una migración correctiva.');
    }
};
