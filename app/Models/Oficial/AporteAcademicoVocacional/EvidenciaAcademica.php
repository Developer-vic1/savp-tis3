<?php

declare(strict_types=1);

namespace App\Models\Oficial\AporteAcademicoVocacional;

use App\Models\Soporte\CodigoInstitucional;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/** Entidad del contrato oficial; conserva las claves de sus madres. */
class EvidenciaAcademica extends Model
{
    use CodigoInstitucional;

    protected $table = 'evidencia_academica';

    protected $primaryKey = 'cod_eac';

    public $incrementing = false;

    protected $keyType = 'string';

    protected $fillable = [
        'cod_vof',
        'cod_cfu',
        'tip_eac',
        'cam_eac',
        'con_eac',
        'est_eac',
    ];

    protected $casts = [
    ];

    public function versionOfertaAcademica(): BelongsTo
    {
        return $this->belongsTo(VersionOfertaAcademica::class, 'cod_vof', 'cod_vof');
    }

    public function capturaFuente(): BelongsTo
    {
        return $this->belongsTo(CapturaFuente::class, 'cod_cfu', 'cod_cfu');
    }
}
