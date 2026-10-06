<?php

declare(strict_types=1);

namespace App\Models\Oficial\Academico;

use App\Support\Modelos\CodigoInstitucional;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/** Entidad del contrato oficial; conserva las claves de sus madres. */
class CapacitacionDocente extends Model
{
    use CodigoInstitucional;

    protected $table = 'capacitacion_docente';

    protected $primaryKey = 'cod_cdo';

    public $incrementing = false;

    protected $keyType = 'string';

    protected $fillable = [
        'cod_doc',
        'nom_cdo',
        'ins_cdo',
        'hor_cdo',
        'fec_cdo',
        'cod_dpe',
        'est_cdo',
        'obs_cdo',
    ];

    protected $casts = [
        'hor_cdo' => 'decimal:2',
        'fec_cdo' => 'date',
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
