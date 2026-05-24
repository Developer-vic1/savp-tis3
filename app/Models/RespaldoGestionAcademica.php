<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RespaldoGestionAcademica extends Model
{
    protected $table = 'respaldo_gestion_academica';
    protected $primaryKey = 'cod_rga';

    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'cod_rga', //Codigo unico del respaldo academico
        'cod_gea', //Codigo de la gestion academica
        'tip_rga', //Tipo archivo de respaldo academico
        'for_rga', //Formato archivo de respaldo academico
        'rut_rga', //Ruta del respaldo academico
        'tam_rga', //Tamaño del respaldo academico
        'has_rga', //Hash del respaldo academico
        'obs_rga', //Observacion del respaldo academico
        'fec_rga', //Fecha del respaldo academico
        'est_rga', //Estado del respaldo academico
    ];

    protected $casts = [
        'tam_rga' => 'integer',
        'fec_rga' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    protected static function booted(): void
    {
        static::creating(function (RespaldoGestionAcademica $respaldo) {
            if (! empty($respaldo->cod_rga)) {
                return;
            }

            $ultimo = self::query()
                ->where('cod_rga', 'like', 'RGA_%')
                ->orderByDesc('cod_rga')
                ->value('cod_rga');

            $numero = $ultimo
                ? ((int) str_replace('RGA_', '', $ultimo)) + 1
                : 1;

            $respaldo->cod_rga = 'RGA_' . str_pad((string) $numero, 4, '0', STR_PAD_LEFT);
        });
    }

    public function gestionAcademica(): BelongsTo
    {
        return $this->belongsTo(GestionAcademica::class, 'cod_gea', 'cod_gea');
    }

    public function getNombreVisibleAttribute(): string
    {
        $anio = $this->gestionAcademica?->ani_gea ?? 'Sin gestión';
        $tipo = ucfirst(strtolower($this->tip_rga ?? 'respaldo'));
        $fecha = $this->fec_rga?->format('d/m/Y H:i') ?? 'sin fecha';

        return "Respaldo académico {$tipo} - Gestión {$anio} - {$fecha}";
    }

    public function getFormatoArchivoAttribute(): string
    {
        return strtoupper($this->for_rga ?? 'ZIP');
    }

    public function getTamanioLegibleAttribute(): string
    {
        if (! $this->tam_rga) {
            return 'Sin tamaño';
        }

        $bytes = (float) $this->tam_rga;
        $unidades = ['B', 'KB', 'MB', 'GB'];
        $indice = 0;

        while ($bytes >= 1024 && $indice < count($unidades) - 1) {
            $bytes /= 1024;
            $indice++;
        }

        return round($bytes, 2) . ' ' . $unidades[$indice];
    }

    public function scopeDeGestion($query, string $codGestion)
    {
        return $query->where('cod_gea', $codGestion);
    }

    public function scopeGenerados($query)
    {
        return $query->where('est_rga', 'GENERADO');
    }

    public function scopeValidados($query)
    {
        return $query->where('est_rga', 'VALIDADO');
    }

    public function scopeArchivados($query)
    {
        return $query->where('est_rga', 'ARCHIVADO');
    }

    public function scopePorTipo($query, string $tipo)
    {
        return $query->where('tip_rga', strtoupper($tipo));
    }

    public function scopePorFormato($query, string $formato)
    {
        return $query->where('for_rga', strtoupper($formato));
    }

    public function estaValidado(): bool
    {
        return $this->est_rga === 'VALIDADO';
    }

    public function estaArchivado(): bool
    {
        return $this->est_rga === 'ARCHIVADO';
    }

    public function esCierre(): bool
    {
        return $this->tip_rga === 'CIERRE';
    }

    public function esZip(): bool
    {
        return $this->for_rga === 'ZIP';
    }
}