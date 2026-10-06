<?php

namespace App\Models\Oficial\Sistema;

use App\Models\Oficial\Academico\GestionAcademica;
use App\Support\Modelos\CodigoInstitucional;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Notificacion extends Model
{
    use CodigoInstitucional, \App\Support\Modelos\FechasConZonaHoraria;

    protected $table = 'notificacion';
    protected $primaryKey = 'cod_not';
    public $incrementing = false;
    protected $keyType = 'string';
    protected $fillable = ['clave_evento', 'origen', 'tipo', 'titulo', 'mensaje', 'est_not', 'cod_usu_emisor', 'cod_gea', 'permiso', 'ruta', 'publicada_en', 'vence_en'];
    protected $attributes = ['est_not' => 'ACTIVA', 'tipo' => 'INFORMACION'];

    protected function casts(): array
    {
        return ['publicada_en' => 'immutable_datetime', 'vence_en' => 'immutable_datetime'];
    }

    public function destinatarios(): HasMany
    {
        return $this->hasMany(NotificacionUsuario::class, 'cod_not', 'cod_not');
    }

    public function emisor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'cod_usu_emisor', 'cod_usu');
    }

    public function gestion(): BelongsTo
    {
        return $this->belongsTo(GestionAcademica::class, 'cod_gea', 'cod_gea');
    }
}
