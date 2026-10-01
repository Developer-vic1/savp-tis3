<?php

namespace App\Http\Controllers\AulaVirtual;

use App\Http\Controllers\Controller;
use App\Services\AporteIngenieril\AporteIngenierilClient;
use App\Services\AporteIngenieril\KnowledgeService;
use App\Services\AporteIngenieril\StudentOrientationService;
use App\Services\AporteIngenieril\TutorService;
use Illuminate\Http\Request;

class StudentOrientationController extends Controller
{
    public function show(Request $request, StudentOrientationService $orientation, AporteIngenierilClient $client)
    {
        $context = $orientation->context($request->user());
        $section = $request->query('section', 'perfil');
        abort_unless(in_array($section, ['perfil', 'riasec', 'analisis', 'fuentes', 'tutor'], true), 404);
        $instrument = $section === 'riasec' ? $client->riasecInstrument() : null;
        return view('aula-virtual.orientacion.peter3', $context + compact('section', 'instrument'));
    }

    public function score(Request $request, StudentOrientationService $orientation)
    {
        $input = $request->validate(['instrument_version' => 'required|string|max:80', 'responses' => 'required|array|size:30', 'responses.*' => 'required|integer|between:1,5', 'student_id' => 'prohibited']);
        $public = ['instrument_version' => $input['instrument_version'], 'responses' => []];
        foreach ($input['responses'] as $item => $value) {
            $public['responses'][] = ['item_id' => (int) $item, 'value' => (int) $value];
        }
        $result = $orientation->score($request->user(), $public);
        if (! $result->available) {
            return back()->withInput()->withErrors(['orientation' => $result->message]);
        }
        return to_route('aula-virtual.estudiante.orientacion.peter3', ['section' => 'riasec'])->with('status', 'Tus intereses se guardaron correctamente.');
    }

    public function analyze(Request $request, StudentOrientationService $orientation)
    {
        $request->validate(['student_id' => 'prohibited']);
        $result = $orientation->analyze($request->user());
        if (! $result->available) {
            return back()->withErrors(['orientation' => $result->message]);
        }
        return to_route('aula-virtual.estudiante.orientacion.peter3', ['section' => 'analisis'])->with('status', 'Tu análisis se guardó correctamente.');
    }

    public function query(Request $request, StudentOrientationService $orientation, KnowledgeService $knowledge, TutorService $tutor)
    {
        $context = $orientation->context($request->user());
        $input = $request->validate(['mode' => 'required|in:fuentes,tutor', 'question' => 'required|string|min:2|max:1000', 'student_id' => 'prohibited']);
        $result = $input['mode'] === 'fuentes' ? $knowledge->search($input['question']) : $tutor->ask($context['student']->cod_est, $input['question']);
        return view('aula-virtual.orientacion.peter3', $context + ['section' => $input['mode'], 'instrument' => null, 'queryResult' => $result, 'question' => $input['question']]);
    }
}
