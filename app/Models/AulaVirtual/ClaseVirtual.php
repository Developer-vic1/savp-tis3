<?php

namespace App\Models\AulaVirtual;

use App\Models\Curso;
use App\Models\Docente;
use App\Models\GestionAcademica;
use App\Models\Paralelo;
use App\Models\PlanAsignatura;
use App\Models\PlanEspecialidad;
use App\Models\Turno;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ClaseVirtual extends Model
{
    protected $table = 'clase_virtual';

    protected $primaryKey = 'cod_cla';

    public $incrementing = false;

    protected $keyType = 'string';

    protected $fillable = [
        'cod_cla',          // Código clase
        'cod_pas',          // Código plan asignatura
        'cod_pes',          // Código plan especialidad
        'nom_cla',          // Nombre clase
        'des_cla',          // Descripción clase
        'fec_ini_cla',      // Fecha inicio clase
        'fec_fin_cla',      // Fecha fin clase
        'vis_cla',          // Visibilidad para estudiantes (false: oculta en preparación, true: visible)
        'est_cla',          // Estado clase (ACTIVA, CERRADA, INACTIVA, ANULADA)
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
                $ultimoCodigo = self::where('cod_cla', 'like', 'CLA_%')
                    ->orderByDesc('cod_cla')
                    ->value('cod_cla');

                $numero = $ultimoCodigo
                    ? ((int) str_replace('CLA_', '', $ultimoCodigo)) + 1
                    : 1;

                $claseVirtual->cod_cla = 'CLA_'.str_pad((string) $numero, 4, '0', STR_PAD_LEFT);
            }

            if (! isset($claseVirtual->attributes['vis_cla'])) {
                $claseVirtual->vis_cla = false;
            }
        });
    }

    // ============================================================
    // RELACIONES
    // ============================================================

    public function planAsignatura(): BelongsTo
    {
        return $this->belongsTo(PlanAsignatura::class, 'cod_pas', 'cod_pas');
    }

    public function planEspecialidad(): BelongsTo
    {
        return $this->belongsTo(PlanEspecialidad::class, 'cod_pes', 'cod_pes');
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
        return $this->hasMany(ActividadClase::class, 'cod_cla', 'cod_cla');
    }

    // ============================================================
    // SCOPES
    // ============================================================

    public function scopeActivas($query)
    {
        return $query->where('est_cla', 'ACTIVA');
    }

    public function scopeDisponibles($query)
    {
        return $query->whereIn('est_cla', ['ACTIVA']);
    }

    public function scopeVisibles($query)
    {
        return $query->where('vis_cla', true);
    }

    public function scopeOcultas($query)
    {
        return $query->where('vis_cla', false);
    }

    // ============================================================
    // MÉTODOS DE DOMINIO Y AYUDANTES
    // ============================================================

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

    public function estaVisible(): bool
    {
        return (bool) $this->vis_cla;
    }

    public function estaOculta(): bool
    {
        return ! (bool) $this->vis_cla;
    }

    public function esPlanAsignatura(): bool
    {
        return ! empty($this->cod_pas);
    }

    public function esPlanEspecialidad(): bool
    {
        return ! empty($this->cod_pes);
    }

    public function getDocenteAttribute(): ?Docente
    {
        return $this->planAsignatura?->docente ?? $this->planEspecialidad?->docente;
    }

    public function getCursoAttribute(): ?Curso
    {
        return $this->planAsignatura?->curso ?? $this->planEspecialidad?->curso;
    }

    public function getParaleloAttribute(): ?Paralelo
    {
        return $this->planAsignatura?->paralelo ?? $this->planEspecialidad?->paralelo;
    }

    public function getTurnoAttribute(): ?Turno
    {
        return $this->planAsignatura?->turno ?? $this->planEspecialidad?->turno;
    }

    public function getGestionAcademicaAttribute(): ?GestionAcademica
    {
        return $this->planAsignatura?->gestionAcademica ?? $this->planEspecialidad?->gestionAcademica;
    }

    public function getTituloMateriaAttribute(): string
    {
        if ($this->planAsignatura?->asignatura) {
            return (string) $this->planAsignatura->asignatura->nom_asi;
        }

        if ($this->planEspecialidad?->especialidad) {
            return (string) $this->planEspecialidad->especialidad->nom_esp;
        }

        return (string) $this->nom_cla;
    }
}
