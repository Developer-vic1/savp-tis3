<?php

declare(strict_types=1);

namespace App\Models\Oficial\Academico;

use App\Models\Soporte\CodigoInstitucional;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/** Entidad del contrato oficial; conserva las claves de sus madres. */
class DocumentoTraslado extends Model
{
    use CodigoInstitucional;

    protected $table = 'documento_traslado';

    protected $primaryKey = 'cod_dtr';

    public $incrementing = false;

    protected $keyType = 'string';

    protected $fillable = [
        'cod_ext',
        'tip_dtr',
        'nom_dtr',
        'fec_dtr',
        'rut_dtr',
        'mim_dtr',
        'tam_dtr',
        'has_dtr',
        'est_dtr',
        'obs_dtr',
    ];

    protected $casts = [
        'fec_dtr' => 'date',
        'tam_dtr' => 'integer',
    ];

    public function expedienteTraslado(): BelongsTo
    {
        return $this->belongsTo(ExpedienteTraslado::class, 'cod_ext', 'cod_ext');
    }
}
