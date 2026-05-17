<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('asistencia_estudiante', function (Blueprint $table) {
            $table->string('cod_asi_est', 20)->primary();

            $table->string('cod_asi_cla', 20);
            $table->string('cod_est', 20);
            $table->string('cod_est_asi', 20);
            $table->string('cod_usu_reg', 20);

            $table->unsignedSmallInteger('min_retraso')->default(0);
            $table->text('obs_asi_est')->nullable();

            $table->dateTime('fec_reg_asi_est')->nullable();

            $table->enum('est_asi_est', [
                'REGISTRADO',
                'RECTIFICADO',
                'ANULADO',
            ])->default('REGISTRADO');

            $table->timestamps();

            $table->foreign('cod_asi_cla')
                ->references('cod_asi_cla')
                ->on('asistencia_clase')
                ->restrictOnDelete()
                ->cascadeOnUpdate();

            $table->foreign('cod_est')
                ->references('cod_est')
                ->on('estudiante')
                ->restrictOnDelete()
                ->cascadeOnUpdate();

            $table->foreign('cod_est_asi')
                ->references('cod_est_asi')
                ->on('estado_asistencia')
                ->restrictOnDelete()
                ->cascadeOnUpdate();

            $table->foreign('cod_usu_reg')
                ->references('cod_usu')
                ->on('users')
                ->restrictOnDelete()
                ->cascadeOnUpdate();

            $table->unique(['cod_asi_cla', 'cod_est'], 'uq_asistencia_estudiante_unico');

            $table->index('cod_asi_cla');
            $table->index('cod_est');
            $table->index('cod_est_asi');
            $table->index('cod_usu_reg');
            $table->index('fec_reg_asi_est');
            $table->index('est_asi_est');
            $table->index(['cod_asi_cla', 'est_asi_est']);
            $table->index(['cod_est', 'est_asi_est']);
            $table->index(['cod_est_asi', 'est_asi_est']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('asistencia_estudiante');
    }
};