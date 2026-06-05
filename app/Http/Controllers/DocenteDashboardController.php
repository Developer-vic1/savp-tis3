<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Schema;

class DocenteDashboardController extends Controller
{
    public function index(Request $request)
    {
        /** @var User|null $user */
        $user = Auth::user();

        if (! $user) {
            return redirect()->route('login');
        }

        if (! $this->puedeAccederPanelDocente($user)) {
            abort(403, 'No tienes permisos para acceder al panel docente.');
        }

        $permisos = $this->obtenerPermisosDocente($user);

        return view('docente.dashboard-docente', [
            'user' => $user,
            'nombreCompleto' => $this->obtenerNombreCompleto($user),
            'rolActual' => $user->getRoleNames()->first() ?? 'Docente',
            'gestionActual' => $this->obtenerGestionActual(),
            'periodoActual' => $this->obtenerPeriodoActual(),
            'estadoSistema' => 'Operativo',

            'permisos' => $permisos,
            'resumen' => $this->obtenerResumenDocente($user, $permisos),
            'cardsSeleccion' => $this->obtenerCardsSeleccion($permisos),
            'seguimientoAcademico' => $this->obtenerSeguimientoAcademico($user),
            'alertas' => $this->obtenerAlertasDocente($permisos),
            'actividadReciente' => $this->obtenerActividadReciente($permisos),
            'estructuraDocente' => $this->obtenerEstructuraDocente($permisos),

            'chartPermisos' => $this->obtenerChartPermisos($permisos),
            'chartEstructuraAcademica' => $this->obtenerChartEstructuraAcademica(),
            'chartEvaluacion' => $this->obtenerChartEvaluacion(),
            'chartSeguimiento' => $this->obtenerChartSeguimiento(),
            'chartCargaDocente' => $this->obtenerChartCargaDocente($user),
        ]);
    }

    private function puedeAccederPanelDocente(User $user): bool
    {
        if (method_exists($user, 'hasRole') && $user->hasRole('Docente')) {
            return true;
        }

        if (method_exists($user, 'can') && $user->can('Panel_Docente')) {
            return true;
        }

        return false;
    }

    private function obtenerPermisosDocente(User $user): Collection
    {
        $permisos = [
            'Panel_Docente' => [
                'label' => 'Panel docente',
                'descripcion' => 'Acceso al panel institucional del docente.',
                'grupo' => 'Principal',
                'prioridad' => 1,
            ],
            'Estudiantes' => [
                'label' => 'Estudiantes',
                'descripcion' => 'Consulta y seguimiento académico de estudiantes.',
                'grupo' => 'Comunidad educativa',
                'prioridad' => 2,
            ],
            'Cursos' => [
                'label' => 'Cursos',
                'descripcion' => 'Consulta de cursos disponibles o asignados.',
                'grupo' => 'Académico',
                'prioridad' => 3,
            ],
            'Paralelos' => [
                'label' => 'Paralelos',
                'descripcion' => 'Consulta de paralelos vinculados a la estructura académica.',
                'grupo' => 'Académico',
                'prioridad' => 4,
            ],
            'Asignaturas' => [
                'label' => 'Asignaturas',
                'descripcion' => 'Consulta y revisión de asignaturas institucionales.',
                'grupo' => 'Académico',
                'prioridad' => 5,
            ],
            'Especialidades_Tecnicas' => [
                'label' => 'Especialidades técnicas',
                'descripcion' => 'Consulta de especialidades técnicas del bachillerato técnico humanístico.',
                'grupo' => 'Académico',
                'prioridad' => 6,
            ],
            'Planes_Asignatura' => [
                'label' => 'Planes de asignatura',
                'descripcion' => 'Planificación académica por asignatura.',
                'grupo' => 'Planificación',
                'prioridad' => 7,
            ],
            'Periodo_Evaluacion' => [
                'label' => 'Periodo de evaluación',
                'descripcion' => 'Consulta del periodo académico y evaluativo activo.',
                'grupo' => 'Evaluación',
                'prioridad' => 8,
            ],
            'Calificaciones' => [
                'label' => 'Calificaciones',
                'descripcion' => 'Gestión y seguimiento de calificaciones académicas.',
                'grupo' => 'Evaluación',
                'prioridad' => 9,
            ],
            'Reportes_Academicos' => [
                'label' => 'Reportes académicos',
                'descripcion' => 'Consulta de reportes de rendimiento y seguimiento académico.',
                'grupo' => 'Reportes',
                'prioridad' => 10,
            ],
            'Mi_Perfil' => [
                'label' => 'Mi perfil',
                'descripcion' => 'Consulta de información personal e institucional del usuario.',
                'grupo' => 'Cuenta',
                'prioridad' => 11,
            ],
            'Acceso_Aula_Virtual' => [
                'label' => 'Aula Virtual',
                'descripcion' => 'Acceso al entorno LMS para cursos, actividades y seguimiento virtual.',
                'grupo' => 'Aula Virtual',
                'prioridad' => 12,
            ],
        ];

        return collect($permisos)
            ->map(function (array $data, string $permiso) use ($user) {
                return [
                    'permiso' => $permiso,
                    'label' => $data['label'],
                    'descripcion' => $data['descripcion'],
                    'grupo' => $data['grupo'],
                    'prioridad' => $data['prioridad'],
                    'habilitado' => method_exists($user, 'can') ? $user->can($permiso) : false,
                ];
            })
            ->sortBy('prioridad')
            ->values();
    }

