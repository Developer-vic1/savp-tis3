<?php

namespace App\Models\Oficial\Academico;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;

class Turno extends Model
{
    protected $table = 'turno';

    protected $primaryKey = 'cod_tur';

    public $incrementing = false;

    protected $keyType = 'string';

    protected $fillable = [
        'cod_tur', // Código turno
        'nom_tur', // Nombre turno
        'hor_ini_tur', // Hora inicio turno
        'hor_fin_tur', // Hora fin turno
        'est_tur', // Estado turno
    ];

    protected static function booted(): void
    {
        static::creating(function ($turno) {

            if (! $turno->cod_tur) {
                $turno->cod_tur = 'TUR_'.strtoupper(bin2hex(random_bytes(8)));
            }
        });
    }

    // 🔗 Relaciones

    public function planesAsignatura(): HasManyThrough
    {
        return $this->hasManyThrough(PlanAsignatura::class, GrupoAcademico::class, 'cod_tur', 'cod_gac', 'cod_tur', 'cod_gac');
    }

    public function grupoAcademicoRegistros(): HasMany
    {
        return $this->hasMany(GrupoAcademico::class, 'cod_tur', 'cod_tur');
    }

    public function plantillaHorariaRegistros(): HasMany
    {
        return $this->hasMany(PlantillaHoraria::class, 'cod_tur', 'cod_tur');
    }

    public function calendarioEventoRegistros(): HasMany
    {
        return $this->hasMany(CalendarioEvento::class, 'cod_tur', 'cod_tur');
    }
}
