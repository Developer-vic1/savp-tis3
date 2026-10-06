<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('solicitud_rol', function (Blueprint $t) {
            $t->string('cod_sor',20)->primary();
            $t->string('cod_gea',20);
            $t->string('cod_usu_solicitante',20);
            $t->string('cod_usu_director',20);
            $t->string('cod_usu_revisor',20)->nullable();
            $t->foreignId('role_id')->nullable()->constrained('roles')->restrictOnDelete();
            $t->string('nombre',80);
            $t->text('justificacion'); $t->text('motivo'); $t->text('funciones');
            $t->string('alcance',120); $t->text('observaciones')->nullable();
            $t->string('documento_ruta',255); $t->char('documento_sha256',64);
            $t->json('analisis');
            $t->string('estado',15)->default('PENDIENTE');
            $t->text('nota_revision')->nullable();
            $t->timestampTz('revisada_en')->nullable(); $t->timestampTz('creada_en')->nullable();
            $t->timestampsTz();
            $t->foreign('cod_gea')->references('cod_gea')->on('gestion_academica')->restrictOnDelete();
            foreach(['solicitante','director','revisor'] as $c) $t->foreign('cod_usu_'.$c)->references('cod_usu')->on('users')->restrictOnDelete();
            $t->index(['estado','created_at']);
        });
        Schema::create('solicitud_rol_permiso',function(Blueprint $t){
            $t->string('cod_sor',20); $t->unsignedBigInteger('permission_id');
            $t->primary(['cod_sor','permission_id']);
            $t->foreign('cod_sor')->references('cod_sor')->on('solicitud_rol')->restrictOnDelete();
            $t->foreign('permission_id')->references('id')->on('permissions')->restrictOnDelete();
        });
        if(DB::getDriverName()==='pgsql'){
            DB::statement("ALTER TABLE solicitud_rol ADD CONSTRAINT solicitud_rol_valores_ck CHECK (cod_sor ~ '^SOR_[0-9]{6,16}$' AND estado IN ('PENDIENTE','REVISADA','RECHAZADA','CREADA','CANCELADA') AND cod_usu_solicitante <> cod_usu_revisor AND length(btrim(nombre)) BETWEEN 4 AND 80 AND documento_sha256 ~ '^[a-f0-9]{64}$' AND (estado <> 'CREADA' OR (role_id IS NOT NULL AND creada_en IS NOT NULL)))");

        }
    }
    public function down(): void
    {
        foreach(['solicitud_rol'] as $t) if(Schema::hasTable($t)&&DB::table($t)->exists()) throw new RuntimeException('Conservar el historial de acceso; preparar una migración correctiva.');
        foreach(['solicitud_rol_permiso','solicitud_rol'] as $t) Schema::dropIfExists($t);
    }
};
