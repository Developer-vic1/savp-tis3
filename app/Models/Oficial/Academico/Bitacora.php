<?php

namespace App\Models\Oficial\Academico;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

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
        'usr_bit',
        'tra_bit',
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
                $bitacora->cod_bit = 'BIT_'.strtoupper(bin2hex(random_bytes(8)));
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

    protected $casts = [
        'val_ant_bit' => 'array',
        'val_nue_bit' => 'array',
        'tra_bit' => 'integer',
        'fec_bit' => 'datetime',
    ];

    public function users(): BelongsTo
    {
        return $this->belongsTo(User::class, 'cod_usu', 'cod_usu');
    }
}
