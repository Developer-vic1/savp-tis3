<?php

declare(strict_types=1);

namespace App\Models\Oficial\Sistema;

use App\Support\Modelos\ClaveCompuesta;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ConcesionAccesoUsuario extends Model
{
    use ClaveCompuesta;

    protected array $columnasClave = ['cod_cac', 'cod_usu'];
    protected $table = 'concesion_acceso_usuario';
    protected $primaryKey = null;
    public $incrementing = false;
    public $timestamps = false;
    protected $keyType = 'string';
    protected $fillable = ['cod_cac', 'cod_usu'];

    public function concesion(): BelongsTo
    {
        return $this->belongsTo(ConcesionAcceso::class, 'cod_cac', 'cod_cac');
    }

    public function usuario(): BelongsTo
    {
        return $this->belongsTo(User::class, 'cod_usu', 'cod_usu');
    }
}
