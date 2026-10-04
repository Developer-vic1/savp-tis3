<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('actividad_clase', function (Blueprint $table) {
            $table->string('cod_act_cla', 20)->primary();

            $table->string('cod_cla', 20);
            $table->string('cod_est', 20)->nullable();
            $table->string('cod_usu', 20);

            $table->enum('tip_act', [
                'INGRESO_CLASE',
                'VIO_MATERIAL',
                'ABRIO_TAREA',
                'ENTREGO_TAREA',
                'ENTREGA_TARDIA',
                'RECIBIO_CALIFICACION',
                'REGISTRO_ASISTENCIA',
                'AUSENCIA_REGISTRADA',
                'ATRASO_REGISTRADO',
                'DESCARGO_ARCHIVO',
                'COMENTO',
                'OTRO',
            ])->default('OTRO');

            $table->string('ref_tab', 80)->nullable();
            $table->string('ref_cod', 30)->nullable();

            $table->dateTime('fec_act')->nullable();
            $table->json('met_act')->nullable();

            $table->timestamps();

            $table->foreign('cod_cla')
                ->references('cod_cla')
                ->on('clase_virtual')
                ->restrictOnDelete()
                ->cascadeOnUpdate();

            $table->foreign('cod_est')
                ->references('cod_est')
                ->on('estudiante')
                ->nullOnDelete()
                ->cascadeOnUpdate();

            $table->foreign('cod_usu')
                ->references('cod_usu')
                ->on('users')
                ->restrictOnDelete()
                ->cascadeOnUpdate();

            $table->index('cod_cla');
            $table->index('cod_est');
            $table->index('cod_usu');
            $table->index('tip_act');
            $table->index('fec_act');
            $table->index(['cod_cla', 'tip_act']);
            $table->index(['cod_cla', 'fec_act']);
            $table->index(['cod_est', 'fec_act']);
            $table->index(['ref_tab', 'ref_cod']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('actividad_clase');
    }
};