<?php

namespace App\Models\Oficial\Academico;

use App\Models\Oficial\AulaVirtual\CalificacionTarea;
use App\Models\Oficial\AulaVirtual\Cuestionario;
use App\Models\Oficial\AulaVirtual\ForoClase;
use App\Models\Oficial\AulaVirtual\RespuestaCuestionario;
use App\Models\Oficial\AulaVirtual\Tarea;
use App\Support\Modelos\CodigoInstitucional;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Docente extends Model
{
    use CodigoInstitucional;

    protected $table = 'docente';

    protected $primaryKey = 'cod_doc';

    public $incrementing = false;

    protected $keyType = 'string';

    protected $fillable = [
        'cod_doc', // Código docente
        'cod_pin', // Código personal institucional
        'esp_doc', // Especialidad docente
        'est_doc', // Estado docente
        'num_mod_doc', // Numero de modificaciones
    ];

    // 🔗 Relaciones

    public function personalInstitucional()
    {
        return $this->belongsTo(PersonalInstitucional::class, 'cod_pin', 'cod_pin');
    }

    public function planAsignaturas()
    {
        return $this->hasMany(PlanAsignatura::class, 'cod_doc', 'cod_doc');
    }

    public function planEspecialidades()
    {
        return $this->hasMany(PlanEspecialidad::class, 'cod_doc', 'cod_doc');
    }

    public function formacionDocenteRegistros(): HasMany
    {
        return $this->hasMany(FormacionDocente::class, 'cod_doc', 'cod_doc');
    }

    public function experienciaDocenteRegistros(): HasMany
    {
        return $this->hasMany(ExperienciaDocente::class, 'cod_doc', 'cod_doc');
    }

    public function capacitacionDocenteRegistros(): HasMany
    {
        return $this->hasMany(CapacitacionDocente::class, 'cod_doc', 'cod_doc');
    }

    public function planAsignaturaRegistros(): HasMany
    {
        return $this->hasMany(PlanAsignatura::class, 'cod_doc', 'cod_doc');
    }

    public function planEspecialidadRegistros(): HasMany
    {
        return $this->hasMany(PlanEspecialidad::class, 'cod_doc', 'cod_doc');
    }

    public function tareaRegistros(): HasMany
    {
        return $this->hasMany(Tarea::class, 'cod_doc', 'cod_doc');
    }

    public function calificacionTareaRegistros(): HasMany
    {
        return $this->hasMany(CalificacionTarea::class, 'cod_doc', 'cod_doc');
    }

    public function cuestionarioRegistros(): HasMany
    {
        return $this->hasMany(Cuestionario::class, 'cod_doc', 'cod_doc');
    }

    public function respuestaCuestionarioRegistros(): HasMany
    {
        return $this->hasMany(RespuestaCuestionario::class, 'doc_rcu', 'cod_doc');
    }

    public function foroClaseRegistros(): HasMany
    {
        return $this->hasMany(ForoClase::class, 'cod_doc', 'cod_doc');
    }

    public function asistenciaClaseRegistros(): HasMany
    {
        return $this->hasMany(AsistenciaClase::class, 'cod_doc', 'cod_doc');
    }
}
