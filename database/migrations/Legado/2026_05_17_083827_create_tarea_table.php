<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tarea', function (Blueprint $table) {
            $table->string('cod_tar', 20)->primary();

            $table->string('cod_cla', 20);
            $table->string('cod_doc', 20);

            $table->string('tit_tar', 180);
            $table->text('des_tar')->nullable();

            $table->enum('tip_tar', [
                'TAREA',
                'PRACTICA',
                'PROYECTO',
                'INVESTIGACION',
                'LABORATORIO',
                'EVALUACION',
            ])->default('TAREA');

            $table->dateTime('fec_pub_tar')->nullable();
            $table->dateTime('fec_lim_tar')->nullable();

            $table->decimal('pun_max_tar', 6, 2)->default(100);
            $table->boolean('perm_ent_tardia')->default(false);

            $table->enum('est_tar', [
                'BORRADOR',
                'PUBLICADA',
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

            $table->index('cod_cla');
            $table->index('cod_doc');
            $table->index('tip_tar');
            $table->index('est_tar');
            $table->index('fec_pub_tar');
            $table->index('fec_lim_tar');
            $table->index(['cod_cla', 'est_tar']);
            $table->index(['cod_cla', 'tip_tar']);
            $table->index(['cod_doc', 'est_tar']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tarea');
    }
};
