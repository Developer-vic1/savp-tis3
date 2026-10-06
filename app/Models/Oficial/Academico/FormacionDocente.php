<?php

declare(strict_types=1);

namespace App\Models\Oficial\Academico;

use App\Support\Modelos\CodigoInstitucional;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/** Entidad del contrato oficial; conserva las claves de sus madres. */
class FormacionDocente extends Model
{
    use CodigoInstitucional;

    protected $table = 'formacion_docente';

    protected $primaryKey = 'cod_fdo';

    public $incrementing = false;

    protected $keyType = 'string';

    protected $fillable = [
        'cod_doc',
        'niv_fdo',
        'tit_fdo',
        'ins_fdo',
        'pai_fdo',
        'fec_fdo',
        'cod_dpe',
        'est_fdo',
        'obs_fdo',
    ];

    protected $casts = [
        'fec_fdo' => 'date',
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
