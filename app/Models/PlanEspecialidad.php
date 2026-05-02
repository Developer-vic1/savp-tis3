<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PlanEspecialidad extends Model
{
    protected $table = 'plan_especialidad';
    protected $primaryKey = 'cod_pes';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'cod_pes',
        'cod_esp',
        'cod_doc',
        'cod_cur',
        'cod_par',
        'cod_tur',
        'cod_gea',
        'hor_pes',
        'est_pes',
    ];

    protected static function booted(): void
    {
        static::creating(function ($plan) {

            if (!$plan->cod_pes) {

                $ultimo = self::where('cod_pes', 'like', 'PES_%')
                    ->orderByDesc('cod_pes')
                    ->value('cod_pes');

                $numero = $ultimo
                    ? ((int) str_replace('PES_', '', $ultimo)) + 1
                    : 1;

                $plan->cod_pes = 'PES_' . str_pad($numero, 4, '0', STR_PAD_LEFT);
            }
        });
    }

    public function especialidad()
    {
        return $this->belongsTo(EspecialidadTecnica::class, 'cod_esp', 'cod_esp');
    }

    public function docente()
    {
        return $this->belongsTo(Docente::class, 'cod_doc', 'cod_doc');
    }

    public function curso()
    {
        return $this->belongsTo(Curso::class, 'cod_cur', 'cod_cur');
    }

    public function paralelo()
    {
        return $this->belongsTo(Paralelo::class, 'cod_par', 'cod_par');
    }

    public function turno()
    {
        return $this->belongsTo(Turno::class, 'cod_tur', 'cod_tur');
    }

    public function gestionAcademica()
    {
        return $this->belongsTo(GestionAcademica::class, 'cod_gea', 'cod_gea');
    }
}