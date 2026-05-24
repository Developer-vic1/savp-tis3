<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('respaldo_gestion_academica', function (Blueprint $table) {
            $table->string('cod_rga', 20)->primary();

            $table->string('cod_gea', 20);

            $table->string('tip_rga', 30)->default('CIERRE');
            $table->string('for_rga', 20)->default('ZIP');

            $table->string('rut_rga', 255)->nullable();
            $table->unsignedBigInteger('tam_rga')->nullable();
            $table->string('has_rga', 128)->nullable();

            $table->timestamp('fec_rga')->nullable();

            $table->string('est_rga', 20)->default('GENERADO');
            $table->text('obs_rga')->nullable();

            $table->timestamps();

            $table->foreign('cod_gea')
                ->references('cod_gea')
                ->on('gestion_academica')
                ->restrictOnDelete()
                ->cascadeOnUpdate();

            $table->index('cod_gea', 'respaldo_gestion_cod_gea_index');
            $table->index('tip_rga', 'respaldo_gestion_tip_rga_index');
            $table->index('for_rga', 'respaldo_gestion_for_rga_index');
            $table->index('est_rga', 'respaldo_gestion_est_rga_index');
            $table->index('fec_rga', 'respaldo_gestion_fec_rga_index');
        });

        DB::statement("
            ALTER TABLE respaldo_gestion_academica
            ADD CONSTRAINT respaldo_gestion_tip_check
            CHECK (tip_rga IN ('PRELIMINAR', 'CIERRE', 'AUDITORIA', 'RECUPERACION'))
        ");

        DB::statement("
            ALTER TABLE respaldo_gestion_academica
            ADD CONSTRAINT respaldo_gestion_for_check
            CHECK (for_rga IN ('ZIP', 'PDF', 'XLSX', 'JSON'))
        ");

        DB::statement("
            ALTER TABLE respaldo_gestion_academica
            ADD CONSTRAINT respaldo_gestion_est_check
            CHECK (est_rga IN ('GENERADO', 'VALIDADO', 'OBSERVADO', 'ARCHIVADO', 'ANULADO'))
        ");
    }

    public function down(): void
    {
        Schema::dropIfExists('respaldo_gestion_academica');
    }
};