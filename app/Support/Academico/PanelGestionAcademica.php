<?php

namespace App\Support\Academico;

use Carbon\CarbonImmutable;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Facades\DB;
use App\Support\Academico\EsquemaAcademico as Schema;

/** Lectura del expediente anual. No crea eventos ni cambia decisiones institucionales. */
class PanelGestionAcademica
{
    public const NORMA_2026 = 'https://www.minedu.gob.bo/files/documentos-normativos/resoluciones-ministeriales/1_RM_0001_EDUCACIN_REGULAR.pdf';

    public function porGestion(string $tabla, string $gestion): ?Builder
    {
        if (! Schema::hasTable($tabla)) {
            return null;
        }
        if (Schema::hasColumn($tabla, 'cod_gea')) {
            return DB::table($tabla)->where('cod_gea', $gestion);
        }
        if (Schema::hasColumn($tabla, 'cod_gac') && Schema::hasTable('grupo_academico')) {
            return DB::table($tabla)->whereIn('cod_gac', DB::table('grupo_academico')->where('cod_gea', $gestion)->select('cod_gac'));
        }

        return null;
    }

    public function periodos(string $gestion): array
    {
        if (! Schema::hasTable('periodo_evaluacion')) {
            return [];
        }
        $consulta = DB::table('periodo_evaluacion');
        if (Schema::hasColumn('periodo_evaluacion', 'cod_gea')) {
            $consulta->where('cod_gea', $gestion);
        }
        $configuracion = $this->porGestion('configuracion_calendario_gestion', $gestion)?->get() ?? collect();

        $eventos = $this->eventos($gestion);
        return $consulta->orderBy('ord_pev')->get()->map(function ($periodo) use ($configuracion, $eventos) {
            $config = $configuracion->first(fn ($c) => isset($c->cod_pev) ? $c->cod_pev === $periodo->cod_pev : (int) ($c->num_tri_ccg ?? 0) === (int) $periodo->ord_pev);
            $inicio = $config->fii_tri_ccg ?? $periodo->fii_pev ?? null;
            $fin = $config->ffi_tri_ccg ?? $periodo->ffi_pev ?? null;
            $progreso = null;
            if ($inicio && $fin && $inicio <= $fin) {
                $dias = CarbonImmutable::parse($inicio)->diffInDays(CarbonImmutable::parse($fin)) + 1;
                $progreso = (int) max(0, min(100, round((CarbonImmutable::parse($inicio)->diffInDays(now(), false) + 1) / $dias * 100)));
            }

            return ['id' => $periodo->cod_pev, 'nombre' => $periodo->nom_pev, 'orden' => (int) $periodo->ord_pev,
                'estado' => $periodo->est_pev, 'fecha_inicio' => $inicio, 'fecha_fin' => $fin,
                'dias_habiles_referencia' => $config->dias_req_ccg ?? null, 'progreso' => $progreso,
                'dias_estimados' => $inicio && $fin ? ($this->dias(['fecha_inicio' => $inicio, 'fecha_fin' => $fin], $eventos)['estimados'] ?? null) : null,
                'incluye_descanso_pedagogico' => false, 'descanso_pedagogico_dias_habiles' => null,
                'fuente_fechas' => $config ? 'Calendario institucional registrado' : ($inicio ? 'Periodo registrado' : 'Fechas por confirmar')];
        })->all();
    }

    public function eventos(string $gestion): array
    {
        return ($this->porGestion('calendario_evento', $gestion)?->orderBy('fii_cae')->get() ?? collect())->map(fn ($e) => [
            'id' => $e->cod_cae, 'nombre' => $e->nom_cae, 'tipo' => $e->tip_cae,
            'inicio' => substr($e->fii_cae, 0, 10), 'fin' => substr($e->ffi_cae, 0, 10),
            'estado' => $e->est_cae, 'efecto' => $e->efe_cae, 'motivo' => $e->mot_cae ?? '',
            'fuente' => $e->fue_cae ?? '', 'url' => filter_var($e->url_cae ?? '', FILTER_VALIDATE_URL) && in_array(parse_url($e->url_cae, PHP_URL_SCHEME), ['https', 'http'], true) ? $e->url_cae : null,
            'documento' => trim(($e->tip_doc_cae ?? '').' '.($e->num_doc_cae ?? '')),
            'alcance' => $e->niv_cae ?? 'INSTITUCIONAL', 'certeza' => $e->cer_cae ?? 'POR_REVISAR',
            'cod_tur' => $e->cod_tur ?? null, 'cod_cur' => $e->cod_cur ?? null, 'cod_par' => $e->cod_par ?? null,
            'cod_hde' => $e->cod_hde ?? null, 'hora_inicio' => $e->hoi_cae ?? null, 'hora_fin' => $e->hof_cae ?? null,
            'computa' => $e->com_cae ?? null,
        ])->all();
    }

