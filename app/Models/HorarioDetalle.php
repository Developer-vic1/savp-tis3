<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class HorarioDetalle extends Model
{
    protected $table = 'horario_detalle';
    protected $primaryKey = 'cod_hde';

    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'cod_hde',
        'cod_hor',
        'cod_hbl',
        'dia_hde',
        'cod_pas',
        'cod_pes',
        'aul_hde',
        'obs_hde',
        'est_hde',
    ];

    public function horario()
    {
        return $this->belongsTo(Horario::class, 'cod_hor', 'cod_hor');
    }

    public function bloque()
    {
        return $this->belongsTo(HorarioBloque::class, 'cod_hbl', 'cod_hbl');
    }

    public function planAsignatura()
    {
        return $this->belongsTo(PlanAsignatura::class, 'cod_pas', 'cod_pas');
    }

    public function planEspecialidad()
    {
        return $this->belongsTo(PlanEspecialidad::class, 'cod_pes', 'cod_pes');
    }

    protected static function booted(): void
    {
        static::creating(function ($detalle) {
            if (! $detalle->cod_hde) {
                $ultimo = self::where('cod_hde', 'like', 'HDE_%')
                    ->orderByDesc('cod_hde')
                    ->value('cod_hde');

                $numero = $ultimo
                    ? ((int) str_replace('HDE_', '', $ultimo)) + 1
                    : 1;

                $detalle->cod_hde = 'HDE_' . str_pad((string) $numero, 4, '0', STR_PAD_LEFT);
            }

            if (! $detalle->est_hde) {
                $detalle->est_hde = 'ACTIVO';
            }
        });
    }
}
