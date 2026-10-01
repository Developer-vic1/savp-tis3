<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/** MIG-004. Metas personales; no duplica plan_asignatura ni resultados vocacionales. */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('metas_academicas', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('cod_est', 20);
            $table->string('cod_gea', 20)->nullable();
            $table->string('titulo', 180);
            $table->text('objetivo');
            $table->text('accion')->nullable();
            $table->date('fecha_objetivo')->nullable();
            $table->string('estado', 20)->default('BORRADOR');
            $table->string('created_by', 20);
            $table->timestampsTz();
            $table->foreign('cod_est')->references('cod_est')->on('estudiante')->restrictOnDelete()->cascadeOnUpdate();
            $table->foreign('cod_gea')->references('cod_gea')->on('gestion_academica')->restrictOnDelete()->cascadeOnUpdate();
            $table->foreign('created_by')->references('cod_usu')->on('users')->restrictOnDelete()->cascadeOnUpdate();
            $table->index(['cod_est', 'estado', 'fecha_objetivo']);
            $table->index('cod_gea');
            $table->index('created_by');
        });
        DB::statement("ALTER TABLE metas_academicas ADD CONSTRAINT academic_goal_content_check CHECK (estado IN ('BORRADOR','ACTIVA','COMPLETADA','CANCELADA') AND NULLIF(TRIM(titulo), '') IS NOT NULL AND NULLIF(TRIM(objetivo), '') IS NOT NULL)");
        Schema::create('meta_academica_revisiones', function (Blueprint $table) {
            $table->id();
            $table->uuid('meta_id');
            $table->string('cod_usu', 20);
            $table->text('motivo');
            $table->jsonb('datos');
            $table->timestampTz('created_at')->useCurrent();
            $table->foreign('meta_id')->references('id')->on('metas_academicas')->restrictOnDelete()->cascadeOnUpdate();
            $table->foreign('cod_usu')->references('cod_usu')->on('users')->restrictOnDelete()->cascadeOnUpdate();
            $table->index(['meta_id', 'created_at']);
            $table->index('cod_usu');
        });
        DB::statement("ALTER TABLE meta_academica_revisiones ADD CONSTRAINT academic_goal_revision_check CHECK (NULLIF(TRIM(motivo), '') IS NOT NULL AND jsonb_typeof(datos) = 'object')");
    }

    public function down(): void
    {
        foreach (['meta_academica_revisiones', 'metas_academicas'] as $name) {
            if (Schema::hasTable($name) && DB::table($name)->exists()) {
                throw new RuntimeException('Rollback cerrado: preservar metas y revisiones mediante una corrección aprobada.');
            }
        }
        Schema::dropIfExists('meta_academica_revisiones');
        Schema::dropIfExists('metas_academicas');
    }
};
