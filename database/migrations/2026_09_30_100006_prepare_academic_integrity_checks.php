<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/** MIG-006. No elimina ni normaliza datos. NOT VALID conserva anomalías históricas para revisión. */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('calificacion', function (Blueprint $table) {
            $table->dropForeign(['cod_est']);
            $table->foreign('cod_est')->references('cod_est')->on('estudiante')->restrictOnDelete()->cascadeOnUpdate();
            $table->index(['cod_est', 'est_cal'], 'calificacion_student_state_idx');
        });
        DB::statement('ALTER TABLE calificacion ADD CONSTRAINT academic_official_score_check CHECK (not_cal >= 0 AND not_cal <= 100) NOT VALID');
        DB::statement('ALTER TABLE tarea ADD CONSTRAINT lms_task_maximum_check CHECK (pun_max_tar >= 1 AND pun_max_tar <= 1000) NOT VALID');
        DB::statement('ALTER TABLE calificacion_tarea ADD CONSTRAINT lms_submission_score_check CHECK (pun_max > 0 AND pun_obt >= 0 AND pun_obt <= pun_max) NOT VALID');
        DB::statement('ALTER TABLE orientacion_respuestas ADD CONSTRAINT local_orientation_likert_check CHECK (valor_likert BETWEEN 1 AND 5) NOT VALID');
        DB::statement('ALTER TABLE orientacion_actividades ADD CONSTRAINT local_orientation_progress_check CHECK (avance BETWEEN 0 AND 100) NOT VALID');
    }

    public function down(): void
    {
        foreach (['calificacion' => 'academic_official_score_check', 'tarea' => 'lms_task_maximum_check',
            'calificacion_tarea' => 'lms_submission_score_check', 'orientacion_respuestas' => 'local_orientation_likert_check',
            'orientacion_actividades' => 'local_orientation_progress_check'] as $table => $constraint) {
            DB::statement("ALTER TABLE {$table} DROP CONSTRAINT {$constraint}");
        }
        Schema::table('calificacion', function (Blueprint $table) {
            $table->dropIndex('calificacion_student_state_idx');
            $table->dropForeign(['cod_est']);
            $table->foreign('cod_est')->references('cod_est')->on('estudiante')->cascadeOnDelete();
        });
    }
};
