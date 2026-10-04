<?php

declare(strict_types=1);

namespace App\Models\Oficial\AporteAcademicoVocacional;

use App\Models\Soporte\CodigoInstitucional;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/** Entidad del contrato oficial; conserva las claves de sus madres. */
class Carrera extends Model
{
    use CodigoInstitucional;

    protected $table = 'carrera';

    protected $primaryKey = 'cod_car';

    public $incrementing = false;

    protected $keyType = 'string';

    protected $fillable = [
        'nom_car',
        'are_car',
        'niv_car',
        'des_car',
        'est_car',
    ];

    protected $casts = [
    ];

    public function orientacionCarreraSugeridaRegistros(): HasMany
    {
        return $this->hasMany(OrientacionCarreraSugerida::class, 'cod_car', 'cod_car');
    }

    public function ofertaAcademicaRegistros(): HasMany
    {
        return $this->hasMany(OfertaAcademica::class, 'cod_car', 'cod_car');
    }
}
