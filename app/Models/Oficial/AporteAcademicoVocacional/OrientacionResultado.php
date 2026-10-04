<?php

namespace App\Models\Oficial\AporteAcademicoVocacional;

use App\Models\Oficial\Academico\Estudiante;
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
        'rea_ors',
        'inv_ors',
        'art_ors',
        'soc_ors',
        'emp_ors',
        'con_ors',
        'mod_ors',
        'ver_ors',
    ];

    protected $casts = [
        'tecnico_practico' => 'decimal:2',
        'analitico_cientifico' => 'decimal:2',
        'creativo_expresivo' => 'decimal:2',
        'social_comunitario' => 'decimal:2',
        'liderazgo_emprendimiento' => 'decimal:2',
        'organizativo_administrativo' => 'decimal:2',
        'compatibilidad_principal' => 'decimal:2',
        'orientacion_actividad_id' => 'integer',
        'rea_ors' => 'decimal:2',
        'inv_ors' => 'decimal:2',
        'art_ors' => 'decimal:2',
        'soc_ors' => 'decimal:2',
        'emp_ors' => 'decimal:2',
        'con_ors' => 'decimal:2',
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

    public function orientacionActividad(): BelongsTo
    {
        return $this->belongsTo(OrientacionActividad::class, 'orientacion_actividad_id', 'id');
    }

    public function orientacionCarreraSugeridaRegistros(): HasMany
    {
        return $this->hasMany(OrientacionCarreraSugerida::class, 'orientacion_resultado_id', 'id');
    }
}
