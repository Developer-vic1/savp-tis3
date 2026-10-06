<?php

namespace App\Models\Oficial\Academico;

use App\Support\Modelos\CodigoInstitucional;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class EspecialidadTecnica extends Model
{
    use CodigoInstitucional;

    protected $table = 'especialidad_tecnica';

    protected $primaryKey = 'cod_esp';

    public $incrementing = false;

    protected $keyType = 'string';

    protected $fillable = [
        'cod_esp',
        'nom_esp',
        'des_esp',
        'est_esp',
    ];

    public function estudiantes()
    {
        return $this->hasMany(Estudiante::class, 'cod_esp', 'cod_esp');
    }

    public function planesEspecialidad()
    {
        return $this->hasMany(PlanEspecialidad::class, 'cod_esp', 'cod_esp');
    }

    public function planEspecialidadRegistros(): HasMany
    {
        return $this->hasMany(PlanEspecialidad::class, 'cod_esp', 'cod_esp');
    }

    public function inscripcionVigenciaRegistros(): HasMany
    {
        return $this->hasMany(InscripcionVigencia::class, 'cod_esp_tec', 'cod_esp');
    }
}
