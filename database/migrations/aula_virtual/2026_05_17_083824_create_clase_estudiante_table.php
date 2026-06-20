<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('clase_estudiante', function (Blueprint $table) {
            $table->string('cod_cla_est', 20)->primary();

            $table->string('cod_cla', 20);
            $table->string('cod_est', 20);

            $table->date('fec_inc_cla_est')->nullable();
            $table->date('fec_ret_cla_est')->nullable();

            $table->timestamp('ult_acc_cla_est')->nullable();
            $table->timestamp('ult_act_cla_est')->nullable();
            $table->unsignedInteger('cant_acc_cla_est')->default(0);

            $table->enum('est_cla_est', [
                'ACTIVO',
                'RETIRADO',
                'TRANSFERIDO',
                'INACTIVO',
                'ANULADO',
            ])->default('ACTIVO');

            $table->timestamps();

            $table->foreign('cod_cla')
                ->references('cod_cla')
                ->on('clase_virtual')
                ->restrictOnDelete()
                ->cascadeOnUpdate();

            $table->foreign('cod_est')
                ->references('cod_est')
                ->on('estudiante')
                ->restrictOnDelete()
                ->cascadeOnUpdate();

            $table->unique(['cod_cla', 'cod_est'], 'uq_clase_estudiante_unico');

            $table->index('cod_cla');
            $table->index('cod_est');
            $table->index('est_cla_est');
            $table->index('ult_acc_cla_est');
            $table->index(['cod_cla', 'est_cla_est']);
            $table->index(['cod_est', 'est_cla_est']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('clase_estudiante');
    }
};