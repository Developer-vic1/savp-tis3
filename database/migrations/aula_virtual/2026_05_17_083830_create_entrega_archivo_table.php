<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('entrega_archivo', function (Blueprint $table) {
            $table->string('cod_ent_arc', 20)->primary();

            $table->string('cod_ent', 20);

            $table->string('nom_arc', 180);
            $table->string('rut_arc', 255);
            $table->string('mime_arc', 120)->nullable();
            $table->unsignedBigInteger('tam_arc')->nullable();

            $table->enum('est_arc', [
                'ACTIVO',
                'ANULADO',
            ])->default('ACTIVO');

            $table->timestamps();

            $table->foreign('cod_ent')
                ->references('cod_ent')
                ->on('entrega_tarea')
                ->restrictOnDelete()
                ->cascadeOnUpdate();

            $table->index('cod_ent');
            $table->index('mime_arc');
            $table->index('est_arc');
            $table->index(['cod_ent', 'est_arc']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('entrega_archivo');
    }
};
