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
        Schema::create('persona', function (Blueprint $table) {
            $table->string('cod_per', 20)->primary();

            $table->string('nom_per', 100); // Nombre de la persona
            $table->string('ape_pat_per', 100); // Apellido paterno
            $table->string('ape_mat_per', 100)->nullable(); // Apellido materno

            $table->string('ci_per', 20)->unique(); // Carnet de identidad
            $table->string('com_per', 20)->nullable(); // Complemento del CI
            $table->string('exp_per', 20)->nullable(); // Expedición del CI
            $table->date('fec_nac_per')->nullable(); // Fecha de nacimiento
            $table->string('gen_per', 20)->nullable(); // Género

            $table->string('tel_per', 20)->nullable(); // Teléfono
            $table->string('ema_per', 150)->nullable()->unique(); // Correo electrónico

            $table->string('dir_per', 255)->nullable(); // Dirección completa o resumen visible
            $table->string('zona_per', 100)->nullable(); // Zona o barrio
            $table->string('ave_per', 120)->nullable(); // Avenida principal
            $table->string('cal_per', 120)->nullable(); // Calle
            $table->string('num_per', 30)->nullable(); // Número de domicilio
            $table->string('ref_per', 255)->nullable(); // Referencia adicional
            $table->string('ciu_per', 100)->nullable(); // Ciudad
            $table->string('mun_per', 100)->nullable(); // Municipio
            $table->string('dep_per', 100)->nullable(); // Departamento

            $table->string('fot_per', 255)->nullable(); // Ruta o nombre de la foto
            $table->boolean('est_per')->default(true); // Estado de la persona

            $table->timestamps();

            $table->index('ci_per');
            $table->index('ema_per');
            $table->index('est_per');
            $table->index('zona_per');
            $table->index('ciu_per');
            $table->index('mun_per');
            $table->index('dep_per');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('persona');
    }
};