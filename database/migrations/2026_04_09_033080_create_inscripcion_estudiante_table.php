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

            $table->string('est_ins', 20)->default('ACTIVO'); // Estado inscripción

            $table->timestamps(); // Fecha de creación y actualización
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('inscripcion_estudiante');
    }
};