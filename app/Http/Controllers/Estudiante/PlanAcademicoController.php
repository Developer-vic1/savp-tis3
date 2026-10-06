<?php

namespace App\Http\Controllers\Estudiante;

use App\Http\Controllers\Controller;
use App\Services\AulaVirtual\CursoVirtualService;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PlanAcademicoController extends Controller
{
    public function index(Request $request, CursoVirtualService $cursos): View
    {
        abort_unless($cursos->estudianteDeUsuario($request->user()), 403);

        return view('estudiante.orientacion.plan', [
            'academic' => $cursos->dashboardEstudiante($request->user()),
        ]);
    }
}
