<?php

namespace App\Http\Controllers\Estudiante;

use App\Http\Controllers\Controller;
use App\Services\AporteIngenieril\AporteIngenierilClient;
use App\Services\AporteIngenieril\DTO\AporteResponse;
use App\Services\AporteIngenieril\StudentOrientationService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class InteresesController extends Controller
{
    public function index(Request $request, StudentOrientationService $orientacion, AporteIngenierilClient $cliente): View
    {
        return view('estudiante.orientacion.intereses', $orientacion->context($request->user()) + [
            'instrument' => $cliente->riasecInstrument(),
        ]);
    }

    public function guardar(Request $request, StudentOrientationService $orientacion): RedirectResponse
    {
        $entrada = $request->validate([
            'instrument_version' => 'required|string|max:80',
            'responses' => 'required|array|size:30',
            'responses.*' => 'required|integer|between:1,5',
            'student_id' => 'prohibited',
        ]);
        $respuestas = [];
        foreach ($entrada['responses'] as $item => $valor) {
            $respuestas[] = ['item_id' => (int) $item, 'value' => (int) $valor];
        }
        $resultado = $orientacion->score($request->user(), [
            'instrument_version' => $entrada['instrument_version'],
            'responses' => $respuestas,
        ]);

        if (! $resultado->available) {
            return back()->withInput()->with('orientation_notice', $this->avisoNoDisponible($resultado, true));
        }

        return to_route('estudiante.intereses')->with('status', 'Tus intereses se guardaron correctamente.');
    }

    public function analizar(Request $request, StudentOrientationService $orientacion): RedirectResponse
    {
        $request->validate(['student_id' => 'prohibited']);
        $resultado = $orientacion->analyze($request->user());
        if (! $resultado->available) {
            return back()->with('orientation_notice', $this->avisoNoDisponible($resultado));
        }

        return to_route('estudiante.intereses')->with('status', 'Tu análisis se guardó correctamente.');
    }

    private function avisoNoDisponible(AporteResponse $resultado, bool $guardandoIntereses = false): array
    {
        if ($resultado->status === 422) {
            return [
                'title' => 'Tu análisis necesita información adicional',
                'message' => $resultado->message,
                'preserved' => 'Tu cuestionario y cualquier resultado anterior permanecen guardados.',
                'next_step' => 'Completa los datos pendientes y vuelve a intentarlo cuando el botón esté habilitado.',
            ];
        }

        return [
            'title' => $guardandoIntereses
                ? 'No pudimos guardar un resultado nuevo en este momento'
                : 'No pudimos actualizar tu análisis en este momento',
            'message' => 'El servicio que procesa la orientación no respondió correctamente. No es un error de tus respuestas.',
            'preserved' => $guardandoIntereses
                ? 'Tu resultado anterior sigue guardado y mantuvimos tus respuestas en el formulario.'
                : 'Tu cuestionario RIASEC y tu último análisis siguen guardados.',
            'next_step' => 'Puedes revisar el resultado disponible y volver a intentarlo en unos minutos.',
        ];
    }
}
