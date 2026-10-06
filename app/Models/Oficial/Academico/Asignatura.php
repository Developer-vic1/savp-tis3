<?php

namespace App\Models\Oficial\Academico;

use App\Support\Modelos\CodigoInstitucional;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;

class Asignatura extends Model
{
    use CodigoInstitucional;

    protected $table = 'asignatura';

    protected $primaryKey = 'cod_asi';

    public $incrementing = false;

    protected $keyType = 'string';

    protected $fillable = [
        'cod_asi', // Código asignatura
        'nom_asi', // Nombre asignatura
        'sig_asi', // Sigla asignatura
        'hor_asi', // Horas académicas
        'est_asi', // Estado asignatura
    ];

    // 🔗 Relaciones

    public function planAsignatura()
    {
        return $this->hasMany(PlanAsignatura::class, 'cod_asi', 'cod_asi');
    }

    public function calificaciones(): HasManyThrough
    {
        return $this->hasManyThrough(Calificacion::class, PlanAsignatura::class, 'cod_asi', 'cod_pas', 'cod_asi', 'cod_pas');
    }

    protected $casts = [
        'hor_asi' => 'decimal:2',
    ];

    public function notaTrasladoRegistros(): HasMany
    {
        return $this->hasMany(NotaTraslado::class, 'cod_asi', 'cod_asi');
    }

    public function planAsignaturaRegistros(): HasMany
    {
        return $this->hasMany(PlanAsignatura::class, 'cod_asi', 'cod_asi');
    }
}
