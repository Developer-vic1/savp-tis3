<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('calificacion_tarea', function (Blueprint $table) {
            $table->string('cod_cal_tar', 20)->primary();

            $table->string('cod_ent', 20);
            $table->string('cod_tar', 20);
            $table->string('cod_est', 20);
            $table->string('cod_doc', 20);

            $table->decimal('pun_obt', 6, 2)->default(0);
            $table->decimal('pun_max', 6, 2)->default(100);

            $table->text('com_cal')->nullable();
            $table->dateTime('fec_cal')->nullable();

            $table->enum('est_cal', [
                'REGISTRADO',
                'RECTIFICADO',
                'DEVUELTO',
                'ANULADO',
            ])->default('REGISTRADO');

            $table->timestamps();

            $table->foreign('cod_ent')
                ->references('cod_ent')
                ->on('entrega_tarea')
                ->restrictOnDelete()
                ->cascadeOnUpdate();

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

            $table->foreign('cod_doc')
                ->references('cod_doc')
                ->on('docente')
                ->restrictOnDelete()
                ->cascadeOnUpdate();

            $table->unique('cod_ent', 'uq_calificacion_por_entrega');

            $table->index('cod_tar');
            $table->index('cod_est');
            $table->index('cod_doc');
            $table->index('fec_cal');
            $table->index('est_cal');
            $table->index(['cod_tar', 'cod_est']);
            $table->index(['cod_doc', 'est_cal']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('calificacion_tarea');
    }
};