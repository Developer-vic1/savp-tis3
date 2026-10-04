<?php

declare(strict_types=1);

namespace App\Models\Oficial\AporteAcademicoVocacional;

use App\Models\Soporte\CodigoInstitucional;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/** Entidad del contrato oficial; conserva las claves de sus madres. */
class InstrumentoPregunta extends Model
{
    use CodigoInstitucional;

    protected $table = 'instrumento_pregunta';

    protected $primaryKey = 'cod_ipr';

    public $incrementing = false;

    protected $keyType = 'string';

    protected $fillable = [
        'cod_ior',
        'pre_ipr',
        'dim_ipr',
        'ord_ipr',
        'pes_ipr',
        'inv_ipr',
        'obl_ipr',
        'vis_ipr',
    ];

    protected $casts = [
        'pre_ipr' => 'integer',
        'ord_ipr' => 'integer',
        'pes_ipr' => 'decimal:4',
        'inv_ipr' => 'boolean',
        'obl_ipr' => 'boolean',
        'vis_ipr' => 'boolean',
    ];

    public function instrumentoOrientacion(): BelongsTo
    {
        return $this->belongsTo(InstrumentoOrientacion::class, 'cod_ior', 'cod_ior');
    }

    public function orientacionPregunta(): BelongsTo
    {
        return $this->belongsTo(OrientacionPregunta::class, 'pre_ipr', 'id');
    }

    public function orientacionRespuestaRegistros(): HasMany
    {
        return $this->hasMany(OrientacionRespuesta::class, 'cod_ipr', 'cod_ipr');
    }
}
