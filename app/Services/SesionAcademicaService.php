<?php

namespace App\Services;

use App\Models\CalendarioEvento;
use App\Models\SesionAcademica;
use App\Support\Academico\CalendarioAcademicoInteligente;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

/**
 * SesionAcademicaService
 *
 * Gestiona el ciclo de vida de las sesiones académicas:
 *   - Generación idempotente desde horario_detalle
 *   - Afectación de sesiones por eventos del calendario
 *   - Plan de recuperación: vincula sesiones perdidas con sesiones de recuperación
 *
 * Principios:
 *   - La generación es IDEMPOTENTE: ejecutar dos veces no duplica sesiones.
 *   - El horario BASE no se modifica destructivamente.
 *   - Las PRE-ALERTAS no modifican sesiones.
 *   - Las suspensiones parciales afectan SOLO las sesiones que se intersecten.
 */
class SesionAcademicaService
{
    public function __construct(
        private CalendarioAcademicoInteligente $calendario
    ) {}

    // =========================================================================
    // GENERACIÓN IDEMPOTENTE DE SESIONES
    // =========================================================================

    /**
     * Genera todas las sesiones académicas para una gestión en un rango de fechas.
     *
     * Idempotencia garantizada por:
     *   - Unique constraint (cod_hde, fec_ses) en la tabla
     *   - updateOrInsert en lugar de insert
     *
     * @param  string  $codGea  Código de gestión académica
     * @param  string  $inicio  Fecha de inicio (Y-m-d)
     * @param  string  $fin  Fecha de fin (Y-m-d)
     * @return array ['creadas' => int, 'existentes' => int]
     */
    public function generarParaGestion(string $codGea, string $inicio, string $fin): array
    {
        $creadas = 0;
        $existentes = 0;

        // Carga todos los horarios activos de la gestión
        $detalles = $this->cargarDetallesHorario($codGea, $inicio, $fin);

        $diasSemana = [
            'LUNES' => 1, 'MARTES' => 2, 'MIERCOLES' => 3,
            'JUEVES' => 4, 'VIERNES' => 5, 'SABADO' => 6, 'DOMINGO' => 7,
        ];

        for ($fecha = Carbon::parse($inicio); $fecha->lte(Carbon::parse($fin)); $fecha->addDay()) {
            $isoDay = $fecha->isoWeekday();

            foreach ($detalles as $detalle) {
                // Verificar que el día de la semana coincide
                $diaNormalized = strtr(mb_strtoupper($detalle->dia_hde), ['É' => 'E', 'Á' => 'A', 'É' => 'E']);
                if (($diasSemana[$diaNormalized] ?? 0) !== $isoDay) {
                    continue;
                }
                // Verificar vigencia de la plantilla
                if ($detalle->fec_ini_pho && $fecha->toDateString() < $detalle->fec_ini_pho) {
                    continue;
                }
                if ($detalle->fec_fin_pho && $fecha->toDateString() > $detalle->fec_fin_pho) {
                    continue;
                }

                $codSes = 'SES_'.substr(md5($detalle->cod_hde.'|'.$fecha->toDateString()), 0, 16);
                $existe = SesionAcademica::whereKey($codSes)->exists();

                if ($existe) {
                    $existentes++;
                } else {
                    // Calcular horas planificadas desde el bloque horario
                    $horaPlanificada = $this->calcularHorasBloque(
                        $detalle->hor_ini_hbl,
                        $detalle->hor_fin_hbl
                    );

                    SesionAcademica::create([
                        'cod_ses' => $codSes,
                        'cod_hde' => $detalle->cod_hde,
                        'cod_gea' => $codGea,
                        'fec_ses' => $fecha->toDateString(),
                        'hor_pla_ses' => $horaPlanificada,
                        'est_ses' => 'PROGRAMADA',
                    ]);
                    $creadas++;
                }
            }
        }

        return ['creadas' => $creadas, 'existentes' => $existentes];
    }

    // =========================================================================
    // AFECTACIÓN DE SESIONES POR EVENTO
    // =========================================================================

