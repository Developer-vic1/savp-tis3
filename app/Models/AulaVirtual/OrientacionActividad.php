<?php

namespace App\Models\AulaVirtual;

use App\Models\Estudiante;
use App\Models\GestionAcademica;
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
    ];

    protected $casts = [
        'avance' => 'integer',
        'iniciado_at' => 'datetime',
        'finalizado_at' => 'datetime',
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
}
