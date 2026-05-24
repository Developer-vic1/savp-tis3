<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class HorarioBloque extends Model
{
    protected $table = 'horario_bloque';
    protected $primaryKey = 'cod_hbl';

    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'cod_hbl',
        'cod_pho',
        'num_hbl',
        'hor_ini_hbl',
        'hor_fin_hbl',
        'nom_hbl',
        'tip_hbl',
        'obs_hbl',
        'est_hbl',
    ];

    protected $casts = [
        'num_hbl' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    // ============================================================
    // GENERACIÓN DE CÓDIGO
    // ============================================================

    protected static function booted(): void
    {
        static::creating(function (HorarioBloque $bloque) {
            if (empty($bloque->cod_hbl)) {
                $ultimoCodigo = self::query()
                    ->where('cod_hbl', 'like', 'HBL_%')
                    ->orderByDesc('cod_hbl')
                    ->value('cod_hbl');

                $ultimoNumero = $ultimoCodigo
                    ? (int) str_replace('HBL_', '', $ultimoCodigo)
                    : 0;

                $bloque->cod_hbl = 'HBL_' . str_pad((string) ($ultimoNumero + 1), 4, '0', STR_PAD_LEFT);
            }

            if (empty($bloque->est_hbl)) {
                $bloque->est_hbl = 'ACTIVO';
            }

            if (empty($bloque->tip_hbl)) {
                $bloque->tip_hbl = 'CLASE';
            }
        });
    }

    // ============================================================
    // RELACIONES
    // ============================================================

    public function plantilla(): BelongsTo
    {
        return $this->belongsTo(PlantillaHoraria::class, 'cod_pho', 'cod_pho');
    }

    /*
     * El turno ya no está guardado directamente en horario_bloque.
     * Se obtiene desde:
     * horario_bloque.cod_pho -> plantilla_horaria.cod_tur
     */
    public function turno(): ?Turno
    {
        return $this->plantilla?->turno;
    }

    public function detalles(): HasMany
    {
        return $this->hasMany(HorarioDetalle::class, 'cod_hbl', 'cod_hbl');
    }

    public function detallesActivos(): HasMany
    {
        return $this->hasMany(HorarioDetalle::class, 'cod_hbl', 'cod_hbl')
            ->where('est_hde', 'ACTIVO');
    }

    // ============================================================
    // ACCESSORS
    // ============================================================

    public function getRangoAttribute(): string
    {
        return "{$this->hor_ini_hbl} - {$this->hor_fin_hbl}";
    }

    public function getDuracionMinutosAttribute(): ?int
    {
        if (! $this->hor_ini_hbl || ! $this->hor_fin_hbl) {
            return null;
        }

        try {
            $inicio = \Carbon\Carbon::createFromFormat('H:i:s', $this->normalizarHora($this->hor_ini_hbl));
            $fin = \Carbon\Carbon::createFromFormat('H:i:s', $this->normalizarHora($this->hor_fin_hbl));

            return $inicio->diffInMinutes($fin, false);
        } catch (\Throwable) {
            return null;
        }
    }

    public function getNombreVisibleAttribute(): string
    {
        return $this->nom_hbl ?: "Bloque {$this->num_hbl}";
    }

    public function getTurnoNombreAttribute(): string
    {
        return $this->plantilla?->turno?->nom_tur ?? 'Sin turno';
    }

    public function getPlantillaNombreAttribute(): string
    {
        return $this->plantilla?->nom_pho ?? 'Sin plantilla';
    }

    // ============================================================
    // SCOPES
    // ============================================================

    public function scopeActivos($query)
    {
        return $query->where('est_hbl', 'ACTIVO');
    }

    public function scopeInactivos($query)
    {
        return $query->where('est_hbl', 'INACTIVO');
    }

    public function scopeDePlantilla($query, string $codPlantilla)
    {
        return $query->where('cod_pho', $codPlantilla);
    }

    public function scopeDeTurno($query, string $codTurno)
    {
        return $query->whereHas('plantilla', function ($consulta) use ($codTurno) {
            $consulta->where('cod_tur', $codTurno);
        });
    }

    public function scopeClases($query)
    {
        return $query->where('tip_hbl', 'CLASE');
    }

    public function scopeRecreos($query)
    {
        return $query->where('tip_hbl', 'RECREO');
    }

    public function scopeOrdenados($query)
    {
        return $query->orderBy('num_hbl')->orderBy('hor_ini_hbl');
    }

    // ============================================================
    // HELPERS
    // ============================================================

    public function estaActivo(): bool
    {
        return $this->est_hbl === 'ACTIVO';
    }

    public function esClase(): bool
    {
        return $this->tip_hbl === 'CLASE';
    }

    public function esRecreo(): bool
    {
        return $this->tip_hbl === 'RECREO';
    }

    public function esSalida(): bool
    {
        return $this->tip_hbl === 'SALIDA';
    }

    private function normalizarHora(mixed $hora): string
    {
        $hora = (string) $hora;

        if (preg_match('/^\d{2}:\d{2}$/', $hora)) {
            return $hora . ':00';
        }

        return $hora;
    }
}