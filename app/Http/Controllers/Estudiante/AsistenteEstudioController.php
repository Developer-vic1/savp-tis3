<?php

namespace App\Http\Controllers\Estudiante;

use App\Http\Controllers\Controller;
use App\Services\AporteIngenieril\ServicioContextoTutor;
use App\Services\AporteIngenieril\StudentOrientationService;
use App\Services\AporteIngenieril\TutorService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\View\View;

class AsistenteEstudioController extends Controller
{
    public function index(Request $request, StudentOrientationService $orientacion): View
    {
        return view('estudiante.orientacion.asistente', $orientacion->context($request->user()) + [
            'conversationHistory' => (array) $request->session()->get('orientation.tutor.history', []),
        ]);
    }

    public function consultar(
        Request $request,
        StudentOrientationService $orientacion,
        TutorService $tutor,
        ServicioContextoTutor $contextoTutor,
    ): View {
        $contexto = $orientacion->context($request->user());
        $entrada = $request->validate([
            'question' => 'required|string|min:2|max:1000',
            'student_id' => 'prohibited',
        ]);
        $historial = (array) $request->session()->get('orientation.tutor.history', []);
        [$contextoAcademico, $contextoEstudiante] = $contextoTutor->construir($contexto);
        $resultado = $tutor->ask(
            $contexto['student']->cod_est,
            $entrada['question'],
            $contextoAcademico,
            $contextoEstudiante,
            $historial,
        );

        if ($resultado->available) {
            $historial = [...$historial,
                ['role' => 'user', 'content' => Str::limit($entrada['question'], 2000, '')],
                ['role' => 'assistant', 'content' => Str::limit((string) data_get($resultado->data, 'answer'), 2000, '')],
            ];
            $historial = array_slice($historial, -8);
            $request->session()->put('orientation.tutor.history', $historial);
        }

        return view('estudiante.orientacion.asistente', $contexto + [
            'conversationHistory' => $historial,
            'queryResult' => $resultado,
            'question' => $entrada['question'],
        ]);
    }

    public function reiniciar(Request $request, StudentOrientationService $orientacion): RedirectResponse
    {
        $orientacion->student($request->user());
        $request->session()->forget('orientation.tutor.history');

        return to_route('estudiante.asistente')->with('status', 'Iniciaste una conversación nueva con el asistente.');
    }
}
