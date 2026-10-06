<?php

declare(strict_types=1);

namespace App\Models\Oficial\Academico;

use App\Support\Modelos\CodigoInstitucional;
use App\Models\Oficial\Sistema\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/** Entidad del contrato oficial; conserva las claves de sus madres. */
class ResultadoAnual extends Model
{
    use CodigoInstitucional;

    protected $table = 'resultado_anual';

    protected $primaryKey = 'cod_ran';

    public $incrementing = false;

    protected $keyType = 'string';

    protected $fillable = [
        'cod_ins',
        'res_ran',
        'fec_ran',
        'cod_usu',
        'ref_ran',
        'obs_ran',
        'est_ran',
    ];

    protected $casts = [
        'fec_ran' => 'date',
    ];

    public function inscripcionEstudiante(): BelongsTo
    {
        return $this->belongsTo(InscripcionEstudiante::class, 'cod_ins', 'cod_ins');
    }

    public function users(): BelongsTo
    {
        return $this->belongsTo(User::class, 'cod_usu', 'cod_usu');
    }
}
