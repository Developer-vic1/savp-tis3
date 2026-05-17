<?php

namespace App\Models\AulaVirtual;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TareaMaterial extends Model
{
    protected $table = 'tarea_material';
    protected $primaryKey = 'cod_tar_mat';

    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'cod_tar_mat', // Código único del material de la tarea
        'cod_tar', // Tarea a la que pertenece el material

        'nom_tar_mat', // Nombre visible del material
        'tip_tar_mat', // Tipo de material: ARCHIVO, ENLACE, PDF, VIDEO, IMAGEN, DOCUMENTO u OTRO
        'rut_tar_mat', // Ruta interna del archivo almacenado
        'url_tar_mat', // Enlace externo del material, si aplica
        'mime_tar_mat', // Tipo MIME del archivo para validación y previsualización
        'tam_tar_mat', // Tamaño del archivo en bytes

        'est_tar_mat', // Estado: ACTIVO, OCULTO o ANULADO
    ];

    protected $casts = [
        'tam_tar_mat' => 'integer',
    ];

    protected static function booted(): void
    {
        static::creating(function (TareaMaterial $tareaMaterial) {
            if (! $tareaMaterial->cod_tar_mat) {
                $ultimoCodigo = self::where('cod_tar_mat', 'like', 'TARM_%')
                    ->orderByDesc('cod_tar_mat')
                    ->value('cod_tar_mat');

                $numero = $ultimoCodigo
                    ? ((int) str_replace('TARM_', '', $ultimoCodigo)) + 1
                    : 1;

                $tareaMaterial->cod_tar_mat = 'TARM_' . str_pad((string) $numero, 4, '0', STR_PAD_LEFT);
            }
        });
    }

    public function tarea(): BelongsTo
    {
        return $this->belongsTo(Tarea::class, 'cod_tar', 'cod_tar');
    }

    public function scopeActivos($query)
    {
        return $query->where('est_tar_mat', 'ACTIVO');
    }

    public function scopeOcultos($query)
    {
        return $query->where('est_tar_mat', 'OCULTO');
    }

    public function scopeAnulados($query)
    {
        return $query->where('est_tar_mat', 'ANULADO');
    }

    public function scopePorTipo($query, string $tipo)
    {
        return $query->where('tip_tar_mat', $tipo);
    }

    public function estaActivo(): bool
    {
        return $this->est_tar_mat === 'ACTIVO';
    }

    public function estaOculto(): bool
    {
        return $this->est_tar_mat === 'OCULTO';
    }

    public function estaAnulado(): bool
    {
        return $this->est_tar_mat === 'ANULADO';
    }

    public function esEnlace(): bool
    {
        return $this->tip_tar_mat === 'ENLACE';
    }

    public function esArchivo(): bool
    {
        return ! empty($this->rut_tar_mat);
    }
}
