<?php

namespace App\Models\AulaVirtual;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class EstadoAsistencia extends Model
{
    protected $table = 'estado_asistencia';
    protected $primaryKey = 'cod_est_asi';

    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'cod_est_asi', // Código único del estado de asistencia

        'nom_est_asi', // Nombre del estado: Presente, Ausente, Atraso, Justificado, Licencia o Sin registro
        'abr_est_asi', // Abreviatura visible del estado, por ejemplo P, A, T, J, L o SR
        'des_est_asi', // Descripción o regla institucional del estado

        'color_est_asi', // Color visual usado en la interfaz para identificar el estado

        'valor_porcentual', // Valor porcentual que aporta al cálculo de asistencia
        'afecta_asistencia', // Define si el estado afecta el porcentaje de asistencia
        'requiere_observacion', // Indica si al usar este estado se debe registrar una observación

        'est_est_asi', // Estado del catálogo: ACTIVO, INACTIVO o ANULADO
    ];

    protected $casts = [
        'valor_porcentual' => 'decimal:2',
        'afecta_asistencia' => 'boolean',
        'requiere_observacion' => 'boolean',
    ];

    protected static function booted(): void
    {
        static::creating(function (EstadoAsistencia $estadoAsistencia) {
            if (! $estadoAsistencia->cod_est_asi) {
                $ultimoCodigo = self::where('cod_est_asi', 'like', 'EASI_%')
                    ->orderByDesc('cod_est_asi')
                    ->value('cod_est_asi');

                $numero = $ultimoCodigo
                    ? ((int) str_replace('EASI_', '', $ultimoCodigo)) + 1
                    : 1;

                $estadoAsistencia->cod_est_asi = 'EASI_' . str_pad((string) $numero, 4, '0', STR_PAD_LEFT);
            }
        });
    }

    public function asistenciasEstudiantes(): HasMany
    {
        return $this->hasMany(AsistenciaEstudiante::class, 'cod_est_asi', 'cod_est_asi');
    }

    public function scopeActivos($query)
    {
        return $query->where('est_est_asi', 'ACTIVO');
    }

    public function scopeInactivos($query)
    {
        return $query->where('est_est_asi', 'INACTIVO');
    }

    public function scopeAnulados($query)
    {
        return $query->where('est_est_asi', 'ANULADO');
    }

    public function scopeAfectanAsistencia($query)
    {
        return $query->where('afecta_asistencia', true);
    }

    public function scopeRequierenObservacion($query)
    {
        return $query->where('requiere_observacion', true);
    }

    public function estaActivo(): bool
    {
        return $this->est_est_asi === 'ACTIVO';
    }

    public function estaInactivo(): bool
    {
        return $this->est_est_asi === 'INACTIVO';
    }

    public function estaAnulado(): bool
    {
        return $this->est_est_asi === 'ANULADO';
    }

    public function afectaAsistencia(): bool
    {
        return (bool) $this->afecta_asistencia;
    }

    public function requiereObservacion(): bool
    {
        return (bool) $this->requiere_observacion;
    }

    public function activar(): void
    {
        $this->forceFill([
            'est_est_asi' => 'ACTIVO',
        ])->save();
    }

    public function desactivar(): void
    {
        $this->forceFill([
            'est_est_asi' => 'INACTIVO',
        ])->save();
    }

    public function anular(): void
    {
        $this->forceFill([
            'est_est_asi' => 'ANULADO',
        ])->save();
    }
}