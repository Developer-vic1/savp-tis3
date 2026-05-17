<?php

namespace App\Models\AulaVirtual;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EntregaArchivo extends Model
{
    protected $table = 'entrega_archivo';
    protected $primaryKey = 'cod_ent_arc';

    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'cod_ent_arc', // Código único del archivo de entrega
        'cod_ent', // Entrega a la que pertenece el archivo

        'nom_arc', // Nombre visible del archivo
        'rut_arc', // Ruta interna del archivo almacenado
        'mime_arc', // Tipo MIME del archivo para validación y previsualización
        'tam_arc', // Tamaño del archivo en bytes

        'est_arc', // Estado: ACTIVO o ANULADO
    ];

    protected $casts = [
        'tam_arc' => 'integer',
    ];

    protected static function booted(): void
    {
        static::creating(function (EntregaArchivo $entregaArchivo) {
            if (! $entregaArchivo->cod_ent_arc) {
                $ultimoCodigo = self::where('cod_ent_arc', 'like', 'ENTA_%')
                    ->orderByDesc('cod_ent_arc')
                    ->value('cod_ent_arc');

                $numero = $ultimoCodigo
                    ? ((int) str_replace('ENTA_', '', $ultimoCodigo)) + 1
                    : 1;

                $entregaArchivo->cod_ent_arc = 'ENTA_' . str_pad((string) $numero, 4, '0', STR_PAD_LEFT);
            }
        });
    }

    public function entrega(): BelongsTo
    {
        return $this->belongsTo(EntregaTarea::class, 'cod_ent', 'cod_ent');
    }

    public function scopeActivos($query)
    {
        return $query->where('est_arc', 'ACTIVO');
    }

    public function scopeAnulados($query)
    {
        return $query->where('est_arc', 'ANULADO');
    }

    public function estaActivo(): bool
    {
        return $this->est_arc === 'ACTIVO';
    }

    public function estaAnulado(): bool
    {
        return $this->est_arc === 'ANULADO';
    }

    public function anular(): void
    {
        $this->forceFill([
            'est_arc' => 'ANULADO',
        ])->save();
    }
}