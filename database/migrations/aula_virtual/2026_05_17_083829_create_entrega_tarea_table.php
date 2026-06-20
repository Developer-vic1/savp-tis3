<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('entrega_tarea', function (Blueprint $table) {
            $table->string('cod_ent', 20)->primary();

            $table->string('cod_tar', 20);
            $table->string('cod_est', 20);

            $table->dateTime('fec_ent')->nullable();
            $table->text('tex_ent')->nullable();

            $table->enum('est_ent', [
                'PENDIENTE',
                'ENTREGADO',
                'ENTREGADO_TARDE',
                'DEVUELTO',
                'CALIFICADO',
                'ANULADO',
            ])->default('PENDIENTE');

            $table->text('obs_ent')->nullable();

            $table->timestamps();

            $table->foreign('cod_tar')
                ->references('cod_tar')
                ->on('tarea')
                ->restrictOnDelete()
                ->cascadeOnUpdate();

            $table->foreign('cod_est')
                ->references('cod_est')
                ->on('estudiante')
                ->restrictOnDelete()
                ->cascadeOnUpdate();

            $table->index('cod_tar');
            $table->index('cod_est');
            $table->index('fec_ent');
            $table->index('est_ent');
            $table->index(['cod_tar', 'cod_est']);
            $table->index(['cod_tar', 'est_ent']);
            $table->index(['cod_est', 'est_ent']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('entrega_tarea');
    }
};