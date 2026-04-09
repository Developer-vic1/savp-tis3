<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('paralelo', function (Blueprint $table) {

            $table->string('cod_par', 20)->primary(); // Código paralelo

            $table->string('nom_par', 50); // Nombre paralelo

            $table->string('cod_cur', 20); // Código curso
            $table->foreign('cod_cur')
                  ->references('cod_cur')
                  ->on('curso')
                  ->cascadeOnDelete();

            $table->string('est_par', 20)->default('ACTIVO'); // Estado paralelo

            $table->timestamps(); // Fecha de creación y actualización
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('paralelo');
    }
};