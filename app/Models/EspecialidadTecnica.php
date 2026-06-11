<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EspecialidadTecnica extends Model
{
    protected $table = 'especialidad_tecnica';
    protected $primaryKey = 'cod_esp';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'cod_esp',
        'nom_esp',
        'des_esp',
        'est_esp',
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
    ];

    protected $casts = [
        'comp_tec_esp' => 'array',
        'hab_pra_esp' => 'array',
        'hab_cog_esp' => 'array',
        'asi_rel_esp' => 'array',
        'car_rel_esp' => 'array',
        'area_pro_esp' => 'array',
        'perfil_riasec_esp' => 'array',
        'int_mul_esp' => 'array',
        'act_comp_esp' => 'array',
        'pal_cla_esp' => 'array',
        'sin_esp' => 'array',
        'err_com_esp' => 'array',
        'acc_rec_esp' => 'array',
        'clas_bth_esp' => 'boolean',
        'niv_tec_esp' => 'integer',
        'niv_pro_esp' => 'integer',
        'niv_tecno_esp' => 'integer',
        'niv_soc_esp' => 'integer',
        'niv_art_esp' => 'integer',
        'niv_adm_esp' => 'integer',
        'conf_esp' => 'integer',
        'val_aca_esp' => 'integer',
        'fec_cla_esp' => 'datetime',
    ];

    protected static function booted(): void
    {
        static::creating(function ($especialidad) {
            if (!$especialidad->cod_esp) {
                $ultimo = self::where('cod_esp', 'like', 'ESP_%')
                    ->orderByDesc('cod_esp')
                    ->value('cod_esp');

                $numero = $ultimo
                    ? ((int) str_replace('ESP_', '', $ultimo)) + 1
                    : 1;

                $especialidad->cod_esp = 'ESP_' . str_pad($numero, 4, '0', STR_PAD_LEFT);
            }
        });
    }

    public function estaClasificadaBth(): bool
    {
        return (bool) $this->clas_bth_esp;
    }

    public function tieneOrientacionCompleta(): bool
    {
        return !empty($this->perfil_riasec_esp) && !empty($this->car_rel_esp);
    }

    public function estudiantes()
    {
        return $this->hasMany(Estudiante::class, 'cod_esp', 'cod_esp');
    }

    public function planesEspecialidad()
    {
        return $this->hasMany(PlanEspecialidad::class, 'cod_esp', 'cod_esp');
    }

    public function scopeActivas($query)
    {
        return $query->where('est_esp', 'ACTIVO');
    }

    public function scopeReconocidas($query)
    {
        return $query->where('est_int_esp', 'RECONOCIDA');
    }

    public function scopePendientesClasificacion($query)
    {
        return $query->where('clas_bth_esp', false);
    }
}