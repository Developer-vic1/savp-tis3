<?php

namespace App\Support\Academico;

use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Support\Str;

/** Simula horarios vigentes y sus dependencias. Nunca confirma eventos ni cambia resultados. */
class CalendarioAcademicoInteligente
{
    public function analizarImpactoEvento(array $evento): array
    {
        $evento = array_merge(CasosCalendarioInstitucional::vacio(), $evento);
        $gestion = DB::table('gestion_academica')->where('cod_gea', $evento['cod_gea'] ?? null)->first();
        if (! $gestion) return $this->bloqueo('Selecciona una gestión registrada.');
        if (! in_array($gestion->est_gea, ['ACTIVA', 'ACTIVO'], true) || DB::table('gestion_academica')->whereIn('est_gea', ['ACTIVA', 'ACTIVO'])->count() !== 1) {
            return $this->bloqueo('El análisis de impacto corresponde únicamente a la gestión activa. Las anteriores se conservan para consulta.');
        }
        $validacion = Validator::make($evento, [
            'inicio' => ['required', 'date_format:Y-m-d', 'after_or_equal:'.substr($gestion->fii_gea, 0, 10)],
            'fin' => ['required', 'date_format:Y-m-d', 'after_or_equal:inicio', 'before_or_equal:'.substr($gestion->ffi_gea, 0, 10)],
            'recuperacion' => ['required', Rule::in(['PENDIENTE','NO','POR_CONFIRMAR','PROPUESTA'])],
            'modalidad_recuperacion' => ['required', Rule::in(['POR_DEFINIR','PRESENCIAL','VIRTUAL'])],
            'medio_recuperacion' => ['required', Rule::in(['AULA_INSTITUCIONAL','VIDEOLLAMADA','OTRO'])],
            'plan_recuperacion' => ['exclude_unless:modalidad_recuperacion,VIRTUAL','required','string','min:10','max:1000'],
            'enlace_recuperacion' => ['nullable','url:https','max:2000'],
            'observacion_recuperacion' => ['required_if:recuperacion,POR_CONFIRMAR','nullable','string','min:10','max:1000'],
            'fecha_recuperacion' => ['exclude_unless:recuperacion,PROPUESTA','required','date_format:Y-m-d','after_or_equal:fin'],
            'hora_recuperacion_inicio' => ['exclude_unless:recuperacion,PROPUESTA','required','date_format:H:i'],
            'hora_recuperacion_fin' => ['exclude_unless:recuperacion,PROPUESTA','required','date_format:H:i','after:hora_recuperacion_inicio'],
            'efecto' => ['required', Rule::in(array_keys(CasosCalendarioInstitucional::efectos()))],
            'jornada' => ['required', Rule::in(['COMPLETA', 'PARCIAL'])],
            'grupos' => ['array', 'max:100'], 'grupos.*' => ['string', 'distinct'],
            'turnos' => ['array', 'max:10'], 'turnos.*' => ['string', 'distinct'],
            'alcance' => ['required', Rule::in(['INSTITUCIONAL', 'TURNO', 'GRUPO'])],
            'hora_inicio' => ['exclude_unless:jornada,PARCIAL', 'required', 'date_format:H:i'],
            'hora_fin' => ['exclude_unless:jornada,PARCIAL', 'required', 'date_format:H:i', 'after:hora_inicio'],
        ]);
        if ($validacion->fails()) return $this->bloqueo('Revisa las fechas, el alcance y las horas antes de calcular.');
        if ($evento['alcance'] !== 'INSTITUCIONAL') {
            $campo = $evento['alcance'] === 'GRUPO' ? 'cod_gac' : 'cod_tur';
            $valores = $evento['alcance'] === 'GRUPO' ? ($evento['grupos'] ?: array_filter([$evento['grupo']])) : ($evento['turnos'] ?: array_filter([$evento['turno']]));
            if (! $valores || DB::table('grupo_academico')->where('cod_gea', $gestion->cod_gea)->whereIn($campo, $valores)->distinct()->count($campo) !== count($valores)) {
                return $this->bloqueo('Elige un grupo o turno que pertenezca a esta gestión.');
            }
        }
        $inicio = CarbonImmutable::parse($evento['inicio']);
        $fin = CarbonImmutable::parse($evento['fin']);
        if ($inicio->diffInDays($fin) >= 366) return $this->bloqueo('El caso debe quedar dentro de una gestión anual.');
        $advertencias = [];
        $panel = app(PanelGestionAcademica::class);
        foreach ($panel->eventos($gestion->cod_gea) as $registrado) {
            if (! in_array($registrado['estado'], ['CANCELADO'], true) && $registrado['inicio'] <= $evento['fin'] && $registrado['fin'] >= $evento['inicio']) {
                $advertencias[] = 'El rango coincide con '.$registrado['nombre'].'. Revisa su alcance y horas para evitar contar dos veces la misma interrupción.';
            }
        }
        if ($inicio->diffInDays($fin) >= 14) $advertencias[] = 'Caso prolongado: revisa continuidad pedagógica, acceso virtual y un plan de recuperación por periodo.';
        if (in_array($evento['efecto'], ['SALIDA', 'ACTIVIDAD', 'VIRTUAL'], true)) {
            $advertencias[] = 'Este caso cambia la actividad o modalidad. No equivale automáticamente a un día sin trabajo curricular.';
        }
        if (empty($evento['documento']) && empty($evento['url_documento'])) {
            $advertencias[] = 'El respaldo es opcional para preparar el caso. La propuesta sigue pendiente de revisión institucional.';
        }
        $horarios = $this->horarios($gestion->cod_gea, $evento['inicio'], $evento['fin']);
        $seleccion = $horarios->filter(fn ($h) => $evento['alcance'] === 'GRUPO' ? in_array($h->cod_gac, $evento['grupos'] ?: [$evento['grupo']], true)
            : ($evento['alcance'] === 'TURNO' ? in_array($h->cod_tur, $evento['turnos'] ?: [$evento['turno']], true) : true));
        $sesiones = $minutos = 0;
        $afectados = collect();
        $detalle = [];
        $fechasGrupo = [];
        $minutosGrupo = [];
        $dias = [1 => 'LUNES', 2 => 'MARTES', 3 => 'MIERCOLES', 4 => 'JUEVES', 5 => 'VIERNES', 6 => 'SABADO', 7 => 'DOMINGO'];
        for ($dia = $inicio; $dia->lte($fin); $dia = $dia->addDay()) {
            foreach ($seleccion as $h) {
                if (! $this->vigente($h, $dia, $dias) || ! ($duracion = $this->minutosAfectados($h, $evento))) continue;
                $sesiones++;
                $minutos += $duracion;
                $minutosGrupo[$h->cod_gac] = ($minutosGrupo[$h->cod_gac] ?? 0) + $duracion;
                $afectados->put($h->cod_hde, $h);
                $fechasGrupo[$h->cod_gac]['inicio'] ??= $dia->toDateString();
                $fechasGrupo[$h->cod_gac]['fin'] = $dia->toDateString();
                $clave = $h->cod_gac.'|'.($h->cod_pas ?: $h->cod_pes);
                $detalle[$clave] ??= ['curso' => mb_strtoupper($h->curso), 'grupo' => mb_strtoupper($h->curso.' '.$h->paralelo.' · '.$h->turno),
                    'materia' => $h->materia, 'docente' => mb_strtoupper($h->docente ?: 'Docente por revisar'), 'sesiones' => 0, 'minutos' => 0,
                    'responsable' => ($evento['responsable'] ?? '') === $h->cod_doc];
                $detalle[$clave]['sesiones']++;
                $detalle[$clave]['minutos'] += $duracion;
            }
        }
        $grupos = $afectados->pluck('cod_gac')->unique();
        $cursos = $afectados->pluck('curso')->unique()->sort()->values()->all();
        $actuales = DB::table('inscripcion_estudiante as i')->join('grupo_academico as g', function ($q) {
            $q->on('g.cod_gea', '=', 'i.cod_gea')->on('g.cod_cur', '=', 'i.cod_cur')->on('g.cod_par', '=', 'i.cod_par')->on('g.cod_tur', '=', 'i.cod_tur');
        })->where('i.cod_gea', $gestion->cod_gea)->whereIn('g.cod_gac', $grupos)
            ->whereIn('i.est_ins', ['CONFIRMADA', 'ACTIVA', 'ACTIVO'])->distinct()->count('i.cod_est');
        $historicos = 0;
        if ($grupos->isNotEmpty()) {
            $vigencias = DB::table('inscripcion_vigencia as v')->join('inscripcion_estudiante as i', 'i.cod_ins', '=', 'v.cod_ins')
                ->where('i.cod_gea', $gestion->cod_gea)->where('v.est_ivg', '<>', 'ANULADO')
                ->where(function ($q) use ($fechasGrupo) {
                    foreach ($fechasGrupo as $grupo => $fechas) {
                        $q->orWhere(fn ($s) => $s->where('v.cod_gac', $grupo)->where('v.fii_ivg', '<=', $fechas['fin'])
                            ->where(fn ($p) => $p->whereNull('v.ffi_ivg')->orWhere('v.ffi_ivg', '>=', $fechas['inicio'])));
                    }
                });
            $historicos = $vigencias->distinct()->count('i.cod_est');
            if (! $historicos && $actuales) {
                $historicos = null;
                $advertencias[] = 'No se encontraron vigencias coincidentes; los inscritos actuales no reconstruyen por sí solos el alumnado de esa fecha.';
            }
        }
        $tareasQuery = DB::table('tarea as t')->join('clase_virtual as c', 'c.cod_cla', '=', 't.cod_cla')
            ->where(fn ($q) => $q->whereIn('c.cod_pas', $afectados->pluck('cod_pas')->filter())->orWhereIn('c.cod_pes', $afectados->pluck('cod_pes')->filter()))
            ->where('t.fec_lim_tar', '>=', $evento['inicio'].' 00:00:00')->where('t.fec_lim_tar', '<=', $evento['fin'].' 23:59:59')
            ->whereNotIn('t.est_tar', ['BORRADOR', 'ANULADA', 'CANCELADA']);
        $tareas = $tareasQuery->selectRaw("count(*) as total, count(case when t.tip_tar in ('EVALUACION','EXAMEN','PRUEBA') then 1 end) as evaluaciones")->first();
        if ($tareas->total) $advertencias[] = 'Hay '.$tareas->total.' vencimientos en el rango. Revísalos individualmente; no se reprograman al preparar este caso.';
        if (! $sesiones) $advertencias[] = 'No hay sesiones del horario vigente en esta selección. Revisa fechas y ámbito antes de ampliar el caso.';
        $responsable = $this->revisarResponsable($evento, $horarios, $afectados, $inicio, $fin, $dias);
        $advertencias = array_merge($advertencias, $responsable['avisos']);
        $recuperacion = $this->revisarRecuperacion($evento, $afectados, $gestion, $panel, $dias);
        $limitesRecuperacion = app(RecuperacionCalendario::class)->revisar($evento, ['id'=>$gestion->cod_gea,'fecha_fin'=>$gestion->ffi_gea], $minutosGrupo ? min($minutosGrupo) : 0);
        $recuperacion['avisos'] = array_merge($recuperacion['avisos'],$limitesRecuperacion['avisos']);
        $recuperacion['bloqueos'] = array_values($limitesRecuperacion['bloqueos']);
        $recuperacion['maximo_minutos_por_grupo'] = $minutosGrupo ? min($minutosGrupo) : 0;
        $recuperacion['observacion'] = $evento['observacion_recuperacion'];
        $recuperacion['modalidad'] = $evento['modalidad_recuperacion'];
        $recuperacion['plan'] = $evento['plan_recuperacion'];
        if (in_array($evento['recuperacion'], ['PROPUESTA','POR_CONFIRMAR'], true)) {
            if ($evento['modalidad_recuperacion'] === 'VIRTUAL') $recuperacion['avisos'][] = 'La recuperación virtual conserva los límites de fecha, duración y trimestre. Confirma conexión, dispositivos, materiales y acompañamiento para los '.$historicos.' estudiantes; no damos por hecho que puedan conectarse. Los cruces de docentes y grupos siguen necesitando coordinación.';
            elseif ($evento['modalidad_recuperacion'] === 'POR_DEFINIR') $recuperacion['avisos'][] = 'La modalidad de recuperación está pendiente. Coordina si será presencial o virtual antes de confirmarla.';
        }
        $nombres = count($cursos) < 5;
        return ['puede_continuar' => ! $recuperacion['bloqueos'], 'bloqueos' => $recuperacion['bloqueos'], 'advertencias' => $advertencias,
            'caso' => ['inicio' => $evento['inicio'], 'fin' => $evento['fin'], 'jornada' => $evento['jornada'],
                'hora_inicio' => $evento['jornada'] === 'PARCIAL' ? $evento['hora_inicio'] : null,
                'hora_fin' => $evento['jornada'] === 'PARCIAL' ? $evento['hora_fin'] : null,
                'duracion' => $evento['jornada'] === 'PARCIAL'
                    ? $this->formatearDuracion((int) CarbonImmutable::parse($evento['hora_inicio'])->diffInMinutes(CarbonImmutable::parse($evento['hora_fin']))) : null],
            'responsable' => $responsable,
            'recuperacion' => $recuperacion,
            'impacto' => ['sesiones' => $sesiones, 'minutos' => $minutos, 'duracion' => $this->formatearDuracion($minutos), 'estudiantes' => $historicos,
                'estudiantes_actuales' => $actuales, 'docentes' => $afectados->pluck('cod_doc')->filter()->unique()->count(),
                'grupos' => $grupos->count(), 'tareas' => (int) $tareas->total, 'evaluaciones' => (int) $tareas->evaluaciones,
                'cursos' => $cursos, 'materias' => $afectados->pluck('materia')->unique()->count(), 'efecto' => $evento['efecto'],
                'sesiones_suspendidas' => in_array($evento['efecto'], ['SUSPENSION', 'PARCIAL'], true) ? $sesiones : 0,
                'sesiones_virtuales' => $evento['efecto'] === 'VIRTUAL' ? $sesiones : 0,
                'mostrar_docentes' => $nombres,
                'detalle' => array_values(array_map(function ($fila) use ($nombres) {
                    $fila['duracion'] = $this->formatearDuracion($fila['minutos']);
                    if (! $nombres) unset($fila['docente']);
                    return $fila;
                }, $detalle))]];
    }

