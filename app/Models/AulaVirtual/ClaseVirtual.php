<?php

namespace App\Models\AulaVirtual;

use App\Models\PlanAsignatura;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ClaseVirtual extends Model
{
    protected $table = 'clase_virtual';
    protected $primaryKey = 'cod_cla';

    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'cod_cla',          //Codigo clase
        'cod_pas',          //Codigo plan asignatura
        'nom_cla',          //Nombre clase
        'des_cla',          //Descripcion clase
        'fec_ini_cla',      //Fecha inicio clase
        'fec_fin_cla',      //Fecha fin clase
        'est_cla',          //Estado clase
    ];

    protected $casts = [
        'fec_ini_cla' => 'date',
        'fec_fin_cla' => 'date',
    ];

    protected static function booted(): void
    {
        static::creating(function (ClaseVirtual $claseVirtual) {
            if (! $claseVirtual->cod_cla) {
                $ultimoCodigo = self::where('cod_cla', 'like', 'CLA_%')
                    ->orderByDesc('cod_cla')
                    ->value('cod_cla');

                $numero = $ultimoCodigo
                    ? ((int) str_replace('CLA_', '', $ultimoCodigo)) + 1
                    : 1;

                $claseVirtual->cod_cla = 'CLA_' . str_pad((string) $numero, 4, '0', STR_PAD_LEFT);
            }
        });
    }

    public function planAsignatura(): BelongsTo
    {
        return $this->belongsTo(PlanAsignatura::class, 'cod_pas', 'cod_pas');
    }

    public function estudiantes(): HasMany
    {
        return $this->hasMany(ClaseEstudiante::class, 'cod_cla', 'cod_cla');
    }

    public function publicaciones(): HasMany
    {
        return $this->hasMany(PublicacionClase::class, 'cod_cla', 'cod_cla');
    }

    public function materiales(): HasMany
    {
        return $this->hasMany(MaterialClase::class, 'cod_cla', 'cod_cla');
    }

    public function tareas(): HasMany
    {
        return $this->hasMany(Tarea::class, 'cod_cla', 'cod_cla');
    }

    public function asistencias(): HasMany
    {
        return $this->hasMany(AsistenciaClase::class, 'cod_cla', 'cod_cla');
    }

    public function actividades(): HasMany
    {
        return $this->hasMany(ActividadClase::class, 'cod_cla', 'cod_cla');
    }

    public function scopeActivas($query)
    {
        return $query->where('est_cla', 'ACTIVA');
    }

    public function scopeDisponibles($query)
    {
        return $query->whereIn('est_cla', ['ACTIVA']);
    }

    public function estaActiva(): bool
    {
        return $this->est_cla === 'ACTIVA';
    }

    public function estaCerrada(): bool
    {
        return $this->est_cla === 'CERRADA';
    }

    public function estaAnulada(): bool
    {
        return $this->est_cla === 'ANULADA';
    }
}
