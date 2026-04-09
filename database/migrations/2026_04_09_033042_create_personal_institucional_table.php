<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('personal_institucional', function (Blueprint $table) {

            $table->string('cod_pin', 20)->primary(); // Código personal institucional

            $table->string('cod_per', 20); // Código persona
            $table->foreign('cod_per')
                  ->references('cod_per')
                  ->on('persona')
                  ->cascadeOnDelete();

            $table->string('car_pin', 100); // Cargo institucional

            $table->string('est_pin', 20)->default('ACTIVO'); // Estado del personal

            $table->timestamps(); // Fecha de creación y actualización
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('personal_institucional');
    }
};