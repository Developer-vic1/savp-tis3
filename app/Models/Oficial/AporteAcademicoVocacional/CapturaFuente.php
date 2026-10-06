<?php

declare(strict_types=1);

namespace App\Models\Oficial\AporteAcademicoVocacional;

use App\Support\Modelos\CodigoInstitucional;
use App\Models\Oficial\Sistema\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/** Entidad del contrato oficial; conserva las claves de sus madres. */
class CapturaFuente extends Model
{
    use CodigoInstitucional;

    protected $table = 'captura_fuente';

    protected $primaryKey = 'cod_cfu';

    public $incrementing = false;

    protected $keyType = 'string';

    public const UPDATED_AT = null;

    protected $fillable = [
        'cod_rfu',
        'fec_cfu',
        'url_cfu',
        'htt_cfu',
        'mim_cfu',
        'eta_cfu',
        'mod_cfu',
        'cab_cfu',
        'tam_cfu',
        'has_cfu',
        'rut_cfu',
        'tex_cfu',
        'ori_cfu',
        'val_cfu',
        'est_cfu',
        'err_cfu',
    ];

    protected $casts = [
        'fec_cfu' => 'datetime',
        'htt_cfu' => 'integer',
        'cab_cfu' => 'array',
        'tam_cfu' => 'integer',
    ];

    public function recursoFuente(): BelongsTo
    {
        return $this->belongsTo(RecursoFuente::class, 'cod_rfu', 'cod_rfu');
    }

    public function users(): BelongsTo
    {
        return $this->belongsTo(User::class, 'val_cfu', 'cod_usu');
    }

    public function evidenciaAcademicaRegistros(): HasMany
    {
        return $this->hasMany(EvidenciaAcademica::class, 'cod_cfu', 'cod_cfu');
    }
}
