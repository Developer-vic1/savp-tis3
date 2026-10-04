<?php

namespace App\Models\Oficial\AulaVirtual;

use App\Models\Oficial\Academico\AsistenciaClase;
use App\Models\Oficial\Academico\PlanAsignatura;
use App\Models\Oficial\Academico\PlanEspecialidad;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;

class ClaseVirtual extends Model
{
    protected $table = 'clase_virtual';

    protected $primaryKey = 'cod_cla';

    public $incrementing = false;

    protected $keyType = 'string';

    protected $fillable = [
        'cod_cla',          // Codigo clase
        'cod_pas',          // Codigo plan asignatura
        'nom_cla',          // Nombre clase
        'des_cla',          // Descripcion clase
        'fec_ini_cla',      // Fecha inicio clase
        'fec_fin_cla',      // Fecha fin clase
        'est_cla',          // Estado clase
        'cod_pes',
        'vis_cla',
    ];

    protected $casts = [
        'fec_ini_cla' => 'date',
        'fec_fin_cla' => 'date',
        'vis_cla' => 'boolean',
    ];

    protected static function booted(): void
    {
        static::creating(function (ClaseVirtual $claseVirtual) {
            if (! $claseVirtual->cod_cla) {
                $claseVirtual->cod_cla = 'CLA_'.strtoupper(bin2hex(random_bytes(8)));
            }
        });
    }

    public function planAsignatura(): BelongsTo
    {
        return $this->belongsTo(PlanAsignatura::class, 'cod_pas', 'cod_pas');
    }

    public function estudiantes(): HasMany
    {
        return $this->hasMany(ClaseEstudiante::class, 'cod_cla', 'cod_cla');
    }

    public function publicaciones(): HasMany
    {
        return $this->hasMany(PublicacionClase::class, 'cod_cla', 'cod_cla');
    }

    public function materiales(): HasMany
    {
        return $this->hasMany(MaterialClase::class, 'cod_cla', 'cod_cla');
    }

    public function tareas(): HasMany
    {
        return $this->hasMany(Tarea::class, 'cod_cla', 'cod_cla');
    }

    public function asistencias(): HasMany
    {
        return $this->hasMany(AsistenciaClase::class, 'cod_cla', 'cod_cla');
    }

    public function actividades(): HasMany
    {
        return $this->hasMany(RegistroActividadClase::class, 'cod_cla', 'cod_cla');
    }

    public function entregas(): HasManyThrough
    {
        return $this->hasManyThrough(EntregaTarea::class, Tarea::class, 'cod_cla', 'cod_tar', 'cod_cla', 'cod_tar');
    }

    public function scopeActivas($query)
    {
        return $query->where('est_cla', 'ACTIVA');
    }

    public function scopeDisponibles($query)
    {
        return $query->whereIn('est_cla', ['ACTIVA']);
    }

    public function estaActiva(): bool
    {
        return $this->est_cla === 'ACTIVA';
    }

    public function estaCerrada(): bool
    {
        return $this->est_cla === 'CERRADA';
    }

    public function estaAnulada(): bool
    {
        return $this->est_cla === 'ANULADA';
    }

    public function planEspecialidad(): BelongsTo
    {
        return $this->belongsTo(PlanEspecialidad::class, 'cod_pes', 'cod_pes');
    }

    public function claseEstudianteRegistros(): HasMany
    {
        return $this->hasMany(ClaseEstudiante::class, 'cod_cla', 'cod_cla');
    }

    public function seccionClaseRegistros(): HasMany
    {
        return $this->hasMany(SeccionClase::class, 'cod_cla', 'cod_cla');
    }

    public function publicacionClaseRegistros(): HasMany
    {
        return $this->hasMany(PublicacionClase::class, 'cod_cla', 'cod_cla');
    }

    public function materialClaseRegistros(): HasMany
    {
        return $this->hasMany(MaterialClase::class, 'cod_cla', 'cod_cla');
    }

    public function tareaRegistros(): HasMany
    {
        return $this->hasMany(Tarea::class, 'cod_cla', 'cod_cla');
    }

    public function cuestionarioRegistros(): HasMany
    {
        return $this->hasMany(Cuestionario::class, 'cod_cla', 'cod_cla');
    }

    public function foroClaseRegistros(): HasMany
    {
        return $this->hasMany(ForoClase::class, 'cod_cla', 'cod_cla');
    }

    public function registroActividadClaseRegistros(): HasMany
    {
        return $this->hasMany(RegistroActividadClase::class, 'cod_cla', 'cod_cla');
    }
}
