<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('orientacion_actividades', function (Blueprint $table) {
            $table->json('riasec_public')->nullable();
            $table->json('riasec_score')->nullable();
            $table->json('analysis_snapshot')->nullable();
            $table->timestamp('analysis_completed_at')->nullable();
            $table->char('riasec_input_hash', 64)->nullable();
            $table->index(['cod_est', 'riasec_input_hash'], 'orientation_riasec_student_hash');
        });
    }

    public function down(): void
    {
        Schema::table('orientacion_actividades', function (Blueprint $table) {
            $table->dropIndex('orientation_riasec_student_hash');
            $table->dropColumn(['riasec_public', 'riasec_score', 'analysis_snapshot', 'analysis_completed_at', 'riasec_input_hash']);
        });
    }
};
