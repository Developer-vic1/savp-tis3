<?php

namespace App\Models\Oficial\Academico;

use App\Support\Modelos\CodigoInstitucional;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;

class Paralelo extends Model
{
    use CodigoInstitucional;

    protected $table = 'paralelo';

    protected $primaryKey = 'cod_par';

    public $incrementing = false;

    protected $keyType = 'string';

    protected $fillable = [
        'cod_par',
        'nom_par',
        'est_par',
    ];

    public function inscripciones()
    {
        return $this->hasMany(InscripcionEstudiante::class, 'cod_par', 'cod_par');
    }

    public function planesAsignatura(): HasManyThrough
    {
        return $this->hasManyThrough(PlanAsignatura::class, GrupoAcademico::class, 'cod_par', 'cod_gac', 'cod_par', 'cod_gac');
    }

    public function planesEspecialidad(): HasManyThrough
    {
        return $this->hasManyThrough(PlanEspecialidad::class, GrupoAcademico::class, 'cod_par', 'cod_gac', 'cod_par', 'cod_gac');
    }

    public function grupoAcademicoRegistros(): HasMany
    {
        return $this->hasMany(GrupoAcademico::class, 'cod_par', 'cod_par');
    }

    public function calendarioEventoRegistros(): HasMany
    {
        return $this->hasMany(CalendarioEvento::class, 'cod_par', 'cod_par');
    }
}
