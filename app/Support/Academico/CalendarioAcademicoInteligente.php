<?php

namespace App\Support\Academico;

use App\Models\CalendarioEvento;
use App\Models\ConfiguracionCalendarioGestion;
use App\Support\Core\SoporteInteligenteBase;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class CalendarioAcademicoInteligente extends SoporteInteligenteBase
{
    public const TIPOS = ['FERIADO', 'DESCANSO_PEDAGOGICO', 'ANIVERSARIO', 'ACTIVIDAD_INSTITUCIONAL', 'BLOQUEO', 'CONFLICTO_SOCIAL', 'INUNDACION', 'DESLIZAMIENTO', 'INCENDIO', 'CLIMA_EXTREMO', 'CONTINGENCIA_SANITARIA', 'FALLA_INFRAESTRUCTURA', 'CORTE_SERVICIO', 'DISPOSICION_AUTORIDAD', 'SUSPENSION', 'RECUPERACION', 'HORARIO_EXCEPCIONAL', 'OTRO'];

    public const EFECTOS = ['INFORMATIVO', 'SIN_CLASES', 'SUSPENSION_PARCIAL', 'INGRESO_DIFERIDO', 'SALIDA_ANTICIPADA', 'HORARIO_AJUSTADO', 'RECUPERACION', 'ACTIVIDAD_INSTITUCIONAL'];

    public const ESTADOS = ['BORRADOR', 'PREALERTA', 'PENDIENTE_APROBACION', 'CONFIRMADO', 'CANCELADO', 'FINALIZADO', 'SUPERADO'];

    public function eventoAplicable(string $gestion, string $fecha, array $ambito = [])
    {
        $query = CalendarioEvento::where('cod_gea', $gestion)->whereDate('fii_cae', '<=', $fecha)->whereDate('ffi_cae', '>=', $fecha)
            ->whereIn('est_cae', ['PREALERTA', 'CONFIRMADO', 'FINALIZADO']);
        foreach (['cod_tur', 'cod_cur', 'cod_par', 'cod_hde'] as $campo) {
            $query->where(fn ($q) => $q->whereNull($campo)->when(! empty($ambito[$campo]), fn ($q) => $q->orWhere($campo, $ambito[$campo])));
        }

        return $query->get();
    }

    public function analizarFecha(string $gestion, string $fecha, array $ambito = []): array
    {
        $eventos = $this->eventoAplicable($gestion, $fecha, $ambito);
        $bloqueos = [];
        $advertencias = [];
        foreach ($eventos as $evento) {
            if ($evento->est_cae === 'PREALERTA') {
                $advertencias[] = 'Prealerta: '.$evento->nom_cae.'. No modifica la jornada.';

                continue;
            }
            if (in_array($evento->efe_cae, ['SIN_CLASES', 'SUSPENSION_PARCIAL', 'INGRESO_DIFERIDO', 'SALIDA_ANTICIPADA'], true)) {
                $afecta = ! $evento->hoi_cae || empty($ambito['hora_inicio']) || empty($ambito['hora_fin'])
                    || ($ambito['hora_inicio'] < $evento->hof_cae && $ambito['hora_fin'] > $evento->hoi_cae);
                if ($afecta) {
                    $bloqueos[] = $evento->nom_cae.': '.$evento->efe_cae;
                }
            } elseif ($evento->efe_cae === 'HORARIO_AJUSTADO') {
                $advertencias[] = 'Revisar plantilla horaria vigente: '.$evento->nom_cae;
            }
        }

        return $this->construirResultado(bloqueos: $bloqueos, advertencias: $advertencias, datosCalculados: ['eventos' => $eventos->toArray()]);
    }

    public function puedeRegistrarAsistencia(string $gestion, string $fecha, array $ambito = []): array
    {
        return $this->analizarFecha($gestion, $fecha, $ambito);
    }

    public function esDiaLectivo(string $gestion, string $fecha, array $ambito = []): bool
    {
        $eventos = $this->eventoAplicable($gestion, $fecha, $ambito)->whereIn('est_cae', ['CONFIRMADO', 'FINALIZADO']);
        if ($eventos->contains(fn ($e) => ! $e->hoi_cae && ($e->efe_cae === 'SIN_CLASES' || $e->com_cae === false))) {
            return false;
        }
        if ($eventos->contains(fn ($e) => $e->com_cae === true)) {
            return true;
        }

        return ! Carbon::parse($fecha)->isWeekend();
    }

    public function calcularDiasEfectivos(string $gestion, string $inicio, string $fin, array $ambito = []): int
    {
        $eventos = CalendarioEvento::where('cod_gea', $gestion)->whereIn('est_cae', ['CONFIRMADO', 'FINALIZADO'])
            ->whereDate('fii_cae', '<=', $fin)->whereDate('ffi_cae', '>=', $inicio)->get()
            ->filter(function ($evento) use ($ambito) {
                foreach (['cod_tur', 'cod_cur', 'cod_par', 'cod_hde'] as $campo) {
                    if ($evento->{$campo} !== null && $evento->{$campo} !== ($ambito[$campo] ?? null)) {
                        return false;
                    }
                }

                return true;
            });
        $dias = 0;
        for ($fecha = Carbon::parse($inicio); $fecha->lte(Carbon::parse($fin)); $fecha->addDay()) {
            $aplicables = $eventos->filter(fn ($e) => $fecha->betweenIncluded($e->fii_cae, $e->ffi_cae));
            if ($aplicables->contains(fn ($e) => ! $e->hoi_cae && ($e->efe_cae === 'SIN_CLASES' || $e->com_cae === false))) {
                continue;
            }
            $dias += (int) (! $fecha->isWeekend() || $aplicables->contains(fn ($e) => $e->com_cae === true));
        }

        return $dias;
    }

    public function proyectarFechaFin(string $gestion, string $inicio, int $diasObjetivo, array $ambito = []): ?string
    {
        if ($diasObjetivo < 1) {
            return null;
        }
        $fecha = Carbon::parse($inicio);
        $limite = $fecha->copy()->addYears(2);
        while ($fecha->lte($limite)) {
            if ($this->esDiaLectivo($gestion, $fecha->toDateString(), $ambito) && --$diasObjetivo === 0) {
                return $fecha->toDateString();
            }
            $fecha->addDay();
        }

        return null;
    }

    public function tareasAfectadas(array $evento)
    {
        $planes = $this->horariosAfectados($evento)->pluck('cod_pas')->filter()->unique();

        return DB::table('tarea as t')->join('clase_virtual as c', 'c.cod_cla', '=', 't.cod_cla')->whereIn('c.cod_pas', $planes)
            ->whereDate('t.fec_lim_tar', '>=', $evento['fii_cae'])->whereDate('t.fec_lim_tar', '<=', $evento['ffi_cae'])->get(['t.cod_tar', 't.tit_tar', 't.fec_lim_tar', 't.tip_tar']);
    }

    public function evaluacionesAfectadas(array $evento)
    {
        return $this->tareasAfectadas($evento)->filter(fn ($t) => in_array($t->tip_tar, ['EVALUACION', 'EXAMEN', 'PRUEBA'], true))->values();
    }

    public function detectarConflictos(array $evento): array
    {
        $conflictos = [];
        $otros = CalendarioEvento::where('cod_gea', $evento['cod_gea'])->whereIn('est_cae', ['CONFIRMADO', 'FINALIZADO'])
            ->whereDate('fii_cae', '<=', $evento['ffi_cae'])->whereDate('ffi_cae', '>=', $evento['fii_cae'])
            ->when(! empty($evento['cod_cae']), fn ($q) => $q->where('cod_cae', '<>', $evento['cod_cae']))->get();
        foreach ($otros as $otro) {
            $compatible = true;
            foreach (['cod_tur', 'cod_cur', 'cod_par', 'cod_hde'] as $campo) {
                if (! empty($evento[$campo]) && $otro->{$campo} && $otro->{$campo} !== $evento[$campo]) {
                    $compatible = false;
                }
            }
            if ($compatible) {
                $conflictos[] = 'Coincide con '.$otro->nom_cae.' ('.$otro->efe_cae.').';
            }
        }

        return $conflictos;
    }

    public function horariosAfectados(array $evento)
    {
        $query = DB::table('horario_detalle as d')->join('horario as h', 'h.cod_hor', '=', 'd.cod_hor')
            ->join('plantilla_horaria as p', 'p.cod_pho', '=', 'h.cod_pho')->join('horario_bloque as b', 'b.cod_hbl', '=', 'd.cod_hbl')
            ->where('h.cod_gea', $evento['cod_gea'])->where('d.est_hde', 'ACTIVO')->where('h.est_hor', 'ACTIVO')
            ->where('b.tip_hbl', 'CLASE')->where('p.est_pho', true)
            ->where(fn ($q) => $q->whereNull('p.fec_ini_pho')->orWhere('p.fec_ini_pho', '<=', $evento['ffi_cae']))
            ->where(fn ($q) => $q->whereNull('p.fec_fin_pho')->orWhere('p.fec_fin_pho', '>=', $evento['fii_cae']));
        foreach (['cod_tur' => 'p.cod_tur', 'cod_cur' => 'h.cod_cur', 'cod_par' => 'h.cod_par', 'cod_hde' => 'd.cod_hde'] as $campo => $columna) {
            if (! empty($evento[$campo])) {
                $query->where($columna, $evento[$campo]);
            }
        }

        return $query->select('d.*', 'h.cod_cur', 'h.cod_par', 'p.cod_tur', 'p.fec_ini_pho', 'p.fec_fin_pho', 'b.hor_ini_hbl', 'b.hor_fin_hbl')->get();
    }

    public function analizarImpactoEvento(array $evento): array
    {
        $horarios = $this->horariosAfectados($evento);
        $estudiantes = collect();
        foreach ($horarios->unique(fn ($h) => implode('|', [$h->cod_cur, $h->cod_par, $h->cod_tur])) as $grupo) {
            $estudiantes = $estudiantes->merge(DB::table('inscripcion_vigencia as v')->join('inscripcion_estudiante as i', 'i.cod_ins', '=', 'v.cod_ins')
                ->where('i.cod_gea', $evento['cod_gea'])->where('v.cod_cur', $grupo->cod_cur)->where('v.cod_par', $grupo->cod_par)->where('v.cod_tur', $grupo->cod_tur)
                ->where('v.est_ivg', '<>', 'ANULADA')->whereDate('v.fii_ivg', '<=', $evento['ffi_cae'])
                ->where(fn ($q) => $q->whereNull('v.ffi_ivg')->orWhereDate('v.ffi_ivg', '>=', $evento['fii_cae']))->pluck('i.cod_est'));
        }
        $docentes = DB::table('plan_asignatura')->whereIn('cod_pas', $horarios->pluck('cod_pas')->filter())->pluck('cod_doc')
            ->merge(DB::table('plan_especialidad')->whereIn('cod_pes', $horarios->pluck('cod_pes')->filter())->pluck('cod_doc'))->unique();

        return $this->construirResultado(impacto: [
            'horarios' => $horarios->toArray(), 'cursos' => $horarios->pluck('cod_cur')->unique()->values()->all(),
            'paralelos' => $horarios->pluck('cod_par')->unique()->values()->all(),
            'estudiantes' => $estudiantes->unique()->count(), 'docentes' => $docentes->count(),
            'tareas' => $this->tareasAfectadas($evento)->all(), 'evaluaciones' => $this->evaluacionesAfectadas($evento)->all(),
        ], advertencias: array_merge(($evento['est_cae'] ?? '') === 'PREALERTA' ? ['Una prealerta no suspende clases.'] : [], $this->detectarConflictos($evento)));
    }

    public function calcularHorasPerdidas(CalendarioEvento $evento): float
    {
        if (! in_array($evento->est_cae, ['CONFIRMADO', 'FINALIZADO'], true) || ! in_array($evento->efe_cae, ['SIN_CLASES', 'SUSPENSION_PARCIAL', 'INGRESO_DIFERIDO', 'SALIDA_ANTICIPADA'], true)) {
            return 0;
        }

        return $this->horasEvento($evento);
    }

    public function calcularHorasRecuperadas(CalendarioEvento $origen): float
    {
        return (float) $origen->recuperaciones()->whereIn('est_cae', ['CONFIRMADO', 'FINALIZADO'])->where('efe_cae', 'RECUPERACION')->get()
            ->sum(fn ($evento) => $this->horasEvento($evento, true));
    }

    public function calcularSaldoRecuperacion(CalendarioEvento $evento): float
    {
        return max(0, $this->calcularHorasPerdidas($evento) - $this->calcularHorasRecuperadas($evento));
    }

    private function horasEvento(CalendarioEvento $evento, bool $recuperacion = false): float
    {
        $horarios = $this->horariosAfectados($evento->getAttributes());
        if ($recuperacion) {
            $horarios = $horarios->unique(fn ($h) => implode('|', [$h->cod_cur, $h->cod_par, $h->cod_tur, $h->cod_hbl]));
        }
        $dias = [1 => 'LUNES', 2 => 'MARTES', 3 => 'MIERCOLES', 4 => 'JUEVES', 5 => 'VIERNES', 6 => 'SABADO', 7 => 'DOMINGO'];
        $minutos = 0;
        for ($fecha = $evento->fii_cae->copy(); $fecha->lte($evento->ffi_cae); $fecha->addDay()) {
            foreach ($horarios as $horario) {
                if (! $recuperacion && strtr(mb_strtoupper($horario->dia_hde), ['É' => 'E', 'Á' => 'A']) !== $dias[$fecha->isoWeekday()]) {
                    continue;
                }
                if (($horario->fec_ini_pho && $fecha->toDateString() < $horario->fec_ini_pho) || ($horario->fec_fin_pho && $fecha->toDateString() > $horario->fec_fin_pho)) {
                    continue;
                }
                $inicio = max($horario->hor_ini_hbl, $evento->hoi_cae ?? '00:00:00');
                $fin = min($horario->hor_fin_hbl, $evento->hof_cae ?? '23:59:59');
                if ($inicio >= $fin) {
                    continue;
                }
                if ($recuperacion && ! $this->analizarFecha($evento->cod_gea, $fecha->toDateString(), (array) $horario + ['hora_inicio' => $inicio, 'hora_fin' => $fin])['puede_continuar']) {
                    continue;
                }
                $minutos += Carbon::parse($inicio)->diffInMinutes(Carbon::parse($fin));
            }
        }

        return round($minutos / 60, 2);
    }

    // =========================================================================
    // CONFIGURACIÓN DE DÍAS POR GESTIÓN (reemplaza constantes dispersas)
    // =========================================================================

    /**
     * Retorna el total de días lectivos requeridos para una gestión.
     * Usa configuracion_calendario_gestion; si no hay configuración, advierte.
     */
    public function diasRequeridos(string $codGea): array
    {
        $total = ConfiguracionCalendarioGestion::totalDiasGestion($codGea);
        $distribucion = ConfiguracionCalendarioGestion::distribucionPorTrimestre($codGea);

        $advertencias = [];
        if ($total === 0) {
            $advertencias[] = 'No se encontró configuración de días para la gestión '.$codGea.'. Configure los trimestres en configuracion_calendario_gestion.';
        }

        return [
            'total' => $total,
            'por_trimestre' => $distribucion,
            'advertencias' => $advertencias,
        ];
    }

    /**
     * Calcula el resumen completo de días lectivos para una gestión:
     *   - requeridos    (de configuracion_calendario_gestion)
     *   - planificados  (días hábiles sin contar eventos SIN_CLASES confirmados)
     *   - efectivos     (días ya transcurridos con clases)
     *   - suspendidos   (días con suspensión SIN_CLASES confirmada)
     *   - recuperados   (días con evento RECUPERACION confirmado)
     *   - pendientes    (requeridos - efectivos - recuperados)
     *
     * Protección: si dos eventos afectan el mismo día, solo cuenta UNA afectación.
     */
    public function resumenDiasGestion(string $codGea, array $ambito = []): array
    {
        $gestion = DB::table('gestion_academica')->where('cod_gea', $codGea)->first();
        if (! $gestion) {
            return ['error' => 'Gestión no encontrada'];
        }

        $fiiGea = $gestion->fii_gea ?? null;
        $ffiGea = $gestion->ffi_gea ?? null;
        $requeridos = $this->diasRequeridos($codGea);

        // Eventos confirmados de la gestión
        $eventos = CalendarioEvento::where('cod_gea', $codGea)
            ->whereIn('est_cae', ['CONFIRMADO', 'FINALIZADO'])
            ->get()
            ->filter(function ($e) use ($ambito) {
                foreach (['cod_tur', 'cod_cur', 'cod_par'] as $campo) {
                    if ($e->{$campo} !== null && $e->{$campo} !== ($ambito[$campo] ?? null)) {
                        return false;
                    }
                }

                return true;
            });

        // Conjuntos de fechas únicas (evita doble descuento)
        $diasSuspendidos = collect();
        $diasRecuperados = collect();
        $hoy = today()->toDateString();

        foreach ($eventos as $evento) {
            for ($f = Carbon::parse($evento->fii_cae); $f->lte($evento->ffi_cae); $f->addDay()) {
                $fStr = $f->toDateString();
                if (in_array($evento->efe_cae, ['SIN_CLASES', 'SUSPENSION_PARCIAL', 'INGRESO_DIFERIDO', 'SALIDA_ANTICIPADA'], true)) {
                    $diasSuspendidos->push($fStr);
                } elseif ($evento->efe_cae === 'RECUPERACION') {
                    $diasRecuperados->push($fStr);
                }
            }
        }

        $diasSuspendidos = $diasSuspendidos->unique()->values();
        $diasRecuperados = $diasRecuperados->unique()->values();

        // Calcular días efectivos hasta hoy (excluye suspendidos, incluye recuperados)
        $efectivos = 0;
        $planificados = 0;
        if ($fiiGea && $ffiGea) {
            for ($f = Carbon::parse($fiiGea); $f->lte(Carbon::parse($ffiGea)); $f->addDay()) {
                $fStr = $f->toDateString();
                $esSuspendido = $diasSuspendidos->contains($fStr);
                $esRecuperado = $diasRecuperados->contains($fStr);
                $esHabil = ! $f->isWeekend();

                if ($esHabil && ! $esSuspendido || $esRecuperado) {
                    $planificados++;
                    if ($fStr <= $hoy) {
                        $efectivos++;
                    }
                }
            }
        }

        $totalRequeridos = $requeridos['total'];
        $totalSuspendidos = $diasSuspendidos->count();
        $totalRecuperados = $diasRecuperados->count();
        $pendientes = max(0, $totalRequeridos - $efectivos - $totalRecuperados);

        return [
            'requeridos' => $totalRequeridos,
            'por_trimestre' => $requeridos['por_trimestre'],
            'planificados' => $planificados,
            'efectivos' => $efectivos,
            'suspendidos' => $totalSuspendidos,
            'recuperados' => $totalRecuperados,
            'pendientes' => $pendientes,
            'advertencias' => $requeridos['advertencias'],
        ];
    }

    // =========================================================================
    // PROYECCIÓN DE CIERRE
    // =========================================================================

    /**
     * Calcula la proyección de cierre distinguiendo:
     *   - fecha_fin_base       (la oficial de la gestión, NO se modifica)
     *   - fecha_fin_proyectada (calculada con los días pendientes)
     *   - fecha_fin_efectiva   (null hasta que termine la gestión)
     *
     * La proyección NO modifica automáticamente el cierre oficial.
     *
     * Ejemplo:
     *   Fin base: 02/12/2026, Déficit: 2 días → Fin proyectado: 04/12/2026
     */
    public function proyeccionCierre(string $codGea, array $ambito = []): array
    {
        $gestion = DB::table('gestion_academica')->where('cod_gea', $codGea)->first();
        if (! $gestion) {
            return ['error' => 'Gestión no encontrada'];
        }

        $resumen = $this->resumenDiasGestion($codGea, $ambito);
        $fechaFinBase = $gestion->ffi_gea ?? null;
        $pendientes = $resumen['pendientes'] ?? 0;

        $fechaFinProyectada = null;
        if ($fechaFinBase && $pendientes > 0) {
            // Proyecta desde el día siguiente al fin base
            $fechaFinProyectada = $this->proyectarFechaFin(
                $codGea,
                Carbon::parse($fechaFinBase)->addDay()->toDateString(),
                $pendientes,
                $ambito
            );
        } else {
            $fechaFinProyectada = $fechaFinBase;
        }

        return [
            'fecha_inicio' => $gestion->fii_gea ?? null,
            'fecha_fin_base' => $fechaFinBase,
            'fecha_fin_proyectada' => $fechaFinProyectada,
            'fecha_fin_efectiva' => null, // se registra cuando la gestión cierra
            'deficit_dias' => $pendientes,
            'resumen' => $resumen,
        ];
    }

    // =========================================================================
    // ASISTENCIA Y SEGUIMIENTO: SESIÓN SUSPENDIDA NO GENERA FALTA
    // =========================================================================

    /**
     * Determina si una ausencia en una sesión suspendida debe generar falta.
     * Retorna false (no generar falta) si la sesión está suspendida por evento confirmado.
     */
    public function ausenciaGeneraFalta(string $codGea, string $fecha, array $ambito = []): bool
    {
        $analisis = $this->analizarFecha($codGea, $fecha, $ambito);

        // Si la sesión no puede continuar por un evento confirmado, NO genera falta injustificada
        return $analisis['puede_continuar'];
    }
}
