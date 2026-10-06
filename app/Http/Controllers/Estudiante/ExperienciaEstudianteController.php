<?php

namespace App\Http\Controllers\Estudiante;

use App\Http\Controllers\Controller;
use App\Models\Oficial\AulaVirtual\MaterialClase;
use App\Models\Oficial\Academico\Calificacion;
use App\Services\AulaVirtual\CursoVirtualService;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ExperienciaEstudianteController extends Controller
{
    private const AREAS = [
        'progreso' => ['title' => 'Mi progreso', 'description' => 'Revisa tu avance con calificaciones, asistencia, materias y entregas registradas.'],
        'fuentes' => ['title' => 'Fuentes académicas', 'description' => 'Consulta recursos vinculados a tus materias y objetivos.'],
    ];

    public function mostrar(Request $request, string $area, CursoVirtualService $cursos): View
    {
        abort_unless(isset(self::AREAS[$area]), 404);
        $permiso = $area === 'progreso' ? 'calificaciones.ver.propias' : 'Materiales_Aula';
        abort_unless($request->user()->can($permiso), 403);

        $estudiante = $cursos->estudianteDeUsuario($request->user());
        abort_unless($estudiante, 403);
        $academico = $cursos->dashboardEstudiante($request->user());
        $calificaciones = $area === 'progreso'
            ? Calificacion::with('asignatura', 'periodoEvaluacion', 'planAsignatura.gestionAcademica', 'planAsignatura.curso')
                ->deEstudiante($estudiante->cod_est)
                ->whereIn('est_cal', ['VIGENTE', 'RECTIFICADA'])
                ->orderByDesc('created_at')
                ->paginate(20)
            : null;
        $materiales = $area === 'fuentes' && $request->user()->can('Acceso_Aula_Virtual')
            ? MaterialClase::whereIn('cod_cla', $academico['cursos']->pluck('cod_cla'))
                ->where('est_mat', 'ACTIVO')
                ->latest()
                ->paginate(20)
            : null;

        return view('estudiante.area', self::AREAS[$area] + [
            'area' => $area,
            'academic' => $academico,
            'officialGrades' => $calificaciones,
            'materials' => $materiales,
        ]);
    }
}
