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
        Schema::create('regente', function (Blueprint $table) {

            $table->string('cod_reg', 20)->primary(); // Código regente

            $table->string('cod_pin', 20); // Código personal institucional
            $table->foreign('cod_pin')
                  ->references('cod_pin')
                  ->on('personal_institucional')
                  ->cascadeOnDelete();

            $table->string('est_reg', 20)->default('ACTIVO'); // Estado regente

            $table->timestamps(); // Fecha de creación y actualización
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('regente');
    }
};
