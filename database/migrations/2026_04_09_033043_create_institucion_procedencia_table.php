<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('institucion_procedencia', function (Blueprint $table) {

            $table->string('cod_ipe', 20)->primary(); // Código institución procedencia

            $table->string('nom_ipe', 150); // Nombre institución
            $table->string('tip_ipe', 50)->nullable(); // Tipo institución
            $table->string('ciu_ipe', 100)->nullable(); // Ciudad institución

            $table->string('est_ipe', 20)->default('ACTIVO'); // Estado institución

            $table->timestamps(); // Fecha de creación y actualización
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('institucion_procedencia');
    }
};