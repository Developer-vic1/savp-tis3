@extends('aula-virtual.layouts.app')

@section('title', 'Mis intereses | SAVP-TIS3')
@section('page-title', 'Mis intereses')

@section('content')
@php
    $riasecNames = ['R' => 'Realista', 'I' => 'Investigador', 'A' => 'Artístico', 'S' => 'Social', 'E' => 'Emprendedor', 'C' => 'Convencional'];
    $storedResponses = collect(data_get($activity?->riasec_public, 'responses', []))
        ->filter(fn ($response) => isset($response['item_id'], $response['value']))
        ->mapWithKeys(fn ($response) => [(string) $response['item_id'] => $response['value']])
        ->all();
    $formResponses = old('responses', $storedResponses);
    $riasecScore = data_get($activity?->riasec_score, 'scores', []);
    $hollandCode = data_get($activity?->riasec_score, 'holland_code');
    $analysis = $activity?->analysis_snapshot;
    $profileReady = (bool) data_get($precheck ?? [], 'ready', false);
    $orientationNotice = session('orientation_notice');
    $careerOptions = collect(data_get($analysis, 'career_evidence_profiles', []))
        ->filter(fn ($career) => in_array(data_get($career, 'vocational_interest_relation.status'), ['AVAILABLE', 'PARTIAL'], true))
        ->unique(fn ($career) => mb_strtolower(trim((string) data_get($career, 'career_name'))))
        ->take(3)
        ->values();
    $analysisGuidance = collect(data_get($analysis, 'limitations', []))->map(function ($limitation) {
        return match (data_get($limitation, 'code')) {
            'DECISION_SUPPORT_ONLY' => ['title' => 'Tú tomas la decisión final', 'message' => 'El análisis organiza información para ayudarte a reflexionar, pero no elige una carrera por ti.'],
            'NO_SUCCESS_PROBABILITY' => ['title' => 'Es una guía, no una predicción', 'message' => 'El resultado no puede asegurar cómo te irá en la universidad. Tus intereses y tu preparación pueden cambiar.'],
            default => null,
        };
    })->filter()->values();
@endphp