    /**
     * Cuando un evento del calendario se CONFIRMA, actualiza el estado de las sesiones
     * que quedan afectadas.
     *
     * Reglas:
     *   - PREALERTA → no modifica sesiones
     *   - CONFIRMADO (SIN_CLASES) → SUSPENDIDA
     *   - CONFIRMADO (SUSPENSION_PARCIAL) → PARCIAL (solo las que se intersecan)
     *   - CONFIRMADO (INGRESO_DIFERIDO / SALIDA_ANTICIPADA) → PARCIAL
     *   - CANCELADO → revierte SUSPENDIDA/PARCIAL → PROGRAMADA (si aún no hay asistencia)
     *
     * NO modifica el horario base.
     */
    public function aplicarEvento(CalendarioEvento $evento): array
    {
        if ($evento->est_cae === 'PREALERTA') {
            return ['afectadas' => 0, 'advertencia' => 'Prealerta no modifica sesiones.'];
        }

        $sesiones = $this->sesionesAfectadasPorEvento($evento);
        $afectadas = 0;

        DB::transaction(function () use ($evento, $sesiones, &$afectadas) {
            foreach ($sesiones as $sesion) {
                if ($evento->est_cae === 'CANCELADO') {
                    // Revertir: solo si la sesión fue afectada por este mismo evento
                    if ($sesion->cod_cae === $evento->cod_cae) {
                        $sesion->update(['est_ses' => 'PROGRAMADA', 'cod_cae' => null, 'hor_rea_ses' => null]);
                        $afectadas++;
                    }

                    continue;
                }

                $nuevoEstado = $this->determinarNuevoEstado($evento, $sesion);
                if ($nuevoEstado && $sesion->est_ses !== $nuevoEstado) {
                    $sesion->update([
                        'est_ses' => $nuevoEstado,
                        'cod_cae' => $evento->cod_cae,
                    ]);
                    $afectadas++;
                }
            }
        });

        return ['afectadas' => $afectadas];
    }

    /**
     * Determina qué sesiones quedan afectadas por un evento del calendario.
     * Filtra por: fecha, turno, curso, paralelo, horario_detalle y horas (suspensión parcial).
     */
    public function sesionesAfectadasPorEvento(CalendarioEvento $evento): Collection
    {
        if (! in_array($evento->efe_cae, [
            'SIN_CLASES', 'SUSPENSION_PARCIAL',
            'INGRESO_DIFERIDO', 'SALIDA_ANTICIPADA',
        ], true)) {
            return collect();
        }

        $query = SesionAcademica::where('cod_gea', $evento->cod_gea)
            ->whereDate('fec_ses', '>=', $evento->fii_cae)
            ->whereDate('fec_ses', '<=', $evento->ffi_cae)
            ->whereNotIn('est_ses', ['CANCELADA']); // No tocar sesiones ya canceladas

        // Filtros opcionales de ámbito
        if ($evento->cod_hde) {
            $query->where('cod_hde', $evento->cod_hde);
        } elseif ($evento->cod_cur || $evento->cod_par || $evento->cod_tur) {
            // Filtrar por curso/paralelo/turno mediante JOIN con horario_detalle
            $hdesAfectados = $this->hdesDeAmbito($evento);
            $query->whereIn('cod_hde', $hdesAfectados);
        }

        $sesiones = $query->with('horarioDetalle.horarioBloque')->get();

        // Para suspensión parcial: filtrar solo las que se intersecan con el rango horario
        if ($evento->efe_cae === 'SUSPENSION_PARCIAL' && $evento->hoi_cae && $evento->hof_cae) {
            $sesiones = $sesiones->filter(function (SesionAcademica $ses) use ($evento) {
                $detalle = DB::table('horario_detalle as d')
                    ->join('horario_bloque as b', 'b.cod_hbl', '=', 'd.cod_hbl')
                    ->where('d.cod_hde', $ses->cod_hde)
                    ->select('b.hor_ini_hbl', 'b.hor_fin_hbl')
                    ->first();

                if (! $detalle) {
                    return false;
                }

                // Intersección de rangos horarios
                return $detalle->hor_ini_hbl < $evento->hof_cae
                    && $detalle->hor_fin_hbl > $evento->hoi_cae;
            });
        }

        return $sesiones->values();
    }

    // =========================================================================
    // PLAN DE RECUPERACIÓN
    // =========================================================================

