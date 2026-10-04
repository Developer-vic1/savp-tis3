<?php

declare(strict_types=1);

namespace App\Models\Oficial\Academico;

use App\Models\Soporte\CodigoInstitucional;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

/** Modelo del contrato canónico; atributos históricos redundantes permanecen en Legado. */
class PersonalInstitucional extends Model
{
    use CodigoInstitucional;

    protected $table = 'personal_institucional';

    protected $primaryKey = 'cod_pin';

    public $incrementing = false;

    protected $keyType = 'string';

    protected $fillable = [
        'cod_per',
        'est_pin',
    ];

    protected $casts = [
    ];

    public function persona(): BelongsTo
    {
        return $this->belongsTo(Persona::class, 'cod_per', 'cod_per');
    }

    public function vinculoPersonalRegistros(): HasMany
    {
        return $this->hasMany(VinculoPersonal::class, 'cod_pin', 'cod_pin');
    }

    public function documentoPersonalRegistros(): HasMany
    {
        return $this->hasMany(DocumentoPersonal::class, 'cod_pin', 'cod_pin');
    }

    public function docenteRegistros(): HasOne
    {
        return $this->hasOne(Docente::class, 'cod_pin', 'cod_pin');
    }
}
