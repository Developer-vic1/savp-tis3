<?php

declare(strict_types=1);

namespace App\Models\Oficial\Academico;

use App\Support\Modelos\CodigoInstitucional;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/** Entidad del contrato oficial; conserva las claves de sus madres. */
class ExperienciaDocente extends Model
{
    use CodigoInstitucional;

    protected $table = 'experiencia_docente';

    protected $primaryKey = 'cod_edo';

    public $incrementing = false;

    protected $keyType = 'string';

    protected $fillable = [
        'cod_doc',
        'ins_edo',
        'car_edo',
        'fii_edo',
        'ffi_edo',
        'cod_dpe',
        'est_edo',
        'obs_edo',
    ];

    protected $casts = [
        'fii_edo' => 'date',
        'ffi_edo' => 'date',
    ];

    public function docente(): BelongsTo
    {
        return $this->belongsTo(Docente::class, 'cod_doc', 'cod_doc');
    }

    public function documentoPersonal(): BelongsTo
    {
        return $this->belongsTo(DocumentoPersonal::class, 'cod_dpe', 'cod_dpe');
    }
}
