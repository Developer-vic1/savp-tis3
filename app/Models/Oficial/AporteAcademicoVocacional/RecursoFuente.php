<?php

declare(strict_types=1);

namespace App\Models\Oficial\AporteAcademicoVocacional;

use App\Models\Soporte\CodigoInstitucional;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/** Entidad del contrato oficial; conserva las claves de sus madres. */
class RecursoFuente extends Model
{
    use CodigoInstitucional;

    protected $table = 'recurso_fuente';

    protected $primaryKey = 'cod_rfu';

    public $incrementing = false;

    protected $keyType = 'string';

    protected $fillable = [
        'cod_uni',
        'cod_sed',
        'tip_rfu',
        'tit_rfu',
        'url_rfu',
        'dom_rfu',
        'con_rfu',
        'est_rfu',
    ];

    protected $casts = [
    ];

    public function universidad(): BelongsTo
    {
        return $this->belongsTo(Universidad::class, 'cod_uni', 'cod_uni');
    }

    public function sedeUniversidad(): BelongsTo
    {
        return $this->belongsTo(SedeUniversidad::class, 'cod_sed', 'cod_sed');
    }

    public function capturaFuenteRegistros(): HasMany
    {
        return $this->hasMany(CapturaFuente::class, 'cod_rfu', 'cod_rfu');
    }
}
