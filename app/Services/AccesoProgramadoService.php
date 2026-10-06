<?php
namespace App\Services;

use App\Models\Oficial\Academico\GestionAcademica;
use App\Models\Oficial\Sistema\{ConcesionAcceso,Permission,Role,User};
use App\Support\PermissionLabel;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\{DB,Schema};
use Illuminate\Validation\{Rule,ValidationException};

class AccesoProgramadoService
{
    private array $ventanas=[];
    private ?bool $disponible=null;

    public function disponible(): bool
    {
        return $this->disponible ??= Schema::hasTable('concesion_acceso') && Schema::hasTable('concesion_acceso_usuario') && Schema::hasTable('concesion_acceso_permiso');
    }

    public function permite(User $usuario,string $permiso): bool
    {
        $cursoDelegado=$permiso===\App\Support\AccesoGestionCursos::PERMISO_DELEGADO;
        if($usuario->est_usu!=='ACTIVO'||!str_contains($permiso,'.')||(!$cursoDelegado&&(PermissionLabel::describe($permiso)['critical']||str_ends_with($permiso,'.global')))||!$this->disponible())return false;
        if(!app(RoleDashboardResolver::class)->roleFor($usuario))return false;
        if($cursoDelegado&&app(RoleDashboardResolver::class)->roleFor($usuario)!=='Docente')return false;
        // Se memoriza solo en esta petición. Cada comprobación vuelve a evaluar el reloj.
        $datos=$this->ventanas[$usuario->getKey()] ??= DB::table('concesion_acceso as c')
            ->join('concesion_acceso_usuario as u','u.cod_cac','=','c.cod_cac')
            ->join('concesion_acceso_permiso as p','p.cod_cac','=','c.cod_cac')
            ->join('permissions as permiso','permiso.id','=','p.permission_id')
            ->where('u.cod_usu',$usuario->getKey())->where('c.estado','AUTORIZADA')->where('c.fin','>',$this->fechaConsulta(now()))
            ->where('permiso.guard_name','web')->get(['permiso.name','c.inicio','c.fin','c.tipo']);
        return $datos->contains(fn($r)=>$r->name===$permiso&&CarbonImmutable::parse($r->inicio)<=now()&&CarbonImmutable::parse($r->fin)>now()
            &&(!$cursoDelegado||$r->tipo==='PERMISOS'&&CarbonImmutable::parse($r->inicio)->diffInSeconds(CarbonImmutable::parse($r->fin))<=86400));
    }

    public function autorizar(User $actor): void
    {
        abort_unless(app(RoleDashboardResolver::class)->roleFor($actor)==='Administrador'
            && $actor->can('roles-permisos.gestionar')&&$actor->can('roles.permisos.asignar'),403);
    }

    public function permisosVigentes(User $usuario): array
    {
        if(!$this->disponible())return [];
        $nombres=DB::table('concesion_acceso as c')->join('concesion_acceso_usuario as u','u.cod_cac','=','c.cod_cac')
            ->join('concesion_acceso_permiso as p','p.cod_cac','=','c.cod_cac')->join('permissions as permiso','permiso.id','=','p.permission_id')
            ->where('u.cod_usu',$usuario->getKey())->where('c.estado','AUTORIZADA')->where('c.inicio','<=',$this->fechaConsulta(now()))->where('c.fin','>',$this->fechaConsulta(now()))->distinct()->pluck('permiso.name');
        return $nombres->filter(fn($p)=>$this->permite($usuario,$p))->values()->all();
    }

