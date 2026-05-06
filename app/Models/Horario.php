<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Horario extends Model
{
    protected $table = 'horario';
    protected $primaryKey = 'cod_hor';

    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'cod_hor',
        'cod_gea',
        'cod_cur',
        'cod_par',
        'cod_tur',
        'nom_hor',
        'obs_hor',
        'est_hor',
    ];

    public function detalles()
    {
        return $this->hasMany(HorarioDetalle::class, 'cod_hor', 'cod_hor');
    }

    public function detallesActivos()
    {
        return $this->hasMany(HorarioDetalle::class, 'cod_hor', 'cod_hor')
            ->where('est_hde', 'ACTIVO');
    }

    public function gestion()
    {
        return $this->belongsTo(GestionAcademica::class, 'cod_gea', 'cod_gea');
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

    protected static function booted(): void
    {
        static::creating(function ($horario) {
            if (! $horario->cod_hor) {
                $ultimo = self::where('cod_hor', 'like', 'HOR_%')
                    ->orderByDesc('cod_hor')
                    ->value('cod_hor');

                $numero = $ultimo
                    ? ((int) str_replace('HOR_', '', $ultimo)) + 1
                    : 1;

                $horario->cod_hor = 'HOR_' . str_pad((string) $numero, 4, '0', STR_PAD_LEFT);
            }
        });
    }
}