<?php

declare(strict_types=1);

namespace App\Models\Oficial\AulaVirtual;

use App\Models\Oficial\Academico\Estudiante;
use App\Models\Soporte\CodigoInstitucional;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/** Entidad del contrato oficial; conserva las claves de sus madres. */
class RegistroActividadClase extends Model
{
    use CodigoInstitucional;

    protected $table = 'registro_actividad_clase';

    protected $primaryKey = 'cod_rac';

    public $incrementing = false;

    protected $keyType = 'string';

    public const UPDATED_AT = null;

    protected $fillable = [
        'cod_cla',
        'cod_est',
        'cod_usu',
        'tip_rac',
        'pub_rac',
        'mat_rac',
        'tar_rac',
        'cue_rac',
        'for_rac',
        'men_rac',
        'fec_rac',
        'met_rac',
    ];

    protected $casts = [
        'fec_rac' => 'datetime',
        'met_rac' => 'array',
    ];

    public function claseVirtual(): BelongsTo
    {
        return $this->belongsTo(ClaseVirtual::class, 'cod_cla', 'cod_cla');
    }

    public function estudiante(): BelongsTo
    {
        return $this->belongsTo(Estudiante::class, 'cod_est', 'cod_est');
    }

    public function users(): BelongsTo
    {
        return $this->belongsTo(User::class, 'cod_usu', 'cod_usu');
    }

    public function publicacionClase(): BelongsTo
    {
        return $this->belongsTo(PublicacionClase::class, 'pub_rac', 'cod_pub');
    }

    public function materialClase(): BelongsTo
    {
        return $this->belongsTo(MaterialClase::class, 'mat_rac', 'cod_mat');
    }

    public function tarea(): BelongsTo
    {
        return $this->belongsTo(Tarea::class, 'tar_rac', 'cod_tar');
    }

    public function cuestionario(): BelongsTo
    {
        return $this->belongsTo(Cuestionario::class, 'cue_rac', 'cod_cue');
    }

    public function foroClase(): BelongsTo
    {
        return $this->belongsTo(ForoClase::class, 'for_rac', 'cod_for');
    }

    public function foroMensaje(): BelongsTo
    {
        return $this->belongsTo(ForoMensaje::class, 'men_rac', 'cod_fme');
    }
}
