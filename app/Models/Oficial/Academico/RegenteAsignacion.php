<?php

namespace App\Models\Oficial\Academico;

use App\Models\Soporte\CodigoInstitucional;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RegenteAsignacion extends Model
{
    protected $table = 'regente_asignaciones';

    protected $primaryKey = 'cod_ras';

    protected string $claveInstitucional = 'cod_ras';

    public $incrementing = false;

    protected $keyType = 'string';

    use CodigoInstitucional;

    protected $fillable = ['cod_gea', 'cod_cur', 'cod_vpe',
        'fii_ras',
        'ffi_ras',
        'est_ras',
        'obs_ras',
    ];

    protected $casts = ['fii_ras' => 'date',
        'ffi_ras' => 'date',
    ];

    public function gestion(): BelongsTo
    {
        return $this->belongsTo(GestionAcademica::class, 'cod_gea', 'cod_gea');
    }

    public function curso(): BelongsTo
    {
        return $this->belongsTo(Curso::class, 'cod_cur', 'cod_cur');
    }

    public function vinculoPersonal(): BelongsTo
    {
        return $this->belongsTo(VinculoPersonal::class, 'cod_vpe', 'cod_vpe');
    }

    public function gestionAcademica(): BelongsTo
    {
        return $this->belongsTo(GestionAcademica::class, 'cod_gea', 'cod_gea');
    }
}
