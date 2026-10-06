<?php

namespace App\Models\Oficial\Sistema;

use App\Support\Modelos\CodigoInstitucional;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class NotificacionUsuario extends Model
{
    use CodigoInstitucional, \App\Support\Modelos\FechasConZonaHoraria;

    protected $table = 'notificacion_usuario';
    protected $primaryKey = 'cod_nus';
    public $incrementing = false;
    protected $keyType = 'string';
    protected $fillable = ['cod_not', 'cod_usu', 'leida_en', 'archivada_en'];

    protected function casts(): array
    {
        return ['leida_en' => 'immutable_datetime', 'archivada_en' => 'immutable_datetime'];
    }

    public function notificacion(): BelongsTo
    {
        return $this->belongsTo(Notificacion::class, 'cod_not', 'cod_not');
    }

    public function usuario(): BelongsTo
    {
        return $this->belongsTo(User::class, 'cod_usu', 'cod_usu');
    }
}
