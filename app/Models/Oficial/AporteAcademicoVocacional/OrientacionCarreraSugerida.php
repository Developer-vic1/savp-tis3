<?php

namespace App\Models\Oficial\AporteAcademicoVocacional;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

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
        'cod_car',
    ];

    protected $casts = [
        'compatibilidad' => 'decimal:2',
        'fortalezas' => 'array',
        'areas_a_fortalecer' => 'array',
        'orden' => 'integer',
        'orientacion_resultado_id' => 'integer',
    ];

    public function resultado(): BelongsTo
    {
        return $this->belongsTo(OrientacionResultado::class, 'orientacion_resultado_id');
    }

    public function orientacionResultado(): BelongsTo
    {
        return $this->belongsTo(OrientacionResultado::class, 'orientacion_resultado_id', 'id');
    }

    public function carrera(): BelongsTo
    {
        return $this->belongsTo(Carrera::class, 'cod_car', 'cod_car');
    }

    public function orientacionOfertaSugeridaRegistros(): HasMany
    {
        return $this->hasMany(OrientacionOfertaSugerida::class, 'sug_oos', 'id');
    }
}
