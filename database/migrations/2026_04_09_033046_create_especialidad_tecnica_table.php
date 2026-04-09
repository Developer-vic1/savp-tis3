<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('especialidad_tecnica', function (Blueprint $table) {

            $table->string('cod_esp', 20)->primary(); // Código especialidad técnica

            $table->string('nom_esp', 150); // Nombre especialidad técnica
            $table->string('des_esp', 255)->nullable(); // Descripción especialidad técnica

            $table->string('est_esp', 20)->default('ACTIVO'); // Estado especialidad técnica

            $table->timestamps(); // Fecha de creación y actualización
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('especialidad_tecnica');
    }
};