<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\{DB,Schema};

return new class extends Migration {
    public function up(): void
    {
        Schema::create('concesion_acceso',function(Blueprint $t){
            $t->string('cod_cac',20)->primary(); $t->string('cod_gea',20);
            $t->string('cod_usu_autorizador',20); $t->foreignId('role_id')->nullable()->constrained('roles')->restrictOnDelete();
            $t->string('tipo',15); $t->text('motivo');
            $t->timestampTz('inicio'); $t->timestampTz('fin');
            $t->string('estado',15)->default('AUTORIZADA');
            $t->timestampTz('activada_en')->nullable(); $t->timestampTz('finalizada_en')->nullable();
            $t->string('cod_usu_revocador',20)->nullable(); $t->text('motivo_revocacion')->nullable();
            $t->timestampTz('revocada_en')->nullable(); $t->timestampsTz();
            $t->foreign('cod_gea')->references('cod_gea')->on('gestion_academica')->restrictOnDelete();
            $t->foreign('cod_usu_autorizador')->references('cod_usu')->on('users')->restrictOnDelete();
            $t->foreign('cod_usu_revocador')->references('cod_usu')->on('users')->restrictOnDelete();
            $t->index(['estado','inicio','fin']);
        });
        Schema::create('concesion_acceso_usuario',function(Blueprint $t){
            $t->string('cod_cac',20);$t->string('cod_usu',20);$t->primary(['cod_cac','cod_usu']);
            $t->foreign('cod_cac')->references('cod_cac')->on('concesion_acceso')->restrictOnDelete();
            $t->foreign('cod_usu')->references('cod_usu')->on('users')->restrictOnDelete();
            $t->index(['cod_usu','cod_cac']);
        });
        Schema::create('concesion_acceso_permiso',function(Blueprint $t){
            $t->string('cod_cac',20);$t->unsignedBigInteger('permission_id');$t->primary(['cod_cac','permission_id']);
            $t->foreign('cod_cac')->references('cod_cac')->on('concesion_acceso')->restrictOnDelete();
            $t->foreign('permission_id')->references('id')->on('permissions')->restrictOnDelete();
        });
        if(DB::getDriverName()==='pgsql'){
            DB::statement("ALTER TABLE concesion_acceso ADD CONSTRAINT concesion_acceso_valores_ck CHECK (cod_cac ~ '^CAC_[0-9]{6,16}$' AND tipo IN ('PERMISOS','ROL') AND ((tipo = 'ROL') = (role_id IS NOT NULL)) AND fin > inicio AND estado IN ('AUTORIZADA','REVOCADA') AND length(btrim(motivo)) >= 20 AND (estado <> 'REVOCADA' OR (cod_usu_revocador IS NOT NULL AND revocada_en IS NOT NULL AND length(btrim(motivo_revocacion)) >= 20)))");
        }
    }
    public function down(): void
    {
        if(Schema::hasTable('concesion_acceso')&&DB::table('concesion_acceso')->exists())throw new RuntimeException('Conservar el historial de acceso; preparar una migración correctiva.');
        foreach(['concesion_acceso_permiso','concesion_acceso_usuario','concesion_acceso'] as $tabla)Schema::dropIfExists($tabla);
    }
};
