<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tipo_vinculacion_estudiante', function (Blueprint $table) {

            $table->string('cod_tve', 20)->primary(); // Código tipo vinculación estudiante

            $table->string('nom_tve', 100); // Nombre tipo vinculación

            $table->string('des_tve', 255)->nullable(); // Descripción tipo vinculación

            $table->string('est_tve', 20)->default('ACTIVO'); // Estado tipo vinculación

            $table->timestamps(); // Fecha de creación y actualización
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tipo_vinculacion_estudiante');
    }
};