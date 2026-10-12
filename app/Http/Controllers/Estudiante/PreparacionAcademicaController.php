<?php

namespace App\Http\Controllers\Estudiante;

use App\Http\Controllers\Controller;
use App\Services\AporteIngenieril\PlanPersonalEstudiante;
use App\Services\AulaVirtual\CursoVirtualService;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PreparacionAcademicaController extends Controller
{
    public function index(Request $request, CursoVirtualService $cursos, PlanPersonalEstudiante $plan): View
    {
        abort_unless($cursos->estudianteDeUsuario($request->user()), 403);

        return view('estudiante.orientacion.preparacion', $plan->snapshot($request->user()) + [
            'academic' => $cursos->dashboardEstudiante($request->user()),
        ]);
    }
}
