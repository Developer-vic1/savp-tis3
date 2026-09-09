<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Incorpora soporte para PlanEspecialidad (cod_pes) y visibilidad inicial (vis_cla)
     * en clase_virtual, manteniendo total compatibilidad con el esquema existente.
     */
    public function up(): void
    {
        if (Schema::hasTable('clase_virtual')) {
            if (! Schema::hasColumn('clase_virtual', 'cod_pes')) {
                Schema::table('clase_virtual', function (Blueprint $table) {
                    $table->string('cod_pes', 20)->nullable()->after('cod_pas');

                    if (Schema::hasTable('plan_especialidad')) {
                        $table->foreign('cod_pes', 'clase_virtual_cod_pes_foreign')
                            ->references('cod_pes')
                            ->on('plan_especialidad')
                            ->restrictOnDelete()
                            ->cascadeOnUpdate();
                    }

                    $table->index('cod_pes', 'clase_virtual_cod_pes_index');
                });
            }

            if (! Schema::hasColumn('clase_virtual', 'vis_cla')) {
                Schema::table('clase_virtual', function (Blueprint $table) {
                    $table->boolean('vis_cla')->default(false)->after('fec_fin_cla');
                    $table->index('vis_cla', 'clase_virtual_vis_cla_index');
                });
            }

            // Hacer cod_pas nullable en PostgreSQL si no lo es
            try {
                DB::statement('ALTER TABLE clase_virtual ALTER COLUMN cod_pas DROP NOT NULL');
            } catch (Throwable) {
                // Si la columna ya es nullable o el driver no lo soporta directamente, continuar
            }
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('clase_virtual')) {
            Schema::table('clase_virtual', function (Blueprint $table) {
                if (Schema::hasColumn('clase_virtual', 'cod_pes')) {
                    try {
                        $table->dropForeign('clase_virtual_cod_pes_foreign');
                    } catch (Throwable) {
                    }
                    $table->dropColumn('cod_pes');
                }

                if (Schema::hasColumn('clase_virtual', 'vis_cla')) {
                    $table->dropColumn('vis_cla');
                }
            });
        }
    }
};
