<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('periodo_evaluacion', function (Blueprint $table) {

            $table->string('cod_pev', 20)->primary(); // Código periodo evaluación

            $table->string('nom_pev', 100); // Nombre periodo evaluación

            $table->integer('ord_pev')->nullable(); // Orden periodo evaluación

            $table->string('est_pev', 20)->default('ACTIVO'); // Estado periodo evaluación

            $table->timestamps(); // Fecha de creación y actualización
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('periodo_evaluacion');
    }
};