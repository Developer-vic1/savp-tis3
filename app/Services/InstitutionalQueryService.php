<?php

namespace App\Services;

use App\Models\Oficial\Academico\AsistenciaEstudiante;
use App\Models\Oficial\Academico\Calificacion;
use App\Models\Oficial\Academico\Curso;
use App\Models\Oficial\Academico\Docente;
use App\Models\Oficial\Academico\Estudiante;
use App\Models\Oficial\Academico\GestionAcademica;
use App\Models\Oficial\Academico\GrupoAcademico;
use App\Models\Oficial\Academico\InscripcionEstudiante;
use App\Models\Oficial\Academico\Paralelo;
use App\Models\Oficial\Academico\PlantillaHoraria;
use App\Models\Oficial\Academico\ReporteGenerado;
use App\Models\Oficial\Academico\Turno;
use App\Models\Oficial\AporteAcademicoVocacional\OrientacionResultado;
use App\Models\Oficial\AulaVirtual\ClaseVirtual;
use App\Models\Oficial\Sistema\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

class InstitutionalQueryService
{
    public const AREAS = [
        'gestion' => ['Gestión académica', GestionAcademica::class, 'Gestion_Academica', ['ani_gea' => 'Gestión', 'fii_gea' => 'Inicio', 'ffi_gea' => 'Fin', 'est_gea' => 'Estado'], []],
        'lms' => ['Aula Virtual institucional', ClaseVirtual::class, 'cursos.ver.institucional', ['nom_cla' => 'Aula', 'planAsignatura.asignatura.nom_asi' => 'Asignatura', 'planAsignatura.curso.nom_cur' => 'Curso', 'est_cla' => 'Estado', 'materiales_count' => 'Materiales publicados', 'tareas_count' => 'Tareas activas'], ['planAsignatura.asignatura', 'planAsignatura.curso']],
        'orientacion' => ['Orientación registrada', OrientacionResultado::class, 'orientacion.ver.institucional', ['estudiante.persona.nom_per' => 'Estudiante', 'perfil_predominante' => 'Perfil registrado', 'estado' => 'Estado', 'created_at' => 'Fecha'], ['estudiante.persona']],
        'estudiantes' => ['Estudiantes', Estudiante::class, 'estudiantes.ver.institucional', ['cod_est' => 'Código', 'persona.nom_per' => 'Nombre', 'persona.ape_pat_per' => 'Apellido', 'est_est' => 'Estado'], ['persona']],
        'docentes' => ['Docentes', Docente::class, 'Docentes', ['cod_doc' => 'Código', 'personalInstitucional.persona.nom_per' => 'Nombre', 'personalInstitucional.persona.ape_pat_per' => 'Apellido', 'est_doc' => 'Estado'], ['personalInstitucional.persona']],
        'cursos' => ['Cursos', Curso::class, 'cursos.ver.institucional', ['cod_cur' => 'Código', 'nom_cur' => 'Curso', 'niv_cur' => 'Nivel', 'est_cur' => 'Estado'], []],
        'turnos' => ['Turnos', Turno::class, 'Turnos', ['cod_tur' => 'Código', 'nom_tur' => 'Turno', 'est_tur' => 'Estado'], []],
        'inscripciones' => ['Inscripciones', InscripcionEstudiante::class, 'inscripciones.ver.institucional', ['cod_ins' => 'Código', 'estudiante.persona.nom_per' => 'Estudiante', 'curso.nom_cur' => 'Curso', 'gestionAcademica.ani_gea' => 'Gestión', 'est_ins' => 'Estado'], ['estudiante.persona', 'curso', 'gestionAcademica']],
        'rendimiento' => ['Rendimiento registrado', Calificacion::class, 'calificaciones.ver.institucional', ['cod_cal' => 'Código', 'estudiante.persona.nom_per' => 'Estudiante', 'asignatura.nom_asi' => 'Asignatura', 'periodoEvaluacion.nom_pev' => 'Periodo', 'not_cal' => 'Nota'], ['estudiante.persona', 'asignatura', 'periodoEvaluacion']],
        'asistencia' => ['Asistencia registrada', AsistenciaEstudiante::class, 'asistencia.ver.institucional', ['cod_asi_est' => 'Código', 'estudiante.persona.nom_per' => 'Estudiante', 'estadoAsistencia.nom_est_asi' => 'Asistencia', 'fec_reg_asi_est' => 'Registro'], ['estudiante.persona', 'estadoAsistencia']],
        'reportes' => ['Reportes generados', ReporteGenerado::class, 'reportes.ver.institucional', ['codigo' => 'Código', 'tipo_reporte' => 'Tipo', 'formato' => 'Formato', 'estado' => 'Estado', 'created_at' => 'Fecha'], []],
    ];

