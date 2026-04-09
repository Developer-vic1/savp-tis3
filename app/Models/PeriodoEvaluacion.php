<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

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

            if (!$periodo->cod_pev) {

                $ultimo = self::where('cod_pev', 'like', 'PEV_%')
                    ->orderByDesc('cod_pev')
                    ->value('cod_pev');

                $numero = $ultimo
                    ? ((int) str_replace('PEV_', '', $ultimo)) + 1
                    : 1;

                $periodo->cod_pev = 'PEV_' . str_pad($numero, 4, '0', STR_PAD_LEFT);
            }
        });
    }

    // 🔗 Relaciones

    public function inscripciones()
    {
        return $this->hasMany(InscripcionEstudiante::class, 'cod_pev', 'cod_pev');
    }
}
