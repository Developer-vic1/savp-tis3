<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('publicacion_clase', function (Blueprint $table) {
            $table->string('cod_pub', 20)->primary();

            $table->string('cod_cla', 20);
            $table->string('cod_usu', 20);

            $table->enum('tip_pub', [
                'ANUNCIO',
                'AVISO',
                'MATERIAL',
                'RECORDATORIO',
                'GENERAL',
            ])->default('GENERAL');

            $table->string('tit_pub', 180);
            $table->text('con_pub')->nullable();

            $table->dateTime('fec_pub')->nullable();

            $table->enum('est_pub', [
                'BORRADOR',
                'PUBLICADO',
                'OCULTO',
                'ANULADO',
            ])->default('BORRADOR');

            $table->timestamps();

            $table->foreign('cod_cla')
                ->references('cod_cla')
                ->on('clase_virtual')
                ->restrictOnDelete()
                ->cascadeOnUpdate();

            $table->foreign('cod_usu')
                ->references('cod_usu')
                ->on('users')
                ->restrictOnDelete()
                ->cascadeOnUpdate();

            $table->index('cod_cla');
            $table->index('cod_usu');
            $table->index('tip_pub');
            $table->index('est_pub');
            $table->index('fec_pub');
            $table->index(['cod_cla', 'est_pub']);
            $table->index(['cod_cla', 'tip_pub']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('publicacion_clase');
    }
};