<?php

namespace App\Models\AulaVirtual;

use App\Models\Estudiante;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ClaseEstudiante extends Model
{
    protected $table = 'clase_estudiante';
    protected $primaryKey = 'cod_cla_est';

    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'cod_cla_est',          //Codigo clase estudiante
        'cod_cla',              //Codigo clase
        'cod_est',              //Codigo estudiante
        'fec_inc_cla_est',      //Fecha inicio clase estudiante
        'fec_ret_cla_est',      //Fecha fin clase estudiante
        'ult_acc_cla_est',      //Ultima fecha de acceso
        'ult_act_cla_est',      //Ultima fecha de actividad
        'cant_acc_cla_est',     //Cantidad de accesos
        'est_cla_est',          //Estado clase estudiante
    ];

    protected $casts = [
        'fec_inc_cla_est' => 'date',
        'fec_ret_cla_est' => 'date',
        'ult_acc_cla_est' => 'datetime',
        'ult_act_cla_est' => 'datetime',
        'cant_acc_cla_est' => 'integer',
    ];

    protected static function booted(): void
    {
        static::creating(function (ClaseEstudiante $claseEstudiante) {
            if (! $claseEstudiante->cod_cla_est) {
                $ultimoCodigo = self::where('cod_cla_est', 'like', 'CLE_%')
                    ->orderByDesc('cod_cla_est')
                    ->value('cod_cla_est');

                $numero = $ultimoCodigo
                    ? ((int) str_replace('CLE_', '', $ultimoCodigo)) + 1
                    : 1;

                $claseEstudiante->cod_cla_est = 'CLE_' . str_pad((string) $numero, 4, '0', STR_PAD_LEFT);
            }
        });
    }

    public function claseVirtual(): BelongsTo
    {
        return $this->belongsTo(ClaseVirtual::class, 'cod_cla', 'cod_cla');
    }

    public function estudiante(): BelongsTo
    {
        return $this->belongsTo(Estudiante::class, 'cod_est', 'cod_est');
    }

    public function actividades(): HasMany
    {
        return $this->hasMany(ActividadClase::class, 'cod_est', 'cod_est')
            ->whereColumn('actividad_clase.cod_cla', 'clase_estudiante.cod_cla');
    }

    public function scopeActivos($query)
    {
        return $query->where('est_cla_est', 'ACTIVO');
    }

    public function scopeInactivos($query)
    {
        return $query->whereIn('est_cla_est', ['RETIRADO', 'TRANSFERIDO', 'INACTIVO', 'ANULADO']);
    }

    public function scopeSinAcceso($query)
    {
        return $query->whereNull('ult_acc_cla_est');
    }

    public function scopeInactivosDesde($query, int $dias)
    {
        return $query->where(function ($subQuery) use ($dias) {
            $subQuery
                ->whereNull('ult_acc_cla_est')
                ->orWhere('ult_acc_cla_est', '<=', now()->subDays($dias));
        });
    }

    public function estaActivo(): bool
    {
        return $this->est_cla_est === 'ACTIVO';
    }

    public function registrarAcceso(): void
    {
        $this->forceFill([
            'ult_acc_cla_est' => now(),
            'ult_act_cla_est' => now(),
            'cant_acc_cla_est' => ((int) $this->cant_acc_cla_est) + 1,
        ])->save();
    }

    public function registrarActividad(): void
    {
        $this->forceFill([
            'ult_act_cla_est' => now(),
        ])->save();
    }
}