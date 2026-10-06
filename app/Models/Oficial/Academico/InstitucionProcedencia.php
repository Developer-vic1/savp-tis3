<?php

namespace App\Models\Oficial\Academico;

use App\Support\Modelos\CodigoInstitucional;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class InstitucionProcedencia extends Model
{
    use CodigoInstitucional;

    protected $table = 'institucion_procedencia';

    protected $primaryKey = 'cod_ipe';

    public $incrementing = false;

    protected $keyType = 'string';

    protected $fillable = [
        'cod_ipe', // Código institución procedencia
        'nom_ipe', // Nombre institución
        'tip_ipe', // Tipo institución
        'ciu_ipe', // Ciudad institución
        'est_ipe', // Estado institución
        'dep_ipe',
        'dir_ipe',
        'tel_ipe',
        'web_ipe',
    ];

    // 🔗 Relaciones

    public function estudiantes()
    {
        return $this->hasMany(Estudiante::class, 'cod_ipe', 'cod_ipe');
    }

    public function expedienteTrasladoRegistros(): HasMany
    {
        return $this->hasMany(ExpedienteTraslado::class, 'cod_ipe', 'cod_ipe');
    }
}
