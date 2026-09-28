<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

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
    ];

    protected static function booted(): void
    {
        static::creating(function ($calificacion) {

            if (!$calificacion->cod_cal) {

                $ultimo = self::where('cod_cal', 'like', 'CAL_%')
                    ->orderByDesc('cod_cal')
                    ->value('cod_cal');

                $numero = $ultimo
                    ? ((int) str_replace('CAL_', '', $ultimo)) + 1
                    : 1;

                $calificacion->cod_cal = 'CAL_' . str_pad($numero, 4, '0', STR_PAD_LEFT);
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
}
