<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('curso', function (Blueprint $table) {

            $table->string('cod_cur', 20)->primary(); // Código curso

            $table->string('nom_cur', 100); // Nombre curso
            $table->string('niv_cur', 50)->nullable(); // Nivel curso

            $table->string('est_cur', 20)->default('ACTIVO'); // Estado curso

            $table->timestamps(); // Fecha de creación y actualización
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('curso');
    }
};