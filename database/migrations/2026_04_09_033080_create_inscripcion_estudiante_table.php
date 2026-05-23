<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('inscripcion_estudiante', function (Blueprint $table) {

            $table->string('cod_ins', 20)->primary(); // Código inscripción estudiante

            $table->string('cod_est', 20); // Código estudiante
            $table->foreign('cod_est')
                  ->references('cod_est')
                  ->on('estudiante')
                  ->cascadeOnDelete();

            $table->string('cod_cur', 20); // Código curso
            $table->foreign('cod_cur')
                  ->references('cod_cur')
                  ->on('curso');

            $table->string('cod_par', 20); // Código paralelo
            $table->foreign('cod_par')
                  ->references('cod_par')
                  ->on('paralelo');

            $table->string('cod_tur', 20); // Código turno
            $table->foreign('cod_tur')
                  ->references('cod_tur')
                  ->on('turno');

            $table->string('cod_gea', 20); // Código gestión académica
            $table->foreign('cod_gea')
                  ->references('cod_gea')
                  ->on('gestion_academica');

            $table->date('fei_ins'); // Fecha inscripción

            $table->string('tip_ins', 30)->default('REGULAR'); // Tipo de inscripción: NUEVO, REGULAR, TRASLADO, etc.
            $table->string('con_ins', 30)->default('NORMAL'); // Condición de inscripción: NORMAL, OBSERVADA, CONDICIONAL, etc.

            $table->text('obs_ins')->nullable(); // Observación general de la inscripción
            $table->text('mot_obs_ins')->nullable(); // Motivo de observación o condición especial
            $table->string('pro_ins', 180)->nullable(); // Procedencia del estudiante en caso de traslado

            $table->boolean('doc_com_ins')->default(false); // Documentos completos
            $table->boolean('sob_aut_ins')->default(false); // Sobrecupo autorizado

            $table->string('registrado_por', 20)->nullable(); // Usuario que registra la inscripción

            $table->timestamp('fec_con_ins')->nullable(); // Fecha de confirmación
            $table->string('confirmado_por', 20)->nullable(); // Usuario que confirma

            $table->timestamp('fec_anu_ins')->nullable(); // Fecha de anulación
            $table->string('anulado_por', 20)->nullable(); // Usuario que anula
            $table->text('mot_anu_ins')->nullable(); // Motivo de anulación

            $table->string('est_ins', 20)->default('ACTIVO'); // Estado inscripción

            $table->timestamps(); // Fecha de creación y actualización
            $table->unique(['cod_est', 'cod_gea'], 'uq_inscripcion_estudiante_por_gestion');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('inscripcion_estudiante');
    }
};