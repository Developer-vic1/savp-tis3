<?php
// Carga acotada e idempotente de la distribución confirmada por el usuario el 05/10/2026.
require __DIR__.'/../../vendor/autoload.php';
$app=require __DIR__.'/../../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();
use App\Models\Oficial\Academico\{CargoInstitucional,VinculoPersonal,PersonalInstitucional,GestionAcademica,Curso,RegenteAsignacion};
use App\Models\Oficial\Sistema\User;
use App\Services\{BitacoraService,RegencyAccessService,RolePermissionService};
use Illuminate\Support\Facades\{Auth,DB,Schema};
use Symfony\Component\Process\Process;

if (DB::connection()->getDatabaseName()!=='SAVPTIS3-OFICIAL') throw new RuntimeException('Destino inesperado.');
$distribucion=['PER_0004'=>[1,2],'PER_0008'=>[3,4],'PER_0007'=>[5,6]];
$identidades=['PER_0004'=>'Virginia Huañapaco Aruquipa','PER_0008'=>'Fanny Uriarte Gutierrez','PER_0007'=>'Sergio Valencia Molina'];
$gestiones=GestionAcademica::where('est_gea','ACTIVO')->where('ani_gea',2026)->get();
if($gestiones->count()!==1) throw new RuntimeException('Se requiere la gestión 2026 activa y única.');
$gestion=$gestiones->first();
$actores=User::role('Administrador')->where('est_usu','ACTIVO')->get();
if($actores->count()!==1 || !Schema::hasTable('bitacora')) throw new RuntimeException('Autoridad o bitácora no disponibles.');
$actor=$actores->first();
if(!$actor->can('usuarios.asignar_roles') || !$actor->can('regencia.asignaciones.gestionar')) throw new RuntimeException('La autoridad carece de los permisos requeridos.');
$cursos=Curso::where('est_cur','ACTIVO')->get();
$cursosPorGrado=[];
foreach(range(1,6) as $grado){$coincidencias=$cursos->filter(fn($c)=>preg_match('/^'.$grado.'(?:ro|do|to|mo)?\b/iu',$c->nom_cur));if($coincidencias->count()!==1) throw new RuntimeException('Grado ambiguo.');$cursosPorGrado[$grado]=$coincidencias->first();}
foreach($distribucion as $persona=>$grados){
    $usuarios=User::with('persona','roles')->where('cod_per',$persona)->where('est_usu','ACTIVO')->get();
    $u=$usuarios->sole();$p=$u->persona;
    if(trim($p->nom_per.' '.$p->ape_pat_per.' '.$p->ape_mat_per)!==$identidades[$persona] || $u->roles->pluck('name')->diff(['Regente'])->isNotEmpty()) throw new RuntimeException('Identidad o roles cambiaron; no se modifica la cuenta.');
    if(PersonalInstitucional::where('cod_per',$persona)->where('est_pin','ACTIVO')->count()!==1) throw new RuntimeException('Personal institucional ambiguo.');
}
if(!in_array('--aplicar',$argv,true)){echo "Comprobación correcta. Requiere --aplicar para guardar la carga autorizada.\n";exit;}
$config=config('database.connections.pgsql');
$ruta=storage_path('app/private/respaldos/regencia-'.now()->format('Ymd-His').'.dump');
if(!is_dir(dirname($ruta)))mkdir(dirname($ruta),0700,true);
$tablas=['cargo_institucional','vinculo_personal','regente_asignaciones','model_has_roles','bitacora'];
$args=['C:/Program Files/PostgreSQL/18/bin/pg_dump.exe','-h',$config['host'],'-p',(string)$config['port'],'-U',$config['username'],'-d',$config['database'],'-Fc','-f',$ruta];
foreach($tablas as $tabla)$args[]='--table='.$tabla;
$dump=new Process($args,null,['PGPASSWORD'=>$config['password']]);$dump->setTimeout(60)->mustRun();
$verificar=new Process(['C:/Program Files/PostgreSQL/18/bin/pg_restore.exe','--list',$ruta]);$verificar->mustRun();
foreach($tablas as $tabla)if(!str_contains($verificar->getOutput(),$tabla))throw new RuntimeException('Respaldo incompleto.');
Auth::setUser($actor);
DB::transaction(function()use($distribucion,$gestion,$cursosPorGrado,$actor){
    DB::select("select pg_advisory_xact_lock(hashtext('regencia-confirmada-2026'))");
    $cargo=CargoInstitucional::where('cla_cai','REGENTE')->lockForUpdate()->get();
    if($cargo->count()>1)throw new RuntimeException('Cargo de regencia ambiguo.');
    if($cargo->isEmpty()){
        $c=CargoInstitucional::create(['cla_cai'=>'REGENTE','nom_cai'=>'Regente','des_cai'=>'Acompañamiento institucional de grados por designación de Dirección.','est_cai'=>'ACTIVO']);
        BitacoraService::registrar(accion:'REGISTRAR_CARGO_REGENCIA',tabla:'cargo_institucional',registro:$c->cod_cai,modulo:'Regencia',valoresNuevos:$c->toArray());
    }else{$c=$cargo->first();if($c->est_cai!=='ACTIVO')throw new RuntimeException('El cargo requiere revisión.');}
    foreach($distribucion as $persona=>$grados){
        $u=User::where('cod_per',$persona)->where('est_usu','ACTIVO')->lockForUpdate()->sole();
        if(!$u->hasRole('Regente'))app(RolePermissionService::class)->assignActor($u,'Regente',$actor);
        $pin=PersonalInstitucional::where('cod_per',$persona)->where('est_pin','ACTIVO')->sole();
        $motivo='Designación de Dirección confirmada por el usuario solicitante el 05/10/2026 para acompañar los grados '.implode(' y ',$grados).' durante la gestión 2026. No se recibió referencia documental de la orden.';
        $vinculos=VinculoPersonal::where('cod_pin',$pin->cod_pin)->where('cod_cai',$c->cod_cai)->where('est_vpe','ACTIVO')->lockForUpdate()->get();
        if($vinculos->count()>1)throw new RuntimeException('Vínculos de regencia ambiguos.');
        $v=$vinculos->first();
        if(!$v){
            $v=VinculoPersonal::create(['cod_pin'=>$pin->cod_pin,'cod_cai'=>$c->cod_cai,'tip_vpe'=>'DESIGNACION','fii_vpe'=>$gestion->fii_gea->toDateString(),'ffi_vpe'=>null,'ref_vpe'=>null,'est_vpe'=>'ACTIVO','obs_vpe'=>$motivo]);
            BitacoraService::registrar(accion:'REGISTRAR_VINCULO_REGENCIA',tabla:'vinculo_personal',registro:$v->cod_vpe,modulo:'Regencia',descripcion:$motivo,valoresNuevos:$v->toArray());
        }
        foreach($grados as $grado){
            $datos=['cod_vpe'=>$v->cod_vpe,'cod_gea'=>$gestion->cod_gea,'cod_cur'=>$cursosPorGrado[$grado]->cod_cur,'fii_ras'=>$gestion->fii_gea->toDateString(),'ffi_ras'=>$gestion->ffi_gea->toDateString(),'obs_ras'=>$motivo];
            $existentes=RegenteAsignacion::where('cod_vpe',$v->cod_vpe)->where('cod_gea',$gestion->cod_gea)->where('cod_cur',$datos['cod_cur'])->get();
            if($existentes->isEmpty())app(RegencyAccessService::class)->assign($actor,$datos);
            elseif($existentes->count()!==1 || $existentes->first()->est_ras!=='ACTIVO' || $existentes->first()->fii_ras->toDateString()!==$datos['fii_ras'] || $existentes->first()->ffi_ras?->toDateString()!==$datos['ffi_ras'])throw new RuntimeException('La historia existente difiere; no se sobrescribe.');
        }
    }
});
echo 'Carga confirmada y auditada. Respaldo privado: '.basename($ruta).PHP_EOL;