    private function formatearDuracion(int $minutos): string
    {
        $horas = intdiv($minutos, 60);
        $resto = $minutos % 60;
        return ($horas ? $horas.' h' : '').($resto ? ($horas ? ' ' : '').$resto.' min' : ($horas ? '' : '0 min'));
    }

    private function horarios(string $gestion, string $inicio, string $fin)
    {
        return DB::table('horario_detalle as d')->join('horario as h', 'h.cod_hor', '=', 'd.cod_hor')
            ->join('grupo_academico as g', 'g.cod_gac', '=', 'h.cod_gac')->join('curso as c', 'c.cod_cur', '=', 'g.cod_cur')
            ->join('paralelo as par', 'par.cod_par', '=', 'g.cod_par')->join('turno as tur', 'tur.cod_tur', '=', 'g.cod_tur')
            ->join('horario_bloque as b', 'b.cod_hbl', '=', 'd.cod_hbl')->join('plantilla_horaria as p', 'p.cod_pho', '=', 'h.cod_pho')
            ->leftJoin('plan_asignatura as pa', 'pa.cod_pas', '=', 'd.cod_pas')->leftJoin('asignatura as a', 'a.cod_asi', '=', 'pa.cod_asi')
            ->leftJoin('plan_especialidad as pe', 'pe.cod_pes', '=', 'd.cod_pes')->leftJoin('especialidad_tecnica as esp', 'esp.cod_esp', '=', 'pe.cod_esp')
            ->leftJoin('docente as doc', function ($q) { $q->on('doc.cod_doc', '=', DB::raw('coalesce(pa.cod_doc, pe.cod_doc)')); })
            ->leftJoin('personal_institucional as pin', 'pin.cod_pin', '=', 'doc.cod_pin')->leftJoin('persona as per', 'per.cod_per', '=', 'pin.cod_per')
            ->where('g.cod_gea', $gestion)->where('d.est_hde', 'ACTIVO')->where('h.est_hor', '<>', 'ANULADO')
            ->where('b.tip_hbl', 'CLASE')->where('b.est_hbl', 'ACTIVO')->where('p.est_pho', true)
            ->where('h.fii_hor', '<=', $fin)->where(fn ($q) => $q->whereNull('h.ffi_hor')->orWhere('h.ffi_hor', '>=', $inicio))
            ->where(fn ($q) => $q->whereNull('p.fec_ini_pho')->orWhere('p.fec_ini_pho', '<=', $fin))
            ->where(fn ($q) => $q->whereNull('p.fec_fin_pho')->orWhere('p.fec_fin_pho', '>=', $inicio))
            ->whereRaw("coalesce(pa.est_pas, pe.est_pes) <> 'ANULADO'")
            ->get(['d.cod_hde', 'd.cod_pas', 'd.cod_pes', 'd.dia_hde', 'g.cod_gac', 'g.cod_cur', 'g.cod_par', 'g.cod_tur',
                'h.fii_hor', 'h.ffi_hor', 'p.fec_ini_pho', 'p.fec_fin_pho', 'b.hor_ini_hbl', 'b.hor_fin_hbl',
                'c.nom_cur as curso', 'par.nom_par as paralelo', 'tur.nom_tur as turno',
                DB::raw('coalesce(pa.cod_doc, pe.cod_doc) as cod_doc'), DB::raw('coalesce(a.nom_asi, esp.nom_esp) as materia'),
                DB::raw('coalesce(pa.fii_pas, pe.fii_pes) as inicio_plan'), DB::raw('coalesce(pa.ffi_pas, pe.ffi_pes) as fin_plan'),
                DB::raw("concat_ws(' ', per.nom_per, per.ape_pat_per, per.ape_mat_per) as docente")]);
    }

