<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Horario extends Model
{
    protected $table = 'horario';
    protected $primaryKey = 'cod_hor';

    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'cod_hor',
        'cod_gea',
        'cod_cur',
        'cod_par',
        'cod_pho',
        'obs_hor',
        'est_hor',
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
        static::creating(function (Horario $horario) {
            if (! empty($horario->cod_hor)) {
                return;
            }

            $ultimoCodigo = self::query()
                ->where('cod_hor', 'like', 'HOR_%')
                ->orderByDesc('cod_hor')
                ->value('cod_hor');

            $ultimoNumero = $ultimoCodigo
                ? (int) str_replace('HOR_', '', $ultimoCodigo)
                : 0;

            $horario->cod_hor = 'HOR_' . str_pad((string) ($ultimoNumero + 1), 4, '0', STR_PAD_LEFT);
        });
    }

    // ============================================================
    // RELACIONES
    // ============================================================

    public function detalles(): HasMany
    {
        return $this->hasMany(HorarioDetalle::class, 'cod_hor', 'cod_hor');
    }

    public function detallesActivos(): HasMany
    {
        return $this->hasMany(HorarioDetalle::class, 'cod_hor', 'cod_hor')
            ->where('est_hde', 'ACTIVO');
    }

    public function gestion(): BelongsTo
    {
        return $this->belongsTo(GestionAcademica::class, 'cod_gea', 'cod_gea');
    }

    public function curso(): BelongsTo
    {
        return $this->belongsTo(Curso::class, 'cod_cur', 'cod_cur');
    }

    public function paralelo(): BelongsTo
    {
        return $this->belongsTo(Paralelo::class, 'cod_par', 'cod_par');
    }

    public function plantilla(): BelongsTo
    {
        return $this->belongsTo(PlantillaHoraria::class, 'cod_pho', 'cod_pho');
    }

    /*
     * El turno ya no se almacena directamente en horario.
     * Se obtiene desde la plantilla horaria:
     * horario.cod_pho -> plantilla_horaria.cod_tur -> turno.cod_tur
     */
    public function turno(): ?Turno
    {
        return $this->plantilla?->turno;
    }

    // ============================================================
    // ACCESSORS / ATRIBUTOS DERIVADOS
    // ============================================================

    public function getNombreDescriptivoAttribute(): string
    {
        $gestion = $this->gestion?->ani_gea ?? 'Sin gestión';
        $curso = $this->curso?->nom_cur ?? 'Sin curso';
        $paralelo = $this->paralelo?->nom_par ?? 'Sin paralelo';
        $turno = $this->plantilla?->turno?->nom_tur ?? 'Sin turno';
        $tipoPlantilla = $this->plantilla?->tip_pho ?? 'Sin plantilla';

        return "Horario {$curso} {$paralelo} - {$turno} {$tipoPlantilla} - Gestión {$gestion}";
    }

    public function getTurnoNombreAttribute(): string
    {
        return $this->plantilla?->turno?->nom_tur ?? 'Sin turno';
    }

    public function getTipoPlantillaAttribute(): string
    {
        return $this->plantilla?->tip_pho ?? 'SIN_PLANTILLA';
    }

    public function getEstaActivoAttribute(): bool
    {
        return $this->est_hor === 'ACTIVO';
    }

    // ============================================================
    // SCOPES
    // ============================================================

    public function scopeActivos($query)
    {
        return $query->where('est_hor', 'ACTIVO');
    }

    public function scopeInactivos($query)
    {
        return $query->where('est_hor', 'INACTIVO');
    }

    public function scopePlanificados($query)
    {
        return $query->where('est_hor', 'PLANIFICADO');
    }

    public function scopeArchivados($query)
    {
        return $query->where('est_hor', 'ARCHIVADO');
    }

    public function scopeDeGestion($query, string $codGestion)
    {
        return $query->where('cod_gea', $codGestion);
    }

    public function scopeDeCurso($query, string $codCurso)
    {
        return $query->where('cod_cur', $codCurso);
    }

    public function scopeDeParalelo($query, string $codParalelo)
    {
        return $query->where('cod_par', $codParalelo);
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

    // ============================================================
    // HELPERS
    // ============================================================

    public function estaActivo(): bool
    {
        return $this->est_hor === 'ACTIVO';
    }

    public function estaPlanificado(): bool
    {
        return $this->est_hor === 'PLANIFICADO';
    }

    public function estaArchivado(): bool
    {
        return $this->est_hor === 'ARCHIVADO';
    }

    public function usaPlantillaRegular(): bool
    {
        return $this->plantilla?->tip_pho === 'REGULAR';
    }

    public function usaPlantillaInvierno(): bool
    {
        return $this->plantilla?->tip_pho === 'INVIERNO';
    }
}