<?php

namespace App\Http\Controllers;

use App\Models\Asignatura;
use App\Models\Bitacora;
use App\Models\Curso;
use App\Models\Docente;
use App\Models\EspecialidadTecnica;
use App\Models\Estudiante;
use App\Models\GestionAcademica;
use App\Models\InscripcionEstudiante;
use App\Models\Paralelo;
use App\Models\PeriodoEvaluacion;
use App\Models\Turno;
use App\Models\User;
use App\Services\RoleDashboardResolver;
use App\Support\InstitutionalRoleGovernance;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class AdminDashboardController extends Controller
{
    public function index()
    {
        $user = auth()->user();
        abort_unless($user && app(RoleDashboardResolver::class)->roleFor($user) === 'Administrador', 403);
        $persona = $user->persona;
        $nombreCompleto = trim(($persona?->nom_per ?? '').' '.($persona?->ape_pat_per ?? '').' '.($persona?->ape_mat_per ?? ''));
        $gestiones = GestionAcademica::whereIn('est_gea', ['ACTIVO', 'ACTIVA'])->limit(2)->get();
        $gestion = $gestiones->count() === 1 ? $gestiones->first() : null;
        $gestionActual = $gestion?->ani_gea ?? 'Sin gestión activa única';
        // El catálogo de periodos no contiene gestión ni fechas: no inferir un periodo actual.
        $periodoActual = PeriodoEvaluacion::where('est_pev', 'ACTIVO')->orderBy('ord_pev')->pluck('nom_pev')->implode(', ') ?: 'Sin periodos habilitados';
        $estadoSistema = 'Sesión activa';
        $resumen = [];
        $definitions = [
            ['Usuarios', 'Gestion_Usuarios', 'admin.gestion-usuarios', 'users', fn () => User::count(), 'Cuentas registradas.'],
            ['Estudiantes', 'Estudiantes', 'admin.gestion-estudiantes', 'academic-cap', fn () => Estudiante::where('est_est', 'ACTIVO')->count(), 'Perfiles estudiantiles activos.'],
            ['Docentes', 'Docentes', 'admin.gestion-docentes', 'user-group', fn () => Docente::where('est_doc', 'ACTIVO')->count(), 'Perfiles docentes activos.'],
            ['Inscripciones', 'Inscripciones', 'admin.gestion-inscripciones', 'clipboard-document', fn () => $gestion ? InscripcionEstudiante::where('cod_gea', $gestion->cod_gea)->where('est_ins', 'ACTIVA')->count() : null, 'Inscripciones activas en la gestión activa única.'],
            ['Especialidades', 'Especialidades_Tecnicas', 'admin.especialidades-tecnicas', 'wrench-screwdriver', fn () => EspecialidadTecnica::where('est_esp', 'ACTIVO')->count(), 'Especialidades habilitadas.'],
            ['Periodos', 'Periodo_Evaluacion', 'admin.periodo-evaluacion', 'calendar-days', fn () => PeriodoEvaluacion::where('est_pev', 'ACTIVO')->count(), 'Periodos habilitados en el catálogo.'],
        ];
        foreach ($definitions as [$label, $permission, $route, $icon, $count, $desc]) {
            if ($user->can($permission)) {
                $resumen[] = compact('label', 'route', 'icon', 'desc') + ['value' => $count() ?? 'Sin datos'];
            }
        }
        $actividadReciente = $user->can('Bitacora') ? Bitacora::with('usuario.persona')->orderByDesc('fec_bit')->limit(6)->get()->map(fn ($item) => [
            'titulo' => $item->acc_bit, 'detalle' => 'Acción registrada sobre '.$item->tab_bit,
            'fecha' => $item->fec_bit ? Carbon::parse($item->fec_bit)->diffForHumans() : 'Sin fecha',
            'icono' => '•', 'color' => 'ui-badge-info',
        ])->all() : [];
        $alertas = [];
        if ($user->can('Gestion_Usuarios')) {
            $alertas[] = ['titulo' => 'Cuentas sin actor institucional',
                'valor' => User::whereDoesntHave('roles', fn ($q) => $q->whereIn('name', app(InstitutionalRoleGovernance::class)->rolesInstitucionales()))->count(),
                'descripcion' => 'Incluye cuentas que solo tienen roles complementarios.', 'color' => 'ui-alert-warning'];
        }
        if ($user->can('Planes_Asignatura') && $gestion) {
            $alertas[] = ['titulo' => 'Docentes sin plan en la gestión activa',
                'valor' => Docente::where('est_doc', 'ACTIVO')->whereDoesntHave('planAsignaturas', fn ($q) => $q->where('cod_gea', $gestion->cod_gea))->count(),
                'descripcion' => 'Perfiles activos sin un plan asignado en esta gestión; requiere revisión humana.', 'color' => 'ui-alert-warning'];
        }
        $estructuraAcademica = [];
        foreach ([['Cursos', Curso::class, 'est_cur', 'Cursos'], ['Paralelos', Paralelo::class, 'est_par', 'Paralelos'], ['Turnos', Turno::class, 'est_tur', 'Turnos'], ['Asignaturas', Asignatura::class, 'est_asi', 'Asignaturas']] as [$label, $model, $state, $permission]) {
            if ($user->can($permission)) {
                $estructuraAcademica[] = ['label' => $label, 'value' => $model::where($state, 'ACTIVO')->count()];
            }
        }
        $chartRoles = $user->can('Gestion_Usuarios') ? DB::table('model_has_roles as mhr')->join('roles as r', 'r.id', '=', 'mhr.role_id')
            ->where('mhr.model_type', User::class)->where('r.guard_name', 'web')->whereIn('r.name', app(InstitutionalRoleGovernance::class)->rolesInstitucionales())
            ->select('r.name', DB::raw('COUNT(*) AS total'))->groupBy('r.name')->orderBy('r.name')->pluck('total', 'name')->all() : [];
        $chartEspecialidades = $user->can('Estudiantes') ? DB::table('estudiante as e')->join('especialidad_tecnica as et', 'et.cod_esp', '=', 'e.cod_esp')
            ->where('e.est_est', 'ACTIVO')->where('et.est_esp', 'ACTIVO')->select('et.nom_esp', DB::raw('COUNT(*) AS total'))
            ->groupBy('et.nom_esp')->orderByDesc('total')->limit(6)->pluck('total', 'nom_esp')->all() : [];
        $chartInscripciones = $user->can('Inscripciones') && $gestion ? DB::table('inscripcion_estudiante as ie')->join('curso as c', 'c.cod_cur', '=', 'ie.cod_cur')
            ->where('ie.est_ins', 'ACTIVA')->where('ie.cod_gea', $gestion->cod_gea)->select('c.nom_cur', DB::raw('COUNT(*) AS total'))
            ->groupBy('c.nom_cur')->orderBy('c.nom_cur')->pluck('total', 'nom_cur')->all() : [];

        return view('admin.dashboard-administrador', compact('nombreCompleto', 'resumen', 'gestionActual', 'periodoActual', 'estadoSistema', 'actividadReciente', 'alertas', 'estructuraAcademica', 'chartRoles', 'chartEspecialidades', 'chartInscripciones'));
    }
}
