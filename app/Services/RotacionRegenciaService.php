<?php
namespace App\Services;

use App\Models\Oficial\Academico\{GestionAcademica,Curso,RegenteAsignacion,VinculoPersonal};
use App\Models\Oficial\Sistema\User;
use Illuminate\Support\Facades\DB;

class RotacionRegenciaService
{
    public static function siguientePar(array $grados): array
    {
        sort($grados);
        if (!in_array($grados,[[1,2],[3,4],[5,6]],true)) return [];
        return array_map(fn($grado)=>(($grado+1)%6)+1,$grados);
    }

    public function propuestas(int $anio): array
    {
        $anterior=GestionAcademica::where('ani_gea',$anio-1)->get();
        if ($anterior->count()!==1) return [];
        $g=$anterior->first();
        $cursos=Curso::where('est_cur','ACTIVO')->get();
        $numeros=$cursos->mapWithKeys(function($c){preg_match('/^([1-6])(?:ro|do|to|mo)?\b/iu',$c->nom_cur,$m);return isset($m[1])?[$c->cod_cur=>(int)$m[1]]:[];});
        $filas=RegenteAsignacion::with('vinculoPersonal.personalInstitucional.persona')->where('cod_gea',$g->cod_gea)->where('est_ras','ACTIVO')
            ->where('fii_ras','<=',$g->ffi_gea->toDateString())->where(fn($q)=>$q->whereNull('ffi_ras')->orWhere('ffi_ras','>=',$g->ffi_gea->toDateString()))->get();
        $propuestas=[];
        foreach($filas->groupBy('cod_vpe') as $codigo=>$asignaciones){
            $grados=$asignaciones->pluck('cod_cur')->unique()->map(fn($c)=>$numeros[$c]??0)->sort()->values()->all();
            $siguiente=self::siguientePar($grados);
            if(!$siguiente)continue;
            $destinos=[];
            foreach($siguiente as $n){$coincidencias=$numeros->filter(fn($valor)=>$valor===$n);if($coincidencias->count()!==1){$destinos=[];break;}$destinos[]=$cursos->firstWhere('cod_cur',$coincidencias->keys()->first());}
            if(count($destinos)===2)$propuestas[]=['vinculo'=>$asignaciones->first()->vinculoPersonal,'cursos'=>$destinos,'gestion'=>$anio];
        }
        return $propuestas;
    }

    public function aplicar(User $actor): int
    {
        abort_unless($actor->est_usu==='ACTIVO' && $actor->hasRole('Administrador') && $actor->can('regencia.asignaciones.gestionar'),403);
        return DB::transaction(function()use($actor){
            $activas=GestionAcademica::where('est_gea','ACTIVO')->lockForUpdate()->get();
            if($activas->count()!==1 || now()->toDateString()<$activas->first()->fii_gea->toDateString() || now()->toDateString()>$activas->first()->ffi_gea->toDateString())return 0;
            $gestion=$activas->first(); $nuevas=0;
            foreach($this->propuestas($gestion->ani_gea) as $propuesta){
                $v=VinculoPersonal::lockForUpdate()->find($propuesta['vinculo']->cod_vpe);
                // Una designación, incluso retirada, impide reinterpretar la decisión de Dirección.
                if(!$v || RegenteAsignacion::where('cod_gea',$gestion->cod_gea)->where('cod_vpe',$v->cod_vpe)->exists())continue;
                // La rotación automática no introduce responsabilidades compartidas por inferencia.
                if(RegenteAsignacion::where('cod_gea',$gestion->cod_gea)->whereIn('cod_cur',collect($propuesta['cursos'])->pluck('cod_cur'))->where('est_ras','ACTIVO')->exists())continue;
                $datos=['cod_vpe'=>$v->cod_vpe,'cod_gea'=>$gestion->cod_gea,'fii_ras'=>$gestion->fii_gea->toDateString(),'ffi_ras'=>$gestion->ffi_gea->toDateString(),
                    'obs_ras'=>'Rotación anual automática según la regla institucional confirmada el 05/10/2026: avance de dos grados y reinicio después de sexto. Aplicada porque no existe una designación para este regente en la gestión.'];
                foreach($propuesta['cursos'] as $curso){app(RegencyAccessService::class)->assign($actor,$datos+['cod_cur'=>$curso->cod_cur]);$nuevas++;}
            }
            return $nuevas;
        });
    }
}
