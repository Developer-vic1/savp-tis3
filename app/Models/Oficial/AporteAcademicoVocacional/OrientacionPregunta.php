<?php

namespace App\Models\Oficial\AporteAcademicoVocacional;

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
        'est_pre',
    ];

    protected $casts = [
        'orden' => 'integer',
        'visible' => 'boolean',
    ];

    public function respuestas(): HasMany
    {
        return $this->hasMany(OrientacionRespuesta::class, 'orientacion_pregunta_id');
    }

    public function instrumentoPreguntaRegistros(): HasMany
    {
        return $this->hasMany(InstrumentoPregunta::class, 'pre_ipr', 'id');
    }
}
