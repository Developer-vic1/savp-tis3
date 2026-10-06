<?php

namespace App\Models\Oficial\Academico;

use App\Support\Modelos\CodigoInstitucional;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;

class Curso extends Model
{
    use CodigoInstitucional;

    protected $table = 'curso';

    protected $primaryKey = 'cod_cur';

    public $incrementing = false;

    protected $keyType = 'string';

    protected $fillable = [
        'cod_cur', // Código curso
        'nom_cur', // Nombre curso
        'niv_cur', // Nivel curso
        'est_cur', // Estado curso
        'ord_cur',
    ];

    // 🔗 Relaciones

    public function inscripciones()
    {
        return $this->hasMany(InscripcionEstudiante::class, 'cod_cur', 'cod_cur');
    }

    public function planesAsignatura(): HasManyThrough
    {
        return $this->hasManyThrough(PlanAsignatura::class, GrupoAcademico::class, 'cod_cur', 'cod_gac', 'cod_cur', 'cod_gac');
    }

    public function planesEspecialidad(): HasManyThrough
    {
        return $this->hasManyThrough(PlanEspecialidad::class, GrupoAcademico::class, 'cod_cur', 'cod_gac', 'cod_cur', 'cod_gac');
    }

    protected $casts = [
        'ord_cur' => 'integer',
    ];

    public function notaTrasladoRegistros(): HasMany
    {
        return $this->hasMany(NotaTraslado::class, 'cod_cur', 'cod_cur');
    }

    public function grupoAcademicoRegistros(): HasMany
    {
        return $this->hasMany(GrupoAcademico::class, 'cod_cur', 'cod_cur');
    }

    public function calendarioEventoRegistros(): HasMany
    {
        return $this->hasMany(CalendarioEvento::class, 'cod_cur', 'cod_cur');
    }

    public function regenteAsignacionRegistros(): HasMany
    {
        return $this->hasMany(RegenteAsignacion::class, 'cod_cur', 'cod_cur');
    }
}
