<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Bitacora extends Model
{
    protected $table = 'bitacora';
    protected $primaryKey = 'cod_bit';
    public $incrementing = false;
    protected $keyType = 'string';

    public $timestamps = false;

    protected $fillable = [
        
        'cod_bit', // Código bitácora
        'acc_bit', // Acción realizada
        'tab_bit', // Tabla afectada
        'reg_bit', // Registro afectado
        'cod_usu', // Código usuario
        'fec_bit', // Fecha y hora
        'est_bit', // Estado registro
    ];

    protected static function booted(): void
    {
        static::creating(function ($bitacora) {

            if (!$bitacora->cod_bit) {

                $ultimo = self::where('cod_bit', 'like', 'BIT_%')
                    ->orderByDesc('cod_bit')
                    ->value('cod_bit');

                $numero = $ultimo
                    ? ((int) str_replace('BIT_', '', $ultimo)) + 1
                    : 1;

                $bitacora->cod_bit = 'BIT_' . str_pad($numero, 4, '0', STR_PAD_LEFT);
            }
        });
    }

    // 🔗 Relaciones

    public function usuario()
    {
        return $this->belongsTo(User::class, 'cod_usu', 'cod_usu');
    }
}