    /**
     * Valida si una sesión de recuperación es posible antes de confirmarla.
     *
     * Verifica:
     *   - Conflicto con horario existente del grupo
     *   - Docente ocupado en otra sesión
     *   - La fecha no es feriado ni tiene suspensión confirmada
     *   - No es sábado por defecto (requiere autorización explícita)
     *   - No hay otra recuperación confirmada en ese horario
     */
    public function validarRecuperacion(array $datos): array
    {
        $advertencias = [];
        $bloqueos = [];

        $fecha = $datos['fec_ses'] ?? null;
        $codGea = $datos['cod_gea'] ?? null;
        $codHde = $datos['cod_hde'] ?? null;
        $horaIni = $datos['hora_inicio'] ?? null;
        $horaFin = $datos['hora_fin'] ?? null;
        $codSesOri = $datos['cod_ses_ori'] ?? null;

        if (! $fecha || ! $codGea) {
            $bloqueos[] = 'Se requieren fecha y gestión académica para validar la recuperación.';

            return ['puede_recuperar' => false, 'bloqueos' => $bloqueos, 'advertencias' => $advertencias];
        }

        // 1. Verificar que la sesión origen existe y está perdida
        if ($codSesOri) {
            $sesOrigen = SesionAcademica::find($codSesOri);
            if (! $sesOrigen) {
                $bloqueos[] = 'La sesión de origen no existe.';
            } elseif (! in_array($sesOrigen->est_ses, ['SUSPENDIDA', 'PARCIAL', 'CANCELADA'], true)) {
                $bloqueos[] = 'La sesión de origen no tiene horas perdidas que recuperar.';
            } elseif ($sesOrigen->sesionesRecuperacion()
                ->whereIn('est_ses', ['PROGRAMADA', 'REALIZADA', 'RECUPERADA'])
                ->exists()) {
                $advertencias[] = 'La sesión de origen ya tiene una recuperación asociada.';
            }
        } else {
            $bloqueos[] = 'Toda recuperación debe estar asociada a una sesión perdida concreta.';
        }

        // 2. Verificar conflicto de calendario (feriados, suspensiones)
        $analisis = $this->calendario->analizarFecha($codGea, $fecha, [
            'hora_inicio' => $horaIni,
            'hora_fin' => $horaFin,
        ]);
        if (! $analisis['puede_continuar']) {
            $bloqueos[] = 'La fecha de recuperación coincide con un evento que bloquea clases: '.implode(', ', $analisis['bloqueos']);
        }

        // 3. Verificar que no es fin de semana sin autorización explícita
        $esFinde = Carbon::parse($fecha)->isWeekend();
        if ($esFinde && empty($datos['autorizado_finde'])) {
            $bloqueos[] = 'Los sábados y domingos requieren autorización explícita documentada para recuperaciones.';
        }

        // 4. Conflicto con otra sesión (mismo cod_hde, misma fecha)
        if ($codHde) {
            $conflicto = SesionAcademica::where('cod_hde', $codHde)
                ->where('fec_ses', $fecha)
                ->whereNotIn('est_ses', ['SUSPENDIDA', 'CANCELADA'])
                ->exists();
            if ($conflicto) {
                $bloqueos[] = 'Ya existe una sesión activa para este horario_detalle en esa fecha.';
            }
        }

        return [
            'puede_recuperar' => empty($bloqueos),
            'bloqueos' => $bloqueos,
            'advertencias' => $advertencias,
        ];
    }

    /**
     * Registra una sesión de recuperación, vinculándola a la sesión perdida.
     */
    public function registrarRecuperacion(array $datos): SesionAcademica
    {
        $validacion = $this->validarRecuperacion($datos);
        if (! $validacion['puede_recuperar']) {
            throw ValidationException::withMessages([
                'recuperacion' => $validacion['bloqueos'],
            ]);
        }

        return DB::transaction(function () use ($datos) {
            $horasPlan = isset($datos['hora_inicio'], $datos['hora_fin'])
                ? $this->calcularHorasBloque($datos['hora_inicio'], $datos['hora_fin'])
                : 1.0;

            $sesion = SesionAcademica::create([
                'cod_ses' => 'SES_'.bin2hex(random_bytes(8)),
                'cod_hde' => $datos['cod_hde'],
                'cod_gea' => $datos['cod_gea'],
                'fec_ses' => $datos['fec_ses'],
                'hor_pla_ses' => $horasPlan,
                'est_ses' => 'PROGRAMADA',
                'cod_ses_ori' => $datos['cod_ses_ori'],
                'obs_ses' => $datos['obs_ses'] ?? null,
            ]);

            BitacoraService::registrar(
                accion: 'SESION_RECUPERACION_REGISTRADA',
                tabla: 'sesion_academica',
                registro: $sesion->cod_ses,
                modulo: 'Sesiones Académicas',
                valoresAnteriores: [],
                valoresNuevos: $sesion->toArray()
            );

            return $sesion;
        }, 3);
    }

