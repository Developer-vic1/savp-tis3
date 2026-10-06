<?php

namespace App\Support\Academico;

use Illuminate\Support\Facades\DB;

/** Una instantánea por petición; nunca mezcla gestiones ni trayectos técnicos. */
class ConsultaParalelosInstitucionales
{
    public function consultar(): array
    {
        $gestion = DB::table('gestion_academica')->whereIn('est_gea', ['ACTIVA', 'ACTIVO'])->orderByDesc('ani_gea')->first();
        $cod = $gestion?->cod_gea;
        $inscritos = DB::table('inscripcion_estudiante')->where('cod_gea', $cod)->where('est_ins', 'ACTIVA')
            ->selectRaw('cod_par, COUNT(DISTINCT cod_est) estudiantes')->groupBy('cod_par')->get();
        $grupos = DB::table('grupo_academico as g')->join('curso as c', 'c.cod_cur', '=', 'g.cod_cur')->join('turno as t', 't.cod_tur', '=', 'g.cod_tur')
            ->where('g.cod_gea', $cod)->where('g.est_gac', 'ACTIVO')->orderBy('c.ord_cur')->orderBy('t.nom_tur')
            ->select('g.*', 'c.nom_cur', 'c.ord_cur', 't.nom_tur')->get();
        $miembros = DB::table('inscripcion_vigencia as v')->join('inscripcion_estudiante as i', 'i.cod_ins', '=', 'v.cod_ins')
            ->where('i.cod_gea', $cod)->where('i.est_ins', 'ACTIVA')->where('v.est_ivg', 'ACTIVO')
            ->where('v.fii_ivg', '<=', now()->toDateString())->where(fn ($q) => $q->whereNull('v.ffi_ivg')->orWhere('v.ffi_ivg', '>=', now()->toDateString()))
            ->selectRaw('v.cod_gac, COUNT(DISTINCT i.cod_est) estudiantes')->groupBy('v.cod_gac')->get()->keyBy('cod_gac');
        $planes = [];
        foreach (['plan_asignatura' => ['est_pas', 'asignatura'], 'plan_especialidad' => ['est_pes', 'especialidad']] as $tabla => [$estado, $tipo]) {
            $planes[$tipo] = DB::table($tabla.' as p')->join('grupo_academico as g', 'g.cod_gac', '=', 'p.cod_gac')
                ->where('g.cod_gea', $cod)->where('g.est_gac', 'ACTIVO')->where('p.'.$estado, 'ACTIVO')
                ->selectRaw('g.cod_par, g.cod_gac, COUNT(*) total')->groupBy('g.cod_par', 'g.cod_gac')->get();
        }
        $horarios = DB::table('horario as h')->join('grupo_academico as g', 'g.cod_gac', '=', 'h.cod_gac')
            ->where('g.cod_gea', $cod)->where('g.est_gac', 'ACTIVO')->where('h.est_hor', 'ACTIVO')
            ->where('h.fii_hor', '<=', now()->toDateString())->where(fn ($q) => $q->whereNull('h.ffi_hor')->orWhere('h.ffi_hor', '>=', now()->toDateString()))
            ->selectRaw('g.cod_par, COUNT(*) total')->groupBy('g.cod_par')->get()->keyBy('cod_par');
        $usos = [];
        $mapa = [];
        foreach (DB::table('paralelo')->orderBy('nom_par')->get() as $paralelo) {
            $filas = $grupos->where('cod_par', $paralelo->cod_par)->map(function ($g) use ($miembros, $planes) {
                $cantidad = (int) ($miembros->get($g->cod_gac)->estudiantes ?? 0);
                return ['curso' => $g->nom_cur, 'cod_cur' => $g->cod_cur, 'turno' => $g->nom_tur, 'cod_tur' => $g->cod_tur, 'estudiantes' => $cantidad, 'capacidad' => (int) $g->cap_gac, 'grupo' => $g->cod_gac,
                    'planes' => (int) $planes['asignatura']->where('cod_gac', $g->cod_gac)->sum('total'),
                    'especialidades' => (int) $planes['especialidad']->where('cod_gac', $g->cod_gac)->sum('total')];
            })->values()->all();
            $id = $paralelo->cod_par;
            $estudiantes = (int) $inscritos->where('cod_par', $id)->sum('estudiantes');
            $pa = (int) $planes['asignatura']->where('cod_par', $id)->sum('total');
            $pe = (int) $planes['especialidad']->where('cod_par', $id)->sum('total');
            $h = (int) ($horarios->get($id)->total ?? 0);
            $usos[$id] = ['estudiantes' => $estudiantes, 'inscripciones' => $estudiantes, 'planes_asignatura' => $pa, 'planes_especialidad' => $pe,
                'horarios' => $h, 'cursos' => $grupos->where('cod_par', $id)->pluck('cod_cur')->unique()->count(), 'grupos' => $filas,
                'total' => $estudiantes + $pa + $pe + $h, 'tiene_estudiantes' => $estudiantes > 0, 'tiene_planificacion' => $pa + $pe + $h > 0,
                'tiene_uso' => $estudiantes + $pa + $pe + $h > 0,
                'texto' => "$estudiantes estudiantes · ".($pa + $pe).' planes · '.$h.' horarios vigentes'];
            foreach ($filas as $fila) $mapa[] = $fila + ['paralelo' => $paralelo->nom_par, 'codigo' => $id];
        }
        return ['gestion' => $gestion, 'usos' => $usos, 'mapa' => $mapa];
    }
}
