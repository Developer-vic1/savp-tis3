<?php
namespace App\Livewire\Admin;

use App\Models\Oficial\Academico\{Curso,GestionAcademica,VinculoPersonal,RegenteAsignacion};
use App\Services\RegencyAccessService;
use App\Support\Academico\AsignacionRegenciaInteligente;
use Illuminate\Support\Facades\DB;
use Livewire\Component;
use Livewire\WithPagination;

class AsignacionesRegencia extends Component
{
    use WithPagination;
    public string $gestion='';
    public string $regente='';
    public string $grado='';
    public string $estado='';
    public int $perPage=10;
    public bool $modal=false;
    public array $form=['cod_vpe'=>'','cod_gea'=>'','cod_cur'=>'','fii_ras'=>'','ffi_ras'=>'','obs_ras'=>''];

    private function autorizar(): void
    {
        abort_unless(auth()->user()?->est_usu==='ACTIVO' && auth()->user()->hasRole('Administrador') && auth()->user()->can('regencia.asignaciones.gestionar'),403);
    }
    public function mount(): void
    {
        $this->autorizar();
        $activas=GestionAcademica::where('est_gea','ACTIVO')->pluck('cod_gea');
        $this->gestion=$activas->count()===1?$activas->first():'';
    }
    public function updated($propiedad): void
    {
        if(in_array($propiedad,['gestion','regente','grado','estado','perPage'],true)) $this->resetPage();
        if($propiedad==='perPage' && !in_array($this->perPage,[10,20,50],true)) $this->perPage=10;
    }
    public function limpiarFiltros(): void { $this->regente=$this->grado=$this->estado=''; $this->resetPage(); }
    public function abrir(): void
    {
        $this->autorizar();
        $this->form=['cod_vpe'=>'','cod_gea'=>$this->gestion,'cod_cur'=>'','fii_ras'=>'','ffi_ras'=>'','obs_ras'=>''];
        $this->resetValidation(); $this->modal=true;
    }
    public function cerrar(): void { $this->modal=false; $this->resetValidation(); }
    public function retirar(string $id,RegencyAccessService $service): void
    {
        $this->autorizar();
        $service->retirar(auth()->user(),$id,now()->toDateString());
        session()->flash('status','Asignación retirada. Se conserva la historia y la bitácora.');
    }
    public function guardar(RegencyAccessService $service): void
    {
        $this->autorizar(); abort_unless($this->modal,422);
        $revision=app(AsignacionRegenciaInteligente::class)->revisar($this->form);
        if(!$revision['puede_guardar']){
            foreach($revision['errores'] as $campo=>$mensajes) $this->addError('form.'.$campo,implode(' ',$mensajes));
            return;
        }
        $service->assign(auth()->user(),$this->form);
        $this->cerrar(); session()->flash('status','Asignación guardada con su motivo y auditada.');
    }
    public static function estadoTemporal(RegenteAsignacion $asignacion,string $hoy): string
    {
        if($asignacion->est_ras!=='ACTIVO') return 'Retirada';
        if($asignacion->fii_ras->toDateString()>$hoy) return 'Programada';
        if($asignacion->ffi_ras && $asignacion->ffi_ras->toDateString()<$hoy) return 'Finalizada';
        return 'Vigente';
    }
    public function render(RegencyAccessService $service)
    {
        $this->autorizar(); $hoy=now()->toDateString(); $ready=$service->available();
        $base=RegenteAsignacion::with('vinculoPersonal.personalInstitucional.persona','gestion','curso');
        if($this->gestion!=='') $base->where('cod_gea',$this->gestion);
        $todas=$ready?(clone $base)->get():collect();
        $regents=VinculoPersonal::with('personalInstitucional.persona')->where('est_vpe','ACTIVO')
            ->where('fii_vpe','<=',$hoy)->where(fn($q)=>$q->whereNull('ffi_vpe')->orWhere('ffi_vpe','>=',$hoy))
            ->whereHas('cargoInstitucional',fn($q)=>$q->where('cla_cai','REGENTE'))
            ->whereHas('personalInstitucional',fn($q)=>$q->where('est_pin','ACTIVO')->whereHas('persona.usuario',fn($u)=>$u->role('Regente')->where('est_usu','ACTIVO')))->get();
        $vigentes=$todas->filter(fn($r)=>self::estadoTemporal($r,$hoy)==='Vigente');
        $inscripciones=collect();
        if ($vigentes->isNotEmpty()) {
            $inscripciones=DB::table('inscripcion_vigencia as v')
                ->join('inscripcion_estudiante as i','i.cod_ins','=','v.cod_ins')
                ->join('grupo_academico as g','g.cod_gac','=','v.cod_gac')
                ->whereIn('g.cod_cur',$vigentes->pluck('cod_cur'))->whereIn('g.cod_gea',$vigentes->pluck('cod_gea'))
                ->whereColumn('i.cod_gea','g.cod_gea')->where('g.est_gac','ACTIVO')
                ->whereIn('i.est_ins',['ACTIVA','ACTIVO','CONFIRMADA'])->where('v.est_ivg','ACTIVO')
                ->where('v.fii_ivg','<=',$hoy)->where(fn($q)=>$q->whereNull('v.ffi_ivg')->orWhere('v.ffi_ivg','>=',$hoy))
                ->get(['i.cod_est','i.obs_ins','i.mot_obs_ins','g.cod_cur','g.cod_gea']);
        }
        $planes=collect();
        if($vigentes->isNotEmpty()) foreach(['plan_asignatura'=>'pas','plan_especialidad'=>'pes'] as $tabla=>$sufijo){
            $planes=$planes->concat(DB::table($tabla.' as p')->join('grupo_academico as g','g.cod_gac','=','p.cod_gac')
                ->whereIn('g.cod_cur',$vigentes->pluck('cod_cur'))->whereIn('g.cod_gea',$vigentes->pluck('cod_gea'))
                ->where('g.est_gac','ACTIVO')->where('p.est_'.$sufijo,'ACTIVO')->where('p.fii_'.$sufijo,'<=',$hoy)
                ->where(fn($q)=>$q->whereNull('p.ffi_'.$sufijo)->orWhere('p.ffi_'.$sufijo,'>=',$hoy))->get(['g.cod_cur','g.cod_gea','p.cod_doc']));
        }
        $panorama=$regents->map(function($r)use($vigentes,$planes,$inscripciones){
            $grados=$vigentes->where('cod_vpe',$r->cod_vpe);
            $docentes=$planes->filter(fn($p)=>$grados->contains(fn($a)=>$a->cod_cur===$p->cod_cur && $a->cod_gea===$p->cod_gea))->pluck('cod_doc')->unique()->count();
            return ['vinculo'=>$r,'grados'=>$grados->unique('cod_cur'),'docentes'=>$docentes] + \App\Support\Academico\PanoramaRegencia::contar($inscripciones,$grados);
        });
        if($this->regente!=='') $base->where('cod_vpe',$this->regente);
        if($this->grado!=='') $base->where('cod_cur',$this->grado);
        if($this->estado!=='') $base->whereIn('cod_ras',$todas->filter(fn($r)=>self::estadoTemporal($r,$hoy)===$this->estado)->pluck('cod_ras'));
        return view('livewire.admin.asignaciones-regencia',[
            'ready'=>$ready,'assignments'=>$ready?$base->orderByDesc('fii_ras')->paginate($this->perPage):null,
            'regents'=>$regents,'panorama'=>$panorama,'hoy'=>$hoy,
            'cobertura'=>\App\Support\Academico\PanoramaRegencia::contar($inscripciones,$vigentes),
            'porGrado'=>$vigentes->unique(fn($r)=>$r->cod_cur.'|'.$r->cod_gea)->mapWithKeys(fn($r)=>[$r->cod_cur.'|'.$r->cod_gea=>\App\Support\Academico\PanoramaRegencia::contar($inscripciones,collect([$r]))]),
            'years'=>GestionAcademica::orderByDesc('ani_gea')->get(),'courses'=>Curso::where('est_cur','ACTIVO')->orderBy('nom_cur')->get(),
            'metricas'=>['asignaciones'=>$todas->count(),'grados'=>$vigentes->unique('cod_cur')->count(),'docentes'=>$planes->pluck('cod_doc')->unique()->count(),'regentes'=>$regents->count()],
            'revision'=>$this->modal?app(AsignacionRegenciaInteligente::class)->revisar($this->form):null,
            'rotacion'=>($seleccionada=GestionAcademica::find($this->gestion)) ? app(\App\Services\RotacionRegenciaService::class)->propuestas($seleccionada->ani_gea+1) : [],
        ]);
    }
}
