<?php

namespace App\Models\Legado;

use App\Models\Asignatura;
use App\Models\Estudiante;
use App\Models\Oficial\Academico\InscripcionEstudiante;
use App\Models\Oficial\Academico\PlanEspecialidad;
use App\Models\PeriodoEvaluacion;
use App\Models\PlanAsignatura;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Calificacion extends Model
{
    protected $table = 'calificacion';

    protected $primaryKey = 'cod_cal';

    public $incrementing = false;

    protected $keyType = 'string';

    protected $casts = ['not_cal' => 'decimal:2'];

    protected $fillable = [
        'cod_cal', // Código calificación
        'cod_est', // Código estudiante
        'cod_asi', // Código asignatura
        'cod_pev', // Código periodo evaluación
        'cod_pas', // Asignación contextual: gestión, curso, paralelo, turno y docente
        'not_cal', // Nota calificación
        'obs_cal', // Observación calificación
        'est_cal', // Estado calificación
        'cod_ins',
        'cod_pes',
    ];

    protected static function booted(): void
    {
        static::creating(function ($calificacion) {

            if (! $calificacion->cod_cal) {
                $calificacion->cod_cal = 'CAL_'.strtoupper(bin2hex(random_bytes(8)));
            }
        });
    }

    // 🔗 Relaciones

    public function estudiante()
    {
        return $this->belongsTo(Estudiante::class, 'cod_est', 'cod_est');
    }

    public function asignatura()
    {
        return $this->belongsTo(Asignatura::class, 'cod_asi', 'cod_asi');
    }

    public function periodoEvaluacion()
    {
        return $this->belongsTo(PeriodoEvaluacion::class, 'cod_pev', 'cod_pev');
    }

    public function planAsignatura()
    {
        return $this->belongsTo(PlanAsignatura::class, 'cod_pas', 'cod_pas');
    }

    public function inscripcionEstudiante(): BelongsTo
    {
        return $this->belongsTo(InscripcionEstudiante::class, 'cod_ins', 'cod_ins');
    }

    public function planEspecialidad(): BelongsTo
    {
        return $this->belongsTo(PlanEspecialidad::class, 'cod_pes', 'cod_pes');
    }
}
