<?php

declare(strict_types=1);

namespace App\Models\Oficial\Sistema;

use Illuminate\Database\Eloquent\Model;

/** Tabla técnica existente: respeta sus claves y generadores nativos. */
class PasswordResetToken extends Model
{
    protected $table = 'password_reset_tokens';

    protected $primaryKey = 'email';

    public $incrementing = false;

    protected $keyType = 'string';

    public $timestamps = false;

    protected $fillable = [
        'email',
        'token',
        'created_at',
    ];

    protected $casts = [
        'created_at' => 'datetime',
    ];

    protected $hidden = [
        'token',
    ];
}
