<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('asistencia_clase', function (Blueprint $table) {
            $table->string('cod_asi_cla', 20)->primary();

            $table->string('cod_cla', 20);
            $table->string('cod_doc', 20);
            $table->string('cod_hbl', 20)->nullable();
            $table->string('cod_usu_reg', 20);

            $table->date('fec_asi_cla');

            $table->time('hor_ini_asi_cla')->nullable();
            $table->time('hor_fin_asi_cla')->nullable();

            $table->enum('tip_asi_cla', [
                'CLASE',
                'LABORATORIO',
                'PRACTICA',
                'EVALUACION',
                'ACTIVIDAD',
            ])->default('CLASE');

            $table->string('tit_asi_cla', 150)->nullable();
            $table->text('obs_asi_cla')->nullable();

            $table->enum('ori_asi_cla', [
                'MANUAL',
                'GENERADA',
                'IMPORTADA',
            ])->default('MANUAL');

            $table->enum('est_asi_cla', [
                'BORRADOR',
                'ABIERTA',
                'CERRADA',
                'ANULADA',
            ])->default('BORRADOR');

            $table->timestamps();

            $table->foreign('cod_cla')
                ->references('cod_cla')
                ->on('clase_virtual')
                ->restrictOnDelete()
                ->cascadeOnUpdate();

            $table->foreign('cod_doc')
                ->references('cod_doc')
                ->on('docente')
                ->restrictOnDelete()
                ->cascadeOnUpdate();

            $table->foreign('cod_hbl')
                ->references('cod_hbl')
                ->on('horario_bloque')
                ->nullOnDelete()
                ->cascadeOnUpdate();

            $table->foreign('cod_usu_reg')
                ->references('cod_usu')
                ->on('users')
                ->restrictOnDelete()
                ->cascadeOnUpdate();

            $table->index('cod_cla');
            $table->index('cod_doc');
            $table->index('cod_hbl');
            $table->index('cod_usu_reg');
            $table->index('fec_asi_cla');
            $table->index('tip_asi_cla');
            $table->index('ori_asi_cla');
            $table->index('est_asi_cla');
            $table->index(['cod_cla', 'fec_asi_cla']);
            $table->index(['cod_cla', 'est_asi_cla']);
            $table->index(['cod_doc', 'fec_asi_cla']);

            $table->unique(
                ['cod_cla', 'cod_hbl', 'fec_asi_cla', 'est_asi_cla'],
                'uq_asistencia_clase_bloque_estado'
            );
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('asistencia_clase');
    }
};