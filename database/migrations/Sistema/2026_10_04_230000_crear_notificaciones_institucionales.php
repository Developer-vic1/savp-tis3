<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('notificacion', function (Blueprint $table) {
            $table->string('cod_not', 20)->primary();
            $table->string('clave_evento', 160);
            $table->string('origen', 25);
            $table->string('tipo', 20)->default('INFORMACION');
            $table->string('titulo', 150);
            $table->text('mensaje');
            $table->string('est_not', 15)->default('ACTIVA');
            $table->string('cod_usu_emisor', 20)->nullable();
            $table->string('cod_gea', 20)->nullable();
            $table->string('permiso', 125)->nullable();
            $table->string('ruta', 150)->nullable();
            $table->timestampTz('publicada_en');
            $table->timestampTz('vence_en')->nullable();
            $table->timestampsTz();
            $table->foreign('cod_usu_emisor')->references('cod_usu')->on('users')->restrictOnDelete()->cascadeOnUpdate();
            $table->foreign('cod_gea')->references('cod_gea')->on('gestion_academica')->restrictOnDelete()->cascadeOnUpdate();
            $table->unique(['origen', 'clave_evento']);
            $table->index(['est_not', 'publicada_en']);
            $table->index('cod_gea');
            $table->index('cod_usu_emisor');
        });
        Schema::create('notificacion_usuario', function (Blueprint $table) {
            $table->string('cod_nus', 20)->primary();
            $table->string('cod_not', 20);
            $table->string('cod_usu', 20);
            $table->timestampTz('leida_en')->nullable();
            $table->timestampTz('archivada_en')->nullable();
            $table->timestampsTz();
            $table->foreign('cod_not')->references('cod_not')->on('notificacion')->restrictOnDelete()->cascadeOnUpdate();
            $table->foreign('cod_usu')->references('cod_usu')->on('users')->restrictOnDelete()->cascadeOnUpdate();
            $table->unique(['cod_not', 'cod_usu']);
            $table->index(['cod_usu', 'archivada_en', 'leida_en', 'created_at'], 'notificacion_usuario_bandeja_idx');
        });
        if (DB::getDriverName() === 'pgsql') {
            DB::statement("ALTER TABLE notificacion ADD CONSTRAINT notificacion_valores_ck CHECK (
                cod_not ~ '^NOT_[0-9]{6,16}$'
                AND origen IN ('ADMINISTRATIVO','AULA_VIRTUAL','APORTE','SISTEMA')
                AND tipo IN ('INFORMACION','ADVERTENCIA','ACCION','RECORDATORIO')
                AND est_not IN ('ACTIVA','CANCELADA')
                AND length(btrim(clave_evento)) > 0 AND length(btrim(titulo)) > 0
                AND length(btrim(mensaje)) BETWEEN 1 AND 2000
                AND (vence_en IS NULL OR vence_en > publicada_en)
                AND (ruta IS NULL OR permiso IS NOT NULL))");
            DB::statement("ALTER TABLE notificacion_usuario ADD CONSTRAINT notificacion_usuario_valores_ck CHECK (
                cod_nus ~ '^NUS_[0-9]{6,16}$'
                AND (leida_en IS NULL OR leida_en >= created_at)
                AND (archivada_en IS NULL OR archivada_en >= created_at))");
        }
    }

    public function down(): void
    {
        if (DB::table('notificacion_usuario')->exists() || DB::table('notificacion')->exists()) {
            throw new RuntimeException('Las notificaciones contienen historial. Conservar los datos y preparar una migración correctiva.');
        }
        Schema::dropIfExists('notificacion_usuario');
        Schema::dropIfExists('notificacion');
    }
};