    public function analizar(User $actor,array $datos): array
    {
        $this->autorizar($actor);
        validator($datos,['motivo_tipo'=>'required|string','motivo'=>'nullable|string|max:2000'])->validate();
        $datos['motivo']=\App\Support\SupportRolesInstitucionales::motivo('acceso',(string)($datos['motivo_tipo']??''),(string)($datos['motivo']??''));
        $datos['vigencia']??='PERSONALIZADO';
        $datos=validator($datos,[
            'tipo'=>['required',Rule::in(['PERMISOS','ROL'])], 'role_id'=>'nullable|integer',
            'vigencia'=>['required',Rule::in(['DIA','PERSONALIZADO'])],
            'usuarios'=>'required|array|min:1|max:2000','usuarios.*'=>'required|string|distinct',
            'permisos'=>'array|max:200','permisos.*'=>'string|distinct',
            'permisos_revisados'=>'sometimes|array|max:200','permisos_revisados.*'=>'string|distinct',
            'inicio'=>['required','date_format:Y-m-d\TH:i'],'fin'=>['required','date_format:Y-m-d\TH:i','after:inicio'], 'motivo'=>'required|string|min:20|max:2000',
        ])->validate();
        $zona=config('seguridad-accesos.zona_horaria','America/La_Paz');
        $inicio=CarbonImmutable::parse($datos['inicio'],$zona)->utc();$fin=CarbonImmutable::parse($datos['fin'],$zona)->utc();
        $gestion=GestionAcademica::where('est_gea','ACTIVO')->get();
        $errores=[];$advertencias=[];
        if($datos['vigencia']==='DIA'&&$inicio->diffInSeconds($fin,false)!==86400.0)$errores[]='Un día requiere exactamente 24 horas. Cambia a Personalizado para ajustar las fechas.';
        if($gestion->count()!==1)$errores[]='Debe existir una sola gestión activa para respaldar la vigencia.';
        $g=$gestion->first();
        if($inicio<now()->startOfMinute())$errores[]='El inicio no puede estar en el pasado.';
        if($g&&($inicio<CarbonImmutable::parse($g->fii_gea->format('Y-m-d'),$zona)->startOfDay()||$fin>CarbonImmutable::parse($g->ffi_gea->format('Y-m-d'),$zona)->endOfDay()))$errores[]='La vigencia debe quedar dentro de la gestión activa.';
        $rol=null;
        if($datos['tipo']==='ROL'){
            $rol=Role::with('permissions')->where('guard_name','web')->find($datos['role_id']??0);
            if(!$rol||in_array($rol->name,Role::INSTITUTIONAL,true))$errores[]='Selecciona un rol complementario aprobado. El actor institucional se administra desde Usuarios.';
            $nombres=$rol?->permissions->pluck('name')->all()??[];
        }else{$nombres=$datos['permisos']??[];}
        $permisos=Permission::where('guard_name','web')->whereIn('name',$nombres)->get();
        if(!$nombres||$permisos->count()!==count($nombres))$errores[]='Selecciona permisos válidos del catálogo.';
        if(isset($datos['permisos_revisados'])){
            $revisados=$datos['permisos_revisados'];$actuales=$nombres;sort($revisados);sort($actuales);
            if($revisados!==$actuales)$errores[]='Las tareas del rol cambiaron después de la revisión. Vuelve a revisar el acceso antes de autorizar.';
        }
        // Una concesión temporal no puede autorizar otra concesión: solo autoridad permanente.
        $propios=$actor->getAllPermissions()->pluck('name')->all();
        foreach($permisos as $p){
            $cursoDelegado=$p->name===\App\Support\AccesoGestionCursos::PERMISO_DELEGADO&&$datos['tipo']==='PERMISOS';
            if(!in_array($p->name,$propios,true)||!str_contains($p->name,'.')||(!$cursoDelegado&&(PermissionLabel::describe($p->name)['critical']||str_ends_with($p->name,'.global'))))$errores[]='No puedes delegar '.$this->etiqueta($p->name).'; requiere otra autoridad o un alcance menor.';
        }
        $delegacionCursos=in_array(\App\Support\AccesoGestionCursos::PERMISO_DELEGADO,$nombres,true);
        if($delegacionCursos&&($datos['tipo']!=='PERMISOS'||$inicio->diffInSeconds($fin)>86400))$errores[]='La edición delegada de Cursos dura como máximo 24 horas y se concede como tarea concreta.';
        $usuarios=User::with(['roles','permissions','roles.permissions','persona'])->whereIn('cod_usu',$datos['usuarios'])->get();
        if($usuarios->count()!==count($datos['usuarios']))$errores[]='Una de las cuentas seleccionadas ya no existe.';
        foreach($usuarios as $u){
            if($delegacionCursos&&app(RoleDashboardResolver::class)->roleFor($u)!=='Docente')$errores[]='La edición delegada de Cursos solo se concede a cuentas Docente vigentes.';
            if($u->is($actor))$errores[]='No puedes conceder accesos temporales a tu propia cuenta.';
            if(!app(RoleDashboardResolver::class)->roleFor($u))$errores[]='Cada destinatario necesita una cuenta activa con un único actor institucional.';
            if(count(array_intersect($nombres,$u->getAllPermissions()->pluck('name')->all())))$advertencias[]='Hay destinatarios que ya tienen parte de estos permisos de forma permanente; sus accesos actuales se conservan.';
        }
        if($this->disponible()&&$nombres){
            $duplicado=ConcesionAcceso::where('estado','AUTORIZADA')->where('inicio','<',$this->fechaConsulta($fin))->where('fin','>',$this->fechaConsulta($inicio))
                ->whereHas('usuarios',fn($q)=>$q->whereIn('users.cod_usu',$datos['usuarios']))
                ->whereHas('permisos',fn($q)=>$q->whereIn('permissions.name',$nombres))->exists();
            if($duplicado)$errores[]='Una cuenta ya tiene un acceso programado coincidente. Revisa las vigencias antes de duplicarlo.';
        }
        if(!app(NotificationService::class)->available())$errores[]='Habilita las notificaciones institucionales antes de programar accesos.';
        return ['valido'=>!$errores,'errores'=>array_values(array_unique($errores)),'advertencias'=>array_values(array_unique($advertencias)),
            'usuarios'=>$usuarios,'permisos'=>$permisos,'inicio'=>$inicio,'fin'=>$fin,'gestion'=>$g,'rol'=>$rol,'datos'=>$datos];
    }

