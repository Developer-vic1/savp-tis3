<?php

declare(strict_types=1);

namespace App\Models\Oficial\Academico;

use App\Support\Modelos\CodigoInstitucional;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

/** Modelo único de la tabla oficial; atributos y relaciones del contrato canónico. */
class SesionAcademica extends Model
{
    use CodigoInstitucional;

    protected $table = 'sesion_academica';

    protected $primaryKey = 'cod_ses';

    public $incrementing = false;

    protected $keyType = 'string';

    protected $fillable = [
        'cod_hde',
        'fec_ses',
        'hor_pla_ses',
        'hor_rea_ses',
        'est_ses',
        'cod_cae',
        'cod_ses_ori',
        'obs_ses',
    ];

    protected $casts = [
        'fec_ses' => 'date',
        'hor_pla_ses' => 'decimal:2',
        'hor_rea_ses' => 'decimal:2',
    ];

    public function horarioDetalle(): BelongsTo
    {
        return $this->belongsTo(HorarioDetalle::class, 'cod_hde', 'cod_hde');
    }

    public function calendarioEvento(): BelongsTo
    {
        return $this->belongsTo(CalendarioEvento::class, 'cod_cae', 'cod_cae');
    }

    public function sesionAcademica(): BelongsTo
    {
        return $this->belongsTo(SesionAcademica::class, 'cod_ses_ori', 'cod_ses');
    }

    public function sesionAcademicaRegistros(): HasMany
    {
        return $this->hasMany(SesionAcademica::class, 'cod_ses_ori', 'cod_ses');
    }

    public function asistenciaClaseRegistros(): HasOne
    {
        return $this->hasOne(AsistenciaClase::class, 'cod_ses', 'cod_ses');
    }
}
