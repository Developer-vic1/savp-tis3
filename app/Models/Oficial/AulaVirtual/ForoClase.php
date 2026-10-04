<?php

declare(strict_types=1);

namespace App\Models\Oficial\AulaVirtual;

use App\Models\Oficial\Academico\Docente;
use App\Models\Soporte\CodigoInstitucional;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/** Entidad del contrato oficial; conserva las claves de sus madres. */
class ForoClase extends Model
{
    use CodigoInstitucional;

    protected $table = 'foro_clase';

    protected $primaryKey = 'cod_for';

    public $incrementing = false;

    protected $keyType = 'string';

    protected $fillable = [
        'cod_cla',
        'cod_sec',
        'cod_doc',
        'tit_for',
        'des_for',
        'ape_for',
        'cie_for',
        'est_for',
    ];

    protected $casts = [
        'ape_for' => 'datetime',
        'cie_for' => 'datetime',
    ];

    public function claseVirtual(): BelongsTo
    {
        return $this->belongsTo(ClaseVirtual::class, 'cod_cla', 'cod_cla');
    }

    public function docente(): BelongsTo
    {
        return $this->belongsTo(Docente::class, 'cod_doc', 'cod_doc');
    }

    public function foroTemaRegistros(): HasMany
    {
        return $this->hasMany(ForoTema::class, 'cod_for', 'cod_for');
    }

    public function registroActividadClaseRegistros(): HasMany
    {
        return $this->hasMany(RegistroActividadClase::class, 'for_rac', 'cod_for');
    }

    /** La FK compuesta en PostgreSQL garantiza también el contexto de clase. */
    public function seccionClase(): BelongsTo
    {
        return $this->belongsTo(SeccionClase::class, 'cod_sec', 'cod_sec');
    }
}
