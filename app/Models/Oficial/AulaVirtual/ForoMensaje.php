<?php

declare(strict_types=1);

namespace App\Models\Oficial\AulaVirtual;

use App\Models\Soporte\CodigoInstitucional;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/** Entidad del contrato oficial; conserva las claves de sus madres. */
class ForoMensaje extends Model
{
    use CodigoInstitucional;

    protected $table = 'foro_mensaje';

    protected $primaryKey = 'cod_fme';

    public $incrementing = false;

    protected $keyType = 'string';

    protected $fillable = [
        'cod_fte',
        'cod_usu',
        'pad_fme',
        'con_fme',
        'fec_fme',
        'est_fme',
    ];

    protected $casts = [
        'fec_fme' => 'datetime',
    ];

    public function foroTema(): BelongsTo
    {
        return $this->belongsTo(ForoTema::class, 'cod_fte', 'cod_fte');
    }

    public function users(): BelongsTo
    {
        return $this->belongsTo(User::class, 'cod_usu', 'cod_usu');
    }

    public function foroMensaje(): BelongsTo
    {
        return $this->belongsTo(ForoMensaje::class, 'pad_fme', 'cod_fme');
    }

    public function foroMensajeRegistros(): HasMany
    {
        return $this->hasMany(ForoMensaje::class, 'pad_fme', 'cod_fme');
    }

    public function foroArchivoRegistros(): HasMany
    {
        return $this->hasMany(ForoArchivo::class, 'cod_fme', 'cod_fme');
    }

    public function registroActividadClaseRegistros(): HasMany
    {
        return $this->hasMany(RegistroActividadClase::class, 'men_rac', 'cod_fme');
    }
}