    public function authorizeQuery(User $user, string $area, string $workspace): void
    {
        abort_unless(isset(self::AREAS[$area]), 404);
        $actor = ['admin' => 'Administrador', 'direccion' => 'Director', 'regencia' => 'Regente', 'secretaria' => 'Secretaria'][$workspace] ?? null;
        abort_unless($actor && app(RoleDashboardResolver::class)->roleFor($user) === $actor, 403);
        if ($workspace === 'secretaria') {
            abort_unless(in_array($area, ['gestion', 'cursos', 'turnos'], true), 403);
        }
        if ($workspace === 'regencia') {
            abort_unless(in_array($area, ['estudiantes', 'cursos', 'inscripciones', 'rendimiento', 'asistencia', 'lms'], true), 403);
        }
        $permission = $workspace === 'secretaria' && $area === 'cursos' ? 'Cursos' : self::AREAS[$area][2];
        if ($workspace === 'admin') {
            $permission = match ($area) {
                'cursos', 'lms' => 'cursos.ver.global',
                'estudiantes' => 'estudiantes.ver.global',
                'rendimiento' => 'calificaciones.ver.global',
                default => $permission,
            };
        }
        abort_unless($user->can($permission), 403);
    }

    public function search(Request $request, string $area, ?string $workspace = null): array
    {
        abort_unless(isset(self::AREAS[$area]), 404);
        [$title, $model, $permission, $columns, $relations] = self::AREAS[$area];
        $workspace ??= $request->routeIs('regencia.*') ? 'regencia' : ($request->routeIs('admin.*') ? 'admin' : ($request->routeIs('secretaria.*') ? 'secretaria' : 'direccion'));
        $this->authorizeQuery($request->user(), $area, $workspace);
        $regency = $workspace === 'regencia';
        $data = $request->validate([
            'search' => ['nullable', 'string', 'max:100'],
            'gestion' => ['nullable', 'string', 'max:30'],
            'curso' => ['nullable', 'string', 'max:30'],
            'estudiante' => ['nullable', 'string', 'max:30'],
            'nivel' => ['nullable', 'string', 'max:50'],
            'paralelo' => ['nullable', 'string', 'max:20'],
            'turno' => ['nullable', 'string', 'max:20'],
            'estado' => ['nullable', 'in:ACTIVO,INACTIVO'],
            'desde' => ['nullable', 'date_format:H:i'],
            'hasta' => ['nullable', 'date_format:H:i', 'after:desde'],
        ]);
        $search = trim($data['search'] ?? '');
        $key = (new $model)->getKeyName();
        $query = $model::query()->with($relations);
        if ($area === 'reportes') {
            $query = app(HistoricalReportAccessService::class)->query($request->user());
        }
        if ($area === 'lms') {
            $query->withCount(['materiales' => fn ($q) => $q->where('est_mat', 'ACTIVO'), 'tareas' => fn ($q) => $q->where('est_tar', 'PUBLICADA')]);
        }
        if ($area === 'rendimiento') {
            $query->with('planAsignatura.gestionAcademica', 'planAsignatura.curso');
            $columns = ['planAsignatura.gestionAcademica.ani_gea' => 'Gestión', 'planAsignatura.curso.nom_cur' => 'Curso'] + $columns;
        }
        $gestion = $data['gestion'] ?? '';
        if ($gestion !== '') {
            match ($area) {
                'estudiantes' => $query->whereHas('inscripciones', fn ($q) => $q->where('cod_gea', $gestion)),
                'inscripciones' => $query->where('cod_gea', $gestion),
                'cursos' => null, // Se correlaciona con paralelo/turno en el mismo plan más abajo.
                'rendimiento', 'lms' => $this->filtrarPlanes($query, fn ($q) => $q->deGestion($gestion)),
                'asistencia' => $this->filtrarPlanes($query, fn ($q) => $q->deGestion($gestion), true),
                default => null,
            };
        }
        $catalogFilters = collect($data)->only(['nivel', 'paralelo', 'turno', 'estado', 'desde', 'hasta'])->all();
        if ($area === 'cursos') {
            $this->constrainCourseCatalog($query, $data, $regency ? $request->user() : null);
        } elseif ($area === 'turnos') {
            $this->constrainShiftCatalog($query, $data);
        }
        $course = $data['curso'] ?? '';
        if ($course !== '') {
            match ($area) {
                'cursos' => $query->where('cod_cur', $course),
                'estudiantes' => $query->whereHas('inscripciones', fn ($q) => $q->where('cod_cur', $course)->when($gestion !== '', fn ($p) => $p->where('cod_gea', $gestion))),
                'inscripciones' => $query->where('cod_cur', $course),
                'rendimiento', 'lms' => $this->filtrarPlanes($query, fn ($q) => $q->deCurso($course)),
                'asistencia' => $this->filtrarPlanes($query, fn ($q) => $q->deCurso($course), true),
                default => null,
            };
        }
        $studentFilter = $data['estudiante'] ?? '';
        if ($studentFilter !== '' && in_array($area, ['estudiantes', 'inscripciones', 'rendimiento', 'asistencia', 'orientacion'], true)) {
            $area === 'rendimiento' ? $query->deEstudiante($studentFilter) : $query->where('cod_est', $studentFilter);
        }
        if ($regency) {
            if ($area === 'estudiantes') {
                $query->where('est_est', 'ACTIVO');
            }
            if ($area === 'inscripciones') {
                $query->where('est_ins', 'ACTIVA');
            }
            $access = app(RegencyAccessService::class);
            $scope = fn ($q) => $access->constrain($q, $request->user(), $q->getModel()->getTable())->when($gestion !== '', fn ($p) => $p->deGestion($gestion));
            match ($area) {
                'estudiantes' => $query->whereHas('inscripciones', fn ($q) => $access->constrain($q, $request->user(), 'inscripcion_estudiante')->where('est_ins', 'ACTIVA')->when($gestion !== '', fn ($p) => $p->where('cod_gea', $gestion))->when($course !== '', fn ($p) => $p->where('cod_cur', $course))),
                'cursos' => null, // constrainCourseCatalog ya aplica el scope al plan filtrado.
                'inscripciones' => $access->constrain($query, $request->user(), 'inscripcion_estudiante'),
                'rendimiento', 'lms' => $this->filtrarPlanes($query, $scope),
                'asistencia' => $this->filtrarPlanes($query, $scope, true),
                default => abort(403),
            };
        }
        $rows = $query
            ->when($search !== '', function ($query) use ($key, $search, $area) {
                $query->where(function ($q) use ($key, $search, $area) {
                    $q->whereRaw('CAST('.$q->getQuery()->getGrammar()->wrap($key).' AS VARCHAR) LIKE ?', ['%'.$search.'%']);
                    $person = match ($area) {
                        'estudiantes' => 'persona', 'docentes' => 'personalInstitucional.persona',
                        'inscripciones', 'rendimiento', 'asistencia', 'orientacion' => 'estudiante.persona', default => null,
                    };
                    if ($person) {
                        $q->orWhereHas($person, fn ($p) => $p->where('nom_per', 'like', '%'.$search.'%')->orWhere('ape_pat_per', 'like', '%'.$search.'%'));
                    }
                    if ($area === 'cursos') {
                        $q->orWhere('nom_cur', 'like', '%'.$search.'%');
                    }
                    if ($area === 'turnos') {
                        $q->orWhere('nom_tur', 'like', '%'.$search.'%');
                    }
                });
            })
            ->orderByDesc($key)->paginate(20)->withQueryString();

        $dashboardRoute = $workspace.'.dashboard';
        $yearsQuery = GestionAcademica::orderByDesc('ani_gea');
        $coursesQuery = Curso::orderBy('nom_cur');
        $coursesQuery->when($gestion !== '', fn ($q) => $q->whereHas('planesAsignatura', fn ($p) => $p->deGestion($gestion)));
        if ($regency) {
            $access = app(RegencyAccessService::class);
            $yearsQuery->whereHas('planesAsignatura', fn ($q) => $access->constrain($q, $request->user(), 'plan_asignatura'));
            $coursesQuery->whereHas('planesAsignatura', fn ($q) => $access->constrain($q, $request->user(), 'plan_asignatura')->when($gestion !== '', fn ($p) => $p->deGestion($gestion)));
        }
        $years = in_array($area, ['estudiantes', 'cursos', 'inscripciones', 'rendimiento', 'asistencia', 'lms'], true) ? $yearsQuery->get() : collect();
        $courses = in_array($area, ['estudiantes', 'cursos', 'inscripciones', 'rendimiento', 'asistencia', 'lms'], true) ? $coursesQuery->get() : collect();
        $levels = $parallels = $shifts = collect();
        if ($workspace === 'secretaria' && $area === 'cursos') {
            $levels = Curso::whereNotNull('niv_cur')->where('niv_cur', '!=', '')->distinct()->orderBy('niv_cur')->pluck('niv_cur');
            $planContext = fn ($p) => $p->when($gestion !== '', fn ($q) => $q->deGestion($gestion));
            $parallels = Paralelo::whereHas('planesAsignatura', $planContext)->orderBy('nom_par')->get(['cod_par', 'nom_par']);
            $shifts = Turno::whereHas('planesAsignatura', $planContext)->orderBy('nom_tur')->get(['cod_tur', 'nom_tur']);
        }

        return compact('title', 'columns', 'rows', 'search', 'dashboardRoute', 'years', 'gestion', 'course', 'courses', 'studentFilter', 'area', 'catalogFilters', 'levels', 'parallels', 'shifts');
    }

