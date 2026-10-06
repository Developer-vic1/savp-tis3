<?php

declare(strict_types=1);

namespace App\Models\Oficial\Academico;

use App\Models\Oficial\Academico\Estudiante;
use App\Support\Modelos\CodigoInstitucional;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOneThrough;

/** Modelo único de la tabla oficial; atributos y relaciones del contrato canónico. */
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
        'fea_cal',
        'obs_cal',
        'est_cal',
    ];

    protected $casts = [
        'not_cal' => 'decimal:2',
        'fea_cal' => 'date',
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

    public function estudiante(): HasOneThrough
    {
        return $this->hasOneThrough(Estudiante::class, InscripcionEstudiante::class, 'cod_ins', 'cod_est', 'cod_ins', 'cod_est');
    }

    public function asignatura(): HasOneThrough
    {
        return $this->hasOneThrough(Asignatura::class, PlanAsignatura::class, 'cod_pas', 'cod_asi', 'cod_pas', 'cod_asi');
    }

    public function getCodEstAttribute(): ?string
    {
        return $this->estudiante?->cod_est;
    }

    public function getCodAsiAttribute(): ?string
    {
        return $this->asignatura?->cod_asi;
    }

    public function scopeDeEstudiante(Builder $query, string $estudiante): Builder
    {
        return $query->whereHas('inscripcionEstudiante', fn (Builder $inscripcion) => $inscripcion->where('cod_est', $estudiante));
    }

    public function scopeDeAsignatura(Builder $query, string $asignatura): Builder
    {
        return $query->whereHas('planAsignatura', fn (Builder $plan) => $plan->where('cod_asi', $asignatura));
    }
}
