<?php

namespace App\Models\Legado;

use App\Models\Calificacion;
use App\Models\Oficial\Academico\ConfiguracionCalendarioGestion;
use App\Models\Oficial\Academico\NotaTraslado;
use App\Models\Oficial\AulaVirtual\Cuestionario;
use App\Models\Oficial\AulaVirtual\Tarea;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PeriodoEvaluacion extends Model
{
    protected $table = 'periodo_evaluacion';

    protected $primaryKey = 'cod_pev';

    public $incrementing = false;

    protected $keyType = 'string';

    protected $fillable = [
        'cod_pev', // Código periodo evaluación
        'nom_pev', // Nombre periodo evaluación
        'ord_pev', // Orden periodo evaluación
        'est_pev', // Estado periodo evaluación
    ];

    protected static function booted(): void
    {
        static::creating(function ($periodo) {

            if (! $periodo->cod_pev) {
                $periodo->cod_pev = 'PEV_'.strtoupper(bin2hex(random_bytes(8)));
            }
        });
    }

    // 🔗 Relaciones

    public function calificaciones()
    {
        return $this->hasMany(Calificacion::class, 'cod_pev', 'cod_pev');
    }

    protected $casts = [
        'ord_pev' => 'integer',
    ];

    public function notaTrasladoRegistros(): HasMany
    {
        return $this->hasMany(NotaTraslado::class, 'cod_pev', 'cod_pev');
    }

    public function configuracionCalendarioGestionRegistros(): HasMany
    {
        return $this->hasMany(ConfiguracionCalendarioGestion::class, 'cod_pev', 'cod_pev');
    }

    public function tareaRegistros(): HasMany
    {
        return $this->hasMany(Tarea::class, 'cod_pev', 'cod_pev');
    }

    public function cuestionarioRegistros(): HasMany
    {
        return $this->hasMany(Cuestionario::class, 'cod_pev', 'cod_pev');
    }

    public function calificacionRegistros(): HasMany
    {
        return $this->hasMany(\App\Models\Oficial\Academico\Calificacion::class, 'cod_pev', 'cod_pev');
    }
}
