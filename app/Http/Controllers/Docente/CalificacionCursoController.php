<?php

namespace App\Http\Controllers\Docente;

use App\Http\Controllers\Controller;
use App\Models\Calificacion;
use App\Services\GradeService;
use App\Services\AulaVirtual\CursoVirtualService;
use Illuminate\Http\Request;

class CalificacionCursoController extends Controller
{
    public function index(Request $request, string $curso, GradeService $grades)
    {
        return view('docente.calificaciones', $grades->gradesForTeacherCourse($request->user(), $curso));
    }

    public function update(Request $request, string $curso, Calificacion $calificacion, GradeService $grades, CursoVirtualService $courses)
    {
        $data = $request->validate([
            'not_cal' => ['required', 'numeric', 'min:0', 'max:100'],
            'obs_cal' => ['nullable', 'string', 'max:255'],
        ]);

        $course = $courses->cursoParaDocente($request->user(), $curso);
        abort_unless($course && $calificacion->cod_pas === $course->cod_pas, 403);
        $grades->update($request->user(), $calificacion, (float) $data['not_cal'], $data['obs_cal'] ?? null);

        return back()->with('status', 'Calificación actualizada correctamente.');
    }

    public function store(Request $request, string $curso, GradeService $grades, CursoVirtualService $courses)
    {
        $course = $courses->cursoParaDocente($request->user(), $curso);
        abort_unless($course, 403);
        $data = $request->validate(['cod_est' => ['required', 'string'], 'cod_pev' => ['required', 'string'],
            'not_cal' => ['required', 'numeric', 'between:0,100'], 'obs_cal' => ['nullable', 'string', 'max:255']]);
        $grades->save($request->user(), $course->cod_pas, $data['cod_est'], $data['cod_pev'], (float) $data['not_cal'], $data['obs_cal'] ?? null);

        return back()->with('status', 'Nota oficial registrada para la gestión de esta asignación.');
    }
}
