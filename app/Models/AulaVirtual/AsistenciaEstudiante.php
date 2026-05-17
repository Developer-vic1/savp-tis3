<?php

namespace App\Models\AulaVirtual;

use App\Models\Estudiante;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AsistenciaEstudiante extends Model
{
    protected $table = 'asistencia_estudiante';
    protected $primaryKey = 'cod_asi_est';

    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'cod_asi_est', // Código único del registro de asistencia del estudiante
        'cod_asi_cla', // Sesión de asistencia a la que pertenece
        'cod_est', // Estudiante registrado en la asistencia
        'cod_est_asi', // Estado de asistencia asignado
        'cod_usu_reg', // Usuario que registró o modificó la asistencia

        'min_retraso', // Minutos de retraso cuando el estado corresponde a atraso
        'obs_asi_est', // Observación específica del registro de asistencia

        'fec_reg_asi_est', // Fecha y hora del registro de asistencia

        'est_asi_est', // Estado interno: REGISTRADO, RECTIFICADO o ANULADO
    ];

    protected $casts = [
        'min_retraso' => 'integer',
        'fec_reg_asi_est' => 'datetime',
    ];

    protected static function booted(): void
    {
        static::creating(function (AsistenciaEstudiante $asistenciaEstudiante) {
            if (! $asistenciaEstudiante->cod_asi_est) {
                $ultimoCodigo = self::where('cod_asi_est', 'like', 'ASIE_%')
                    ->orderByDesc('cod_asi_est')
                    ->value('cod_asi_est');

                $numero = $ultimoCodigo
                    ? ((int) str_replace('ASIE_', '', $ultimoCodigo)) + 1
                    : 1;

                $asistenciaEstudiante->cod_asi_est = 'ASIE_' . str_pad((string) $numero, 4, '0', STR_PAD_LEFT);
            }

            if (! $asistenciaEstudiante->fec_reg_asi_est) {
                $asistenciaEstudiante->fec_reg_asi_est = now();
            }
        });
    }

    public function asistenciaClase(): BelongsTo
    {
        return $this->belongsTo(AsistenciaClase::class, 'cod_asi_cla', 'cod_asi_cla');
    }

    public function estudiante(): BelongsTo
    {
        return $this->belongsTo(Estudiante::class, 'cod_est', 'cod_est');
    }

    public function estadoAsistencia(): BelongsTo
    {
        return $this->belongsTo(EstadoAsistencia::class, 'cod_est_asi', 'cod_est_asi');
    }

    public function usuarioRegistro(): BelongsTo
    {
        return $this->belongsTo(User::class, 'cod_usu_reg', 'cod_usu');
    }

    public function scopeRegistrados($query)
    {
        return $query->where('est_asi_est', 'REGISTRADO');
    }

    public function scopeRectificados($query)
    {
        return $query->where('est_asi_est', 'RECTIFICADO');
    }

    public function scopeAnulados($query)
    {
        return $query->where('est_asi_est', 'ANULADO');
    }

    public function scopeDeEstudiante($query, string $codEst)
    {
        return $query->where('cod_est', $codEst);
    }

    public function scopeDeSesion($query, string $codAsiCla)
    {
        return $query->where('cod_asi_cla', $codAsiCla);
    }

    public function estaRegistrado(): bool
    {
        return $this->est_asi_est === 'REGISTRADO';
    }

    public function estaRectificado(): bool
    {
        return $this->est_asi_est === 'RECTIFICADO';
    }

    public function estaAnulado(): bool
    {
        return $this->est_asi_est === 'ANULADO';
    }

    public function tieneRetraso(): bool
    {
        return (int) $this->min_retraso > 0;
    }

    public function rectificar(string $codEstadoAsistencia, ?string $observacion = null, int $minutosRetraso = 0): void
    {
        $this->forceFill([
            'cod_est_asi' => $codEstadoAsistencia,
            'obs_asi_est' => $observacion ?? $this->obs_asi_est,
            'min_retraso' => max(0, $minutosRetraso),
            'fec_reg_asi_est' => now(),
            'est_asi_est' => 'RECTIFICADO',
        ])->save();
    }

    public function anular(?string $observacion = null): void
    {
        $this->forceFill([
            'obs_asi_est' => $observacion ?? $this->obs_asi_est,
            'est_asi_est' => 'ANULADO',
        ])->save();
    }
}