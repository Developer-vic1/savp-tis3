<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PlanAsignatura extends Model
{
    protected $table = 'plan_asignatura';
    protected $primaryKey = 'cod_pas';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'cod_pas', // Código plan asignatura
        'cod_asi', // Código asignatura
        'cod_doc', // Código docente
        'cod_cur', // Código curso
        'cod_par', // Código paralelo
        'cod_tur', // Código turno
        'cod_gea', // Código gestión académica
        'hor_pas', // Horas asignadas
        'est_pas', // Estado plan asignatura
    ];

    protected static function booted(): void
    {
        static::creating(function ($plan) {

            if (!$plan->cod_pas) {

                $ultimo = self::where('cod_pas', 'like', 'PAS_%')
                    ->orderByDesc('cod_pas')
                    ->value('cod_pas');

                $numero = $ultimo
                    ? ((int) str_replace('PAS_', '', $ultimo)) + 1
                    : 1;

                $plan->cod_pas = 'PAS_' . str_pad($numero, 4, '0', STR_PAD_LEFT);
            }
        });
    }

    // 🔗 Relaciones

    public function asignatura()
    {
        return $this->belongsTo(Asignatura::class, 'cod_asi', 'cod_asi');
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