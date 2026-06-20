<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('estado_asistencia', function (Blueprint $table) {
            $table->string('cod_est_asi', 20)->primary();

            $table->string('nom_est_asi', 80);
            $table->string('abr_est_asi', 10);
            $table->text('des_est_asi')->nullable();

            $table->string('color_est_asi', 30)->nullable();

            $table->decimal('valor_porcentual', 5, 2)->default(0);
            $table->boolean('afecta_asistencia')->default(true);
            $table->boolean('requiere_observacion')->default(false);

            $table->enum('est_est_asi', [
                'ACTIVO',
                'INACTIVO',
                'ANULADO',
            ])->default('ACTIVO');

            $table->timestamps();

            $table->unique('nom_est_asi', 'uq_estado_asistencia_nombre');
            $table->unique('abr_est_asi', 'uq_estado_asistencia_abreviatura');

            $table->index('est_est_asi');
            $table->index('afecta_asistencia');
            $table->index('requiere_observacion');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('estado_asistencia');
    }
};