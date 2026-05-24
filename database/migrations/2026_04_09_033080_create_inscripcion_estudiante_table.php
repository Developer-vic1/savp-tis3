<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('inscripcion_estudiante', function (Blueprint $table) {
            $table->string('cod_ins', 20)->primary();

            $table->string('cod_est', 20);
            $table->string('cod_gea', 20);
            $table->string('cod_cur', 20);
            $table->string('cod_par', 20);
            $table->string('cod_tur', 20);

            $table->date('fei_ins');

            $table->string('tip_ins', 30)->default('REGULAR');
            $table->string('con_ins', 30)->default('NORMAL');
            $table->string('est_ins', 30)->default('PENDIENTE');

            $table->string('pro_ins', 180)->nullable();
            $table->text('obs_ins')->nullable();
            $table->text('mot_obs_ins')->nullable();

            $table->boolean('doc_com_ins')->default(false);
            $table->boolean('sob_aut_ins')->default(false);

            $table->boolean('sie_ins')->default(false);
            $table->timestamp('fec_sie_ins')->nullable();

            $table->string('cod_esp_tec', 20)->nullable();
            $table->string('est_esp_tec_ins', 30)->default('NO_APLICA');
            $table->text('obs_esp_tec_ins')->nullable();

            $table->timestamp('fec_con_ins')->nullable();
            $table->timestamp('fec_anu_ins')->nullable();
            $table->text('mot_anu_ins')->nullable();

            $table->timestamp('fec_ret_ins')->nullable();
            $table->text('mot_ret_ins')->nullable();

            $table->timestamps();

            $table->unique(
                ['cod_est', 'cod_gea'],
                'uq_inscripcion_estudiante_por_gestion'
            );

            $table->index('cod_est', 'inscripcion_cod_est_index');
            $table->index('cod_gea', 'inscripcion_cod_gea_index');
            $table->index('cod_cur', 'inscripcion_cod_cur_index');
            $table->index('cod_par', 'inscripcion_cod_par_index');
            $table->index('cod_tur', 'inscripcion_cod_tur_index');
            $table->index('cod_esp_tec', 'inscripcion_cod_esp_tec_index');
            $table->index('tip_ins', 'inscripcion_tip_ins_index');
            $table->index('con_ins', 'inscripcion_con_ins_index');
            $table->index('est_ins', 'inscripcion_est_ins_index');
            $table->index('fei_ins', 'inscripcion_fei_ins_index');
            $table->index('sie_ins', 'inscripcion_sie_ins_index');
        });

        Schema::table('inscripcion_estudiante', function (Blueprint $table) {
            $table->foreign('cod_est', 'inscripcion_cod_est_foreign')
                ->references('cod_est')
                ->on('estudiante')
                ->restrictOnDelete()
                ->cascadeOnUpdate();

            $table->foreign('cod_gea', 'inscripcion_cod_gea_foreign')
                ->references('cod_gea')
                ->on('gestion_academica')
                ->restrictOnDelete()
                ->cascadeOnUpdate();

            $table->foreign('cod_cur', 'inscripcion_cod_cur_foreign')
                ->references('cod_cur')
                ->on('curso')
                ->restrictOnDelete()
                ->cascadeOnUpdate();

            $table->foreign('cod_par', 'inscripcion_cod_par_foreign')
                ->references('cod_par')
                ->on('paralelo')
                ->restrictOnDelete()
                ->cascadeOnUpdate();

            $table->foreign('cod_tur', 'inscripcion_cod_tur_foreign')
                ->references('cod_tur')
                ->on('turno')
                ->restrictOnDelete()
                ->cascadeOnUpdate();

            $table->foreign('cod_esp_tec', 'inscripcion_cod_esp_tec_foreign')
                ->references('cod_esp')
                ->on('especialidad_tecnica')
                ->nullOnDelete()
                ->cascadeOnUpdate();
        });

        DB::statement("
            ALTER TABLE inscripcion_estudiante
            ADD CONSTRAINT inscripcion_tip_check
            CHECK (tip_ins IN (
                'NUEVO',
                'REGULAR',
                'TRASLADO',
                'EXTERIOR',
                'VULNERABLE',
                'EXCEPCIONAL',
                'PREINSCRIPCION'
            ))
        ");

        DB::statement("
            ALTER TABLE inscripcion_estudiante
            ADD CONSTRAINT inscripcion_con_check
            CHECK (con_ins IN (
                'NORMAL',
                'OBSERVADA',
                'CONDICIONAL',
                'PROVISIONAL',
                'SOBRECUPO',
                'REZAGO_ESCOLAR'
            ))
        ");

        DB::statement("
            ALTER TABLE inscripcion_estudiante
            ADD CONSTRAINT inscripcion_est_check
            CHECK (est_ins IN (
                'PENDIENTE',
                'CONFIRMADA',
                'ACTIVA',
                'OBSERVADA',
                'ANULADA',
                'RETIRADA',
                'ARCHIVADA'
            ))
        ");

        DB::statement("
            ALTER TABLE inscripcion_estudiante
            ADD CONSTRAINT inscripcion_esp_tec_est_check
            CHECK (est_esp_tec_ins IN (
                'NO_APLICA',
                'PENDIENTE',
                'ASIGNADA',
                'OBSERVADA',
                'RETIRADA'
            ))
        ");

        DB::statement("
            ALTER TABLE inscripcion_estudiante
            ADD CONSTRAINT inscripcion_fechas_estado_check
            CHECK (
                (fec_anu_ins IS NULL OR fec_anu_ins >= fei_ins)
                AND
                (fec_ret_ins IS NULL OR fec_ret_ins >= fei_ins)
                AND
                (fec_con_ins IS NULL OR fec_con_ins >= fei_ins)
            )
        ");
    }

    public function down(): void
    {
        Schema::dropIfExists('inscripcion_estudiante');
    }
};