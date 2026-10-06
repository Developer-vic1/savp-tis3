<?php

declare(strict_types=1);

namespace App\Models\Oficial\Sistema;

use Illuminate\Database\Eloquent\Model;

/** Tabla técnica existente: respeta sus claves y generadores nativos. */
class Migration extends Model
{
    protected $table = 'migrations';

    protected $primaryKey = 'id';

    public $incrementing = true;

    protected $keyType = 'int';

    public $timestamps = false;

    protected $fillable = [
        'id',
        'migration',
        'batch',
    ];

    protected $casts = [
        'id' => 'integer',
        'batch' => 'integer',
    ];
}
