<?php

namespace App\Models\AulaVirtual;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OrientacionCarreraSugerida extends Model
{
    protected $table = 'orientacion_carreras_sugeridas';

    protected $fillable = [
        'orientacion_resultado_id',
        'carrera',
        'area_profesional',
        'compatibilidad',
        'razon',
        'fortalezas',
        'areas_a_fortalecer',
        'orden',
    ];

    protected $casts = [
        'compatibilidad' => 'decimal:2',
        'fortalezas' => 'array',
        'areas_a_fortalecer' => 'array',
        'orden' => 'integer',
    ];

    public function resultado(): BelongsTo
    {
        return $this->belongsTo(OrientacionResultado::class, 'orientacion_resultado_id');
    }
}
