<?php

namespace App\Http\Controllers\Estudiante;

use App\Http\Controllers\Controller;
use App\Services\AporteIngenieril\AporteIngenierilClient;
use App\Services\AporteIngenieril\DTO\AporteResponse;
use App\Services\AporteIngenieril\PlanPersonalEstudiante;
use App\Services\AporteIngenieril\StudentOrientationService;
use App\Support\Orientacion\ExperienciaIntereses;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class InteresesController extends Controller
{
    public function index(Request $request, StudentOrientationService $orientacion, AporteIngenierilClient $cliente, PlanPersonalEstudiante $plan): View
    {
        $contexto = $orientacion->context($request->user());
        $actualizarPorCambio = ($contexto['student'] ?? null) && $plan->requiereCuestionarioNuevo($request->user(), $contexto['student']->cod_est,
            $contexto['payload']['academic_period'] ?? null, data_get($contexto['activity']?->riasec_score, 'trace_id'));

        return view('estudiante.orientacion.intereses', $contexto + [
            'instrument' => $cliente->riasecInstrument(),
            'experiencia' => ExperienciaIntereses::presentar($contexto),
            'actualizarPorCambio' => (bool) $actualizarPorCambio,
        ]);
    }

    public function estudio(Request $request, StudentOrientationService $orientacion): View
    {
        $contexto = $orientacion->context($request->user());

        return view('estudiante.orientacion.analisis', $contexto + [
            'experiencia' => ExperienciaIntereses::presentar($contexto),
        ]);
    }

    public function borrador(Request $request, StudentOrientationService $orientacion): JsonResponse
    {
        $entrada = $request->validate([
            'instrument_version' => 'required|string|max:80',
            'responses' => ['required', 'array', 'min:1', 'max:30', function ($atributo, $valor, $fallar) {
                if (! is_array($valor)) {
                    return;
                }
                foreach (array_keys($valor) as $id) {
                    if (! preg_match('/^(?:[1-9]|[12][0-9]|30)$/', (string) $id)) {
                        $fallar('Guarda únicamente respuestas de las 30 actividades del cuestionario.');
                        break;
                    }
                }
            }],
            'responses.*' => ['required', 'integer', 'between:1,5', function ($atributo, $valor, $fallar) {
                if ((! is_int($valor) && ! is_string($valor)) || ! preg_match('/^[1-5]$/', (string) $valor)) {
                    $fallar('Elige una de las cinco opciones disponibles.');
                }
            }],
            'student_id' => 'prohibited',
        ], [
            'responses.required' => 'Responde al menos una actividad antes de guardar tu avance.',
            'responses.*.between' => 'Elige una de las cinco opciones disponibles.',
        ]);
        $respuestas = [];
        foreach ($entrada['responses'] as $id => $valor) {
            $respuestas[] = ['item_id' => (int) $id, 'value' => (int) $valor];
        }
        $resultado = $orientacion->guardarBorrador($request->user(), ['instrument_version' => $entrada['instrument_version'], 'responses' => $respuestas]);
        if (! $resultado->available) {
            return response()->json(['notice' => [
                'title' => $resultado->status === 422 ? 'Revisa el cuestionario antes de guardar' : 'No pudimos guardar tu avance en este momento',
                'message' => $resultado->message,
                'preserved' => 'Tu borrador anterior y cualquier resultado guardado permanecen disponibles. Conserva esta pestaña abierta para mantener tus cambios actuales.',
                'next_step' => $resultado->status === 422 ? 'Revisa el mensaje antes de volver a guardar.' : 'Vuelve a intentarlo cuando se restablezca la conexión.',
            ]], $resultado->status === 422 ? 422 : 503);
        }

        return response()->json($resultado->data);
    }

    public function guardar(Request $request, StudentOrientationService $orientacion): RedirectResponse|JsonResponse
    {
        $entrada = $request->validate([
            'instrument_version' => 'required|string|max:80',
            'responses' => ['required', 'array', 'size:30', function ($atributo, $valor, $fallar) {
                if (is_array($valor) && (array_diff(range(1, 30), array_keys($valor)) || array_diff(array_keys($valor), range(1, 30)))) {
                    $fallar('Responde una vez cada una de las 30 actividades del cuestionario.');
                }
            }],
            'responses.*' => 'required|integer|between:1,5',
            'student_id' => 'prohibited',
        ], [
            'responses.required' => 'Responde las actividades antes de guardar tus intereses.',
            'responses.size' => 'Faltan actividades por responder. Completa las 30 antes de guardar.',
            'responses.*.required' => 'Elige cuánto te interesa esta actividad.',
            'responses.*.integer' => 'Elige una opción de la escala del cuestionario.',
            'responses.*.between' => 'Elige una de las cinco opciones disponibles.',
            'instrument_version.required' => 'Recarga el cuestionario para obtener su versión vigente.',
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
            if ($request->expectsJson()) {
                return response()->json(['notice' => $this->avisoNoDisponible($resultado, true)], $resultado->status === 422 ? 422 : 503);
            }

            return back()->withInput()->with('orientation_notice', $this->avisoNoDisponible($resultado, true));
        }

        if ($request->expectsJson()) {
            $request->session()->flash('status', 'Tus intereses se guardaron correctamente.');

            return response()->json(['redirect' => route('estudiante.analisis').'#mi-perfil']);
        }

        return to_route('estudiante.analisis')->with('status', 'Tus intereses se guardaron correctamente.');
    }

    public function analizar(Request $request, StudentOrientationService $orientacion): RedirectResponse|JsonResponse
    {
        $request->validate(['student_id' => 'prohibited']);
        $resultado = $orientacion->analyze($request->user());
        if (! $resultado->available) {
            if ($request->expectsJson()) {
                return response()->json(['notice' => $this->avisoNoDisponible($resultado)], $resultado->status === 422 ? 422 : 503);
            }

            return back()->with('orientation_notice', $this->avisoNoDisponible($resultado));
        }

        if ($request->expectsJson()) {
            $request->session()->flash('status', 'Tu análisis está listo. Revisa los motivos y elige tu siguiente paso.');

            return response()->json(['redirect' => route('estudiante.analisis').'#mi-analisis']);
        }

        return to_route('estudiante.analisis')->with('status', 'Tu análisis se guardó correctamente.');
    }

    private function avisoNoDisponible(AporteResponse $resultado, bool $guardandoIntereses = false): array
    {
        if (($resultado->data['failure_stage'] ?? null) === 'storage') {
            return [
                'title' => 'No pudimos guardar tu estudio',
                'message' => $resultado->message,
                'preserved' => 'Tu cuestionario y cualquier estudio anterior siguen guardados. No mostramos el estudio nuevo como confirmado.',
                'next_step' => 'Vuelve a intentarlo. Si el problema continúa, solicita ayuda a la administración.',
            ];
        }
        if (($resultado->data['failure_stage'] ?? null) === 'profile_changed') {
            return [
                'title' => 'Tu perfil cambió durante el análisis',
                'message' => $resultado->message,
                'preserved' => 'Tus respuestas actuales y los estudios anteriores permanecen guardados.',
                'next_step' => 'Recarga Mi análisis, revisa tu perfil y vuelve a generar el estudio.',
            ];
        }
        if ($resultado->status === 422) {
            return [
                'title' => $guardandoIntereses ? 'Revisa el cuestionario antes de guardar' : 'Tu análisis necesita información adicional',
                'message' => $resultado->message,
                'preserved' => $guardandoIntereses ? 'Tus respuestas permanecen en el formulario y tu resultado anterior sigue guardado.' : 'Tu cuestionario y cualquier resultado anterior permanecen guardados.',
                'next_step' => $guardandoIntereses ? 'Revisa el mensaje antes de volver a guardar.' : 'Completa los datos pendientes y vuelve a intentarlo cuando el botón esté habilitado.',
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
