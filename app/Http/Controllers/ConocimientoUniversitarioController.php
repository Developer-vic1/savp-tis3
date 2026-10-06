<?php

namespace App\Http\Controllers;

use App\Rules\FechaPublicacionAcademica;
use App\Rules\UrlFuenteUniversitaria;
use App\Services\AporteIngenieril\AporteIngenierilClient;
use App\Services\AporteIngenieril\InspectorConexionAporte;
use App\Services\RoleDashboardResolver;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class ConocimientoUniversitarioController extends Controller
{
    public function index(Request $request, AporteIngenierilClient $cliente)
    {
        $actor = $this->actor($request);

        $fuenteInicial = $request->validate([
            'url' => ['sometimes', 'nullable', 'string', 'max:2000', new UrlFuenteUniversitaria],
            'carrera' => ['sometimes', 'nullable', 'string', 'max:240'],
            'institucion' => ['sometimes', 'nullable', 'string', 'max:240'],
        ]);

        return response()->view('workspaces.conocimiento-universitario', [
            'actor' => $actor,
            'fuenteInicial' => $fuenteInicial,
            'canReview' => $actor === 'Administrador' && $request->user()->can('conocimiento.revisar'),
            'canPropose' => $request->user()->can('conocimiento.proponer'),
            'accessPermissions' => [
                'Ver fuentes y probar tutor' => $request->user()->can('conocimiento.ver'),
                'Analizar y proponer fuentes' => $request->user()->can('conocimiento.proponer'),
                'Aprobar o rechazar propuestas' => $actor === 'Administrador' && $request->user()->can('conocimiento.revisar'),
            ],
            'result' => $cliente->knowledgeGovernance(),
        ])->header('Cache-Control', 'no-store');
    }

    public function conexion(Request $request, InspectorConexionAporte $inspector)
    {
        $this->actor($request);

        return response()->json($inspector->inspeccionar())->header('Cache-Control', 'no-store');
    }

    public function probarTutor(Request $request, AporteIngenierilClient $cliente)
    {
        $this->actor($request);
        $entrada = $request->validate([
            'question' => ['required', 'string', 'min:2', 'max:2000'],
            'conversation_history' => ['sometimes', 'array', 'max:8'],
            'conversation_history.*' => ['array:role,content'],
            'conversation_history.*.role' => ['required', 'in:user,assistant'],
            'conversation_history.*.content' => ['required', 'string', 'max:2000'],
            'student_context' => ['prohibited'],
            'academic_context' => ['prohibited'],
        ]);
        $resultado = $cliente->tutor(['schema_version' => '1.0'] + $entrada);

        return response()->json($resultado->available ? $resultado->data : [
            'message' => 'El tutor no está disponible. Revise la conexión del servicio.',
        ], $resultado->available ? 200 : 503)->header('Cache-Control', 'no-store');
    }

    public function guardar(Request $request, AporteIngenierilClient $cliente)
    {
        $actor = $this->actor($request);
        $entrada = $request->validate($this->reglasFuente() + [
            'analysis_token' => ['required', 'string', 'size:64'],
        ]);
        $candidato = $this->candidato($entrada);
        $analisis = (array) $request->session()->pull('knowledge.source_analysis', []);
        $idUsuario = (string) $request->user()->getAuthIdentifier();
        $tokenValido = isset($analisis['token_hash'], $analisis['payload_hash'])
            && hash_equals((string) $analisis['token_hash'], hash('sha256', $entrada['analysis_token']))
            && hash_equals((string) $analisis['payload_hash'], $this->huellaCandidato($candidato))
            && hash_equals((string) ($analisis['user_id'] ?? ''), $idUsuario)
            && hash_equals((string) ($analisis['actor'] ?? ''), $actor)
            && is_int($analisis['expires_at'] ?? null)
            && $analisis['expires_at'] >= now()->timestamp
            && ($analisis['can_submit'] ?? false) === true;
        if (! $tokenValido) {
            throw ValidationException::withMessages([
                'analysis_token' => 'Analice nuevamente la fuente antes de enviarla. El formulario cambió o el análisis venció.',
            ]);
        }
        $this->exigirVistaPreviaVerificada($request, $cliente, $candidato, $actor);
        $resultado = $cliente->submitKnowledgeSource($candidato + ['submitted_by_role' => $actor]);

        if (! $resultado->available) {
            return back()->withInput()->withErrors(['knowledge' => 'No fue posible registrar la propuesta de fuente.']);
        }

        if ($resultado->data['accepted'] ?? false) {
            app(\App\Services\NotificationService::class)->publicar([
                'clave_evento' => 'fuente:'.hash('sha256', $request->user()->getKey().':'.$entrada['analysis_token']),
                'origen' => 'APORTE', 'tipo' => 'INFORMACION',
                'titulo' => 'Tu propuesta de fuente quedó registrada',
                'mensaje' => 'Puedes consultar su revisión en Conocimiento universitario. Su incorporación al estudio requiere la revisión correspondiente.',
                'permiso' => 'conocimiento.ver', 'ruta' => 'conocimiento.fuentes.index',
                'cod_usu_emisor' => $request->user()->getKey(),
            ], [$request->user()]);
        }

        return to_route('conocimiento.fuentes.index')->with(
            $resultado->data['accepted'] ? 'status' : 'warning',
            $resultado->data['message']
        );
    }

    public function analizar(Request $request, AporteIngenierilClient $cliente)
    {
        $actor = $this->actor($request);
        $candidato = $this->candidato($request->validate($this->reglasFuente()));
        $request->session()->forget('knowledge.source_analysis');
        $this->exigirVistaPreviaVerificada($request, $cliente, $candidato, $actor);
        $resultado = $cliente->analyzeKnowledgeSource($candidato);
        if (! $resultado->available) {
            return response()->json([
                'message' => 'El análisis documental no está disponible temporalmente.',
            ], 503)->header('Cache-Control', 'no-store');
        }

        $token = null;
        if (($resultado->data['can_submit'] ?? false) === true) {
            $token = Str::random(64);
            $request->session()->put('knowledge.source_analysis', [
                'token_hash' => hash('sha256', $token),
                'payload_hash' => $this->huellaCandidato($candidato),
                'user_id' => (string) $request->user()->getAuthIdentifier(),
                'actor' => $actor,
                'expires_at' => now()->addMinutes(10)->timestamp,
                'can_submit' => true,
            ]);
        } else {
            $request->session()->forget('knowledge.source_analysis');
        }

        return response()->json($resultado->data + ['analysis_token' => $token])->header('Cache-Control', 'no-store');
    }

    public function comprobarUrl(Request $request, AporteIngenierilClient $cliente)
    {
        $actor = $this->actor($request);
        $entrada = $request->validate(['url' => $this->reglasFuente()['url']]);
        $request->session()->forget(['knowledge.source_preview', 'knowledge.source_analysis']);
        $resultado = $cliente->inspectKnowledgeSource(['url' => $entrada['url']]);
        if (! $resultado->available) {
            return response()->json(['message' => 'No se pudo comprobar la URL. No se considera verificada.'], 503)->header('Cache-Control', 'no-store');
        }
        $this->guardarVistaPrevia($request, $resultado->data, $entrada['url'], $actor);

        return response()->json($resultado->data)->header('Cache-Control', 'no-store');
    }

    private function guardarVistaPrevia(Request $request, array $datos, string $url, string $actor): void
    {
        if (($datos['requested_url'] ?? null) !== $url) {
            return;
        }
        $request->session()->put('knowledge.source_preview', [
            'data' => $datos,
            'url' => $url,
            'user_id' => (string) $request->user()->getAuthIdentifier(),
            'actor' => $actor,
            'expires_at' => now()->addMinutes(5)->timestamp,
        ]);
    }

    private function exigirVistaPreviaVerificada(Request $request, AporteIngenierilClient $cliente, array $candidato, string $actor): void
    {
        $vistaPrevia = (array) $request->session()->get('knowledge.source_preview', []);
        if (($vistaPrevia['url'] ?? null) !== $candidato['url'] || ($vistaPrevia['actor'] ?? null) !== $actor
            || ($vistaPrevia['user_id'] ?? null) !== (string) $request->user()->getAuthIdentifier()
            || ($vistaPrevia['expires_at'] ?? 0) < now()->timestamp) {
            $request->session()->forget('knowledge.source_preview');
            $resultado = $cliente->inspectKnowledgeSource(['url' => $candidato['url']]);
            if ($resultado->available) {
                $this->guardarVistaPrevia($request, $resultado->data, $candidato['url'], $actor);
            }
            $vistaPrevia = (array) $request->session()->get('knowledge.source_preview', []);
        }
        $datos = (array) ($vistaPrevia['data'] ?? []);
        if (($datos['can_use'] ?? false) !== true || ($datos['status'] ?? '') !== 'VERIFICADA') {
            throw ValidationException::withMessages(['url' => $datos['message'] ?? 'Compruebe primero la URL. No se ha confirmado un documento público utilizable.']);
        }
        $normalize = fn (string $text): string => mb_strtolower(preg_replace('/\s+/u', ' ', trim($text)) ?? '');
        if ($normalize($candidato['title']) !== $normalize((string) ($datos['title'] ?? ''))) {
            throw ValidationException::withMessages(['title' => 'Use el título publicado detectado en la vista previa, no un texto arbitrario.']);
        }
        $esPdf = ($datos['content_type'] ?? '') === 'application/pdf';
        if ($esPdf !== str_ends_with($candidato['source_type'], '_PDF')) {
            throw ValidationException::withMessages(['source_type' => 'El tipo no coincide con el documento detectado. Revise la vista previa.']);
        }
    }

    public function revisar(string $proposalId, Request $request, AporteIngenierilClient $cliente)
    {
        abort_unless($this->actor($request) === 'Administrador', 403);
        $entrada = $request->validate([
            'approved' => ['required', 'boolean'],
            'review_note' => ['required', 'string', 'min:3', 'max:800'],
            'reviewed_by_role' => ['prohibited'],
        ]);
        $resultado = $cliente->reviewKnowledgeSource($proposalId, [
            'approved' => $request->boolean('approved'),
            'review_note' => $entrada['review_note'],
            'reviewed_by_role' => 'Administrador',
        ]);

        if (! $resultado->available) {
            return back()->withErrors(['knowledge' => 'La revisión no pudo registrarse. Verifique el estado actual de la propuesta.']);
        }

        return to_route('conocimiento.fuentes.index')->with('status', $resultado->data['message']);
    }

    private function actor(Request $request): string
    {
        $actor = app(RoleDashboardResolver::class)->roleFor($request->user());
        abort_unless(in_array($actor, ['Administrador', 'Director'], true), 403);

        return $actor;
    }

    private function reglasFuente(): array
    {
        return [
            'title' => ['required', 'string', 'min:3', 'max:240'],
            'declared_institution' => ['required', 'string', 'min:3', 'max:240'],
            'url' => ['bail', 'required', 'string', 'url:https', 'max:2000', new UrlFuenteUniversitaria],
            'source_type' => ['required', 'in:OFFICIAL_CURRICULUM_PDF,OFFICIAL_CAREER_HTML,OFFICIAL_REGULATION_PDF,OFFICIAL_UNIVERSITY_PAGE'],
            'scope' => ['required', 'string', 'min:10', 'max:500'],
            'publication_date' => ['nullable', 'string', new FechaPublicacionAcademica],
            'version' => ['required', 'string', 'max:120'],
            'campus' => ['required', 'string', 'min:2', 'max:160'],
            'city' => ['required', 'string', 'min:2', 'max:120'],
            'justification' => ['required', 'string', 'min:20', 'max:1000'],
            'limitations_text' => ['bail', 'nullable', 'string', 'max:2000', function (string $attribute, mixed $value, Closure $fail): void {
                $lines = $this->limitaciones($value);
                if (count($lines) > 10) {
                    $fail('Use como máximo 10 limitaciones; ninguna se descarta automáticamente.');
                }
                foreach ($lines as $line) {
                    if (mb_strlen($line) < 3 || mb_strlen($line) > 500) {
                        $fail('Cada limitación debe tener entre 3 y 500 caracteres.');

                        break;
                    }
                }
            }],
            'submitted_by_role' => ['prohibited'],
        ];
    }

    private function candidato(array $entrada): array
    {
        $limitaciones = $this->limitaciones($entrada['limitations_text'] ?? null);
        unset($entrada['limitations_text'], $entrada['analysis_token']);

        return $entrada + ['publication_date' => null, 'limitations' => $limitaciones];
    }

    private function limitaciones(?string $value): array
    {
        return collect(preg_split('/\R+/', $value ?? ''))
            ->map(fn ($item) => trim((string) $item))
            ->filter(fn ($item) => $item !== '')
            ->values()
            ->all();
    }

    private function huellaCandidato(array $candidato): string
    {
        ksort($candidato);

        return hash('sha256', (string) json_encode($candidato, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));
    }
}
