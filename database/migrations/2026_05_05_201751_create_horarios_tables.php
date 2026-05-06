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
     * horario:
     *  Cabecera del horario por gestión, curso, paralelo y turno.
     *
     * horario_bloque:
     *  Bloques horarios por turno. Ej: bloque 1, bloque 2, bloque 3.
     *
     * horario_detalle:
     *  Celdas reales de la matriz semanal.
     *  Puede apuntar a plan_asignatura o plan_especialidad.
     */
    public function up(): void
    {
        /*
        |--------------------------------------------------------------------------
        | TABLA: horario
        |--------------------------------------------------------------------------
        | Representa el horario de un curso/paralelo/turno en una gestión.
        | Ejemplo:
        | Gestión 2026 - 4to de Secundaria - Paralelo A - Turno Tarde
        |--------------------------------------------------------------------------
        */
        Schema::create('horario', function (Blueprint $table) {
            $table->string('cod_hor', 20)->primary();

            $table->string('cod_gea', 20);
            $table->string('cod_cur', 20);
            $table->string('cod_par', 20);
            $table->string('cod_tur', 20);

            $table->string('nom_hor', 150)->nullable();
            $table->text('obs_hor')->nullable();

            $table->string('est_hor', 20)->default('ACTIVO');

            $table->timestamps();

            $table->unique(
                ['cod_gea', 'cod_cur', 'cod_par', 'cod_tur'],
                'horario_gea_cur_par_tur_unique'
            );

            $table->index('cod_gea');
            $table->index('cod_cur');
            $table->index('cod_par');
            $table->index('cod_tur');
            $table->index('est_hor');
        });

        /*
        |--------------------------------------------------------------------------
        | LLAVES FORÁNEAS: horario
        |--------------------------------------------------------------------------
        | Se agregan después para mantener control y evitar problemas de orden.
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

            if (Schema::hasTable('turno') && Schema::hasColumn('turno', 'cod_tur')) {
                $table->foreign('cod_tur', 'horario_cod_tur_foreign')
                    ->references('cod_tur')
                    ->on('turno')
                    ->restrictOnDelete()
                    ->cascadeOnUpdate();
            }
        });

        /*
        |--------------------------------------------------------------------------
        | TABLA: horario_bloque
        |--------------------------------------------------------------------------
        | Define los periodos disponibles por turno.
        |
        | Ejemplo:
        | Turno mañana:
        | Bloque 1: 08:00 - 08:40
        | Bloque 2: 08:40 - 09:20
        |
        | Turno tarde:
        | Bloque 1: 14:00 - 14:40
        | Bloque 2: 14:40 - 15:20
        |--------------------------------------------------------------------------
        */
        Schema::create('horario_bloque', function (Blueprint $table) {
            $table->string('cod_hbl', 20)->primary();

            $table->string('cod_tur', 20);
            $table->unsignedTinyInteger('num_hbl');

            $table->time('hor_ini_hbl');
            $table->time('hor_fin_hbl');

            $table->string('nom_hbl', 80)->nullable();
            $table->text('obs_hbl')->nullable();

            $table->string('est_hbl', 20)->default('ACTIVO');

            $table->timestamps();

            $table->unique(
                ['cod_tur', 'num_hbl'],
                'horario_bloque_tur_num_unique'
            );

            $table->unique(
                ['cod_tur', 'hor_ini_hbl', 'hor_fin_hbl'],
                'horario_bloque_tur_horas_unique'
            );

            $table->index('cod_tur');
            $table->index('num_hbl');
            $table->index('est_hbl');
        });

        /*
        |--------------------------------------------------------------------------
        | LLAVES FORÁNEAS: horario_bloque
        |--------------------------------------------------------------------------
        */
        Schema::table('horario_bloque', function (Blueprint $table) {
            if (Schema::hasTable('turno') && Schema::hasColumn('turno', 'cod_tur')) {
                $table->foreign('cod_tur', 'horario_bloque_cod_tur_foreign')
                    ->references('cod_tur')
                    ->on('turno')
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
        | Ejemplo:
        | LUNES - Bloque 1 - MAT-L.V.
        | MARTES - Bloque 3 - LCO-M.H.
        | MIÉRCOLES - Bloque 1 - TTE
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

            /*
            |--------------------------------------------------------------------------
            | Evita que un mismo horario tenga dos asignaciones en el mismo día/bloque.
            |--------------------------------------------------------------------------
            */
            $table->unique(
                ['cod_hor', 'cod_hbl', 'dia_hde'],
                'horario_detalle_hor_bloque_dia_unique'
            );

            $table->index('cod_hor');
            $table->index('cod_hbl');
            $table->index('dia_hde');
            $table->index('cod_pas');
            $table->index('cod_pes');
            $table->index('est_hde');
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

            /*
            |--------------------------------------------------------------------------
            | Plan especialidad
            |--------------------------------------------------------------------------
            | En el proyecto venimos manejando plan_especialidad. Si tu PK real no es
            | cod_pes, cambia esta referencia por el nombre real de la columna.
            |--------------------------------------------------------------------------
            */
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
        | Como estás trabajando con PostgreSQL, esto ayuda a proteger la lógica:
        |
        | 1. El día solo puede ser uno de los días académicos.
        | 2. La hora de fin debe ser mayor que la hora de inicio.
        | 3. En horario_detalle debe existir cod_pas o cod_pes, pero no ambos.
        |--------------------------------------------------------------------------
        */
        DB::statement("
            ALTER TABLE horario_detalle
            ADD CONSTRAINT horario_detalle_dia_check
            CHECK (dia_hde IN ('LUNES', 'MARTES', 'MIERCOLES', 'JUEVES', 'VIERNES'))
        ");

        DB::statement("
            ALTER TABLE horario_bloque
            ADD CONSTRAINT horario_bloque_horas_check
            CHECK (hor_fin_hbl > hor_ini_hbl)
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
