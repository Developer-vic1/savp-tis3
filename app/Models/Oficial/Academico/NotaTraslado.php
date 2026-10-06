<?php

declare(strict_types=1);

namespace App\Models\Oficial\Academico;

use App\Support\Modelos\CodigoInstitucional;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/** Entidad del contrato oficial; conserva las claves de sus madres. */
class NotaTraslado extends Model
{
    use CodigoInstitucional;

    protected $table = 'nota_traslado';

    protected $primaryKey = 'cod_ntr';

    public $incrementing = false;

    protected $keyType = 'string';

    protected $fillable = [
        'cod_ext',
        'ani_ntr',
        'cur_ntr',
        'mat_ntr',
        'per_ntr',
        'ori_ntr',
        'not_ntr',
        'esc_ntr',
        'cod_cur',
        'cod_asi',
        'cod_pev',
        'obs_ntr',
    ];

    protected $casts = [
        'ani_ntr' => 'integer',
        'not_ntr' => 'decimal:2',
    ];

    public function expedienteTraslado(): BelongsTo
    {
        return $this->belongsTo(ExpedienteTraslado::class, 'cod_ext', 'cod_ext');
    }

    public function curso(): BelongsTo
    {
        return $this->belongsTo(Curso::class, 'cod_cur', 'cod_cur');
    }

    public function asignatura(): BelongsTo
    {
        return $this->belongsTo(Asignatura::class, 'cod_asi', 'cod_asi');
    }

    public function periodoEvaluacion(): BelongsTo
    {
        return $this->belongsTo(PeriodoEvaluacion::class, 'cod_pev', 'cod_pev');
    }
}
