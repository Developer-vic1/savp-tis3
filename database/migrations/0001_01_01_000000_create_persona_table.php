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

            $table->string('nom_per', 100);
            $table->string('ape_pat_per', 100);
            $table->string('ape_mat_per', 100)->nullable();

            $table->string('ci_per', 20)->unique()->nullable();
            $table->date('fec_nac_per')->nullable();
            $table->string('gen_per', 20)->nullable();

            $table->string('tel_per', 20)->nullable();
            $table->string('ema_per', 150)->nullable()->unique();
            $table->string('dir_per', 255)->nullable();

            $table->string('fot_per', 255)->nullable();
            $table->boolean('est_per')->default(true);

            $table->timestamps();
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