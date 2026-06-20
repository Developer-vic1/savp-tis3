<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('orientacion_actividades')) {
            Schema::create('orientacion_actividades', function (Blueprint $table) {
                $table->id();
                $table->string('cod_est', 20);
                $table->string('cod_gea', 20)->nullable();
                $table->enum('estado', ['pendiente', 'en_proceso', 'finalizado', 'revisado', 'requiere_seguimiento'])->default('pendiente');
                $table->unsignedTinyInteger('avance')->default(0);
                $table->timestamp('iniciado_at')->nullable();
                $table->timestamp('finalizado_at')->nullable();
                $table->string('revisado_por', 20)->nullable();
                $table->timestamps();

                $table->foreign('cod_est')->references('cod_est')->on('estudiante')->restrictOnDelete()->cascadeOnUpdate();
                $table->foreign('cod_gea')->references('cod_gea')->on('gestion_academica')->nullOnDelete()->cascadeOnUpdate();
                $table->foreign('revisado_por')->references('cod_usu')->on('users')->nullOnDelete()->cascadeOnUpdate();
                $table->index(['cod_est', 'estado']);
            });
        }

        if (! Schema::hasTable('orientacion_preguntas')) {
            Schema::create('orientacion_preguntas', function (Blueprint $table) {
                $table->id();
                $table->string('codigo', 40)->unique();
                $table->string('dimension', 80);
                $table->text('texto');
                $table->enum('tipo', ['likert'])->default('likert');
                $table->unsignedTinyInteger('orden');
                $table->boolean('visible')->default(true);
                $table->timestamps();

                $table->index(['visible', 'orden']);
                $table->index('dimension');
            });
        }

        if (! Schema::hasTable('orientacion_respuestas')) {
            Schema::create('orientacion_respuestas', function (Blueprint $table) {
                $table->id();
                $table->foreignId('orientacion_actividad_id')->constrained('orientacion_actividades')->cascadeOnDelete();
                $table->foreignId('orientacion_pregunta_id')->constrained('orientacion_preguntas')->restrictOnDelete();
                $table->string('cod_est', 20);
                $table->unsignedTinyInteger('valor_likert');
                $table->timestamps();

                $table->foreign('cod_est')->references('cod_est')->on('estudiante')->restrictOnDelete()->cascadeOnUpdate();
                $table->unique(['orientacion_actividad_id', 'orientacion_pregunta_id'], 'uq_orientacion_respuesta_pregunta');
                $table->index(['cod_est', 'orientacion_actividad_id']);
            });
        }

        if (! Schema::hasTable('orientacion_resultados')) {
            Schema::create('orientacion_resultados', function (Blueprint $table) {
                $table->id();
                $table->foreignId('orientacion_actividad_id')->constrained('orientacion_actividades')->cascadeOnDelete();
                $table->string('cod_est', 20);
                $table->decimal('tecnico_practico', 5, 2)->default(0);
                $table->decimal('analitico_cientifico', 5, 2)->default(0);
                $table->decimal('creativo_expresivo', 5, 2)->default(0);
                $table->decimal('social_comunitario', 5, 2)->default(0);
                $table->decimal('liderazgo_emprendimiento', 5, 2)->default(0);
                $table->decimal('organizativo_administrativo', 5, 2)->default(0);
                $table->string('perfil_predominante', 80);
                $table->text('interpretacion');
                $table->decimal('compatibilidad_principal', 5, 2)->default(0);
                $table->enum('estado', ['generado', 'revisado', 'requiere_seguimiento'])->default('generado');
                $table->timestamps();

                $table->foreign('cod_est')->references('cod_est')->on('estudiante')->restrictOnDelete()->cascadeOnUpdate();
                $table->unique('orientacion_actividad_id', 'uq_orientacion_resultado_actividad');
                $table->index(['cod_est', 'estado']);
            });
        }

        if (! Schema::hasTable('orientacion_carreras_sugeridas')) {
            Schema::create('orientacion_carreras_sugeridas', function (Blueprint $table) {
                $table->id();
                $table->foreignId('orientacion_resultado_id')->constrained('orientacion_resultados')->cascadeOnDelete();
                $table->string('carrera', 180);
                $table->string('area_profesional', 120);
                $table->decimal('compatibilidad', 5, 2)->default(0);
                $table->text('razon');
                $table->json('fortalezas')->nullable();
                $table->json('areas_a_fortalecer')->nullable();
                $table->unsignedTinyInteger('orden')->default(1);
                $table->timestamps();

                $table->index(['orientacion_resultado_id', 'orden']);
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('orientacion_carreras_sugeridas');
        Schema::dropIfExists('orientacion_resultados');
        Schema::dropIfExists('orientacion_respuestas');
        Schema::dropIfExists('orientacion_preguntas');
        Schema::dropIfExists('orientacion_actividades');
    }
};
