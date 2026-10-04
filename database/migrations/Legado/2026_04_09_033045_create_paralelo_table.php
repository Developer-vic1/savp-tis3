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
            $table->string('nom_par', 50); // Nombre paralelo: A, B, C, D
            $table->string('est_par', 20)->default('ACTIVO'); // Estado paralelo
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('paralelo');
    }
};
