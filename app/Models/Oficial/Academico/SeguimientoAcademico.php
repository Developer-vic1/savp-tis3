<?php

declare(strict_types=1);

namespace App\Models\Oficial\Academico;

use App\Models\Soporte\CodigoInstitucional;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/** Modelo del contrato canónico; atributos históricos redundantes permanecen en Legado. */
class SeguimientoAcademico extends Model
{
    use CodigoInstitucional;

    protected $table = 'seguimiento_academico';

    protected $primaryKey = 'cod_seg';

    public $incrementing = false;

    protected $keyType = 'string';

    protected $fillable = [
        'cod_ins',
        'tip_seg',
        'ori_seg',
        'mot_seg',
        'niv_ape_seg',
        'est_seg',
        'vis_seg',
        'cod_usu_res',
        'fec_ape_seg',
        'fec_pro_seg',
        'fec_cie_seg',
        'res_seg',
        'pro_acc_seg',
        'obs_seg',
    ];

    protected $casts = [
        'fec_ape_seg' => 'datetime',
        'fec_pro_seg' => 'datetime',
        'fec_cie_seg' => 'datetime',
    ];

    public function inscripcionEstudiante(): BelongsTo
    {
        return $this->belongsTo(InscripcionEstudiante::class, 'cod_ins', 'cod_ins');
    }
}
