<?php

declare(strict_types=1);

namespace App\Models\Oficial\Academico;

use App\Models\Soporte\CodigoInstitucional;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/** Modelo del contrato canónico; atributos históricos redundantes permanecen en Legado. */
class Calificacion extends Model
{
    use CodigoInstitucional;

    protected $table = 'calificacion';

    protected $primaryKey = 'cod_cal';

    public $incrementing = false;

    protected $keyType = 'string';

    protected $fillable = [
        'cod_ins',
        'cod_pas',
        'cod_pes',
        'cod_pev',
        'not_cal',
        'obs_cal',
        'est_cal',
    ];

    protected $casts = [
        'not_cal' => 'decimal:2',
    ];

    public function inscripcionEstudiante(): BelongsTo
    {
        return $this->belongsTo(InscripcionEstudiante::class, 'cod_ins', 'cod_ins');
    }

    public function planAsignatura(): BelongsTo
    {
        return $this->belongsTo(PlanAsignatura::class, 'cod_pas', 'cod_pas');
    }

    public function planEspecialidad(): BelongsTo
    {
        return $this->belongsTo(PlanEspecialidad::class, 'cod_pes', 'cod_pes');
    }

    public function periodoEvaluacion(): BelongsTo
    {
        return $this->belongsTo(PeriodoEvaluacion::class, 'cod_pev', 'cod_pev');
    }
}
