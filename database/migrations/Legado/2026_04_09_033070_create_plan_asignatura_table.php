<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('plan_asignatura', function (Blueprint $table) {

            $table->string('cod_pas', 20)->primary(); // Código plan asignatura

            $table->string('cod_asi', 20); // Código asignatura
            $table->foreign('cod_asi')
                  ->references('cod_asi')
                  ->on('asignatura');

            $table->string('cod_doc', 20); // Código docente
            $table->foreign('cod_doc')
                  ->references('cod_doc')
                  ->on('docente');

            $table->string('cod_cur', 20); // Código curso
            $table->foreign('cod_cur')
                  ->references('cod_cur')
                  ->on('curso');

            $table->string('cod_par', 20); // Código paralelo
            $table->foreign('cod_par')
                  ->references('cod_par')
                  ->on('paralelo');

            $table->string('cod_tur', 20); // Código turno
            $table->foreign('cod_tur')
                  ->references('cod_tur')
                  ->on('turno');

            $table->string('cod_gea', 20); // Código gestión académica
            $table->foreign('cod_gea')
                  ->references('cod_gea')
                  ->on('gestion_academica');

            $table->integer('hor_pas')->nullable(); // Horas asignadas

            $table->string('est_pas', 20)->default('ACTIVO'); // Estado plan asignatura

            $table->timestamps(); // Fecha de creación y actualización
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('plan_asignatura');
    }
};