<?php

namespace App\Models\Oficial\AulaVirtual;

use App\Support\Modelos\CodigoInstitucional;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EntregaArchivo extends Model
{
    use CodigoInstitucional;

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
        'has_arc',
    ];

    protected $casts = [
        'tam_arc' => 'integer',
    ];

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

    public function entregaTarea(): BelongsTo
    {
        return $this->belongsTo(EntregaTarea::class, 'cod_ent', 'cod_ent');
    }
}