    private function vigente($h, CarbonImmutable $dia, array $dias): bool
    {
        $fecha = $dia->toDateString();
        if (Str::upper(Str::ascii($h->dia_hde)) !== $dias[$dia->dayOfWeekIso]) return false;
        foreach (['fii_hor', 'fec_ini_pho', 'inicio_plan'] as $campo) if ($h->$campo && substr($h->$campo, 0, 10) > $fecha) return false;
        foreach (['ffi_hor', 'fec_fin_pho', 'fin_plan'] as $campo) if ($h->$campo && substr($h->$campo, 0, 10) < $fecha) return false;
        return true;
    }

    private function minutosAfectados($h, array $evento): int
    {
        $inicio = substr($h->hor_ini_hbl, 0, 5);
        $fin = substr($h->hor_fin_hbl, 0, 5);
        if ($evento['jornada'] === 'PARCIAL') {
            $inicio = max($inicio, $evento['hora_inicio']);
            $fin = min($fin, $evento['hora_fin']);
        }
        if ($inicio >= $fin) return 0;
        $a = explode(':', $inicio); $b = explode(':', $fin);
        return ($b[0] * 60 + $b[1]) - ($a[0] * 60 + $a[1]);
    }

    private function revisarResponsable(array $evento, $horarios, $afectados, CarbonImmutable $inicio, CarbonImmutable $fin, array $dias): array
    {
        $resultado = ['nombre' => null, 'imparte_en_grupo' => false, 'cruces' => [], 'avisos' => []];
        if (! in_array($evento['tipo'], ['VISITA_UNIVERSIDAD', 'VIAJE'], true) && $evento['efecto'] !== 'SALIDA') return $resultado;
        if (empty($evento['responsable'])) {
            $resultado['avisos'][] = 'Falta definir el docente acompañante. Revisa autorización, responsables y continuidad de las otras clases antes de realizar la salida.';
            return $resultado;
        }
        $responsable = $horarios->firstWhere('cod_doc', $evento['responsable']);
        $resultado['nombre'] = $responsable ? mb_strtoupper($responsable->docente) : 'Responsable por revisar';
        $resultado['imparte_en_grupo'] = $afectados->contains('cod_doc', $evento['responsable']);
        if (! $resultado['imparte_en_grupo']) $resultado['avisos'][] = 'El acompañante no coincide con los docentes de las materias afectadas en estas horas. Confirma quién queda a cargo y la autorización.';
        $grupos = $afectados->pluck('cod_gac')->unique();
        $cruces = [];
        for ($dia = $inicio; $dia->lte($fin); $dia = $dia->addDay()) {
            foreach ($horarios as $h) {
                if ($h->cod_doc !== $evento['responsable'] || $grupos->contains($h->cod_gac) || ! $this->vigente($h, $dia, $dias) || ! $this->minutosAfectados($h, $evento)) continue;
                $clave = $dia->toDateString().'|'.$h->cod_hde;
                $cruces[$clave] = ['fecha' => $dia->toDateString(), 'grupo' => mb_strtoupper($h->curso.' '.$h->paralelo.' · '.$h->turno),
                    'materia' => $h->materia, 'horas' => substr($h->hor_ini_hbl, 0, 5).'–'.substr($h->hor_fin_hbl, 0, 5)];
            }
        }
        $resultado['cruces_total'] = count($cruces);
        $resultado['cruces'] = array_slice(array_values($cruces), 0, 12);
        if ($cruces) $resultado['avisos'][] = 'El acompañante tiene '.count($cruces).' sesiones coincidentes en otros grupos. Define cobertura o modifica las horas de la salida.';
        else $resultado['avisos'][] = 'No se detectan cruces del acompañante en los horarios registrados. Esto no confirma disponibilidad personal ni una autorización.';
        return $resultado;
    }

