<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PlantillaHoraria extends Model
{
    protected $table = 'plantilla_horaria';
    protected $primaryKey = 'cod_pho';

    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'cod_pho',
        'cod_tur',
        'nom_pho',
        'tip_pho',
        'des_pho',
        'fec_ini_pho',
        'fec_fin_pho',
        'dur_blo_pho',
        'ord_pho',
        'act_pho',
        'est_pho',
    ];

    protected $casts = [
        'fec_ini_pho' => 'date',
        'fec_fin_pho' => 'date',
        'dur_blo_pho' => 'integer',
        'ord_pho' => 'integer',
        'act_pho' => 'boolean',
        'est_pho' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    // ============================================================
    // GENERACIÓN DE CÓDIGO
    // ============================================================

    protected static function booted(): void
    {
        static::creating(function (PlantillaHoraria $plantilla) {
            if (! empty($plantilla->cod_pho)) {
                return;
            }

            $ultimoCodigo = self::query()
                ->where('cod_pho', 'like', 'PHO_%')
                ->orderByDesc('cod_pho')
                ->value('cod_pho');

            $ultimoNumero = $ultimoCodigo
                ? (int) str_replace('PHO_', '', $ultimoCodigo)
                : 0;

            $plantilla->cod_pho = 'PHO_' . str_pad((string) ($ultimoNumero + 1), 4, '0', STR_PAD_LEFT);
        });
    }

    // ============================================================
    // RELACIONES
    // ============================================================

    public function turno(): BelongsTo
    {
        return $this->belongsTo(Turno::class, 'cod_tur', 'cod_tur');
    }

    public function bloques(): HasMany
    {
        return $this->hasMany(HorarioBloque::class, 'cod_pho', 'cod_pho')
            ->orderBy('num_hbl')
            ->orderBy('hor_ini_hbl');
    }

    public function bloquesActivos(): HasMany
    {
        return $this->hasMany(HorarioBloque::class, 'cod_pho', 'cod_pho')
            ->where('est_hbl', 'ACTIVO')
            ->orderBy('num_hbl')
            ->orderBy('hor_ini_hbl');
    }

    public function horarios(): HasMany
    {
        return $this->hasMany(Horario::class, 'cod_pho', 'cod_pho');
    }

    public function horariosActivos(): HasMany
    {
        return $this->hasMany(Horario::class, 'cod_pho', 'cod_pho')
            ->where('est_hor', 'ACTIVO');
    }

    // ============================================================
    // SCOPES
    // ============================================================

    public function scopeActivas($query)
    {
        return $query->where('est_pho', true);
    }

    public function scopeInactivas($query)
    {
        return $query->where('est_pho', false);
    }

    public function scopeAplicadas($query)
    {
        return $query->where('act_pho', true);
    }

    public function scopeNoAplicadas($query)
    {
        return $query->where('act_pho', false);
    }

    public function scopeRegulares($query)
    {
        return $query->where('tip_pho', 'REGULAR');
    }

    public function scopeInvierno($query)
    {
        return $query->where('tip_pho', 'INVIERNO');
    }

    public function scopeAjustes($query)
    {
        return $query->where('tip_pho', 'AJUSTE');
    }

    public function scopeEmergencias($query)
    {
        return $query->where('tip_pho', 'EMERGENCIA');
    }

    public function scopeDelTurno($query, string $codTurno)
    {
        return $query->where('cod_tur', $codTurno);
    }

    // ============================================================
    // ACCESSORS
    // ============================================================

    public function getNombreCompletoAttribute(): string
    {
        $turno = $this->turno?->nom_tur ?? 'Sin turno';

        return "{$this->nom_pho} - {$turno}";
    }

    public function getVigenciaAttribute(): string
    {
        if (! $this->fec_ini_pho && ! $this->fec_fin_pho) {
            return 'Base anual / sin rango temporal';
        }

        $inicio = $this->fec_ini_pho?->format('d/m/Y') ?? 'Sin inicio';
        $fin = $this->fec_fin_pho?->format('d/m/Y') ?? 'Sin fin';

        return "{$inicio} al {$fin}";
    }

    public function getEstadoNombreAttribute(): string
    {
        return $this->est_pho ? 'Activo' : 'Inactivo';
    }

    public function getAplicacionNombreAttribute(): string
    {
        return $this->act_pho ? 'Aplicada' : 'No aplicada';
    }

    // ============================================================
    // HELPERS
    // ============================================================

    public function esRegular(): bool
    {
        return $this->tip_pho === 'REGULAR';
    }

    public function esInvierno(): bool
    {
        return $this->tip_pho === 'INVIERNO';
    }

    public function esAjuste(): bool
    {
        return $this->tip_pho === 'AJUSTE';
    }

    public function esEmergencia(): bool
    {
        return $this->tip_pho === 'EMERGENCIA';
    }

    public function estaAplicada(): bool
    {
        return (bool) $this->act_pho;
    }

    public function estaActiva(): bool
    {
        return (bool) $this->est_pho;
    }

    public function tieneVigenciaTemporal(): bool
    {
        return ! is_null($this->fec_ini_pho) || ! is_null($this->fec_fin_pho);
    }
}