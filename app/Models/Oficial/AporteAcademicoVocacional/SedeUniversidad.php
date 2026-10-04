<?php

declare(strict_types=1);

namespace App\Models\Oficial\AporteAcademicoVocacional;

use App\Models\Soporte\CodigoInstitucional;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/** Entidad del contrato oficial; conserva las claves de sus madres. */
class SedeUniversidad extends Model
{
    use CodigoInstitucional;

    protected $table = 'sede_universidad';

    protected $primaryKey = 'cod_sed';

    public $incrementing = false;

    protected $keyType = 'string';

    protected $fillable = [
        'cod_uni',
        'nom_sed',
        'dep_sed',
        'ciu_sed',
        'dir_sed',
        'tel_sed',
        'web_sed',
        'lat_sed',
        'lon_sed',
        'fii_sed',
        'ffi_sed',
        'est_sed',
    ];

    protected $casts = [
        'lat_sed' => 'decimal:6',
        'lon_sed' => 'decimal:6',
        'fii_sed' => 'date',
        'ffi_sed' => 'date',
    ];

    public function universidad(): BelongsTo
    {
        return $this->belongsTo(Universidad::class, 'cod_uni', 'cod_uni');
    }

    public function ofertaAcademicaRegistros(): HasMany
    {
        return $this->hasMany(OfertaAcademica::class, 'cod_sed', 'cod_sed');
    }

    public function recursoFuenteRegistros(): HasMany
    {
        return $this->hasMany(RecursoFuente::class, 'cod_sed', 'cod_sed');
    }
}
