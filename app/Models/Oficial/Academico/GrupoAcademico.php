<?php

declare(strict_types=1);

namespace App\Models\Oficial\Academico;

use App\Support\Modelos\CodigoInstitucional;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/** Entidad del contrato oficial; conserva las claves de sus madres. */
class GrupoAcademico extends Model
{
    use CodigoInstitucional;

    protected $table = 'grupo_academico';

    protected $primaryKey = 'cod_gac';

    public $incrementing = false;

    protected $keyType = 'string';

    protected $fillable = [
        'cod_gea',
        'cod_cur',
        'cod_par',
        'cod_tur',
        'cap_gac',
        'est_gac',
        'obs_gac',
    ];

    protected $casts = [
        'cap_gac' => 'integer',
    ];

    public function gestionAcademica(): BelongsTo
    {
        return $this->belongsTo(GestionAcademica::class, 'cod_gea', 'cod_gea');
    }

    public function curso(): BelongsTo
    {
        return $this->belongsTo(Curso::class, 'cod_cur', 'cod_cur');
    }

    public function paralelo(): BelongsTo
    {
        return $this->belongsTo(Paralelo::class, 'cod_par', 'cod_par');
    }

    public function turno(): BelongsTo
    {
        return $this->belongsTo(Turno::class, 'cod_tur', 'cod_tur');
    }

    public function planAsignaturaRegistros(): HasMany
    {
        return $this->hasMany(PlanAsignatura::class, 'cod_gac', 'cod_gac');
    }

    public function planEspecialidadRegistros(): HasMany
    {
        return $this->hasMany(PlanEspecialidad::class, 'cod_gac', 'cod_gac');
    }

    public function horarioRegistros(): HasMany
    {
        return $this->hasMany(Horario::class, 'cod_gac', 'cod_gac');
    }

    public function inscripcionVigenciaRegistros(): HasMany
    {
        return $this->hasMany(InscripcionVigencia::class, 'cod_gac', 'cod_gac');
    }
}
