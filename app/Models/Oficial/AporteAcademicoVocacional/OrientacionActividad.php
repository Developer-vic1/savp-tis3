<?php

namespace App\Models\Oficial\AporteAcademicoVocacional;

use App\Models\Oficial\Academico\Estudiante;
use App\Models\Oficial\Academico\GestionAcademica;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class OrientacionActividad extends Model
{
    protected $table = 'orientacion_actividades';

    protected $fillable = [
        'cod_est',
        'cod_gea',
        'estado',
        'avance',
        'iniciado_at',
        'finalizado_at',
        'revisado_por',
        'riasec_public',
        'riasec_score',
        'analysis_snapshot',
        'analysis_completed_at',
        'riasec_input_hash',
        'cod_ior',
        'int_oac',
        'rev_oac',
    ];

    protected $casts = [
        'avance' => 'integer',
        'iniciado_at' => 'datetime',
        'finalizado_at' => 'datetime',
        'riasec_public' => 'array',
        'riasec_score' => 'array',
        'analysis_snapshot' => 'array',
        'analysis_completed_at' => 'datetime',
        'int_oac' => 'integer',
        'rev_oac' => 'datetime',
    ];

    public function estudiante(): BelongsTo
    {
        return $this->belongsTo(Estudiante::class, 'cod_est', 'cod_est');
    }

    public function gestionAcademica(): BelongsTo
    {
        return $this->belongsTo(GestionAcademica::class, 'cod_gea', 'cod_gea');
    }

    public function revisor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'revisado_por', 'cod_usu');
    }

    public function respuestas(): HasMany
    {
        return $this->hasMany(OrientacionRespuesta::class, 'orientacion_actividad_id');
    }

    public function resultado(): HasOne
    {
        return $this->hasOne(OrientacionResultado::class, 'orientacion_actividad_id');
    }

    public function instrumentoOrientacion(): BelongsTo
    {
        return $this->belongsTo(InstrumentoOrientacion::class, 'cod_ior', 'cod_ior');
    }

    public function users(): BelongsTo
    {
        return $this->belongsTo(User::class, 'revisado_por', 'cod_usu');
    }

    public function orientacionRespuestaRegistros(): HasMany
    {
        return $this->hasMany(OrientacionRespuesta::class, 'orientacion_actividad_id', 'id');
    }

    public function orientacionResultadoRegistros(): HasOne
    {
        return $this->hasOne(OrientacionResultado::class, 'orientacion_actividad_id', 'id');
    }
}
