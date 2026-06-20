<?php

namespace App\Models\AulaVirtual;

use App\Models\Estudiante;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class OrientacionResultado extends Model
{
    protected $table = 'orientacion_resultados';

    protected $fillable = [
        'orientacion_actividad_id',
        'cod_est',
        'tecnico_practico',
        'analitico_cientifico',
        'creativo_expresivo',
        'social_comunitario',
        'liderazgo_emprendimiento',
        'organizativo_administrativo',
        'perfil_predominante',
        'interpretacion',
        'compatibilidad_principal',
        'estado',
    ];

    protected $casts = [
        'tecnico_practico' => 'decimal:2',
        'analitico_cientifico' => 'decimal:2',
        'creativo_expresivo' => 'decimal:2',
        'social_comunitario' => 'decimal:2',
        'liderazgo_emprendimiento' => 'decimal:2',
        'organizativo_administrativo' => 'decimal:2',
        'compatibilidad_principal' => 'decimal:2',
    ];

    public function actividad(): BelongsTo
    {
        return $this->belongsTo(OrientacionActividad::class, 'orientacion_actividad_id');
    }

    public function estudiante(): BelongsTo
    {
        return $this->belongsTo(Estudiante::class, 'cod_est', 'cod_est');
    }

    public function carreras(): HasMany
    {
        return $this->hasMany(OrientacionCarreraSugerida::class, 'orientacion_resultado_id')->orderBy('orden');
    }
}
