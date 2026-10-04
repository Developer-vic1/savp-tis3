<?php

declare(strict_types=1);

namespace App\Models\Oficial\AporteAcademicoVocacional;

use App\Models\Soporte\CodigoInstitucional;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/** Entidad del contrato oficial; conserva las claves de sus madres. */
class VersionOfertaAcademica extends Model
{
    use CodigoInstitucional;

    protected $table = 'version_oferta_academica';

    protected $primaryKey = 'cod_vof';

    public $incrementing = false;

    protected $keyType = 'string';

    protected $fillable = [
        'cod_ofa',
        'nom_vof',
        'mod_vof',
        'dur_vof',
        'uni_vof',
        'tit_vof',
        'req_vof',
        'cos_vof',
        'mon_vof',
        'bec_vof',
        'fii_vof',
        'ffi_vof',
        'est_vof',
        'fec_vof',
        'dat_vof',
    ];

    protected $casts = [
        'dur_vof' => 'decimal:2',
        'cos_vof' => 'decimal:2',
        'fii_vof' => 'date',
        'ffi_vof' => 'date',
        'fec_vof' => 'datetime',
        'dat_vof' => 'array',
    ];

    public function ofertaAcademica(): BelongsTo
    {
        return $this->belongsTo(OfertaAcademica::class, 'cod_ofa', 'cod_ofa');
    }

    public function orientacionOfertaSugeridaRegistros(): HasMany
    {
        return $this->hasMany(OrientacionOfertaSugerida::class, 'cod_vof', 'cod_vof');
    }

    public function evidenciaAcademicaRegistros(): HasMany
    {
        return $this->hasMany(EvidenciaAcademica::class, 'cod_vof', 'cod_vof');
    }
}
