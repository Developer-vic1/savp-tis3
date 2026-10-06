<?php

declare(strict_types=1);

namespace App\Models\Oficial\Sistema;

use Illuminate\Database\Eloquent\Model;

/** Tabla técnica existente: respeta sus claves y generadores nativos. */
class CacheLock extends Model
{
    protected $table = 'cache_locks';

    protected $primaryKey = 'key';

    public $incrementing = false;

    protected $keyType = 'string';

    public $timestamps = false;

    protected $fillable = [
        'key',
        'owner',
        'expiration',
    ];

    protected $casts = [
        'expiration' => 'integer',
    ];
}
