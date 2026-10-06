<?php

declare(strict_types=1);

namespace App\Models\Oficial\Academico;

use App\Models\Oficial\Sistema\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/** Modelo único de la tabla oficial; atributos y relaciones del contrato canónico. */
class UserStatusLogs extends Model
{
    protected $table = 'user_status_logs';

    protected $primaryKey = 'id';

    public const UPDATED_AT = null;

    protected $fillable = [
        'cod_usu',
        'ant_usl',
        'nue_usl',
        'mot_usl',
        'cod_usu_admin',
        'fec_usl',
    ];

    public function usuario(): BelongsTo
    {
        return $this->belongsTo(User::class, 'cod_usu', 'cod_usu');
    }

    public function usuarioAdministrador(): BelongsTo
    {
        return $this->belongsTo(User::class, 'cod_usu_admin', 'cod_usu');
    }

    protected $casts = [
        'id' => 'integer',
        'fec_usl' => 'datetime',
    ];
}
