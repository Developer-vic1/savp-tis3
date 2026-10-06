<?php

declare(strict_types=1);

namespace App\Models\Oficial\Academico;

use App\Support\Modelos\CodigoInstitucional;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/** Entidad del contrato oficial; conserva las claves de sus madres. */
class CargoInstitucional extends Model
{
    use CodigoInstitucional;

    protected $table = 'cargo_institucional';

    protected $primaryKey = 'cod_cai';

    public $incrementing = false;

    protected $keyType = 'string';

    protected $fillable = [
        'cla_cai',
        'nom_cai',
        'des_cai',
        'est_cai',
    ];

    protected $casts = [
    ];

    public function vinculoPersonalRegistros(): HasMany
    {
        return $this->hasMany(VinculoPersonal::class, 'cod_cai', 'cod_cai');
    }

    public function requisitoDocumentoCargoRegistros(): HasMany
    {
        return $this->hasMany(RequisitoDocumentoCargo::class, 'cod_cai', 'cod_cai');
    }
}
