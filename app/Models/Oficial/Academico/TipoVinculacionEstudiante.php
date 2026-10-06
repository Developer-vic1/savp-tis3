<?php

namespace App\Models\Oficial\Academico;

use App\Support\Modelos\CodigoInstitucional;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class TipoVinculacionEstudiante extends Model
{
    use CodigoInstitucional;

    protected $table = 'tipo_vinculacion_estudiante';

    protected $primaryKey = 'cod_tve';

    public $incrementing = false;

    protected $keyType = 'string';

    protected $fillable = [
        'cod_tve', // Código tipo vinculación estudiante
        'nom_tve', // Nombre tipo vinculación
        'des_tve', // Descripción tipo vinculación
        'est_tve', // Estado tipo vinculación
    ];

    // 🔗 Relaciones

    public function estudiantes()
    {
        return $this->hasMany(Estudiante::class, 'cod_tve', 'cod_tve');
    }

    public function estudianteRegistros(): HasMany
    {
        return $this->hasMany(Estudiante::class, 'cod_tve', 'cod_tve');
    }
}
