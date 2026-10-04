<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('estudiante', function (Blueprint $table) {

            // 🔑 PK
            $table->string('cod_est', 20)->primary();

            // 🔹 RUDE (código único del estudiante)
            $table->string('rud_est', 20)->unique();

            // 🔗 FK persona
            $table->string('cod_per', 20);
            $table->foreign('cod_per')
                  ->references('cod_per')
                  ->on('persona')
                  ->cascadeOnDelete();

            // 🔗 FK tipo vinculación
            $table->string('cod_tve', 20);
            $table->foreign('cod_tve')
                  ->references('cod_tve')
                  ->on('tipo_vinculacion_estudiante');

            // 🔗 FK institución procedencia
            $table->string('cod_ipe', 20)->nullable();
            $table->foreign('cod_ipe')
                  ->references('cod_ipe')
                  ->on('institucion_procedencia');

            // 🔗 FK especialidad técnica
            $table->string('cod_esp', 20)->nullable();
            $table->foreign('cod_esp')
                  ->references('cod_esp')
                  ->on('especialidad_tecnica');

            // 🔹 Estado
            $table->string('est_est', 20)->default('ACTIVO');

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('estudiante');
    }
};