<?php

namespace App\Models\Legado;

use App\Models\Estudiante;
use App\Models\GestionAcademica;
use App\Models\Oficial\Academico\InscripcionEstudiante;
use App\Models\PlanAsignatura;
use App\Models\Soporte\CodigoInstitucional;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/** Hecho de seguimiento; los campos de propuestas anteriores no se presuponen desplegados. */
class SeguimientoAcademico extends Model
{
    use CodigoInstitucional;

    protected $table = 'seguimiento_academico';

    protected $primaryKey = 'cod_seg';

    public $incrementing = false;

    protected $keyType = 'string';

    protected $guarded = ['cod_seg', 'cod_est', 'cod_gea', 'cod_cur', 'cod_pas', 'cod_usu_aut', 'cod_usu_res'];

    protected $casts = ['fec_ape_seg' => 'date', 'fec_pro_seg' => 'date', 'fec_cie_seg' => 'date',
        'vis_seg' => 'boolean'];

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

    public function inscripcionEstudiante(): BelongsTo
    {
        return $this->belongsTo(InscripcionEstudiante::class, 'cod_ins', 'cod_ins');
    }

    public function users(): BelongsTo
    {
        return $this->belongsTo(User::class, 'cod_usu_res', 'cod_usu');
    }
}
