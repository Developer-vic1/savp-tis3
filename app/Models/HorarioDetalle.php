<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class HorarioDetalle extends Model
{
    protected $table = 'horario_detalle';
    protected $primaryKey = 'cod_hde';

    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'cod_hde',
        'cod_hor',
        'cod_hbl',
        'dia_hde',
        'cod_pas',
        'cod_pes',
        'aul_hde',
        'obs_hde',
        'est_hde',
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    // ============================================================
    // GENERACIÓN DE CÓDIGO
    // ============================================================

    protected static function booted(): void
    {
        static::creating(function (HorarioDetalle $detalle) {
            if (empty($detalle->cod_hde)) {
                $ultimoCodigo = self::query()
                    ->where('cod_hde', 'like', 'HDE_%')
                    ->orderByDesc('cod_hde')
                    ->value('cod_hde');

                $ultimoNumero = $ultimoCodigo
                    ? (int) str_replace('HDE_', '', $ultimoCodigo)
                    : 0;

                $detalle->cod_hde = 'HDE_' . str_pad((string) ($ultimoNumero + 1), 4, '0', STR_PAD_LEFT);
            }

            if (empty($detalle->est_hde)) {
                $detalle->est_hde = 'ACTIVO';
            }
        });
    }

    // ============================================================
    // RELACIONES
    // ============================================================

    public function horario(): BelongsTo
    {
        return $this->belongsTo(Horario::class, 'cod_hor', 'cod_hor');
    }

    public function bloque(): BelongsTo
    {
        return $this->belongsTo(HorarioBloque::class, 'cod_hbl', 'cod_hbl');
    }

    public function planAsignatura(): BelongsTo
    {
        return $this->belongsTo(PlanAsignatura::class, 'cod_pas', 'cod_pas');
    }

    public function planEspecialidad(): BelongsTo
    {
        return $this->belongsTo(PlanEspecialidad::class, 'cod_pes', 'cod_pes');
    }

    // ============================================================
    // ACCESSORS
    // ============================================================

    public function getTipoPlanAttribute(): string
    {
        if (! empty($this->cod_pas)) {
            return 'ASIGNATURA';
        }

        if (! empty($this->cod_pes)) {
            return 'ESPECIALIDAD';
        }

        return 'SIN_PLAN';
    }

    public function getPlanNombreAttribute(): string
    {
        if ($this->planAsignatura) {
            return $this->planAsignatura->asignatura->nom_asi
                ?? $this->planAsignatura->nom_pas
                ?? 'Asignatura';
        }

        if ($this->planEspecialidad) {
            return $this->planEspecialidad->especialidad->nom_esp
                ?? $this->planEspecialidad->nom_pes
                ?? 'Especialidad';
        }

        return 'Sin plan asignado';
    }

    public function getRangoBloqueAttribute(): string
    {
        return $this->bloque?->rango ?? 'Sin bloque';
    }

    public function getHorarioNombreAttribute(): string
    {
        return $this->horario?->nombre_descriptivo ?? 'Sin horario';
    }

    // ============================================================
    // SCOPES
    // ============================================================

    public function scopeActivos($query)
    {
        return $query->where('est_hde', 'ACTIVO');
    }

    public function scopeInactivos($query)
    {
        return $query->where('est_hde', 'INACTIVO');
    }

    public function scopeSuspendidos($query)
    {
        return $query->where('est_hde', 'SUSPENDIDO');
    }

    public function scopeDelHorario($query, string $codHorario)
    {
        return $query->where('cod_hor', $codHorario);
    }

    public function scopeDelBloque($query, string $codBloque)
    {
        return $query->where('cod_hbl', $codBloque);
    }

    public function scopeDelDia($query, string $dia)
    {
        return $query->where('dia_hde', strtoupper($dia));
    }

    public function scopeAsignaturas($query)
    {
        return $query->whereNotNull('cod_pas')->whereNull('cod_pes');
    }

    public function scopeEspecialidades($query)
    {
        return $query->whereNull('cod_pas')->whereNotNull('cod_pes');
    }

    // ============================================================
    // HELPERS
    // ============================================================

    public function estaActivo(): bool
    {
        return $this->est_hde === 'ACTIVO';
    }

    public function esAsignatura(): bool
    {
        return ! empty($this->cod_pas) && empty($this->cod_pes);
    }

    public function esEspecialidad(): bool
    {
        return empty($this->cod_pas) && ! empty($this->cod_pes);
    }
}