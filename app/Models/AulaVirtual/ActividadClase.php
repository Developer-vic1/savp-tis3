<?php

namespace App\Models\AulaVirtual;

use App\Models\Estudiante;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ActividadClase extends Model
{
    protected $table = 'actividad_clase';
    protected $primaryKey = 'cod_act_cla';

    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'cod_act_cla', // Código único de la actividad registrada
        'cod_cla', // Clase virtual donde ocurrió la actividad
        'cod_est', // Estudiante relacionado con la actividad, si corresponde
        'cod_usu', // Usuario que ejecutó o generó la actividad

        'tip_act', // Tipo de actividad: ingreso, entrega, asistencia, descarga, comentario u otro
        'ref_tab', // Tabla o módulo relacionado con la actividad
        'ref_cod', // Código del registro relacionado

        'fec_act', // Fecha y hora de la actividad
        'met_act', // Metadatos en JSON para análisis posterior
    ];

    protected $casts = [
        'fec_act' => 'datetime',
        'met_act' => 'array',
    ];

    protected static function booted(): void
    {
        static::creating(function (ActividadClase $actividadClase) {
            if (! $actividadClase->cod_act_cla) {
                $ultimoCodigo = self::where('cod_act_cla', 'like', 'ACTC_%')
                    ->orderByDesc('cod_act_cla')
                    ->value('cod_act_cla');

                $numero = $ultimoCodigo
                    ? ((int) str_replace('ACTC_', '', $ultimoCodigo)) + 1
                    : 1;

                $actividadClase->cod_act_cla = 'ACTC_' . str_pad((string) $numero, 4, '0', STR_PAD_LEFT);
            }

            if (! $actividadClase->fec_act) {
                $actividadClase->fec_act = now();
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

    public function usuario(): BelongsTo
    {
        return $this->belongsTo(User::class, 'cod_usu', 'cod_usu');
    }

    public function scopePorTipo($query, string $tipo)
    {
        return $query->where('tip_act', $tipo);
    }

    public function scopePorReferencia($query, string $tabla, string $codigo)
    {
        return $query->where('ref_tab', $tabla)
            ->where('ref_cod', $codigo);
    }

    public function scopeRecientes($query)
    {
        return $query->orderByDesc('fec_act');
    }

    public function scopeDeEstudiante($query, string $codEst)
    {
        return $query->where('cod_est', $codEst);
    }

    public function scopeDeClase($query, string $codCla)
    {
        return $query->where('cod_cla', $codCla);
    }

    public function esIngresoClase(): bool
    {
        return $this->tip_act === 'INGRESO_CLASE';
    }

    public function esEntregaTarea(): bool
    {
        return in_array($this->tip_act, ['ENTREGO_TAREA', 'ENTREGA_TARDIA'], true);
    }

    public function esAsistencia(): bool
    {
        return in_array($this->tip_act, [
            'REGISTRO_ASISTENCIA',
            'AUSENCIA_REGISTRADA',
            'ATRASO_REGISTRADO',
        ], true);
    }
}