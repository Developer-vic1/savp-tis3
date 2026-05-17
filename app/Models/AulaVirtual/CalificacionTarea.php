<?php

namespace App\Models\AulaVirtual;

use App\Models\Docente;
use App\Models\Estudiante;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CalificacionTarea extends Model
{
    protected $table = 'calificacion_tarea';
    protected $primaryKey = 'cod_cal_tar';

    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'cod_cal_tar', // Código único de la calificación
        'cod_ent', // Entrega calificada
        'cod_tar', // Tarea correspondiente
        'cod_est', // Estudiante evaluado
        'cod_doc', // Docente que calificó

        'pun_obt', // Puntaje obtenido por el estudiante
        'pun_max', // Puntaje máximo usado para la calificación
        'com_cal', // Comentario o retroalimentación del docente
        'fec_cal', // Fecha y hora de calificación

        'est_cal', // Estado: REGISTRADO, RECTIFICADO, DEVUELTO o ANULADO
    ];

    protected $casts = [
        'pun_obt' => 'decimal:2',
        'pun_max' => 'decimal:2',
        'fec_cal' => 'datetime',
    ];

    protected static function booted(): void
    {
        static::creating(function (CalificacionTarea $calificacionTarea) {
            if (! $calificacionTarea->cod_cal_tar) {
                $ultimoCodigo = self::where('cod_cal_tar', 'like', 'CALT_%')
                    ->orderByDesc('cod_cal_tar')
                    ->value('cod_cal_tar');

                $numero = $ultimoCodigo
                    ? ((int) str_replace('CALT_', '', $ultimoCodigo)) + 1
                    : 1;

                $calificacionTarea->cod_cal_tar = 'CALT_' . str_pad((string) $numero, 4, '0', STR_PAD_LEFT);
            }

            if (! $calificacionTarea->fec_cal) {
                $calificacionTarea->fec_cal = now();
            }
        });
    }

    public function entrega(): BelongsTo
    {
        return $this->belongsTo(EntregaTarea::class, 'cod_ent', 'cod_ent');
    }

    public function tarea(): BelongsTo
    {
        return $this->belongsTo(Tarea::class, 'cod_tar', 'cod_tar');
    }

    public function estudiante(): BelongsTo
    {
        return $this->belongsTo(Estudiante::class, 'cod_est', 'cod_est');
    }

    public function docente(): BelongsTo
    {
        return $this->belongsTo(Docente::class, 'cod_doc', 'cod_doc');
    }

    public function scopeRegistradas($query)
    {
        return $query->where('est_cal', 'REGISTRADO');
    }

    public function scopeRectificadas($query)
    {
        return $query->where('est_cal', 'RECTIFICADO');
    }

    public function scopeDevueltas($query)
    {
        return $query->where('est_cal', 'DEVUELTO');
    }

    public function scopeAnuladas($query)
    {
        return $query->where('est_cal', 'ANULADO');
    }

    public function estaRegistrada(): bool
    {
        return $this->est_cal === 'REGISTRADO';
    }

    public function estaRectificada(): bool
    {
        return $this->est_cal === 'RECTIFICADO';
    }

    public function estaDevuelta(): bool
    {
        return $this->est_cal === 'DEVUELTO';
    }

    public function estaAnulada(): bool
    {
        return $this->est_cal === 'ANULADO';
    }

    public function porcentaje(): float
    {
        if ((float) $this->pun_max <= 0) {
            return 0;
        }

        return round(((float) $this->pun_obt / (float) $this->pun_max) * 100, 2);
    }

    public function rectificar(float $puntaje, ?string $comentario = null): void
    {
        $this->forceFill([
            'pun_obt' => $puntaje,
            'com_cal' => $comentario ?? $this->com_cal,
            'fec_cal' => now(),
            'est_cal' => 'RECTIFICADO',
        ])->save();
    }

    public function devolver(?string $comentario = null): void
    {
        $this->forceFill([
            'com_cal' => $comentario ?? $this->com_cal,
            'est_cal' => 'DEVUELTO',
        ])->save();
    }

    public function anular(): void
    {
        $this->forceFill([
            'est_cal' => 'ANULADO',
        ])->save();
    }
}
