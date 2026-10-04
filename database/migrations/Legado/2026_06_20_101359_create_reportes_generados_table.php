<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('reportes_generados', function (Blueprint $table) {
            $table->id();
            $table->string('codigo', 40)->unique();
            $table->string('tipo_reporte', 60);
            $table->string('formato', 10)->default('pdf'); // pdf, zip, sql
            $table->string('nombre_archivo', 220);
            $table->string('ruta_archivo', 500);
            $table->unsignedBigInteger('tamano_bytes')->default(0);
            $table->string('hash_archivo', 64)->nullable();
            $table->string('generado_por', 80)->nullable();
            $table->string('estado', 20)->default('generado'); // generado, error, eliminado
            $table->text('observacion')->nullable();
            $table->timestamps();

            $table->index('tipo_reporte');
            $table->index('formato');
            $table->index('created_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('reportes_generados');
    }
};
