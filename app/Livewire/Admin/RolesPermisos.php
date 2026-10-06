<?php
namespace App\Livewire\Admin;

use App\Models\Oficial\Sistema\{Role,User,Permission,SolicitudRol,ConcesionAcceso};
use App\Services\{InstitutionalAuthorityService,RolePermissionService,RoleRequestService,AccesoProgramadoService};
use App\Support\{PermissionLabel,ConsultaRolesInstitucionales,LegacyReadPermission};
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Locked;
use Livewire\{Component,WithFileUploads};

class RolesPermisos extends Component
{
    use WithFileUploads;
    public ?int $selectedRoleId=null;
    public array $selectedPermissions=[];
    public string $apartado='roles';
    #[Locked] public string $huellaRol='';
    public string $motivoCambio='';
    #[Locked] public bool $edicionPermisos=false;
    public string $tipoMotivoCambio='',$tipoMotivoSolicitud='',$tipoMotivoAcceso='',$tipoMotivoRevocacion='';
    public array $responsabilidadesSolicitud=[];
    public bool $showRequestModal=false;
    #[Locked] public int $requestStep=1;
    public string $requestedName='',$justification='',$institutionalReason='',$functions='',$requestedScope='',$observations='';
    public array $requestedPermissions=[];
    public $document=null;
    #[Locked] public ?array $governanceResult=null;
    #[Locked] public ?array $lecturaDocumento=null;
    #[Locked] public ?string $activeRequestId=null;
    public string $reviewNote='';
    public bool $directorMatches=false,$documentReadable=false,$signaturePresent=false,$sealPresent=false;
    public bool $modalAcceso=false;
    public string $modoVigenciaAcceso='PERSONALIZADO';
    public string $tipoAcceso='PERMISOS',$inicioAcceso='',$finAcceso='',$motivoAcceso='',$buscarUsuario='';
    public string $fechaInicioAcceso='',$horaInicioAcceso='08',$minutoInicioAcceso='00',$fechaFinAcceso='',$horaFinAcceso='18',$minutoFinAcceso='00';
    public ?int $rolAcceso=null,$rolDestinatarios=null;
    public array $usuariosAcceso=[],$permisosAcceso=[];
    #[Locked] public ?array $analisisAcceso=null;
    #[Locked] public string $revisionAcceso='';
    #[Locked] public ?string $revocarCodigo=null;
    public string $motivoRevocacion='';
    public string $buscarCuenta='', $tipoMotivoUsuario='', $motivoUsuario='';
    public array $permisosUsuario=[];
    #[Locked] public ?string $cuentaSeleccionada=null;
    #[Locked] public bool $edicionUsuario=false;
    #[Locked] public string $huellaUsuario='';

    public function seleccionarCuenta(string $id):void
    {
        $this->consultar();$svc=app(\App\Services\PermisosPersonalesService::class);$svc->autorizar(auth()->user());
        $usuario=User::with(['roles.permissions','permissions'])->findOrFail($id);
        $this->cuentaSeleccionada=$id;$this->permisosUsuario=$usuario->permissions->pluck('name')->all();
        $this->huellaUsuario=$svc->huella($usuario);$this->edicionUsuario=false;
        $this->tipoMotivoUsuario=$this->motivoUsuario='';$this->resetValidation();
    }
    public function editarCuenta():void
    {
        $svc=app(\App\Services\PermisosPersonalesService::class);$svc->autorizar(auth()->user());
        $usuario=User::findOrFail($this->cuentaSeleccionada);
        abort_if($usuario->is(auth()->user()),403);abort_unless(app(\App\Services\RoleDashboardResolver::class)->roleFor($usuario),422);
        $this->edicionUsuario=true;
    }
    public function descartarCuenta():void{$this->seleccionarCuenta($this->cuentaSeleccionada??'');}
    public function guardarCuenta():void
    {
        abort_unless($this->edicionUsuario&&$this->cuentaSeleccionada,422,'Activa Edición para cambiar los permisos personales.');
        app(\App\Services\PermisosPersonalesService::class)->guardar(auth()->user(),$this->cuentaSeleccionada,$this->permisosUsuario,$this->huellaUsuario,$this->tipoMotivoUsuario,$this->motivoUsuario);
        $this->seleccionarCuenta($this->cuentaSeleccionada);
        $this->dispatch('notificaciones-actualizadas');
        $this->dispatch('roles-acceso-guardado',title:'Permisos personales actualizados',text:'Los cambios se guardaron. La persona tiene su aviso en Notificaciones.');
    }
    public function accesoParaCuenta():void
    {
        app(\App\Services\PermisosPersonalesService::class)->autorizar(auth()->user());
        User::findOrFail($this->cuentaSeleccionada);$this->abrirAcceso();$this->usuariosAcceso=[$this->cuentaSeleccionada];
    }

