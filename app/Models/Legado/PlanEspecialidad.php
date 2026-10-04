<?php

namespace App\Models\Legado;

use App\Models\Curso;
use App\Models\Docente;
use App\Models\EspecialidadTecnica;
use App\Models\GestionAcademica;
use App\Models\Oficial\Academico\Calificacion;
use App\Models\Oficial\Academico\GrupoAcademico;
use App\Models\Oficial\Academico\HorarioDetalle;
use App\Models\Oficial\AulaVirtual\ClaseVirtual;
use App\Models\Paralelo;
use App\Models\Turno;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PlanEspecialidad extends Model
{
    protected $table = 'plan_especialidad';

    protected $primaryKey = 'cod_pes';

    public $incrementing = false;

    protected $keyType = 'string';

    protected $fillable = [
        'cod_pes',
        'cod_esp',
        'cod_doc',
        'cod_cur',
        'cod_par',
        'cod_tur',
        'cod_gea',
        'hor_pes',
        'est_pes',
        'cod_gac',
        'fii_pes',
        'ffi_pes',
    ];

    protected static function booted(): void
    {
        static::creating(function ($plan) {

            if (! $plan->cod_pes) {
                $plan->cod_pes = 'PES_'.strtoupper(bin2hex(random_bytes(8)));
            }
        });
    }

    public function especialidad()
    {
        return $this->belongsTo(EspecialidadTecnica::class, 'cod_esp', 'cod_esp');
    }

    public function docente()
    {
        return $this->belongsTo(Docente::class, 'cod_doc', 'cod_doc');
    }

    public function curso()
    {
        return $this->belongsTo(Curso::class, 'cod_cur', 'cod_cur');
    }

    public function paralelo()
    {
        return $this->belongsTo(Paralelo::class, 'cod_par', 'cod_par');
    }

    public function turno()
    {
        return $this->belongsTo(Turno::class, 'cod_tur', 'cod_tur');
    }

    public function gestionAcademica()
    {
        return $this->belongsTo(GestionAcademica::class, 'cod_gea', 'cod_gea');
    }

    protected $casts = [
        'hor_pes' => 'decimal:2',
        'fii_pes' => 'date',
        'ffi_pes' => 'date',
    ];

    public function especialidadTecnica(): BelongsTo
    {
        return $this->belongsTo(\App\Models\Oficial\Academico\EspecialidadTecnica::class, 'cod_esp', 'cod_esp');
    }

    public function grupoAcademico(): BelongsTo
    {
        return $this->belongsTo(GrupoAcademico::class, 'cod_gac', 'cod_gac');
    }

    public function horarioDetalleRegistros(): HasMany
    {
        return $this->hasMany(HorarioDetalle::class, 'cod_pes', 'cod_pes');
    }

    public function claseVirtualRegistros(): HasMany
    {
        return $this->hasMany(ClaseVirtual::class, 'cod_pes', 'cod_pes');
    }

    public function calificacionRegistros(): HasMany
    {
        return $this->hasMany(Calificacion::class, 'cod_pes', 'cod_pes');
    }
}
