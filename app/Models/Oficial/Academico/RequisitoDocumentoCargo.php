<?php

declare(strict_types=1);

namespace App\Models\Oficial\Academico;

use App\Support\Modelos\CodigoInstitucional;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/** Entidad del contrato oficial; conserva las claves de sus madres. */
class RequisitoDocumentoCargo extends Model
{
    use CodigoInstitucional;

    protected $table = 'requisito_documento_cargo';

    protected $primaryKey = 'cod_rdc';

    public $incrementing = false;

    protected $keyType = 'string';

    protected $fillable = [
        'cod_cai',
        'cod_tdp',
        'obl_rdc',
        'vig_rdc',
        'dia_rdc',
        'est_rdc',
    ];

    protected $casts = [
        'obl_rdc' => 'boolean',
        'vig_rdc' => 'boolean',
        'dia_rdc' => 'integer',
    ];

    public function cargoInstitucional(): BelongsTo
    {
        return $this->belongsTo(CargoInstitucional::class, 'cod_cai', 'cod_cai');
    }

    public function tipoDocumentoPersonal(): BelongsTo
    {
        return $this->belongsTo(TipoDocumentoPersonal::class, 'cod_tdp', 'cod_tdp');
    }
}
