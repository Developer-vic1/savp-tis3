<?php

namespace App\Support\Academico;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

/** Lectura del contrato oficial: los planes y horarios pertenecen al grupo académico. */
class ConsultaCursosInstitucionales
{
    public function resumen(string $gestion): Collection
    {
        $planes = DB::table('plan_asignatura as p')->join('grupo_academico as g', 'g.cod_gac', '=', 'p.cod_gac')
            ->where('g.cod_gea', $gestion)->where('p.est_pas', 'ACTIVO')->selectRaw('g.cod_cur, COUNT(*) planes, COUNT(DISTINCT p.cod_asi) materias')->groupBy('g.cod_cur')->get()->keyBy('cod_cur');
        $tecnicos = DB::table('plan_especialidad as p')->join('grupo_academico as g', 'g.cod_gac', '=', 'p.cod_gac')
            ->where('g.cod_gea', $gestion)->where('p.est_pes', 'ACTIVO')->selectRaw('g.cod_cur, COUNT(*) planes, COUNT(DISTINCT p.cod_esp) especialidades')->groupBy('g.cod_cur')->get()->keyBy('cod_cur');
        $horarios = DB::table('horario as h')->join('grupo_academico as g', 'g.cod_gac', '=', 'h.cod_gac')
            ->join('horario_detalle as d', 'd.cod_hor', '=', 'h.cod_hor')->where('g.cod_gea', $gestion)
            ->where('h.est_hor', 'ACTIVO')->where('d.est_hde', 'ACTIVO')
            ->selectRaw('g.cod_cur, COUNT(DISTINCT h.cod_hor) horarios, COUNT(DISTINCT g.cod_gac) grupos_horario, COUNT(*) bloques')->groupBy('g.cod_cur')->get()->keyBy('cod_cur');
        $grupos = DB::table('grupo_academico as g')->join('paralelo as p', 'p.cod_par', '=', 'g.cod_par')->join('turno as t', 't.cod_tur', '=', 'g.cod_tur')
            ->where('g.cod_gea', $gestion)->where('g.est_gac', 'ACTIVO')->select('g.cod_gac', 'g.cod_cur', 'g.cod_par', 'g.cod_tur', 'p.nom_par', 't.nom_tur')->get()->groupBy('cod_cur');
        // Una inscripción anual sigue siendo un estudiante, aunque tenga trayecto técnico simultáneo.
        $inscritos = DB::table('inscripcion_estudiante')->where('cod_gea', $gestion)->whereIn('est_ins', ['ACTIVA','ARCHIVADA'])
            ->selectRaw('cod_cur, COUNT(DISTINCT cod_est) estudiantes')->groupBy('cod_cur')->get()->keyBy('cod_cur');

        return DB::table('curso')->orderBy('ord_cur')->get()->map(function ($curso) use ($planes, $tecnicos, $horarios, $grupos, $inscritos) {
            $id = $curso->cod_cur;
            $contextos = $grupos->get($id, collect());
            return [
                'cod_cur' => $id, 'nombre' => $curso->nom_cur, 'orden' => (int) $curso->ord_cur, 'nivel' => $curso->niv_cur,
                'descripcion' => $curso->des_cur ?? '',
                'estado' => $curso->est_cur === 'ACTIVO' ? 'ACTIVO' : 'INACTIVO',
                'etapa' => $curso->ord_cur>6?'Grado con autorización extraordinaria':($curso->ord_cur <= 3 ? 'Formación general' : 'Formación técnica especializada'),
                'estudiantes' => (int) ($inscritos->get($id)->estudiantes ?? 0),
                'planes' => (int) ($planes->get($id)->planes ?? 0), 'materias' => (int) ($planes->get($id)->materias ?? 0),
                'planes_tecnicos' => (int) ($tecnicos->get($id)->planes ?? 0), 'especialidades' => (int) ($tecnicos->get($id)->especialidades ?? 0),
                'horarios' => (int) ($horarios->get($id)->horarios ?? 0), 'bloques' => (int) ($horarios->get($id)->bloques ?? 0),
                'grupos_horario' => (int) ($horarios->get($id)->grupos_horario ?? 0), 'grupos' => $contextos->count(),
                'paralelos' => $contextos->unique('cod_par')->sortBy('nom_par')->map(fn ($g) => ['valor' => $g->cod_par, 'etiqueta' => $g->nom_par])->values()->all(),
                'turnos' => $contextos->pluck('nom_tur')->unique()->values()->all(),
                'contextos' => $contextos->map(fn ($g) => (array) $g)->values()->all(),
            ];
        });
    }

