<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('turno', function (Blueprint $table) {

            $table->string('cod_tur', 20)->primary(); // Código turno

            $table->string('nom_tur', 50); // Nombre turno

            $table->string('hor_ini_tur', 10)->nullable(); // Hora inicio turno
            $table->string('hor_fin_tur', 10)->nullable(); // Hora fin turno

            $table->string('est_tur', 20)->default('ACTIVO'); // Estado turno

            $table->timestamps(); // Fecha de creación y actualización
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('turno');
    }
};