    /** Referencias oficiales revisadas el 04/10/2026. Nunca se incorporan automáticamente a la BD. */
    public function referencias(int $anio): array
    {
        if ($anio !== 2026) {
            return [];
        }

        return [
            ['nombre' => 'Feriado adicional de Corpus Christi', 'fecha' => '2026-06-05', 'fuente' => 'MTEPS · Comunicado DGTHSO-026/2026', 'url' => 'https://www.mintrabajo.gob.bo/wp-content/uploads/2026/05/COMUNICADO-DGTHSO-026-2026.pdf'],
            ['nombre' => 'Traslado del Año Nuevo Andino Amazónico Chaqueño', 'fecha' => '2026-06-22', 'fuente' => 'MTEPS · Comunicado DGTHSO-029/2026', 'url' => 'https://mintrabajo.gob.bo/wp-content/uploads/2026/06/COMUNICADO-DGTHSO-029-2026.pdf'],
            ['nombre' => 'Feriado adicional de la Independencia de Bolivia', 'fecha' => '2026-08-07', 'fuente' => 'MTEPS · Decreto Supremo 5521', 'url' => 'https://mintrabajo.gob.bo/index.php/nota_prensa/la-paz-22/'],
        ];
    }

    public function planificacion(?array $gestion, array $periodos): array
    {
        $faltantes = array_values(array_diff([1, 2, 3], array_column($periodos, 'orden')));
        $inicio = $gestion['fecha_inicio'] ?? null;
        $norma = (int) ($gestion['anio'] ?? 0) === 2026;
        $temprana = $inicio && today()->toDateString() <= substr($inicio, 0, 10)
            && in_array($gestion['estado'] ?? '', ['PLANIFICADA', 'ACTIVA'], true);
        $mensaje = ! $norma ? 'Confirma la normativa ministerial de esta gestión antes de completar sus periodos.'
            : (! $faltantes ? 'Los tres trimestres ya están registrados.'
                : (! $temprana ? 'La gestión ya inició. La incorporación de trimestres está disponible durante la planificación inicial.' : 'Puedes completar los trimestres faltantes desde Periodos de evaluación.'));

        return ['faltantes' => $faltantes, 'puede_agregar' => $norma && $temprana && $faltantes !== [], 'mensaje' => $mensaje];
    }