    public function trayectoria(): array
    {
        return DB::table('inscripcion_estudiante as i')->join('gestion_academica as a', 'a.cod_gea', '=', 'i.cod_gea')
            ->join('curso as c', 'c.cod_cur', '=', 'i.cod_cur')->whereNotIn('i.est_ins', ['ANULADO', 'ANULADA'])
            ->selectRaw('a.ani_gea anio, c.cod_cur curso, c.nom_cur nombre, COUNT(DISTINCT i.cod_est) estudiantes')
            ->groupBy('a.ani_gea', 'c.cod_cur', 'c.nom_cur')->orderBy('a.ani_gea')->orderBy('c.cod_cur')->get()->map(fn ($r) => (array) $r)->all();
    }

    public function materias(string $curso, string $gestion): array
    {
        $base = fn ($tabla) => DB::table($tabla.' as p')->join('grupo_academico as g', 'g.cod_gac', '=', 'p.cod_gac')
            ->join('paralelo as pa', 'pa.cod_par', '=', 'g.cod_par')->join('turno as t', 't.cod_tur', '=', 'g.cod_tur')
            ->join('docente as d', 'd.cod_doc', '=', 'p.cod_doc')->join('personal_institucional as pin', 'pin.cod_pin', '=', 'd.cod_pin')
            ->join('persona as per', 'per.cod_per', '=', 'pin.cod_per')->where('g.cod_cur', $curso)->where('g.cod_gea', $gestion);
        $seleccion = "g.cod_gac, pa.nom_par paralelo, t.nom_tur turno, CONCAT_WS(' ',per.nom_per,per.ape_pat_per,per.ape_mat_per) docente";
        $materias = $base('plan_asignatura')->join('asignatura as a', 'a.cod_asi', '=', 'p.cod_asi')->where('p.est_pas', 'ACTIVO')
            ->selectRaw("a.nom_asi nombre, p.hor_pas horas, {$seleccion}")->orderBy('a.nom_asi')->get()->map(fn ($r) => ['tipo' => 'Materia curricular'] + (array) $r);
        $tecnicas = $base('plan_especialidad')->join('especialidad_tecnica as e', 'e.cod_esp', '=', 'p.cod_esp')->where('p.est_pes', 'ACTIVO')
            ->selectRaw("e.nom_esp nombre, p.hor_pes horas, {$seleccion}")->orderBy('e.nom_esp')->get()->map(fn ($r) => ['tipo' => 'Especialidad técnica'] + (array) $r);
        return $materias->concat($tecnicas)->values()->all();
    }

    public function periodosHorario(string $curso, string $gestion, string $paralelo): array
    {
        return DB::table('horario as h')->join('grupo_academico as g','g.cod_gac','=','h.cod_gac')->join('plantilla_horaria as p','p.cod_pho','=','h.cod_pho')->join('turno as t','t.cod_tur','=','p.cod_tur')
            ->where('g.cod_cur',$curso)->where('g.cod_gea',$gestion)->where('g.cod_par',$paralelo)->where('h.est_hor','ACTIVO')
            ->orderByDesc('h.fii_hor')->select('h.cod_hor','h.fii_hor','h.ffi_hor','p.nom_pho','t.nom_tur')->get()->map(fn($h)=>[
                'valor'=>$h->cod_hor,'etiqueta'=>$h->nom_pho.' · '.$h->nom_tur.' · '.\Carbon\Carbon::parse($h->fii_hor)->format('d/m').'–'.($h->ffi_hor?\Carbon\Carbon::parse($h->ffi_hor)->format('d/m'):'Sin cierre'),
                'aplicado'=>$h->fii_hor<=now()->toDateString() && (!$h->ffi_hor || $h->ffi_hor>=now()->toDateString()),
            ])->all();
    }

