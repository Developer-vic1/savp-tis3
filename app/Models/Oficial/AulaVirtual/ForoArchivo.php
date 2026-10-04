<?php

declare(strict_types=1);

namespace App\Models\Oficial\AulaVirtual;

use App\Models\Soporte\CodigoInstitucional;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/** Entidad del contrato oficial; conserva las claves de sus madres. */
class ForoArchivo extends Model
{
    use CodigoInstitucional;

    protected $table = 'foro_archivo';

    protected $primaryKey = 'cod_far';

    public $incrementing = false;

    protected $keyType = 'string';

    protected $fillable = [
        'cod_fme',
        'nom_far',
        'rut_far',
        'mim_far',
        'tam_far',
        'has_far',
        'est_far',
    ];

    protected $casts = [
        'tam_far' => 'integer',
    ];

    public function foroMensaje(): BelongsTo
    {
        return $this->belongsTo(ForoMensaje::class, 'cod_fme', 'cod_fme');
    }
}
