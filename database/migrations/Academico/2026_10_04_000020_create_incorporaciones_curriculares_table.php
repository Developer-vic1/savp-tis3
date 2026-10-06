<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('incorporacion_curricular', function (Blueprint $t) {
            $t->uuid('id')->primary();
            $t->string('dominio',20);
            $t->string('nombre',150);
            $t->unsignedSmallInteger('gestion');
            $t->json('datos');
            $t->json('evidencia');
            $t->string('ruta_pdf');
            $t->string('sha256',64);
            $t->string('cod_usu',20);
            $t->string('estado',20)->default('PROGRAMADA');
            $t->string('registro',20)->nullable();
            $t->timestamps();
            $t->unique(['dominio','nombre','gestion']);
        });
    }
    public function down(): void { Schema::dropIfExists('incorporacion_curricular'); }
};
