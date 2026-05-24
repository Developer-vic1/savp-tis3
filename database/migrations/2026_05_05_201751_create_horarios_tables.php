<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Módulo de horarios institucionales.
     *
     * Estructura normalizada:
     *
     * turno
     *  Define la jornada institucional: Mañana, Tarde, Especial.
     *
     * plantilla_horaria
     *  Define la variante horaria de un turno: REGULAR, INVIERNO, AJUSTE, EMERGENCIA.
     *
     * horario
     *  Cabecera del horario académico por gestión, curso, paralelo y plantilla horaria.
     *
     * horario_bloque
     *  Define los bloques de tiempo pertenecientes a una plantilla horaria.
     *
     * horario_detalle
     *  Define cada celda real de la matriz semanal:
     *  horario + bloque + día + plan académico.
     */
    public function up(): void
    {
        /*
        |--------------------------------------------------------------------------
        | TABLA: horario
        |--------------------------------------------------------------------------
        | Representa la cabecera de un horario académico.
        |
        | Ya no guarda cod_tur directamente porque el turno se obtiene desde:
        | horario.cod_pho -> plantilla_horaria.cod_tur -> turno.cod_tur
        |
        | Tampoco guarda nom_hor porque ese nombre se puede generar desde las
        | relaciones: gestión + curso + paralelo + plantilla + turno.
        |--------------------------------------------------------------------------
        */
        Schema::create('horario', function (Blueprint $table) {
            $table->string('cod_hor', 20)->primary();

            $table->string('cod_gea', 20);
            $table->string('cod_cur', 20);
            $table->string('cod_par', 20);
            $table->string('cod_pho', 20);

            $table->text('obs_hor')->nullable();

            $table->string('est_hor', 20)->default('ACTIVO');

            $table->timestamps();

            $table->unique(
                ['cod_gea', 'cod_cur', 'cod_par', 'cod_pho'],
                'horario_gea_cur_par_pho_unique'
            );

            $table->index('cod_gea', 'horario_cod_gea_index');
            $table->index('cod_cur', 'horario_cod_cur_index');
            $table->index('cod_par', 'horario_cod_par_index');
            $table->index('cod_pho', 'horario_cod_pho_index');
            $table->index('est_hor', 'horario_est_hor_index');
        });

        /*
        |--------------------------------------------------------------------------
        | LLAVES FORÁNEAS: horario
        |--------------------------------------------------------------------------
        */
        Schema::table('horario', function (Blueprint $table) {
            if (Schema::hasTable('gestion_academica') && Schema::hasColumn('gestion_academica', 'cod_gea')) {
                $table->foreign('cod_gea', 'horario_cod_gea_foreign')
                    ->references('cod_gea')
                    ->on('gestion_academica')
                    ->restrictOnDelete()
                    ->cascadeOnUpdate();
            }

            if (Schema::hasTable('curso') && Schema::hasColumn('curso', 'cod_cur')) {
                $table->foreign('cod_cur', 'horario_cod_cur_foreign')
                    ->references('cod_cur')
                    ->on('curso')
                    ->restrictOnDelete()
                    ->cascadeOnUpdate();
            }

            if (Schema::hasTable('paralelo') && Schema::hasColumn('paralelo', 'cod_par')) {
                $table->foreign('cod_par', 'horario_cod_par_foreign')
                    ->references('cod_par')
                    ->on('paralelo')
                    ->restrictOnDelete()
                    ->cascadeOnUpdate();
            }

            if (Schema::hasTable('plantilla_horaria') && Schema::hasColumn('plantilla_horaria', 'cod_pho')) {
                $table->foreign('cod_pho', 'horario_cod_pho_foreign')
                    ->references('cod_pho')
                    ->on('plantilla_horaria')
                    ->restrictOnDelete()
                    ->cascadeOnUpdate();
            }
        });

        /*
        |--------------------------------------------------------------------------
        | TABLA: horario_bloque
        |--------------------------------------------------------------------------
        | Define los bloques de tiempo de una plantilla horaria.
        |
        | Ya no guarda cod_tur porque sería redundante:
        | horario_bloque.cod_pho -> plantilla_horaria.cod_tur
        |
        | Esto mejora 3FN/BCNF y reduce inconsistencias.
        |--------------------------------------------------------------------------
        */
        Schema::create('horario_bloque', function (Blueprint $table) {
            $table->string('cod_hbl', 20)->primary();

            $table->string('cod_pho', 20);
            $table->unsignedTinyInteger('num_hbl');

            $table->time('hor_ini_hbl');
            $table->time('hor_fin_hbl');

            $table->string('nom_hbl', 80)->nullable();
            $table->string('tip_hbl', 30)->default('CLASE');
            $table->text('obs_hbl')->nullable();

            $table->string('est_hbl', 20)->default('ACTIVO');

            $table->timestamps();

            $table->unique(
                ['cod_pho', 'num_hbl'],
                'horario_bloque_pho_num_unique'
            );

            $table->unique(
                ['cod_pho', 'hor_ini_hbl', 'hor_fin_hbl'],
                'horario_bloque_pho_horas_unique'
            );

            $table->index('cod_pho', 'horario_bloque_cod_pho_index');
            $table->index('num_hbl', 'horario_bloque_num_hbl_index');
            $table->index('tip_hbl', 'horario_bloque_tip_hbl_index');
            $table->index('est_hbl', 'horario_bloque_est_hbl_index');
        });

        /*
        |--------------------------------------------------------------------------
        | LLAVES FORÁNEAS: horario_bloque
        |--------------------------------------------------------------------------
        */
        Schema::table('horario_bloque', function (Blueprint $table) {
            if (Schema::hasTable('plantilla_horaria') && Schema::hasColumn('plantilla_horaria', 'cod_pho')) {
                $table->foreign('cod_pho', 'horario_bloque_cod_pho_foreign')
                    ->references('cod_pho')
                    ->on('plantilla_horaria')
                    ->restrictOnDelete()
                    ->cascadeOnUpdate();
            }
        });

        /*
        |--------------------------------------------------------------------------
        | TABLA: horario_detalle
        |--------------------------------------------------------------------------
        | Representa cada celda de la matriz semanal.
        |
        | No repite gestión, curso, paralelo ni turno, porque esos datos se
        | obtienen desde horario y plantilla_horaria.
        |
        | cod_pas: referencia a plan_asignatura.
        | cod_pes: referencia a plan_especialidad.
        |
        | Regla:
        | Solo debe llenarse uno:
        | - cod_pas para materia curricular.
        | - cod_pes para especialidad técnica.
        |--------------------------------------------------------------------------
        */
        Schema::create('horario_detalle', function (Blueprint $table) {
            $table->string('cod_hde', 20)->primary();

            $table->string('cod_hor', 20);
            $table->string('cod_hbl', 20);

            $table->string('dia_hde', 15);

            $table->string('cod_pas', 20)->nullable();
            $table->string('cod_pes', 20)->nullable();

            $table->string('aul_hde', 100)->nullable();
            $table->text('obs_hde')->nullable();

            $table->string('est_hde', 20)->default('ACTIVO');

            $table->timestamps();

            $table->unique(
                ['cod_hor', 'cod_hbl', 'dia_hde'],
                'horario_detalle_hor_bloque_dia_unique'
            );

            $table->index('cod_hor', 'horario_detalle_cod_hor_index');
            $table->index('cod_hbl', 'horario_detalle_cod_hbl_index');
            $table->index('dia_hde', 'horario_detalle_dia_hde_index');
            $table->index('cod_pas', 'horario_detalle_cod_pas_index');
            $table->index('cod_pes', 'horario_detalle_cod_pes_index');
            $table->index('est_hde', 'horario_detalle_est_hde_index');
        });

        /*
        |--------------------------------------------------------------------------
        | LLAVES FORÁNEAS: horario_detalle
        |--------------------------------------------------------------------------
        */
        Schema::table('horario_detalle', function (Blueprint $table) {
            if (Schema::hasTable('horario') && Schema::hasColumn('horario', 'cod_hor')) {
                $table->foreign('cod_hor', 'horario_detalle_cod_hor_foreign')
                    ->references('cod_hor')
                    ->on('horario')
                    ->cascadeOnDelete()
                    ->cascadeOnUpdate();
            }

            if (Schema::hasTable('horario_bloque') && Schema::hasColumn('horario_bloque', 'cod_hbl')) {
                $table->foreign('cod_hbl', 'horario_detalle_cod_hbl_foreign')
                    ->references('cod_hbl')
                    ->on('horario_bloque')
                    ->restrictOnDelete()
                    ->cascadeOnUpdate();
            }

            if (Schema::hasTable('plan_asignatura') && Schema::hasColumn('plan_asignatura', 'cod_pas')) {
                $table->foreign('cod_pas', 'horario_detalle_cod_pas_foreign')
                    ->references('cod_pas')
                    ->on('plan_asignatura')
                    ->restrictOnDelete()
                    ->cascadeOnUpdate();
            }

            if (Schema::hasTable('plan_especialidad') && Schema::hasColumn('plan_especialidad', 'cod_pes')) {
                $table->foreign('cod_pes', 'horario_detalle_cod_pes_foreign')
                    ->references('cod_pes')
                    ->on('plan_especialidad')
                    ->restrictOnDelete()
                    ->cascadeOnUpdate();
            }
        });

        /*
        |--------------------------------------------------------------------------
        | CHECK CONSTRAINTS PARA POSTGRESQL
        |--------------------------------------------------------------------------
        */
        DB::statement("
            ALTER TABLE horario
            ADD CONSTRAINT horario_estado_check
            CHECK (est_hor IN ('ACTIVO', 'INACTIVO', 'PLANIFICADO', 'ARCHIVADO'))
        ");

        DB::statement("
            ALTER TABLE horario_bloque
            ADD CONSTRAINT horario_bloque_horas_check
            CHECK (hor_fin_hbl > hor_ini_hbl)
        ");

        DB::statement("
            ALTER TABLE horario_bloque
            ADD CONSTRAINT horario_bloque_tipo_check
            CHECK (tip_hbl IN ('CLASE', 'RECREO', 'DESCANSO', 'FORMACION', 'SALIDA', 'OTRO'))
        ");

        DB::statement("
            ALTER TABLE horario_bloque
            ADD CONSTRAINT horario_bloque_estado_check
            CHECK (est_hbl IN ('ACTIVO', 'INACTIVO'))
        ");

        DB::statement("
            ALTER TABLE horario_detalle
            ADD CONSTRAINT horario_detalle_dia_check
            CHECK (dia_hde IN ('LUNES', 'MARTES', 'MIERCOLES', 'JUEVES', 'VIERNES'))
        ");

        DB::statement("
            ALTER TABLE horario_detalle
            ADD CONSTRAINT horario_detalle_estado_check
            CHECK (est_hde IN ('ACTIVO', 'INACTIVO', 'SUSPENDIDO'))
        ");

        DB::statement("
            ALTER TABLE horario_detalle
            ADD CONSTRAINT horario_detalle_plan_check
            CHECK (
                (cod_pas IS NOT NULL AND cod_pes IS NULL)
                OR
                (cod_pas IS NULL AND cod_pes IS NOT NULL)
            )
        ");
    }

    public function down(): void
    {
        Schema::dropIfExists('horario_detalle');
        Schema::dropIfExists('horario_bloque');
        Schema::dropIfExists('horario');
    }
};
