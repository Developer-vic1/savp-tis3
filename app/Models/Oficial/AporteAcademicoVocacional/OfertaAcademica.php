<?php

declare(strict_types=1);

namespace App\Models\Oficial\AporteAcademicoVocacional;

use App\Models\Soporte\CodigoInstitucional;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/** Entidad del contrato oficial; conserva las claves de sus madres. */
class OfertaAcademica extends Model
{
    use CodigoInstitucional;

    protected $table = 'oferta_academica';

    protected $primaryKey = 'cod_ofa';

    public $incrementing = false;

    protected $keyType = 'string';

    protected $fillable = [
        'cod_sed',
        'cod_car',
    ];

    protected $casts = [
    ];

    public function sedeUniversidad(): BelongsTo
    {
        return $this->belongsTo(SedeUniversidad::class, 'cod_sed', 'cod_sed');
    }

    public function carrera(): BelongsTo
    {
        return $this->belongsTo(Carrera::class, 'cod_car', 'cod_car');
    }

    public function versionOfertaAcademicaRegistros(): HasMany
    {
        return $this->hasMany(VersionOfertaAcademica::class, 'cod_ofa', 'cod_ofa');
    }
}
