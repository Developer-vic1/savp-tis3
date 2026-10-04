<?php

declare(strict_types=1);

namespace App\Models\Oficial\Academico;

use App\Models\Oficial\AulaVirtual\Cuestionario;
use App\Models\Oficial\AulaVirtual\Tarea;
use App\Models\Soporte\CodigoInstitucional;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/** Modelo del contrato canónico; atributos históricos redundantes permanecen en Legado. */
class PeriodoEvaluacion extends Model
{
    use CodigoInstitucional;

    protected $table = 'periodo_evaluacion';

    protected $primaryKey = 'cod_pev';

    public $incrementing = false;

    protected $keyType = 'string';

    protected $fillable = [
        'nom_pev',
        'ord_pev',
        'est_pev',
    ];

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
        return $this->hasMany(Calificacion::class, 'cod_pev', 'cod_pev');
    }
}
