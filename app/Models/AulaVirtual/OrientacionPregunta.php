<?php

namespace App\Models\AulaVirtual;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class OrientacionPregunta extends Model
{
    protected $table = 'orientacion_preguntas';

    protected $fillable = [
        'codigo',
        'dimension',
        'texto',
        'tipo',
        'orden',
        'visible',
    ];

    protected $casts = [
        'orden' => 'integer',
        'visible' => 'boolean',
    ];

    public function respuestas(): HasMany
    {
        return $this->hasMany(OrientacionRespuesta::class, 'orientacion_pregunta_id');
    }
}
