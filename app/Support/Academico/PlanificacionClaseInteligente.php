<?php

namespace App\Support\Academico;

use App\Models\Oficial\Academico\HorarioDetalle;
use App\Models\Oficial\Academico\PlanAsignatura;
use App\Models\Oficial\Academico\PlanEspecialidad;
use App\Services\BitacoraService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\ValidationException;

/** Revisión del bloque, su planificación y disponibilidad docente en el contrato oficial. */
class PlanificacionClaseInteligente
{
    public const DIAS=['LUNES','MARTES','MIERCOLES','JUEVES','VIERNES'];

    public function contexto(string $horario,string $bloque,string $dia): array
    {
        $h=DB::table('horario as h')->join('grupo_academico as g','g.cod_gac','=','h.cod_gac')
            ->join('gestion_academica as a','a.cod_gea','=','g.cod_gea')->join('curso as c','c.cod_cur','=','g.cod_cur')
            ->join('paralelo as p','p.cod_par','=','g.cod_par')->join('plantilla_horaria as ph','ph.cod_pho','=','h.cod_pho')->join('turno as t','t.cod_tur','=','g.cod_tur')
            ->where('h.cod_hor',$horario)->select('h.*','g.cod_gea','g.cod_cur','g.cod_par','g.est_gac','a.est_gea','a.ani_gea','a.fii_gea','a.ffi_gea','c.nom_cur','c.ord_cur','p.nom_par','t.nom_tur','ph.est_pho')->first();
        $b=$h?DB::table('horario_bloque')->where('cod_hbl',$bloque)->where('cod_pho',$h->cod_pho)->first():null;
        $errores=[];
        if(!$h||!$b)return ['valido'=>false,'bloqueos'=>['El bloque no pertenece al horario seleccionado.'],'horario'=>null,'bloque'=>null];
        if(!in_array($dia,self::DIAS,true))$errores[]='Selecciona un día de clase de lunes a viernes.';
        if($h->est_gea!=='ACTIVO'||$h->est_gac!=='ACTIVO'||$h->est_hor!=='ACTIVO'||!in_array($h->est_pho,[true,1,'1','t'],true))$errores[]='Solo se organizan clases de grupos y horarios vigentes en la gestión activa.';
        if(!in_array(CursoInteligente::normalizar($h->nom_tur),config('academico.turnos_habilitados',['manana']),true))$errores[]='Este turno está registrado, pero aún no se ha incorporado para organizar nuevas clases.';
        if($b->est_hbl!=='ACTIVO'||$b->tip_hbl!=='CLASE'||$b->hor_ini_hbl>=$b->hor_fin_hbl)$errores[]='Este bloque no corresponde a una clase habilitada; los recreos se conservan.';
        if(($h->ffi_hor&&$h->ffi_hor<now()->toDateString())||$h->fii_hor<$h->fii_gea||($h->ffi_gea&&($h->ffi_hor??$h->ffi_gea)>$h->ffi_gea))$errores[]='Este período es histórico o excede la gestión. Selecciona el horario vigente o uno futuro.';
        if(DB::table('horario_detalle')->where('cod_hor',$horario)->where('cod_hbl',$bloque)->where('dia_hde',$dia)->exists())$errores[]='El bloque ya contiene un registro. Su historia se conserva; no se reemplaza desde Agregar clase.';
        return ['valido'=>!$errores,'bloqueos'=>$errores,'horario'=>(array)$h,'bloque'=>(array)$b];
    }

    public function opciones(string $horario): array
    {
        $grupo=DB::table('horario')->where('cod_hor',$horario)->value('cod_gac');
        $planes=DB::table('plan_asignatura')->where('cod_gac',$grupo)->where('est_pas','ACTIVO')->get()->groupBy('cod_asi');
        $tecnicos=DB::table('plan_especialidad')->where('cod_gac',$grupo)->where('est_pes','ACTIVO')->get()->groupBy('cod_esp');
        $materias=DB::table('asignatura')->where('est_asi','ACTIVO')->orderBy('nom_asi')->get()->map(fn($m)=>[
            'valor'=>$m->cod_asi,'etiqueta'=>$m->nom_asi,'horas'=>(float)$m->hor_asi,'docentes'=>$planes->get($m->cod_asi,collect())->pluck('cod_doc')->unique()->all(),
            'planificado'=>$planes->has($m->cod_asi),'campo'=>ConsultaAsignaturasInstitucionales::campo(AsignaturaInteligente::interpretar($m->nom_asi)['area']??'')]);
        $especialidades=DB::table('especialidad_tecnica')->where('est_esp','ACTIVO')->orderBy('nom_esp')->get()->map(fn($m)=>['valor'=>$m->cod_esp,'etiqueta'=>$m->nom_esp,'docentes'=>$tecnicos->get($m->cod_esp,collect())->pluck('cod_doc')->unique()->all(),'planificado'=>$tecnicos->has($m->cod_esp)]);
        $docentes=DB::table('docente as d')->join('personal_institucional as pin','pin.cod_pin','=','d.cod_pin')->join('persona as p','p.cod_per','=','pin.cod_per')->where('d.est_doc','ACTIVO')->where('pin.est_pin','ACTIVO')
            ->select('d.cod_doc as valor','d.esp_doc as especialidad')->selectRaw("UPPER(CONCAT_WS(' ',p.nom_per,p.ape_pat_per,p.ape_mat_per)) etiqueta")->orderBy('p.nom_per')->get();
        return ['materias'=>$materias->all(),'especialidades'=>$especialidades->all(),'docentes'=>$docentes->map(fn($d)=>(array)$d)->all()];
    }

