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
        Schema::create('director', function (Blueprint $table) {

            $table->string('cod_dir', 20)->primary(); // Código director

            $table->string('cod_pin', 20); // Código personal institucional
            $table->foreign('cod_pin')
                  ->references('cod_pin')
                  ->on('personal_institucional')
                  ->cascadeOnDelete();

            $table->string('est_dir', 20)->default('ACTIVO'); // Estado director

            $table->timestamps(); // Fecha de creación y actualización
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('director');
    }
};
