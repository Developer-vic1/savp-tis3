<?php

declare(strict_types=1);

namespace App\Models\Oficial\AporteAcademicoVocacional;

use App\Support\Modelos\CodigoInstitucional;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/** Entidad del contrato oficial; conserva las claves de sus madres. */
class InstrumentoOrientacion extends Model
{
    use CodigoInstitucional;

    protected $table = 'instrumento_orientacion';

    protected $primaryKey = 'cod_ior';

    public $incrementing = false;

    protected $keyType = 'string';

    protected $fillable = [
        'nom_ior',
        'ver_ior',
        'tip_ior',
        'alg_ior',
        'des_ior',
        'fii_ior',
        'ffi_ior',
        'est_ior',
    ];

    protected $casts = [
        'fii_ior' => 'date',
        'ffi_ior' => 'date',
    ];

    public function instrumentoPreguntaRegistros(): HasMany
    {
        return $this->hasMany(InstrumentoPregunta::class, 'cod_ior', 'cod_ior');
    }

    public function orientacionActividadRegistros(): HasMany
    {
        return $this->hasMany(OrientacionActividad::class, 'cod_ior', 'cod_ior');
    }
}