    public function consultar(?array $gestion): array
    {
        $vacio = ['eventos' => [], 'referencias' => [], 'alertas' => [], 'movimientos' => [], 'cursos' => [],
            'inscripciones' => [], 'novedades' => [], 'comparativas' => [], 'conteos' => [], 'dias' => null, 'trayectoria' => [], 'anomalias' => []];
        if (! $gestion) {
            $vacio['alertas'][] = $this->aviso('warning', 'Selecciona una gestión', 'Elige el expediente anual que quieres revisar.', 'anios');

            return $vacio;
        }
        $id = $gestion['id'];
        $eventos = $this->eventos($id);
        $referencias = $this->referencias((int) $gestion['anio']);
        $inscripciones = $this->porGestion('inscripcion_estudiante', $id)?->get() ?? collect();
        if (Schema::hasColumn('inscripcion_estudiante', 'cod_gac') && Schema::hasTable('grupo_academico')) {
            $grupos = DB::table('grupo_academico')->where('cod_gea', $id)->get()->keyBy('cod_gac');
            $inscripciones->each(function ($i) use ($grupos) {
                $grupo = $grupos->get($i->cod_gac);
                $i->cod_cur = $grupo->cod_cur ?? null;
                $i->cod_par = $grupo->cod_par ?? null;
                $i->cod_tur = $grupo->cod_tur ?? null;
            });
        }
        $novedadesQuery = $this->porGestion('novedad_estudiante', $id);
        if (! $novedadesQuery && Schema::hasTable('novedad_estudiante') && Schema::hasColumn('novedad_estudiante', 'cod_ins')) {
            $novedadesQuery = DB::table('novedad_estudiante')->whereIn('cod_ins', $inscripciones->pluck('cod_ins'));
        }
        $novedades = $novedadesQuery?->orderByDesc('fii_nes')->get() ?? collect();
        $cursos = Schema::hasTable('curso') ? DB::table('curso')->pluck('nom_cur', 'cod_cur') : collect();
        $paralelos = Schema::hasTable('paralelo') ? DB::table('paralelo')->pluck('nom_par', 'cod_par') : collect();
        $distribucion = $inscripciones->groupBy(fn ($i) => ($i->cod_cur ?? '').'|'.($i->cod_par ?? ''))
            ->map(fn ($grupo) => ['curso' => ($cursos[$grupo->first()->cod_cur] ?? 'Curso por confirmar'), 'nombre' => ($cursos[$grupo->first()->cod_cur] ?? 'Curso por confirmar').' '.($paralelos[$grupo->first()->cod_par] ?? ''), 'cantidad' => $grupo->count()])->sortBy('nombre')->values()->all();
        $estados = $inscripciones->groupBy('est_ins')->map(fn ($grupo, $estado) => ['nombre' => $this->humanizar($estado), 'cantidad' => $grupo->count()])->values()->all();
        $personas = Schema::hasTable('estudiante') && Schema::hasTable('persona') ? DB::table('estudiante as e')
            ->join('persona as p', 'p.cod_per', '=', 'e.cod_per')->whereIn('e.cod_est', $inscripciones->pluck('cod_est')->merge($novedades->pluck('cod_est'))->filter()->unique())
            ->get(['e.cod_est', 'p.nom_per', 'p.ape_pat_per', 'p.ape_mat_per'])->keyBy('cod_est') : collect();
        $nombre = fn ($codigo) => isset($personas[$codigo]) ? mb_strtoupper(trim($personas[$codigo]->nom_per.' '.$personas[$codigo]->ape_pat_per.' '.$personas[$codigo]->ape_mat_per)) : 'Estudiante por confirmar';
        $movimientos = $novedades->map(fn ($n) => ['nombre' => $nombre($n->cod_est ?? $inscripciones->firstWhere('cod_ins', $n->cod_ins ?? '')?->cod_est),
            'tipo' => $this->humanizar($n->tip_nes), 'fecha' => $n->fii_nes, 'estado' => $this->humanizar($n->est_nes),
            'motivo' => $n->mot_nes ?? '', 'observacion' => $n->obs_nes ?? ''])->all();
        foreach ($inscripciones as $i) {
            foreach (['fec_ret_ins' => ['Retiro', 'mot_ret_ins'], 'fec_anu_ins' => ['Anulación', 'mot_anu_ins']] as $fecha => [$tipo, $motivo]) {
                if (! empty($i->{$fecha})) {
                    $movimientos[] = ['nombre' => $nombre($i->cod_est), 'tipo' => $tipo, 'fecha' => $i->{$fecha}, 'estado' => $this->humanizar($i->est_ins), 'motivo' => $i->{$motivo} ?? '', 'observacion' => $i->obs_ins ?? ''];
                }
            }
            if (strtoupper($i->est_ins) === 'OBSERVADA' || ! empty($i->mot_obs_ins)) {
                $movimientos[] = ['nombre' => $nombre($i->cod_est), 'tipo' => 'Observación', 'fecha' => $i->updated_at ?? $i->fei_ins ?? '', 'estado' => $this->humanizar($i->est_ins), 'motivo' => $i->mot_obs_ins ?? '', 'observacion' => $i->obs_ins ?? ''];
            }
        }
        $alertas = [];
        foreach ($referencias as &$referencia) {
            $referencia['registrado'] = collect($eventos)->contains(fn ($e) => in_array($e['estado'], ['CONFIRMADO', 'FINALIZADO'], true) && $e['efecto'] === 'SIN_CLASES'
                && ! $e['cod_tur'] && ! $e['cod_cur'] && ! $e['cod_par'] && ! $e['cod_hde'] && ! $e['hora_inicio'] && ! $e['hora_fin']
                && $e['inicio'] <= $referencia['fecha'] && $e['fin'] >= $referencia['fecha']);
            if (! $referencia['registrado']) {
                $alertas[] = $this->aviso('warning', 'Feriado por revisar · '.CarbonImmutable::parse($referencia['fecha'])->format('d/m'), $referencia['nombre'].' no figura como suspensión general en el calendario registrado. Revisa la disposición y su aplicación institucional.', 'calendario');
            }
        }
        unset($referencia);
        $pendientes = $inscripciones->filter(fn ($i) => in_array(strtoupper($i->est_ins), ['PENDIENTE', 'OBSERVADA'], true))->count();
        if ($pendientes) {
            $alertas[] = $this->aviso('warning', 'Inscripciones que requieren atención', $pendientes.' inscripciones pendientes u observadas. Revisa sus datos y motivos antes del cierre.', 'inscripciones');
        }
        $periodos = $this->periodos($id);
        $regla = $this->planificacion($gestion, $periodos);
        if ($regla['faltantes']) {
            $alertas[] = $this->aviso('error', 'Trimestres incompletos', $regla['mensaje'], 'periodos');
        }
        foreach ($periodos as $p) {
            if (! $p['fecha_inicio'] || ! $p['fecha_fin']) {
                $alertas[] = $this->aviso('warning', 'Fechas por confirmar', $p['nombre'].' aún no tiene un rango registrado para esta gestión.', 'periodos');
            }
        }
        $dias = $this->dias($gestion, $eventos);
        if ($dias && $dias['estimados'] < 200 && (int) $gestion['anio'] === 2026) {
            $alertas[] = $this->aviso('warning', 'Revisar los días curriculares', 'El calendario registrado permite estimar '.$dias['estimados'].' días de lunes a viernes. Contrasta suspensiones, recuperaciones y los 200 días efectivos requeridos; esta estimación no certifica cumplimiento.', 'calendario');
        }
        if (! Schema::hasTable('calendario_evento')) {
            $alertas[] = $this->aviso('error', 'Calendario no disponible', 'No pudimos consultar los eventos institucionales. Puedes seguir revisando el expediente y volver a intentarlo.', 'calendario');
        }
        $anomalias = [];
        $duplicadas = $inscripciones->whereIn('est_ins', ['CONFIRMADA', 'ACTIVA', 'ACTIVO'])->groupBy('cod_est')->filter(fn ($g) => $g->count() > 1)->count();
        if ($duplicadas) {
            $anomalias[] = $this->aviso('error', 'Más de una inscripción vigente', $duplicadas.' estudiantes tienen varias inscripciones marcadas como vigentes en esta gestión. Revisa cada caso antes de consolidar datos.', 'inscripciones');
        }
        $fueraRango = $inscripciones->filter(fn ($i) => ! empty($i->fei_ins) && ((int) substr($i->fei_ins, 0, 4) !== (int) $gestion['anio']))->count();
        if ($fueraRango) {
            $anomalias[] = $this->aviso('warning', 'Fechas de inscripción de otro año', $fueraRango.' registros tienen una fecha que no coincide con el año de la gestión. Comprueba si corresponden a una inscripción anticipada o a un dato incorrecto.', 'inscripciones');
        }
        $sinGrupo = $inscripciones->filter(function ($i) {
            return isset($i->cod_gac) ? ! $i->cod_gac : empty($i->cod_cur) || empty($i->cod_par) || empty($i->cod_tur);
        })->count();
        if ($sinGrupo) {
            $anomalias[] = $this->aviso('warning', 'Ubicación académica por completar', $sinGrupo.' inscripciones no tienen completo su grupo, curso, paralelo o turno.', 'inscripciones');
        }
        foreach ($periodos as $i => $periodo) {
            foreach (array_slice($periodos, $i + 1) as $otro) {
                if ($periodo['fecha_inicio'] && $periodo['fecha_fin'] && $otro['fecha_inicio'] && $otro['fecha_fin'] && $periodo['fecha_inicio'] <= $otro['fecha_fin'] && $otro['fecha_inicio'] <= $periodo['fecha_fin']) {
                    $anomalias[] = $this->aviso('error', 'Trimestres con fechas superpuestas', $periodo['nombre'].' coincide con '.$otro['nombre'].'. Revisa la planificación y su respaldo.', 'periodos');
                }
            }
        }
        $sinFuente = collect($eventos)->filter(fn ($e) => ! $e['url'])->count();
        if ($sinFuente) {
            $anomalias[] = $this->aviso('warning', 'Documentación de eventos por completar', $sinFuente.' eventos no tienen enlace a su documento de respaldo. Sus fuentes escritas se conservan para la revisión.', 'calendario');
        }
        if ($inscripciones->isNotEmpty() && (! Schema::hasTable('inscripcion_vigencia') || ! DB::table('inscripcion_vigencia')->whereIn('cod_ins', $inscripciones->pluck('cod_ins'))->exists())) {
            $anomalias[] = $this->aviso('warning', 'Trayectoria de grupos por confirmar', 'Las inscripciones actuales están disponibles, pero no hay vigencias registradas para reconstruir cambios de curso en fechas anteriores. El analista distingue este límite del alumnado actual.', 'seguimiento');
        }
        $trayectoria = $inscripciones->filter(fn ($i) => ! empty($i->fei_ins))->groupBy(fn ($i) => substr($i->fei_ins, 0, 10))
            ->map(fn ($g, $fecha) => ['fecha' => $fecha, 'inscripciones' => $g->count()])->sortBy('fecha')->values();
        $acumulado = 0;
        $trayectoria = $trayectoria->map(function ($punto) use (&$acumulado) {
            $acumulado += $punto['inscripciones'];
            return $punto + ['acumulado' => $acumulado];
        })->all();
        $alertas = array_merge($alertas, $anomalias);

        return ['eventos' => $eventos, 'referencias' => $referencias, 'alertas' => $alertas,
            'recuperaciones' => array_values(array_filter($eventos, fn($e)=>str_starts_with($e['tipo'],'RECUPERACION') && $e['estado'] !== 'CANCELADO')),
            'movimientos' => collect($movimientos)->sortByDesc('fecha')->values()->all(), 'cursos' => $distribucion,
            'inscripciones' => $estados, 'novedades' => $novedades->groupBy('tip_nes')->map(fn ($g, $tipo) => ['nombre' => $this->humanizar($tipo), 'cantidad' => $g->count()])->values()->all(),
            'comparativas' => DB::table('gestion_academica')->orderByDesc('ani_gea')->get()->map(function ($g) {
                $docentesComparacion = ($this->porGestion('plan_asignatura', $g->cod_gea)?->pluck('cod_doc') ?? collect())
                    ->merge($this->porGestion('plan_especialidad', $g->cod_gea)?->pluck('cod_doc') ?? collect())->filter()->unique()->count();
                return ['anio' => $g->ani_gea, 'estado' => $this->humanizar($g->est_gea),
                    'inscripciones' => $this->porGestion('inscripcion_estudiante', $g->cod_gea)?->count(),
                    'planes_tecnicos' => $this->porGestion('plan_especialidad', $g->cod_gea)?->count(),
                    'grupos' => $this->porGestion('grupo_academico', $g->cod_gea)?->count(),
                    'horarios' => $this->porGestion('horario', $g->cod_gea)?->count(), 'docentes' => $docentesComparacion,
                    'planes' => $this->porGestion('plan_asignatura', $g->cod_gea)?->count(), 'eventos' => $this->porGestion('calendario_evento', $g->cod_gea)?->count()];
            })->all(), 'conteos' => ['inscripciones' => $inscripciones->count(), 'novedades' => $novedades->count(), 'movimientos' => count($movimientos), 'eventos' => count($eventos), 'recuperaciones' => count(array_filter($eventos, fn($e)=>str_starts_with($e['tipo'],'RECUPERACION') && in_array($e['estado'],['CONFIRMADO','FINALIZADO'],true)))], 'dias' => $dias,
            'trayectoria' => $trayectoria, 'anomalias' => $anomalias];
    }