    private function bloqueo(string $mensaje): array
    {
        return ['puede_continuar' => false, 'bloqueos' => [$mensaje], 'advertencias' => [], 'impacto' => null];
    }

    private function revisarRecuperacion(array $evento, $afectados, $gestion, PanelGestionAcademica $panel, array $dias): array
    {
        $suspendidas = in_array($evento['efecto'], ['SUSPENSION', 'PARCIAL'], true) && $afectados->isNotEmpty();
        $salida = ['estado' => $evento['recuperacion'], 'fecha' => null, 'cruces' => [], 'avisos' => [],
            'mensaje' => $suspendidas ? 'Hay clases que conviene recuperar. La fecha y la organización requieren revisión institucional.'
                : 'El cambio de actividad o modalidad no exige automáticamente recuperar una jornada completa.'];
        if ($evento['recuperacion'] === 'NO') $salida['mensaje'] = 'Has propuesto no reprogramar. Revisa que la continuidad de las clases y los días efectivos queden respaldados.';
        if ($evento['recuperacion'] === 'POR_CONFIRMAR') $salida['mensaje'] = 'Se propone recuperar, con el día pendiente de coordinación con estudiantes y docentes. No hay una fecha confirmada.';
        if ($evento['recuperacion'] !== 'PROPUESTA') return $salida;
        $v = Validator::make($evento, ['fecha_recuperacion' => ['required', 'date_format:Y-m-d', 'after_or_equal:fin', 'before_or_equal:'.substr($gestion->ffi_gea, 0, 10)],
            'hora_recuperacion_inicio' => ['required', 'date_format:H:i'], 'hora_recuperacion_fin' => ['required', 'date_format:H:i', 'after:hora_recuperacion_inicio']]);
        if ($v->fails()) { $salida['avisos'][] = 'La recuperación necesita una fecha posterior al caso, dentro de la gestión activa, y horas válidas.'; return $salida; }
        $salida['fecha'] = $evento['fecha_recuperacion'];
        $salida['mensaje'] = 'Fecha propuesta para recuperar clases. Este análisis no modifica el horario ni las entregas.';
        foreach ($panel->eventos($gestion->cod_gea) as $e) {
            if (! in_array($e['estado'], ['CONFIRMADO', 'FINALIZADO'], true) || $e['inicio'] > $salida['fecha'] || $e['fin'] < $salida['fecha']) continue;
            if ($e['cod_cur'] && ! $afectados->contains('cod_cur', $e['cod_cur'])) continue;
            if ($e['cod_par'] && ! $afectados->contains('cod_par', $e['cod_par'])) continue;
            if ($e['cod_tur'] && ! $afectados->contains('cod_tur', $e['cod_tur'])) continue;
            if (in_array($e['efecto'], ['SIN_CLASES', 'SUSPENSION_PARCIAL', 'SALIDA_ANTICIPADA'], true)) $salida['avisos'][] = 'La fecha propuesta coincide con '.$e['nombre'].'. Revisa su alcance y horas antes de elegirla.';
        }
        $fecha = CarbonImmutable::parse($salida['fecha']);
        $horarios = $this->horarios($gestion->cod_gea, $salida['fecha'], $salida['fecha']);
        $grupos = $afectados->pluck('cod_gac')->unique(); $docentes = $afectados->pluck('cod_doc')->filter()->unique();
        $franja = ['jornada' => 'PARCIAL', 'hora_inicio' => $evento['hora_recuperacion_inicio'], 'hora_fin' => $evento['hora_recuperacion_fin']];
        $cruces = $horarios->filter(fn ($h) => ($grupos->contains($h->cod_gac) || $docentes->contains($h->cod_doc)) && $this->vigente($h, $fecha, $dias) && $this->minutosAfectados($h, $franja));
        $salida['cruces_total'] = $cruces->count();
        $salida['cruces'] = $cruces->take(10)->map(fn ($h) => ['grupo' => mb_strtoupper($h->curso.' '.$h->paralelo.' · '.$h->turno), 'materia' => $h->materia,
            'docente' => mb_strtoupper($h->docente), 'horas' => substr($h->hor_ini_hbl, 0, 5).'–'.substr($h->hor_fin_hbl, 0, 5)])->values()->all();
        $salida['avisos'][] = $cruces->isNotEmpty() ? 'La recuperación coincide con '.$cruces->count().' clases de los grupos o docentes involucrados. Revisa cobertura y horas.'
            : 'No se detectan clases coincidentes en los horarios registrados. Confirma disponibilidad, espacios y autorización.';
        return $salida;
    }
}
