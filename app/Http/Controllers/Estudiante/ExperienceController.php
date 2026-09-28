<?php

namespace App\Http\Controllers\Estudiante;

use App\Http\Controllers\Controller;
use App\Services\AulaVirtual\CursoVirtualService;
use Illuminate\Http\Request;

class ExperienceController extends Controller
{
    private const AREAS = [
        'progreso' => ['title' => 'Mi progreso', 'description' => 'Revisa tu avance con calificaciones, asistencia, materias y entregas registradas.'],
        'futuro' => ['title' => 'Mi futuro académico', 'description' => 'Explora opciones académicas sin convertirlas en decisiones deterministas.'],
        'preparacion' => ['title' => 'Mi preparación', 'description' => 'Identifica fortalezas actuales y áreas que puedes reforzar.'],
        'plan' => ['title' => 'Mi plan', 'description' => 'Organiza tus próximos pasos con la información académica disponible.'],
        'fuentes' => ['title' => 'Fuentes académicas', 'description' => 'Consulta recursos vinculados a tus materias y objetivos.'],
        'asistente' => ['title' => 'Asistente de estudio', 'description' => 'Espacio preparado para recibir apoyo académico contextual.'],
    ];

    public function show(Request $request, string $area, CursoVirtualService $courses)
    {
        abort_unless(isset(self::AREAS[$area]), 404);
        $student = $courses->estudianteDeUsuario($request->user());
        abort_unless($student, 403);
        $academic = $courses->dashboardEstudiante($request->user());
        $grades = $area === 'progreso' && $request->user()->can('calificaciones.ver.propias')
            ? \App\Models\Calificacion::with('asignatura', 'periodoEvaluacion', 'planAsignatura.gestionAcademica', 'planAsignatura.curso')
                ->where('cod_est', $student->cod_est)->where('est_cal', 'ACTIVO')->orderByDesc('created_at')->paginate(20)
            : null;
        $materials = $area === 'fuentes' && $request->user()->can('Acceso_Aula_Virtual')
            ? \App\Models\AulaVirtual\MaterialClase::whereIn('cod_cla', $academic['cursos']->pluck('cod_cla'))->where('est_mat', 'ACTIVO')->latest()->paginate(20)
            : null;

        return view('estudiante.area', self::AREAS[$area] + [
            'area' => $area,
            'academic' => $academic, 'officialGrades' => $grades, 'materials' => $materials,
        ]);
    }
}
