<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\User;

class Bitacora extends Model
{
    protected $table = 'bitacora';
    protected $primaryKey = 'cod_bit';

    public $incrementing = false;
    protected $keyType = 'string';

    public $timestamps = false;

    protected $fillable = [
        'cod_bit',

        'cod_usu',
        'rol_bit',

        'acc_bit',
        'mod_bit',
        'tab_bit',
        'reg_bit',
        'nom_reg_bit',
        'des_bit',

        'niv_bit',
        'res_bit',

        'ip_bit',
        'age_bit',
        'rut_bit',
        'met_bit',

        'val_ant_bit',
        'val_nue_bit',

        'err_bit',
        'fec_bit',
    ];

    protected function casts(): array
    {
        return [
            'fec_bit' => 'datetime',
            'val_ant_bit' => 'array',
            'val_nue_bit' => 'array',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function ($bitacora) {
            if (! $bitacora->cod_bit) {
                $ultimo = self::where('cod_bit', 'like', 'BIT_%')
                    ->orderByDesc('cod_bit')
                    ->value('cod_bit');

                $numero = $ultimo
                    ? ((int) str_replace('BIT_', '', $ultimo)) + 1
                    : 1;

                $bitacora->cod_bit = 'BIT_' . str_pad($numero, 4, '0', STR_PAD_LEFT);
            }

            if (! $bitacora->fec_bit) {
                $bitacora->fec_bit = now();
            }
        });
    }

    public function usuario()
    {
        return $this->belongsTo(User::class, 'cod_usu', 'cod_usu');
    }
}
