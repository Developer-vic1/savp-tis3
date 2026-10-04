<?php

declare(strict_types=1);

namespace App\Models\Oficial\Academico;

use App\Models\Oficial\AulaVirtual\ClaseVirtual;
use App\Models\Soporte\CodigoInstitucional;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/** Modelo del contrato canónico; atributos históricos redundantes permanecen en Legado. */
class PlanEspecialidad extends Model
{
    use CodigoInstitucional;

    protected $table = 'plan_especialidad';

    protected $primaryKey = 'cod_pes';

    public $incrementing = false;

    protected $keyType = 'string';

    protected $fillable = [
        'cod_esp',
        'cod_doc',
        'cod_gac',
        'hor_pes',
        'fii_pes',
        'ffi_pes',
        'est_pes',
    ];

    protected $casts = [
        'hor_pes' => 'decimal:2',
        'fii_pes' => 'date',
        'ffi_pes' => 'date',
    ];

    public function especialidadTecnica(): BelongsTo
    {
        return $this->belongsTo(EspecialidadTecnica::class, 'cod_esp', 'cod_esp');
    }

    public function docente(): BelongsTo
    {
        return $this->belongsTo(Docente::class, 'cod_doc', 'cod_doc');
    }

    public function grupoAcademico(): BelongsTo
    {
        return $this->belongsTo(GrupoAcademico::class, 'cod_gac', 'cod_gac');
    }

    public function horarioDetalleRegistros(): HasMany
    {
        return $this->hasMany(HorarioDetalle::class, 'cod_pes', 'cod_pes');
    }

    public function claseVirtualRegistros(): HasMany
    {
        return $this->hasMany(ClaseVirtual::class, 'cod_pes', 'cod_pes');
    }

    public function calificacionRegistros(): HasMany
    {
        return $this->hasMany(Calificacion::class, 'cod_pes', 'cod_pes');
    }
}
