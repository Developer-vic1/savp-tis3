<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/** MIG-002. Extiende los recursos actuales; no copia material ni convierte datos históricos. */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('unidades_clase', function (Blueprint $table) {
            $table->id();
            $table->string('cod_cla', 20);
            $table->string('titulo', 180);
            $table->text('descripcion')->nullable();
            $table->integer('orden');
            $table->boolean('publicada')->default(false);
            $table->timestampTz('archivada_at')->nullable();
            $table->string('created_by', 20);
            $table->timestampsTz();
            $table->foreign('cod_cla')->references('cod_cla')->on('clase_virtual')->restrictOnDelete()->cascadeOnUpdate();
            $table->foreign('created_by')->references('cod_usu')->on('users')->restrictOnDelete()->cascadeOnUpdate();
            $table->unique(['id', 'cod_cla'], 'units_class_context_unique');
            $table->unique(['cod_cla', 'orden'], 'units_class_order_unique');
            $table->index(['cod_cla', 'publicada', 'archivada_at']);
            $table->index('created_by');
        });
        \App\Support\PortableCheckConstraint::statement("ALTER TABLE unidades_clase ADD CONSTRAINT units_content_check CHECK (orden > 0 AND NULLIF(TRIM(titulo), '') IS NOT NULL AND (archivada_at IS NULL OR NOT publicada))");
        foreach (['material_clase', 'tarea', 'publicacion_clase'] as $name) {
            Schema::table($name, function (Blueprint $table) use ($name) {
                $table->unsignedBigInteger('unidad_id')->nullable();
                $table->index(['unidad_id', 'cod_cla'], $name.'_unit_context_idx');
                $table->foreign(['unidad_id', 'cod_cla'], $name.'_unit_context_fk')->references(['id', 'cod_cla'])->on('unidades_clase')->restrictOnDelete()->cascadeOnUpdate();
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('unidades_clase') && DB::table('unidades_clase')->exists()) {
            throw new RuntimeException('Rollback cerrado: preservar unidades, orden y sus enlaces antes de retirar el schema.');
        }
        foreach (['material_clase', 'tarea', 'publicacion_clase'] as $name) {
            Schema::table($name, function (Blueprint $table) use ($name) {
                $table->dropForeign($name.'_unit_context_fk');
                $table->dropIndex($name.'_unit_context_idx');
                $table->dropColumn('unidad_id');
            });
        }
        Schema::dropIfExists('unidades_clase');
    }
};
