<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/** MIG-001. Preparada para revisión; sin catálogos cargados ni habilitación operativa. */
return new class extends Migration
{
    private const CATALOGS = ['kardex_tipos', 'kardex_categorias', 'kardex_niveles', 'kardex_estados', 'kardex_medidas'];

    public function up(): void
    {
        foreach (self::CATALOGS as $name) {
            Schema::create($name, function (Blueprint $table) {
                $table->string('codigo', 20);
                $table->integer('version');
                $table->string('nombre', 120);
                $table->text('descripcion')->nullable();
                $table->boolean('activo')->default(true);
                $table->timestampsTz();
                $table->primary(['codigo', 'version']);
                $table->index(['version', 'activo']);
            });
            DB::statement("ALTER TABLE {$name} ADD CONSTRAINT {$name}_version_check CHECK (version > 0)");
        }
        // PK cod_pas ya evita duplicados; esta clave permite validar contexto compuesto por FK.
        Schema::table('plan_asignatura', function (Blueprint $table) {
            $table->unique(['cod_pas', 'cod_gea', 'cod_cur'], 'plan_kardex_context_unique');
        });
        Schema::create('seguimiento_academico', function (Blueprint $table) {
            $table->string('cod_seg', 20)->primary();
            foreach (['cod_est', 'cod_gea', 'cod_cur', 'cod_pas', 'cod_usu_aut', 'cod_usu_res'] as $field) {
                $table->string($field, 20);
            }
            $table->integer('version_catalogo');
            foreach (['tip_seg', 'niv_ape_seg', 'est_seg'] as $field) {
                $table->string($field, 20);
            }
            $table->string('cod_categoria', 20)->nullable();
            $table->string('cod_medida', 20)->nullable();
            $table->string('ori_seg', 100);
            $table->text('mot_seg');
            $table->string('vis_seg', 20)->default('RESTRINGIDO');
            $table->boolean('visible_estudiante')->default(false);
            $table->date('fec_ape_seg');
            $table->date('fec_pro_seg')->nullable();
            $table->date('fec_cie_seg')->nullable();
            foreach (['res_seg', 'pro_acc_seg', 'obs_seg'] as $field) {
                $table->text($field)->nullable();
            }
            $table->timestampsTz();
            $table->foreign('cod_est')->references('cod_est')->on('estudiante')->restrictOnDelete()->cascadeOnUpdate();
            $table->foreign(['cod_pas', 'cod_gea', 'cod_cur'], 'seguimiento_plan_context_fk')
                ->references(['cod_pas', 'cod_gea', 'cod_cur'])->on('plan_asignatura')->restrictOnDelete()->cascadeOnUpdate();
            foreach (['cod_usu_aut', 'cod_usu_res'] as $field) {
                $table->foreign($field)->references('cod_usu')->on('users')->restrictOnDelete()->cascadeOnUpdate();
            }
            foreach (['tip_seg' => 'kardex_tipos', 'niv_ape_seg' => 'kardex_niveles', 'est_seg' => 'kardex_estados', 'cod_categoria' => 'kardex_categorias', 'cod_medida' => 'kardex_medidas'] as $field => $catalog) {
                $table->foreign([$field, 'version_catalogo'])->references(['codigo', 'version'])->on($catalog)->restrictOnDelete()->restrictOnUpdate();
            }
            $table->index(['cod_est', 'cod_gea', 'fec_ape_seg'], 'seguimiento_student_timeline_idx');
            $table->index(['cod_gea', 'cod_cur', 'est_seg', 'fec_pro_seg'], 'seguimiento_regency_review_idx');
            $table->index('cod_pas');
            $table->index('cod_usu_aut');
            $table->index('cod_usu_res');
            foreach (['tip_seg', 'niv_ape_seg', 'est_seg', 'cod_categoria', 'cod_medida'] as $field) {
                $table->index([$field, 'version_catalogo']);
            }
        });
        DB::statement("ALTER TABLE seguimiento_academico ADD CONSTRAINT seguimiento_domain_check CHECK (version_catalogo > 0 AND NULLIF(TRIM(mot_seg), '') IS NOT NULL AND vis_seg IN ('NORMAL','RESTRINGIDO') AND (NOT visible_estudiante OR vis_seg = 'NORMAL') AND (fec_pro_seg IS NULL OR fec_pro_seg >= fec_ape_seg) AND (fec_cie_seg IS NULL OR fec_cie_seg >= fec_ape_seg))");
        Schema::create('seguimiento_revisiones', function (Blueprint $table) {
            $table->id();
            $table->string('cod_seg', 20);
            $table->string('cod_usu', 20);
            $table->string('tipo', 30);
            $table->text('motivo');
            $table->jsonb('datos');
            $table->timestampTz('created_at')->useCurrent();
            $table->foreign('cod_seg')->references('cod_seg')->on('seguimiento_academico')->restrictOnDelete()->cascadeOnUpdate();
            $table->foreign('cod_usu')->references('cod_usu')->on('users')->restrictOnDelete()->cascadeOnUpdate();
            $table->index(['cod_seg', 'created_at']);
            $table->index('cod_usu');
        });
        DB::statement("ALTER TABLE seguimiento_revisiones ADD CONSTRAINT seguimiento_revision_check CHECK (tipo IN ('RECTIFICACION','ANULACION','CAMBIO_ESTADO','SEGUIMIENTO') AND NULLIF(TRIM(motivo), '') IS NOT NULL AND jsonb_typeof(datos) = 'object')");
        Schema::create('seguimiento_evidencias', function (Blueprint $table) {
            $table->id();
            $table->string('cod_seg', 20);
            $table->string('cod_usu', 20);
            $table->string('ruta', 255);
            $table->string('nombre', 180);
            $table->string('mime', 100);
            $table->bigInteger('bytes');
            $table->char('sha256', 64);
            $table->timestampTz('created_at')->useCurrent();
            $table->foreign('cod_seg')->references('cod_seg')->on('seguimiento_academico')->restrictOnDelete()->cascadeOnUpdate();
            $table->foreign('cod_usu')->references('cod_usu')->on('users')->restrictOnDelete()->cascadeOnUpdate();
            $table->index(['cod_seg', 'created_at']);
            $table->index('cod_usu');
        });
        DB::statement("ALTER TABLE seguimiento_evidencias ADD CONSTRAINT seguimiento_evidence_check CHECK (bytes > 0 AND sha256 ~ '^[a-f0-9]{64}$' AND ruta LIKE 'kardex/%')");
    }

    public function down(): void
    {
        $tables = ['seguimiento_evidencias', 'seguimiento_revisiones', 'seguimiento_academico', ...array_reverse(self::CATALOGS)];
        foreach ($tables as $name) {
            if (Schema::hasTable($name) && DB::table($name)->exists()) {
                throw new RuntimeException('Rollback cerrado: exportar y aprobar una corrección que preserve el histórico de Kardex.');
            }
        }
        foreach ($tables as $name) {
            Schema::dropIfExists($name);
        }
        Schema::table('plan_asignatura', fn (Blueprint $table) => $table->dropUnique('plan_kardex_context_unique'));
    }
};
