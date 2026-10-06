<?php
namespace App\Support\Comunidad;

use App\Models\Oficial\Academico\Docente;
use App\Services\BitacoraService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\ValidationException;

class PerfilEspecialidadDocente
{
    public function opciones(): array
    {
        return DB::table('asignatura')->where('est_asi','ACTIVO')->orderBy('nom_asi')->get(['cod_asi','nom_asi'])->map(fn($m)=>['valor'=>'materia:'.$m->cod_asi,'etiqueta'=>$m->nom_asi,'tipo'=>'Materia'])->concat(
            DB::table('especialidad_tecnica')->where('est_esp','ACTIVO')->orderBy('nom_esp')->get(['cod_esp','nom_esp'])->map(fn($e)=>['valor'=>'tecnica:'.$e->cod_esp,'etiqueta'=>$e->nom_esp,'tipo'=>'Especialidad técnica'])
        )->all();
    }

    public function interpretar(string $perfil, array $opciones): array
    {
        // Conserva todo fragmento histórico que no puede reconocerse sin ambigüedad.
        $seleccion=[];$otros=[];
        $normalizar=fn($t)=>\Illuminate\Support\Str::lower(\Illuminate\Support\Str::ascii(trim($t)));
        $exacto=collect($opciones)->first(fn($o)=>$normalizar($o['etiqueta'])===$normalizar($perfil));
        if($exacto)return ['seleccion'=>[$exacto['valor']],'otro'=>''];
        $reconocer=function($fragmento)use($opciones,$normalizar){
            return collect($opciones)->filter(function($o)use($fragmento,$normalizar){
                $referencias=[$o['etiqueta']];
                if($o['tipo']==='Materia')foreach(\App\Support\Academico\AsignaturaInteligente::catalogo() as $item)if($normalizar($item['nombre'])===$normalizar($o['etiqueta'])){$referencias=array_merge($referencias,$item['palabras_clave']);break;}
                return collect($referencias)->contains(fn($r)=>$normalizar($r)===$normalizar($fragmento));
            });
        };
        foreach(preg_split('/\s*[·;,]\s*/u',$perfil) as $fragmento){
            if(trim($fragmento)==='')continue;
            $candidatos=$reconocer($fragmento);
            if($candidatos->count()===1){$seleccion[]=$candidatos->first()['valor'];continue;}
            $partes=preg_split('/\s+\by\b\s+/iu',$fragmento);$ids=[];$reconocidas=true;
            foreach($partes as $parte){$candidatos=$reconocer($parte);if($candidatos->count()!==1){$reconocidas=false;break;}$ids[]=$candidatos->first()['valor'];}
            if($reconocidas)$seleccion=array_merge($seleccion,$ids);else $otros[]=trim($fragmento);
        }
        return ['seleccion'=>array_values(array_unique($seleccion)),'otro'=>implode(' · ',$otros)];
    }

    public function componer(array $seleccion,bool $otro,string $texto,array $opciones): string
    {
        $valores=collect($opciones)->keyBy('valor');
        if(count($seleccion)>30||collect($seleccion)->contains(fn($id)=>!is_string($id)||!$valores->has($id))||count(array_unique($seleccion))!==count($seleccion))throw ValidationException::withMessages(['seleccionEspecialidad'=>'Selecciona materias o especialidades disponibles en el catálogo.']);
        if($otro && (mb_strlen(trim($texto))<3 || mb_strlen(trim($texto))>150 || !preg_match('/[\p{L}]{3}/u',$texto)))throw ValidationException::withMessages(['otraEspecialidad'=>'Identifica la otra especialidad, por ejemplo Literatura comparada.']);
        $partes=collect($seleccion)->map(fn($id)=>$valores[$id]['etiqueta'])->all();
        if($otro)$partes[]=trim($texto);
        $perfil=implode(' · ',array_unique($partes));
        if($perfil===''||mb_strlen($perfil)>150)throw ValidationException::withMessages(['seleccionEspecialidad'=>$perfil===''?'Selecciona al menos una materia, especialidad u Otra.':'El perfil admite hasta 150 caracteres. Reduce la selección sin abreviar de forma ambigua.']);
        return $perfil;
    }

    public function guardar(string $codigo,array $seleccion,bool $otro,string $texto): Docente
    {
        Gate::authorize('Personal_Institucional');
        $perfil=$this->componer($seleccion,$otro,$texto,$this->opciones());
        return DB::transaction(function()use($codigo,$perfil,$seleccion,$otro,$texto){
            $docente=Docente::with('personalInstitucional.persona')->lockForUpdate()->findOrFail($codigo);
            if((int)$docente->num_mod_doc>=3)throw ValidationException::withMessages(['seleccionEspecialidad'=>'Se alcanzó el límite de tres modificaciones del perfil.']);
            if($docente->esp_doc===$perfil)return $docente;
            if(!Schema::hasTable('bitacora'))throw ValidationException::withMessages(['seleccionEspecialidad'=>'La bitácora no está disponible. El perfil se conserva sin modificar.']);
            $antes=$docente->getAttributes();
            $docente->update(['esp_doc'=>$perfil,'num_mod_doc'=>(int)$docente->num_mod_doc+1]);
            BitacoraService::registrar(accion:'EDITAR_DOCENTE',tabla:'docente',registro:$codigo,modulo:'Personal institucional',nombreRegistro:$docente->personalInstitucional?->persona?->nom_per??$codigo,descripcion:'Especialidades profesionales actualizadas desde el catálogo institucional.',nivel:'WARNING',valoresAnteriores:$antes,valoresNuevos:$docente->getAttributes()+['seleccion_catalogo'=>$seleccion,'otra_especialidad'=>$otro?$texto:null]);
            return $docente;
        });
    }
}
