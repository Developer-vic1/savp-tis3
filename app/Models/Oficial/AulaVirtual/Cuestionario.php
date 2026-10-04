<?php

declare(strict_types=1);

namespace App\Models\Oficial\AulaVirtual;

use App\Models\Oficial\Academico\Docente;
use App\Models\Oficial\Academico\PeriodoEvaluacion;
use App\Models\Soporte\CodigoInstitucional;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/** Entidad del contrato oficial; conserva las claves de sus madres. */
class Cuestionario extends Model
{
    use CodigoInstitucional;

    protected $table = 'cuestionario';

    protected $primaryKey = 'cod_cue';

    public $incrementing = false;

    protected $keyType = 'string';

    protected $fillable = [
        'cod_cla',
        'cod_sec',
        'cod_doc',
        'cod_pev',
        'tit_cue',
        'des_cue',
        'pub_cue',
        'ape_cue',
        'cie_cue',
        'dur_cue',
        'int_cue',
        'met_cue',
        'mez_cue',
        'mos_cue',
        'max_cue',
        'est_cue',
    ];

    protected $casts = [
        'pub_cue' => 'datetime',
        'ape_cue' => 'datetime',
        'cie_cue' => 'datetime',
        'dur_cue' => 'integer',
        'int_cue' => 'integer',
        'mez_cue' => 'boolean',
        'mos_cue' => 'boolean',
        'max_cue' => 'decimal:2',
    ];

    public function claseVirtual(): BelongsTo
    {
        return $this->belongsTo(ClaseVirtual::class, 'cod_cla', 'cod_cla');
    }

    public function docente(): BelongsTo
    {
        return $this->belongsTo(Docente::class, 'cod_doc', 'cod_doc');
    }

    public function periodoEvaluacion(): BelongsTo
    {
        return $this->belongsTo(PeriodoEvaluacion::class, 'cod_pev', 'cod_pev');
    }

    public function preguntaCuestionarioRegistros(): HasMany
    {
        return $this->hasMany(PreguntaCuestionario::class, 'cod_cue', 'cod_cue');
    }

    public function intentoCuestionarioRegistros(): HasMany
    {
        return $this->hasMany(IntentoCuestionario::class, 'cod_cue', 'cod_cue');
    }

    public function registroActividadClaseRegistros(): HasMany
    {
        return $this->hasMany(RegistroActividadClase::class, 'cue_rac', 'cod_cue');
    }

    /** La FK compuesta en PostgreSQL garantiza también el contexto de clase. */
    public function seccionClase(): BelongsTo
    {
        return $this->belongsTo(SeccionClase::class, 'cod_sec', 'cod_sec');
    }
}
