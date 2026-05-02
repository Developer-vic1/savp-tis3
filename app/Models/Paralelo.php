<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Paralelo extends Model
{
    protected $table = 'paralelo';
    protected $primaryKey = 'cod_par';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'cod_par',
        'nom_par',
        'est_par',
    ];

    protected static function booted(): void
    {
        static::creating(function ($paralelo) {
            if (! $paralelo->cod_par) {
                $ultimo = self::where('cod_par', 'like', 'PAR_%')
                    ->orderByDesc('cod_par')
                    ->value('cod_par');

                $numero = $ultimo
                    ? ((int) str_replace('PAR_', '', $ultimo)) + 1
                    : 1;

                $paralelo->cod_par = 'PAR_' . str_pad($numero, 4, '0', STR_PAD_LEFT);
            }
        });
    }

    public function inscripciones()
    {
        return $this->hasMany(InscripcionEstudiante::class, 'cod_par', 'cod_par');
    }

    public function planesAsignatura()
    {
        return $this->hasMany(PlanAsignatura::class, 'cod_par', 'cod_par');
    }

    public function planesEspecialidad()
    {
        return $this->hasMany(PlanEspecialidad::class, 'cod_par', 'cod_par');
    }
}
