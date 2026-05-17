<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('clase_virtual', function (Blueprint $table) {
            $table->string('cod_cla', 20)->primary();
            $table->string('cod_pas', 20);

            $table->string('nom_cla', 150);
            $table->text('des_cla')->nullable();

            $table->date('fec_ini_cla')->nullable();
            $table->date('fec_fin_cla')->nullable();

            $table->enum('est_cla', [
                'ACTIVA',
                'CERRADA',
                'INACTIVA',
                'ANULADA',
            ])->default('ACTIVA');

            $table->timestamps();

            $table->foreign('cod_pas')
                ->references('cod_pas')
                ->on('plan_asignatura')
                ->restrictOnDelete()
                ->cascadeOnUpdate();

            $table->index('cod_pas');
            $table->index('est_cla');
            $table->index(['cod_pas', 'est_cla']);
            $table->index(['fec_ini_cla', 'fec_fin_cla']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('clase_virtual');
    }
};