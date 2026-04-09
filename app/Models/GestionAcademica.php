<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class GestionAcademica extends Model
{
    protected $table = 'gestion_academica';
    protected $primaryKey = 'cod_gea';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'cod_gea', // Código gestión académica
        'ani_gea', // Año gestión académica
        'fii_gea', // Fecha inicio gestión
        'ffi_gea', // Fecha fin gestión
        'est_gea', // Estado gestión académica
    ];

    protected static function booted(): void
    {
        static::creating(function ($gestion) {

            if (!$gestion->cod_gea) {

                $ultimo = self::where('cod_gea', 'like', 'GEA_%')
                    ->orderByDesc('cod_gea')
                    ->value('cod_gea');

                $numero = $ultimo
                    ? ((int) str_replace('GEA_', '', $ultimo)) + 1
                    : 1;

                $gestion->cod_gea = 'GEA_' . str_pad($numero, 4, '0', STR_PAD_LEFT);
            }
        });
    }

    // 🔗 Relaciones

    public function inscripciones()
    {
        return $this->hasMany(InscripcionEstudiante::class, 'cod_gea', 'cod_gea');
    }

    public function planesAsignatura()
    {
        return $this->hasMany(PlanAsignatura::class, 'cod_gea', 'cod_gea');
    }
}