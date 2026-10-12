<?php

namespace App\Http\Controllers\Estudiante;

use App\Http\Controllers\Controller;
use App\Services\AporteIngenieril\PlanPersonalEstudiante;
use App\Services\AulaVirtual\CursoVirtualService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PlanAcademicoController extends Controller
{
    public function index(Request $request, CursoVirtualService $cursos, PlanPersonalEstudiante $plan): View
    {
        abort_unless($cursos->estudianteDeUsuario($request->user()), 403);

        return view('estudiante.orientacion.plan', $plan->snapshot($request->user()) + [
            'academic' => $cursos->dashboardEstudiante($request->user()),
        ]);
    }

    public function elegir(Request $request, PlanPersonalEstudiante $plan): RedirectResponse
    {
        $plan->elegir($request->user(), $request->only(['career_id', 'meta', 'accion_personal', 'compromiso', 'student_id']));

        return to_route('estudiante.plan')->with('status', 'Tu plan empieza desde cero. Conservamos tus planes anteriores.');
    }

    public function cambiar(Request $request, PlanPersonalEstudiante $plan): RedirectResponse
    {
        $plan->solicitarCambio($request->user(), $request->only(['career_id', 'motivo', 'confirmar', 'student_id']));

        return to_route('estudiante.intereses')->with('status', 'Motivo guardado. Responde nuevamente el cuestionario y genera un estudio antes de confirmar tu nuevo enfoque.');
    }

    public function progreso(Request $request, PlanPersonalEstudiante $plan): RedirectResponse
    {
        $plan->progreso($request->user(), $request->only(['plan_id', 'accion_id', 'completada', 'student_id']));

        return to_route('estudiante.plan')->with('status', 'Tu avance personal quedó guardado con su historial.');
    }

    public function conservar(Request $request, PlanPersonalEstudiante $plan): RedirectResponse
    {
        $plan->cancelarCambio($request->user(), $request->only(['plan_id', 'student_id']));

        return to_route('estudiante.plan')->with('status', 'Conservaste tu enfoque actual y su avance. La solicitud de cambio permanece en el historial.');
    }
}
