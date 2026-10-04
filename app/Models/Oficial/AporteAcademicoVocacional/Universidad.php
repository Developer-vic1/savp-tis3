<?php

declare(strict_types=1);

namespace App\Models\Oficial\AporteAcademicoVocacional;

use App\Models\Soporte\CodigoInstitucional;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/** Entidad del contrato oficial; conserva las claves de sus madres. */
class Universidad extends Model
{
    use CodigoInstitucional;

    protected $table = 'universidad';

    protected $primaryKey = 'cod_uni';

    public $incrementing = false;

    protected $keyType = 'string';

    protected $fillable = [
        'nom_uni',
        'sig_uni',
        'tip_uni',
        'pai_uni',
        'web_uni',
        'est_uni',
    ];

    protected $casts = [
    ];

    public function sedeUniversidadRegistros(): HasMany
    {
        return $this->hasMany(SedeUniversidad::class, 'cod_uni', 'cod_uni');
    }

    public function recursoFuenteRegistros(): HasMany
    {
        return $this->hasMany(RecursoFuente::class, 'cod_uni', 'cod_uni');
    }
}