    /** Conserva todos los perfiles compatibles, aunque no figuren en el plan del grupo. */
    public function docentesDelArea(array $docentes, string $materia, bool $tecnica = false): array
    {
        $soporte = app(\App\Support\Comunidad\DocenteInteligente::class);
        return array_values(array_filter($docentes, fn ($docente) =>
            $soporte->correspondencia((string) ($docente['especialidad'] ?? ''), $materia, $tecnica)['coincide']
        ));
    }

    private function coincidencias(array $h,array $b,string $dia)
    {
        return DB::table('horario_detalle as d')->join('horario as h','h.cod_hor','=','d.cod_hor')->join('grupo_academico as g','g.cod_gac','=','h.cod_gac')->join('horario_bloque as b','b.cod_hbl','=','d.cod_hbl')
            ->leftJoin('plan_asignatura as p','p.cod_pas','=','d.cod_pas')->leftJoin('plan_especialidad as e','e.cod_pes','=','d.cod_pes')
            ->where('g.cod_gea',$h['cod_gea'])->where('d.est_hde','ACTIVO')->where('h.est_hor','ACTIVO')->where('g.est_gac','ACTIVO')->where('d.dia_hde',$dia)
            ->where('b.hor_ini_hbl','<',$b['hor_fin_hbl'])->where('b.hor_fin_hbl','>',$b['hor_ini_hbl'])
            ->where('h.fii_hor','<=',$h['ffi_hor']??$h['ffi_gea'])
            ->where(fn($q)=>$q->whereNull('h.ffi_hor')->orWhere('h.ffi_hor','>=',$h['fii_hor']))
            ->whereRaw('COALESCE(p.fii_pas,e.fii_pes) <= ?',[$h['ffi_hor']??$h['ffi_gea']])
            ->whereRaw('(COALESCE(p.ffi_pas,e.ffi_pes) IS NULL OR COALESCE(p.ffi_pas,e.ffi_pes) >= ?)',[$h['fii_hor']]);
    }

