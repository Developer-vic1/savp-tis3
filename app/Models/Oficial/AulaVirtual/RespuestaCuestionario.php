<?php

declare(strict_types=1);

namespace App\Models\Oficial\AulaVirtual;

use App\Models\Oficial\Academico\Docente;
use App\Models\Soporte\CodigoInstitucional;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/** Entidad del contrato oficial; conserva las claves de sus madres. */
class RespuestaCuestionario extends Model
{
    use CodigoInstitucional;

    protected $table = 'respuesta_cuestionario';

    protected $primaryKey = 'cod_rcu';

    public $incrementing = false;

    protected $keyType = 'string';

    protected $fillable = [
        'cod_inc',
        'cod_prc',
        'tex_rcu',
        'pun_rcu',
        'cor_rcu',
        'doc_rcu',
        'fec_rcu',
        'obs_rcu',
    ];

    protected $casts = [
        'pun_rcu' => 'decimal:2',
        'cor_rcu' => 'boolean',
        'fec_rcu' => 'datetime',
    ];

    public function intentoCuestionario(): BelongsTo
    {
        return $this->belongsTo(IntentoCuestionario::class, 'cod_inc', 'cod_inc');
    }

    public function preguntaCuestionario(): BelongsTo
    {
        return $this->belongsTo(PreguntaCuestionario::class, 'cod_prc', 'cod_prc');
    }

    public function docente(): BelongsTo
    {
        return $this->belongsTo(Docente::class, 'doc_rcu', 'cod_doc');
    }

    public function respuestaOpcionRegistros(): HasMany
    {
        return $this->hasMany(RespuestaOpcion::class, 'cod_rcu', 'cod_rcu');
    }
}
