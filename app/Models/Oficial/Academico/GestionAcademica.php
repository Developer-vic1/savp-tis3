<?php

namespace App\Models\Oficial\Academico;

use App\Models\Oficial\AporteAcademicoVocacional\OrientacionActividad;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;

class GestionAcademica extends Model
{
    protected $table = 'gestion_academica';

    protected $primaryKey = 'cod_gea';

    public $incrementing = false;

    protected $keyType = 'string';

    protected $fillable = [
        'cod_gea', // Código gestión académica
        'ani_gea', // Año gestión académica
        'fii_gea', // Fecha inicio gestión
        'ffi_gea', // Fecha fin gestión
        'est_gea', // Estado gestión académica
    ];

    protected static function booted(): void
    {
        static::creating(function ($gestion) {

            if (! $gestion->cod_gea) {
                $gestion->cod_gea = 'GEA_'.strtoupper(bin2hex(random_bytes(8)));
            }
        });
    }

    // 🔗 Relaciones

    public function inscripciones()
    {
        return $this->hasMany(InscripcionEstudiante::class, 'cod_gea', 'cod_gea');
    }

    public function planesAsignatura(): HasManyThrough
    {
        return $this->hasManyThrough(PlanAsignatura::class, GrupoAcademico::class, 'cod_gea', 'cod_gac', 'cod_gea', 'cod_gac');
    }

    public function respaldos()
    {
        return $this->hasMany(RespaldoGestionAcademica::class, 'cod_gea', 'cod_gea');
    }

    public function horarios(): HasManyThrough
    {
        return $this->hasManyThrough(Horario::class, GrupoAcademico::class, 'cod_gea', 'cod_gac', 'cod_gea', 'cod_gac');
    }

    protected $casts = [
        'ani_gea' => 'integer',
        'fii_gea' => 'date',
        'ffi_gea' => 'date',
    ];

    public function configuracionCalendarioGestionRegistros(): HasMany
    {
        return $this->hasMany(ConfiguracionCalendarioGestion::class, 'cod_gea', 'cod_gea');
    }

    public function grupoAcademicoRegistros(): HasMany
    {
        return $this->hasMany(GrupoAcademico::class, 'cod_gea', 'cod_gea');
    }

    public function calendarioEventoRegistros(): HasMany
    {
        return $this->hasMany(CalendarioEvento::class, 'cod_gea', 'cod_gea');
    }

    public function inscripcionEstudianteRegistros(): HasMany
    {
        return $this->hasMany(InscripcionEstudiante::class, 'cod_gea', 'cod_gea');
    }

    public function respaldoGestionAcademicaRegistros(): HasMany
    {
        return $this->hasMany(RespaldoGestionAcademica::class, 'cod_gea', 'cod_gea');
    }

    public function regenteAsignacionRegistros(): HasMany
    {
        return $this->hasMany(RegenteAsignacion::class, 'cod_gea', 'cod_gea');
    }

    public function orientacionActividadRegistros(): HasMany
    {
        return $this->hasMany(OrientacionActividad::class, 'cod_gea', 'cod_gea');
    }
}
