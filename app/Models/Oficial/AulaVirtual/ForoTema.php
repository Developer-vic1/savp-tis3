<?php

declare(strict_types=1);

namespace App\Models\Oficial\AulaVirtual;

use App\Support\Modelos\CodigoInstitucional;
use App\Models\Oficial\Sistema\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/** Entidad del contrato oficial; conserva las claves de sus madres. */
class ForoTema extends Model
{
    use CodigoInstitucional;

    protected $table = 'foro_tema';

    protected $primaryKey = 'cod_fte';

    public $incrementing = false;

    protected $keyType = 'string';

    protected $fillable = [
        'cod_for',
        'cod_usu',
        'tit_fte',
        'fec_fte',
        'est_fte',
    ];

    protected $casts = [
        'fec_fte' => 'datetime',
    ];

    public function foroClase(): BelongsTo
    {
        return $this->belongsTo(ForoClase::class, 'cod_for', 'cod_for');
    }

    public function users(): BelongsTo
    {
        return $this->belongsTo(User::class, 'cod_usu', 'cod_usu');
    }

    public function foroMensajeRegistros(): HasMany
    {
        return $this->hasMany(ForoMensaje::class, 'cod_fte', 'cod_fte');
    }
}
