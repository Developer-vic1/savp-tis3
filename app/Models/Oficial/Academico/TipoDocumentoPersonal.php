<?php

declare(strict_types=1);

namespace App\Models\Oficial\Academico;

use App\Support\Modelos\CodigoInstitucional;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/** Entidad del contrato oficial; conserva las claves de sus madres. */
class TipoDocumentoPersonal extends Model
{
    use CodigoInstitucional;

    protected $table = 'tipo_documento_personal';

    protected $primaryKey = 'cod_tdp';

    public $incrementing = false;

    protected $keyType = 'string';

    protected $fillable = [
        'cla_tdp',
        'nom_tdp',
        'des_tdp',
        'ven_tdp',
        'est_tdp',
    ];

    protected $casts = [
        'ven_tdp' => 'boolean',
    ];

    public function requisitoDocumentoCargoRegistros(): HasMany
    {
        return $this->hasMany(RequisitoDocumentoCargo::class, 'cod_tdp', 'cod_tdp');
    }

    public function documentoPersonalRegistros(): HasMany
    {
        return $this->hasMany(DocumentoPersonal::class, 'cod_tdp', 'cod_tdp');
    }
}
