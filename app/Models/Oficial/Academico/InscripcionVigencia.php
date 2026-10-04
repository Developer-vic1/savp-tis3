<?php

declare(strict_types=1);

namespace App\Models\Oficial\Academico;

use App\Models\Soporte\CodigoInstitucional;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/** Modelo del contrato canónico; atributos históricos redundantes permanecen en Legado. */
class InscripcionVigencia extends Model
{
    use CodigoInstitucional;

    protected $table = 'inscripcion_vigencia';

    protected $primaryKey = 'cod_ivg';

    public $incrementing = false;

    protected $keyType = 'string';

    protected $fillable = [
        'cod_ins',
        'cod_gac',
        'cod_esp_tec',
        'fii_ivg',
        'ffi_ivg',
        'tip_ivg',
        'cie_ivg',
        'mot_ivg',
        'obs_ivg',
        'est_ivg',
    ];

    protected $casts = [
        'fii_ivg' => 'date',
        'ffi_ivg' => 'date',
    ];

    public function inscripcionEstudiante(): BelongsTo
    {
        return $this->belongsTo(InscripcionEstudiante::class, 'cod_ins', 'cod_ins');
    }

    public function grupoAcademico(): BelongsTo
    {
        return $this->belongsTo(GrupoAcademico::class, 'cod_gac', 'cod_gac');
    }

    public function especialidadTecnica(): BelongsTo
    {
        return $this->belongsTo(EspecialidadTecnica::class, 'cod_esp_tec', 'cod_esp');
    }
}
