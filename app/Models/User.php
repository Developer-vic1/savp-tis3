<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;

class User extends Authenticatable
{
    protected $table = 'users';
    protected $primaryKey = 'cod_usu';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'cod_usu',
        'cod_per',
        'email',
        'password',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected static function booted(): void
    {
        static::creating(function ($user) {
            if (!$user->cod_usu) {
                $ultimo = self::where('cod_usu', 'like', 'USU_%')
                    ->orderByDesc('cod_usu')
                    ->value('cod_usu');

                $numero = $ultimo
                    ? ((int) str_replace('USU_', '', $ultimo)) + 1
                    : 1;

                $user->cod_usu = 'USU_' . str_pad($numero, 4, '0', STR_PAD_LEFT);
            }
        });
    }
}
