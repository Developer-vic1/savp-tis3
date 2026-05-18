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
        Schema::create('plantilla_horaria', function (Blueprint $table) {
            $table->string('cod_pho', 20)->primary();

            $table->string('cod_tur', 20); // Turno al que pertenece la plantilla: mañana, tarde, especial si aplica.

            $table->string('nom_pho', 120); // Nombre visible de la plantilla. Ej: Plantilla Regular - Mañana.
            $table->string('tip_pho', 30)->default('REGULAR'); // REGULAR, INVIERNO, AJUSTE, EMERGENCIA.
            $table->text('des_pho')->nullable(); // Descripción institucional de la plantilla.

            $table->date('fec_ini_pho')->nullable(); // Fecha inicio de vigencia si la plantilla es temporal.
            $table->date('fec_fin_pho')->nullable(); // Fecha fin de vigencia si la plantilla es temporal.

            $table->unsignedSmallInteger('dur_blo_pho')->nullable(); // Duración base sugerida del bloque en minutos.
            $table->unsignedSmallInteger('ord_pho')->default(1); // Orden visual de la plantilla dentro del turno.

            $table->boolean('act_pho')->default(false); // Indica si la plantilla está actualmente aplicada.
            $table->boolean('est_pho')->default(true); // Estado lógico: activo/inactivo.

            $table->timestamps();

            $table->foreign('cod_tur')
                ->references('cod_tur')
                ->on('turno')
                ->restrictOnDelete()
                ->cascadeOnUpdate();

            $table->unique(
                ['cod_tur', 'tip_pho', 'nom_pho'],
                'plantilla_horaria_turno_tipo_nombre_unique'
            );

            $table->index('cod_tur', 'plantilla_horaria_cod_tur_index');
            $table->index('tip_pho', 'plantilla_horaria_tip_pho_index');
            $table->index('act_pho', 'plantilla_horaria_act_pho_index');
            $table->index('est_pho', 'plantilla_horaria_est_pho_index');
            $table->index(['fec_ini_pho', 'fec_fin_pho'], 'plantilla_horaria_fechas_index');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('plantilla_horaria');
    }
};