<?php

namespace App\Models\Oficial\Academico;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;

class Paralelo extends Model
{
    protected $table = 'paralelo';

    protected $primaryKey = 'cod_par';

    public $incrementing = false;

    protected $keyType = 'string';

    protected $fillable = [
        'cod_par',
        'nom_par',
        'est_par',
    ];

    protected static function booted(): void
    {
        static::creating(function ($paralelo) {
            if (! $paralelo->cod_par) {
                $paralelo->cod_par = 'PAR_'.strtoupper(bin2hex(random_bytes(8)));
            }
        });
    }

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
