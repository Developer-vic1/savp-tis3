<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('documento_inscripcion_estudiante', function (Blueprint $table) {
            $table->string('cod_die', 20)->primary(); // Código documento inscripción

            $table->string('cod_ins', 20); // Código inscripción estudiante
            $table->foreign('cod_ins')
                ->references('cod_ins')
                ->on('inscripcion_estudiante')
                ->restrictOnDelete();

            $table->string('nom_die', 150); // Nombre del documento
            $table->string('tip_die', 40)->default('GENERAL'); // Tipo documental

            $table->string('est_die', 20)->default('PENDIENTE'); // PRESENTADO, PENDIENTE, OBSERVADO, NO_APLICA
            $table->text('obs_die')->nullable(); // Observación documental

            $table->date('fec_pre_die')->nullable(); // Fecha de presentación
            $table->string('registrado_por', 20)->nullable(); // Usuario que registra o actualiza

            $table->timestamps();

            $table->index('cod_ins', 'idx_documento_inscripcion_cod_ins');
            $table->index('est_die', 'idx_documento_inscripcion_est_die');
            $table->index('tip_die', 'idx_documento_inscripcion_tip_die');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('documento_inscripcion_estudiante');
    }
};