<?php

declare(strict_types=1);

namespace App\Models\Oficial\Academico;

use App\Support\Modelos\CodigoInstitucional;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/** Entidad del contrato oficial; conserva las claves de sus madres. */
class ExpedienteTraslado extends Model
{
    use CodigoInstitucional;

    protected $table = 'expediente_traslado';

    protected $primaryKey = 'cod_ext';

    public $incrementing = false;

    protected $keyType = 'string';

    protected $fillable = [
        'cod_est',
        'cod_ipe',
        'tip_ext',
        'ref_ext',
        'fec_ext',
        'est_ext',
        'obs_ext',
    ];

    protected $casts = [
        'fec_ext' => 'date',
    ];

    public function estudiante(): BelongsTo
    {
        return $this->belongsTo(Estudiante::class, 'cod_est', 'cod_est');
    }

    public function institucionProcedencia(): BelongsTo
    {
        return $this->belongsTo(InstitucionProcedencia::class, 'cod_ipe', 'cod_ipe');
    }

    public function documentoTrasladoRegistros(): HasMany
    {
        return $this->hasMany(DocumentoTraslado::class, 'cod_ext', 'cod_ext');
    }

    public function notaTrasladoRegistros(): HasMany
    {
        return $this->hasMany(NotaTraslado::class, 'cod_ext', 'cod_ext');
    }
}
