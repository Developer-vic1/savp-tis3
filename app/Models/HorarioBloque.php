<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class HorarioBloque extends Model
{
    protected $table = 'horario_bloque';
    protected $primaryKey = 'cod_hbl';

    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'cod_hbl',
        'cod_tur',
        'num_hbl',
        'hor_ini_hbl',
        'hor_fin_hbl',
        'nom_hbl',
        'obs_hbl',
        'est_hbl',
    ];

    protected $casts = [
        'num_hbl' => 'integer',
        'hor_ini_hbl' => 'datetime:H:i',
        'hor_fin_hbl' => 'datetime:H:i',
    ];

    public function turno()
    {
        return $this->belongsTo(Turno::class, 'cod_tur', 'cod_tur');
    }

    public function detalles()
    {
        return $this->hasMany(HorarioDetalle::class, 'cod_hbl', 'cod_hbl');
    }

    protected static function booted(): void
    {
        static::creating(function ($bloque) {
            if (! $bloque->cod_hbl) {
                $ultimo = self::where('cod_hbl', 'like', 'HBL_%')
                    ->orderByDesc('cod_hbl')
                    ->value('cod_hbl');

                $numero = $ultimo
                    ? ((int) str_replace('HBL_', '', $ultimo)) + 1
                    : 1;

                $bloque->cod_hbl = 'HBL_' . str_pad((string) $numero, 4, '0', STR_PAD_LEFT);
            }

            if (! $bloque->est_hbl) {
                $bloque->est_hbl = 'ACTIVO';
            }
        });
    }
}
