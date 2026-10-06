<?php

declare(strict_types=1);

namespace App\Models\Oficial\Academico;

use App\Support\Modelos\CodigoInstitucional;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/** Entidad del contrato oficial; conserva las claves de sus madres. */
class Aula extends Model
{
    use CodigoInstitucional;

    protected $table = 'aula';

    protected $primaryKey = 'cod_aul';

    public $incrementing = false;

    protected $keyType = 'string';

    protected $fillable = [
        'nom_aul',
        'tip_aul',
        'ubi_aul',
        'cap_aul',
        'est_aul',
    ];

    protected $casts = [
        'cap_aul' => 'integer',
    ];

    public function horarioDetalleRegistros(): HasMany
    {
        return $this->hasMany(HorarioDetalle::class, 'cod_aul', 'cod_aul');
    }
}
