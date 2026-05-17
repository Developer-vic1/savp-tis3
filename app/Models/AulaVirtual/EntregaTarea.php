<?php

namespace App\Models\AulaVirtual;

use App\Models\Estudiante;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class EntregaTarea extends Model
{
    protected $table = 'entrega_tarea';
    protected $primaryKey = 'cod_ent';

    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'cod_ent', // Código único de la entrega
        'cod_tar', // Tarea a la que pertenece la entrega
        'cod_est', // Estudiante que realiza la entrega

        'fec_ent', // Fecha y hora en que el estudiante entregó la tarea
        'tex_ent', // Texto, comentario o respuesta escrita de la entrega

        'est_ent', // Estado: PENDIENTE, ENTREGADO, ENTREGADO_TARDE, DEVUELTO, CALIFICADO o ANULADO
        'obs_ent', // Observación interna o nota adicional de la entrega
    ];

    protected $casts = [
        'fec_ent' => 'datetime',
    ];

    protected static function booted(): void
    {
        static::creating(function (EntregaTarea $entregaTarea) {
            if (! $entregaTarea->cod_ent) {
                $ultimoCodigo = self::where('cod_ent', 'like', 'ENT_%')
                    ->orderByDesc('cod_ent')
                    ->value('cod_ent');

                $numero = $ultimoCodigo
                    ? ((int) str_replace('ENT_', '', $ultimoCodigo)) + 1
                    : 1;

                $entregaTarea->cod_ent = 'ENT_' . str_pad((string) $numero, 4, '0', STR_PAD_LEFT);
            }

            if (! $entregaTarea->fec_ent && in_array($entregaTarea->est_ent, ['ENTREGADO', 'ENTREGADO_TARDE'], true)) {
                $entregaTarea->fec_ent = now();
            }
        });
    }

    public function tarea(): BelongsTo
    {
        return $this->belongsTo(Tarea::class, 'cod_tar', 'cod_tar');
    }

    public function estudiante(): BelongsTo
    {
        return $this->belongsTo(Estudiante::class, 'cod_est', 'cod_est');
    }

    public function archivos(): HasMany
    {
        return $this->hasMany(EntregaArchivo::class, 'cod_ent', 'cod_ent');
    }

    public function calificacion(): HasOne
    {
        return $this->hasOne(CalificacionTarea::class, 'cod_ent', 'cod_ent');
    }

    public function scopePendientes($query)
    {
        return $query->where('est_ent', 'PENDIENTE');
    }

    public function scopeEntregadas($query)
    {
        return $query->whereIn('est_ent', ['ENTREGADO', 'ENTREGADO_TARDE']);
    }

    public function scopeTardias($query)
    {
        return $query->where('est_ent', 'ENTREGADO_TARDE');
    }

    public function scopeDevueltas($query)
    {
        return $query->where('est_ent', 'DEVUELTO');
    }

    public function scopeCalificadas($query)
    {
        return $query->where('est_ent', 'CALIFICADO');
    }

    public function scopeAnuladas($query)
    {
        return $query->where('est_ent', 'ANULADO');
    }

    public function estaPendiente(): bool
    {
        return $this->est_ent === 'PENDIENTE';
    }

    public function estaEntregada(): bool
    {
        return in_array($this->est_ent, ['ENTREGADO', 'ENTREGADO_TARDE'], true);
    }

    public function estaEntregadaTarde(): bool
    {
        return $this->est_ent === 'ENTREGADO_TARDE';
    }

    public function estaDevuelta(): bool
    {
        return $this->est_ent === 'DEVUELTO';
    }

    public function estaCalificada(): bool
    {
        return $this->est_ent === 'CALIFICADO';
    }

    public function estaAnulada(): bool
    {
        return $this->est_ent === 'ANULADO';
    }

    public function marcarEntregada(bool $tardia = false): void
    {
        $this->forceFill([
            'fec_ent' => $this->fec_ent ?? now(),
            'est_ent' => $tardia ? 'ENTREGADO_TARDE' : 'ENTREGADO',
        ])->save();
    }

    public function marcarCalificada(): void
    {
        $this->forceFill([
            'est_ent' => 'CALIFICADO',
        ])->save();
    }

    public function devolver(?string $observacion = null): void
    {
        $this->forceFill([
            'est_ent' => 'DEVUELTO',
            'obs_ent' => $observacion ?? $this->obs_ent,
        ])->save();
    }

    public function anular(?string $observacion = null): void
    {
        $this->forceFill([
            'est_ent' => 'ANULADO',
            'obs_ent' => $observacion ?? $this->obs_ent,
        ])->save();
    }
}