    private function obtenerResumenDocente(User $user, Collection $permisos): array
    {
        return [
            [
                'label' => 'Permisos activos',
                'value' => $permisos->where('habilitado', true)->count(),
                'desc' => 'Permisos docentes habilitados para el panel institucional.',
                'icon' => 'shield',
                'tone' => 'primary',
            ],
            [
                'label' => 'Cursos',
                'value' => $this->contarTablaSiExiste(['cursos', 'curso']),
                'desc' => 'Cursos configurados en la estructura académica.',
                'icon' => 'book',
                'tone' => 'info',
            ],
            [
                'label' => 'Paralelos',
                'value' => $this->contarTablaSiExiste(['paralelos', 'paralelo']),
                'desc' => 'Paralelos registrados para organización académica.',
                'icon' => 'layers',
                'tone' => 'violet',
            ],
            [
                'label' => 'Asignaturas',
                'value' => $this->contarTablaSiExiste(['asignaturas', 'asignatura']),
                'desc' => 'Asignaturas disponibles para seguimiento docente.',
                'icon' => 'document',
                'tone' => 'warning',
            ],
            [
                'label' => 'Estudiantes',
                'value' => $this->contarTablaSiExiste(['estudiantes', 'estudiante']),
                'desc' => 'Estudiantes registrados en el sistema académico.',
                'icon' => 'users',
                'tone' => 'primary',
            ],
            [
                'label' => 'Calificaciones',
                'value' => $this->contarTablaSiExiste(['calificaciones', 'calificacion', 'calificacion_tarea']),
                'desc' => 'Registros de evaluación académica disponibles.',
                'icon' => 'chart',
                'tone' => 'info',
            ],
        ];
    }

