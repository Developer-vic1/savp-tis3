<?php

declare(strict_types=1);

namespace App\Models\Oficial\Academico;

use App\Support\Modelos\CodigoInstitucional;
use App\Support\Modelos\ContextoGrupoAcademico;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/** Modelo único de la tabla oficial; atributos y relaciones del contrato canónico. */
class Horario extends Model
{
    use CodigoInstitucional;
    use ContextoGrupoAcademico;

    protected $table = 'horario';

    protected $primaryKey = 'cod_hor';

    public $incrementing = false;

    protected $keyType = 'string';

    protected $fillable = [
        'cod_gac',
        'cod_pho',
        'fii_hor',
        'ffi_hor',
        'obs_hor',
        'est_hor',
    ];

    protected $casts = [
        'fii_hor' => 'date',
        'ffi_hor' => 'date',
    ];

    public function grupoAcademico(): BelongsTo
    {
        return $this->belongsTo(GrupoAcademico::class, 'cod_gac', 'cod_gac');
    }

    public function plantillaHoraria(): BelongsTo
    {
        return $this->belongsTo(PlantillaHoraria::class, 'cod_pho', 'cod_pho');
    }

    public function horarioDetalleRegistros(): HasMany
    {
        return $this->hasMany(HorarioDetalle::class, 'cod_hor', 'cod_hor');
    }
}
