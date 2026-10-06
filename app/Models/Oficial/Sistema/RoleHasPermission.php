<?php

declare(strict_types=1);

namespace App\Models\Oficial\Sistema;

use App\Support\Modelos\ClaveCompuesta;
use Illuminate\Database\Eloquent\Model;

/** Tabla técnica existente: respeta sus claves y generadores nativos. */
class RoleHasPermission extends Model
{
    use ClaveCompuesta;

    protected array $columnasClave = [
        'permission_id',
        'role_id',
    ];

    protected $table = 'role_has_permissions';

    protected $primaryKey = null;

    public $incrementing = false;

    protected $keyType = 'string';

    public $timestamps = false;

    protected $fillable = [
        'permission_id',
        'role_id',
    ];

    protected $casts = [
        'permission_id' => 'integer',
        'role_id' => 'integer',
    ];
}
