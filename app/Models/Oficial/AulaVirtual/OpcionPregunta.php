<?php

declare(strict_types=1);

namespace App\Models\Oficial\AulaVirtual;

use App\Models\Soporte\CodigoInstitucional;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/** Entidad del contrato oficial; conserva las claves de sus madres. */
class OpcionPregunta extends Model
{
    use CodigoInstitucional;

    protected $table = 'opcion_pregunta';

    protected $primaryKey = 'cod_opr';

    protected $hidden = ['por_opr'];

    public $incrementing = false;

    protected $keyType = 'string';

    protected $fillable = [
        'cod_prc',
        'tex_opr',
        'por_opr',
        'ord_opr',
        'est_opr',
    ];

    protected $casts = [
        'por_opr' => 'decimal:2',
        'ord_opr' => 'integer',
    ];

    public function preguntaCuestionario(): BelongsTo
    {
        return $this->belongsTo(PreguntaCuestionario::class, 'cod_prc', 'cod_prc');
    }

    public function respuestaOpcionRegistros(): HasMany
    {
        return $this->hasMany(RespuestaOpcion::class, 'cod_opr', 'cod_opr');
    }
}
