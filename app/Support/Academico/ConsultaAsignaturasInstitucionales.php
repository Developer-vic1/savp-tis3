<?php

namespace App\Support\Academico;

use App\Models\Oficial\Academico\Asignatura;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

/** Los planes enlazan la materia con su grupo, docente y gestión. */
class ConsultaAsignaturasInstitucionales
{
    public static function campos(): array
    {
        return [
            'comunidad' => ['nombre'=>'Comunidad y Sociedad', 'icono'=>'ph-users-three', 'tono'=>'info'],
            'ciencia' => ['nombre'=>'Ciencia, Tecnología y Producción', 'icono'=>'ph-atom', 'tono'=>'warning'],
            'vida' => ['nombre'=>'Vida, Tierra y Territorio', 'icono'=>'ph-leaf', 'tono'=>'primary'],
            'cosmos' => ['nombre'=>'Cosmos y Pensamiento', 'icono'=>'ph-sparkle', 'tono'=>'muted'],
            'revision' => ['nombre'=>'Campo por revisar', 'icono'=>'ph-question', 'tono'=>'muted'],
        ];
    }

    public static function campo(string $area): array
    {
        foreach (self::campos() as $clave=>$campo) {
            if (AsignaturaInteligente::normalizar($area) === AsignaturaInteligente::normalizar($campo['nombre'])) return ['clave'=>$clave]+$campo;
        }
        return ['clave'=>'revision']+self::campos()['revision'];
    }

    public function catalogo(string $gestion): Collection
    {
        $planes = DB::table('plan_asignatura as p')->join('grupo_academico as g','g.cod_gac','=','p.cod_gac')
            ->select('p.cod_asi')->selectRaw('COUNT(*) historicos, COUNT(*) FILTER (WHERE g.cod_gea = ? AND p.est_pas = ? AND g.est_gac = ?) actuales, COUNT(DISTINCT p.cod_doc) FILTER (WHERE g.cod_gea = ? AND p.est_pas = ? AND g.est_gac = ?) docentes, COUNT(DISTINCT g.cod_cur) FILTER (WHERE g.cod_gea = ? AND p.est_pas = ? AND g.est_gac = ?) cursos',[$gestion,'ACTIVO','ACTIVO',$gestion,'ACTIVO','ACTIVO',$gestion,'ACTIVO','ACTIVO'])->groupBy('p.cod_asi')->get()->keyBy('cod_asi');
        $notas = DB::table('calificacion as n')->join('plan_asignatura as p','p.cod_pas','=','n.cod_pas')
            ->where('n.est_cal','<>','ANULADA')->selectRaw('p.cod_asi, COUNT(*) cantidad')->groupBy('p.cod_asi')->get()->keyBy('cod_asi');
        $horarios = DB::table('horario_detalle as d')->join('plan_asignatura as p','p.cod_pas','=','d.cod_pas')
            ->join('horario as h','h.cod_hor','=','d.cod_hor')->join('grupo_academico as g','g.cod_gac','=','h.cod_gac')
            ->select('p.cod_asi')->selectRaw('COUNT(*) historicos, COUNT(*) FILTER (WHERE g.cod_gea = ? AND h.est_hor = ? AND d.est_hde = ?) actuales',[$gestion,'ACTIVO','ACTIVO'])->groupBy('p.cod_asi')->get()->keyBy('cod_asi');

        return Asignatura::orderBy('nom_asi')->get()->map(function ($materia) use($planes,$notas,$horarios) {
            $id=$materia->cod_asi;
            $analisis=AsignaturaInteligente::interpretar($materia->nom_asi);
            $p=(int)($planes->get($id)->historicos??0); $n=(int)($notas->get($id)->cantidad??0); $h=(int)($horarios->get($id)->historicos??0);
            $materia->analisis_inteligente=$analisis;
            $materia->campo_educativo=self::campo($analisis['area']??'');
            $materia->uso_academico=['planes'=>$p,'calificaciones'=>$n,'horarios'=>$h,'total'=>$p+$n+$h,'tiene_uso'=>($p+$n+$h)>0,'texto'=>"{$p} planes · {$n} notas · {$h} bloques históricos"];
            $materia->planes_actuales=(int)($planes->get($id)->actuales??0);
            $materia->docentes_actuales=(int)($planes->get($id)->docentes??0);
            $materia->cursos_actuales=(int)($planes->get($id)->cursos??0);
            $materia->bloques_actuales=(int)($horarios->get($id)->actuales??0);
            return $materia;
        });
    }

    public function docentes(string $materia,string $gestion): array
    {
        return DB::table('plan_asignatura as p')->join('grupo_academico as g','g.cod_gac','=','p.cod_gac')
            ->join('curso as c','c.cod_cur','=','g.cod_cur')->join('paralelo as pa','pa.cod_par','=','g.cod_par')->join('turno as t','t.cod_tur','=','g.cod_tur')
            ->join('docente as d','d.cod_doc','=','p.cod_doc')->join('personal_institucional as pin','pin.cod_pin','=','d.cod_pin')->join('persona as per','per.cod_per','=','pin.cod_per')
            ->where('p.cod_asi',$materia)->where('g.cod_gea',$gestion)->where('p.est_pas','ACTIVO')->where('g.est_gac','ACTIVO')
            ->select('d.cod_doc','c.nom_cur','pa.nom_par','t.nom_tur','p.hor_pas')->selectRaw("CONCAT_WS(' ',per.nom_per,per.ape_pat_per,per.ape_mat_per) nombre")
            ->orderBy('per.nom_per')->orderBy('c.ord_cur')->orderBy('pa.nom_par')->get()->groupBy('cod_doc')
            ->map(fn($filas)=>['nombre'=>mb_strtoupper($filas->first()->nombre),'grupos'=>$filas->map(fn($f)=>['curso'=>$f->nom_cur,'paralelo'=>$f->nom_par,'turno'=>$f->nom_tur,'horas'=>(float)$f->hor_pas])->all()])->values()->all();
    }

    public function historial(string $codigo): array
    {
        return DB::table('bitacora')->where('tab_bit','asignatura')->where('reg_bit',$codigo)->where('res_bit','EXITOSO')
            ->whereIn('acc_bit',['EDITAR_ASIGNATURA','DESACTIVAR_ASIGNATURA','REACTIVAR_ASIGNATURA'])
            ->orderByDesc('fec_bit')->limit(12)->get(['acc_bit','des_bit','fec_bit','val_nue_bit'])->map(function($r){
                $valores=is_string($r->val_nue_bit)?json_decode($r->val_nue_bit,true):$r->val_nue_bit;
                return ['accion'=>$r->acc_bit,'fecha'=>$r->fec_bit,'motivo'=>$valores['motivo']??$r->des_bit,'tiene_motivo'=>isset($valores['motivo'])];
            })->all();
    }
}