    private function dias(array $gestion, array $eventos): ?array
    {
        if (! $gestion['fecha_inicio'] || ! $gestion['fecha_fin'] || $gestion['fecha_inicio'] > $gestion['fecha_fin']) {
            return null;
        }
        $inicio = CarbonImmutable::parse($gestion['fecha_inicio']);
        $fin = CarbonImmutable::parse($gestion['fecha_fin']);
        if ($inicio->diffInDays($fin) > 370) {
            return null;
        }
        $base = $lectivos = 0;
        for ($dia = $inicio; $dia->lte($fin); $dia = $dia->addDay()) {
            $laborable = $dia->isWeekday();
            $base += (int) $laborable;
            foreach ($eventos as $evento) {
                if ($evento['inicio'] > $dia->toDateString() || $evento['fin'] < $dia->toDateString()
                    || ! in_array($evento['estado'], ['CONFIRMADO', 'FINALIZADO'], true) || $evento['cod_tur'] || $evento['cod_cur'] || $evento['cod_par']
                    || $evento['cod_hde'] || $evento['hora_inicio'] || $evento['hora_fin']) {
                    continue;
                }
                if ($evento['efecto'] === 'SIN_CLASES') {
                    $laborable = false;
                    break;
                }
                if ($evento['efecto'] === 'RECUPERACION' && $evento['computa']) {
                    $laborable = true;
                }
            }
            $lectivos += (int) $laborable;
        }

        return ['base' => $base, 'estimados' => $lectivos, 'ajuste' => $base - $lectivos];
    }

    private function aviso(string $nivel, string $titulo, string $mensaje, string $vista): array
    {
        return compact('nivel', 'titulo', 'mensaje', 'vista');
    }

    public function humanizar(?string $texto): string
    {
        return ucfirst(mb_strtolower(str_replace('_', ' ', $texto ?? 'Por confirmar')));
    }
}
