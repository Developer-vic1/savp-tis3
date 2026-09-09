<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('inscripcion_vigencia', function (Blueprint $table) {
            $table->string('cod_ivg', 20)->primary();
            foreach (['cod_ins', 'cod_cur', 'cod_par', 'cod_tur'] as $campo) {
                $table->string($campo, 20);
            }
            $table->string('cod_esp_tec', 20)->nullable();
            $table->date('fii_ivg');
            $table->date('ffi_ivg')->nullable();
            $table->string('tip_ivg', 20);
            $table->string('cie_ivg', 20)->nullable();
            $table->text('mot_ivg')->nullable();
            $table->string('est_ivg', 20);
            $table->timestamps();
            foreach (['cod_ins' => 'inscripcion_estudiante', 'cod_cur' => 'curso', 'cod_par' => 'paralelo', 'cod_tur' => 'turno'] as $campo => $tabla) {
                $table->foreign($campo)->references($campo)->on($tabla)->restrictOnDelete()->cascadeOnUpdate();
            }
            $table->foreign('cod_esp_tec')->references('cod_esp')->on('especialidad_tecnica')->restrictOnDelete()->cascadeOnUpdate();
            $table->index(['cod_ins', 'fii_ivg', 'ffi_ivg']);
        });

        Schema::create('calendario_evento', function (Blueprint $table) {
            $table->string('cod_cae', 20)->primary();
            $table->string('cod_gea', 20);
            $table->string('nom_cae', 180);
            $table->string('tip_cae', 40);
            $table->date('fii_cae');
            $table->date('ffi_cae');
            $table->time('hoi_cae')->nullable();
            $table->time('hof_cae')->nullable();
            foreach (['cod_tur' => 'turno', 'cod_cur' => 'curso', 'cod_par' => 'paralelo', 'cod_hde' => 'horario_detalle'] as $campo => $tabla) {
                $table->string($campo, 20)->nullable();
                $table->foreign($campo)->references($campo)->on($tabla)->restrictOnDelete()->cascadeOnUpdate();
            }
            $table->string('est_cae', 20)->default('PREALERTA');
            $table->string('efe_cae', 30)->default('INFORMATIVO');
            $table->boolean('com_cae')->nullable();
            $table->string('cod_cae_ori', 20)->nullable();
            $table->text('mot_cae');
            $table->text('fue_cae')->nullable();
            $table->timestamps();
            $table->foreign('cod_gea')->references('cod_gea')->on('gestion_academica')->restrictOnDelete()->cascadeOnUpdate();
            $table->index(['cod_gea', 'fii_cae', 'ffi_cae']);
        });

        Schema::table('calendario_evento', function (Blueprint $table) {
            $table->foreign('cod_cae_ori')->references('cod_cae')->on('calendario_evento')->restrictOnDelete();
        });

        Schema::create('novedad_estudiante', function (Blueprint $table) {
            $table->string('cod_nes', 20)->primary();
            $table->string('cod_est', 20);
            $table->string('cod_gea', 20);
            $table->string('tip_nes', 40);
            $table->date('fii_nes');
            $table->date('ffi_nes');
            $table->string('est_nes', 20)->default('ACTIVA');
            $table->text('mot_nes');
            $table->text('obs_nes')->nullable();
            $table->string('rut_res_nes', 255)->nullable();
            $table->timestamps();
            $table->foreign('cod_est')->references('cod_est')->on('estudiante')->restrictOnDelete()->cascadeOnUpdate();
            $table->foreign('cod_gea')->references('cod_gea')->on('gestion_academica')->restrictOnDelete()->cascadeOnUpdate();
            $table->index(['cod_est', 'cod_gea', 'fii_nes', 'ffi_nes']);
        });

        Schema::create('seguimiento_academico', function (Blueprint $table) {
            $table->string('cod_seg', 20)->primary();
            $table->string('cod_est', 20);
            $table->string('cod_gea', 20);
            $table->string('tip_seg', 20);
            $table->string('ori_seg', 100);
            $table->text('mot_seg');
            $table->string('niv_ape_seg', 20);
            $table->string('est_seg', 20)->default('ABIERTO');
            $table->string('vis_seg', 20)->default('NORMAL');
            $table->string('cod_usu_res', 20);
            $table->date('fec_ape_seg');
            $table->date('fec_pro_seg')->nullable();
            $table->date('fec_cie_seg')->nullable();
            $table->text('res_seg')->nullable();
            $table->text('pro_acc_seg')->nullable();
            $table->text('obs_seg')->nullable();
            $table->timestamps();
            $table->foreign('cod_est')->references('cod_est')->on('estudiante')->restrictOnDelete()->cascadeOnUpdate();
            $table->foreign('cod_gea')->references('cod_gea')->on('gestion_academica')->restrictOnDelete()->cascadeOnUpdate();
            $table->foreign('cod_usu_res')->references('cod_usu')->on('users')->restrictOnDelete()->cascadeOnUpdate();
            $table->index(['cod_gea', 'est_seg', 'fec_pro_seg']);
        });

        DB::statement("CREATE UNIQUE INDEX uq_ivg_activa ON inscripcion_vigencia (cod_ins) WHERE est_ivg = 'ACTIVA'");
        foreach ([
            'inscripcion_vigencia' => [
                'ivg_fechas' => 'ffi_ivg IS NULL OR ffi_ivg >= fii_ivg',
                'ivg_tipo' => "tip_ivg IN ('INICIAL','CAMBIO','REINGRESO','RESTITUCION')",
                'ivg_estado' => "est_ivg IN ('ACTIVA','CERRADA','ANULADA')",
                'ivg_cierre' => "(est_ivg <> 'ACTIVA' OR (ffi_ivg IS NULL AND cie_ivg IS NULL)) AND (est_ivg <> 'CERRADA' OR (ffi_ivg IS NOT NULL AND cie_ivg IS NOT NULL)) AND (cie_ivg IS NULL OR cie_ivg IN ('RETIRO','TRASLADO','CAMBIO','CONCLUSION','CORRECCION')) AND (cie_ivg IS NULL OR cie_ivg = 'CONCLUSION' OR NULLIF(TRIM(mot_ivg), '') IS NOT NULL)",
            ],
            'calendario_evento' => [
                'cae_fechas' => 'ffi_cae >= fii_cae',
                'cae_horas' => '(hoi_cae IS NULL AND hof_cae IS NULL) OR (hoi_cae IS NOT NULL AND hof_cae IS NOT NULL AND hof_cae > hoi_cae)',
                'cae_ambito' => 'cod_par IS NULL OR cod_cur IS NOT NULL',
                'cae_origen' => 'cod_cae_ori IS NULL OR cod_cae_ori <> cod_cae',
                'cae_estado' => "est_cae IN ('PREALERTA','CONFIRMADO','CANCELADO','FINALIZADO')",
                'cae_efecto' => "efe_cae IN ('INFORMATIVO','SIN_CLASES','SUSPENSION_PARCIAL','INGRESO_DIFERIDO','SALIDA_ANTICIPADA','HORARIO_AJUSTADO','RECUPERACION','ACTIVIDAD_INSTITUCIONAL')",
                'cae_motivo' => "NULLIF(TRIM(mot_cae), '') IS NOT NULL",
            ],
            'novedad_estudiante' => [
                'nes_fechas' => 'ffi_nes >= fii_nes',
                'nes_estado' => "est_nes IN ('ACTIVA','FINALIZADA','CANCELADA')",
                'nes_motivo' => "NULLIF(TRIM(mot_nes), '') IS NOT NULL",
            ],
            'seguimiento_academico' => [
                'seg_fechas' => '(fec_cie_seg IS NULL OR fec_cie_seg >= fec_ape_seg) AND (fec_pro_seg IS NULL OR fec_pro_seg >= fec_ape_seg)',
                'seg_estado' => "est_seg IN ('ABIERTO','EN_SEGUIMIENTO','RESUELTO','CANCELADO')",
                'seg_visibilidad' => "vis_seg IN ('NORMAL','RESTRINGIDO')",
                'seg_motivo' => "NULLIF(TRIM(mot_seg), '') IS NOT NULL",
                'seg_cierre' => "est_seg NOT IN ('RESUELTO','CANCELADO') OR (fec_cie_seg IS NOT NULL AND NULLIF(TRIM(res_seg), '') IS NOT NULL)",
            ],
        ] as $tabla => $reglas) {
            foreach ($reglas as $nombre => $regla) {
                DB::statement("ALTER TABLE {$tabla} ADD CONSTRAINT {$nombre} CHECK ({$regla})");
            }
        }

        // CREATE OR REPLACE + DROP TRIGGER IF EXISTS garantizan idempotencia en re-migraciones.
        DB::unprepared(<<<'SQL'
            CREATE OR REPLACE FUNCTION validar_solapamiento_ivg() RETURNS trigger LANGUAGE plpgsql AS $$
            BEGIN
                PERFORM 1 FROM inscripcion_estudiante WHERE cod_ins = NEW.cod_ins FOR UPDATE;
                IF NEW.est_ivg <> 'ANULADA' AND EXISTS (
                    SELECT 1 FROM inscripcion_vigencia v
                    WHERE v.cod_ins = NEW.cod_ins AND v.cod_ivg <> NEW.cod_ivg AND v.est_ivg <> 'ANULADA'
                    AND daterange(v.fii_ivg, v.ffi_ivg, '[]') && daterange(NEW.fii_ivg, NEW.ffi_ivg, '[]')
                ) THEN
                    RAISE EXCEPTION 'Las vigencias de la inscripción no pueden solaparse' USING ERRCODE = '23514';
                END IF;
                RETURN NEW;
            END $$;
            DROP TRIGGER IF EXISTS comprobar_solapamiento_ivg ON inscripcion_vigencia;
            CREATE TRIGGER comprobar_solapamiento_ivg BEFORE INSERT OR UPDATE ON inscripcion_vigencia
            FOR EACH ROW EXECUTE FUNCTION validar_solapamiento_ivg();
            SQL);
    }

    public function down(): void
    {
        throw new RuntimeException('Esta migración contiene historia académica. Revierta mediante una migración correctiva que preserve los datos.');
    }
};
