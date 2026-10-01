<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/** MIG-005. Adaptación selectiva del calendario_evento del árbol operativo; sin copiar su paquete. */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('calendario_evento', function (Blueprint $table) {
            $table->string('cod_cae', 20)->primary();
            $table->string('cod_gea', 20);
            $table->string('nom_cae', 180);
            $table->string('tip_cae', 40);
            $table->date('fii_cae');
            $table->date('ffi_cae');
            $table->time('hoi_cae')->nullable();
            $table->time('hof_cae')->nullable();
            foreach (['cod_tur' => 'turno', 'cod_cur' => 'curso', 'cod_par' => 'paralelo'] as $field => $target) {
                $table->string($field, 20)->nullable();
                $table->foreign($field)->references($field)->on($target)->restrictOnDelete()->cascadeOnUpdate();
                $table->index($field);
            }
            $table->string('est_cae', 20)->default('PREALERTA');
            $table->string('efe_cae', 30)->default('INFORMATIVO');
            $table->string('cod_cae_ori', 20)->nullable();
            $table->text('mot_cae');
            $table->text('fue_cae')->nullable();
            $table->string('created_by', 20);
            $table->timestampsTz();
            $table->foreign('cod_gea')->references('cod_gea')->on('gestion_academica')->restrictOnDelete()->cascadeOnUpdate();
            $table->foreign('created_by')->references('cod_usu')->on('users')->restrictOnDelete()->cascadeOnUpdate();
            $table->index(['cod_gea', 'fii_cae', 'ffi_cae']);
            $table->index(['cod_gea', 'cod_cur', 'est_cae']);
            $table->index('created_by');
        });
        Schema::table('calendario_evento', fn (Blueprint $table) => $table->foreign('cod_cae_ori')->references('cod_cae')->on('calendario_evento')->restrictOnDelete()->cascadeOnUpdate());
        \App\Support\PortableCheckConstraint::statement("ALTER TABLE calendario_evento ADD CONSTRAINT calendar_event_content_check CHECK (ffi_cae >= fii_cae AND ((hoi_cae IS NULL AND hof_cae IS NULL) OR (hoi_cae IS NOT NULL AND hof_cae IS NOT NULL AND hof_cae > hoi_cae)) AND (cod_par IS NULL OR cod_cur IS NOT NULL) AND (cod_cae_ori IS NULL OR cod_cae_ori <> cod_cae) AND est_cae IN ('PREALERTA','CONFIRMADO','CANCELADO','FINALIZADO') AND efe_cae IN ('INFORMATIVO','SIN_CLASES','SUSPENSION_PARCIAL','INGRESO_DIFERIDO','SALIDA_ANTICIPADA','HORARIO_AJUSTADO','RECUPERACION','ACTIVIDAD_INSTITUCIONAL') AND NULLIF(TRIM(mot_cae), '') IS NOT NULL)");
        Schema::create('calendario_evento_revisiones', function (Blueprint $table) {
            $table->id();
            $table->string('cod_cae', 20);
            $table->string('cod_usu', 20);
            $table->text('motivo');
            $table->jsonb('datos');
            $table->timestampTz('created_at')->useCurrent();
            $table->foreign('cod_cae')->references('cod_cae')->on('calendario_evento')->restrictOnDelete()->cascadeOnUpdate();
            $table->foreign('cod_usu')->references('cod_usu')->on('users')->restrictOnDelete()->cascadeOnUpdate();
            $table->index(['cod_cae', 'created_at']);
            $table->index('cod_usu');
        });
        \App\Support\PortableCheckConstraint::statement("ALTER TABLE calendario_evento_revisiones ADD CONSTRAINT calendar_event_revision_check CHECK (NULLIF(TRIM(motivo), '') IS NOT NULL AND jsonb_typeof(datos) = 'object')");
    }

    public function down(): void
    {
        foreach (['calendario_evento_revisiones', 'calendario_evento'] as $name) {
            if (Schema::hasTable($name) && DB::table($name)->exists()) {
                throw new RuntimeException('Rollback cerrado: preservar eventos y revisiones antes de retirar el calendario institucional.');
            }
        }
        Schema::dropIfExists('calendario_evento_revisiones');
        Schema::table('calendario_evento', fn (Blueprint $table) => $table->dropForeign(['cod_cae_ori']));
        Schema::dropIfExists('calendario_evento');
    }
};
