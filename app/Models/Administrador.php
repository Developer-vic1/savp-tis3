<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Administrador extends Model
{
    protected $table = 'administrador';
    protected $primaryKey = 'cod_adm';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'cod_adm', // Código administrador
        'cod_pin', // Código personal institucional
        'est_adm', // Estado administrador
    ];

    protected static function booted(): void
    {
        static::creating(function ($administrador) {

            if (!$administrador->cod_adm) {

                $ultimo = self::where('cod_adm', 'like', 'ADM_%')
                    ->orderByDesc('cod_adm')
                    ->value('cod_adm');

                $numero = $ultimo
                    ? ((int) str_replace('ADM_', '', $ultimo)) + 1
                    : 1;

                $administrador->cod_adm = 'ADM_' . str_pad($numero, 4, '0', STR_PAD_LEFT);
            }
        });
    }

    // 🔗 Relaciones

    public function personalInstitucional()
    {
        return $this->belongsTo(PersonalInstitucional::class, 'cod_pin', 'cod_pin');
    }
}
