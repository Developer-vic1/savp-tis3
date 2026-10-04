<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('plan_especialidad', function (Blueprint $table) {

            $table->string('cod_pes', 20)->primary(); // Código plan especialidad

            $table->string('cod_esp', 20); // Código especialidad técnica
            $table->foreign('cod_esp')
                ->references('cod_esp')
                ->on('especialidad_tecnica')
                ->restrictOnDelete()
                ->cascadeOnUpdate();

            $table->string('cod_doc', 20); // Código docente
            $table->foreign('cod_doc')
                ->references('cod_doc')
                ->on('docente')
                ->restrictOnDelete()
                ->cascadeOnUpdate();

            $table->string('cod_cur', 20); // Código curso
            $table->foreign('cod_cur')
                ->references('cod_cur')
                ->on('curso')
                ->restrictOnDelete()
                ->cascadeOnUpdate();

            $table->string('cod_par', 20); // Código paralelo
            $table->foreign('cod_par')
                ->references('cod_par')
                ->on('paralelo')
                ->restrictOnDelete()
                ->cascadeOnUpdate();

            $table->string('cod_tur', 20); // Código turno
            $table->foreign('cod_tur')
                ->references('cod_tur')
                ->on('turno')
                ->restrictOnDelete()
                ->cascadeOnUpdate();

            $table->string('cod_gea', 20); // Código gestión académica
            $table->foreign('cod_gea')
                ->references('cod_gea')
                ->on('gestion_academica')
                ->restrictOnDelete()
                ->cascadeOnUpdate();

            $table->integer('hor_pes')->default(0); // Horas asignadas a la especialidad

            $table->string('est_pes', 20)->default('ACTIVO'); // Estado plan especialidad

            $table->timestamps();

            $table->unique([
                'cod_esp',
                'cod_doc',
                'cod_cur',
                'cod_par',
                'cod_tur',
                'cod_gea',
            ], 'uq_plan_especialidad_contexto');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('plan_especialidad');
    }
};