<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Asignatura extends Model
{
    protected $table = 'asignatura';
    protected $primaryKey = 'cod_asi';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'cod_asi', // Código asignatura
        'nom_asi', // Nombre asignatura
        'sig_asi', // Sigla asignatura
        'hor_asi', // Horas académicas
        'est_asi', // Estado asignatura
    ];

    protected static function booted(): void
    {
        static::creating(function ($asignatura) {

            if (!$asignatura->cod_asi) {

                $ultimo = self::where('cod_asi', 'like', 'ASI_%')
                    ->orderByDesc('cod_asi')
                    ->value('cod_asi');

                $numero = $ultimo
                    ? ((int) str_replace('ASI_', '', $ultimo)) + 1
                    : 1;

                $asignatura->cod_asi = 'ASI_' . str_pad($numero, 4, '0', STR_PAD_LEFT);
            }
        });
    }

    // 🔗 Relaciones

    public function planAsignatura()
    {
        return $this->hasMany(PlanAsignatura::class, 'cod_asi', 'cod_asi');
    }

    public function calificaciones()
    {
        return $this->hasMany(Calificacion::class, 'cod_asi', 'cod_asi');
    }
}