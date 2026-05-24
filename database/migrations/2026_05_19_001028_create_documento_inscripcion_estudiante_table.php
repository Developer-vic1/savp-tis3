<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('documento_inscripcion_estudiante', function (Blueprint $table) {
            $table->string('cod_die', 20)->primary();

            $table->string('cod_ins', 20);

            $table->string('nom_die', 150);
            $table->string('tip_die', 40)->default('GENERAL');
            $table->string('est_die', 30)->default('PENDIENTE');

            $table->boolean('obl_die')->default(false);
            $table->date('fec_lim_die')->nullable();
            $table->date('fec_pre_die')->nullable();

            $table->string('rut_die', 255)->nullable();
            $table->string('for_die', 20)->nullable();
            $table->unsignedBigInteger('tam_die')->nullable();
            $table->string('has_die', 128)->nullable();

            $table->text('obs_die')->nullable();

            $table->timestamps();

            $table->foreign('cod_ins')
                ->references('cod_ins')
                ->on('inscripcion_estudiante')
                ->restrictOnDelete()
                ->cascadeOnUpdate();

            $table->index('cod_ins', 'idx_documento_inscripcion_cod_ins');
            $table->index('tip_die', 'idx_documento_inscripcion_tip_die');
            $table->index('est_die', 'idx_documento_inscripcion_est_die');
            $table->index('obl_die', 'idx_documento_inscripcion_obl_die');
            $table->index('fec_lim_die', 'idx_documento_inscripcion_fec_lim_die');
            $table->index('fec_pre_die', 'idx_documento_inscripcion_fec_pre_die');
        });

        DB::statement("
            ALTER TABLE documento_inscripcion_estudiante
            ADD CONSTRAINT documento_inscripcion_tip_check
            CHECK (tip_die IN (
                'IDENTIDAD',
                'RUDE',
                'VACUNAS',
                'ACADEMICO',
                'TRASLADO',
                'EXTERIOR',
                'VULNERABILIDAD',
                'ESPECIALIDAD',
                'AUTORIZACION',
                'GENERAL'
            ))
        ");

        DB::statement("
            ALTER TABLE documento_inscripcion_estudiante
            ADD CONSTRAINT documento_inscripcion_est_check
            CHECK (est_die IN (
                'PENDIENTE',
                'PRESENTADO',
                'OBSERVADO',
                'VALIDADO',
                'NO_APLICA',
                'VENCIDO',
                'ANULADO'
            ))
        ");

        DB::statement("
            ALTER TABLE documento_inscripcion_estudiante
            ADD CONSTRAINT documento_inscripcion_for_check
            CHECK (
                for_die IS NULL
                OR for_die IN ('PDF', 'JPG', 'JPEG', 'PNG', 'DOCX', 'XLSX', 'ZIP')
            )
        ");

        DB::statement("
            ALTER TABLE documento_inscripcion_estudiante
            ADD CONSTRAINT documento_inscripcion_fechas_check
            CHECK (
                fec_pre_die IS NULL
                OR fec_lim_die IS NULL
                OR fec_pre_die >= fec_lim_die
                OR est_die IN ('PRESENTADO', 'VALIDADO', 'OBSERVADO')
            )
        ");
    }

    public function down(): void
    {
        Schema::dropIfExists('documento_inscripcion_estudiante');
    }
};