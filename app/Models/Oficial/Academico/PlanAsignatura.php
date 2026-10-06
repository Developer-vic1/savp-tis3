<?php

declare(strict_types=1);

namespace App\Models\Oficial\Academico;

use App\Models\Oficial\AulaVirtual\ClaseVirtual;
use App\Support\Modelos\CodigoInstitucional;
use App\Support\Modelos\ContextoGrupoAcademico;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/** Modelo único de la tabla oficial; atributos y relaciones del contrato canónico. */
class PlanAsignatura extends Model
{
    use CodigoInstitucional;
    use ContextoGrupoAcademico;

    protected $table = 'plan_asignatura';

    protected $primaryKey = 'cod_pas';

    public $incrementing = false;

    protected $keyType = 'string';

    protected $fillable = [
        'cod_asi',
        'cod_doc',
        'cod_gac',
        'hor_pas',
        'fii_pas',
        'ffi_pas',
        'est_pas',
    ];

    protected $casts = [
        'hor_pas' => 'decimal:2',
        'fii_pas' => 'date',
        'ffi_pas' => 'date',
    ];

    public function asignatura(): BelongsTo
    {
        return $this->belongsTo(Asignatura::class, 'cod_asi', 'cod_asi');
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
        return $this->hasMany(HorarioDetalle::class, 'cod_pas', 'cod_pas');
    }

    public function claseVirtualRegistros(): HasMany
    {
        return $this->hasMany(ClaseVirtual::class, 'cod_pas', 'cod_pas');
    }

    public function calificacionRegistros(): HasMany
    {
        return $this->hasMany(Calificacion::class, 'cod_pas', 'cod_pas');
    }
}
