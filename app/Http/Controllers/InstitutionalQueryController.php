<?php

namespace App\Http\Controllers;

use App\Models\Calificacion;
use App\Models\Curso;
use App\Models\Docente;
use App\Models\Estudiante;
use App\Models\InscripcionEstudiante;
use App\Models\AulaVirtual\AsistenciaEstudiante;
use App\Models\ReporteGenerado;
use Illuminate\Http\Request;

class InstitutionalQueryController extends Controller
{
    public const AREAS = [
        'orientacion' => ['Orientación registrada', \App\Models\AulaVirtual\OrientacionResultado::class, 'orientacion.ver.institucional', ['estudiante.persona.nom_per' => 'Estudiante', 'perfil_predominante' => 'Perfil registrado', 'estado' => 'Estado', 'created_at' => 'Fecha'], ['estudiante.persona']],
        'estudiantes' => ['Estudiantes', Estudiante::class, 'estudiantes.ver.institucional', ['cod_est' => 'Código', 'persona.nom_per' => 'Nombre', 'persona.ape_pat_per' => 'Apellido', 'est_est' => 'Estado'], ['persona']],
        'docentes' => ['Docentes', Docente::class, 'Docentes', ['cod_doc' => 'Código', 'personalInstitucional.persona.nom_per' => 'Nombre', 'personalInstitucional.persona.ape_pat_per' => 'Apellido', 'est_doc' => 'Estado'], ['personalInstitucional.persona']],
        'cursos' => ['Cursos', Curso::class, 'cursos.ver.institucional', ['cod_cur' => 'Código', 'nom_cur' => 'Curso', 'niv_cur' => 'Nivel', 'est_cur' => 'Estado'], []],
        'inscripciones' => ['Inscripciones', InscripcionEstudiante::class, 'inscripciones.ver.institucional', ['cod_ins' => 'Código', 'estudiante.persona.nom_per' => 'Estudiante', 'curso.nom_cur' => 'Curso', 'gestionAcademica.ani_gea' => 'Gestión', 'est_ins' => 'Estado'], ['estudiante.persona', 'curso', 'gestionAcademica']],
        'rendimiento' => ['Rendimiento registrado', Calificacion::class, 'calificaciones.ver.institucional', ['cod_cal' => 'Código', 'estudiante.persona.nom_per' => 'Estudiante', 'asignatura.nom_asi' => 'Asignatura', 'periodoEvaluacion.nom_pev' => 'Periodo', 'not_cal' => 'Nota'], ['estudiante.persona', 'asignatura', 'periodoEvaluacion']],
        'asistencia' => ['Asistencia registrada', AsistenciaEstudiante::class, 'asistencia.ver.institucional', ['cod_asi_est' => 'Código', 'estudiante.persona.nom_per' => 'Estudiante', 'estadoAsistencia.nom_est_asi' => 'Asistencia', 'fec_reg_asi_est' => 'Registro'], ['estudiante.persona', 'estadoAsistencia']],
        'reportes' => ['Reportes generados', ReporteGenerado::class, 'reportes.ver.institucional', ['codigo' => 'Código', 'tipo_reporte' => 'Tipo', 'formato' => 'Formato', 'estado' => 'Estado', 'created_at' => 'Fecha'], []],
    ];

    public function index(Request $request, string $area)
    {
        abort_unless(isset(self::AREAS[$area]), 404);
        [$title, $model, $permission, $columns, $relations] = self::AREAS[$area];
        $regency = $request->routeIs('regencia.*');
        abort_unless($request->user()->hasRole($regency ? 'Regente' : 'Director') && $request->user()->can($permission), 403, 'No tienes autorización para realizar esta consulta.');
        $data = $request->validate(['search' => ['nullable', 'string', 'max:100'], 'gestion' => ['nullable', 'string', 'exists:gestion_academica,cod_gea']]);
        $search = trim($data['search'] ?? '');
        $key = (new $model)->getKeyName();
        $query = $model::query()->with($relations);
        if ($area === 'rendimiento') {
            $query->with('planAsignatura.gestionAcademica', 'planAsignatura.curso');
            $columns = ['planAsignatura.gestionAcademica.ani_gea' => 'Gestión', 'planAsignatura.curso.nom_cur' => 'Curso'] + $columns;
        }
        $gestion = $data['gestion'] ?? '';
        if ($gestion !== '') {
            match ($area) {
                'estudiantes' => $query->whereHas('inscripciones', fn ($q) => $q->where('cod_gea', $gestion)),
                'inscripciones' => $query->where('cod_gea', $gestion),
                'cursos' => $query->whereHas('planesAsignatura', fn ($q) => $q->where('cod_gea', $gestion)),
                'rendimiento' => app(\App\Services\GradeService::class)->available() ? $query->whereHas('planAsignatura', fn ($q) => $q->where('cod_gea', $gestion)) : $query->whereRaw('1 = 0'),
                'asistencia' => $query->whereHas('asistenciaClase.claseVirtual.planAsignatura', fn ($q) => $q->where('cod_gea', $gestion)),
                default => null,
            };
        }
        if ($regency) {
            $access = app(\App\Services\RegencyAccessService::class);
            $scope = fn ($q) => $access->constrain($q, $request->user(), 'plan_asignatura')->when($gestion !== '', fn ($p) => $p->where('cod_gea', $gestion));
            match ($area) {
                'estudiantes' => $query->whereHas('inscripciones', fn ($q) => $access->constrain($q, $request->user(), 'inscripcion_estudiante')->when($gestion !== '', fn ($p) => $p->where('cod_gea', $gestion))),
                'cursos' => $query->whereHas('planesAsignatura', $scope),
                'inscripciones' => $access->constrain($query, $request->user(), 'inscripcion_estudiante'),
                'rendimiento' => app(\App\Services\GradeService::class)->available() ? $query->whereHas('planAsignatura', $scope) : $query->whereRaw('1 = 0'),
                'asistencia' => $query->whereHas('asistenciaClase.claseVirtual.planAsignatura', $scope),
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
                    if ($person) { $q->orWhereHas($person, fn ($p) => $p->where('nom_per', 'like', '%'.$search.'%')->orWhere('ape_pat_per', 'like', '%'.$search.'%')); }
                    if ($area === 'cursos') { $q->orWhere('nom_cur', 'like', '%'.$search.'%'); }
                });
            })
            ->orderByDesc($key)->paginate(20)->withQueryString();

        $dashboardRoute = $regency ? 'regencia.dashboard' : 'direccion.dashboard';
        $years = in_array($area, ['estudiantes', 'cursos', 'inscripciones', 'rendimiento', 'asistencia'], true)
            ? \App\Models\GestionAcademica::orderByDesc('ani_gea')->get() : collect();
        return view('workspaces.consulta', compact('title', 'columns', 'rows', 'search', 'dashboardRoute', 'years', 'gestion'));
    }
}