    public function crear(User $actor,array $datos): ConcesionAcceso
    {
        $this->autorizar($actor);
        abort_unless($this->disponible()&&Schema::hasTable('bitacora'),409,'La preparación de la base de datos aún está pendiente.');
        validator($datos,['usuarios'=>'required|array|min:1|max:2000','usuarios.*'=>'required|string|distinct'])->validate();
        return DB::transaction(function()use($actor,$datos){
            // Mismo orden de bloqueo que la administración permanente de roles.
            app(RolePermissionService::class)->lockRoles();
            $usuarios=User::whereIn('cod_usu',array_merge($datos['usuarios']??[],[$actor->getKey()]))->orderBy('cod_usu')->lockForUpdate()->get();
            $operador=$usuarios->firstWhere('cod_usu',$actor->getKey());abort_unless($operador,403);
            $a=$this->analizar($operador,$datos);
            if(!$a['valido'])throw ValidationException::withMessages(['acceso'=>$a['errores']]);
            $c=ConcesionAcceso::create(['cod_gea'=>$a['gestion']->getKey(),'cod_usu_autorizador'=>$actor->getKey(),
                'role_id'=>$a['rol']?->getKey(),'tipo'=>$datos['tipo'],'motivo'=>$a['datos']['motivo'],'inicio'=>$a['inicio'],'fin'=>$a['fin'],'estado'=>'AUTORIZADA']);
            $c->usuarios()->attach($a['usuarios']->modelKeys());$c->permisos()->attach($a['permisos']->modelKeys());
            BitacoraService::registrar(accion:'PROGRAMAR_ACCESO',tabla:'concesion_acceso',registro:$c->getKey(),modulo:'Roles y Permisos',descripcion:$c->motivo,
                valoresNuevos:['inicio'=>$c->inicio->toIso8601String(),'fin'=>$c->fin->toIso8601String(),'usuarios'=>$a['usuarios']->modelKeys(),'permisos'=>$a['permisos']->pluck('name')->all()]);
            $this->avisar($c,'programado');
            $this->ventanas=[];
            return $c;
        },3);
    }

    public function revocar(User $actor,string $codigo,string $motivo,string $tipoMotivo='OTRO'): void
    {
        $this->autorizar($actor);$motivo=\App\Support\SupportRolesInstitucionales::motivo('revocacion',$tipoMotivo,$motivo);validator(compact('motivo'),['motivo'=>'required|string|min:20|max:2000'])->validate();
        abort_unless($this->disponible()&&Schema::hasTable('bitacora'),409);
        DB::transaction(function()use($actor,$codigo,$motivo){
            app(RolePermissionService::class)->lockRoles();$operador=User::lockForUpdate()->findOrFail($actor->getKey());$this->autorizar($operador);
            $c=ConcesionAcceso::lockForUpdate()->findOrFail($codigo);
            if($c->estado==='REVOCADA')return;
            $c->update(['estado'=>'REVOCADA','cod_usu_revocador'=>$actor->getKey(),'motivo_revocacion'=>$motivo,'revocada_en'=>now()]);
            BitacoraService::registrar(accion:'REVOCAR_ACCESO',tabla:'concesion_acceso',registro:$codigo,modulo:'Roles y Permisos',descripcion:$motivo);
            $this->avisar($c,'revocado');$this->ventanas=[];
        },3);
    }

