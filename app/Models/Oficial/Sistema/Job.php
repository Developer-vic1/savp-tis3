<?php

declare(strict_types=1);

namespace App\Models\Oficial\Sistema;

use Illuminate\Database\Eloquent\Model;

/** Tabla técnica existente: respeta sus claves y generadores nativos. */
class Job extends Model
{
    protected $table = 'jobs';

    protected $primaryKey = 'id';

    public $incrementing = true;

    protected $keyType = 'int';

    public $timestamps = false;

    protected $fillable = [
        'id',
        'queue',
        'payload',
        'attempts',
        'reserved_at',
        'available_at',
        'created_at',
    ];

    protected $casts = [
        'id' => 'integer',
        'attempts' => 'integer',
        'reserved_at' => 'integer',
        'available_at' => 'integer',
        'created_at' => 'integer',
    ];
}
