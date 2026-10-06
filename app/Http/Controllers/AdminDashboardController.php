<?php

namespace App\Http\Controllers;

use App\Models\Oficial\Academico\Asignatura;
use App\Models\Oficial\Academico\Bitacora;
use App\Models\Oficial\Academico\Curso;
use App\Models\Oficial\Academico\Docente;
use App\Models\Oficial\Academico\EspecialidadTecnica;
use App\Models\Oficial\Academico\Estudiante;
use App\Models\Oficial\Academico\GestionAcademica;
use App\Models\Oficial\Academico\InscripcionEstudiante;
use App\Models\Oficial\Academico\Paralelo;
use App\Models\Oficial\Academico\Persona;
use App\Models\Oficial\Academico\PeriodoEvaluacion;
use App\Models\Oficial\Academico\Turno;
use App\Models\Oficial\Sistema\User;
use App\Services\RoleDashboardResolver;
use App\Support\Academico\PeriodoEvaluacionInteligente;
use App\Support\Academico\PanelGestionAcademica;
use App\Support\Bitacora\BitacoraInteligente;
use App\Support\InstitutionalRoleGovernance;
use Illuminate\Support\Facades\DB;

class AdminDashboardController extends Controller
{
    public function index(BitacoraInteligente $bitacora, PeriodoEvaluacionInteligente $periodos)
    {
        $user = auth()->user();
        abort_unless($user && app(RoleDashboardResolver::class)->roleFor($user) === 'Administrador', 403);
        $persona = $user->persona;
        $nombreCompleto = trim(($persona?->nom_per ?? '').' '.($persona?->ape_pat_per ?? '').' '.($persona?->ape_mat_per ?? ''));
        $gestiones = GestionAcademica::whereIn('est_gea', ['ACTIVO', 'ACTIVA'])->limit(2)->get();
        $gestion = $gestiones->count() === 1 ? $gestiones->first() : null;
        $gestionActual = $gestion?->ani_gea ?? 'Sin gestión activa única';
        $fechaControl = now('America/La_Paz');
        $periodoActual = $periodos->orientarPeriodoActual($gestion, PeriodoEvaluacion::where('est_pev', 'ACTIVO')->orderBy('ord_pev')->get(), $fechaControl);
        $estadoSistema = 'Sesión activa';
        $resumen = [];
        $definitions = [
            ['Usuarios', 'Gestion_Usuarios', 'admin.gestion-usuarios', 'users', fn () => User::count(), 'Cuentas de acceso al sistema; una persona registrada puede no tener cuenta.'],
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
        $actividadReciente = $user->can('Bitacora') ? Bitacora::with('usuario.persona')->orderByDesc('fec_bit')->orderByDesc('cod_bit')
            ->limit(6)->get()->map(fn ($item) => $bitacora->presentar($item))->all() : [];
        $alertas = [];
        if ($user->can('Gestion_Usuarios')) {
            $alertas[] = ['titulo' => 'Cuentas sin actor institucional',
                'valor' => User::whereDoesntHave('roles', fn ($q) => $q->whereIn('name', app(InstitutionalRoleGovernance::class)->rolesInstitucionales()))->count(),
                'descripcion' => 'Incluye cuentas que solo tienen roles complementarios.', 'color' => 'ui-alert-warning'];
        }
        if ($user->can('Planes_Asignatura') && $gestion) {
            $planesGestion = app(PanelGestionAcademica::class)->porGestion('plan_asignatura', $gestion->cod_gea);
            $alertas[] = ['titulo' => 'Docentes sin plan en la gestión activa',
                'valor' => $planesGestion ? Docente::where('est_doc', 'ACTIVO')->whereNotIn('cod_doc', $planesGestion->whereNotNull('cod_doc')->select('cod_doc'))->count() : 'No disponible',
                'descripcion' => 'Perfiles activos sin un plan asignado en esta gestión; requiere revisión humana.', 'color' => 'ui-alert-warning'];
        }
        $estructuraAcademica = [];
        foreach ([
            ['Cursos', Curso::class, 'est_cur', 'Cursos', 'admin.gestion-cursos', 'Niveles activos para organizar las inscripciones.', 'ph-graduation-cap'],
            ['Paralelos', Paralelo::class, 'est_par', 'Paralelos', 'admin.gestion-paralelos', 'Grupos disponibles en el catálogo institucional.', 'ph-users-three'],
            ['Turnos', Turno::class, 'est_tur', 'Turnos', 'admin.gestion-turnos', 'Turnos activos para la planificación de horarios.', 'ph-clock'],
            ['Asignaturas', Asignatura::class, 'est_asi', 'Asignaturas', 'admin.gestion-asignaturas', 'Materias activas en la oferta curricular.', 'ph-books'],
        ] as [$label, $model, $state, $permission, $route, $descripcion, $icono]) {
            if ($user->can($permission)) {
                $estructuraAcademica[] = compact('label', 'route', 'descripcion', 'icono') + ['value' => $model::where($state, 'ACTIVO')->count()];
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
        $chartTurnos = $user->can('Inscripciones') && $user->can('Turnos') && $gestion ? DB::table('inscripcion_estudiante as ie')->leftJoin('turno as t', 't.cod_tur', '=', 'ie.cod_tur')
            ->where('ie.est_ins', 'ACTIVA')->where('ie.cod_gea', $gestion->cod_gea)
            ->selectRaw("COALESCE(t.nom_tur, 'Sin turno asignado') AS nombre, COUNT(*) AS total")
            ->groupBy('t.nom_tur')->orderByDesc('total')->pluck('total', 'nombre')->all() : [];

        $resumenPersonas = $user->can('Gestion_Personas') ? ['total' => Persona::count(), 'con_cuenta' => Persona::whereHas('usuario')->count(),
            'sin_cuenta' => Persona::whereDoesntHave('usuario')->count(), 'activas' => Persona::where('est_per', true)->count()] : null;
        return view('admin.dashboard-administrador', compact('nombreCompleto', 'resumen', 'resumenPersonas', 'gestionActual', 'periodoActual', 'fechaControl', 'gestion', 'estadoSistema', 'actividadReciente', 'alertas', 'estructuraAcademica', 'chartRoles', 'chartEspecialidades', 'chartInscripciones', 'chartTurnos'));
    }
}
