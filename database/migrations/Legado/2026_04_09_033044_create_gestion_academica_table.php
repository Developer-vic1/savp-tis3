<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('gestion_academica', function (Blueprint $table) {

            $table->string('cod_gea', 20)->primary(); // Código gestión académica

            $table->integer('ani_gea'); // Año gestión académica

            $table->date('fii_gea')->nullable(); // Fecha inicio gestión
            $table->date('ffi_gea')->nullable(); // Fecha fin gestión

            $table->string('est_gea', 20)->default('ACTIVO'); // Estado gestión académica

            $table->timestamps(); // Fecha de creación y actualización
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('gestion_academica');
    }
};