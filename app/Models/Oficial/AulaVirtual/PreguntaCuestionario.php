<?php

declare(strict_types=1);

namespace App\Models\Oficial\AulaVirtual;

use App\Support\Modelos\CodigoInstitucional;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/** Entidad del contrato oficial; conserva las claves de sus madres. */
class PreguntaCuestionario extends Model
{
    use CodigoInstitucional;

    protected $table = 'pregunta_cuestionario';

    protected $primaryKey = 'cod_prc';

    public $incrementing = false;

    protected $keyType = 'string';

    protected $fillable = [
        'cod_cue',
        'tip_prc',
        'enu_prc',
        'pun_prc',
        'ord_prc',
        'req_prc',
        'est_prc',
    ];

    protected $casts = [
        'pun_prc' => 'decimal:2',
        'ord_prc' => 'integer',
        'req_prc' => 'boolean',
    ];

    public function cuestionario(): BelongsTo
    {
        return $this->belongsTo(Cuestionario::class, 'cod_cue', 'cod_cue');
    }

    public function opcionPreguntaRegistros(): HasMany
    {
        return $this->hasMany(OpcionPregunta::class, 'cod_prc', 'cod_prc');
    }

    public function respuestaCuestionarioRegistros(): HasMany
    {
        return $this->hasMany(RespuestaCuestionario::class, 'cod_prc', 'cod_prc');
    }
}
