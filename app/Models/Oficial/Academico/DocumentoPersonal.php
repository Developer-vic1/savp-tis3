<?php

declare(strict_types=1);

namespace App\Models\Oficial\Academico;

use App\Models\Soporte\CodigoInstitucional;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/** Entidad del contrato oficial; conserva las claves de sus madres. */
class DocumentoPersonal extends Model
{
    use CodigoInstitucional;

    protected $table = 'documento_personal';

    protected $primaryKey = 'cod_dpe';

    protected $hidden = ['rut_dpe', 'has_dpe'];

    public $incrementing = false;

    protected $keyType = 'string';

    protected $fillable = [
        'cod_pin',
        'cod_tdp',
        'cod_vpe',
        'num_dpe',
        'emi_dpe',
        'ven_dpe',
        'rut_dpe',
        'mim_dpe',
        'tam_dpe',
        'has_dpe',
        'est_dpe',
        'val_dpe',
        'fec_dpe',
        'obs_dpe',
    ];

    protected $casts = [
        'emi_dpe' => 'date',
        'ven_dpe' => 'date',
        'tam_dpe' => 'integer',
        'fec_dpe' => 'datetime',
    ];

    public function personalInstitucional(): BelongsTo
    {
        return $this->belongsTo(PersonalInstitucional::class, 'cod_pin', 'cod_pin');
    }

    public function tipoDocumentoPersonal(): BelongsTo
    {
        return $this->belongsTo(TipoDocumentoPersonal::class, 'cod_tdp', 'cod_tdp');
    }

    public function vinculoPersonal(): BelongsTo
    {
        return $this->belongsTo(VinculoPersonal::class, 'cod_vpe', 'cod_vpe');
    }

    public function users(): BelongsTo
    {
        return $this->belongsTo(User::class, 'val_dpe', 'cod_usu');
    }

    public function formacionDocenteRegistros(): HasMany
    {
        return $this->hasMany(FormacionDocente::class, 'cod_dpe', 'cod_dpe');
    }

    public function experienciaDocenteRegistros(): HasMany
    {
        return $this->hasMany(ExperienciaDocente::class, 'cod_dpe', 'cod_dpe');
    }

    public function capacitacionDocenteRegistros(): HasMany
    {
        return $this->hasMany(CapacitacionDocente::class, 'cod_dpe', 'cod_dpe');
    }
}
