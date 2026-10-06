<?php

declare(strict_types=1);

namespace App\Models\Oficial\AporteAcademicoVocacional;

use App\Support\Modelos\CodigoInstitucional;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/** Entidad del contrato oficial; conserva las claves de sus madres. */
class OrientacionOfertaSugerida extends Model
{
    use CodigoInstitucional;

    protected $table = 'orientacion_ofertas_sugeridas';

    protected $primaryKey = 'cod_oos';

    public $incrementing = false;

    protected $keyType = 'string';

    protected $fillable = [
        'sug_oos',
        'cod_vof',
        'com_oos',
        'raz_oos',
        'ord_oos',
    ];

    protected $casts = [
        'sug_oos' => 'integer',
        'com_oos' => 'decimal:2',
        'ord_oos' => 'integer',
    ];

    public function orientacionCarreraSugerida(): BelongsTo
    {
        return $this->belongsTo(OrientacionCarreraSugerida::class, 'sug_oos', 'id');
    }

    public function versionOfertaAcademica(): BelongsTo
    {
        return $this->belongsTo(VersionOfertaAcademica::class, 'cod_vof', 'cod_vof');
    }
}
