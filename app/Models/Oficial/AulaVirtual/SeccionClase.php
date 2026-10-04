<?php

declare(strict_types=1);

namespace App\Models\Oficial\AulaVirtual;

use App\Models\Soporte\CodigoInstitucional;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/** Entidad del contrato oficial; conserva las claves de sus madres. */
class SeccionClase extends Model
{
    use CodigoInstitucional;

    protected $table = 'seccion_clase';

    protected $primaryKey = 'cod_sec';

    public $incrementing = false;

    protected $keyType = 'string';

    protected $fillable = [
        'cod_cla',
        'nom_sec',
        'des_sec',
        'ord_sec',
        'vis_sec',
        'est_sec',
    ];

    protected $casts = [
        'ord_sec' => 'integer',
        'vis_sec' => 'boolean',
    ];

    public function claseVirtual(): BelongsTo
    {
        return $this->belongsTo(ClaseVirtual::class, 'cod_cla', 'cod_cla');
    }

    public function publicacionClaseRegistros(): HasMany
    {
        return $this->hasMany(PublicacionClase::class, 'cod_sec', 'cod_sec');
    }

    public function materiales(): HasMany
    {
        return $this->hasMany(MaterialClase::class, 'cod_sec', 'cod_sec');
    }

    public function tareas(): HasMany
    {
        return $this->hasMany(Tarea::class, 'cod_sec', 'cod_sec');
    }

    public function cuestionarios(): HasMany
    {
        return $this->hasMany(Cuestionario::class, 'cod_sec', 'cod_sec');
    }

    public function foros(): HasMany
    {
        return $this->hasMany(ForoClase::class, 'cod_sec', 'cod_sec');
    }
}
