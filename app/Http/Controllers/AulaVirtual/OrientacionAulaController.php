<?php

namespace App\Http\Controllers\AulaVirtual;

use App\Http\Controllers\Controller;
use App\Services\AporteIngenieril\AporteIngenierilClient;
use App\Services\AporteIngenieril\KnowledgeService;
use App\Services\AporteIngenieril\ServicioContextoTutor;
use App\Services\AporteIngenieril\StudentOrientationService;
use App\Services\AporteIngenieril\TutorService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\View\View;

class OrientacionAulaController extends Controller
{
    public function mostrar(
        Request $request,
        StudentOrientationService $orientacion,
        AporteIngenierilClient $cliente,
    ): View|RedirectResponse {
        $contexto = $orientacion->context($request->user());
        $seccion = $request->query('section', 'perfil');
        abort_unless(in_array($seccion, ['perfil', 'riasec', 'analisis', 'fuentes', 'tutor'], true), 404);
        if ($seccion === 'analisis') {
            return to_route('estudiante.intereses');
        }
        if ($seccion === 'tutor') {
            return to_route('estudiante.asistente');
        }
        $request->validate([
            'explore' => 'nullable|in:analysis,universities,all',
            'career' => 'nullable|string|max:160',
            'university' => 'nullable|string|max:180',
            'compare' => 'nullable|array|max:3',
            'compare.*' => 'string|max:160|distinct',
        ]);
        $instrumento = $seccion === 'riasec' ? $cliente->riasecInstrument() : null;

        return view('aula-virtual.orientacion.aporte-ingenieril', $contexto + [
            'section' => $seccion,
            'instrument' => $instrumento,
            'conversationHistory' => [],
        ]);
    }

    public function consultar(
        Request $request,
        StudentOrientationService $orientacion,
        KnowledgeService $conocimiento,
        TutorService $tutor,
        ServicioContextoTutor $contextoTutor,
    ): View {
        $contexto = $orientacion->context($request->user());
        $entrada = $request->validate([
            'mode' => 'required|in:fuentes,tutor',
            'question' => 'required|string|min:2|max:1000',
            'student_id' => 'prohibited',
        ]);
        $historial = (array) $request->session()->get('orientation.tutor.history', []);
        if ($entrada['mode'] === 'fuentes') {
            $resultado = $conocimiento->search($entrada['question']);
        } else {
            [$contextoAcademico, $contextoEstudiante] = $contextoTutor->construir($contexto);
            $resultado = $tutor->ask(
                $contexto['student']->cod_est,
                $entrada['question'],
                $contextoAcademico,
                $contextoEstudiante,
                $historial,
            );
            if ($resultado->available) {
                $historialActualizado = [...$historial,
                    ['role' => 'user', 'content' => Str::limit($entrada['question'], 2000, '')],
                    ['role' => 'assistant', 'content' => Str::limit((string) data_get($resultado->data, 'answer'), 2000, '')],
                ];
                $request->session()->put('orientation.tutor.history', array_slice($historialActualizado, -8));
            }
        }

        return view('aula-virtual.orientacion.aporte-ingenieril', $contexto + [
            'section' => $entrada['mode'],
            'instrument' => null,
            'queryResult' => $resultado,
            'question' => $entrada['question'],
            'conversationHistory' => $historial,
        ]);
    }

    public function reiniciarTutor(Request $request, StudentOrientationService $orientacion): RedirectResponse
    {
        $orientacion->student($request->user());
        $request->session()->forget('orientation.tutor.history');

        return to_route('aula-virtual.estudiante.orientacion.aporte', ['section' => 'tutor'])
            ->with('status', 'Iniciaste una conversación nueva con el tutor.');
    }
}