    public function analizar(string $horario,string $bloque,string $dia,array $form): array
    {
        $ctx=$this->contexto($horario,$bloque,$dia);
        $resultado=['valido'=>false,'bloqueos'=>$ctx['bloqueos'],'advertencias'=>[],'docente'=>'','materia'=>'','plan'=>null,'crear_plan'=>false,'carga'=>0,'reglas'=>['Bloque y grupo de la gestión activa','Horario y planificación vigentes','Docente disponible por hora y período','Carga semanal registrada','Historia académica conservada']];
        if(!$ctx['valido'])return $resultado;
        $h=$ctx['horario'];$b=$ctx['bloque'];$tipo=$form['tipo_plan']??'';
        if(!in_array($tipo,['MATERIA','ESPECIALIDAD'],true)){$resultado['bloqueos'][]='Selecciona materia o especialidad técnica.';return $resultado;}
        $tecnica=$tipo==='ESPECIALIDAD';$id=$form[$tecnica?'cod_esp':'cod_mat']??'';$doc=$form['cod_doc']??'';
        if($tecnica&&((int)$h['ord_cur']<4||(int)$h['ord_cur']>6))$resultado['bloqueos'][]='Este grado no tiene un tramo técnico ordinario definido. Revisa su autorización curricular antes de asignar una especialidad.';
        if(!$id||!$doc){$resultado['advertencias'][]='Elige la materia y su docente para revisar la disponibilidad.';return $resultado;}
        $materia=DB::table($tecnica?'especialidad_tecnica':'asignatura')->where($tecnica?'cod_esp':'cod_asi',$id)->where($tecnica?'est_esp':'est_asi','ACTIVO')->first();
        $docente=DB::table('docente as d')->join('personal_institucional as pin','pin.cod_pin','=','d.cod_pin')->join('persona as p','p.cod_per','=','pin.cod_per')->where('d.cod_doc',$doc)->where('d.est_doc','ACTIVO')->where('pin.est_pin','ACTIVO')->select('d.esp_doc')->selectRaw("UPPER(CONCAT_WS(' ',p.nom_per,p.ape_pat_per,p.ape_mat_per)) nombre")->first();
        if(!$materia||!$docente){$resultado['bloqueos'][]='La materia o el docente dejaron de estar vigentes. Revisa la selección.';return $resultado;}
        $resultado['materia']=$materia->{$tecnica?'nom_esp':'nom_asi'};$resultado['docente']=$docente->nombre;
        $resultado['correspondencia']=app(\App\Support\Comunidad\DocenteInteligente::class)->correspondencia((string)$docente->esp_doc,$resultado['materia'],$tecnica);
        $resultado['requiere_motivo']=$resultado['correspondencia']['requiere_motivo'];
        $resultado['motivo_excepcion']=trim($form['motivo_especialidad']??'');
        $resultado['motivo_valido']=mb_strlen($resultado['motivo_excepcion'])<=500 && ExpedienteParaleloInstitucional::justificacionComprensible($resultado['motivo_excepcion']);
        if($resultado['requiere_motivo'])$resultado['advertencias'][]=$resultado['correspondencia']['mensaje'];
        $modalidad=$form['modalidad_aula']??'curso';
        $resultado['aula']= $modalidad==='curso' ? $h['nom_cur'].' · '.$h['nom_par'] : trim($form['aul_hor']??'');
        if(!in_array($modalidad,['curso','otro'],true)||($modalidad==='otro'&&mb_strlen($resultado['aula'])<3))$resultado['bloqueos'][]='Identifica el otro espacio, por ejemplo Laboratorio de Física.';

        $planes=DB::table($tecnica?'plan_especialidad':'plan_asignatura')->where('cod_gac',$h['cod_gac'])->where($tecnica?'cod_esp':'cod_asi',$id)->where($tecnica?'est_pes':'est_pas','ACTIVO')->get();
        $plan=$planes->firstWhere('cod_doc',$doc);
        if(!$plan&&$planes->isNotEmpty())$resultado['bloqueos'][]='Esta materia ya tiene otro docente asignado al grupo. Modifica su planificación con respaldo antes de cambiarlo.';
        if(!$plan){
            $resultado['crear_plan']=true;
            if(!Gate::allows($tecnica?'Especialidades_Tecnicas':'Planes_Asignatura'))$resultado['bloqueos'][]='Necesitas permiso para planificar esta materia antes de crear su clase.';
            $resultado['advertencias'][]='Esta materia aún no tiene planificación en el grupo. Al guardar se registrarán su docente y carga semanal junto con la clase.';
        }else{
            if($plan->{$tecnica?'fii_pes':'fii_pas'}>$h['fii_hor']||($plan->{$tecnica?'ffi_pes':'ffi_pas'}&&$plan->{$tecnica?'ffi_pes':'ffi_pas'}<($h['ffi_hor']??$h['ffi_gea'])))$resultado['bloqueos'][]='La planificación no cubre todo este período horario. Revisa sus fechas antes de asignar la clase.';
            $resultado['plan']=$plan->{$tecnica?'cod_pes':'cod_pas'};
        }
        $carga=$plan?(float)$plan->{$tecnica?'hor_pes':'hor_pas'}:(float)($form['carga_horaria']??0);
        $resultado['carga']=$carga;
        if($carga<1||$carga>80||floor($carga)!==$carga)$resultado['bloqueos'][]='La carga semanal debe ser de 1 a 80 períodos académicos completos.';
        if($plan){
            $cantidad=DB::table('horario_detalle')->where('cod_hor',$horario)->where('est_hde','ACTIVO')->where($tecnica?'cod_pes':'cod_pas',$resultado['plan'])->count();
            if($cantidad+1>$carga)$resultado['bloqueos'][]="La materia ya ocupa {$cantidad} bloques de sus {$carga} períodos semanales. Revisa su carga antes de agregar otro.";
        }
        if($this->coincidencias($h,$b,$dia)->where('g.cod_gac',$h['cod_gac'])->exists())$resultado['bloqueos'][]='El grupo tiene otra clase que coincide en hora y período, aunque pertenezca a otra plantilla.';
        $cruce=$this->coincidencias($h,$b,$dia)->whereRaw('COALESCE(p.cod_doc,e.cod_doc) = ?',[$doc])->join('curso as c','c.cod_cur','=','g.cod_cur')->join('paralelo as pa','pa.cod_par','=','g.cod_par')->select('c.nom_cur','pa.nom_par','b.hor_ini_hbl','b.hor_fin_hbl')->first();
        if($cruce)$resultado['bloqueos'][]='El docente ya imparte una clase en '.$cruce->nom_cur.' · '.$cruce->nom_par.', de '.substr($cruce->hor_ini_hbl,0,5).' a '.substr($cruce->hor_fin_hbl,0,5).'. Selecciona otro bloque disponible.';
        $resultado['valido']=!$resultado['bloqueos'] && (!$resultado['requiere_motivo']||$resultado['motivo_valido']) && (!$resultado['crear_plan']||($form['confirmar_plan']??false)===true);
        return $resultado;
    }

