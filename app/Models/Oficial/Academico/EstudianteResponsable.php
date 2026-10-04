<?php

declare(strict_types=1);

namespace App\Models\Oficial\Academico;

use App\Models\Soporte\CodigoInstitucional;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/** Entidad del contrato oficial; conserva las claves de sus madres. */
class EstudianteResponsable extends Model
{
    use CodigoInstitucional;

    protected $table = 'estudiante_responsable';

    protected $primaryKey = 'cod_ere';

    public $incrementing = false;

    protected $keyType = 'string';

    protected $fillable = [
        'cod_est',
        'cod_per',
        'par_ere',
        'pri_ere',
        'aut_ere',
        'con_ere',
        'fii_ere',
        'ffi_ere',
        'est_ere',
        'obs_ere',
    ];

    protected $casts = [
        'pri_ere' => 'boolean',
        'aut_ere' => 'boolean',
        'con_ere' => 'boolean',
        'fii_ere' => 'date',
        'ffi_ere' => 'date',
    ];

    public function estudiante(): BelongsTo
    {
        return $this->belongsTo(Estudiante::class, 'cod_est', 'cod_est');
    }

    public function persona(): BelongsTo
    {
        return $this->belongsTo(Persona::class, 'cod_per', 'cod_per');
    }
}