    /** Un mismo plan debe satisfacer gestión/paralelo/turno y el scope de Regencia. */
    public function constrainCourseCatalog(Builder $query, array $filters, ?User $regent = null): Builder
    {
        $query->when(filled($filters['nivel'] ?? null), fn ($q) => $q->where('niv_cur', $filters['nivel']))
            ->when(filled($filters['estado'] ?? null), fn ($q) => $q->where('est_cur', $filters['estado']));
        if ($regent || filled($filters['gestion'] ?? null) || filled($filters['paralelo'] ?? null) || filled($filters['turno'] ?? null)) {
            $query->whereHas('planesAsignatura', function ($plan) use ($filters, $regent) {
                foreach (['gestion' => 'cod_gea', 'paralelo' => 'cod_par', 'turno' => 'cod_tur'] as $filter => $column) {
                    if (filled($filters[$filter] ?? null)) {
                        $plan->whereHas('grupoAcademico', fn ($grupo) => $grupo->where($column, $filters[$filter]));
                    }
                }
                if ($regent) {
                    app(RegencyAccessService::class)->constrain($plan, $regent, 'plan_asignatura');
                }
            });
        }

        return $query;
    }

    public function constrainShiftCatalog(Builder $query, array $filters): Builder
    {
        return $query->when(filled($filters['estado'] ?? null), fn ($q) => $q->where('est_tur', $filters['estado']))
            ->when(filled($filters['desde'] ?? null), fn ($q) => $q->where('hor_ini_tur', '>=', $filters['desde']))
            ->when(filled($filters['hasta'] ?? null), fn ($q) => $q->where('hor_fin_tur', '<=', $filters['hasta']));
    }

