<?php

declare(strict_types=1);

namespace App\Models\Oficial\Sistema;

use App\Support\Modelos\ClaveCompuesta;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ConcesionAccesoPermiso extends Model
{
    use ClaveCompuesta;

    protected array $columnasClave = ['cod_cac', 'permission_id'];
    protected $table = 'concesion_acceso_permiso';
    protected $primaryKey = null;
    public $incrementing = false;
    public $timestamps = false;
    protected $keyType = 'string';
    protected $fillable = ['cod_cac', 'permission_id'];
    protected $casts = ['permission_id' => 'integer'];

    public function concesion(): BelongsTo
    {
        return $this->belongsTo(ConcesionAcceso::class, 'cod_cac', 'cod_cac');
    }

    public function permiso(): BelongsTo
    {
        return $this->belongsTo(Permission::class, 'permission_id', 'id');
    }
}
