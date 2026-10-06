<?php

declare(strict_types=1);

namespace App\Models\Oficial\Sistema;

/** Tabla técnica existente: respeta sus claves y generadores nativos. */
class PersonalAccessToken extends \Laravel\Sanctum\PersonalAccessToken
{
    protected $table = 'personal_access_tokens';

    protected $primaryKey = 'id';

    public $incrementing = true;

    protected $keyType = 'int';

    public $timestamps = true;

    protected $fillable = [
        'id',
        'tokenable_type',
        'tokenable_id',
        'name',
        'token',
        'abilities',
        'last_used_at',
        'expires_at',
        'created_at',
        'updated_at',
    ];

    protected $casts = [
        'id' => 'integer',
        'tokenable_id' => 'string',
        'last_used_at' => 'datetime',
        'expires_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'abilities' => 'json',
    ];

    protected $hidden = [
        'token',
    ];
}
