@extends('aula-virtual.layouts.app')
@section('title', 'Mi orientación | SAVP-TIS3')
@section('page-title', 'Mi orientación')
@section('content')
@php($statusLabel = fn ($value) => ['AVAILABLE'=>'Disponible','PARTIAL'=>'Parcial','INSUFFICIENT'=>'Insuficiente','UNAVAILABLE'=>'Sin evidencia disponible'][$value] ?? 'Sin evidencia disponible')
<div class="space-y-6 max-w-6xl mx-auto">
    <section class="ui-panel">
        <p class="ui-kicker">Orientación académica y vocacional</p>
        <h1 class="ui-title text-2xl font-black">Conoce tus intereses y explora tus opciones</h1>
        <p class="ui-subtitle mt-3">Tus intereses, tu preparación y la evidencia disponible se presentan por separado. Este análisis no predice éxito ni decide una carrera.</p>
        <nav aria-label="Mi orientación" class="flex flex-wrap gap-3 mt-5">
            @foreach (['perfil' => 'Estado de mi perfil', 'riasec' => 'Intereses RIASEC', 'analisis' => 'Mi análisis y carreras', 'fuentes' => 'Fuentes e información', 'tutor' => 'Tutor académico-vocacional'] as $key => $label)
                <a class="ui-btn-secondary" @if($section === $key) aria-current="page" @endif href="{{ route('aula-virtual.estudiante.orientacion.peter3', ['section' => $key]) }}">{{ $label }}</a>
            @endforeach
        </nav>
    </section>
    @if (session('status')) <p class="ui-panel" role="status">{{ session('status') }}</p> @endif
    @if ($errors->any())
        <div class="ui-panel" role="alert" tabindex="-1"><h2 class="font-bold">Revisa la información para continuar</h2><ul class="list-disc pl-5">@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>
    @endif
    @if (! $storageReady)
        <p class="ui-panel" role="alert">La persistencia de orientación está pendiente de habilitación. Consulta al responsable del sistema antes de completar el cuestionario.</p>
    @endif
    @if ($section === 'perfil')
        <section class="ui-panel">
            <h2 class="ui-title text-xl font-bold">Estado de mi perfil</h2>
            <p class="ui-subtitle mt-2">{{ $precheck['ready'] ? 'Tu perfil está listo para generar el análisis completo.' : 'Tu perfil todavía no está listo para generar un análisis completo.' }}</p>
            <ul class="mt-4 space-y-3">
                @foreach ($precheck['requirements'] as $requirement)
                    <li class="flex flex-wrap gap-2"><span aria-hidden="true">{{ $requirement['type'] === 'NO_APLICA' ? '—' : ($requirement['complete'] ? '✓' : '○') }}</span><strong>{{ $requirement['label'] }}</strong><span>{{ ['OBLIGATORIO'=>'Obligatorio','RECOMENDADO'=>'Recomendado','OPCIONAL'=>'Opcional','NO_APLICA'=>'No aplica'][$requirement['type']] }} · {{ $requirement['type'] === 'NO_APLICA' ? 'No aplica' : ($requirement['complete'] ? 'Completo' : 'Pendiente') }}</span></li>
                @endforeach
            </ul>
            <div class="flex flex-wrap gap-3 mt-5"><a class="ui-btn-primary" href="{{ route('aula-virtual.estudiante.orientacion.peter3', ['section' => 'riasec']) }}">Completar RIASEC</a><a class="ui-btn-secondary" href="{{ route('estudiante.area', ['area' => 'progreso']) }}">Revisar información académica</a></div>
            <p class="ui-subtitle mt-4">Último RIASEC: {{ $activity?->finalizado_at?->format('d/m/Y H:i') ?? 'Todavía no completado' }}. Último análisis: {{ $activity?->analysis_completed_at?->format('d/m/Y H:i') ?? 'Todavía no generado' }}.</p>
        </section>
    @elseif ($section === 'riasec')
        @if ($activity?->riasec_score)
            <section class="ui-panel"><h2 class="ui-title text-xl font-bold">Tus intereses · {{ $activity->riasec_score['holland_code'] }}</h2><p class="ui-subtitle mt-2">Este resultado representa intereses vocacionales. No mide inteligencia, capacidad ni probabilidad de éxito.</p>
                <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3 mt-4">
                    @foreach ($activity->riasec_score['scores'] as $code => $score)
                        <div><label for="interest-{{ $code }}" class="font-bold">{{ ['R'=>'Realista','I'=>'Investigador','A'=>'Artístico','S'=>'Social','E'=>'Emprendedor','C'=>'Convencional'][$code] }} · {{ $score }}/20</label><meter id="interest-{{ $code }}" class="block w-full h-6" min="0" max="20" value="{{ $score }}">{{ $score }} de 20</meter></div>
                    @endforeach
                </div>
                <ul class="ui-subtitle mt-4 list-disc pl-5">@foreach ($activity->riasec_score['limitations'] as $limitation)<li>{{ $limitation }}</li>@endforeach</ul>
                <details class="mt-3"><summary>Trazabilidad del resultado</summary><p class="break-all text-sm">Versión: {{ $activity->riasec_score['instrument_version'] }} · Referencia: {{ $activity->riasec_score['trace_id'] }}</p></details>
            </section>
        @endif
        @if ($instrument?->available)
            <section class="ui-panel" x-data="{ answered: {{ count(old('responses', [])) }}, submitting: false }">
                <h2 class="ui-title text-xl font-bold">{{ $instrument->data['title'] }}</h2>
                <p class="ui-subtitle mt-3">Responde según cuánto te gustaría realizar cada actividad. No hay respuestas correctas. Completa las 30 preguntas; puedes revisar cualquier respuesta antes de guardar.</p>
                <p class="font-bold mt-3 sticky top-0 p-3 rounded-lg" style="background:var(--ui-surface)" role="status">Progreso: <span x-text="answered">0</span>/30</p>
                <form method="POST" action="{{ route('aula-virtual.estudiante.orientacion.peter3.score') }}" class="mt-5 space-y-5" @submit="submitting = true" @change="answered = $el.querySelectorAll('input[type=radio]:checked').length">
                    @csrf <input type="hidden" name="instrument_version" value="{{ $instrument->data['instrument_version'] }}">
                    @foreach ($instrument->data['items'] as $item)
                        <fieldset class="border rounded-xl p-4" style="border-color:var(--ui-border)"><legend class="font-bold px-2">{{ $item['item_id'] }}. {{ $item['text'] }}</legend><div class="grid gap-2 sm:grid-cols-5">
                            @foreach ($instrument->data['response_scale'] as $option)
                                <label class="flex items-center gap-2 border rounded-lg p-3 cursor-pointer" style="border-color:var(--ui-border)"><input required type="radio" name="responses[{{ $item['item_id'] }}]" value="{{ $option['value'] }}" @checked((string) old('responses.'.$item['item_id']) === (string) $option['value'])><span>{{ $option['label'] }}</span></label>
                            @endforeach
                        </div></fieldset>
                    @endforeach
                    <button class="ui-btn-primary" type="submit" :disabled="submitting || answered !== 30" @disabled(! $storageReady)><span x-show="!submitting">Guardar y ver mis intereses</span><span x-show="submitting" x-cloak>Guardando tus respuestas…</span></button>
                </form>
                <p class="ui-subtitle mt-5">{{ $instrument->data['source_attribution'] }} · <a class="underline" href="{{ $instrument->data['source_url'] }}" rel="noopener noreferrer" target="_blank">Fuente del instrumento</a></p>
            </section>
        @else <p class="ui-panel" role="alert">{{ $instrument?->message ?? 'El instrumento no está disponible en este momento.' }}</p> @endif
    @elseif ($section === 'analisis')
        <section class="ui-panel"><h2 class="ui-title text-xl font-bold">Mi análisis y perfiles de carrera</h2><p class="ui-subtitle mt-2">Afinidad no equivale a preparación. La falta de evidencia no representa bajo nivel ni cero.</p>
            @if (! $precheck['ready'])<p class="mt-4" role="status">Tu perfil todavía no está listo. <a class="underline" href="{{ route('aula-virtual.estudiante.orientacion.peter3') }}">Revisa qué falta</a>.</p>@endif
            <form class="mt-4" method="POST" action="{{ route('aula-virtual.estudiante.orientacion.peter3.analysis') }}" x-data="{ submitting: false }" @submit="submitting = true">@csrf<button type="submit" class="ui-btn-primary" @disabled(! $precheck['ready']) :disabled="submitting"><span x-show="!submitting">Generar análisis con mis datos</span><span x-show="submitting" x-cloak>Analizando tu perfil…</span></button></form>
        </section>
        @if ($activity?->analysis_snapshot)
            <section class="ui-panel"><h2 class="ui-title text-xl font-bold">Evidencia de mi perfil</h2><dl class="grid gap-3 sm:grid-cols-2 mt-4">
                @foreach(['academic_evidence'=>'Datos académicos', 'attendance_evidence'=>'Asistencia', 'learning_activity_evidence'=>'Actividad de aprendizaje', 'historical_evidence'=>'Historia académica', 'technical_evidence'=>'Formación técnica BTH', 'declared_interest_evidence'=>'Intereses declarados'] as $component => $label)
                    <div><dt class="font-bold">{{ $label }}</dt><dd>{{ $statusLabel($activity->analysis_snapshot['student_snapshot'][$component]['status']) }}</dd></div>
                @endforeach
            </dl><p class="ui-subtitle mt-4">Los campos sin evidencia permanecen vacíos; no representan un desempeño de cero.</p></section>
            @foreach ($activity->analysis_snapshot['career_evidence_profiles'] as $career)
                <article class="ui-panel"><h3 class="ui-title text-xl font-bold">{{ $career['career_name'] }}</h3><p class="ui-subtitle">{{ $career['university'] }}</p><dl class="mt-4 grid gap-3 sm:grid-cols-2"><div><dt class="font-bold">Afinidad de intereses</dt><dd>{{ $statusLabel($career['vocational_interest_relation']['status']) }}</dd></div><div><dt class="font-bold">Preparación académica</dt><dd>{{ $career['preparation']['interpretation'] }}</dd></div><div><dt class="font-bold">Cobertura de evidencia</dt><dd>{{ $career['evidence_quality']['academic_record_count'] }} observaciones · {{ $career['evidence_quality']['distinct_subject_count'] }} materias · {{ $career['evidence_quality']['ordered_period_count'] }} periodos ordenados</dd></div><div><dt class="font-bold">BTH relacionado</dt><dd>{{ $statusLabel($career['technical_relation']['status']) }}</dd></div></dl>
                    <details class="mt-4"><summary>Evidencia y áreas de refuerzo</summary><ul class="list-disc pl-5">@foreach($career['preparation']['reinforcement_areas'] as $area)<li>{{ $area['competency'] }}: {{ $area['rationale'] }}</li>@endforeach @foreach($career['areas_without_evidence'] as $area)<li>Sin evidencia observada: {{ $area }}</li>@endforeach</ul></details>
                    <details class="mt-4"><summary>Fuentes y limitaciones</summary><ul class="list-disc pl-5">@foreach($career['sources'] as $source)<li>{{ $source['title'] ?? $source['source_id'] }}</li>@endforeach @foreach($career['limitations'] as $limitation)<li>{{ $limitation['message'] }}</li>@endforeach</ul></details>
                </article>
            @endforeach
            <section class="ui-panel"><h3 class="font-bold">Limitaciones del análisis</h3><ul class="list-disc pl-5">@foreach($activity->analysis_snapshot['limitations'] as $limitation)<li>{{ $limitation['message'] }}</li>@endforeach</ul><details class="mt-3"><summary>Trazabilidad</summary><p class="break-all text-sm">Referencia: {{ $activity->analysis_snapshot['trace_id'] }} · Huella de entrada: {{ $activity->analysis_snapshot['traceability']['input_hash'] }}</p></details></section>
        @else <p class="ui-panel">Todavía no tienes un análisis guardado.</p> @endif
    @else
        <section class="ui-panel"><h2 class="ui-title text-xl font-bold">{{ $section === 'fuentes' ? 'Fuentes e información' : 'Tutor académico-vocacional' }}</h2><p class="ui-subtitle mt-2">{{ $section === 'fuentes' ? 'Busca fragmentos de las fuentes oficiales del catálogo. La búsqueda puede omitir evidencia.' : 'El tutor responde en modo estructurado con fuentes. Puede abstenerse cuando la evidencia no sea suficiente.' }}</p>
            <form class="space-y-3 mt-4" method="POST" action="{{ route('aula-virtual.estudiante.orientacion.peter3.query') }}" x-data="{submitting:false}" @submit="submitting=true">@csrf<input type="hidden" name="mode" value="{{ $section }}"><label for="orientation-question" class="block font-bold">{{ $section === 'fuentes' ? 'Tu consulta' : 'Tu pregunta' }}</label><textarea id="orientation-question" class="ui-input w-full" name="question" minlength="2" maxlength="1000" rows="3" required>{{ $question ?? old('question') }}</textarea><button type="submit" class="ui-btn-primary" :disabled="submitting"><span x-show="!submitting">Consultar</span><span x-show="submitting" x-cloak>Consultando fuentes…</span></button></form>
        </section>
        @isset($queryResult)
            @if (! $queryResult->available)<p class="ui-panel" role="alert">{{ $queryResult->message }}</p>
            @else
                <section class="ui-panel" role="status">
                    @if($section === 'tutor')<p class="whitespace-pre-wrap">{{ $queryResult->data['answer'] }}</p>@endif
                    @if($queryResult->data['insufficient_evidence'])<p class="font-bold mt-3">La evidencia disponible es insuficiente para responder con certeza.</p>@endif
                    @foreach($queryResult->data['results'] ?? $queryResult->data['sources'] as $source)<article class="border rounded-xl p-4 mt-4" style="border-color:var(--ui-border)"><h3 class="font-bold">{{ $source['title'] }}</h3><p class="ui-subtitle">{{ $source['institution'] }} · {{ $source['official'] ? 'Fuente oficial' : 'Revisar procedencia' }}</p><p class="mt-2">{{ $source['summary'] }}</p><p class="text-sm mt-2 break-words">{{ $source['reference'] }}</p></article>@endforeach
                    <ul class="ui-subtitle mt-4 list-disc pl-5">@foreach($queryResult->data['warnings'] as $warning)<li>{{ $warning }}</li>@endforeach</ul><p class="ui-subtitle mt-4">El soporte semántico de las citas no ha sido evaluado. El catálogo no es exhaustivo.</p><details class="mt-3"><summary>Trazabilidad</summary><p class="break-all text-sm">Referencia: {{ $queryResult->traceId }}</p></details>
                </section>
            @endif
        @endisset
    @endif
</div>
@endsection
