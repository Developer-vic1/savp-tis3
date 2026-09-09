<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SeguimientoAcademico extends Model
{
    protected static function booted(): void
    {
        static::deleting(fn () => throw new \DomainException('El seguimiento no se elimina. Utilice cierre o cancelación.'));
    }

    protected $table = 'seguimiento_academico';

    protected $primaryKey = 'cod_seg';

    public $incrementing = false;

    protected $keyType = 'string';

    protected $fillable = ['cod_seg', 'cod_est', 'cod_gea', 'tip_seg', 'ori_seg', 'mot_seg', 'niv_ape_seg', 'est_seg', 'vis_seg', 'cod_usu_res', 'fec_ape_seg', 'fec_pro_seg', 'fec_cie_seg', 'res_seg', 'pro_acc_seg', 'obs_seg'];

    protected $casts = ['fec_ape_seg' => 'date', 'fec_pro_seg' => 'date', 'fec_cie_seg' => 'date'];

    public function estudiante()
    {
        return $this->belongsTo(Estudiante::class, 'cod_est', 'cod_est');
    }

    public function gestion()
    {
        return $this->belongsTo(GestionAcademica::class, 'cod_gea', 'cod_gea');
    }

    public function responsable()
    {
        return $this->belongsTo(User::class, 'cod_usu_res', 'cod_usu');
    }
}
