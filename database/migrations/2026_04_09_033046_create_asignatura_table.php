<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('asignatura', function (Blueprint $table) {

            $table->string('cod_asi', 20)->primary(); // Código asignatura

            $table->string('nom_asi', 150); // Nombre asignatura
            $table->string('sig_asi', 20)->nullable(); // Sigla asignatura

            $table->integer('hor_asi')->nullable(); // Horas académicas

            $table->string('est_asi', 20)->default('ACTIVO'); // Estado asignatura

            $table->timestamps(); // Fecha de creación y actualización
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('asignatura');
    }
};