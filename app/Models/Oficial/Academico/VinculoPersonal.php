<?php

declare(strict_types=1);

namespace App\Models\Oficial\Academico;

use App\Models\Soporte\CodigoInstitucional;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/** Entidad del contrato oficial; conserva las claves de sus madres. */
class VinculoPersonal extends Model
{
    use CodigoInstitucional;

    protected $table = 'vinculo_personal';

    protected $primaryKey = 'cod_vpe';

    public $incrementing = false;

    protected $keyType = 'string';

    protected $fillable = [
        'cod_pin',
        'cod_cai',
        'tip_vpe',
        'fii_vpe',
        'ffi_vpe',
        'ref_vpe',
        'est_vpe',
        'obs_vpe',
    ];

    protected $casts = [
        'fii_vpe' => 'date',
        'ffi_vpe' => 'date',
    ];

    public function personalInstitucional(): BelongsTo
    {
        return $this->belongsTo(PersonalInstitucional::class, 'cod_pin', 'cod_pin');
    }

    public function cargoInstitucional(): BelongsTo
    {
        return $this->belongsTo(CargoInstitucional::class, 'cod_cai', 'cod_cai');
    }

    public function documentoPersonalRegistros(): HasMany
    {
        return $this->hasMany(DocumentoPersonal::class, 'cod_vpe', 'cod_vpe');
    }

    public function regenteAsignacionRegistros(): HasMany
    {
        return $this->hasMany(RegenteAsignacion::class, 'cod_vpe', 'cod_vpe');
    }
}
