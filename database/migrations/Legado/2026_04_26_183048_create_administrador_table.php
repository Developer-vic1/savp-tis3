<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('administrador', function (Blueprint $table) {
            $table->string('cod_adm', 20)->primary(); // Código administrador

            $table->string('cod_pin', 20); // Código personal institucional
            $table->foreign('cod_pin')
                  ->references('cod_pin')
                  ->on('personal_institucional')
                  ->cascadeOnDelete();

            $table->string('est_adm', 20)->default('ACTIVO'); // Estado administrador

            $table->timestamps(); // Fecha de creación y actualización
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('administrador');
    }
};
