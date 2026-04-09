<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('docente', function (Blueprint $table) {

            $table->string('cod_doc', 20)->primary(); // Código docente

            $table->string('cod_pin', 20); // Código personal institucional
            $table->foreign('cod_pin')
                  ->references('cod_pin')
                  ->on('personal_institucional')
                  ->cascadeOnDelete();

            $table->string('esp_doc', 150)->nullable(); // Especialidad docente

            $table->string('est_doc', 20)->default('ACTIVO'); // Estado docente

            $table->timestamps(); // Fecha de creación y actualización
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('docente');
    }
};