    private function obtenerCardsSeleccion(Collection $permisos): array
    {
        $cards = [
            'Estudiantes' => [
                'titulo' => 'Seguimiento de estudiantes',
                'subtitulo' => 'Comunidad educativa',
                'descripcion' => 'Consulta estudiantes, revisa su estado académico y prepara seguimiento por curso o asignatura.',
                'icono' => 'students',
                'tono' => 'primary',
                'route' => Route::has('admin.gestion-estudiantes') ? route('admin.gestion-estudiantes') : '#',
                'estado' => 'Disponible',
            ],
            'Cursos' => [
                'titulo' => 'Cursos',
                'subtitulo' => 'Estructura académica',
                'descripcion' => 'Revisa los cursos disponibles y la estructura académica asociada al trabajo docente.',
                'icono' => 'courses',
                'tono' => 'info',
                'route' => Route::has('admin.gestion-cursos') ? route('admin.gestion-cursos') : '#',
                'estado' => 'Disponible',
            ],
            'Paralelos' => [
                'titulo' => 'Paralelos',
                'subtitulo' => 'Organización académica',
                'descripcion' => 'Consulta la distribución por paralelos para comprender la carga académica institucional.',
                'icono' => 'parallel',
                'tono' => 'violet',
                'route' => Route::has('admin.gestion-paralelos') ? route('admin.gestion-paralelos') : '#',
                'estado' => 'Disponible',
            ],
            'Asignaturas' => [
                'titulo' => 'Asignaturas',
                'subtitulo' => 'Plan académico',
                'descripcion' => 'Accede a las asignaturas registradas para revisión y seguimiento académico.',
                'icono' => 'subjects',
                'tono' => 'warning',
                'route' => Route::has('admin.gestion-asignaturas') ? route('admin.gestion-asignaturas') : '#',
                'estado' => 'Disponible',
            ],
            'Especialidades_Tecnicas' => [
                'titulo' => 'Especialidades técnicas',
                'subtitulo' => 'BTH',
                'descripcion' => 'Consulta las áreas técnicas disponibles dentro del bachillerato técnico humanístico.',
                'icono' => 'tools',
                'tono' => 'primary',
                'route' => '#',
                'estado' => 'Próximamente',
            ],
            'Planes_Asignatura' => [
                'titulo' => 'Planes de asignatura',
                'subtitulo' => 'Planificación docente',
                'descripcion' => 'Espacio para planificación, organización de contenidos y seguimiento por asignatura.',
                'icono' => 'plan',
                'tono' => 'info',
                'route' => '#',
                'estado' => 'Próximamente',
            ],
            'Periodo_Evaluacion' => [
                'titulo' => 'Periodo de evaluación',
                'subtitulo' => 'Evaluación académica',
                'descripcion' => 'Consulta el periodo activo y organiza el seguimiento de evaluaciones.',
                'icono' => 'calendar',
                'tono' => 'violet',
                'route' => '#',
                'estado' => 'Próximamente',
            ],
            'Calificaciones' => [
                'titulo' => 'Calificaciones',
                'subtitulo' => 'Evaluación',
                'descripcion' => 'Consulta o registra calificaciones según la configuración académica disponible.',
                'icono' => 'grades',
                'tono' => 'warning',
                'route' => '#',
                'estado' => 'Próximamente',
            ],
            'Reportes_Academicos' => [
                'titulo' => 'Reportes académicos',
                'subtitulo' => 'Evidencia y análisis',
                'descripcion' => 'Visualiza reportes de rendimiento, asistencia, calificaciones y seguimiento.',
                'icono' => 'reports',
                'tono' => 'primary',
                'route' => '#',
                'estado' => 'Próximamente',
            ],
            'Acceso_Aula_Virtual' => [
                'titulo' => 'Aula Virtual',
                'subtitulo' => 'LMS institucional',
                'descripcion' => 'Accede al entorno virtual para cursos, actividades, materiales, calificaciones y seguimiento digital.',
                'icono' => 'lms',
                'tono' => 'info',
                'route' => Route::has('aula-virtual.inicio') ? route('aula-virtual.inicio') : '#',
                'estado' => 'LMS',
            ],
        ];

        return collect($cards)
            ->filter(function ($card, string $permiso) use ($permisos) {
                return (bool) optional($permisos->firstWhere('permiso', $permiso))['habilitado'];
            })
            ->values()
            ->toArray();
    }

    private function obtenerSeguimientoAcademico(User $user): array
    {
        return [
            [
                'titulo' => 'Cursos y paralelos',
                'descripcion' => 'Base estructural para organizar el seguimiento académico del docente.',
                'valor' => $this->contarTablaSiExiste(['cursos', 'curso']) . ' / ' . $this->contarTablaSiExiste(['paralelos', 'paralelo']),
                'estado' => 'Estructura',
                'tono' => 'primary',
            ],
            [
                'titulo' => 'Asignaturas disponibles',
                'descripcion' => 'Asignaturas registradas que podrán asociarse al seguimiento docente.',
                'valor' => $this->contarTablaSiExiste(['asignaturas', 'asignatura']),
                'estado' => 'Académico',
                'tono' => 'info',
            ],
            [
                'titulo' => 'Estudiantes registrados',
                'descripcion' => 'Estudiantes disponibles para procesos de consulta y seguimiento académico.',
                'valor' => $this->contarTablaSiExiste(['estudiantes', 'estudiante']),
                'estado' => 'Comunidad',
                'tono' => 'violet',
            ],
            [
                'titulo' => 'Evaluación académica',
                'descripcion' => 'Estado base para calificaciones, periodos y reportes académicos.',
                'valor' => $this->contarTablaSiExiste(['calificaciones', 'calificacion', 'calificacion_tarea']),
                'estado' => 'Evaluación',
                'tono' => 'warning',
            ],
        ];
    }

