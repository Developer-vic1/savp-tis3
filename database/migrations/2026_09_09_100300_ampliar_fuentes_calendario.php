<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('calendario_evento', function (Blueprint $table) {
            $table->string('ent_cae', 180)->nullable();
            $table->string('tip_doc_cae', 80)->nullable();
            $table->string('num_doc_cae', 100)->nullable();
            $table->date('fec_emi_cae')->nullable();
            $table->date('fec_pub_cae')->nullable();
            $table->string('url_cae', 2000)->nullable();
            $table->string('niv_cae', 20)->default('INSTITUCIONAL');
            $table->string('cer_cae', 20)->default('INFORMATIVO');
            $table->string('cod_cae_ant', 20)->nullable();
            $table->foreign('cod_cae_ant')->references('cod_cae')->on('calendario_evento')->restrictOnDelete();
        });
        DB::statement('ALTER TABLE calendario_evento DROP CONSTRAINT cae_estado');
        DB::statement("ALTER TABLE calendario_evento ADD CONSTRAINT cae_estado CHECK (est_cae IN ('BORRADOR','PREALERTA','PENDIENTE_APROBACION','CONFIRMADO','CANCELADO','FINALIZADO','SUPERADO'))");
        DB::statement("ALTER TABLE calendario_evento ADD CONSTRAINT cae_nivel CHECK (niv_cae IN ('NACIONAL','DEPARTAMENTAL','DISTRITAL','INSTITUCIONAL'))");
        DB::statement("ALTER TABLE calendario_evento ADD CONSTRAINT cae_certeza CHECK (cer_cae IN ('INFORMATIVO','PROBABLE','ALTA_PROBABILIDAD','CONFIRMADO'))");
        DB::statement('ALTER TABLE calendario_evento ADD CONSTRAINT cae_antecesor CHECK (cod_cae_ant IS NULL OR cod_cae_ant <> cod_cae)');
    }

    public function down(): void
    {
        throw new RuntimeException('Las fuentes normativas y sus versiones forman parte de la historia académica.');
    }
};
