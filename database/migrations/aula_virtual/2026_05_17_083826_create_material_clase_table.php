<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('material_clase', function (Blueprint $table) {
            $table->string('cod_mat', 20)->primary();

            $table->string('cod_cla', 20);
            $table->string('cod_pub', 20)->nullable();
            $table->string('cod_usu', 20);

            $table->string('nom_mat', 180);
            $table->enum('tip_mat', [
                'ARCHIVO',
                'ENLACE',
                'PDF',
                'VIDEO',
                'IMAGEN',
                'DOCUMENTO',
                'OTRO',
            ])->default('ARCHIVO');

            $table->string('rut_mat', 255)->nullable();
            $table->string('url_mat', 500)->nullable();
            $table->string('mime_mat', 120)->nullable();
            $table->unsignedBigInteger('tam_mat')->nullable();

            $table->enum('est_mat', [
                'ACTIVO',
                'OCULTO',
                'ANULADO',
            ])->default('ACTIVO');

            $table->timestamps();

            $table->foreign('cod_cla')
                ->references('cod_cla')
                ->on('clase_virtual')
                ->restrictOnDelete()
                ->cascadeOnUpdate();

            $table->foreign('cod_pub')
                ->references('cod_pub')
                ->on('publicacion_clase')
                ->nullOnDelete()
                ->cascadeOnUpdate();

            $table->foreign('cod_usu')
                ->references('cod_usu')
                ->on('users')
                ->restrictOnDelete()
                ->cascadeOnUpdate();

            $table->index('cod_cla');
            $table->index('cod_pub');
            $table->index('cod_usu');
            $table->index('tip_mat');
            $table->index('est_mat');
            $table->index(['cod_cla', 'est_mat']);
            $table->index(['cod_cla', 'tip_mat']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('material_clase');
    }
};