    private function obtenerAlertasDocente(Collection $permisos): array
    {
        $alertas = [];

        if (! optional($permisos->firstWhere('permiso', 'Panel_Docente'))['habilitado']) {
            $alertas[] = [
                'titulo' => 'Panel docente no habilitado',
                'descripcion' => 'El usuario no tiene el permiso Panel_Docente. Revisa el rol asignado en el seeder.',
                'valor' => '!',
                'tipo' => 'danger',
            ];
        }

        if (! optional($permisos->firstWhere('permiso', 'Cursos'))['habilitado']) {
            $alertas[] = [
                'titulo' => 'Cursos no disponibles',
                'descripcion' => 'El docente no tiene permiso para consultar cursos.',
                'valor' => '0',
                'tipo' => 'warning',
            ];
        }

        if (! optional($permisos->firstWhere('permiso', 'Calificaciones'))['habilitado']) {
            $alertas[] = [
                'titulo' => 'Calificaciones no habilitadas',
                'descripcion' => 'El docente no podrá revisar o registrar calificaciones hasta que se active el permiso.',
                'valor' => '0',
                'tipo' => 'warning',
            ];
        }

        if (empty($alertas)) {
            $alertas[] = [
                'titulo' => 'Permisos docentes correctos',
                'descripcion' => 'El rol docente cuenta con permisos suficientes para operar su panel institucional.',
                'valor' => 'OK',
                'tipo' => 'success',
            ];
        }

        if (! optional($permisos->firstWhere('permiso', 'Acceso_Aula_Virtual'))['habilitado']) {
            $alertas[] = [
                'titulo' => 'Aula Virtual no habilitada',
                'descripcion' => 'El acceso al LMS es independiente. Si el docente debe usar Aula Virtual, asigna Acceso_Aula_Virtual.',
                'valor' => 'LMS',
                'tipo' => 'info',
            ];
        }

        return $alertas;
    }

    private function obtenerActividadReciente(Collection $permisos): array
    {
        return [
            [
                'titulo' => 'Panel docente cargado',
                'detalle' => 'El sistema reconoció el rol docente y habilitó el dashboard institucional correspondiente.',
                'fecha' => now()->format('d/m/Y H:i'),
                'icono' => '✅',
                'color' => 'bg-emerald-50 text-emerald-700 ring-emerald-200 dark:bg-emerald-400/10 dark:text-emerald-300 dark:ring-emerald-400/20',
            ],
            [
                'titulo' => 'Permisos activos detectados',
                'detalle' => 'Se detectaron ' . $permisos->where('habilitado', true)->count() . ' permisos docentes activos.',
                'fecha' => now()->format('d/m/Y'),
                'icono' => '🛡️',
                'color' => 'bg-sky-50 text-sky-700 ring-sky-200 dark:bg-sky-400/10 dark:text-sky-300 dark:ring-sky-400/20',
            ],
            [
                'titulo' => 'Seguimiento académico preparado',
                'detalle' => 'Los datos de estudiantes, cursos, asignaturas y calificaciones alimentarán los reportes institucionales.',
                'fecha' => now()->format('d/m/Y'),
                'icono' => '📌',
                'color' => 'bg-violet-50 text-violet-700 ring-violet-200 dark:bg-violet-400/10 dark:text-violet-300 dark:ring-violet-400/20',
            ],
        ];
    }

    private function obtenerEstructuraDocente(Collection $permisos): array
    {
        return $permisos
            ->groupBy('grupo')
            ->map(function (Collection $items, string $grupo) {
                return [
                    'grupo' => $grupo,
                    'total' => $items->count(),
                    'habilitados' => $items->where('habilitado', true)->count(),
                    'pendientes' => $items->where('habilitado', false)->count(),
                    'porcentaje' => $items->count() > 0
                        ? round(($items->where('habilitado', true)->count() / $items->count()) * 100)
                        : 0,
                ];
            })
            ->values()
            ->toArray();
    }

    private function obtenerChartPermisos(Collection $permisos): array
    {
        return [
            'Habilitados' => $permisos->where('habilitado', true)->count(),
            'Pendientes' => $permisos->where('habilitado', false)->count(),
        ];
    }

    private function obtenerChartEstructuraAcademica(): array
    {
        return [
            'Cursos' => $this->contarTablaSiExiste(['cursos', 'curso']),
            'Paralelos' => $this->contarTablaSiExiste(['paralelos', 'paralelo']),
            'Asignaturas' => $this->contarTablaSiExiste(['asignaturas', 'asignatura']),
            'Especialidades' => $this->contarTablaSiExiste(['especialidades_tecnicas', 'especialidad_tecnica']),
        ];
    }

    private function obtenerChartEvaluacion(): array
    {
        return [
            'Periodos' => $this->contarTablaSiExiste(['periodo_evaluacion', 'periodos_evaluacion']),
            'Calificaciones' => $this->contarTablaSiExiste(['calificaciones', 'calificacion', 'calificacion_tarea']),
            'Reportes' => $this->contarTablaSiExiste(['reportes_academicos', 'reporte_academico']),
        ];
    }

    private function obtenerChartSeguimiento(): array
    {
        return [
            'Estudiantes' => $this->contarTablaSiExiste(['estudiantes', 'estudiante']),
            'Inscripciones' => $this->contarTablaSiExiste(['inscripciones', 'inscripcion']),
            'Cursos' => $this->contarTablaSiExiste(['cursos', 'curso']),
        ];
    }

