<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('regente_asignaciones', function (Blueprint $table) {
            $table->id();
            $table->string('cod_reg', 20);
            $table->string('cod_gea', 20);
            $table->string('cod_cur', 20);
            $table->boolean('activa')->default(true);
            $table->timestamps();
            $table->foreign('cod_reg')->references('cod_reg')->on('regente')->restrictOnDelete();
            $table->foreign('cod_gea')->references('cod_gea')->on('gestion_academica')->restrictOnDelete();
            $table->foreign('cod_cur')->references('cod_cur')->on('curso')->restrictOnDelete();
            $table->unique(['cod_reg', 'cod_gea', 'cod_cur'], 'regente_gestion_grado_unique');
            $table->index(['cod_gea', 'cod_cur', 'activa'], 'regente_gestion_grado_activa_index');
        });

        Schema::table('calificacion', function (Blueprint $table) {
            // Los históricos sin contexto permanecen intactos: nunca se infiere su gestión.
            $table->string('cod_pas', 20)->nullable();
            $table->index('cod_pas', 'calificacion_plan_index');
            $table->foreign('cod_pas')->references('cod_pas')->on('plan_asignatura')->restrictOnDelete();
            $table->unique(['cod_est', 'cod_pas', 'cod_pev'], 'calificacion_estudiante_plan_periodo_unique');
        });
    }

    public function down(): void
    {
        Schema::table('calificacion', function (Blueprint $table) {
            $table->dropUnique('calificacion_estudiante_plan_periodo_unique');
            $table->dropForeign(['cod_pas']);
            $table->dropIndex('calificacion_plan_index');
            $table->dropColumn('cod_pas');
        });
        Schema::dropIfExists('regente_asignaciones');
    }
};
