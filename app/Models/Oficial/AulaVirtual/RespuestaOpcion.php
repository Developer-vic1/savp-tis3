<?php

declare(strict_types=1);

namespace App\Models\Oficial\AulaVirtual;

use App\Support\Modelos\CodigoInstitucional;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/** Entidad del contrato oficial; conserva las claves de sus madres. */
class RespuestaOpcion extends Model
{
    use CodigoInstitucional;

    protected $table = 'respuesta_opcion';

    protected $primaryKey = 'cod_rop';

    public $incrementing = false;

    protected $keyType = 'string';

    public const UPDATED_AT = null;

    protected $fillable = [
        'cod_rcu',
        'cod_opr',
    ];

    protected $casts = [
    ];

    public function respuestaCuestionario(): BelongsTo
    {
        return $this->belongsTo(RespuestaCuestionario::class, 'cod_rcu', 'cod_rcu');
    }

    public function opcionPregunta(): BelongsTo
    {
        return $this->belongsTo(OpcionPregunta::class, 'cod_opr', 'cod_opr');
    }
}
