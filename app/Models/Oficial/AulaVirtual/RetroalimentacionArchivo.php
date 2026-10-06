<?php

declare(strict_types=1);

namespace App\Models\Oficial\AulaVirtual;

use App\Support\Modelos\CodigoInstitucional;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/** Entidad del contrato oficial; conserva las claves de sus madres. */
class RetroalimentacionArchivo extends Model
{
    use CodigoInstitucional;

    protected $table = 'retroalimentacion_archivo';

    protected $primaryKey = 'cod_raf';

    public $incrementing = false;

    protected $keyType = 'string';

    protected $fillable = [
        'cod_cal_tar',
        'nom_raf',
        'rut_raf',
        'mim_raf',
        'tam_raf',
        'has_raf',
        'est_raf',
    ];

    protected $casts = [
        'tam_raf' => 'integer',
    ];

    public function calificacionTarea(): BelongsTo
    {
        return $this->belongsTo(CalificacionTarea::class, 'cod_cal_tar', 'cod_cal_tar');
    }
}
