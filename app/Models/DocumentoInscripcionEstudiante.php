<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DocumentoInscripcionEstudiante extends Model
{
    protected $table = 'documento_inscripcion_estudiante';
    protected $primaryKey = 'cod_die';

    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'cod_die',          // Código documento inscripción
        'cod_ins',          // Inscripción relacionada
        'nom_die',          // Nombre del documento
        'tip_die',          // Tipo documental
        'est_die',          // Estado documental
        'obl_die',          // Obligatorio
        'fec_lim_die',      // Fecha límite
        'fec_pre_die',      // Fecha de presentación
        'rut_die',          // Ruta del archivo
        'for_die',          // Formato del archivo
        'tam_die',          // Tamaño del archivo
        'has_die',          // Hash del archivo
        'obs_die',          // Observación
    ];

    protected $casts = [
        'obl_die' => 'boolean', // Obligatorio
        'fec_lim_die' => 'date', // Fecha límite
        'fec_pre_die' => 'date', // Fecha de presentación
        'tam_die' => 'integer', // Tamaño del archivo
        'created_at' => 'datetime', // Fecha de creación
        'updated_at' => 'datetime', // Fecha de actualización
    ];

    protected static function booted(): void
    {
        static::creating(function (DocumentoInscripcionEstudiante $documento) {
            if (! empty($documento->cod_die)) {
                return;
            }

            $ultimo = self::query()
                ->where('cod_die', 'like', 'DIE_%')
                ->orderByDesc('cod_die')
                ->value('cod_die');

            $numero = $ultimo
                ? ((int) str_replace('DIE_', '', $ultimo)) + 1
                : 1;

            $documento->cod_die = 'DIE_' . str_pad((string) $numero, 4, '0', STR_PAD_LEFT);
        });
    }

    public function inscripcion(): BelongsTo
    {
        return $this->belongsTo(InscripcionEstudiante::class, 'cod_ins', 'cod_ins');
    }

    public function getTamanioLegibleAttribute(): string
    {
        if (! $this->tam_die) {
            return 'Sin archivo';
        }

        $bytes = (float) $this->tam_die;
        $unidades = ['B', 'KB', 'MB', 'GB'];
        $indice = 0;

        while ($bytes >= 1024 && $indice < count($unidades) - 1) {
            $bytes /= 1024;
            $indice++;
        }

        return round($bytes, 2) . ' ' . $unidades[$indice];
    }

    public function getEstaVencidoAttribute(): bool
    {
        if (! $this->fec_lim_die) {
            return false;
        }

        if (in_array($this->est_die, ['PRESENTADO', 'VALIDADO', 'NO_APLICA', 'ANULADO'], true)) {
            return false;
        }

        return now()->toDateString() > $this->fec_lim_die->toDateString();
    }

    public function getTieneArchivoAttribute(): bool
    {
        return ! empty($this->rut_die);
    }

    public function scopeDeInscripcion($query, string $codInscripcion)
    {
        return $query->where('cod_ins', $codInscripcion);
    }

    public function scopePorTipo($query, string $tipo)
    {
        return $query->where('tip_die', strtoupper($tipo));
    }

    public function scopePorEstado($query, string $estado)
    {
        return $query->where('est_die', strtoupper($estado));
    }

    public function scopeObligatorios($query)
    {
        return $query->where('obl_die', true);
    }

    public function scopeOpcionales($query)
    {
        return $query->where('obl_die', false);
    }

    public function scopePendientes($query)
    {
        return $query->where('est_die', 'PENDIENTE');
    }

    public function scopePresentados($query)
    {
        return $query->where('est_die', 'PRESENTADO');
    }

    public function scopeObservados($query)
    {
        return $query->where('est_die', 'OBSERVADO');
    }

    public function scopeValidados($query)
    {
        return $query->where('est_die', 'VALIDADO');
    }

    public function scopeNoAplica($query)
    {
        return $query->where('est_die', 'NO_APLICA');
    }

    public function scopeVencidos($query)
    {
        return $query
            ->whereNotNull('fec_lim_die')
            ->where('fec_lim_die', '<', now()->toDateString())
            ->whereNotIn('est_die', ['PRESENTADO', 'VALIDADO', 'NO_APLICA', 'ANULADO']);
    }

    public function estaPendiente(): bool
    {
        return $this->est_die === 'PENDIENTE';
    }

    public function estaPresentado(): bool
    {
        return $this->est_die === 'PRESENTADO';
    }

    public function estaObservado(): bool
    {
        return $this->est_die === 'OBSERVADO';
    }

    public function estaValidado(): bool
    {
        return $this->est_die === 'VALIDADO';
    }

    public function noAplica(): bool
    {
        return $this->est_die === 'NO_APLICA';
    }

    public function estaAnulado(): bool
    {
        return $this->est_die === 'ANULADO';
    }

    public function esObligatorio(): bool
    {
        return (bool) $this->obl_die;
    }

    public function tieneArchivo(): bool
    {
        return ! empty($this->rut_die);
    }
}