    /** Proyección mínima de Secretaría; no incluye personas, notas ni responsables. */
    public function secretaryDetail(User $user, string $area, string $id, string $gestion = ''): array
    {
        $this->authorizeQuery($user, $area, 'secretaria');
        abort_unless(in_array($area, ['cursos', 'turnos'], true), 403);
        validator(['id' => $id, 'gestion' => $gestion], ['id' => ['required', 'string', 'max:20'], 'gestion' => ['nullable', 'string', 'max:20']])->validate();
        if ($area === 'cursos') {
            $record = Curso::select('cod_cur', 'nom_cur', 'niv_cur', 'est_cur')->findOrFail($id);
            $offering = GrupoAcademico::where('cod_cur', $id)
                ->when($gestion !== '', fn ($q) => $q->where('cod_gea', $gestion))
                ->select('cod_gac', 'cod_cur', 'cod_gea', 'cod_par', 'cod_tur')
                ->with('gestionAcademica:cod_gea,ani_gea', 'paralelo:cod_par,nom_par', 'turno:cod_tur,nom_tur')
                ->orderBy('cod_gea')->orderBy('cod_par')->orderBy('cod_tur')->limit(51)->get();

            return ['title' => $record->nom_cur, 'fields' => ['Código' => $record->cod_cur, 'Nivel' => $record->niv_cur, 'Estado' => $record->est_cur],
                'items' => $offering->take(50)->map(fn ($p) => implode(' · ', [$p->gestionAcademica?->ani_gea ?? 'Gestión no disponible', $p->paralelo?->nom_par ?? 'Paralelo no disponible', $p->turno?->nom_tur ?? 'Turno no disponible'])),
                'itemsTitle' => 'Oferta registrada en planes', 'truncated' => $offering->count() > 50];
        }
        $record = Turno::select('cod_tur', 'nom_tur', 'hor_ini_tur', 'hor_fin_tur', 'est_tur')->findOrFail($id);
        $templates = PlantillaHoraria::where('cod_tur', $id)->orderBy('ord_pho')->limit(51)->get(['nom_pho', 'tip_pho', 'est_pho']);

        return ['title' => $record->nom_tur, 'fields' => ['Código' => $record->cod_tur, 'Inicio' => $record->hor_ini_tur, 'Fin' => $record->hor_fin_tur, 'Estado' => $record->est_tur],
            'items' => $templates->take(50)->map(fn ($p) => implode(' · ', [$p->nom_pho, $p->tip_pho, $p->est_pho])),
            'itemsTitle' => 'Plantillas horarias registradas', 'truncated' => $templates->count() > 50];
    }

    private function filtrarPlanes(Builder $query, \Closure $filtro, bool $asistencia = false): Builder
    {
        if ($asistencia) {
            return $query->whereHas('asistenciaClase.claseVirtual', fn ($clase) => $this->filtrarPlanes($clase, $filtro));
        }

        return $query->where(fn ($planes) => $planes->whereHas('planAsignatura', $filtro)->orWhereHas('planEspecialidad', $filtro));
    }
}
