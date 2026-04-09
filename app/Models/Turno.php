<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Turno extends Model
{
    protected $table = 'turno';
    protected $primaryKey = 'cod_tur';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'cod_tur', // Código turno
        'nom_tur', // Nombre turno
        'hor_ini_tur', // Hora inicio turno
        'hor_fin_tur', // Hora fin turno
        'est_tur', // Estado turno
    ];

    protected static function booted(): void
    {
        static::creating(function ($turno) {

            if (!$turno->cod_tur) {

                $ultimo = self::where('cod_tur', 'like', 'TUR_%')
                    ->orderByDesc('cod_tur')
                    ->value('cod_tur');

                $numero = $ultimo
                    ? ((int) str_replace('TUR_', '', $ultimo)) + 1
                    : 1;

                $turno->cod_tur = 'TUR_' . str_pad($numero, 4, '0', STR_PAD_LEFT);
            }
        });
    }

    // 🔗 Relaciones

    public function planesAsignatura()
    {
        return $this->hasMany(PlanAsignatura::class, 'cod_tur', 'cod_tur');
    }
}