    public function guardar(string $horario,string $bloque,string $dia,array $form): HorarioDetalle
    {
        \App\Support\AccesoGestionCursos::autorizar();
        validator($form,['tipo_plan'=>'required|in:MATERIA,ESPECIALIDAD','cod_doc'=>'required|string','carga_horaria'=>'required|integer|min:1|max:80','modalidad_aula'=>'sometimes|in:curso,otro','motivo_especialidad'=>'nullable|string|max:500','aul_hor'=>'nullable|string|max:100','obs_hor'=>'nullable|string|max:255'])->validate();
        return DB::transaction(function()use($horario,$bloque,$dia,$form){
            if(!Schema::hasTable('bitacora'))throw ValidationException::withMessages(['clase'=>'La bitácora no está disponible. Conservamos la clase sin guardar; solicita revisar el registro institucional.']);
            // Dos peticiones para el mismo docente se revisan en serie, también entre cursos.
            DB::table('docente')->where('cod_doc',$form['cod_doc'])->lockForUpdate()->first();
            $cabecera=DB::table('horario')->where('cod_hor',$horario)->lockForUpdate()->first();
            if(!$cabecera)throw ValidationException::withMessages(['clase'=>'El horario ya no está disponible.']);
            DB::table('grupo_academico')->where('cod_gac',$cabecera->cod_gac)->lockForUpdate()->first();
            $revision=$this->analizar($horario,$bloque,$dia,$form);
            if(($revision['requiere_motivo']??false)&&!$revision['motivo_valido'])throw ValidationException::withMessages(['formClaseHorario.motivo_especialidad'=>'Explica la excepción en al menos cinco palabras comprensibles y hasta 500 caracteres. Ejemplo: Suplencia temporal autorizada mientras retorna el docente titular.']);
            if($revision['crear_plan'])validator($form,['confirmar_plan'=>'accepted'],['confirmar_plan.accepted'=>'Confirma que la nueva materia y su carga forman parte de la planificación aprobada del grupo.'])->validate();
            if(!$revision['valido'])throw ValidationException::withMessages(['clase'=>implode(' ',$revision['bloqueos']?:$revision['advertencias'])]);
            $tecnica=$form['tipo_plan']==='ESPECIALIDAD';$plan=$revision['plan'];
            if(!$plan){
                Gate::authorize($tecnica?'Especialidades_Tecnicas':'Planes_Asignatura');
                $modelo=$tecnica?PlanEspecialidad::class:PlanAsignatura::class;
                $p=$modelo::create(['cod_gac'=>$cabecera->cod_gac,'cod_doc'=>$form['cod_doc'],$tecnica?'cod_esp':'cod_asi'=>$form[$tecnica?'cod_esp':'cod_mat'],$tecnica?'hor_pes':'hor_pas'=>$revision['carga'],$tecnica?'fii_pes':'fii_pas'=>$cabecera->fii_hor,$tecnica?'ffi_pes':'ffi_pas'=>$cabecera->ffi_hor,$tecnica?'est_pes':'est_pas'=>'ACTIVO']);
                $plan=$p->getKey();
            }
            $detalle=HorarioDetalle::create(['cod_hor'=>$horario,'cod_hbl'=>$bloque,'dia_hde'=>$dia,'cod_pas'=>$tecnica?null:$plan,'cod_pes'=>$tecnica?$plan:null,'aul_hde'=>$revision['aula'],'obs_hde'=>trim($form['obs_hor']??''),'est_hde'=>'ACTIVO']);
            BitacoraService::registrar(accion:'CREAR_CLASE_HORARIO',tabla:'horario_detalle',registro:$detalle->getKey(),modulo:'Cursos',nombreRegistro:$revision['materia'],descripcion:'Clase organizada con revisión de bloque, docente y carga semanal.',nivel:'SUCCESS',valoresNuevos:['clase'=>$detalle->getAttributes(),'plan_creado'=>$revision['crear_plan'],'revision'=>$revision,'motivo_excepcion'=>$revision['requiere_motivo']?$revision['motivo_excepcion']:null,'modalidad_aula'=>$form['modalidad_aula']??'curso']);
            return $detalle;
        },3);
    }
}
