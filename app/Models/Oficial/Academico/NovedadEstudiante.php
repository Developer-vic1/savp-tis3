<?php

declare(strict_types=1);

namespace App\Models\Oficial\Academico;

use App\Support\Modelos\CodigoInstitucional;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/** Entidad del contrato oficial; conserva las claves de sus madres. */
class NovedadEstudiante extends Model
{
    use CodigoInstitucional;

    protected $table = 'novedad_estudiante';

    protected $primaryKey = 'cod_nes';

    public $incrementing = false;

    protected $keyType = 'string';

    protected $fillable = [
        'cod_ins',
        'tip_nes',
        'fii_nes',
        'ffi_nes',
        'est_nes',
        'mot_nes',
        'obs_nes',
        'rut_res_nes',
    ];

    protected $casts = [
        'fii_nes' => 'date',
        'ffi_nes' => 'date',
    ];

    public function inscripcionEstudiante(): BelongsTo
    {
        return $this->belongsTo(InscripcionEstudiante::class, 'cod_ins', 'cod_ins');
    }

    public function asistenciaEstudianteRegistros(): HasMany
    {
        return $this->hasMany(AsistenciaEstudiante::class, 'cod_nes', 'cod_nes');
    }
}