    public function procesarAvisos(): int
    {
        if(!$this->disponible()||!app(NotificationService::class)->available())return 0;
        $total=0;
        ConcesionAcceso::where('estado','AUTORIZADA')->where('inicio','<=',$this->fechaConsulta(now()))
            ->where(fn($q)=>$q->whereNull('activada_en')->orWhere(fn($q)=>$q->where('fin','<=',$this->fechaConsulta(now()))->whereNull('finalizada_en')))
            ->orderBy('cod_cac')->chunkById(100,function($filas)use(&$total){foreach($filas as $fila){
                DB::transaction(function()use($fila,&$total){
                    $c=ConcesionAcceso::lockForUpdate()->findOrFail($fila->getKey());if($c->estado!=='AUTORIZADA')return;
                    if($c->fin<=now()){
                        if(!$c->finalizada_en){$this->avisar($c,'finalizado');$c->update(['finalizada_en'=>now(),'activada_en'=>$c->activada_en??now()]);$total++;}
                    }elseif(!$c->activada_en){$this->avisar($c,'vigente');$c->update(['activada_en'=>now()]);$total++;}
                });
            }},'cod_cac');
        return $total;
    }

    private function avisar(ConcesionAcceso $c,string $hecho): void
    {
        $c->loadMissing(['usuarios','permisos']);
        $detalle=\App\Support\ResumenAvisoAcceso::tareas($c->permisos->pluck('name'));
        $plazo=\App\Support\ResumenAvisoAcceso::plazo((int)$c->inicio->diffInSeconds($c->fin));
        $titulo=match($hecho){'programado'=>'Acceso programado','vigente'=>'Tu permiso ya está disponible','revocado'=>'Acceso revocado',default=>'Acceso finalizado'};
        $mensaje=match($hecho){
            'programado'=>"Tendrás permiso para: {$detalle}, durante {$plazo}.",
            'vigente'=>"Puedes realizar: {$detalle}. Acceso por {$plazo}.",
            'revocado'=>"Se retiró tu acceso para: {$detalle}.",
            default=>"Terminó tu acceso para: {$detalle}.",
        };
        // Cada aviso va únicamente a sus destinatarios; no incluye identidades de otros usuarios.
        $destinatarios=$c->usuarios->filter(fn($u)=>app(RoleDashboardResolver::class)->roleFor($u));
        if($destinatarios->isNotEmpty()){
            $aviso=app(NotificationService::class)->publicar(['clave_evento'=>'acceso:'.$c->getKey().':'.$hecho,'origen'=>'SISTEMA','tipo'=>'INFORMACION','titulo'=>$titulo,
                'mensaje'=>$mensaje,'cod_gea'=>$c->cod_gea,'cod_usu_emisor'=>$c->cod_usu_autorizador],$destinatarios);
            abort_unless($aviso,409,'No se pudo guardar el aviso privado; la operación de acceso no se aplicó.');
        }else abort_if($hecho==='programado',409,'No hay cuentas vigentes para recibir el aviso del acceso.');
        $c->loadMissing('usuarios.persona');
        $personas=$c->usuarios->map(fn($u)=>trim(implode(' ',array_filter([$u->persona?->nom_per,$u->persona?->ape_pat_per,$u->persona?->ape_mat_per])))?:'Cuenta institucional');
        $persona=mb_strimwidth($personas->first()??'Cuenta institucional',0,120,'…').($personas->count()>1?' y '.($personas->count()-1).' cuentas más':'');
        $operador=User::find($c->cod_usu_autorizador);
        if(!$operador||!app(RoleDashboardResolver::class)->roleFor($operador))return;
        $estado=match($hecho){'programado'=>'Permiso programado','vigente'=>'Permiso habilitado','revocado'=>'Permiso revocado',default=>'Permiso finalizado'};
        $mensajeOperador=match($hecho){
            'programado'=>"Autorizaste a {$persona}: {$detalle}, durante {$plazo}.",
            'vigente'=>"{$persona} ya puede realizar: {$detalle}, durante {$plazo}.",
            'revocado'=>"Se retiró el acceso de {$persona}: {$detalle}.",
            default=>"Terminó el acceso de {$persona}: {$detalle}.",
        };
        $avisoOperador=app(NotificationService::class)->publicar([
            'clave_evento'=>'acceso:'.$c->getKey().':'.$hecho.':operador','origen'=>'SISTEMA','tipo'=>'INFORMACION',
            'titulo'=>$estado,'mensaje'=>$mensajeOperador,
            'cod_gea'=>$c->cod_gea,'cod_usu_emisor'=>$c->cod_usu_autorizador,
        ],[$c->cod_usu_autorizador]);
        abort_unless($avisoOperador,409,'No se pudo guardar la confirmación del autorizador; la operación no se aplicó.');
    }
    private function etiqueta(string $p):string{return PermissionLabel::describe($p)['label'];}
    private function fechaConsulta(\Carbon\CarbonInterface $fecha): string
    {
        return DB::getDriverName()==='pgsql' ? $fecha->toIso8601String() : $fecha->format('Y-m-d H:i:s');
    }
}