    public function horario(string $curso, string $gestion, string $paralelo, ?string $cabecera = null): array
    {
        $filas = DB::table('horario_detalle as hd')->join('horario as h', 'h.cod_hor', '=', 'hd.cod_hor')
            ->join('grupo_academico as g', 'g.cod_gac', '=', 'h.cod_gac')->join('curso as c', 'c.cod_cur', '=', 'g.cod_cur')
            ->join('paralelo as pa', 'pa.cod_par', '=', 'g.cod_par')->join('horario_bloque as b', 'b.cod_hbl', '=', 'hd.cod_hbl')
            ->join('plantilla_horaria as ph', 'ph.cod_pho', '=', 'h.cod_pho')->join('turno as t', 't.cod_tur', '=', 'ph.cod_tur')
            ->leftJoin('plan_asignatura as pas', 'pas.cod_pas', '=', 'hd.cod_pas')->leftJoin('asignatura as a', 'a.cod_asi', '=', 'pas.cod_asi')
            ->leftJoin('plan_especialidad as pes', 'pes.cod_pes', '=', 'hd.cod_pes')->leftJoin('especialidad_tecnica as e', 'e.cod_esp', '=', 'pes.cod_esp')
            ->leftJoin('docente as d', fn ($j) => $j->on('d.cod_doc', '=', DB::raw('COALESCE(pas.cod_doc,pes.cod_doc)')))
            ->leftJoin('personal_institucional as pin', 'pin.cod_pin', '=', 'd.cod_pin')->leftJoin('persona as per', 'per.cod_per', '=', 'pin.cod_per')
            ->where('g.cod_cur', $curso)->where('g.cod_gea', $gestion)->where('g.cod_par', $paralelo)
            ->where('h.est_hor', 'ACTIVO')->where('hd.est_hde', 'ACTIVO')->whereColumn('b.cod_pho', 'h.cod_pho')
            ->when($cabecera,fn($q)=>$q->where('h.cod_hor',$cabecera))
            ->select('hd.*', 'b.hor_ini_hbl', 'b.hor_fin_hbl', 'b.nom_hbl', 'ph.cod_pho', 'ph.nom_pho', 'ph.fec_ini_pho', 'ph.fec_fin_pho', 'h.fii_hor', 'h.ffi_hor', 't.nom_tur', 'c.nom_cur', 'pa.nom_par')
            ->selectRaw("COALESCE(a.nom_asi,e.nom_esp) nombre, CONCAT_WS(' ',per.nom_per,per.ape_pat_per,per.ape_mat_per) docente")
            ->orderByDesc('ph.act_pho')->orderBy('ph.fec_ini_pho')->orderBy('b.num_hbl')->get();
        $recreos = DB::table('horario_bloque')->whereIn('cod_pho', $filas->pluck('cod_pho')->unique())->where('tip_hbl', 'RECREO')->where('est_hbl', 'ACTIVO')->get()->groupBy('cod_pho');
        $dias = ['LUNES'=>'Lunes','MARTES'=>'Martes','MIERCOLES'=>'Miércoles','MIÉRCOLES'=>'Miércoles','JUEVES'=>'Jueves','VIERNES'=>'Viernes','SABADO'=>'Sábado','DOMINGO'=>'Domingo'];
        $hoy = now()->toDateString();
        return $filas->map(fn ($r) => [
            'id' => $r->cod_hde, 'dia' => $dias[$r->dia_hde] ?? $r->dia_hde,
            'inicio' => substr($r->hor_ini_hbl,0,5), 'fin' => substr($r->hor_fin_hbl,0,5), 'bloque' => $r->nom_hbl,
            'turno' => $r->nom_tur, 'plantilla_id' => $r->cod_hor, 'plantilla' => $r->nom_pho,
            'aplicada' => $r->fii_hor <= $hoy && (!$r->ffi_hor || $r->ffi_hor >= $hoy),
            'periodo' => \Carbon\Carbon::parse($r->fii_hor)->format('d/m/Y').' — '.($r->ffi_hor ? \Carbon\Carbon::parse($r->ffi_hor)->format('d/m/Y') : 'Sin cierre'),
            'asignacion_id' => $r->cod_pas ?: $r->cod_pes,
            'nombre' => $r->nombre ?: 'Asignación por revisar', 'docente' => mb_strtoupper($r->docente),
            'curso' => $r->nom_cur, 'paralelo' => $r->nom_par, 'tipo' => $r->cod_pas ? 'Materia curricular' : 'Especialidad técnica',
            'aula' => $r->aul_hde ?: $r->nom_cur.' · '.$r->nom_par, 'coincide' => false,
            'recreos' => $recreos->get($r->cod_pho, collect())->map(fn ($b) => ['inicio'=>substr($b->hor_ini_hbl,0,5),'fin'=>substr($b->hor_fin_hbl,0,5)])->all(),
        ])->all();
    }
}
