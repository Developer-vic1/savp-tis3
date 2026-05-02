<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Curso extends Model
{
    protected $table = 'curso';
    protected $primaryKey = 'cod_cur';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'cod_cur', // Código curso
        'nom_cur', // Nombre curso
        'niv_cur', // Nivel curso
        'est_cur', // Estado curso
    ];

    protected static function booted(): void
    {
        static::creating(function ($curso) {

            if (!$curso->cod_cur) {

                $ultimo = self::where('cod_cur', 'like', 'CUR_%')
                    ->orderByDesc('cod_cur')
                    ->value('cod_cur');

                $numero = $ultimo
                    ? ((int) str_replace('CUR_', '', $ultimo)) + 1
                    : 1;

                $curso->cod_cur = 'CUR_' . str_pad($numero, 4, '0', STR_PAD_LEFT);
            }
        });
    }

    // 🔗 Relaciones

    public function inscripciones()
    {
        return $this->hasMany(InscripcionEstudiante::class, 'cod_cur', 'cod_cur');
    }

    public function planesAsignatura()
    {
        return $this->hasMany(PlanAsignatura::class, 'cod_cur', 'cod_cur');
    }

    public function planesEspecialidad()
    {
        return $this->hasMany(PlanEspecialidad::class, 'cod_cur', 'cod_cur');
    }
}