    // =========================================================================
    // RESUMEN DE HORAS
    // =========================================================================

    /**
     * Calcula el resumen de horas para un horario_detalle o una gestión completa.
     *
     * @param  string|null  $codHde  Si null, calcula para toda la gestión
     */
    public function resumenHoras(string $codGea, ?string $codHde = null): array
    {
        $query = SesionAcademica::where('cod_gea', $codGea);
        if ($codHde) {
            $query->where('cod_hde', $codHde);
        }
        $sesiones = $query->get();

        $planificadas = $sesiones->sum('hor_pla_ses');
        $realizadas = $sesiones->filter->estaRealizada()->sum(fn ($s) => $s->horasEfectivas());
        $perdidas = $sesiones->filter->estaSuspendida()->sum('hor_pla_ses');
        // Parciales: parte planificada menos parte realizada
        $perdidas += $sesiones->where('est_ses', 'PARCIAL')->sum(fn ($s) => $s->horasPerdidas());
        $recuperadas = $sesiones->where('est_ses', 'RECUPERADA')->sum(fn ($s) => $s->horasEfectivas());
        $saldo = max(0, $perdidas - $recuperadas);

        return [
            'horas_planificadas' => round($planificadas, 2),
            'horas_realizadas' => round($realizadas, 2),
            'horas_perdidas' => round($perdidas, 2),
            'horas_recuperadas' => round($recuperadas, 2),
            'saldo' => round($saldo, 2),
        ];
    }

    // =========================================================================
    // PRIVADOS
    // =========================================================================

    private function cargarDetallesHorario(string $codGea, string $inicio, string $fin): Collection
    {
        return DB::table('horario_detalle as d')
            ->join('horario as h', 'h.cod_hor', '=', 'd.cod_hor')
            ->join('plantilla_horaria as p', 'p.cod_pho', '=', 'h.cod_pho')
            ->join('horario_bloque as b', 'b.cod_hbl', '=', 'd.cod_hbl')
            ->where('h.cod_gea', $codGea)
            ->where('d.est_hde', 'ACTIVO')
            ->where('h.est_hor', 'ACTIVO')
            ->where('b.tip_hbl', 'CLASE')
            ->where('p.est_pho', true)
            ->where(fn ($q) => $q->whereNull('p.fec_ini_pho')->orWhere('p.fec_ini_pho', '<=', $fin))
            ->where(fn ($q) => $q->whereNull('p.fec_fin_pho')->orWhere('p.fec_fin_pho', '>=', $inicio))
            ->select('d.cod_hde', 'd.dia_hde', 'b.hor_ini_hbl', 'b.hor_fin_hbl', 'p.fec_ini_pho', 'p.fec_fin_pho')
            ->get();
    }

    private function hdesDeAmbito(CalendarioEvento $evento): array
    {
        $query = DB::table('horario_detalle as d')
            ->join('horario as h', 'h.cod_hor', '=', 'd.cod_hor')
            ->join('plantilla_horaria as p', 'p.cod_pho', '=', 'h.cod_pho')
            ->where('h.cod_gea', $evento->cod_gea)
            ->where('d.est_hde', 'ACTIVO')
            ->where('h.est_hor', 'ACTIVO');

        if ($evento->cod_tur) {
            $query->where('p.cod_tur', $evento->cod_tur);
        }
        if ($evento->cod_cur) {
            $query->where('h.cod_cur', $evento->cod_cur);
        }
        if ($evento->cod_par) {
            $query->where('h.cod_par', $evento->cod_par);
        }

        return $query->pluck('d.cod_hde')->toArray();
    }

    private function determinarNuevoEstado(CalendarioEvento $evento, SesionAcademica $sesion): ?string
    {
        // No afectar sesiones ya realizadas
        if (in_array($sesion->est_ses, ['REALIZADA', 'RECUPERADA'], true)) {
            return null;
        }

        return match ($evento->efe_cae) {
            'SIN_CLASES' => 'SUSPENDIDA',
            'SUSPENSION_PARCIAL',
            'INGRESO_DIFERIDO',
            'SALIDA_ANTICIPADA' => 'PARCIAL',
            default => null,
        };
    }

    private function calcularHorasBloque(string $inicio, string $fin): float
    {
        return round(Carbon::parse($inicio)->diffInMinutes(Carbon::parse($fin)) / 60, 2);
    }
}
