<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('bitacora', function (Blueprint $table) {

            $table->string('cod_bit', 20)->primary(); // Código bitácora

            $table->string('acc_bit', 150); // Acción realizada
            $table->string('tab_bit', 100); // Tabla afectada
            $table->string('reg_bit', 50)->nullable(); // Registro afectado

            $table->string('cod_usu', 20)->nullable(); // Código usuario
            $table->foreign('cod_usu')
                  ->references('cod_usu')
                  ->on('users')
                  ->nullOnDelete();

            $table->timestamp('fec_bit')->useCurrent(); // Fecha y hora

            $table->string('est_bit', 20)->default('ACTIVO'); // Estado registro

        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bitacora');
    }
};