    private function obtenerChartCargaDocente(User $user): array
    {
        return [
            'Cursos' => $this->contarTablaSiExiste(['cursos', 'curso']),
            'Asignaturas' => $this->contarTablaSiExiste(['asignaturas', 'asignatura']),
            'Estudiantes' => $this->contarTablaSiExiste(['estudiantes', 'estudiante']),
            'Calificaciones' => $this->contarTablaSiExiste(['calificaciones', 'calificacion', 'calificacion_tarea']),
        ];
    }

    private function obtenerNombreCompleto(User $user): string
    {
        try {
            if (method_exists($user, 'persona') && $user->persona) {
                $persona = $user->persona;

                $camposNombreCompleto = [
                    'nombre_completo',
                    'nom_completo',
                    'nombreCompleto',
                ];

                foreach ($camposNombreCompleto as $campo) {
                    if (! empty($persona->{$campo})) {
                        return trim($persona->{$campo});
                    }
                }

                $partes = [
                    $persona->nombres ?? null,
                    $persona->nombre ?? null,
                    $persona->primer_nombre ?? null,
                    $persona->segundo_nombre ?? null,
                    $persona->ap_paterno ?? null,
                    $persona->apellido_paterno ?? null,
                    $persona->ap_materno ?? null,
                    $persona->apellido_materno ?? null,
                ];

                $nombre = collect($partes)
                    ->filter()
                    ->implode(' ');

                if (trim($nombre) !== '') {
                    return trim($nombre);
                }
            }

            if (! empty($user->name)) {
                return trim($user->name);
            }

            return $user->email ?? 'Docente';
        } catch (\Throwable $e) {
            report($e);

            return $user->email ?? 'Docente';
        }
    }

    private function obtenerGestionActual(): string
    {
        try {
            $tabla = $this->primeraTablaExistente([
                'gestion_academica',
                'gestiones_academicas',
            ]);

            if (! $tabla) {
                return 'Gestión no configurada';
            }

            $query = DB::table($tabla);

            foreach (['estado', 'est_gac', 'activo'] as $columnaEstado) {
                if (Schema::hasColumn($tabla, $columnaEstado)) {
                    $query->where($columnaEstado, true);
                    break;
                }
            }

            foreach (['created_at', 'id', 'cod_gestion', 'cod_gac'] as $columnaOrden) {
                if (Schema::hasColumn($tabla, $columnaOrden)) {
                    $query->orderByDesc($columnaOrden);
                    break;
                }
            }

            $gestion = $query->first();

            if (! $gestion) {
                return 'Gestión no configurada';
            }

            return $gestion->nombre
                ?? $gestion->gestion
                ?? $gestion->nom_gac
                ?? $gestion->anio
                ?? 'Gestión actual';
        } catch (\Throwable $e) {
            report($e);

            return 'Gestión no configurada';
        }
    }

    private function obtenerPeriodoActual(): string
    {
        try {
            $tabla = $this->primeraTablaExistente([
                'periodo_evaluacion',
                'periodos_evaluacion',
            ]);

            if (! $tabla) {
                return 'Periodo no configurado';
            }

            $query = DB::table($tabla);

            foreach (['estado', 'est_per', 'activo'] as $columnaEstado) {
                if (Schema::hasColumn($tabla, $columnaEstado)) {
                    $query->where($columnaEstado, true);
                    break;
                }
            }

            foreach (['created_at', 'id', 'cod_periodo', 'cod_per'] as $columnaOrden) {
                if (Schema::hasColumn($tabla, $columnaOrden)) {
                    $query->orderByDesc($columnaOrden);
                    break;
                }
            }

            $periodo = $query->first();

            if (! $periodo) {
                return 'Periodo no configurado';
            }

            return $periodo->nombre
                ?? $periodo->periodo
                ?? $periodo->nom_per
                ?? 'Periodo activo';
        } catch (\Throwable $e) {
            report($e);

            return 'Periodo no configurado';
        }
    }

    private function contarTablaSiExiste(array $tablas): int
    {
        try {
            $tabla = $this->primeraTablaExistente($tablas);

            if (! $tabla) {
                return 0;
            }

            return (int) DB::table($tabla)->count();
        } catch (\Throwable $e) {
            report($e);

            return 0;
        }
    }

    private function primeraTablaExistente(array $tablas): ?string
    {
        foreach ($tablas as $tabla) {
            if (Schema::hasTable($tabla)) {
                return $tabla;
            }
        }

        return null;
    }
}