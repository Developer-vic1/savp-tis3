<?php

namespace App\Models\Oficial\Academico;

use App\Models\Oficial\AulaVirtual\ClaseVirtual;
use App\Support\Modelos\CodigoInstitucional;
use App\Models\Oficial\Sistema\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class AsistenciaClase extends Model
{
    use CodigoInstitucional;

    protected $table = 'asistencia_clase';

    protected $primaryKey = 'cod_asi_cla';

    public $incrementing = false;

    protected $keyType = 'string';

    protected $fillable = [
        'cod_asi_cla', // Código único de la sesión de asistencia
        'cod_cla', // Clase virtual a la que pertenece la asistencia
        'cod_doc', // Docente responsable de la sesión
        'cod_hbl', // Bloque horario asociado, si corresponde
        'cod_usu_reg', // Usuario que registró la asistencia

        'fec_asi_cla', // Fecha de la sesión de asistencia
        'hor_ini_asi_cla', // Hora de inicio de la sesión
        'hor_fin_asi_cla', // Hora de finalización de la sesión

        'tip_asi_cla', // Tipo: CLASE, LABORATORIO, PRACTICA, EVALUACION o ACTIVIDAD
        'tit_asi_cla', // Título visible de la sesión de asistencia
        'obs_asi_cla', // Observación general de la asistencia

        'ori_asi_cla', // Origen: MANUAL, GENERADA o IMPORTADA
        'est_asi_cla', // Estado: BORRADOR, ABIERTA, CERRADA o ANULADA
        'cod_ses',
    ];

    protected $casts = [
        'fec_asi_cla' => 'date',
        'hor_ini_asi_cla' => 'datetime:H:i',
        'hor_fin_asi_cla' => 'datetime:H:i',
    ];

    public function claseVirtual(): BelongsTo
    {
        return $this->belongsTo(ClaseVirtual::class, 'cod_cla', 'cod_cla');
    }

    public function docente(): BelongsTo
    {
        return $this->belongsTo(Docente::class, 'cod_doc', 'cod_doc');
    }

    public function horarioBloque(): BelongsTo
    {
        return $this->belongsTo(HorarioBloque::class, 'cod_hbl', 'cod_hbl');
    }

    public function usuarioRegistro(): BelongsTo
    {
        return $this->belongsTo(User::class, 'cod_usu_reg', 'cod_usu');
    }

    public function estudiantes(): HasMany
    {
        return $this->hasMany(AsistenciaEstudiante::class, 'cod_asi_cla', 'cod_asi_cla');
    }

    public function scopeBorradores($query)
    {
        return $query->where('est_asi_cla', 'BORRADOR');
    }

    public function scopeAbiertas($query)
    {
        return $query->where('est_asi_cla', 'ABIERTA');
    }

    public function scopeCerradas($query)
    {
        return $query->where('est_asi_cla', 'CERRADA');
    }

    public function scopeAnuladas($query)
    {
        return $query->where('est_asi_cla', 'ANULADA');
    }

    public function scopePorFecha($query, $fecha)
    {
        return $query->whereDate('fec_asi_cla', $fecha);
    }

    public function scopeDeClase($query, string $codCla)
    {
        return $query->where('cod_cla', $codCla);
    }

    public function scopeDeDocente($query, string $codDoc)
    {
        return $query->where('cod_doc', $codDoc);
    }

    public function esBorrador(): bool
    {
        return $this->est_asi_cla === 'BORRADOR';
    }

    public function estaAbierta(): bool
    {
        return $this->est_asi_cla === 'ABIERTA';
    }

    public function estaCerrada(): bool
    {
        return $this->est_asi_cla === 'CERRADA';
    }

    public function estaAnulada(): bool
    {
        return $this->est_asi_cla === 'ANULADA';
    }

    public function abrir(): void
    {
        $this->forceFill([
            'est_asi_cla' => 'ABIERTA',
        ])->save();
    }

    public function cerrar(): void
    {
        $this->forceFill([
            'est_asi_cla' => 'CERRADA',
        ])->save();
    }

    public function anular(?string $observacion = null): void
    {
        $this->forceFill([
            'est_asi_cla' => 'ANULADA',
            'obs_asi_cla' => $observacion ?? $this->obs_asi_cla,
        ])->save();
    }

    public function sesionAcademica(): BelongsTo
    {
        return $this->belongsTo(SesionAcademica::class, 'cod_ses', 'cod_ses');
    }

    public function users(): BelongsTo
    {
        return $this->belongsTo(User::class, 'cod_usu_reg', 'cod_usu');
    }

    public function asistenciaEstudianteRegistros(): HasMany
    {
        return $this->hasMany(AsistenciaEstudiante::class, 'cod_asi_cla', 'cod_asi_cla');
    }
}
