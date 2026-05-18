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
        'cod_pho',      // Código único de la plantilla horaria. Ej: PHO_0001.

        'cod_tur',      // Turno al que pertenece la plantilla.

        'nom_pho',      // Nombre visible. Ej: Plantilla Regular - Mañana.
        'tip_pho',      // Tipo de plantilla: REGULAR, INVIERNO, AJUSTE, EMERGENCIA.
        'des_pho',      // Descripción institucional o aclaración de uso.

        'fec_ini_pho',  // Fecha de inicio de vigencia si es temporal.
        'fec_fin_pho',  // Fecha de fin de vigencia si es temporal.

        'dur_blo_pho',  // Duración base sugerida del bloque en minutos.
        'ord_pho',      // Orden visual dentro del turno.

        'act_pho',      // Indica si la plantilla está aplicada actualmente.
        'est_pho',      // Estado lógico de la plantilla.
    ];

    protected $casts = [
        'fec_ini_pho' => 'date',
        'fec_fin_pho' => 'date',
        'dur_blo_pho' => 'integer',
        'ord_pho' => 'integer',
        'act_pho' => 'boolean',
        'est_pho' => 'boolean',
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
            ->orderBy('num_hbl');
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

    public function scopeRegulares($query)
    {
        return $query->where('tip_pho', 'REGULAR');
    }

    public function scopeInvierno($query)
    {
        return $query->where('tip_pho', 'INVIERNO');
    }

    public function scopeDelTurno($query, string $codTurno)
    {
        return $query->where('cod_tur', $codTurno);
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

    public function nombreEstado(): string
    {
        return $this->est_pho ? 'Activo' : 'Inactivo';
    }

    public function nombreAplicacion(): string
    {
        return $this->act_pho ? 'Aplicada' : 'No aplicada';
    }
}