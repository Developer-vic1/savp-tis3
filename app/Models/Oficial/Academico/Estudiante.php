<?php

namespace App\Models\Oficial\Academico;

use App\Models\Oficial\AporteAcademicoVocacional\OrientacionActividad;
use App\Models\Oficial\AulaVirtual\ClaseEstudiante;
use App\Models\Oficial\AulaVirtual\EntregaTarea;
use App\Models\Oficial\AulaVirtual\IntentoCuestionario;
use App\Models\Oficial\AulaVirtual\RegistroActividadClase;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;

class Estudiante extends Model
{
    protected $table = 'estudiante';

    protected $primaryKey = 'cod_est';

    public $incrementing = false;

    protected $keyType = 'string';

    protected $fillable = [
        'cod_est',
        'rud_est',
        'cod_per',
        'cod_tve',
        'cod_ipe',
        'cod_esp',
        'est_est',
    ];

    protected static function booted(): void
    {
        static::creating(function ($estudiante) {

            if (! $estudiante->cod_est) {
                $estudiante->cod_est = 'EST_'.strtoupper(bin2hex(random_bytes(8)));
            }
        });
    }

    // 🔗 Relaciones

    public function persona()
    {
        return $this->belongsTo(Persona::class, 'cod_per', 'cod_per');
    }

    public function tipoVinculacion()
    {
        return $this->belongsTo(TipoVinculacionEstudiante::class, 'cod_tve', 'cod_tve');
    }

    public function institucionProcedencia()
    {
        return $this->belongsTo(InstitucionProcedencia::class, 'cod_ipe', 'cod_ipe');
    }

    public function especialidad()
    {
        return $this->belongsTo(EspecialidadTecnica::class, 'cod_esp', 'cod_esp');
    }

    public function calificaciones(): HasManyThrough
    {
        return $this->hasManyThrough(Calificacion::class, InscripcionEstudiante::class, 'cod_est', 'cod_ins', 'cod_est', 'cod_ins');
    }

    public function inscripciones()
    {
        return $this->hasMany(InscripcionEstudiante::class, 'cod_est', 'cod_est');
    }

    public function tipoVinculacionEstudiante(): BelongsTo
    {
        return $this->belongsTo(TipoVinculacionEstudiante::class, 'cod_tve', 'cod_tve');
    }

    public function estudianteResponsableRegistros(): HasMany
    {
        return $this->hasMany(EstudianteResponsable::class, 'cod_est', 'cod_est');
    }

    public function expedienteTrasladoRegistros(): HasMany
    {
        return $this->hasMany(ExpedienteTraslado::class, 'cod_est', 'cod_est');
    }

    public function inscripcionEstudianteRegistros(): HasMany
    {
        return $this->hasMany(InscripcionEstudiante::class, 'cod_est', 'cod_est');
    }

    public function claseEstudianteRegistros(): HasMany
    {
        return $this->hasMany(ClaseEstudiante::class, 'cod_est', 'cod_est');
    }

    public function entregaTareaRegistros(): HasMany
    {
        return $this->hasMany(EntregaTarea::class, 'cod_est', 'cod_est');
    }

    public function intentoCuestionarioRegistros(): HasMany
    {
        return $this->hasMany(IntentoCuestionario::class, 'cod_est', 'cod_est');
    }

    public function registroActividadClaseRegistros(): HasMany
    {
        return $this->hasMany(RegistroActividadClase::class, 'cod_est', 'cod_est');
    }

    public function asistenciaEstudianteRegistros(): HasMany
    {
        return $this->hasMany(AsistenciaEstudiante::class, 'cod_est', 'cod_est');
    }

    public function orientacionActividadRegistros(): HasMany
    {
        return $this->hasMany(OrientacionActividad::class, 'cod_est', 'cod_est');
    }
}
