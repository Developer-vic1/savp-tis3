<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('especialidad_tecnica', function (Blueprint $table) {
            $table->string('sig_esp', 20)->nullable();
            $table->string('fam_pro_esp', 150)->nullable();
            $table->string('cam_for_esp', 150)->nullable();
            $table->string('area_bth_esp', 150)->nullable();
            
            $table->unsignedTinyInteger('niv_tec_esp')->nullable();
            $table->unsignedTinyInteger('niv_pro_esp')->nullable();
            $table->unsignedTinyInteger('niv_tecno_esp')->nullable();
            $table->unsignedTinyInteger('niv_soc_esp')->nullable();
            $table->unsignedTinyInteger('niv_art_esp')->nullable();
            $table->unsignedTinyInteger('niv_adm_esp')->nullable();
            
            $table->jsonb('comp_tec_esp')->nullable();
            $table->jsonb('hab_pra_esp')->nullable();
            $table->jsonb('hab_cog_esp')->nullable();
            $table->jsonb('asi_rel_esp')->nullable();
            $table->jsonb('car_rel_esp')->nullable();
            $table->jsonb('area_pro_esp')->nullable();
            $table->jsonb('perfil_riasec_esp')->nullable();
            $table->jsonb('int_mul_esp')->nullable();
            $table->jsonb('act_comp_esp')->nullable();
            $table->jsonb('pal_cla_esp')->nullable();
            $table->jsonb('sin_esp')->nullable();
            $table->jsonb('err_com_esp')->nullable();
            
            $table->text('exp_voc_esp')->nullable();
            $table->jsonb('acc_rec_esp')->nullable();
            
            $table->string('est_int_esp', 50)->nullable();
            $table->unsignedTinyInteger('conf_esp')->default(0);
            $table->unsignedTinyInteger('val_aca_esp')->default(0);
            $table->boolean('clas_bth_esp')->default(false);
            $table->timestamp('fec_cla_esp')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('especialidad_tecnica', function (Blueprint $table) {
            $table->dropColumn([
                'sig_esp',
                'fam_pro_esp',
                'cam_for_esp',
                'area_bth_esp',
                'niv_tec_esp',
                'niv_pro_esp',
                'niv_tecno_esp',
                'niv_soc_esp',
                'niv_art_esp',
                'niv_adm_esp',
                'comp_tec_esp',
                'hab_pra_esp',
                'hab_cog_esp',
                'asi_rel_esp',
                'car_rel_esp',
                'area_pro_esp',
                'perfil_riasec_esp',
                'int_mul_esp',
                'act_comp_esp',
                'pal_cla_esp',
                'sin_esp',
                'err_com_esp',
                'exp_voc_esp',
                'acc_rec_esp',
                'est_int_esp',
                'conf_esp',
                'val_aca_esp',
                'clas_bth_esp',
                'fec_cla_esp',
            ]);
        });
    }
};
