<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/** Concepto canónico del árbol operativo, ampliado por MIG-001; no habilita escrituras. */
class SeguimientoAcademico extends Model
{
    protected $table = 'seguimiento_academico';

    protected $primaryKey = 'cod_seg';

    public $incrementing = false;

    protected $keyType = 'string';

    protected $guarded = ['cod_seg', 'cod_est', 'cod_gea', 'cod_cur', 'cod_pas', 'cod_usu_aut', 'cod_usu_res'];

    protected $casts = ['fec_ape_seg' => 'date', 'fec_pro_seg' => 'date', 'fec_cie_seg' => 'date',
        'visible_estudiante' => 'boolean', 'version_catalogo' => 'integer'];

    protected static function booted(): void
    {
        static::deleting(fn () => throw new \DomainException('El seguimiento conserva su histórico mediante revisiones; no se elimina.'));
    }

    public function estudiante()
    {
        return $this->belongsTo(Estudiante::class, 'cod_est', 'cod_est');
    }

    public function planAsignatura()
    {
        return $this->belongsTo(PlanAsignatura::class, 'cod_pas', 'cod_pas');
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
