<?php

declare(strict_types=1);

namespace App\Models\Oficial\Sistema;

use App\Support\Modelos\ClaveCompuesta;
use Illuminate\Database\Eloquent\Model;

/** Tabla técnica existente: respeta sus claves y generadores nativos. */
class ModelHasPermission extends Model
{
    use ClaveCompuesta;

    protected array $columnasClave = [
        'permission_id',
        'cod_usu',
        'model_type',
    ];

    protected $table = 'model_has_permissions';

    protected $primaryKey = null;

    public $incrementing = false;

    protected $keyType = 'string';

    public $timestamps = false;

    protected $fillable = [
        'permission_id',
        'model_type',
        'cod_usu',
    ];

    protected $casts = [
        'permission_id' => 'integer',
    ];
}
