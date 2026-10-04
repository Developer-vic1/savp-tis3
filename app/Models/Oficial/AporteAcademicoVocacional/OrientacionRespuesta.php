<?php

namespace App\Models\Oficial\AporteAcademicoVocacional;

use App\Models\Oficial\Academico\Estudiante;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OrientacionRespuesta extends Model
{
    protected $table = 'orientacion_respuestas';

    protected $fillable = [
        'orientacion_actividad_id',
        'orientacion_pregunta_id',
        'cod_est',
        'valor_likert',
        'cod_ipr',
    ];

    protected $casts = [
        'valor_likert' => 'integer',
        'orientacion_actividad_id' => 'integer',
    ];

    public function actividad(): BelongsTo
    {
        return $this->belongsTo(OrientacionActividad::class, 'orientacion_actividad_id');
    }

    public function pregunta(): BelongsTo
    {
        return $this->belongsTo(OrientacionPregunta::class, 'orientacion_pregunta_id');
    }

    public function estudiante(): BelongsTo
    {
        return $this->belongsTo(Estudiante::class, 'cod_est', 'cod_est');
    }

    public function orientacionActividad(): BelongsTo
    {
        return $this->belongsTo(OrientacionActividad::class, 'orientacion_actividad_id', 'id');
    }

    public function instrumentoPregunta(): BelongsTo
    {
        return $this->belongsTo(InstrumentoPregunta::class, 'cod_ipr', 'cod_ipr');
    }
}