    private function consultar():void{abort_unless(auth()->user()?->can('roles-permisos.ver'),403);}
    public function mount():void{$this->consultar();$this->selectedRoleId=Role::where('guard_name','web')->where('name','Administrador')->value('id')??Role::where('guard_name','web')->value('id');$this->loadRole();}
    public function updatedSelectedRoleId():void{$this->loadRole();}
    public function loadRole():void{$this->consultar();$role=Role::where('guard_name','web')->findOrFail($this->selectedRoleId);$this->selectedPermissions=$role->permissions()->pluck('name')->all();$this->huellaRol=app(RolePermissionService::class)->huella($role);$this->motivoCambio='';$this->tipoMotivoCambio='';$this->edicionPermisos=false;$this->resetValidation();}
    public function habilitarEdicion():void{$this->consultar();app(AccesoProgramadoService::class)->autorizar(auth()->user());$this->edicionPermisos=true;}
    public function salirEdicion():void{$this->loadRole();}
    public function save(RolePermissionService $service):void
    {
        $this->consultar();abort_unless($this->edicionPermisos,422,'Activa Edición antes de cambiar permisos.');$this->validate(['selectedPermissions'=>'array|max:300','selectedPermissions.*'=>'string|distinct','motivoCambio'=>'string|max:2000']);
        $role=Role::where('guard_name','web')->findOrFail($this->selectedRoleId);$service->sync($role,$this->selectedPermissions,auth()->user(),$this->motivoCambio,$this->tipoMotivoCambio,$this->huellaRol);$this->tipoMotivoCambio='';$this->loadRole();
        $this->dispatch('notificaciones-actualizadas');
        $this->dispatch('roles-acceso-guardado',title:'Permisos de '.$role->name.' actualizados',text:'Los cambios se guardaron.'.(app(\App\Services\NotificationService::class)->available()?' Las cuentas de este rol tienen su aviso en Notificaciones.':' Las notificaciones institucionales no están habilitadas.'));
    }
    public function openRequest():void
    {
        app(RoleRequestService::class)->authorize(auth()->user(),'roles.solicitudes.crear');
        $this->reset(['requestedName','justification','institutionalReason','functions','requestedScope','observations','requestedPermissions','document','governanceResult','lecturaDocumento','activeRequestId','requestStep']);
        $this->tipoMotivoSolicitud='';$this->responsabilidadesSolicitud=[];$this->resetValidation();$this->showRequestModal=true;
    }
    public function closeRequest():void{$this->showRequestModal=false;$this->resetValidation();}
    private function requestData():array
    {
        $support=\App\Support\SupportRolesInstitucionales::class;
        $support::comprobarTexto($this->requestedName,'requestedName',4,true);
        $motivo=$support::motivo('solicitud',$this->tipoMotivoSolicitud,$this->institutionalReason,'institutionalReason');
        $funciones=$support::funciones($this->responsabilidadesSolicitud);
        $this->justification=$motivo;$this->functions=$funciones;
        return ['requested_name'=>$this->requestedName,'justification'=>$motivo,'institutional_reason'=>$this->institutionalReason,'motivo_tipo'=>$this->tipoMotivoSolicitud,'responsabilidades'=>$this->responsabilidadesSolicitud,'functions'=>$funciones,'scope'=>$this->requestedScope,'observations'=>$this->observations,'requested_permissions'=>$this->requestedPermissions];
    }
    public function nextRequestStep():void
    {
        app(RoleRequestService::class)->authorize(auth()->user(),'roles.solicitudes.crear');
        if($this->requestStep===1){$this->requestData();$this->validate(['requestedName'=>'required|string|min:4|max:80','justification'=>'required|string|min:30|max:2000','functions'=>'required|string|min:30|max:3000','requestedScope'=>['required',\Illuminate\Validation\Rule::in(\App\Support\SupportRolesInstitucionales::AMBITOS)]]);$this->requestStep=2;}
        elseif($this->requestStep===2){$this->analyzeRequest();if(!$this->governanceResult['reasons']&&!$this->governanceResult['blocked_permissions'])$this->requestStep=3;}
    }
    public function volverSolicitud():void{$this->consultar();$this->requestStep=max(1,$this->requestStep-1);}
    public function analyzeRequest():void
    {
        app(RoleRequestService::class)->authorize(auth()->user(),'roles.solicitudes.crear');$this->validate(['requestedPermissions'=>'required|array|min:1','requestedPermissions.*'=>'string|distinct']);
        $this->governanceResult=app(RoleRequestService::class)->analyze($this->requestData());
    }
    public function leerCarta():void
    {
        $this->validate(['document'=>'required|file|max:8192|mimes:pdf|extensions:pdf']);
        $this->lecturaDocumento=app(RoleRequestService::class)->comprobarDocumento(auth()->user(),$this->requestData(),$this->document);
    }
    public function submitRequest(RoleRequestService $service):void
    {
        abort_unless($this->showRequestModal&&$this->requestStep===3,422);$this->validate(['document'=>'required|file|max:8192|mimes:pdf|extensions:pdf']);
        $s=$service->submit(auth()->user(),$this->requestData(),$this->document);$this->closeRequest();
        $this->dispatch($s->estado==='RECHAZADA'?'swal:warning':'swal:success',title:$s->estado==='RECHAZADA'?'Carta que necesita corrección':'Solicitud registrada',text:$s->estado==='RECHAZADA'?'El intento y su evidencia se conservaron; el rol no se creó.':'Otro administrador debe revisar la carta firmada antes de crear el rol.');
    }
    public function viewRequest(string $id):void
    {
        $s=app(RoleRequestService::class);$s->authorize(auth()->user(),'roles.solicitudes.ver');abort_unless($s->disponible(),404);SolicitudRol::findOrFail($id);
        $this->activeRequestId=$id;$this->reviewNote='';$this->directorMatches=$this->documentReadable=$this->signaturePresent=$this->sealPresent=false;$this->resetValidation();
    }
    public function reviewRequest(RoleRequestService $s,bool $approved):void{$s->review(auth()->user(),$this->activeRequestId??'',$approved,$this->reviewNote,$this->directorMatches,$this->documentReadable,$this->signaturePresent,$this->sealPresent);$this->activeRequestId=null;$this->dispatch('swal:success',title:'Revisión registrada',text:'Conservamos la decisión y la carta en el historial.');}
    public function cerrarRevision():void{$this->activeRequestId=null;$this->resetValidation();}
    public function createRole(RoleRequestService $s):void{$s->createRole(auth()->user(),$this->activeRequestId??'');$this->activeRequestId=null;$this->dispatch('swal:success',title:'Rol complementario creado',text:'La autorización y los permisos aprobados quedaron registrados.');}
    public function cancelRequest(RoleRequestService $s):void{$s->cancel(auth()->user(),$this->activeRequestId??'');$this->activeRequestId=null;}
    public function abrirAcceso():void
    {
        app(AccesoProgramadoService::class)->autorizar(auth()->user());$this->reset(['tipoAcceso','modoVigenciaAcceso','rolAcceso','rolDestinatarios','usuariosAcceso','permisosAcceso','inicioAcceso','finAcceso','motivoAcceso','buscarUsuario','analisisAcceso']);
        $this->tipoMotivoAcceso='';$inicio=now()->setTimezone(config('seguridad-accesos.zona_horaria'))->addMinute();
        $this->fechaInicioAcceso=$inicio->toDateString();$this->horaInicioAcceso=$inicio->format('H');$this->minutoInicioAcceso=$inicio->format('i');
        $this->fechaFinAcceso='';$this->horaFinAcceso='18';$this->minutoFinAcceso='00';$this->modalAcceso=true;$this->resetValidation();
    }
    public function updated($campo):void
    {
        if(in_array($campo,['tipoAcceso','modoVigenciaAcceso','rolAcceso','usuariosAcceso','permisosAcceso','inicioAcceso','finAcceso','motivoAcceso','tipoMotivoAcceso','fechaInicioAcceso','horaInicioAcceso','minutoInicioAcceso','fechaFinAcceso','horaFinAcceso','minutoFinAcceso']))$this->analisisAcceso=null;
        if(in_array($campo,['document','requestedName','requestedPermissions','justification','functions','tipoMotivoSolicitud','institutionalReason','responsabilidadesSolicitud','requestedScope'])){$this->lecturaDocumento=null;$this->governanceResult=null;}
    }
    public function agregarGrupo():void
    {
        app(AccesoProgramadoService::class)->autorizar(auth()->user());$role=Role::where('guard_name','web')->findOrFail($this->rolDestinatarios);
        $ids=User::where('est_usu','ACTIVO')->role($role->name)->limit(2000)->pluck('cod_usu')->all();
        $this->usuariosAcceso=array_values(array_unique(array_merge($this->usuariosAcceso,$ids)));$this->analisisAcceso=null;
    }
    private function datosAcceso():array
    {
        $this->validate(['modoVigenciaAcceso'=>['required',\Illuminate\Validation\Rule::in(['DIA','PERSONALIZADO'])],
            'fechaInicioAcceso'=>'required|date_format:Y-m-d','fechaFinAcceso'=>'required|date_format:Y-m-d',
            'horaInicioAcceso'=>['required','regex:/^(?:[01][0-9]|2[0-3])$/'],'horaFinAcceso'=>['required','regex:/^(?:[01][0-9]|2[0-3])$/'],
            'minutoInicioAcceso'=>['required','regex:/^[0-5][0-9]$/'],'minutoFinAcceso'=>['required','regex:/^[0-5][0-9]$/']]);
        $inicio=$this->fechaInicioAcceso.'T'.$this->horaInicioAcceso.':'.$this->minutoInicioAcceso;
        $fin=$this->fechaFinAcceso.'T'.$this->horaFinAcceso.':'.$this->minutoFinAcceso;
        $zona=config('seguridad-accesos.zona_horaria','America/La_Paz');
        if($this->modoVigenciaAcceso==='DIA'&&\Carbon\CarbonImmutable::parse($inicio,$zona)->diffInSeconds(\Carbon\CarbonImmutable::parse($fin,$zona),false)!==86400.0){
            throw \Illuminate\Validation\ValidationException::withMessages(['modoVigenciaAcceso'=>'Un día requiere exactamente 24 horas. Cambia a Personalizado para ajustar las fechas.']);
        }
        return ['tipo'=>$this->tipoAcceso,'vigencia'=>$this->modoVigenciaAcceso,'role_id'=>$this->rolAcceso,'usuarios'=>$this->usuariosAcceso,'permisos'=>$this->permisosAcceso,
            'inicio'=>$this->fechaInicioAcceso.'T'.$this->horaInicioAcceso.':'.$this->minutoInicioAcceso,
            'fin'=>$this->fechaFinAcceso.'T'.$this->horaFinAcceso.':'.$this->minutoFinAcceso,'motivo'=>$this->motivoAcceso,'motivo_tipo'=>$this->tipoMotivoAcceso];
    }
    public function analizarAcceso():void
    {
        $this->analisisAcceso=null;$this->revisionAcceso='';$this->resetValidation();
        $datos=$this->datosAcceso();$a=app(AccesoProgramadoService::class)->analizar(auth()->user(),$datos);
        $this->revisionAcceso=hash('sha256',json_encode($datos));
        $this->analisisAcceso=['valido'=>$a['valido'],'errores'=>$a['errores'],'advertencias'=>$a['advertencias'],'personas'=>$a['usuarios']->count(),'permisos'=>$a['permisos']->count(),'inicio'=>$a['inicio']->setTimezone(config('seguridad-accesos.zona_horaria'))->locale('es')->isoFormat('dddd D [de] MMMM [a las] HH:mm'),'fin'=>$a['fin']->setTimezone(config('seguridad-accesos.zona_horaria'))->locale('es')->isoFormat('dddd D [de] MMMM [a las] HH:mm')];
        $a['usuarios']->loadMissing('persona');
        $this->analisisAcceso['destinatarios']=$a['usuarios']->take(20)->map(fn($u)=>trim(implode(' ',array_filter([$u->persona?->nom_per,$u->persona?->ape_pat_per,$u->persona?->ape_mat_per])))?:'Cuenta institucional')->all();
        $this->analisisAcceso['tareas']=$a['permisos']->map(fn($p)=>PermissionLabel::describe($p->name)['label'].' · '.PermissionLabel::describe($p->name)['scope_label'])->all();
        $this->analisisAcceso['nombres_permisos']=$a['permisos']->pluck('name')->all();
        $this->analisisAcceso['motivo']=$a['datos']['motivo'];
    }
    public function guardarAcceso():void
    {
        abort_unless($this->modalAcceso&&($this->analisisAcceso['valido']??false),422,'Revisa la vigencia antes de autorizar.');
        $datos=$this->datosAcceso();abort_unless(hash_equals($this->revisionAcceso,hash('sha256',json_encode($datos))),422,'Los datos cambiaron; vuelve a revisar la vigencia.');
        $datos['permisos_revisados']=$this->analisisAcceso['nombres_permisos'];
        app(AccesoProgramadoService::class)->crear(auth()->user(),$datos);$this->modalAcceso=false;
        $this->dispatch('notificaciones-actualizadas');
        $destino=$this->analisisAcceso['personas']===1?$this->analisisAcceso['destinatarios'][0]:$this->analisisAcceso['personas'].' cuentas';
        $this->dispatch('roles-acceso-guardado',title:'Acceso programado para '.$destino,text:'Terminará el '.$this->analisisAcceso['fin'].'. La confirmación y los avisos personales están en Notificaciones.');
    }
    public function abrirRevocacion(string $id):void{app(AccesoProgramadoService::class)->autorizar(auth()->user());ConcesionAcceso::findOrFail($id);$this->revocarCodigo=$id;$this->motivoRevocacion='';$this->tipoMotivoRevocacion='';}
    public function cerrarRevocacion():void{$this->revocarCodigo=null;$this->resetValidation();}
    public function revocarAcceso():void{app(AccesoProgramadoService::class)->revocar(auth()->user(),$this->revocarCodigo??'',$this->motivoRevocacion,$this->tipoMotivoRevocacion);$this->revocarCodigo=null;$this->dispatch('notificaciones-actualizadas');}