<div class="mx-auto max-w-6xl space-y-6">
    <section class="ui-panel">
        <p class="ui-kicker">Análisis de intereses</p>
        <h1 class="ui-title mt-2 text-3xl font-black">Comprende tus intereses antes de explorar carreras</h1>
        <p class="ui-subtitle mt-3 max-w-3xl leading-7">
            Este espacio contiene únicamente tu cuestionario RIASEC y la interpretación de tu perfil.
            Las universidades y las mallas curriculares se consultan por separado en “Mi futuro académico”.
        </p>
    </section>

    @if ($orientationNotice)
        <section class="rounded-2xl border border-amber-200 bg-amber-50 p-5 text-amber-900 dark:border-amber-800/60 dark:bg-amber-950/30 dark:text-amber-100" role="status">
            <div class="flex items-start gap-3">
                <i class="ph ph-cloud-slash mt-0.5 text-2xl" aria-hidden="true"></i>
                <div>
                    <h2 class="font-black">{{ data_get($orientationNotice, 'title') }}</h2>
                    <p class="mt-2 text-sm leading-6">{{ data_get($orientationNotice, 'message') }}</p>
                    <p class="mt-2 text-sm font-bold">{{ data_get($orientationNotice, 'preserved') }}</p>
                    <p class="mt-1 text-sm leading-6">{{ data_get($orientationNotice, 'next_step') }}</p>
                </div>
            </div>
        </section>
    @endif

    @if ($activity?->riasec_score)
        <section class="ui-panel">
            <div class="flex flex-wrap items-start justify-between gap-4">
                <div>
                    <p class="ui-kicker">Resultado guardado</p>
                    <h2 class="ui-title mt-1 text-2xl font-black">Mi perfil de intereses</h2>
                    <p class="ui-subtitle mt-2 max-w-3xl">Representa preferencias vocacionales; no mide inteligencia, aptitud ni probabilidad de éxito.</p>
                </div>
                @if ($hollandCode)
                    <div class="rounded-2xl border px-6 py-4 text-center" style="border-color: var(--ui-border);">
                        <span class="block text-xs font-bold uppercase tracking-wider" style="color: var(--ui-muted);">Código Holland</span>
                        <strong class="mt-1 block text-3xl font-black tracking-widest">{{ $hollandCode }}</strong>
                    </div>
                @endif
            </div>

            <div class="mt-6 grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
                @foreach ($riasecNames as $code => $name)
                    @php $score = (int) data_get($riasecScore, $code, 0); @endphp
                    <article class="rounded-2xl border p-4" style="border-color: var(--ui-border);">
                        <div class="flex items-center justify-between gap-3">
                            <div><span class="text-xs font-black" style="color: var(--ui-muted);">{{ $code }}</span><h3 class="font-bold">{{ $name }}</h3></div>
                            <strong class="text-xl">{{ $score }}<span class="text-sm font-medium" style="color: var(--ui-muted);">/20</span></strong>
                        </div>
                        <meter class="mt-4 block h-3 w-full" min="0" max="20" value="{{ $score }}" aria-label="{{ $name }}: {{ $score }} de 20">{{ $score }} de 20</meter>
                    </article>
                @endforeach
            </div>
        </section>
    @endif

    @if ($instrument?->available)
        @php
            $instrumentItems = collect(data_get($instrument->data, 'items', []));
            $responseScale = data_get($instrument->data, 'response_scale', []);
        @endphp
        <section class="ui-panel">
            <p class="ui-kicker">Cuestionario RIASEC</p>
            <h2 class="ui-title mt-1 text-2xl font-black">{{ data_get($instrument->data, 'title', 'Perfil de intereses') }}</h2>
            <p class="ui-subtitle mt-2 max-w-3xl">Responde según lo que realmente te gustaría hacer. No existen respuestas correctas o incorrectas.</p>

            <form method="POST" action="{{ route('estudiante.intereses.guardar') }}" class="mt-6 space-y-4" x-data="{ submitting: false }" @submit="submitting = true">
                @csrf
                <input type="hidden" name="instrument_version" value="{{ data_get($instrument->data, 'instrument_version') }}">
                @foreach ($instrumentItems as $item)
                    @php
                        $itemId = data_get($item, 'item_id');
                        $currentValue = data_get($formResponses, (string) $itemId);
                    @endphp
                    <fieldset class="rounded-2xl border p-4 sm:p-5" style="border-color: var(--ui-border);">
                        <legend class="px-2 font-bold leading-6"><span class="mr-2 rounded-lg px-2 py-1 text-sm" style="background: var(--ui-soft);">{{ $itemId }}</span>{{ data_get($item, 'text') }}</legend>
                        <div class="mt-4 grid gap-2 sm:grid-cols-5">
                            @foreach ($responseScale as $option)
                                @php $optionValue = data_get($option, 'value'); $optionId = 'interest-'.$itemId.'-'.$optionValue; @endphp
                                <label for="{{ $optionId }}" class="flex min-h-[64px] cursor-pointer items-center gap-3 rounded-xl border p-3 has-[:checked]:border-sky-500 has-[:checked]:bg-sky-50 dark:has-[:checked]:bg-sky-950/30" style="border-color: var(--ui-border);">
                                    <input id="{{ $optionId }}" required type="radio" name="responses[{{ $itemId }}]" value="{{ $optionValue }}" @checked((string) $currentValue === (string) $optionValue)>
                                    <span class="text-sm font-medium">{{ data_get($option, 'label') }}</span>
                                </label>
                            @endforeach
                        </div>
                    </fieldset>
                @endforeach
                <button class="ui-btn-primary" type="submit" :disabled="submitting" @disabled(!$storageReady)>
                    <i class="ph ph-floppy-disk" aria-hidden="true"></i>
                    <span x-show="!submitting">Guardar mis intereses</span><span x-show="submitting" x-cloak>Procesando…</span>
                </button>
            </form>
        </section>
    @else
        <section class="ui-panel" role="status">
            <div class="flex items-start gap-3">
                <i class="ph ph-warning-circle mt-0.5 text-xl" aria-hidden="true" style="color: var(--ui-primary);"></i>
                <div>
                    <h2 class="font-bold">El cuestionario no está disponible en este momento</h2>
                    <p class="ui-subtitle mt-1 text-sm leading-6">Tu último resultado permanece guardado. Podrás actualizarlo cuando el servicio de análisis vuelva a estar disponible.</p>
                </div>
            </div>
        </section>
    @endif

    <section class="ui-panel">
        <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
            <div>
                <p class="ui-kicker">Interpretación personal</p>
                <h2 class="ui-title mt-1 text-2xl font-black">Mi análisis</h2>
                <p class="ui-subtitle mt-2 max-w-3xl">Combina tus intereses con la información académica disponible, sin mezclar universidades ni convertirlo en un ranking.</p>
            </div>
            <form method="POST" action="{{ route('estudiante.intereses.analizar') }}" x-data="{ submitting: false }" @submit="submitting = true">
                @csrf
                <button class="ui-btn-primary" type="submit" @disabled(!$profileReady) :disabled="submitting">
                    <i class="ph ph-sparkle" aria-hidden="true"></i>{{ $analysis ? 'Actualizar mi análisis' : 'Generar mi análisis' }}
                </button>
            </form>
        </div>

        @if (!$profileReady)
            <div class="mt-5 rounded-2xl border border-amber-200 bg-amber-50 p-4 text-amber-800 dark:border-amber-800/60 dark:bg-amber-950/30 dark:text-amber-200">Aún faltan requisitos obligatorios para generar el análisis.</div>
        @elseif ($analysis)
            <div class="mt-5 grid gap-3 md:grid-cols-2">
                @forelse ($analysisGuidance as $guidance)
                    <article class="rounded-2xl border p-4" style="border-color: var(--ui-border); background: var(--ui-soft);"><h3 class="font-bold">{{ $guidance['title'] }}</h3><p class="ui-subtitle mt-2 text-sm leading-6">{{ $guidance['message'] }}</p></article>
                @empty
                    <p class="ui-subtitle">Tu análisis está disponible para orientar la exploración.</p>
                @endforelse
            </div>
        @endif
    </section>

    @if ($analysis)
        <section class="ui-panel">
            <div class="flex flex-col gap-3 lg:flex-row lg:items-start lg:justify-between">
                <div>
                    <p class="ui-kicker">Tres rutas para comenzar</p>
                    <h2 class="ui-title mt-1 text-2xl font-black">Carreras relacionadas con tus intereses RIASEC</h2>
                    <p class="ui-subtitle mt-2 max-w-3xl leading-6">No es un ranking ni una decisión automática. Son hasta tres opciones con relación documentada para que investigues qué se estudia y luego elijas qué universidades quieres revisar.</p>
                </div>
                @if($hollandCode)<span class="rounded-full border px-3 py-1.5 text-sm font-bold" style="border-color: var(--ui-border);">Tu patrón observado: {{ $hollandCode }}</span>@endif
            </div>

            <div class="mt-6 grid gap-4 lg:grid-cols-3">
                @forelse($careerOptions as $career)
                    @php
                        $relationStatus = data_get($career, 'vocational_interest_relation.status');
                        $areas = collect(data_get($career, 'academic_program.knowledge_areas', []))->filter()->take(3);
                    @endphp
                    <article class="rounded-2xl border p-5" style="border-color: var(--ui-border);">
                        <span class="inline-flex rounded-full px-3 py-1 text-xs font-black" style="background: var(--ui-soft); color: var(--ui-primary);">Opción para explorar</span>
                        <h3 class="ui-title mt-3 text-xl font-black">{{ data_get($career, 'career_name', 'Carrera por explorar') }}</h3>
                        <p class="ui-subtitle mt-3 text-sm leading-6">
                            {{ $relationStatus === 'AVAILABLE'
                                ? 'Existe una relación documentada entre tu patrón de intereses y referencias ocupacionales vinculadas con esta carrera.'
                                : 'Hay señales relacionadas con tu patrón de intereses, aunque la evidencia disponible todavía es parcial.' }}
                        </p>
                        @if($areas->isNotEmpty())
                            <div class="mt-4"><p class="text-sm font-bold">Áreas que podrías conocer</p><div class="mt-2 flex flex-wrap gap-2">@foreach($areas as $area)<span class="rounded-full border px-3 py-1 text-xs" style="border-color: var(--ui-border);">{{ $area }}</span>@endforeach</div></div>
                        @endif
                    </article>
                @empty
                    <div class="rounded-2xl border border-dashed p-6 text-center lg:col-span-3" style="border-color: var(--ui-border);"><h3 class="font-black">Aún no hay tres relaciones documentadas</h3><p class="ui-subtitle mt-2">Tu perfil permanece guardado. El sistema mostrará opciones cuando exista evidencia suficiente para explicarlas sin inventar.</p></div>
                @endforelse
            </div>

            <div class="mt-6 flex flex-col gap-3 rounded-2xl border p-5 sm:flex-row sm:items-center sm:justify-between" style="border-color: var(--ui-border); background: var(--ui-soft);">
                <div><h3 class="font-black">Ahora elige dónde quieres estudiar</h3><p class="ui-subtitle mt-1 text-sm">Las universidades, mallas y fuentes oficiales están en una ventana separada.</p></div>
                <a href="{{ route('estudiante.futuro', ['explore' => 'analysis']) }}" class="ui-btn-primary inline-flex shrink-0"><i class="ph ph-buildings" aria-hidden="true"></i>Ver universidades para estas carreras</a>
            </div>
        </section>
    @endif
</div>
@endsection
