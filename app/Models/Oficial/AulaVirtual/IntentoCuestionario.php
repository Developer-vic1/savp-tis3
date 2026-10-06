<?php

declare(strict_types=1);

namespace App\Models\Oficial\AulaVirtual;

use App\Models\Oficial\Academico\Estudiante;
use App\Support\Modelos\CodigoInstitucional;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/** Entidad del contrato oficial; conserva las claves de sus madres. */
class IntentoCuestionario extends Model
{
    use CodigoInstitucional;

    protected $table = 'intento_cuestionario';

    protected $primaryKey = 'cod_inc';

    public $incrementing = false;

    protected $keyType = 'string';

    protected $fillable = [
        'cod_cue',
        'cod_est',
        'num_inc',
        'ini_inc',
        'fin_inc',
        'pun_inc',
        'max_inc',
        'est_inc',
    ];

    protected $casts = [
        'num_inc' => 'integer',
        'ini_inc' => 'datetime',
        'fin_inc' => 'datetime',
        'pun_inc' => 'decimal:2',
        'max_inc' => 'decimal:2',
    ];

    public function cuestionario(): BelongsTo
    {
        return $this->belongsTo(Cuestionario::class, 'cod_cue', 'cod_cue');
    }

    public function estudiante(): BelongsTo
    {
        return $this->belongsTo(Estudiante::class, 'cod_est', 'cod_est');
    }

    public function respuestaCuestionarioRegistros(): HasMany
    {
        return $this->hasMany(RespuestaCuestionario::class, 'cod_inc', 'cod_inc');
    }
}
