<?php

namespace App\Services;

use App\Models\Oficial\Academico\IncorporacionCurricular;
use App\Models\Oficial\Sistema\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class IncorporacionCurricularService
{
    public function programar(string $dominio, array $datos, array $revision, object $pdf): IncorporacionCurricular
    {
        Gate::authorize($dominio==='asignatura'?'Asignaturas':'Especialidades_Tecnicas');
        abort_unless(auth()->user()?->hasRole('Administrador'),403);
        if(!($revision['coherente']??false) || empty($revision['firma_sello_comprobados_por']) || !hash_equals($revision['sha256']??'',hash_file('sha256',$pdf->getRealPath()))) throw ValidationException::withMessages(['documentoCurricular'=>'Falta una lectura y comprobación válida del respaldo actual.']);
        $nombre=$datos[$dominio==='asignatura'?'nom_asi':'nom_esp'];
        if(!Schema::hasTable('incorporacion_curricular') || !Schema::hasTable('bitacora')) throw ValidationException::withMessages(['documentoCurricular'=>'Falta habilitar el registro de incorporaciones mediante la migración incremental. No se guardó ni activó la oferta.']);
        if(!app(NotificationService::class)->available()) throw ValidationException::withMessages(['documentoCurricular'=>'Las notificaciones institucionales no están disponibles. No se guardó la incorporación.']);
        $id=(string)Str::uuid(); $ruta=$pdf->storeAs('curricular', $id.'.pdf','local');
        if (!$ruta || !Storage::disk('local')->exists($ruta)) throw ValidationException::withMessages(['documentoCurricular'=>'No se pudo conservar el PDF. No se guardó la incorporación.']);
        try {
            return DB::transaction(function()use($dominio,$datos,$revision,$nombre,$id,$ruta){
                $gestion=DB::table('gestion_academica')->where('est_gea','ACTIVO')->lockForUpdate()->get();
                if($gestion->count()!==1 || (int)$revision['datos']['gestion'] !== (int)$gestion->first()->ani_gea+1) throw ValidationException::withMessages(['documentoCurricular'=>'La incorporación debe corresponder a la siguiente gestión activa.']);
                if(IncorporacionCurricular::where('dominio',$dominio)->where('gestion',$revision['datos']['gestion'])->whereRaw('LOWER(nombre) = ?', [mb_strtolower($nombre)])->exists()) throw ValidationException::withMessages(['documentoCurricular'=>'Esta incorporación ya está programada.']);
                $r=IncorporacionCurricular::create(['id'=>$id,'dominio'=>$dominio,'nombre'=>$nombre,'gestion'=>(int)$revision['datos']['gestion'],'datos'=>$datos,'evidencia'=>$revision,'ruta_pdf'=>$ruta,'sha256'=>$revision['sha256'],'cod_usu'=>auth()->id(),'estado'=>'PROGRAMADA']);
                BitacoraService::registrar(accion:'PROGRAMAR_INCORPORACION_CURRICULAR',tabla:'incorporacion_curricular',registro:$id,modulo:'Catálogo académico',nombreRegistro:$nombre,descripcion:'Incorporación documentada para gestión '.$r->gestion,nivel:'SUCCESS',valoresNuevos:$r->toArray());
                // Aviso público de la oferta: sin documento, firma, nombre de autoridad ni datos personales.
                app(NotificationService::class)->publicar(['clave_evento'=>'incorporacion:'.$id,'origen'=>'ADMINISTRATIVO','tipo'=>'INFORMACION','titulo'=>'Nueva oferta para gestión '.$r->gestion,
                    'mensaje'=>($dominio==='asignatura'?'La materia ':'La especialidad ').$nombre.' está programada para incorporarse en la gestión '.$r->gestion.'. La oferta actual se conserva.','cod_usu_emisor'=>auth()->id()],User::where('est_usu','ACTIVO')->pluck('cod_usu'));
                return $r;
            });
        } catch(\Throwable $e) { Storage::disk('local')->delete($ruta); throw $e; }
    }

    public function aplicar(string $id, string $dominio): void
    {
        Gate::authorize($dominio==='asignatura'?'Asignaturas':'Especialidades_Tecnicas');
        abort_unless(auth()->user()?->hasRole('Administrador'),403);
        DB::transaction(function()use($id,$dominio){
            $r=IncorporacionCurricular::where('dominio',$dominio)->lockForUpdate()->findOrFail($id);
            $gestiones=DB::table('gestion_academica')->where('est_gea','ACTIVO')->lockForUpdate()->get();
            if($r->estado!=='PROGRAMADA' || $gestiones->count()!==1 || (int)$gestiones->first()->ani_gea!==$r->gestion) throw ValidationException::withMessages(['incorporacion'=>'Solo puede aplicarse cuando su gestión sea la única gestión activa.']);
            if(!Schema::hasTable('bitacora') || !Storage::disk('local')->exists($r->ruta_pdf) || !hash_equals($r->sha256,hash_file('sha256',Storage::disk('local')->path($r->ruta_pdf)))) throw ValidationException::withMessages(['incorporacion'=>'El respaldo o la bitácora no están disponibles. Se conserva la programación sin aplicar.']);
            $datos=$r->datos;
            if($dominio==='asignatura'){
                $existentes=\App\Models\Oficial\Academico\Asignatura::get()->map(fn($m)=>['nombre'=>$m->nom_asi,'sigla'=>$m->sig_asi])->all();
                $analisis=\App\Support\Academico\AsignaturaInteligente::interpretar($datos['nom_asi'],$existentes);
                if(!($analisis['valido']??false) || ($analisis['duplicado']??false)) throw ValidationException::withMessages(['incorporacion'=>'La materia cambió o ya existe un equivalente. Revisa el catálogo.']);
                $registro=\App\Models\Oficial\Academico\Asignatura::create(array_intersect_key($datos,array_flip(['nom_asi','sig_asi','hor_asi']))+['est_asi'=>'ACTIVO']);
            }else{
                $analisis=app(\App\Support\Academico\EspecialidadTecnicaInteligente::class)->analizar($datos);
                if(!$analisis['puede_guardar']) throw ValidationException::withMessages(['incorporacion'=>implode(' ',$analisis['bloqueos'])]);
                $registro=\App\Models\Oficial\Academico\EspecialidadTecnica::create(array_intersect_key($datos,array_flip(['nom_esp','des_esp']))+['est_esp'=>'ACTIVO']);
            }
            $r->update(['estado'=>'APLICADA','registro'=>$registro->getKey()]);
            BitacoraService::registrar(accion:'APLICAR_INCORPORACION_CURRICULAR',tabla:'incorporacion_curricular',registro:$r->id,modulo:'Catálogo académico',nombreRegistro:$r->nombre,descripcion:'Oferta incorporada en su gestión autorizada.',nivel:'SUCCESS',valoresNuevos:['registro'=>$registro->toArray(),'programacion'=>$r->toArray()]);
        });
    }
}
