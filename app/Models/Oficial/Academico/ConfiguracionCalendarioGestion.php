<?php

declare(strict_types=1);

namespace App\Models\Oficial\Academico;

use App\Models\Soporte\CodigoInstitucional;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/** Modelo del contrato canónico; atributos históricos redundantes permanecen en Legado. */
class ConfiguracionCalendarioGestion extends Model
{
    use CodigoInstitucional;

    protected $table = 'configuracion_calendario_gestion';

    protected $primaryKey = 'cod_ccg';

    public $incrementing = false;

    protected $keyType = 'string';

    protected $fillable = [
        'cod_gea',
        'cod_pev',
        'dias_req_ccg',
        'fii_tri_ccg',
        'ffi_tri_ccg',
        'cie_ccg',
        'est_ccg',
    ];

    protected $casts = [
        'dias_req_ccg' => 'integer',
        'fii_tri_ccg' => 'date',
        'ffi_tri_ccg' => 'date',
        'cie_ccg' => 'datetime',
    ];

    public function gestionAcademica(): BelongsTo
    {
        return $this->belongsTo(GestionAcademica::class, 'cod_gea', 'cod_gea');
    }

    public function periodoEvaluacion(): BelongsTo
    {
        return $this->belongsTo(PeriodoEvaluacion::class, 'cod_pev', 'cod_pev');
    }
}
