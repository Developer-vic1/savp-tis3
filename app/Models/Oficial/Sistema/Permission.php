<?php

declare(strict_types=1);

namespace App\Models\Oficial\Sistema;

/** Tabla técnica existente: respeta sus claves y generadores nativos. */
class Permission extends \Spatie\Permission\Models\Permission
{
    protected $table = 'permissions';

    protected $primaryKey = 'id';

    public $incrementing = true;

    protected $keyType = 'int';

    public $timestamps = true;

    protected $fillable = [
        'id',
        'name',
        'guard_name',
        'created_at',
        'updated_at',
    ];

    protected $casts = [
        'id' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];
}
