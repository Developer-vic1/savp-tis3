<?php

namespace App\Http\Controllers\AulaVirtual;

use App\Http\Controllers\Controller;
use App\Services\AulaVirtual\CursoVirtualService;
use Illuminate\Http\Request;

class CursoVirtualController extends Controller
{
    public function __construct(private readonly CursoVirtualService $cursos) {}

    public function indexEstudiante(Request $request)
    {
        return view('aula-virtual.cursos.index-estudiante', [
            'cursos' => $this->cursos->cursosEstudiante($request->user()),
            'servicio' => $this->cursos,
        ]);
    }

    public function indexDocente(Request $request)
    {
        return view('aula-virtual.cursos.index-docente', [
            'cursos' => $this->cursos->cursosDocente($request->user()),
            'servicio' => $this->cursos,
        ]);
    }

    public function showEstudiante(Request $request, string $curso)
    {
        $clase = $this->cursos->cursoParaEstudiante($request->user(), $curso);
        abort_if(! $clase, 403);

        return view('aula-virtual.cursos.show-estudiante', [
            'curso' => $clase,
            'estudiante' => $this->cursos->estudianteDeUsuario($request->user()),
            'resumen' => $this->cursos->cursoResumen($clase, $this->cursos->estudianteDeUsuario($request->user())),
        ]);
    }

    public function showDocente(Request $request, string $curso)
    {
        $clase = $this->cursos->cursoParaDocente($request->user(), $curso);
        abort_if(! $clase, 403);

        return view('aula-virtual.cursos.show-docente', [
            'curso' => $clase,
            'resumen' => $this->cursos->cursoResumen($clase),
        ]);
    }
}
