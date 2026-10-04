<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('calificacion', function (Blueprint $table) {

            $table->string('cod_cal', 20)->primary(); // Código calificación

            $table->string('cod_est', 20); // Código estudiante
            $table->foreign('cod_est')
                  ->references('cod_est')
                  ->on('estudiante')
                  ->cascadeOnDelete();

            $table->string('cod_asi', 20); // Código asignatura
            $table->foreign('cod_asi')
                  ->references('cod_asi')
                  ->on('asignatura');

            $table->string('cod_pev', 20); // Código periodo evaluación
            $table->foreign('cod_pev')
                  ->references('cod_pev')
                  ->on('periodo_evaluacion');

            $table->decimal('not_cal', 5, 2); // Nota calificación

            $table->string('obs_cal', 255)->nullable(); // Observación calificación

            $table->string('est_cal', 20)->default('ACTIVO'); // Estado calificación

            $table->timestamps(); // Fecha de creación y actualización
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('calificacion');
    }
};