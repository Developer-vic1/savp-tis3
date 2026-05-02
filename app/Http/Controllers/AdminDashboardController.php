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
use App\Models\PlanAsignatura;
use App\Models\Turno;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class AdminDashboardController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        $persona = $user->persona;

        $nombreCompleto = trim(
            ($persona->nom_per ?? '') . ' ' .
                ($persona->ape_pat_per ?? '') . ' ' .
                ($persona->ape_mat_per ?? '')
        );

        /*
        |--------------------------------------------------------------------------
        | RESUMEN PRINCIPAL
        |--------------------------------------------------------------------------
        */
        $resumen = [
            [
                'label' => 'Usuarios',
                'value' => User::count(),
                'desc' => 'Cuentas registradas en el sistema.',
                'icon' => 'users',
            ],
            [
                'label' => 'Estudiantes',
                'value' => Estudiante::where('est_est', 'ACTIVO')->count(),
                'desc' => 'Estudiantes activos registrados.',
                'icon' => 'academic-cap',
            ],
            [
                'label' => 'Docentes',
                'value' => Docente::where('est_doc', 'ACTIVO')->count(),
                'desc' => 'Docentes activos registrados.',
                'icon' => 'user-group',
            ],
            [
                'label' => 'Inscripciones',
                'value' => InscripcionEstudiante::where('est_ins', 'ACTIVO')->count(),
                'desc' => 'Inscripciones activas en la gestión.',
                'icon' => 'clipboard-document',
            ],
            [
                'label' => 'Especialidades',
                'value' => EspecialidadTecnica::where('est_esp', 'ACTIVO')->count(),
                'desc' => 'Especialidades técnicas habilitadas.',
                'icon' => 'wrench-screwdriver',
            ],
            [
                'label' => 'Periodos',
                'value' => PeriodoEvaluacion::count(),
                'desc' => 'Periodos evaluativos registrados.',
                'icon' => 'calendar-days',
            ],
        ];

        /*
        |--------------------------------------------------------------------------
        | GESTIÓN Y PERIODO ACTUAL
        |--------------------------------------------------------------------------
        | Ajusta estos campos si tus migraciones usan otros nombres.
        |--------------------------------------------------------------------------
        */
        $gestionActualModel = GestionAcademica::query()
            ->orderByDesc('created_at')
            ->first();

        $periodoActualModel = PeriodoEvaluacion::query()
            ->orderByDesc('created_at')
            ->first();

        $gestionActual = $gestionActualModel->ani_gea
            ?? $gestionActualModel->nombre
            ?? $gestionActualModel->gestion
            ?? 'No definido';

        $periodoActual = $periodoActualModel->nom_pev
            ?? $periodoActualModel->nom_per_eval
            ?? $periodoActualModel->nombre
            ?? 'No definido';

        $estadoSistema = 'Operativo';

        /*
        |--------------------------------------------------------------------------
        | ACTIVIDAD RECIENTE - SOLO DATOS REALES
        |--------------------------------------------------------------------------
        */
        $actividadReciente = Bitacora::with('usuario.persona')
            ->orderByDesc('fec_bit')
            ->limit(6)
            ->get()
            ->map(function ($item) {
                $accion = strtoupper((string) $item->acc_bit);
                $tabla = strtolower((string) $item->tab_bit);
                $persona = optional($item->usuario)->persona;

                $usuarioNombre = $persona
                    ? trim(
                        ($persona->nom_per ?? '') . ' ' .
                            ($persona->ape_pat_per ?? '')
                    )
                    : 'Sistema';

                if (str_contains($accion, 'CREAR') || str_contains($accion, 'INSERT')) {
                    $tipo = 'crear';
                } elseif (str_contains($accion, 'EDITAR') || str_contains($accion, 'UPDATE')) {
                    $tipo = 'editar';
                } elseif (str_contains($accion, 'ELIMINAR') || str_contains($accion, 'DELETE')) {
                    $tipo = 'eliminar';
                } else {
                    $tipo = 'general';
                }

                $config = match ($tipo) {
                    'crear' => [
                        'icono' => '🟢',
                        'color' => 'bg-emerald-50 text-emerald-700 ring-emerald-200',
                        'label' => 'Registro creado',
                    ],
                    'editar' => [
                        'icono' => '🟡',
                        'color' => 'bg-amber-50 text-amber-700 ring-amber-200',
                        'label' => 'Registro actualizado',
                    ],
                    'eliminar' => [
                        'icono' => '🔴',
                        'color' => 'bg-rose-50 text-rose-700 ring-rose-200',
                        'label' => 'Registro eliminado',
                    ],
                    default => [
                        'icono' => '📌',
                        'color' => 'bg-slate-50 text-slate-700 ring-slate-200',
                        'label' => 'Actividad registrada',
                    ],
                };

                $tablaFormateada = match ($tabla) {
                    'users' => 'usuario',
                    'persona' => 'persona',
                    'estudiante' => 'estudiante',
                    'docente' => 'docente',
                    'inscripcion_estudiante' => 'inscripción',
                    'curso' => 'curso',
                    'paralelo' => 'paralelo',
                    'turno' => 'turno',
                    'asignatura' => 'asignatura',
                    'especialidad_tecnica' => 'especialidad técnica',
                    'gestion_academica' => 'gestión académica',
                    'periodo_evaluacion' => 'periodo de evaluación',
                    default => $tabla ?: 'registro',
                };

                $detalle = "{$usuarioNombre} realizó una acción sobre {$tablaFormateada}";

                if (!empty($item->reg_bit)) {
                    $detalle .= " ({$item->reg_bit})";
                }

                return [
                    'titulo' => $config['label'],
                    'detalle' => $detalle,
                    'fecha' => Carbon::parse($item->fec_bit)->diffForHumans(),
                    'icono' => $config['icono'],
                    'color' => $config['color'],
                ];
            })
            ->toArray();

        /*
        |--------------------------------------------------------------------------
        | ALERTAS - SOLO DATOS REALES
        |--------------------------------------------------------------------------
        */

        $modelMorphKey = config('permission.column_names.model_morph_key', 'model_id');

        $usuariosSinRol = DB::table('users as u')
            ->leftJoin('model_has_roles as mhr', function ($join) use ($modelMorphKey) {
                $join->on('u.cod_usu', '=', "mhr.$modelMorphKey")
                    ->where('mhr.model_type', '=', User::class);
            })
            ->whereNull('mhr.role_id')
            ->count();

        $inscripcionesIncompletas = InscripcionEstudiante::query()
            ->whereNull('cod_est')
            ->orWhereNull('cod_cur')
            ->orWhereNull('cod_par')
            ->orWhereNull('cod_tur')
            ->orWhereNull('cod_gea')
            ->count();

        $planesPendientes = max(
            Docente::count() - PlanAsignatura::count(),
            0
        );

        $alertas = [
            [
                'titulo' => 'Usuarios sin rol asignado',
                'valor' => $usuariosSinRol,
                'descripcion' => 'Cuentas pendientes de asignación de rol dentro del sistema.',
                'color' => 'border-rose-100 bg-rose-50/70 text-rose-700 ring-rose-200',
            ],
            [
                'titulo' => 'Inscripciones incompletas',
                'valor' => $inscripcionesIncompletas,
                'descripcion' => 'Registros de inscripción con datos faltantes.',
                'color' => 'border-orange-100 bg-orange-50/70 text-orange-700 ring-orange-200',
            ],
            [
                'titulo' => 'Planes pendientes',
                'valor' => $planesPendientes,
                'descripcion' => 'Diferencia entre docentes activos y planes de asignatura registrados.',
                'color' => 'border-amber-100 bg-amber-50/70 text-amber-700 ring-amber-200',
            ],
        ];
        /*
        |--------------------------------------------------------------------------
        | ESTRUCTURA ACADÉMICA
        |--------------------------------------------------------------------------
        */
        $estructuraAcademica = [
            ['label' => 'Cursos', 'value' => Curso::where('est_cur', 'ACTIVO')->count()],
            ['label' => 'Paralelos', 'value' => Paralelo::where('est_par', 'ACTIVO')->count()],
            ['label' => 'Turnos', 'value' => Turno::where('est_tur', 'ACTIVO')->count()],
            ['label' => 'Asignaturas', 'value' => Asignatura::where('est_asi', 'ACTIVO')->count()],
            ['label' => 'Especialidades', 'value' => EspecialidadTecnica::where('est_esp', 'ACTIVO')->count()],
            ['label' => 'Planes de asignatura', 'value' => PlanAsignatura::count()],
        ];

        /*
        |--------------------------------------------------------------------------
        | GRÁFICO 1: USUARIOS POR ROL
        |--------------------------------------------------------------------------
        */
        $rolesConConteo = DB::table('model_has_roles as mhr')
            ->join('roles as r', 'r.id', '=', 'mhr.role_id')
            ->select('r.name', DB::raw('COUNT(*) as total'))
            ->groupBy('r.name')
            ->orderBy('r.name')
            ->get();

        $chartRoles = $rolesConConteo->pluck('total', 'name')->toArray();

        /*
        |--------------------------------------------------------------------------
        | GRÁFICO 2: ESTUDIANTES POR ESPECIALIDAD
        |--------------------------------------------------------------------------
        */
        $chartEspecialidades = DB::table('estudiante as e')
            ->join('especialidad_tecnica as et', 'et.cod_esp', '=', 'e.cod_esp')
            ->where('e.est_est', 'ACTIVO')
            ->where('et.est_esp', 'ACTIVO')
            ->select('et.nom_esp as nombre', DB::raw('COUNT(*) as total'))
            ->groupBy('et.nom_esp')
            ->orderByDesc('total')
            ->limit(6)
            ->pluck('total', 'nombre')
            ->toArray();

        /*
        |--------------------------------------------------------------------------
        | GRÁFICO 3: INSCRIPCIONES POR CURSO
        |--------------------------------------------------------------------------
        */
        $chartInscripciones = DB::table('inscripcion_estudiante as ie')
            ->join('curso as c', 'c.cod_cur', '=', 'ie.cod_cur')
            ->where('ie.est_ins', 'ACTIVO')
            ->where('c.est_cur', 'ACTIVO')
            ->select('c.nom_cur as nombre', DB::raw('COUNT(*) as total'))
            ->groupBy('c.nom_cur')
            ->orderBy('c.nom_cur')
            ->pluck('total', 'nombre')
            ->toArray();


        return view('admin.dashboard-administrador', compact(
            'nombreCompleto',
            'resumen',
            'gestionActual',
            'periodoActual',
            'estadoSistema',
            'actividadReciente',
            'alertas',
            'estructuraAcademica',
            'chartRoles',
            'chartEspecialidades',
            'chartInscripciones'
        ));
    }
}
