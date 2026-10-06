<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('referencia_documental',function(Blueprint $t){
            $t->uuid('id')->primary();
            $t->string('tipo',20);
            $t->string('cod_pin',20)->nullable();
            $t->string('titular',150);
            $t->string('ruta');
            $t->string('sha256',64);
            $t->boolean('vigente')->default(true);
            $t->string('cod_usu',20)->nullable();
            $t->timestamps();
            $t->index(['tipo','cod_pin','vigente']);
        });
    }
    public function down(): void { Schema::dropIfExists('referencia_documental'); }
};
