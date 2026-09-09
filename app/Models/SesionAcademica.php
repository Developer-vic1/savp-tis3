<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Sesión Académica
 *
 * Representa la ocurrencia real de un horario_detalle en una fecha concreta.
 * Es el eje central para calcular horas planificadas, realizadas, perdidas y recuperadas.
 *
 * Estados:
 *   PROGRAMADA   – generada por el sistema; pendiente de ocurrencia
 *   REALIZADA    – la sesión se llevó a cabo normalmente
 *   SUSPENDIDA   – fue totalmente suprimida por un evento del calendario
 *   PARCIAL      – se realizó parcialmente (ej. suspensión parcial o ingreso diferido)
 *   REPROGRAMADA – se moverá a otra fecha (tiene cod_ses_ori en la nueva)
 *   RECUPERADA   – reemplaza a una sesión suspendida previa (cod_ses_ori apunta a la perdida)
 *   CANCELADA    – no se realizará ni recuperará
 */
class SesionAcademica extends Model
{
    protected $table = 'sesion_academica';

    protected $primaryKey = 'cod_ses';

    public $incrementing = false;

    protected $keyType = 'string';

    protected $fillable = [
        'cod_ses', 'cod_hde', 'cod_gea', 'fec_ses',
        'hor_pla_ses', 'hor_rea_ses', 'est_ses',
        'cod_cae', 'cod_ses_ori', 'obs_ses',
    ];

    protected $casts = [
        'fec_ses' => 'date',
        'hor_pla_ses' => 'float',
        'hor_rea_ses' => 'float',
    ];

    // ─── Relaciones ──────────────────────────────────────────────────────────

    public function horarioDetalle()
    {
        return $this->belongsTo(HorarioDetalle::class, 'cod_hde', 'cod_hde');
    }

    public function gestion()
    {
        return $this->belongsTo(GestionAcademica::class, 'cod_gea', 'cod_gea');
    }

    public function evento()
    {
        return $this->belongsTo(CalendarioEvento::class, 'cod_cae', 'cod_cae');
    }

    public function sesionOrigen()
    {
        return $this->belongsTo(self::class, 'cod_ses_ori', 'cod_ses');
    }

    public function sesionesRecuperacion()
    {
        return $this->hasMany(self::class, 'cod_ses_ori', 'cod_ses');
    }

    // ─── Helpers de estado ───────────────────────────────────────────────────

    public function estaSuspendida(): bool
    {
        return in_array($this->est_ses, ['SUSPENDIDA', 'CANCELADA'], true);
    }

    public function estaRealizada(): bool
    {
        return in_array($this->est_ses, ['REALIZADA', 'PARCIAL', 'RECUPERADA'], true);
    }

    public function horasEfectivas(): float
    {
        if ($this->est_ses === 'REALIZADA' || $this->est_ses === 'RECUPERADA') {
            return $this->hor_rea_ses ?? $this->hor_pla_ses;
        }
        if ($this->est_ses === 'PARCIAL') {
            return $this->hor_rea_ses ?? 0.0;
        }

        return 0.0;
    }

    public function horasPerdidas(): float
    {
        if (in_array($this->est_ses, ['SUSPENDIDA', 'CANCELADA'], true)) {
            return $this->hor_pla_ses;
        }
        if ($this->est_ses === 'PARCIAL') {
            return max(0, $this->hor_pla_ses - ($this->hor_rea_ses ?? 0.0));
        }

        return 0.0;
    }
}
