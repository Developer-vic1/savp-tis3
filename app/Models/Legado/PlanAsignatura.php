<?php

namespace App\Models\Legado;

use App\Models\Asignatura;
use App\Models\Curso;
use App\Models\Docente;
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

class PlanAsignatura extends Model
{
    protected $table = 'plan_asignatura';

    protected $primaryKey = 'cod_pas';

    public $incrementing = false;

    protected $keyType = 'string';

    protected $fillable = [
        'cod_pas', // Código plan asignatura
        'cod_asi', // Código asignatura
        'cod_doc', // Código docente
        'cod_cur', // Código curso
        'cod_par', // Código paralelo
        'cod_tur', // Código turno
        'cod_gea', // Código gestión académica
        'hor_pas', // Horas asignadas
        'est_pas', // Estado plan asignatura
        'cod_gac',
        'fii_pas',
        'ffi_pas',
    ];

    protected static function booted(): void
    {
        static::creating(function ($plan) {

            if (! $plan->cod_pas) {
                $plan->cod_pas = 'PAS_'.strtoupper(bin2hex(random_bytes(8)));
            }
        });
    }

    // 🔗 Relaciones

    public function asignatura()
    {
        return $this->belongsTo(Asignatura::class, 'cod_asi', 'cod_asi');
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
        'hor_pas' => 'decimal:2',
        'fii_pas' => 'date',
        'ffi_pas' => 'date',
    ];

    public function grupoAcademico(): BelongsTo
    {
        return $this->belongsTo(GrupoAcademico::class, 'cod_gac', 'cod_gac');
    }

    public function horarioDetalleRegistros(): HasMany
    {
        return $this->hasMany(HorarioDetalle::class, 'cod_pas', 'cod_pas');
    }

    public function claseVirtualRegistros(): HasMany
    {
        return $this->hasMany(ClaseVirtual::class, 'cod_pas', 'cod_pas');
    }

    public function calificacionRegistros(): HasMany
    {
        return $this->hasMany(Calificacion::class, 'cod_pas', 'cod_pas');
    }
}