    public function render()
    {
        $this->consultar();$r=app(ConsultaRolesInstitucionales::class)->resumen();$roles=$r['roles'];$selectedRole=$roles->firstWhere('id',$this->selectedRoleId);
        $catalogador=app(\App\Support\CatalogoVentanasPermisos::class);
        $ventanas=$catalogador->ventanas(auth()->user());
        $catalogoCompleto=Permission::with('roles:id,name,guard_name')->orderBy('guard_name')->orderBy('name')->get()->map(function($p)use($catalogador,$ventanas){
            $label=PermissionLabel::describe($p->name);
            return $catalogador->describir(['name'=>$p->name,'id'=>$p->id,'guard'=>$p->guard_name,'roles'=>$p->roles->pluck('name')->all(),...$label,'domain'=>$label['area']],$p->guard_name==='web'?$ventanas:[]);
        });
        $permisos=$catalogoCompleto->where('guard','web')->values();
        $solicitudesDisponibles=app(RoleRequestService::class)->disponible();$accesosDisponibles=app(AccesoProgramadoService::class)->disponible();
        $cuentas=collect();
        if($this->modalAcceso){
            $cuentas=User::where('est_usu','ACTIVO')->with('persona')->where(function($q){
                $q->whereIn('cod_usu',$this->usuariosAcceso);
                if(mb_strlen(trim($this->buscarUsuario))>=2){$s='%'.mb_strtolower(trim($this->buscarUsuario)).'%';$q->orWhereHas('persona',fn($p)=>$p->where(fn($n)=>$n->whereRaw('LOWER(nom_per) LIKE ?',[$s])->orWhereRaw('LOWER(ape_pat_per) LIKE ?',[$s])->orWhereRaw('LOWER(ape_mat_per) LIKE ?',[$s])));}
            })->limit(100)->get();
        }
        $permanentes=auth()->user()->getAllPermissions()->pluck('name')->all();$temporales=app(AccesoProgramadoService::class)->permisosVigentes(auth()->user());
        $misPermisos=array_values(array_unique(array_merge($permanentes,$temporales)));
        $svcPersonal=app(\App\Services\PermisosPersonalesService::class);
        $puedePersonal=app(\App\Services\RoleDashboardResolver::class)->roleFor(auth()->user())==='Administrador'
            &&in_array('roles-permisos.gestionar',$permanentes,true)&&in_array('roles.permisos.asignar',$permanentes,true);
        $coincidencias=collect();$usuarioElegido=null;$resumenUsuario=null;$tareasUsuario=collect();
        if($puedePersonal){
            if(mb_strlen(trim($this->buscarCuenta))>=2){
                $texto='%'.mb_strtolower(trim($this->buscarCuenta)).'%';
                $coincidencias=User::with(['persona','roles'])->where('est_usu','ACTIVO')
                    ->whereHas('persona',fn($p)=>$p->where(fn($q)=>$q->whereRaw('LOWER(nom_per) LIKE ?',[$texto])->orWhereRaw('LOWER(ape_pat_per) LIKE ?',[$texto])->orWhereRaw('LOWER(ape_mat_per) LIKE ?',[$texto])))
                    ->orderBy('cod_usu')->limit(20)->get();
            }
            if($this->cuentaSeleccionada){
                $usuarioElegido=User::with(['persona','roles.permissions','permissions'])->find($this->cuentaSeleccionada);
                if($usuarioElegido){
                    $heredados=$usuarioElegido->getPermissionsViaRoles()->pluck('name')->all();$directos=$usuarioElegido->permissions->pluck('name')->all();
                    $temporalesUsuario=app(AccesoProgramadoService::class)->permisosVigentes($usuarioElegido);
                    $unicos=array_values(array_unique(array_merge($heredados,$directos,$temporalesUsuario)));
                    $resumenUsuario=['total'=>count($unicos),'heredados'=>count($heredados),'personales'=>count(array_diff($directos,$heredados)),
                        'temporales'=>count(array_diff($temporalesUsuario,$heredados,$directos)),'directos'=>$directos,'heredadosNombres'=>$heredados];
                    $tareasUsuario=$permisos->map(fn($p)=>[...$p,'heredado'=>in_array($p['name'],$heredados,true),'directo'=>in_array($p['name'],$directos,true),
                        'temporal'=>in_array($p['name'],$temporalesUsuario,true),'delegable'=>$svcPersonal->delegable($p['name'],auth()->user())
                            &&($p['name']!==\App\Support\AccesoGestionCursos::PERMISO_DELEGADO||app(\App\Services\RoleDashboardResolver::class)->roleFor($usuarioElegido)==='Docente')]);
                }
            }
        }
        return view('livewire.admin.roles-permisos',[
            'puedePersonal'=>$puedePersonal,'coincidencias'=>$coincidencias,'usuarioElegido'=>$usuarioElegido,'resumenUsuario'=>$resumenUsuario,'tareasUsuario'=>$tareasUsuario,
            'permisosCuenta'=>$permisos->whereIn('name',$misPermisos),'cuenta'=>['total'=>count($misPermisos),'permanentes'=>count($permanentes),'temporales'=>count(array_diff($temporales,$permanentes))],
            'gestionActiva'=>\App\Models\Oficial\Academico\GestionAcademica::where('est_gea','ACTIVO')->first(),
            'roles'=>$roles,'selectedRole'=>$selectedRole,'cuentasConRol'=>$r['cuentas'],'permisos'=>$permisos,
            'catalogoCompleto'=>$catalogoCompleto,'ventanas'=>$ventanas,
            'dominios'=>$permisos->pluck('domain')->unique()->sort()->values(),'authority'=>app(InstitutionalAuthorityService::class)->current(),
            'solicitudesDisponibles'=>$solicitudesDisponibles,'accesosDisponibles'=>$accesosDisponibles,'cuentas'=>$cuentas,
            'solicitudes'=>$solicitudesDisponibles?SolicitudRol::with(['solicitante.persona','revisor.persona','permisos'])->latest()->limit(25)->get():collect(),
            'activeRequest'=>$solicitudesDisponibles&&$this->activeRequestId?SolicitudRol::with(['permisos','director.persona','gestion'])->find($this->activeRequestId):null,
            'accesos'=>$accesosDisponibles?ConcesionAcceso::with(['rol','permisos'])->withCount('usuarios')->latest()->limit(25)->get():collect(),
            'roleWindows'=>collect(config('architecture_windows',[]))->where('actor',$selectedRole?->name)->values(),
            'legacyReadGrants'=>collect(LegacyReadPermission::FALLBACKS)->filter(fn($actores,$p)=>isset($actores[$selectedRole?->name])&&$selectedRole?->permissions->contains('name',$actores[$selectedRole?->name]))->keys(),
        ]);
    }
}
