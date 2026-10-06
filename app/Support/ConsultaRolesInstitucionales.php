<?php
namespace App\Support;

use App\Models\Oficial\Sistema\{Role,User};
use Illuminate\Support\Facades\DB;

class ConsultaRolesInstitucionales
{
    public function resumen():array
    {
        $morph=(new User)->getMorphClass();
        $cuentas=DB::table('model_has_roles')->where('model_type',$morph)->select('role_id',DB::raw('COUNT(DISTINCT cod_usu) AS total'))->groupBy('role_id')->pluck('total','role_id');
        $roles=Role::where('guard_name','web')->with('permissions')->orderBy('id')->get()->each(function($r)use($cuentas){
            $r->setAttribute('users_count',(int)($cuentas[$r->id]??0));$r->setAttribute('permissions_count',$r->permissions->count());
            $r->setAttribute('descripcion',match($r->name){'Administrador'=>'Cuida las cuentas, la configuración y la seguridad institucional.',
                'Director'=>'Supervisa la actividad educativa y consulta los resultados de la institución.',
                'Secretaria'=>'Organiza registros, inscripciones y la atención administrativa.',
                'Regente'=>'Acompaña la asistencia y el seguimiento de estudiantes.',
                'Docente'=>'Trabaja con sus cursos, clases, actividades y evaluaciones.',
                'Estudiante'=>'Consulta sus materias, actividades y orientación vocacional.',
                default=>'Función complementaria aprobada con permisos específicos.'});
            $r->setAttribute('icono',match($r->name){'Administrador'=>'ph-shield-check','Director'=>'ph-buildings','Docente'=>'ph-chalkboard-teacher','Estudiante'=>'ph-student','Regente'=>'ph-user-focus','Secretaria'=>'ph-address-book',default=>'ph-users-three'});
        });
        return ['roles'=>$roles,'cuentas'=>DB::table('model_has_roles')->where('model_type',$morph)->distinct()->count('cod_usu')];
    }
}
