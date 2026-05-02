<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EspecialidadTecnica extends Model
{
    protected $table = 'especialidad_tecnica';
    protected $primaryKey = 'cod_esp';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'cod_esp',
        'nom_esp',
        'des_esp',
        'est_esp',
    ];

    protected static function booted(): void
    {
        static::creating(function ($especialidad) {
            if (!$especialidad->cod_esp) {
                $ultimo = self::where('cod_esp', 'like', 'ESP_%')
                    ->orderByDesc('cod_esp')
                    ->value('cod_esp');

                $numero = $ultimo
                    ? ((int) str_replace('ESP_', '', $ultimo)) + 1
                    : 1;

                $especialidad->cod_esp = 'ESP_' . str_pad($numero, 4, '0', STR_PAD_LEFT);
            }
        });
    }

    public function estudiantes()
    {
        return $this->hasMany(Estudiante::class, 'cod_esp', 'cod_esp');
    }

    public function planesEspecialidad()
    {
        return $this->hasMany(PlanEspecialidad::class, 'cod_esp', 'cod_esp');
    }
}