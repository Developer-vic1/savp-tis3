<?php
namespace App\Services;

use App\Models\Oficial\Sistema\{SolicitudRol,Role,User};
use App\Models\Oficial\Academico\GestionAcademica;
use App\Support\InstitutionalRoleGovernance;
use App\Support\SupportRolesInstitucionales as SupportRol;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\{DB,Schema,Storage};
use Illuminate\Validation\ValidationException;
use Spatie\Permission\Models\Permission;

class RoleRequestService
{
    public function __construct(private InstitutionalRoleGovernance $governance,private InstitutionalAuthorityService $authority,private InstitutionalDocumentAnalyzer $analyzer){}
    public function authorize(User $actor,string $permission): void
    {
        if((new RoleDashboardResolver)->roleFor($actor)!=='Administrador'||!$actor->can('roles-permisos.gestionar')||!$actor->can($permission))throw new AuthorizationException('No tienes autorización para esta operación.');
    }
    public function disponible():bool{return Schema::hasTable('solicitud_rol')&&Schema::hasTable('solicitud_rol_permiso');}
    private function preparar():void
    {
        if(!$this->disponible()||!Schema::hasTable('bitacora'))throw ValidationException::withMessages(['role_request'=>'Falta aplicar la migración de gobernanza revisada. Tus datos de consulta se conservan.']);
    }
    public function analyze(array $data):array
    {
        return $this->governance->analyze($data['requested_name'],$data['justification'],$data['functions'],$data['requested_permissions'],
            Role::where('guard_name','web')->pluck('name')->all(),Permission::where('guard_name','web')->pluck('name')->all());
    }
    private function contexto():array
    {
        $gestiones=GestionAcademica::where('est_gea','ACTIVO')->get();$a=$this->authority->current();
        if($gestiones->count()!==1||$a['status']!=='ACTIVO')throw ValidationException::withMessages(['role_request'=>'Necesitamos una gestión activa única y un Director activo identificado para revisar la carta.']);
        return [$gestiones->first(),$a];
    }
    private function validarDatos(array $data):array
    {
        validator($data,['requested_name'=>'required|string|max:80','motivo_tipo'=>'required|string','institutional_reason'=>'nullable|string|max:2000','responsabilidades'=>'required|array|max:7','responsabilidades.*'=>'string'])->validate();
        SupportRol::comprobarTexto((string)($data['requested_name']??''),'requested_name',4,true);
        $data['institutional_reason']=SupportRol::motivo('solicitud',(string)($data['motivo_tipo']??''),(string)($data['institutional_reason']??''),'institutional_reason');
        $data['justification']=$data['institutional_reason'];
        $data['functions']=SupportRol::funciones($data['responsabilidades']??[]);
        return validator($data,[
            'motivo_tipo'=>['required',\Illuminate\Validation\Rule::in(array_keys(SupportRol::MOTIVOS['solicitud']))],
            'responsabilidades'=>'required|array|min:1|max:7','responsabilidades.*'=>['required','string','distinct',\Illuminate\Validation\Rule::in(array_keys(SupportRol::RESPONSABILIDADES))],
            'requested_name'=>'required|string|min:4|max:80','justification'=>'required|string|min:30|max:2000',
            'institutional_reason'=>'required|string|min:20|max:2000','functions'=>'required|string|min:30|max:3000',
            'scope'=>['required',\Illuminate\Validation\Rule::in(SupportRol::AMBITOS)],'observations'=>'nullable|string|max:2000',
            'requested_permissions'=>'required|array|min:1|max:150','requested_permissions.*'=>'string|distinct',
        ])->validate();
    }
    public function comprobarDocumento(User $actor,array $data,UploadedFile $file):array
    {
        $this->authorize($actor,'roles.solicitudes.crear');$data=$this->validarDatos($data);
        validator(['document'=>$file],['document'=>'required|file|max:8192|mimes:pdf|extensions:pdf'])->validate();
        [$g,$a]=$this->contexto();
        return $this->analyzer->leerAutorizacion($file->getRealPath(),$data['requested_name'],$a['name'],$g->ani_gea);
    }
    public function submit(User $actor,array $data,UploadedFile $file):SolicitudRol
    {
        $this->authorize($actor,'roles.solicitudes.crear');$this->preparar();$data=$this->validarDatos($data);
        $lectura=$this->comprobarDocumento($actor,$data,$file);$ruta=null;
        try {
            return DB::transaction(function()use($actor,$data,$file,$lectura,&$ruta){
                app(RolePermissionService::class)->lockRoles();$operador=User::lockForUpdate()->findOrFail($actor->getKey());$this->authorize($operador,'roles.solicitudes.crear');
                [$g,$a]=$this->contexto();$analisis=$this->analyze($data);
                if($analisis['reasons']||$analisis['blocked_permissions'])throw ValidationException::withMessages(['role_request'=>$analisis['summary']]);
                foreach($data['requested_permissions'] as $p)if(!$operador->getAllPermissions()->contains('name',$p))throw ValidationException::withMessages(['role_request'=>'No puedes solicitar autoridad superior a tus permisos permanentes.']);
                $ruta=$file->store('gobernanza/cartas','local');
                if(!$ruta)throw ValidationException::withMessages(['document'=>'No pudimos guardar la evidencia. Vuelve a intentarlo.']);
                $s=SolicitudRol::create(['cod_gea'=>$g->getKey(),'cod_usu_solicitante'=>$actor->getKey(),'cod_usu_director'=>$a['director']->persona->usuario->getKey(),
                    'nombre'=>$data['requested_name'],'justificacion'=>$data['justification'],'motivo'=>$data['institutional_reason'],'funciones'=>$data['functions'],
                    'alcance'=>$data['scope'],'observaciones'=>$data['observations']??null,'documento_ruta'=>$ruta,'documento_sha256'=>$lectura['sha256'],
                    'analisis'=>['gobernanza'=>$analisis,'documento'=>$lectura],'estado'=>$lectura['coherente']?'PENDIENTE':'RECHAZADA','nota_revision'=>$lectura['coherente']?null:implode(' ',$lectura['errores'])]);
                $s->permisos()->attach(Permission::where('guard_name','web')->whereIn('name',$data['requested_permissions'])->pluck('id'));
                BitacoraService::registrar(accion:$lectura['coherente']?'SOLICITAR_ROL':'SOLICITUD_ROL_PDF_RECHAZADO',tabla:'solicitud_rol',registro:$s->getKey(),modulo:'Roles y Permisos',descripcion:$s->motivo,
                    resultado:$lectura['coherente']?'EXITOSO':'BLOQUEADO',valoresNuevos:['sha256'=>$lectura['sha256'],'analisis'=>$lectura,'nombre'=>$s->nombre]);
                return $s;
            },3);
        }catch(\Throwable $e){if($ruta)Storage::disk('local')->delete($ruta);throw $e;}
    }
    public function cancel(User $actor,string $id):void
    {
        $this->authorize($actor,'roles.solicitudes.cancelar');$this->preparar();
        DB::transaction(function()use($actor,$id){
            app(RolePermissionService::class)->lockRoles();
            $operador=User::lockForUpdate()->findOrFail($actor->getKey());$this->authorize($operador,'roles.solicitudes.cancelar');
            $s=SolicitudRol::lockForUpdate()->findOrFail($id);abort_unless($s->cod_usu_solicitante===$actor->getKey()&&in_array($s->estado,['PENDIENTE','REVISADA'],true),403);
            $s->update(['estado'=>'CANCELADA']);BitacoraService::registrar(accion:'CANCELAR_SOLICITUD_ROL',tabla:'solicitud_rol',registro:$id,modulo:'Roles y Permisos',descripcion:'El solicitante canceló su petición. Se conserva la carta.');
        });
    }
    public function review(User $actor,string $id,bool $approved,string $note,bool $directorMatches,bool $readable,bool $signaturePresent,bool $sealPresent):void
    {
        $this->authorize($actor,'roles.solicitudes.analizar');$this->preparar();validator(['nota'=>$note],['nota'=>'required|string|min:20|max:2000'])->validate();
        SupportRol::comprobarTexto($note,'nota');
        DB::transaction(function()use($actor,$id,$approved,$note,$directorMatches,$readable,$signaturePresent,$sealPresent){
            app(RolePermissionService::class)->lockRoles();
            $operador=User::lockForUpdate()->findOrFail($actor->getKey());$this->authorize($operador,'roles.solicitudes.analizar');
            $s=SolicitudRol::lockForUpdate()->findOrFail($id);abort_unless($s->estado==='PENDIENTE'&&$s->cod_usu_solicitante!==$actor->getKey(),403);
            if($approved){if(!$directorMatches||!$readable||!$signaturePresent||!$sealPresent)throw ValidationException::withMessages(['role_request'=>'Verifica la carta, el Director, la firma y su autenticidad antes de aprobar.']);$this->revalidarDocumento($s);}
            $s->update(['estado'=>$approved?'REVISADA':'RECHAZADA','cod_usu_revisor'=>$actor->getKey(),'nota_revision'=>$note,'revisada_en'=>now()]);
            BitacoraService::registrar(accion:$approved?'REVISAR_SOLICITUD_ROL':'RECHAZAR_SOLICITUD_ROL',tabla:'solicitud_rol',registro:$id,modulo:'Roles y Permisos',descripcion:$note);
        });
    }
    private function revalidarDocumento(SolicitudRol $s):void
    {
        [$g,$a]=$this->contexto();
        if($g->getKey()!==$s->cod_gea||$a['director']->persona->usuario->getKey()!==$s->cod_usu_director)throw ValidationException::withMessages(['role_request'=>'La gestión o el Director cambió. Presenta una carta vigente.']);
        if(!Storage::disk('local')->exists($s->documento_ruta)||hash_file('sha256',Storage::disk('local')->path($s->documento_ruta))!==$s->documento_sha256)throw ValidationException::withMessages(['document'=>'La evidencia no coincide con la carta presentada.']);
        $lectura=$this->analyzer->leerAutorizacion(Storage::disk('local')->path($s->documento_ruta),$s->nombre,$a['name'],$g->ani_gea);
        if(!$lectura['coherente'])throw ValidationException::withMessages(['document'=>$lectura['errores']]);
    }
    public function createRole(User $actor,string $id):Role
    {
        $this->authorize($actor,'roles.crear');$this->preparar();
        return DB::transaction(function()use($actor,$id){
            app(RolePermissionService::class)->lockRoles();$operador=User::lockForUpdate()->findOrFail($actor->getKey());$this->authorize($operador,'roles.crear');
            $s=SolicitudRol::with('permisos')->lockForUpdate()->findOrFail($id);abort_unless($s->estado==='REVISADA'&&$s->cod_usu_revisor&&$s->cod_usu_solicitante!==$s->cod_usu_revisor,409);
            $this->revalidarDocumento($s);$r=$this->analyze(['requested_name'=>$s->nombre,'justification'=>$s->justificacion,'functions'=>$s->funciones,'requested_permissions'=>$s->permisos->pluck('name')->all()]);
            if($r['reasons']||$r['blocked_permissions'])throw ValidationException::withMessages(['role_request'=>$r['summary']]);
            $role=app(RolePermissionService::class)->createApprovedRole($s->nombre,$s->permisos->pluck('name')->all(),$operador);
            $s->update(['estado'=>'CREADA','role_id'=>$role->getKey(),'creada_en'=>now()]);
            BitacoraService::registrar(accion:'CREAR_ROL_RESPALDADO',tabla:'solicitud_rol',registro:$id,modulo:'Roles y Permisos',descripcion:$s->motivo,valoresNuevos:['role_id'=>$role->getKey(),'revisor'=>$s->cod_usu_revisor,'sha256'=>$s->documento_sha256]);return $role;
        },3);
    }
}
