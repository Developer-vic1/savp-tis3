<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tarea_material', function (Blueprint $table) {
            $table->string('cod_tar_mat', 20)->primary();

            $table->string('cod_tar', 20);

            $table->string('nom_tar_mat', 180);
            $table->enum('tip_tar_mat', [
                'ARCHIVO',
                'ENLACE',
                'PDF',
                'VIDEO',
                'IMAGEN',
                'DOCUMENTO',
                'OTRO',
            ])->default('ARCHIVO');

            $table->string('rut_tar_mat', 255)->nullable();
            $table->string('url_tar_mat', 500)->nullable();
            $table->string('mime_tar_mat', 120)->nullable();
            $table->unsignedBigInteger('tam_tar_mat')->nullable();

            $table->enum('est_tar_mat', [
                'ACTIVO',
                'OCULTO',
                'ANULADO',
            ])->default('ACTIVO');

            $table->timestamps();

            $table->foreign('cod_tar')
                ->references('cod_tar')
                ->on('tarea')
                ->restrictOnDelete()
                ->cascadeOnUpdate();

            $table->index('cod_tar');
            $table->index('tip_tar_mat');
            $table->index('est_tar_mat');
            $table->index(['cod_tar', 'est_tar_mat']);
            $table->index(['cod_tar', 'tip_tar_mat']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tarea_material');
    }
};
