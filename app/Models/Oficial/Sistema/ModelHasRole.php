<?php

declare(strict_types=1);

namespace App\Models\Oficial\Sistema;

use App\Support\Modelos\ClaveCompuesta;
use Illuminate\Database\Eloquent\Model;

/** Tabla técnica existente: respeta sus claves y generadores nativos. */
class ModelHasRole extends Model
{
    use ClaveCompuesta;

    protected array $columnasClave = [
        'role_id',
        'cod_usu',
        'model_type',
    ];

    protected $table = 'model_has_roles';

    protected $primaryKey = null;

    public $incrementing = false;

    protected $keyType = 'string';

    public $timestamps = false;

    protected $fillable = [
        'role_id',
        'model_type',
        'cod_usu',
    ];

    protected $casts = [
        'role_id' => 'integer',
    ];
}
