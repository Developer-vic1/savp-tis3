<?php

namespace App\Http\Controllers;

use App\Rules\UrlFuenteUniversitaria;
use App\Services\AporteIngenieril\ActualizacionesFuentes;
use App\Services\AporteIngenieril\AporteIngenierilClient;
use App\Services\AporteIngenieril\InspectorConexionAporte;
use App\Services\NotificationService;
use App\Services\RoleDashboardResolver;
use App\Support\Conocimiento\FuenteUniversitariaInteligente;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
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
            'usuarioNombre' => $this->nombreUsuario($request),
            'fuenteInicial' => $fuenteInicial,
            'soporteFuente' => app(FuenteUniversitariaInteligente::class)->contratoParaInterfaz(),
            'canReview' => $actor === 'Administrador' && $request->user()->can('conocimiento.revisar'),
            'canPropose' => $request->user()->can('conocimiento.proponer'),
            'result' => $cliente->knowledgeGovernance(),
        ])->header('Cache-Control', 'no-store');
    }

    public function conexion(Request $request, InspectorConexionAporte $inspector)
    {
        $this->actor($request);

        return response()->json($inspector->inspeccionar())->header('Cache-Control', 'no-store');
    }

    public function resumenDirectorio(Request $request, AporteIngenierilClient $cliente)
    {
        $this->actor($request);
        $resultado = $cliente->nationalDirectory();
        if (! $resultado->available) {
            return response()->json(['message' => 'El directorio nacional no está disponible en este momento.'], 503)
                ->header('Cache-Control', 'no-store');
        }

        $instituciones = collect(data_get($resultado->data, 'institutions', []));
        $departamentos = $instituciones
            ->flatMap(fn ($institucion) => array_unique($institucion['departments'] ?? []))
            ->countBy()->sortKeys()->all();

        return response()->json([
            'institution_count' => $instituciones->count(),
            'departments' => $departamentos,
            'institutions' => $instituciones
                ->sortBy('name', SORT_NATURAL | SORT_FLAG_CASE)
                ->values()
                ->map(fn ($institucion) => [
                    'name' => $institucion['name'],
                    'acronym' => $institucion['acronym'] ?? '',
                    'departments' => $institucion['departments'] ?? [],
                ])->all(),
        ])->header('Cache-Control', 'no-store');
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
        $consulta = ['schema_version' => '1.0'] + $entrada;
        if (preg_match('/\b(?:universidades|instituciones)\b/iu', $entrada['question'])
            && preg_match('/\b(?:cu[aá]ntas?|n[uú]mero|total)\b/iu', $entrada['question'])) {
            $consulta['academic_context'] = [
                'institutional_university_count' => DB::table('universidad')
                    ->where('est_uni', 'ACTIVA')
                    ->whereExists(fn ($query) => $query->selectRaw('1')
                        ->from('sede_universidad')
                        ->whereColumn('sede_universidad.cod_uni', 'universidad.cod_uni')
                        ->where('sede_universidad.est_sed', 'ACTIVA'))
                    ->count(),
            ];
        }
        $resultado = $cliente->tutor($consulta);

        $datos = $resultado->data;
        if ($resultado->available && preg_match('/^\s*(hola|buen[oa]s?\s+(d[ií]as|tardes|noches)|hey)[!¡,.\s]*$/iu', $entrada['question'])) {
            $datos['answer'] = 'Hola, '.$this->nombreUsuario($request).'. Puedo ayudarte a consultar carreras, materias y publicaciones universitarias verificadas. No guardo memoria permanente de nuestras conversaciones. ¿Qué publicación quieres revisar?';
        }

        return response()->json($resultado->available ? $datos : [
            'message' => 'El asistente documental no está disponible. Conserva tu consulta y vuelve a intentar.',
        ], $resultado->available ? 200 : 503)->header('Cache-Control', 'no-store');
    }

    public function actualizaciones(Request $request, ActualizacionesFuentes $servicio)
    {
        $this->actor($request);

        return response()->json($servicio->snapshot())->header('Cache-Control', 'no-store');
    }

    public function configurarActualizaciones(Request $request, ActualizacionesFuentes $servicio)
    {
        $this->actor($request);
        $datos = $request->validate(['source_id' => ['required', 'string', 'max:160', 'regex:/^[A-Za-z0-9_-]+$/'], 'cadence' => ['required', Rule::in(array_keys(ActualizacionesFuentes::CADENCIAS))]]);

        return response()->json($servicio->configurar($datos['source_id'], $datos['cadence']))->header('Cache-Control', 'no-store');
    }

    public function revisarActualizaciones(Request $request, ActualizacionesFuentes $servicio)
    {
        $this->actor($request);
        $datos = $request->validate(['source_id' => ['required', 'string', 'max:160', 'regex:/^[A-Za-z0-9_-]+$/']]);

        return response()->json($servicio->revisar($datos['source_id']))->header('Cache-Control', 'no-store');
    }

    private function nombreUsuario(Request $request): string
    {
        $persona = $request->user()->cod_per ? $request->user()->persona : null;
        $primerNombre = explode(' ', Str::squish((string) ($persona?->nom_per ?? '')))[0];
        $primerApellido = trim((string) ($persona?->ape_pat_per ?: $persona?->ape_mat_per));

        return Str::title(trim($primerNombre.' '.$primerApellido)) ?: 'Administrador';
    }

    public function guardar(Request $request, AporteIngenierilClient $cliente)
    {
        $actor = $this->actor($request);
        $entrada = app(FuenteUniversitariaInteligente::class)->validar($request->all(), [
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
            app(NotificationService::class)->publicar([
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
        $candidato = $this->candidato(app(FuenteUniversitariaInteligente::class)->validar($request->all()));
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

    public function extraer(string $proposalId, Request $request, AporteIngenierilClient $cliente)
    {
        abort_unless($this->actor($request) === 'Administrador', 403);
        if ($request->except('_token') !== []) {
            throw ValidationException::withMessages(['fuente' => 'La extracción usa la propuesta ya aprobada; no acepta una URL distinta.']);
        }

        $gobierno = $cliente->knowledgeGovernance();
        if (! $gobierno->available) {
            return response()->json(['message' => 'No pudimos comprobar el estado de la propuesta. Inténtelo de nuevo.'], 503)
                ->header('Cache-Control', 'no-store');
        }
        $propuesta = collect($gobierno->data['proposals'] ?? [])->first(
            fn ($item) => ($item['proposal_id'] ?? null) === $proposalId
        );
        abort_if($propuesta === null, 404);
        if (($propuesta['status'] ?? null) !== 'APROBADA_PENDIENTE_INGESTA') {
            return response()->json(['message' => 'La propuesta necesita aprobación antes de extraer el documento.'], 409)
                ->header('Cache-Control', 'no-store');
        }

        $resultado = $request->isMethod('get')
            ? $cliente->readKnowledgeExtraction($proposalId)
            : $cliente->extractKnowledgeSource($proposalId);
        if (! $resultado->available
            || ($resultado->data['proposal_id'] ?? null) !== $proposalId
            || ($resultado->data['source_url'] ?? null) !== ($propuesta['url'] ?? null)) {
            return response()->json(['message' => 'No se completó la extracción. La fuente sigue pendiente; compruebe el documento antes de reintentar.'], 503)
                ->header('Cache-Control', 'no-store');
        }

        return response()->json($resultado->data)->header('Cache-Control', 'no-store');
    }

    private function actor(Request $request): string
    {
        $actor = app(RoleDashboardResolver::class)->roleFor($request->user());
        abort_unless(in_array($actor, ['Administrador', 'Director'], true), 403);

        return $actor;
    }

    private function reglasFuente(): array
    {
        return app(FuenteUniversitariaInteligente::class)->reglas();
    }

    private function candidato(array $entrada): array
    {
        $limitaciones = $this->limitaciones($entrada['limitations_text'] ?? null);
        unset($entrada['limitations_text'], $entrada['analysis_token']);

        return $entrada + ['publication_date' => null, 'limitations' => $limitaciones];
    }

    private function limitaciones(?string $value): array
    {
        return app(FuenteUniversitariaInteligente::class)->limitaciones($value);
    }

    private function huellaCandidato(array $candidato): string
    {
        ksort($candidato);

        return hash('sha256', (string) json_encode($candidato, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));
    }
}
