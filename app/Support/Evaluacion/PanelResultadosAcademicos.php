<?php
namespace App\Support\Evaluacion;

use Illuminate\Database\Query\Builder;
use Illuminate\Support\Facades\DB;

/** Una fila por inscripción anual; no multiplicar estudiantes por cantidad de notas. */
class PanelResultadosAcademicos
{
    /** Mayor promedio de cada género registrado; no deduce una carrera de la nota. */
    public function destacados(Builder $consulta, bool $orientacion): array
    {
        $destacados = [];
        foreach (['M' => 'Hombre', 'F' => 'Mujer'] as $genero => $etiqueta) {
            $registro = (clone $consulta)->where('p.gen_per', $genero)->whereNotNull('n.promedio')
                ->orderByDesc('n.promedio')->orderBy('i.cod_ins')->first();
            if (! $registro) continue;
            $carreras = [];
            $fecha = null;
            if ($orientacion) {
                $actividad = \App\Models\Oficial\AporteAcademicoVocacional\OrientacionActividad::where('cod_est', $registro->cod_est)
                    ->where('cod_gea', $registro->cod_gea)->orderByDesc('id')->first();
                $estudio = app(RendimientoEstudiantil::class)->estudio($actividad);
                if ($estudio['estado'] === 'contrato_valido') {
                    $carreras = $estudio['datos']['career_evidence_profiles'];
                    $fecha = $actividad->analysis_completed_at;
                }
            }
            $periodos = DB::table('calificacion as cal')->join('periodo_evaluacion as pe', 'pe.cod_pev', '=', 'cal.cod_pev')
                ->where('cal.cod_ins', $registro->cod_ins)->whereIn('cal.est_cal', ['VIGENTE', 'RECTIFICADA'])
                ->select('pe.nom_pev', 'pe.ord_pev')->selectRaw('AVG(cal.not_cal) AS promedio')
                ->groupBy('pe.cod_pev', 'pe.nom_pev', 'pe.ord_pev')->orderBy('pe.ord_pev')->get();
            $trayectoria = ['labels' => $periodos->pluck('nom_pev')->all(), 'tipo' => 'line', 'unidad' => 'puntos / 100', 'maximo' => 100,
                'series' => [['label' => 'Promedio observado', 'token' => 'primary', 'data' => $periodos->map(fn ($p) => round((float)$p->promedio, 2))->all()]]];
            $destacados[] = compact('etiqueta', 'registro', 'carreras', 'fecha', 'trayectoria');
        }
        return $destacados;
    }
    /** Consulta snapshots existentes: sin generar estudios ni enviar datos personales. */
    public function estudios(array $f): array
    {
        $evidencias = [];
        foreach (['academic'=>'Calificaciones', 'historical'=>'Trayectoria', 'attendance'=>'Asistencia', 'learning_activity'=>'Actividad de aprendizaje', 'technical'=>'Especialidad técnica', 'declared_interest'=>'Intereses declarados'] as $clave=>$nombre) {
            $evidencias[$clave] = ['nombre'=>$nombre, 'AVAILABLE'=>0, 'PARTIAL'=>0, 'INSUFFICIENT'=>0, 'UNAVAILABLE'=>0];
        }
        $resultado = ['estudios'=>0, 'no_interpretables'=>0, 'ultima_lectura'=>null, 'evidencias'=>$evidencias, 'carreras'=>[]];
        if (!($f['orientacion'] ?? false)) return $resultado;

        $cohorte = $this->consulta($f)->select('i.cod_est', 'i.cod_gea')->distinct();
        $estudios = DB::table('orientacion_actividades as oa')
            ->joinSub($cohorte, 'cohorte', fn ($j) => $j->on('cohorte.cod_est','=','oa.cod_est')->on('cohorte.cod_gea','=','oa.cod_gea'))
            ->whereRaw('oa.id = (SELECT MAX(actual.id) FROM orientacion_actividades AS actual WHERE actual.cod_est = oa.cod_est AND actual.cod_gea = oa.cod_gea)')
            ->whereNotNull('oa.analysis_completed_at')->whereNotNull('oa.analysis_snapshot')
            ->select('oa.analysis_snapshot', 'oa.analysis_completed_at')->cursor();
        $carreras = [];
        foreach ($estudios as $estudio) {
            $snapshot = json_decode($estudio->analysis_snapshot, true);
            if (!is_array($snapshot) || !is_array($snapshot['student_snapshot'] ?? null) || !is_array($snapshot['career_evidence_profiles'] ?? null)) {
                $resultado['no_interpretables']++;
                continue;
            }
            $resultado['estudios']++;
            $resultado['ultima_lectura'] = max($resultado['ultima_lectura'] ?? '', $estudio->analysis_completed_at);
            foreach ($resultado['evidencias'] as $clave=>$datos) {
                $estado = data_get($snapshot, 'student_snapshot.'.$clave.'_evidence.status');
                if (!in_array($estado, ['AVAILABLE','PARTIAL','INSUFFICIENT','UNAVAILABLE'], true)) $estado = 'UNAVAILABLE';
                $resultado['evidencias'][$clave][$estado]++;
            }
            // Una carrera se cuenta una sola vez por estudio, aunque el contrato repita perfiles.
            $vistas = [];
            foreach ($snapshot['career_evidence_profiles'] as $perfil) {
                $nombre = data_get($perfil, 'career_name');
                $universidad = data_get($perfil, 'university');
                if (!is_string($nombre) || trim($nombre)==='') continue;
                $universidad = is_string($universidad) && trim($universidad)!=='' ? $universidad : 'Universidad sin referencia';
                $clave = $universidad.'|'.$nombre;
                if (isset($vistas[$clave])) continue;
                $vistas[$clave] = true;
                $carreras[$clave] ??= ['nombre'=>$nombre, 'universidad'=>$universidad, 'estudios'=>0, 'con_notas'=>0];
                $carreras[$clave]['estudios']++;
                if ((int)data_get($perfil, 'evidence_quality.academic_record_count', 0)>0) $carreras[$clave]['con_notas']++;
            }
        }
        $resultado['carreras'] = collect($carreras)->sortByDesc('estudios')->take(8)->values()->all();
        return $resultado;
    }
    public function consulta(array $f): Builder
    {
        $notas = DB::table('calificacion')->whereIn('est_cal', ['VIGENTE', 'RECTIFICADA'])
            ->when($f['periodo'] ?? '', fn ($q, $v) => $q->where('cod_pev', $v))
            ->selectRaw("cod_ins, COUNT(*) AS notas, AVG(not_cal) AS promedio, SUM(CASE WHEN not_cal <= 50 THEN 1 ELSE 0 END) AS bajas, SUM(CASE WHEN UPPER(COALESCE(obs_cal,'')) LIKE '%SINTETIC%' THEN 1 ELSE 0 END) AS sinteticas")->groupBy('cod_ins');
        $ultimas = DB::table('orientacion_actividades')->selectRaw('MAX(id) AS id')->groupBy('cod_est', 'cod_gea');
        $orientacion = DB::table('orientacion_actividades as oa')->joinSub($ultimas, 'u', 'u.id', '=', 'oa.id')
            ->when(!($f['orientacion'] ?? false), fn ($q) => $q->whereRaw('1 = 0'))
            ->select('oa.cod_est', 'oa.cod_gea', 'oa.avance')->selectRaw('CASE WHEN oa.finalizado_at IS NOT NULL THEN 1 ELSE 0 END AS riasec, CASE WHEN oa.analysis_completed_at IS NOT NULL AND oa.analysis_snapshot IS NOT NULL THEN 1 ELSE 0 END AS analisis');
        $resultados = DB::table('resultado_anual')->where('est_ran', 'VIGENTE')->selectRaw("cod_ins, MAX(CASE WHEN res_ran = 'RETENIDO' THEN 1 ELSE 0 END) AS retenido, MAX(CASE WHEN res_ran IN ('PROMOVIDO','EGRESADO') THEN 1 ELSE 0 END) AS promovido")->groupBy('cod_ins');
        return DB::table('inscripcion_estudiante as i')->join('estudiante as e', 'e.cod_est', '=', 'i.cod_est')->join('persona as p', 'p.cod_per', '=', 'e.cod_per')
            ->join('curso as c', 'c.cod_cur', '=', 'i.cod_cur')->join('gestion_academica as g', 'g.cod_gea', '=', 'i.cod_gea')->leftJoin('paralelo as pa', 'pa.cod_par', '=', 'i.cod_par')
            ->leftJoinSub($notas, 'n', 'n.cod_ins', '=', 'i.cod_ins')
            ->leftJoinSub($orientacion, 'o', fn ($j) => $j->on('o.cod_est', '=', 'i.cod_est')->on('o.cod_gea', '=', 'i.cod_gea'))
            ->leftJoinSub($resultados, 'r', 'r.cod_ins', '=', 'i.cod_ins')->where('i.est_ins', '!=', 'ANULADA')
            ->when($f['gestion'] ?? '', fn ($q, $v) => $q->where('i.cod_gea', $v))
            ->when($f['grado'] ?? '', fn ($q, $v) => $q->where('i.cod_cur', $v))
            ->when($f['search'] ?? '', fn ($q, $v) => $q->whereRaw("LOWER(CONCAT(p.nom_per, ' ', p.ape_pat_per, ' ', COALESCE(p.ape_mat_per, ''))) LIKE ?", ['%'.mb_strtolower($v).'%']))
            ->when(($f['seguimiento'] ?? '') === 'riesgo', fn ($q) => $q->where('n.bajas', '>', 0))
            ->when(($f['seguimiento'] ?? '') === 'sin_notas', fn ($q) => $q->whereNull('n.cod_ins'))
            ->when(($f['seguimiento'] ?? '') === 'sin_analisis', fn ($q) => $q->where(fn ($w) => $w->whereNull('o.analisis')->orWhere('o.analisis', 0)))
            ->when(($f['seguimiento'] ?? '') === 'retirados', fn ($q) => $q->where('i.est_ins', 'RETIRADA'))
            ->select('i.cod_ins', 'i.cod_est', 'i.cod_cur', 'i.cod_gea', 'i.est_ins', 'c.nom_cur', 'pa.nom_par', 'g.ani_gea', 'p.nom_per', 'p.ape_pat_per', 'p.ape_mat_per', 'n.promedio')
            ->selectRaw('COALESCE(n.notas,0) AS notas, COALESCE(n.bajas,0) AS bajas, COALESCE(n.sinteticas,0) AS sinteticas, COALESCE(o.riasec,0) AS riasec, COALESCE(o.analisis,0) AS analisis, COALESCE(o.avance,0) AS avance, COALESCE(r.retenido,0) AS retenido, COALESCE(r.promovido,0) AS promovido');
    }
    private function agregado(Builder $q): Builder { return DB::query()->fromSub($q, 'datos'); }
    private function columnas(): string
    {
        return "COUNT(*) AS total, SUM(CASE WHEN notas > 0 THEN 1 ELSE 0 END) AS evaluados, AVG(promedio) AS promedio, SUM(CASE WHEN bajas > 0 THEN 1 ELSE 0 END) AS riesgo, SUM(sinteticas) AS sinteticas, SUM(riasec) AS riasec, SUM(analisis) AS analisis, SUM(retenido) AS retenidos, SUM(promovido) AS promovidos, SUM(CASE WHEN est_ins = 'RETIRADA' THEN 1 ELSE 0 END) AS retirados";
    }
    public function resumen(Builder $q): object { return $this->agregado($q)->selectRaw($this->columnas())->first(); }
    public function grados(Builder $q) { return $this->agregado($q)->select('cod_cur', 'nom_cur')->selectRaw($this->columnas())->groupBy('cod_cur', 'nom_cur')->orderBy('nom_cur')->get(); }
    public function gestiones(array $f)
    {
        $f['gestion'] = '';
        return $this->agregado($this->consulta($f))->select('ani_gea')->selectRaw($this->columnas())->groupBy('ani_gea')->orderBy('ani_gea')->get();
    }
    public function periodos(array $f)
    {
        $f['periodo'] = '';
        $base = $this->consulta($f)->select('i.cod_ins');
        $observados = DB::table('calificacion as cal')->whereIn('cal.cod_ins', $base)->whereIn('cal.est_cal', ['VIGENTE', 'RECTIFICADA'])
            ->join('periodo_evaluacion as pe', 'pe.cod_pev', '=', 'cal.cod_pev')
            ->select('pe.cod_pev', 'pe.nom_pev', 'pe.ord_pev')->selectRaw('AVG(cal.not_cal) AS promedio, COUNT(DISTINCT cal.cod_ins) AS evaluados, COUNT(DISTINCT CASE WHEN cal.not_cal <= 50 THEN cal.cod_ins END) AS riesgo')
            ->groupBy('pe.cod_pev', 'pe.nom_pev', 'pe.ord_pev')->orderBy('pe.ord_pev')->get();
        return DB::table('periodo_evaluacion')->select('cod_pev', 'nom_pev', 'ord_pev')->orderBy('ord_pev')->get()->map(function ($periodo) use ($observados) {
            return $observados->firstWhere('cod_pev', $periodo->cod_pev) ?? (object) ['cod_pev'=>$periodo->cod_pev, 'nom_pev'=>$periodo->nom_pev, 'ord_pev'=>$periodo->ord_pev, 'promedio'=>null, 'evaluados'=>0, 'riesgo'=>0];
        });
    }
    public function perfiles(array $f)
    {
        if (!($f['orientacion'] ?? false)) return collect();
        $inscritos = $this->consulta($f)->select('i.cod_est', 'i.cod_gea');
        $publico = DB::getDriverName() === 'pgsql' ? "oa.riasec_score->>'holland_code'" : "json_extract(oa.riasec_score, '$.holland_code')";
        $codigo = "COALESCE(NULLIF(resultado.perfil_predominante,''), $publico)";
        return DB::table('orientacion_actividades as oa')
            ->joinSub($inscritos, 'inscritos', fn ($j) => $j->on('inscritos.cod_est','=','oa.cod_est')->on('inscritos.cod_gea','=','oa.cod_gea'))
            ->leftJoin('orientacion_resultados as resultado','resultado.orientacion_actividad_id','=','oa.id')
            ->whereRaw('oa.id = (SELECT MAX(actual.id) FROM orientacion_actividades AS actual WHERE actual.cod_est = oa.cod_est AND actual.cod_gea = oa.cod_gea)')
            ->whereNotNull('oa.finalizado_at')->whereRaw("$codigo IS NOT NULL")
            ->selectRaw("$codigo AS perfil_predominante, COUNT(DISTINCT oa.cod_est) AS estudiantes")
            ->groupByRaw($codigo)->orderByDesc('estudiantes')->get();
    }
}
