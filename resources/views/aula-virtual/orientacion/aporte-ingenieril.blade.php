@extends('aula-virtual.layouts.app')

@section('title', 'Mi orientación | SAVP-TIS3')
@section('page-title', 'Mi orientación')

@section('content')
@php
    /*
    |--------------------------------------------------------------------------
    | Configuración visual local
    |--------------------------------------------------------------------------
    | Esta vista únicamente interpreta datos ya validados por el backend.
    | No calcula RIASEC, no determina preparación y no inventa evidencia.
    */

    $sections = [
        'perfil' => [
            'label' => 'Estado de mi perfil',
            'short' => 'Perfil',
            'icon' => 'ph-user-circle',
            'description' => 'Revisa si tienes la información mínima para continuar.',
        ],
        'riasec' => [
            'label' => 'Intereses RIASEC',
            'short' => 'RIASEC',
            'icon' => 'ph-compass',
            'description' => 'Explora tus intereses mediante el cuestionario vocacional.',
        ],
        'analisis' => [
            'label' => 'Mi análisis y carreras',
            'short' => 'Análisis',
            'icon' => 'ph-chart-line-up',
            'description' => 'Consulta la evidencia académica y vocacional disponible.',
        ],
        'fuentes' => [
            'label' => 'Fuentes e información',
            'short' => 'Fuentes',
            'icon' => 'ph-books',
            'description' => 'Consulta información recuperada desde fuentes documentadas.',
        ],
        'tutor' => [
            'label' => 'Tutor académico-vocacional',
            'short' => 'Tutor',
            'icon' => 'ph-chats-circle',
            'description' => 'Formula preguntas sobre carreras y evidencia disponible.',
        ],
    ];

    $statusMeta = [
        'AVAILABLE' => [
            'label' => 'Disponible',
            'class' => 'border-emerald-200 bg-emerald-50 text-emerald-700 dark:border-emerald-800/60 dark:bg-emerald-950/30 dark:text-emerald-300',
            'icon' => 'ph-check-circle',
        ],
        'PARTIAL' => [
            'label' => 'Parcial',
            'class' => 'border-amber-200 bg-amber-50 text-amber-700 dark:border-amber-800/60 dark:bg-amber-950/30 dark:text-amber-300',
            'icon' => 'ph-warning-circle',
        ],
        'INSUFFICIENT' => [
            'label' => 'Insuficiente',
            'class' => 'border-orange-200 bg-orange-50 text-orange-700 dark:border-orange-800/60 dark:bg-orange-950/30 dark:text-orange-300',
            'icon' => 'ph-info',
        ],
        'UNAVAILABLE' => [
            'label' => 'Sin evidencia disponible',
            'class' => 'border-slate-200 bg-slate-50 text-slate-600 dark:border-slate-700 dark:bg-slate-900/50 dark:text-slate-300',
            'icon' => 'ph-minus-circle',
        ],
    ];

    $requirementMeta = [
        'OBLIGATORIO' => [
            'label' => 'Obligatorio',
            'class' => 'border-rose-200 bg-rose-50 text-rose-700 dark:border-rose-800/60 dark:bg-rose-950/30 dark:text-rose-300',
        ],
        'RECOMENDADO' => [
            'label' => 'Recomendado',
            'class' => 'border-amber-200 bg-amber-50 text-amber-700 dark:border-amber-800/60 dark:bg-amber-950/30 dark:text-amber-300',
        ],
        'OPCIONAL' => [
            'label' => 'Opcional',
            'class' => 'border-sky-200 bg-sky-50 text-sky-700 dark:border-sky-800/60 dark:bg-sky-950/30 dark:text-sky-300',
        ],
        'NO_APLICA' => [
            'label' => 'No aplica',
            'class' => 'border-slate-200 bg-slate-50 text-slate-600 dark:border-slate-700 dark:bg-slate-900/50 dark:text-slate-300',
        ],
    ];

    $riasecNames = [
        'R' => 'Realista',
        'I' => 'Investigador',
        'A' => 'Artístico',
        'S' => 'Social',
        'E' => 'Emprendedor',
        'C' => 'Convencional',
    ];

    $requirements = collect(data_get($precheck ?? [], 'requirements', []));
    $requiredItems = $requirements->where('type', 'OBLIGATORIO');

    $requiredCompleted = $requiredItems
        ->filter(fn($item) => (bool) data_get($item, 'complete'))
        ->count();

    $requiredTotal = $requiredItems->count();

    $requiredProgress = $requiredTotal > 0
        ? (int) round(($requiredCompleted / $requiredTotal) * 100)
        : 100;

    $profileReady = (bool) data_get($precheck ?? [], 'ready', false);

    /*
    |--------------------------------------------------------------------------
    | Respuestas previamente guardadas
    |--------------------------------------------------------------------------
    | Se utilizan solo como preselección visual.
    | El cálculo sigue realizándose exclusivamente en el backend analítico.
    */

    $storedResponses = collect(data_get($activity?->riasec_public, 'responses', []))
        ->filter(fn($response) => isset($response['item_id'], $response['value']))
        ->mapWithKeys(fn($response) => [
            (string) $response['item_id'] => $response['value'],
        ])
        ->all();

    $formResponses = old('responses', $storedResponses);

    $riasecScore = data_get($activity?->riasec_score, 'scores', []);
    $hollandCode = data_get($activity?->riasec_score, 'holland_code');
    $riasecLimitations = data_get($activity?->riasec_score, 'limitations', []);

    $analysis = $activity?->analysis_snapshot;
    $careerProfiles = data_get($analysis, 'career_evidence_profiles', []);
    $externalCareerProfiles = data_get($analysis, 'informational_external_careers', []);
    $studentSnapshot = data_get($analysis, 'student_snapshot', []);
    $analysisLimitations = data_get($analysis, 'limitations', []);
    $studentAnalysisGuidance = collect($analysisLimitations)
        ->map(function ($limitation) {
            return match (data_get($limitation, 'code')) {
                'DECISION_SUPPORT_ONLY' => [
                    'icon' => 'ph-compass',
                    'title' => 'Tú tomas la decisión final',
                    'message' => 'Usa estos resultados para explorar y conversar con personas de confianza. El sistema organiza evidencia, pero no elige una carrera por ti.',
                ],
                'NO_SUCCESS_PROBABILITY' => [
                    'icon' => 'ph-path',
                    'title' => 'Es una guía, no una predicción',
                    'message' => 'Tus intereses y tu preparación pueden cambiar. El análisis no puede asegurar cómo te irá en la universidad.',
                ],
                default => null,
            };
        })
        ->filter()
        ->values();
    $analysisStatusLabels = [
        'COMPLETE' => 'Análisis listo',
        'PARTIAL' => 'Análisis parcial',
        'INSUFFICIENT' => 'Faltan datos para completar el análisis',
    ];
    $explorationMode = (string) request('explore', 'analysis');
    $explorationModes = [
        'analysis' => [
            'label' => 'Según mi análisis',
            'icon' => 'ph-sparkle',
            'description' => 'Opciones con alguna relación documentada con tus intereses o preparación.',
        ],
        'universities' => [
            'label' => 'Explorar por universidad',
            'icon' => 'ph-buildings',
            'description' => 'Elige una universidad y revisa las carreras documentadas que ofrece.',
        ],
        'all' => [
            'label' => 'Todas las carreras',
            'icon' => 'ph-books',
            'description' => 'Consulta el catálogo completo, incluso opciones con evidencia todavía limitada.',
        ],
    ];

    $careerKeyFor = static function ($career): string {
        $careerName = trim((string) data_get($career, 'career_name', ''));

        return $careerName !== ''
            ? \Illuminate\Support\Str::slug($careerName)
            : (string) data_get($career, 'career_id', '');
    };
    $careerCatalog = collect($careerProfiles)
        ->map(fn ($career) => [
            'id' => (string) data_get($career, 'career_id', ''),
            'key' => $careerKeyFor($career),
            'name' => (string) data_get($career, 'career_name', 'Carrera'),
            'university' => (string) data_get($career, 'university', ''),
        ])
        ->merge(collect($externalCareerProfiles)->map(fn ($career) => [
            'id' => (string) data_get($career, 'career_id', ''),
            'key' => $careerKeyFor($career),
            'name' => (string) data_get($career, 'career_name', 'Carrera'),
            'university' => (string) data_get($career, 'university', ''),
        ]))
        ->filter(fn ($career) => $career['id'] !== '' && $career['key'] !== '')
        ->values();
    $availableCareers = $careerCatalog
        ->groupBy('key')
        ->map(fn ($options, $key) => [
            'key' => (string) $key,
            'name' => (string) data_get($options->first(), 'name', 'Carrera'),
            'universities' => $options->pluck('university')->filter()->unique()->sort()->values(),
        ])
        ->sortBy('name', SORT_NATURAL | SORT_FLAG_CASE)
        ->values();
    $allUniversities = $careerCatalog
        ->pluck('university')
        ->filter()
        ->unique()
        ->sort()
        ->values();
    $requestedCareer = trim((string) request('career', ''));
    $legacyCareerOption = $careerCatalog->first(fn ($career) => $career['id'] === $requestedCareer);
    $selectedCareer = (string) data_get($legacyCareerOption, 'key', $requestedCareer);
    if ($selectedCareer !== '' && ! $availableCareers->contains(fn ($career) => $career['key'] === $selectedCareer)) {
        $selectedCareer = '';
    }
    if ($explorationMode !== 'all') {
        $selectedCareer = '';
    }
    $availableUniversities = match ($explorationMode) {
        'universities' => $allUniversities,
        'all' => $selectedCareer === ''
            ? collect()
            : $careerCatalog
            ->where('key', $selectedCareer)
            ->pluck('university')
            ->filter()
            ->unique()
            ->sort()
            ->values(),
        default => collect(),
    };
    $requestedUniversity = trim((string) request('university', ''));
    $selectedUniversity = $availableUniversities->contains($requestedUniversity)
        ? $requestedUniversity
        : '';
    $selectedComparisonIds = collect((array) request()->query('compare', []))
        ->filter(fn ($careerId) => is_string($careerId) && $careerId !== '')
        ->unique()
        ->intersect(collect($careerProfiles)->pluck('career_id')->filter())
        ->take(3)
        ->values();
    $comparisonProfiles = collect($careerProfiles)
        ->filter(fn ($career) => $selectedComparisonIds->contains(data_get($career, 'career_id')))
        ->sortBy(fn ($career) => data_get($career, 'university').'|'.data_get($career, 'career_name'))
        ->values();
    $analysisBaseQuery = collect([
        'section' => 'analisis',
        'explore' => $explorationMode !== 'analysis' ? $explorationMode : null,
        'career' => $selectedCareer !== '' ? $selectedCareer : null,
        'university' => $selectedUniversity !== '' ? $selectedUniversity : null,
        'compare' => $selectedComparisonIds->isNotEmpty() ? $selectedComparisonIds->all() : null,
    ])->filter(fn ($value) => $value !== null && $value !== '')->all();
    $analysisUrl = function (array $overrides = [], array $remove = []) use ($analysisBaseQuery) {
        $query = array_merge($analysisBaseQuery, $overrides);
        foreach ($remove as $key) {
            unset($query[$key]);
        }

        return route('aula-virtual.estudiante.orientacion.aporte', $query);
    };
    $isRelatedToAnalysis = static fn ($career): bool => in_array(
        data_get($career, 'vocational_interest_relation.status'),
        ['AVAILABLE', 'PARTIAL'],
        true,
    ) || (int) data_get($career, 'preparation.relations_with_observed_academic_evidence', 0) > 0
        || (int) data_get($career, 'preparation.related_relations', 0) > 0;
    $visibleCareerProfiles = match ($explorationMode) {
        'analysis' => collect($careerProfiles)->filter($isRelatedToAnalysis)->values(),
        'universities' => $selectedUniversity === ''
            ? collect()
            : collect($careerProfiles)
                ->filter(fn ($career) => data_get($career, 'university') === $selectedUniversity)
                ->values(),
        default => collect($careerProfiles)
            ->when($selectedCareer !== '', fn ($profiles) => $profiles->filter(
                fn ($career) => $careerKeyFor($career) === $selectedCareer
            ))
            ->when($selectedUniversity !== '', fn ($profiles) => $profiles->filter(
                fn ($career) => data_get($career, 'university') === $selectedUniversity
            ))
            ->values(),
    };
    $visibleExternalCareers = match ($explorationMode) {
        'analysis' => collect(),
        'universities' => $selectedUniversity === ''
            ? collect()
            : collect($externalCareerProfiles)
                ->filter(fn ($career) => data_get($career, 'university') === $selectedUniversity)
                ->values(),
        default => collect($externalCareerProfiles)
            ->when($selectedCareer !== '', fn ($profiles) => $profiles->filter(
                fn ($career) => $careerKeyFor($career) === $selectedCareer
            ))
            ->when($selectedUniversity !== '', fn ($profiles) => $profiles->filter(
                fn ($career) => data_get($career, 'university') === $selectedUniversity
            ))
            ->values(),
    };
    $visibleOptionCount = $visibleCareerProfiles->count() + $visibleExternalCareers->count();

    $queryData = isset($queryResult) ? ($queryResult->data ?? []) : [];
    $querySources = data_get($queryData, 'results', data_get($queryData, 'sources', []));
    $queryWarnings = data_get($queryData, 'warnings', []);
    $queryInsufficient = (bool) data_get($queryData, 'insufficient_evidence', false);
    $querySuggestedTopics = data_get($queryData, 'suggested_topics', []);
    $queryAnswerMode = data_get($queryData, 'answer_mode');
    $conversationHistory = $conversationHistory ?? [];

    $currentSection = $sections[$section] ?? $sections['perfil'];
@endphp

<div class="mx-auto max-w-7xl space-y-6">

    {{-- =========================================================
    HERO / ENCABEZADO
    ========================================================== --}}
    <section class="ui-panel overflow-hidden">
        <div class="flex flex-col gap-5 lg:flex-row lg:items-start lg:justify-between">
            <div class="max-w-3xl">
                <div class="mb-3 flex flex-wrap items-center gap-2">
                    <span
                        class="inline-flex items-center gap-2 rounded-full border px-3 py-1 text-xs font-bold uppercase tracking-wide"
                        style="border-color: var(--ui-border); color: var(--ui-muted);">
                        <i class="ph ph-compass" aria-hidden="true"></i>
                        Orientación académica y vocacional
                    </span>

                    @if ($profileReady)
                        <span
                            class="inline-flex items-center gap-2 rounded-full border border-emerald-200 bg-emerald-50 px-3 py-1 text-xs font-bold text-emerald-700 dark:border-emerald-800/60 dark:bg-emerald-950/30 dark:text-emerald-300">
                            <i class="ph ph-check-circle" aria-hidden="true"></i>
                            Perfil listo
                        </span>
                    @else
                        <span
                            class="inline-flex items-center gap-2 rounded-full border border-amber-200 bg-amber-50 px-3 py-1 text-xs font-bold text-amber-700 dark:border-amber-800/60 dark:bg-amber-950/30 dark:text-amber-300">
                            <i class="ph ph-warning-circle" aria-hidden="true"></i>
                            Información pendiente
                        </span>
                    @endif
                </div>

                <h1 class="ui-title text-2xl font-black sm:text-3xl">
                    Conoce tus intereses y explora tus opciones
                </h1>

                <p class="ui-subtitle mt-3 max-w-3xl leading-7">
                    El sistema analiza por separado tus intereses, tu preparación académica
                    observada y la evidencia disponible. No predice tu éxito, no mide tu
                    inteligencia y no decide una carrera por ti.
                </p>
            </div>

            <div class="min-w-[220px] rounded-2xl border p-4"
                style="border-color: var(--ui-border); background: var(--ui-surface);">
                <div class="flex items-center justify-between gap-3">
                    <span class="text-sm font-semibold">Requisitos obligatorios</span>
                    <span class="text-sm font-black">
                        {{ $requiredCompleted }}/{{ $requiredTotal }}
                    </span>
                </div>

                <div class="mt-3 h-2.5 overflow-hidden rounded-full" style="background: var(--ui-border);"
                    aria-hidden="true">
                    <div class="h-full rounded-full bg-emerald-500 transition-all"
                        style="width: {{ $requiredProgress }}%"></div>
                </div>

                <p class="ui-subtitle mt-2 text-xs">
                    {{ $requiredProgress }}% de los requisitos mínimos completados.
                </p>
            </div>
        </div>

        {{-- Navegación principal --}}
        <nav aria-label="Secciones de orientación" class="mt-6 grid gap-2 sm:grid-cols-2 lg:grid-cols-5">
            @foreach ($sections as $key => $item)
            @php
                $active = $section === $key;
            @endphp

            <a href="{{ route('aula-virtual.estudiante.orientacion.aporte', ['section' => $key]) }}" @if($active)
            aria-current="page" @endif @class([
                    'group flex min-h-[72px] items-center gap-3 rounded-xl border px-4 py-3 transition',
                    'border-slate-200 hover:border-slate-300 hover:bg-slate-50 dark:border-slate-700 dark:hover:bg-slate-900/50' => !$active,
                    'border-transparent bg-slate-900 text-white shadow-sm dark:bg-white dark:text-slate-900' => $active,
                ])>
                <span @class([
                    'flex h-9 w-9 shrink-0 items-center justify-center rounded-lg',
                    'bg-white/10 dark:bg-black/5' => $active,
                    'bg-slate-100 text-slate-700 dark:bg-slate-800 dark:text-slate-200' => !$active,
                ])>
                    <i class="ph {{ $item['icon'] }} text-lg" aria-hidden="true"></i>
                </span>

                <span class="min-w-0">
                    <span class="block text-sm font-bold leading-tight">
                        {{ $item['short'] }}
                    </span>

                    <span @class([
                        'mt-1 hidden text-xs leading-tight lg:block',
                        'text-white/70 dark:text-slate-600' => $active,
                        'text-slate-500 dark:text-slate-400' => !$active,
                    ])>
                        {{ $item['description'] }}
                    </span>
                </span>
            </a>
            @endforeach
        </nav>
    </section>

    {{-- =========================================================
    MENSAJES GENERALES
    ========================================================== --}}
    @if (session('status'))
        <section
            class="rounded-2xl border border-emerald-200 bg-emerald-50 p-4 text-emerald-800 dark:border-emerald-800/60 dark:bg-emerald-950/30 dark:text-emerald-200"
            role="status">
            <div class="flex items-start gap-3">
                <i class="ph ph-check-circle mt-0.5 text-xl" aria-hidden="true"></i>
                <div>
                    <h2 class="font-bold">Proceso completado</h2>
                    <p class="mt-1 text-sm">{{ session('status') }}</p>
                </div>
            </div>
        </section>
    @endif

    @if ($errors->any())
        <section id="orientation-errors"
            class="rounded-2xl border border-rose-200 bg-rose-50 p-4 text-rose-800 dark:border-rose-800/60 dark:bg-rose-950/30 dark:text-rose-200"
            role="alert" tabindex="-1">
            <div class="flex items-start gap-3">
                <i class="ph ph-warning-circle mt-0.5 text-xl" aria-hidden="true"></i>

                <div>
                    <h2 class="font-bold">
                        Revisa la información antes de continuar
                    </h2>

                    <ul class="mt-2 list-disc space-y-1 pl-5 text-sm">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            </div>
        </section>
    @endif

    @if (!$storageReady)
        <section
            class="rounded-2xl border border-amber-200 bg-amber-50 p-4 text-amber-800 dark:border-amber-800/60 dark:bg-amber-950/30 dark:text-amber-200"
            role="alert">
            <div class="flex items-start gap-3">
                <i class="ph ph-database mt-0.5 text-xl" aria-hidden="true"></i>

                <div>
                    <h2 class="font-bold">
                        La orientación todavía no puede guardar resultados
                    </h2>

                    <p class="mt-1 text-sm leading-6">
                        La estructura de persistencia aún no está habilitada en este entorno.
                        No completes el cuestionario hasta que el responsable del sistema
                        habilite el almacenamiento correspondiente.
                    </p>
                </div>
            </div>
        </section>
    @endif


    {{-- =========================================================
    PERFIL / PRECHECK
    ========================================================== --}}
    @if ($section === 'perfil')

        <section class="grid gap-6 lg:grid-cols-[1.4fr_.6fr]">

            <div class="ui-panel">
                <div class="flex flex-wrap items-start justify-between gap-4">
                    <div>
                        <p class="ui-kicker">Preparación del perfil</p>

                        <h2 class="ui-title mt-1 text-xl font-black">
                            ¿Tengo la información necesaria para comenzar?
                        </h2>

                        <p class="ui-subtitle mt-2 leading-6">
                            Antes de generar el análisis se verifica que existan los datos
                            mínimos necesarios. Los datos ausentes nunca se convierten
                            automáticamente en cero.
                        </p>
                    </div>

                    @if ($profileReady)
                        <span
                            class="inline-flex items-center gap-2 rounded-full border border-emerald-200 bg-emerald-50 px-3 py-1.5 text-sm font-bold text-emerald-700 dark:border-emerald-800/60 dark:bg-emerald-950/30 dark:text-emerald-300">
                            <i class="ph ph-check-circle" aria-hidden="true"></i>
                            Listo para analizar
                        </span>
                    @else
                        <span
                            class="inline-flex items-center gap-2 rounded-full border border-amber-200 bg-amber-50 px-3 py-1.5 text-sm font-bold text-amber-700 dark:border-amber-800/60 dark:bg-amber-950/30 dark:text-amber-300">
                            <i class="ph ph-warning" aria-hidden="true"></i>
                            Completa los pendientes
                        </span>
                    @endif
                </div>

                <div class="mt-6 space-y-3">
                    @forelse ($requirements as $requirement)
                        @php
                            $type = data_get($requirement, 'type', 'OPCIONAL');
                            $complete = (bool) data_get($requirement, 'complete', false);
                            $meta = $requirementMeta[$type] ?? $requirementMeta['OPCIONAL'];

                            $requirementIcon = $type === 'NO_APLICA'
                                ? 'ph-minus'
                                : ($complete ? 'ph-check' : 'ph-clock');
                        @endphp

                        <article
                            class="flex flex-col gap-3 rounded-2xl border p-4 sm:flex-row sm:items-center sm:justify-between"
                            style="border-color: var(--ui-border);">
                            <div class="flex min-w-0 items-start gap-3">
                                <span @class([
                                    'flex h-10 w-10 shrink-0 items-center justify-center rounded-xl',
                                    'bg-emerald-100 text-emerald-700 dark:bg-emerald-950/50 dark:text-emerald-300' => $complete && $type !== 'NO_APLICA',
                                    'bg-slate-100 text-slate-500 dark:bg-slate-800 dark:text-slate-300' => !$complete || $type === 'NO_APLICA',
                                ])>
                                    <i class="ph {{ $requirementIcon }} text-lg" aria-hidden="true"></i>
                                </span>

                                <div class="min-w-0">
                                    <h3 class="font-bold">
                                        {{ data_get($requirement, 'label', 'Requisito') }}
                                    </h3>

                                    <p class="ui-subtitle mt-1 text-sm">
                                        @if ($type === 'NO_APLICA')
                                            Este componente no aplica a tu situación actual.
                                        @elseif ($complete)
                                            Información disponible para el análisis.
                                        @elseif ($type === 'OBLIGATORIO')
                                            Debes completar este requisito antes de generar el análisis.
                                        @else
                                            Su ausencia no bloquea el análisis, pero puede reducir la cobertura de evidencia.
                                        @endif
                                    </p>
                                </div>
                            </div>

                            <div class="flex flex-wrap items-center gap-2">
                                <span class="rounded-full border px-2.5 py-1 text-xs font-bold {{ $meta['class'] }}">
                                    {{ $meta['label'] }}
                                </span>

                                <span @class([
                                    'rounded-full border px-2.5 py-1 text-xs font-bold',
                                    'border-emerald-200 bg-emerald-50 text-emerald-700 dark:border-emerald-800/60 dark:bg-emerald-950/30 dark:text-emerald-300' => $complete && $type !== 'NO_APLICA',
                                    'border-slate-200 bg-slate-50 text-slate-600 dark:border-slate-700 dark:bg-slate-900/50 dark:text-slate-300' => !$complete || $type === 'NO_APLICA',
                                ])>
                                    {{ $type === 'NO_APLICA' ? 'No aplica' : ($complete ? 'Completo' : 'Pendiente') }}
                                </span>
                            </div>
                        </article>
                    @empty
                        <div class="rounded-2xl border border-dashed p-6 text-center" style="border-color: var(--ui-border);">
                            <i class="ph ph-info text-2xl" aria-hidden="true"></i>

                            <p class="ui-subtitle mt-2">
                                No fue posible obtener los requisitos del perfil.
                            </p>
                        </div>
                    @endforelse
                </div>

                <div class="mt-6 flex flex-wrap gap-3">
                    <a class="ui-btn-primary"
                        href="{{ route('aula-virtual.estudiante.orientacion.aporte', ['section' => 'riasec']) }}">
                        <i class="ph ph-compass" aria-hidden="true"></i>
                        {{ $activity?->riasec_score ? 'Revisar RIASEC' : 'Completar RIASEC' }}
                    </a>

                    <a class="ui-btn-secondary" href="{{ route('estudiante.area', ['area' => 'progreso']) }}">
                        <i class="ph ph-chart-line" aria-hidden="true"></i>
                        Revisar información académica
                    </a>

                    @if ($profileReady)
                        <a class="ui-btn-secondary"
                            href="{{ route('aula-virtual.estudiante.orientacion.aporte', ['section' => 'analisis']) }}">
                            <i class="ph ph-arrow-right" aria-hidden="true"></i>
                            Ir a mi análisis
                        </a>
                    @endif
                </div>
            </div>

            <aside class="space-y-6">
                <section class="ui-panel">
                    <p class="ui-kicker">Actividad reciente</p>

                    <div class="mt-4 space-y-4">
                        <div>
                            <p class="text-sm font-semibold">Último RIASEC</p>

                            <p class="ui-subtitle mt-1">
                                {{ $activity?->finalizado_at?->format('d/m/Y H:i') ?? 'Todavía no completado' }}
                            </p>
                        </div>

                        <div class="border-t pt-4" style="border-color: var(--ui-border);">
                            <p class="text-sm font-semibold">Último análisis</p>

                            <p class="ui-subtitle mt-1">
                                {{ $activity?->analysis_completed_at?->format('d/m/Y H:i') ?? 'Todavía no generado' }}
                            </p>
                        </div>
                    </div>
                </section>

                <section class="ui-panel">
                    <div class="flex items-start gap-3">
                        <span
                            class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-sky-100 text-sky-700 dark:bg-sky-950/50 dark:text-sky-300">
                            <i class="ph ph-shield-check text-lg" aria-hidden="true"></i>
                        </span>

                        <div>
                            <h2 class="font-bold">Cómo se interpreta</h2>

                            <p class="ui-subtitle mt-2 text-sm leading-6">
                                La falta de evidencia significa que no existe información
                                suficiente para afirmar algo. No significa bajo rendimiento,
                                incapacidad ni una calificación de cero.
                            </p>
                        </div>
                    </div>
                </section>
            </aside>
        </section>


        {{-- =========================================================
        RIASEC
        ========================================================== --}}
    @elseif ($section === 'riasec')

        @if ($activity?->riasec_score)
            <section class="ui-panel">
                <div class="flex flex-col gap-4 lg:flex-row lg:items-start lg:justify-between">
                    <div>
                        <p class="ui-kicker">Último resultado guardado</p>

                        <h2 class="ui-title mt-1 text-2xl font-black">
                            Tus intereses
                            @if ($hollandCode)
                                · {{ $hollandCode }}
                            @endif
                        </h2>

                        <p class="ui-subtitle mt-2 max-w-3xl leading-6">
                            Este perfil representa intereses vocacionales. No mide
                            inteligencia, aptitud, capacidad ni probabilidad de éxito académico
                            o profesional.
                        </p>
                    </div>

                    @if ($hollandCode)
                        <div class="flex min-w-[150px] flex-col items-center justify-center rounded-2xl border px-6 py-4 text-center"
                            style="border-color: var(--ui-border);">
                            <span class="text-xs font-bold uppercase tracking-wider" style="color: var(--ui-muted);">
                                Código Holland
                            </span>

                            <strong class="mt-1 text-3xl font-black tracking-widest">
                                {{ $hollandCode }}
                            </strong>
                        </div>
                    @endif
                </div>

                <div class="mt-6 grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
                    @foreach ($riasecNames as $code => $name)
                        @php
                            $score = (int) data_get($riasecScore, $code, 0);
                            $percentage = max(0, min(100, $score * 5));
                        @endphp

                        <article class="rounded-2xl border p-4" style="border-color: var(--ui-border);">
                            <div class="flex items-center justify-between gap-3">
                                <div>
                                    <span class="text-xs font-black uppercase tracking-widest" style="color: var(--ui-muted);">
                                        {{ $code }}
                                    </span>

                                    <h3 class="font-bold">{{ $name }}</h3>
                                </div>

                                <strong class="text-xl">
                                    {{ $score }}
                                    <span class="text-sm font-medium" style="color: var(--ui-muted);">/20</span>
                                </strong>
                            </div>

                            <meter class="mt-4 block h-3 w-full" min="0" max="20" value="{{ $score }}"
                                aria-label="{{ $name }}: {{ $score }} de 20">
                                {{ $score }} de 20
                            </meter>

                            <p class="ui-subtitle mt-2 text-xs">
                                {{ $percentage }}% de la escala dimensional del instrumento.
                            </p>
                        </article>
                    @endforeach
                </div>

                @if (!empty($riasecLimitations))
                    <div class="mt-6 rounded-2xl border p-4" style="border-color: var(--ui-border);">
                        <h3 class="flex items-center gap-2 font-bold">
                            <i class="ph ph-info" aria-hidden="true"></i>
                            Consideraciones para interpretar el resultado
                        </h3>

                        <ul class="ui-subtitle mt-3 list-disc space-y-2 pl-5 text-sm">
                            @foreach ($riasecLimitations as $limitation)
                                <li>
                                    {{ is_array($limitation) ? data_get($limitation, 'message', '') : $limitation }}
                                </li>
                            @endforeach
                        </ul>
                    </div>
                @endif

            </section>
        @endif


        @if ($instrument?->available)
            @php
                $instrumentItems = collect(data_get($instrument->data, 'items', []));
                $responseScale = data_get($instrument->data, 'response_scale', []);
                $itemCount = $instrumentItems->count();
            @endphp

            <section class="ui-panel" x-data="{
                            submitting: false,
                            total: {{ $itemCount }},
                            answered: {{ count(array_filter($formResponses, fn($value) => $value !== null && $value !== '')) }},
                            updateProgress() {
                                this.answered = this.$el.querySelectorAll(
                                    'input[type=radio]:checked'
                                ).length;
                            }
                        }" x-init="updateProgress()">
                <div class="flex flex-col gap-5 lg:flex-row lg:items-start lg:justify-between">
                    <div class="max-w-3xl">
                        <p class="ui-kicker">Cuestionario de intereses</p>

                        <h2 class="ui-title mt-1 text-2xl font-black">
                            {{ data_get($instrument->data, 'title', 'Perfil de intereses RIASEC') }}
                        </h2>

                        <p class="ui-subtitle mt-3 leading-7">
                            Responde cuánto te gustaría realizar cada actividad.
                            No existen respuestas correctas o incorrectas. Elige la opción que
                            mejor represente tu interés personal.
                        </p>
                    </div>

                    <div class="rounded-2xl border p-4" style="border-color: var(--ui-border);">
                        <p class="text-sm font-semibold">
                            Progreso del cuestionario
                        </p>

                        <p class="mt-1 text-2xl font-black">
                            <span x-text="answered">{{ count($formResponses) }}</span>
                            <span class="text-base font-medium" style="color: var(--ui-muted);">
                                / {{ $itemCount }}
                            </span>
                        </p>
                    </div>
                </div>

                <div class="sticky top-2 z-20 mt-5 rounded-2xl border p-3 shadow-sm backdrop-blur"
                    style="border-color: var(--ui-border); background: color-mix(in srgb, var(--ui-surface) 94%, transparent);"
                    role="status" aria-live="polite">
                    <div class="flex items-center justify-between gap-3 text-sm">
                        <span class="font-bold">
                            Preguntas respondidas
                        </span>

                        <span>
                            <span x-text="answered">{{ count($formResponses) }}</span>
                            / {{ $itemCount }}
                        </span>
                    </div>

                    <div class="mt-2 h-2 overflow-hidden rounded-full" style="background: var(--ui-border);">
                        <div class="h-full rounded-full bg-emerald-500 transition-all duration-300"
                            :style="`width: ${total > 0 ? (answered / total) * 100 : 0}%`"></div>
                    </div>
                </div>

                <form method="POST" action="{{ route('aula-virtual.estudiante.orientacion.aporte.score') }}"
                    class="mt-6 space-y-4" @submit="submitting = true" @change="updateProgress()">
                    @csrf

                    <input type="hidden" name="instrument_version"
                        value="{{ data_get($instrument->data, 'instrument_version') }}">

                    @foreach ($instrumentItems as $item)
                        @php
                            $itemId = data_get($item, 'item_id');
                            $currentValue = data_get($formResponses, (string) $itemId);
                        @endphp

                        <fieldset class="rounded-2xl border p-4 transition focus-within:ring-2 focus-within:ring-sky-500/40 sm:p-5"
                            style="border-color: var(--ui-border);">
                            <legend class="px-2 font-bold leading-6">
                                <span class="mr-2 inline-flex h-7 min-w-7 items-center justify-center rounded-lg px-2 text-sm"
                                    style="background: var(--ui-soft);">
                                    {{ $itemId }}
                                </span>

                                {{ data_get($item, 'text') }}
                            </legend>

                            <div class="mt-4 grid gap-2 sm:grid-cols-5">
                                @foreach ($responseScale as $option)
                                    @php
                                        $optionValue = data_get($option, 'value');
                                        $optionId = 'riasec-' . $itemId . '-' . $optionValue;
                                    @endphp

                                    <label for="{{ $optionId }}"
                                        class="group relative flex min-h-[70px] cursor-pointer items-center gap-3 rounded-xl border p-3 transition hover:bg-slate-50 has-[:checked]:border-sky-500 has-[:checked]:bg-sky-50 dark:hover:bg-slate-900/50 dark:has-[:checked]:bg-sky-950/30"
                                        style="border-color: var(--ui-border);">
                                        <input id="{{ $optionId }}" required type="radio" name="responses[{{ $itemId }}]"
                                            value="{{ $optionValue }}" @checked((string) $currentValue === (string) $optionValue)
                                            class="h-4 w-4 shrink-0">

                                        <span class="text-sm font-medium leading-5">
                                            {{ data_get($option, 'label') }}
                                        </span>
                                    </label>
                                @endforeach
                            </div>
                        </fieldset>
                    @endforeach

                    <div class="sticky bottom-3 z-20 flex flex-col gap-3 rounded-2xl border p-4 shadow-lg backdrop-blur sm:flex-row sm:items-center sm:justify-between"
                        style="border-color: var(--ui-border); background: color-mix(in srgb, var(--ui-surface) 96%, transparent);">
                        <div>
                            <p class="font-bold">
                                Revisa tus respuestas antes de guardar
                            </p>

                            <p class="ui-subtitle mt-1 text-sm">
                                Necesitas responder los {{ $itemCount }} ítems.
                            </p>
                        </div>

                        <button class="ui-btn-primary min-w-[220px] justify-center" type="submit"
                            :disabled="submitting || answered !== total" @disabled(!$storageReady)>
                            <i class="ph" :class="submitting ? 'ph-circle-notch animate-spin' : 'ph-floppy-disk'"
                                aria-hidden="true"></i>

                            <span x-show="!submitting">
                                Guardar y ver mis intereses
                            </span>

                            <span x-show="submitting" x-cloak>
                                Procesando respuestas…
                            </span>
                        </button>
                    </div>
                </form>

                <footer class="mt-6 rounded-2xl border p-4 text-sm" style="border-color: var(--ui-border);">
                    <p class="ui-subtitle">
                        {{ data_get($instrument->data, 'source_attribution') }}
                    </p>

                    @if (data_get($instrument->data, 'source_url'))
                        <a class="mt-2 inline-flex items-center gap-2 font-bold underline underline-offset-4"
                            href="{{ data_get($instrument->data, 'source_url') }}" rel="noopener noreferrer" target="_blank">
                            <i class="ph ph-arrow-square-out" aria-hidden="true"></i>
                            Consultar la fuente del instrumento
                        </a>
                    @endif
                </footer>
            </section>
        @else
            <section class="ui-panel" role="alert">
                <div class="flex items-start gap-3">
                    <i class="ph ph-warning-circle mt-0.5 text-xl" aria-hidden="true"></i>

                    <div>
                        <h2 class="font-bold">
                            El cuestionario no está disponible en este momento
                        </h2>

                        <p class="ui-subtitle mt-1">
                            {{ $instrument?->message ?? 'No fue posible obtener el instrumento. Intenta nuevamente más tarde.' }}
                        </p>
                    </div>
                </div>
            </section>
        @endif


        {{-- =========================================================
        ANALISIS
        ========================================================== --}}
    @elseif ($section === 'analisis')

        <section class="ui-panel">
            <div class="flex flex-col gap-5 lg:flex-row lg:items-start lg:justify-between">
                <div class="max-w-3xl">
                    <p class="ui-kicker">Análisis multidimensional</p>

                    <h2 class="ui-title mt-1 text-2xl font-black">
                        Mi análisis y perfiles de carrera
                    </h2>

                    <p class="ui-subtitle mt-3 leading-7">
                        La afinidad de intereses, la preparación académica y la cobertura
                        de evidencia son dimensiones distintas. Una no sustituye a las otras.
                    </p>
                </div>

                <div>
                    @if ($profileReady)
                        <span
                            class="inline-flex items-center gap-2 rounded-full border border-emerald-200 bg-emerald-50 px-3 py-1.5 text-sm font-bold text-emerald-700 dark:border-emerald-800/60 dark:bg-emerald-950/30 dark:text-emerald-300">
                            <i class="ph ph-check-circle" aria-hidden="true"></i>
                            Perfil habilitado
                        </span>
                    @else
                        <span
                            class="inline-flex items-center gap-2 rounded-full border border-amber-200 bg-amber-50 px-3 py-1.5 text-sm font-bold text-amber-700 dark:border-amber-800/60 dark:bg-amber-950/30 dark:text-amber-300">
                            <i class="ph ph-lock-key" aria-hidden="true"></i>
                            Análisis bloqueado
                        </span>
                    @endif
                </div>
            </div>

            @if (!$profileReady)
                <div class="mt-5 rounded-2xl border border-amber-200 bg-amber-50 p-4 text-amber-800 dark:border-amber-800/60 dark:bg-amber-950/30 dark:text-amber-200"
                    role="status">
                    <div class="flex items-start gap-3">
                        <i class="ph ph-warning-circle mt-0.5 text-xl" aria-hidden="true"></i>

                        <div>
                            <h3 class="font-bold">
                                Aún faltan datos obligatorios
                            </h3>

                            <p class="mt-1 text-sm leading-6">
                                El análisis completo no se generará hasta que completes
                                los requisitos mínimos.
                            </p>

                            <a class="mt-3 inline-flex font-bold underline underline-offset-4"
                                href="{{ route('aula-virtual.estudiante.orientacion.aporte', ['section' => 'perfil']) }}">
                                Revisar lo que falta
                            </a>
                        </div>
                    </div>
                </div>
            @endif

            <form class="mt-6" method="POST" action="{{ route('aula-virtual.estudiante.orientacion.aporte.analysis') }}"
                x-data="{ submitting: false }" @submit="submitting = true">
                @csrf

                <button type="submit" class="ui-btn-primary" @disabled(!$profileReady) :disabled="submitting">
                    <i class="ph" :class="submitting ? 'ph-circle-notch animate-spin' : 'ph-sparkle'"
                        aria-hidden="true"></i>

                    <span x-show="!submitting">
                        {{ $analysis ? 'Actualizar análisis con mis datos' : 'Generar análisis con mis datos' }}
                    </span>

                    <span x-show="submitting" x-cloak>
                        Analizando información disponible…
                    </span>
                </button>
            </form>
        </section>


        @if ($analysis)

            {{-- Cobertura del perfil --}}
            <section class="ui-panel">
                <div class="flex flex-wrap items-start justify-between gap-4">
                    <div>
                        <p class="ui-kicker">Cobertura de datos</p>

                        <h2 class="ui-title mt-1 text-xl font-black">
                            Evidencia disponible de mi perfil
                        </h2>
                    </div>

                    <span class="rounded-full border px-3 py-1 text-xs font-bold" style="border-color: var(--ui-border);">
                        {{ $analysisStatusLabels[data_get($analysis, 'analysis_status')] ?? 'Estado por confirmar' }}
                    </span>
                </div>

                @php
                    $profileComponents = [
                        'academic_evidence' => [
                            'label' => 'Datos académicos',
                            'icon' => 'ph-graduation-cap',
                        ],
                        'attendance_evidence' => [
                            'label' => 'Asistencia',
                            'icon' => 'ph-calendar-check',
                        ],
                        'learning_activity_evidence' => [
                            'label' => 'Actividad de aprendizaje',
                            'icon' => 'ph-check-square-offset',
                        ],
                        'historical_evidence' => [
                            'label' => 'Historia académica',
                            'icon' => 'ph-clock-counter-clockwise',
                        ],
                        'technical_evidence' => [
                            'label' => 'Formación técnica BTH',
                            'icon' => 'ph-wrench',
                        ],
                        'declared_interest_evidence' => [
                            'label' => 'Intereses declarados',
                            'icon' => 'ph-heart',
                        ],
                    ];
                @endphp

                <div class="mt-5 grid gap-3 sm:grid-cols-2 lg:grid-cols-3">
                    @foreach ($profileComponents as $component => $componentMeta)
                        @php
                            $status = data_get($studentSnapshot, "{$component}.status", 'UNAVAILABLE');
                            $meta = $statusMeta[$status] ?? $statusMeta['UNAVAILABLE'];
                        @endphp

                        <article class="rounded-2xl border p-4" style="border-color: var(--ui-border);">
                            <div class="flex items-start gap-3">
                                <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl"
                                    style="background: var(--ui-soft);">
                                    <i class="ph {{ $componentMeta['icon'] }} text-lg" aria-hidden="true"></i>
                                </span>

                                <div>
                                    <h3 class="font-bold">
                                        {{ $componentMeta['label'] }}
                                    </h3>

                                    <span
                                        class="mt-2 inline-flex items-center gap-1.5 rounded-full border px-2.5 py-1 text-xs font-bold {{ $meta['class'] }}">
                                        <i class="ph {{ $meta['icon'] }}" aria-hidden="true"></i>
                                        {{ $meta['label'] }}
                                    </span>
                                </div>
                            </div>
                        </article>
                    @endforeach
                </div>

                <div class="mt-5 rounded-2xl border p-4" style="border-color: var(--ui-border);">
                    <p class="ui-subtitle text-sm leading-6">
                        <strong class="text-current">Importante:</strong>
                        un componente sin evidencia permanece como desconocido o no observado.
                        No se interpreta como un desempeño de cero.
                    </p>
                </div>
            </section>


            {{-- Exploración académica y comparación guiada --}}
            <section class="ui-panel" aria-labelledby="career-comparison-title">
                <div class="flex flex-col gap-3 lg:flex-row lg:items-end lg:justify-between">
                    <div class="max-w-3xl">
                        <p class="ui-kicker">Exploración guiada</p>

                        <h2 id="career-comparison-title" class="ui-title mt-1 text-2xl font-black">
                            Explora carreras, materias y planes de estudio
                        </h2>

                        <p class="ui-subtitle mt-2 leading-6">
                            Decide cómo deseas explorar: parte de las relaciones encontradas en tu análisis,
                            consulta las carreras de una universidad específica o revisa el catálogo completo.
                        </p>
                    </div>

                    <span class="ui-subtitle text-sm">
                        @if ($explorationMode === 'universities' && $selectedUniversity === '')
                            Elige una universidad para comenzar
                        @elseif ($explorationMode === 'analysis')
                            {{ $visibleOptionCount }} opción(es) relacionadas con tu análisis
                        @else
                            {{ $visibleOptionCount }} opción(es) mostradas
                        @endif
                    </span>
                </div>

                <nav class="mt-5 grid gap-3 md:grid-cols-3" aria-label="Formas de explorar carreras">
                    @foreach ($explorationModes as $mode => $modeMeta)
                        @php $modeIsActive = $explorationMode === $mode; @endphp
                        <a href="{{ $analysisUrl(['explore' => $mode], ['career', 'university']) }}"
                            @if ($modeIsActive) aria-current="page" @endif
                            class="group rounded-2xl border p-4 transition hover:-translate-y-0.5 hover:shadow-sm"
                            style="border-color: {{ $modeIsActive ? 'var(--ui-primary)' : 'var(--ui-border)' }}; background: {{ $modeIsActive ? 'var(--ui-soft)' : 'var(--ui-surface)' }};">
                            <div class="flex items-start gap-3">
                                <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl"
                                    style="background: {{ $modeIsActive ? 'var(--ui-primary)' : 'var(--ui-soft)' }}; color: {{ $modeIsActive ? 'white' : 'var(--ui-primary)' }};">
                                    <i class="ph {{ $modeMeta['icon'] }} text-lg" aria-hidden="true"></i>
                                </span>
                                <span>
                                    <span class="block font-black">{{ $modeMeta['label'] }}</span>
                                    <span class="ui-subtitle mt-1 block text-sm leading-5">{{ $modeMeta['description'] }}</span>
                                </span>
                            </div>
                        </a>
                    @endforeach
                </nav>

                @if ($explorationMode === 'analysis')
                    <div class="mt-4 flex items-start gap-3 rounded-2xl border p-4" style="border-color: var(--ui-border); background: var(--ui-surface);">
                        <i class="ph ph-info mt-0.5 text-xl" aria-hidden="true" style="color: var(--ui-primary);"></i>
                        <p class="ui-subtitle text-sm leading-6">
                            <strong class="text-current">Estas opciones no forman un ranking.</strong>
                            Se muestran porque existe alguna relación documentada con tus intereses o tu preparación.
                            Revisa cada perfil antes de tomar una decisión.
                        </p>
                    </div>
                @elseif ($explorationMode === 'universities')
                    <form method="GET" action="{{ url()->current() }}"
                        class="mt-5 grid gap-4 rounded-2xl border p-4 md:grid-cols-[minmax(0,1fr)_auto] md:items-end"
                        style="border-color: var(--ui-border); background: var(--ui-soft);">
                        <input type="hidden" name="section" value="analisis">
                        <input type="hidden" name="explore" value="universities">
                        @foreach ($selectedComparisonIds as $careerId)
                            <input type="hidden" name="compare[]" value="{{ $careerId }}">
                        @endforeach

                        <label class="block">
                            <span class="mb-2 block text-sm font-bold">Universidad que quieres explorar</span>
                            <select name="university" class="ui-input w-full" required>
                                <option value="">Selecciona una universidad</option>
                                @foreach ($allUniversities as $universityOption)
                                    <option value="{{ $universityOption }}" @selected($selectedUniversity === $universityOption)>
                                        {{ $universityOption }}
                                    </option>
                                @endforeach
                            </select>
                            <span class="ui-subtitle mt-2 block text-xs leading-5">
                                La universidad no se elige automáticamente; tú decides cuál consultar.
                            </span>
                        </label>

                        <div class="flex flex-wrap gap-2">
                            <button type="submit" class="ui-btn-primary min-h-11">
                                <i class="ph ph-buildings" aria-hidden="true"></i>
                                Ver carreras
                            </button>
                            @if ($selectedUniversity !== '')
                                <a href="{{ $analysisUrl([], ['university', 'career']) }}" class="ui-btn-secondary min-h-11">
                                    Limpiar
                                </a>
                            @endif
                        </div>
                    </form>

                    @if ($selectedUniversity === '')
                        <div class="mt-4 flex items-start gap-3 rounded-2xl border border-dashed p-4" style="border-color: var(--ui-border);">
                            <i class="ph ph-buildings mt-0.5 text-xl" aria-hidden="true" style="color: var(--ui-primary);"></i>
                            <div>
                                <p class="font-bold">Elige una universidad para ver sus carreras</p>
                                <p class="ui-subtitle mt-1 text-sm">Después podrás revisar materias, duración y malla oficial de cada opción disponible.</p>
                            </div>
                        </div>
                    @endif
                @else

                <ol class="mt-5 grid gap-3 md:grid-cols-3" aria-label="Pasos para explorar carreras">
                    <li class="rounded-2xl border p-4" style="border-color: var(--ui-border); background: var(--ui-soft);">
                        <div class="flex items-start gap-3">
                            <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-full font-black text-white" style="background: var(--ui-primary);">1</span>
                            <div><p class="font-black">Elige una carrera</p><p class="ui-subtitle mt-1 text-sm leading-5">Parte del campo profesional que deseas comprender.</p></div>
                        </div>
                    </li>
                    <li class="rounded-2xl border p-4" style="border-color: var(--ui-border); background: var(--ui-soft);">
                        <div class="flex items-start gap-3">
                            <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-full border font-black" style="border-color: var(--ui-primary); color: var(--ui-primary);">2</span>
                            <div><p class="font-black">Elige una universidad</p><p class="ui-subtitle mt-1 text-sm leading-5">Tú decides qué institución consultar entre las que ofrecen esa carrera.</p></div>
                        </div>
                    </li>
                    <li class="rounded-2xl border p-4" style="border-color: var(--ui-border); background: var(--ui-soft);">
                        <div class="flex items-start gap-3">
                            <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-full border font-black" style="border-color: var(--ui-primary); color: var(--ui-primary);">3</span>
                            <div><p class="font-black">Revisa y verifica</p><p class="ui-subtitle mt-1 text-sm leading-5">Consulta materias, malla y publicación oficial de la opción elegida.</p></div>
                        </div>
                    </li>
                </ol>

                <div class="mt-4 flex items-start gap-3 rounded-2xl border p-4" style="border-color: var(--ui-border); background: var(--ui-surface);">
                    <i class="ph ph-info mt-0.5 text-xl" aria-hidden="true" style="color: var(--ui-primary);"></i>
                    <p class="ui-subtitle text-sm leading-6">
                        <strong class="text-current">El Aporte no fabrica un ranking global.</strong>
                        Presenta afinidad de intereses, preparación observada y evidencia como dimensiones separadas.
                        Así puedes comprender cada carrera sin convertir datos distintos en un porcentaje engañoso.
                    </p>
                </div>

                <form method="GET" action="{{ url()->current() }}"
                    class="mt-5 grid gap-4 rounded-2xl border p-4 lg:grid-cols-[minmax(0,1fr)_minmax(0,1fr)_auto] lg:items-end"
                    style="border-color: var(--ui-border); background: var(--ui-soft);">
                    <input type="hidden" name="section" value="analisis">
                    <input type="hidden" name="explore" value="all">
                    @foreach ($selectedComparisonIds as $careerId)
                        <input type="hidden" name="compare[]" value="{{ $careerId }}">
                    @endforeach

                    <label class="block">
                        <span class="mb-2 block text-sm font-bold">1. Carrera que quieres explorar</span>
                        <select name="career" class="ui-input w-full">
                            <option value="">Selecciona una carrera</option>
                            @foreach ($availableCareers as $careerOption)
                                <option value="{{ $careerOption['key'] }}" @selected($selectedCareer === $careerOption['key'])>
                                    {{ $careerOption['name'] }}
                                </option>
                            @endforeach
                        </select>
                    </label>

                    <label class="block">
                        <span class="mb-2 block text-sm font-bold">2. Universidad que quieres consultar</span>
                        <select name="university" class="ui-input w-full" @disabled($selectedCareer === '')>
                            @if ($selectedCareer === '')
                                <option value="">Primero elige una carrera</option>
                            @else
                                <option value="">Todas las universidades que ofrecen esta carrera</option>
                                @foreach ($availableUniversities as $universityOption)
                                    <option value="{{ $universityOption }}" @selected($selectedUniversity === $universityOption)>
                                        {{ $universityOption }}
                                    </option>
                                @endforeach
                            @endif
                        </select>
                        <span class="ui-subtitle mt-2 block text-xs leading-5">
                            @if ($selectedCareer === '')
                                El sistema no elegirá una universidad por ti.
                            @else
                                {{ $availableUniversities->count() }} universidad(es) disponible(s) para esta carrera.
                            @endif
                        </span>
                    </label>

                    <div class="flex flex-wrap gap-2">
                        <button type="submit" class="ui-btn-primary min-h-11">
                            <i class="ph ph-funnel" aria-hidden="true"></i>
                            {{ $selectedCareer === '' ? 'Ver universidades' : 'Ver resultados' }}
                        </button>
                        @if ($selectedCareer !== '' || $selectedUniversity !== '')
                            <a href="{{ $analysisUrl([], ['career', 'university']) }}" class="ui-btn-secondary min-h-11">
                                Limpiar
                            </a>
                        @endif
                    </div>
                </form>
                @endif

                <section id="mi-comparacion" class="mt-5 rounded-2xl border p-5" style="border-color: var(--ui-border); background: var(--ui-surface);" aria-labelledby="selected-careers-title">
                    <div class="flex flex-wrap items-start justify-between gap-3">
                        <div>
                            <p class="ui-kicker">Mi comparación temporal</p>
                            <h3 id="selected-careers-title" class="ui-title mt-1 text-xl font-black">
                                {{ $comparisonProfiles->count() }} de 3 carreras preseleccionadas
                            </h3>
                            <p class="ui-subtitle mt-1 text-sm">Selecciona al menos dos para contrastarlas. La lista se conserva solo en esta dirección web.</p>
                        </div>
                        @if ($comparisonProfiles->isNotEmpty())
                            <a href="{{ $analysisUrl([], ['compare']) }}" class="ui-btn-secondary min-h-11 text-sm">
                                <i class="ph ph-trash" aria-hidden="true"></i> Vaciar comparación
                            </a>
                        @endif
                    </div>

                    @if ($comparisonProfiles->isEmpty())
                        <div class="mt-4 flex items-start gap-3 rounded-2xl border border-dashed p-4" style="border-color: var(--ui-border);">
                            <i class="ph ph-scales mt-0.5 text-xl" aria-hidden="true" style="color: var(--ui-primary);"></i>
                            <div><p class="font-bold">Aún no preseleccionaste carreras</p><p class="ui-subtitle mt-1 text-sm">Usa “Añadir a comparación” en la tabla o en cada perfil.</p></div>
                        </div>
                    @else
                        <div class="mt-4 grid gap-4 {{ $comparisonProfiles->count() === 3 ? 'xl:grid-cols-3' : 'lg:grid-cols-2' }}">
                            @foreach ($comparisonProfiles as $career)
                                @php
                                    $careerId = (string) data_get($career, 'career_id');
                                    $comparisonVocStatus = data_get($career, 'vocational_interest_relation.status', 'UNAVAILABLE');
                                    $comparisonTechnicalStatus = data_get($career, 'technical_relation.status', 'UNAVAILABLE');
                                    $comparisonVocMeta = $statusMeta[$comparisonVocStatus] ?? $statusMeta['UNAVAILABLE'];
                                    $comparisonTechnicalMeta = $statusMeta[$comparisonTechnicalStatus] ?? $statusMeta['UNAVAILABLE'];
                                    $remainingComparisonIds = $selectedComparisonIds->reject(fn ($id) => $id === $careerId)->values()->all();
                                @endphp
                                <article class="rounded-2xl border p-4" style="border-color: var(--ui-border); background: var(--ui-soft);">
                                    <div class="flex items-start justify-between gap-3">
                                        <div class="min-w-0">
                                            <p class="ui-kicker">Carrera preseleccionada</p>
                                            <h4 class="ui-title mt-1 font-black leading-5">{{ data_get($career, 'career_name', 'Carrera') }}</h4>
                                            <p class="ui-subtitle mt-1 text-xs">Fuente del plan: {{ data_get($career, 'university', 'institución no disponible') }}</p>
                                        </div>
                                        <a href="{{ $analysisUrl(['compare' => $remainingComparisonIds]) }}" class="ui-icon-btn h-11 w-11 shrink-0" aria-label="Quitar {{ data_get($career, 'career_name', 'carrera') }} de la comparación">
                                            <i class="ph ph-x" aria-hidden="true"></i>
                                        </a>
                                    </div>
                                    <dl class="mt-4 space-y-3 text-sm">
                                        <div class="flex items-start justify-between gap-3"><dt class="ui-subtitle">Intereses</dt><dd class="text-right font-bold">{{ $comparisonVocMeta['label'] }}</dd></div>
                                        <div class="flex items-start justify-between gap-3"><dt class="ui-subtitle">BTH</dt><dd class="text-right font-bold">{{ $comparisonTechnicalMeta['label'] }}</dd></div>
                                        <div class="flex items-start justify-between gap-3"><dt class="ui-subtitle">Evidencia</dt><dd class="text-right font-bold">{{ data_get($career, 'evidence_quality.academic_record_count', 0) }} registros</dd></div>
                                        <div class="border-t pt-3" style="border-color: var(--ui-border);"><dt class="font-bold">Preparación observada</dt><dd class="ui-subtitle mt-1 leading-6">{{ data_get($career, 'preparation.interpretation', 'Sin evidencia suficiente para interpretar esta dimensión.') }}</dd></div>
                                    </dl>
                                </article>
                            @endforeach
                        </div>
                        @if ($comparisonProfiles->count() === 1)
                            <p class="ui-subtitle mt-4 text-sm"><i class="ph ph-info" aria-hidden="true"></i> Añade una segunda carrera para obtener una comparación útil.</p>
                        @endif
                    @endif
                </section>

                <div class="mt-6 flex flex-wrap items-end justify-between gap-3">
                    <div>
                        <h3 class="ui-title text-lg font-black">Vista rápida de opciones visibles</h3>
                        <p class="ui-subtitle mt-1 text-sm">Preselecciona aquí o revisa el perfil completo de cada carrera más abajo.</p>
                    </div>
                    <span class="ui-badge-info">Máximo 3 opciones</span>
                </div>

                <div class="mt-4 overflow-x-auto rounded-2xl border" style="border-color: var(--ui-border);">
                    <table class="min-w-[960px] w-full text-left text-sm">
                        <caption class="sr-only">
                            Comparativa completa de las opciones de carrera analizadas.
                        </caption>

                        <thead style="background: var(--ui-soft);">
                            <tr>
                                <th scope="col" class="px-4 py-3 font-bold">Opción</th>
                                <th scope="col" class="px-4 py-3 font-bold">Intereses</th>
                                <th scope="col" class="px-4 py-3 font-bold">Preparación observada</th>
                                <th scope="col" class="px-4 py-3 font-bold">BTH relacionado</th>
                                <th scope="col" class="px-4 py-3 font-bold">Cobertura de evidencia</th>
                                <th scope="col" class="px-4 py-3 font-bold">Comparación</th>
                            </tr>
                        </thead>

                        <tbody>
                            @forelse ($visibleCareerProfiles as $career)
                                @php
                                    $comparisonVocStatus = data_get($career, 'vocational_interest_relation.status', 'UNAVAILABLE');
                                    $comparisonTechnicalStatus = data_get($career, 'technical_relation.status', 'UNAVAILABLE');
                                    $comparisonVocMeta = $statusMeta[$comparisonVocStatus] ?? $statusMeta['UNAVAILABLE'];
                                    $comparisonTechnicalMeta = $statusMeta[$comparisonTechnicalStatus] ?? $statusMeta['UNAVAILABLE'];
                                    $observedRelations = data_get($career, 'preparation.relations_with_observed_academic_evidence', 0);
                                    $relatedRelations = data_get($career, 'preparation.related_relations', 0);
                                    $reinforcementCount = count(data_get($career, 'preparation.reinforcement_areas', []));
                                    $careerId = (string) data_get($career, 'career_id');
                                    $isCompared = $selectedComparisonIds->contains($careerId);
                                    $canAddComparison = $careerId !== '' && ($isCompared || $selectedComparisonIds->count() < 3);
                                    $nextComparisonIds = $isCompared
                                        ? $selectedComparisonIds->reject(fn ($id) => $id === $careerId)->values()->all()
                                        : collect($selectedComparisonIds->all())->push($careerId)->unique()->take(3)->values()->all();
                                @endphp

                                <tr class="border-t align-top" style="border-color: var(--ui-border);">
                                    <th scope="row" class="px-4 py-4 font-bold">
                                        <span class="block">{{ data_get($career, 'career_name', 'Carrera') }}</span>
                                        <span class="ui-subtitle mt-1 block max-w-xs font-normal">
                                            Fuente del plan: {{ data_get($career, 'university', 'institución no disponible') }}
                                        </span>
                                    </th>

                                    <td class="px-4 py-4">
                                        <span class="inline-flex items-center gap-1.5 rounded-full border px-2.5 py-1 text-xs font-bold {{ $comparisonVocMeta['class'] }}">
                                            <i class="ph {{ $comparisonVocMeta['icon'] }}" aria-hidden="true"></i>
                                            {{ $comparisonVocMeta['label'] }}
                                        </span>
                                    </td>

                                    <td class="px-4 py-4">
                                        <p class="font-bold">
                                            {{ $observedRelations }} de {{ $relatedRelations }} relaciones documentadas
                                        </p>
                                        <p class="ui-subtitle mt-1 text-xs">
                                            {{ $reinforcementCount }} área(s) de refuerzo señalada(s)
                                        </p>
                                    </td>

                                    <td class="px-4 py-4">
                                        <span class="inline-flex items-center gap-1.5 rounded-full border px-2.5 py-1 text-xs font-bold {{ $comparisonTechnicalMeta['class'] }}">
                                            <i class="ph {{ $comparisonTechnicalMeta['icon'] }}" aria-hidden="true"></i>
                                            {{ $comparisonTechnicalMeta['label'] }}
                                        </span>
                                    </td>

                                    <td class="px-4 py-4">
                                        <p class="font-bold">
                                            {{ data_get($career, 'evidence_quality.academic_record_count', 0) }} registros
                                        </p>
                                        <p class="ui-subtitle mt-1 text-xs">
                                            {{ data_get($career, 'evidence_quality.distinct_subject_count', 0) }} materias ·
                                            {{ data_get($career, 'evidence_quality.ordered_period_count', 0) }} periodos
                                        </p>
                                    </td>

                                    <td class="px-4 py-4">
                                        @if ($canAddComparison)
                                            <a href="{{ $analysisUrl(['compare' => $nextComparisonIds]) }}#mi-comparacion"
                                                class="{{ $isCompared ? 'ui-btn-secondary' : 'ui-btn-primary' }} min-h-11 whitespace-nowrap text-xs">
                                                <i class="ph {{ $isCompared ? 'ph-check-circle' : 'ph-plus-circle' }}" aria-hidden="true"></i>
                                                {{ $isCompared ? 'Preseleccionada' : 'Añadir' }}
                                            </a>
                                        @else
                                            <span class="ui-subtitle block max-w-36 text-xs leading-5">Límite alcanzado. Quita una opción para añadir otra.</span>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="px-4 py-8 text-center ui-subtitle">
                                        No hay opciones con este filtro. Prueba otra carrera o limpia la búsqueda.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <p class="ui-subtitle mt-4 text-sm leading-6">
                    Lectura de estados: “Disponible” indica una relación documentada; “Parcial” conserva
                    una relación con límites explícitos; “Insuficiente” y “Sin evidencia disponible” señalan
                    información por revisar sin asumir bajo desempeño.
                </p>
            </section>

            {{-- Perfiles de carrera --}}
            <section>
                <div class="mb-4 flex flex-wrap items-end justify-between gap-3 px-1">
                    <div>
                        <p class="ui-kicker">Exploración académica</p>

                        <h2 class="ui-title mt-1 text-2xl font-black">
                            Perfiles de carrera analizados
                        </h2>
                    </div>

                    <span class="ui-subtitle text-sm">
                        {{ $visibleCareerProfiles->count() }} opciones visibles de {{ count($careerProfiles) }} validadas
                    </span>
                </div>

                <div class="space-y-5">
                    @forelse ($visibleCareerProfiles as $career)
                        @php
                            $vocStatus = data_get($career, 'vocational_interest_relation.status', 'UNAVAILABLE');
                            $technicalStatus = data_get($career, 'technical_relation.status', 'UNAVAILABLE');

                            $vocMeta = $statusMeta[$vocStatus] ?? $statusMeta['UNAVAILABLE'];
                            $technicalMeta = $statusMeta[$technicalStatus] ?? $statusMeta['UNAVAILABLE'];

                            $reinforcementAreas = data_get($career, 'preparation.reinforcement_areas', []);
                            $areasWithoutEvidence = data_get($career, 'areas_without_evidence', []);
                            $careerSources = data_get($career, 'sources', []);
                            $careerLimitations = data_get($career, 'limitations', []);
                            $academicProgram = data_get($career, 'academic_program', []);
                            $curriculumNote = data_get($academicProgram, 'curriculum_note');
                            if (! is_string($curriculumNote) || blank($curriculumNote)) {
                                $curriculumNote = 'Este resumen muestra únicamente información documentada. Verifica la malla completa en su publicación oficial.';
                            }
                            $curriculumNote = str_ireplace(
                                ['en el corpus', 'al corpus', 'corpus'],
                                ['en las fuentes revisadas', 'a las fuentes revisadas', 'fuentes revisadas'],
                                $curriculumNote,
                            );
                            $programSubjects = collect(data_get($academicProgram, 'documented_subjects', []))->filter()->unique()->values();
                            if ($programSubjects->isEmpty()) {
                                $programSubjects = collect(data_get($career, 'preparation.evidence_items', []))
                                    ->flatMap(fn ($item) => data_get($item, 'initial_subjects', []))
                                    ->filter()
                                    ->unique()
                                    ->values();
                            }
                            $programAreas = collect(data_get($academicProgram, 'knowledge_areas', []))->filter()->unique()->values();
                            if ($programAreas->isEmpty()) {
                                $programAreas = collect(data_get($career, 'preparation.evidence_items', []))
                                    ->flatMap(fn ($item) => data_get($item, 'related_university_knowledge', []))
                                    ->filter()
                                    ->unique()
                                    ->values();
                            }
                            $curriculumSources = collect(data_get($academicProgram, 'sources', []));
                            if ($curriculumSources->isEmpty()) {
                                $curriculumSources = collect($careerSources)->filter(function ($source) {
                                    $sourceType = (string) data_get($source, 'source_type', '');
                                    $title = (string) data_get($source, 'title', '');

                                    return $sourceType === 'OFFICIAL_CURRICULUM_PDF'
                                        || preg_match('/malla|plan de estudios|curricular/i', $title) === 1;
                                })->values();
                            }
                            $programStatus = data_get($academicProgram, 'curriculum_status',
                                $programSubjects->isNotEmpty() || $curriculumSources->isNotEmpty() ? 'PARTIAL' : 'UNAVAILABLE');
                            $programMeta = $statusMeta[$programStatus] ?? $statusMeta['UNAVAILABLE'];
                            $observedPreparationAreas = (int) data_get($career, 'preparation.relations_with_observed_academic_evidence', 0);
                            $relatedPreparationAreas = (int) data_get($career, 'preparation.related_relations', 0);
                            $affinitySources = data_get($career, 'vocational_interest_relation.sources', []);
                            $preparationSourceIds = collect(data_get($career, 'preparation.evidence_items', []))
                                ->flatMap(fn ($item) => data_get($item, 'source_ids', []))
                                ->merge(collect($reinforcementAreas)->flatMap(fn ($item) => data_get($item, 'source_ids', [])))
                                ->unique()
                                ->values();
                            $preparationSources = collect($careerSources)
                                ->filter(fn ($source) => $preparationSourceIds->contains(data_get($source, 'source_id')))
                                ->values();
                            $careerId = (string) data_get($career, 'career_id');
                            $isCompared = $selectedComparisonIds->contains($careerId);
                            $canAddComparison = $careerId !== '' && ($isCompared || $selectedComparisonIds->count() < 3);
                            $nextComparisonIds = $isCompared
                                ? $selectedComparisonIds->reject(fn ($id) => $id === $careerId)->values()->all()
                                : collect($selectedComparisonIds->all())->push($careerId)->unique()->take(3)->values()->all();
                        @endphp

                        <article class="ui-panel" id="carrera-{{ $careerId }}">
                            <header class="flex flex-col gap-4 border-b pb-5 lg:flex-row lg:items-start lg:justify-between"
                                style="border-color: var(--ui-border);">
                                <div>
                                    <p class="ui-kicker">Carrera analizada</p>

                                    <h3 class="ui-title mt-1 text-2xl font-black">
                                        {{ data_get($career, 'career_name', 'Carrera') }}
                                    </h3>
                                    <p class="ui-subtitle mt-2 text-sm">
                                        Fuente académica: {{ data_get($career, 'university', 'institución no disponible') }}
                                    </p>
                                </div>

                                <div class="flex flex-wrap items-center gap-2">
                                    <span class="rounded-full border px-3 py-1 text-xs font-bold {{ $vocMeta['class'] }}">
                                        Afinidad: {{ $vocMeta['label'] }}
                                    </span>

                                    <span class="rounded-full border px-3 py-1 text-xs font-bold {{ $technicalMeta['class'] }}">
                                        BTH: {{ $technicalMeta['label'] }}
                                    </span>

                                    @if ($canAddComparison)
                                        <a href="{{ $analysisUrl(['compare' => $nextComparisonIds]) }}#mi-comparacion"
                                            class="{{ $isCompared ? 'ui-btn-secondary' : 'ui-btn-primary' }} min-h-11 text-xs">
                                            <i class="ph {{ $isCompared ? 'ph-check-circle' : 'ph-plus-circle' }}" aria-hidden="true"></i>
                                            {{ $isCompared ? 'Quitar de comparación' : 'Añadir a comparación' }}
                                        </a>
                                    @else
                                        <span class="ui-badge-muted">Comparación completa</span>
                                    @endif
                                </div>
                            </header>

                            <section class="mt-5 rounded-2xl border p-5" style="border-color: var(--ui-border); background: var(--ui-soft);" aria-labelledby="programa-{{ $careerId }}">
                                <div class="flex flex-col gap-4 lg:flex-row lg:items-start lg:justify-between">
                                    <div class="max-w-3xl">
                                        <div class="flex flex-wrap items-center gap-2">
                                            <h4 id="programa-{{ $careerId }}" class="ui-title text-lg font-black">Qué estudiarías en esta carrera</h4>
                                            <span class="inline-flex items-center gap-1.5 rounded-full border px-2.5 py-1 text-xs font-bold {{ $programMeta['class'] }}">
                                                <i class="ph {{ $programMeta['icon'] }}" aria-hidden="true"></i>
                                                {{ $programMeta['label'] }}
                                            </span>
                                        </div>
                                        <p class="ui-subtitle mt-2 text-sm leading-6">
                                            {{ data_get($academicProgram, 'professional_profile', 'El perfil profesional no está disponible en este análisis guardado. Actualiza el análisis para recuperar el resumen vigente del catálogo.') }}
                                        </p>
                                    </div>

                                    <dl class="grid min-w-56 grid-cols-2 gap-3 text-sm">
                                        <div class="rounded-xl border p-3" style="border-color: var(--ui-border); background: var(--ui-surface);">
                                            <dt class="ui-subtitle text-xs">Grado</dt>
                                            <dd class="mt-1 font-bold">{{ data_get($academicProgram, 'degree', 'No documentado') }}</dd>
                                        </div>
                                        <div class="rounded-xl border p-3" style="border-color: var(--ui-border); background: var(--ui-surface);">
                                            <dt class="ui-subtitle text-xs">Duración</dt>
                                            <dd class="mt-1 font-bold">{{ data_get($academicProgram, 'duration', 'No documentada') }}</dd>
                                        </div>
                                    </dl>
                                </div>

                                <div class="mt-5 grid gap-4 lg:grid-cols-2">
                                    <div>
                                        <h5 class="flex items-center gap-2 font-bold"><i class="ph ph-books" aria-hidden="true"></i> Áreas de formación</h5>
                                        @if ($programAreas->isNotEmpty())
                                            <ul class="mt-3 flex flex-wrap gap-2">
                                                @foreach ($programAreas as $area)
                                                    <li class="rounded-full border px-3 py-1.5 text-xs font-bold" style="border-color: var(--ui-border); background: var(--ui-surface);">{{ $area }}</li>
                                                @endforeach
                                            </ul>
                                        @else
                                            <p class="ui-subtitle mt-2 text-sm">No hay áreas oficiales suficientemente estructuradas para mostrar.</p>
                                        @endif
                                    </div>

                                    <div>
                                        <h5 class="flex items-center gap-2 font-bold"><i class="ph ph-list-checks" aria-hidden="true"></i> Materias documentadas</h5>
                                        @if ($programSubjects->isNotEmpty())
                                            <ul class="ui-subtitle mt-3 grid gap-2 text-sm sm:grid-cols-2">
                                                @foreach ($programSubjects as $subject)
                                                    <li class="flex items-start gap-2"><i class="ph ph-check mt-0.5" aria-hidden="true" style="color: var(--ui-primary);"></i><span>{{ $subject }}</span></li>
                                                @endforeach
                                            </ul>
                                        @else
                                            <p class="ui-subtitle mt-2 text-sm">La fuente de malla existe, pero sus materias todavía no están estructuradas para mostrarlas sin inferencias.</p>
                                        @endif
                                    </div>
                                </div>

                                <div class="mt-5 flex flex-col gap-3 border-t pt-4 sm:flex-row sm:items-center sm:justify-between" style="border-color: var(--ui-border);">
                                    <p class="ui-subtitle max-w-3xl text-xs leading-5">
                                        {{ $curriculumNote }}
                                    </p>
                                    <div class="flex flex-wrap gap-2">
                                        @forelse ($curriculumSources as $source)
                                            @php $curriculumUrl = data_get($source, 'reference'); @endphp
                                            @if (is_string($curriculumUrl) && in_array(parse_url($curriculumUrl, PHP_URL_SCHEME), ['http', 'https'], true))
                                                <a href="{{ $curriculumUrl }}" target="_blank" rel="noopener noreferrer" class="ui-btn-primary min-h-11 text-xs">
                                                    <i class="ph ph-arrow-square-out" aria-hidden="true"></i>
                                                    Ver malla oficial
                                                </a>
                                            @endif
                                        @empty
                                            <span class="ui-badge-muted">Fuente curricular no disponible</span>
                                        @endforelse
                                    </div>
                                </div>
                            </section>

                            <div class="mt-5 grid gap-4 lg:grid-cols-3">
                                <section class="rounded-2xl border p-4" style="border-color: var(--ui-border);">
                                    <div class="flex items-center gap-2">
                                        <i class="ph ph-heart text-lg" aria-hidden="true"></i>

                                        <h4 class="font-bold">
                                            Afinidad de intereses
                                        </h4>
                                    </div>

                                    <p class="mt-3 text-lg font-black">
                                        {{ $vocMeta['label'] }}
                                    </p>

                                    <p class="ui-subtitle mt-2 text-sm leading-6">
                                        Describe la relación observada entre intereses y evidencia
                                        ocupacional documentada. No representa aptitud.
                                    </p>

                                    <div class="mt-4 border-t pt-3 text-xs" style="border-color: var(--ui-border);">
                                        <p class="font-bold">Fuente de la afinidad</p>
                                        @forelse ($affinitySources as $source)
                                            @php
                                                $affinityUrl = data_get($source, 'reference');
                                            @endphp
                                            <div class="mt-2">
                                                @if (is_string($affinityUrl) && str_starts_with($affinityUrl, 'http'))
                                                    <a href="{{ $affinityUrl }}" target="_blank" rel="noopener noreferrer"
                                                        class="font-bold underline underline-offset-2">
                                                        {{ data_get($source, 'title', 'Ver fuente') }}
                                                    </a>
                                                @else
                                                    <span>{{ data_get($source, 'title', 'Fuente documentada') }}</span>
                                                @endif
                                            </div>
                                        @empty
                                            <p class="ui-subtitle mt-2">Todavía no contamos con una referencia ocupacional revisada para relacionar esta carrera con tus intereses.</p>
                                        @endforelse
                                    </div>
                                </section>

                                <section class="rounded-2xl border p-4" style="border-color: var(--ui-border);">
                                    <div class="flex items-center gap-2">
                                        <i class="ph ph-graduation-cap text-lg" aria-hidden="true"></i>

                                        <h4 class="font-bold">
                                            Preparación académica
                                        </h4>
                                    </div>

                                    <p class="mt-3 leading-7">
                                        @if ($relatedPreparationAreas > 0 && $observedPreparationAreas > 0)
                                            Encontramos información de tus materias relacionada con {{ $observedPreparationAreas }} de {{ $relatedPreparationAreas }} áreas formativas revisadas. Úsala para reconocer bases y aspectos que podrías reforzar; no es una nota final sobre tu capacidad.
                                        @elseif ($relatedPreparationAreas > 0)
                                            Aún no encontramos suficientes datos de tus materias para explicar tu preparación en las {{ $relatedPreparationAreas }} áreas formativas revisadas.
                                        @else
                                            Todavía no contamos con una relación académica revisada para explicar tu preparación en esta carrera.
                                        @endif
                                    </p>

                                    <div class="mt-4 border-t pt-3 text-xs" style="border-color: var(--ui-border);">
                                        <p class="font-bold">Fuente de la preparación</p>
                                        @forelse ($preparationSources as $source)
                                            @php
                                                $preparationUrl = data_get($source, 'reference');
                                            @endphp
                                            <div class="mt-2">
                                                @if (is_string($preparationUrl) && str_starts_with($preparationUrl, 'http'))
                                                    <a href="{{ $preparationUrl }}" target="_blank" rel="noopener noreferrer"
                                                        class="font-bold underline underline-offset-2">
                                                        {{ data_get($source, 'title', 'Ver fuente') }}
                                                    </a>
                                                @else
                                                    <span>{{ data_get($source, 'title', 'Fuente documentada') }}</span>
                                                @endif
                                            </div>
                                        @empty
                                            <p class="ui-subtitle mt-2">Todavía no contamos con una referencia académica revisada para esta carrera.</p>
                                        @endforelse
                                    </div>
                                </section>

                                <section class="rounded-2xl border p-4" style="border-color: var(--ui-border);">
                                    <div class="flex items-center gap-2">
                                        <i class="ph ph-database text-lg" aria-hidden="true"></i>

                                        <h4 class="font-bold">
                                            Información utilizada
                                        </h4>
                                    </div>

                                    <dl class="mt-3 space-y-2 text-sm">
                                        <div class="flex justify-between gap-3">
                                                <dt class="ui-subtitle">Registros académicos</dt>
                                            <dd class="font-black">
                                                {{ data_get($career, 'evidence_quality.academic_record_count', 0) }}
                                            </dd>
                                        </div>

                                        <div class="flex justify-between gap-3">
                                            <dt class="ui-subtitle">Materias distintas</dt>
                                            <dd class="font-black">
                                                {{ data_get($career, 'evidence_quality.distinct_subject_count', 0) }}
                                            </dd>
                                        </div>

                                        <div class="flex justify-between gap-3">
                                            <dt class="ui-subtitle">Periodos ordenados</dt>
                                            <dd class="font-black">
                                                {{ data_get($career, 'evidence_quality.ordered_period_count', 0) }}
                                            </dd>
                                        </div>
                                    </dl>
                                </section>
                            </div>

                            <div class="mt-5 grid gap-4 lg:grid-cols-2">
                                <x-plegable-institucional class="rounded-2xl border p-4" style="border-color: var(--ui-border);" icono="ph-graduation-cap"><x-slot:titulo>Evidencia y áreas de refuerzo</x-slot:titulo>

                                    <div class="mt-4 space-y-4">
                                        @if (!empty($reinforcementAreas))
                                            <div>
                                                <h4 class="text-sm font-bold">
                                                    Áreas de refuerzo observadas
                                                </h4>

                                                <ul class="ui-subtitle mt-2 list-disc space-y-2 pl-5 text-sm">
                                                    @foreach ($reinforcementAreas as $area)
                                                        <li>
                                                            <strong>
                                                                {{ data_get($area, 'competency', 'Competencia') }}
                                                            </strong>

                                                            @if (data_get($area, 'rationale'))
                                                                — {{ data_get($area, 'rationale') }}
                                                            @endif
                                                        </li>
                                                    @endforeach
                                                </ul>
                                            </div>
                                        @endif

                                        @if (!empty($areasWithoutEvidence))
                                            <div>
                                                <h4 class="text-sm font-bold">
                                                    Áreas sin evidencia observada
                                                </h4>

                                                <ul class="ui-subtitle mt-2 list-disc space-y-2 pl-5 text-sm">
                                                    @foreach ($areasWithoutEvidence as $area)
                                                        <li>{{ is_array($area) ? data_get($area, 'label', json_encode($area)) : $area }}
                                                        </li>
                                                    @endforeach
                                                </ul>
                                            </div>
                                        @endif

                                        @if (empty($reinforcementAreas) && empty($areasWithoutEvidence))
                                            <p class="ui-subtitle text-sm">
                                                No se reportaron áreas adicionales para este perfil.
                                            </p>
                                        @endif
                                    </div>
                                </x-plegable-institucional>

                                <x-plegable-institucional class="rounded-2xl border p-4" style="border-color: var(--ui-border);" icono="ph-file-text"><x-slot:titulo>Fuentes y limitaciones</x-slot:titulo>

                                    <div class="mt-4 space-y-4">
                                        <div>
                                            <h4 class="text-sm font-bold">
                                                Fuentes utilizadas
                                            </h4>

                                            @if (!empty($careerSources))
                                                <ul class="mt-3 space-y-3 text-sm">
                                                    @foreach ($careerSources as $source)
                                                        @php
                                                            $officialUrl = data_get($source, 'reference');
                                                        @endphp
                                                        <li class="rounded-xl border p-3" style="border-color: var(--ui-border);">
                                                            <p class="font-bold">
                                                                {{ data_get($source, 'title', 'Fuente documentada') }}
                                                            </p>
                                                            @if (is_string($officialUrl) && str_starts_with($officialUrl, 'http'))
                                                                <a href="{{ $officialUrl }}" target="_blank" rel="noopener noreferrer"
                                                                    class="ui-button-secondary mt-3 inline-flex text-xs">
                                                                    <i class="ph ph-arrow-square-out" aria-hidden="true"></i>
                                                                    Ver información oficial
                                                                </a>
                                                            @endif
                                                        </li>
                                                    @endforeach
                                                </ul>
                                            @else
                                                <p class="ui-subtitle mt-2 text-sm">
                                                    No se reportaron fuentes adicionales.
                                                </p>
                                            @endif
                                        </div>

                                        @if (!empty($careerLimitations))
                                            <div>
                                                <h4 class="text-sm font-bold">
                                                    Limitaciones
                                                </h4>

                                                <ul class="ui-subtitle mt-2 list-disc space-y-2 pl-5 text-sm">
                                                    @foreach ($careerLimitations as $limitation)
                                                        <li>
                                                            {{ data_get($limitation, 'message', is_string($limitation) ? $limitation : '') }}
                                                        </li>
                                                    @endforeach
                                                </ul>
                                            </div>
                                        @endif
                                    </div>
                                </x-plegable-institucional>
                            </div>
                        </article>
                    @empty
                        <section class="ui-panel text-center">
                            <i class="ph ph-magnifying-glass text-3xl" aria-hidden="true"></i>

                            <h3 class="mt-3 font-bold">
                                No hay perfiles de carrera disponibles
                            </h3>

                            <p class="ui-subtitle mt-2">
                                El análisis no devolvió opciones documentadas para mostrar.
                            </p>
                        </section>
                    @endforelse
                </div>
            </section>

            @if ($visibleExternalCareers->isNotEmpty())
                <section class="ui-panel" aria-labelledby="external-careers-title">
                    <div class="flex items-start gap-3">
                        <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-sky-100 text-sky-700 dark:bg-sky-950/50 dark:text-sky-300">
                            <i class="ph ph-info" aria-hidden="true"></i>
                        </span>
                        <div>
                            <p class="ui-kicker">Información para explorar</p>
                            <h2 id="external-careers-title" class="ui-title mt-1 text-xl font-black">
                                Opciones con información pendiente de revisión
                            </h2>
                            <p class="ui-subtitle mt-2 text-sm leading-6">
                                Puedes consultar estas páginas oficiales, pero su contenido todavía no influye en tu análisis porque no ha podido revisarse por completo.
                            </p>
                        </div>
                    </div>

                    <div class="mt-5 grid gap-4 lg:grid-cols-2">
                        @foreach ($visibleExternalCareers as $externalCareer)
                            <article class="rounded-2xl border p-5" style="border-color: var(--ui-border);">
                                <p class="ui-kicker">{{ data_get($externalCareer, 'university', 'Universidad') }}</p>
                                <h3 class="ui-title mt-1 text-lg font-black">
                                    {{ data_get($externalCareer, 'career_name', 'Carrera') }}
                                </h3>
                                <div class="mt-3 flex flex-wrap gap-2 text-xs font-bold">
                                    <span class="rounded-full border px-3 py-1" style="border-color: var(--ui-border);">
                                        Página oficial
                                    </span>
                                    <span class="rounded-full border px-3 py-1" style="border-color: var(--ui-border);">
                                        Pendiente de revisión
                                    </span>
                                </div>

                                @foreach (data_get($externalCareer, 'sources', []) as $source)
                                    @php
                                        $externalUrl = data_get($source, 'reference');
                                    @endphp
                                    <div class="mt-4 rounded-xl border p-3" style="border-color: var(--ui-border);">
                                        <p class="font-bold">{{ data_get($source, 'title', 'Fuente oficial externa') }}</p>
                                        @if (is_string($externalUrl) && str_starts_with($externalUrl, 'http'))
                                            <a href="{{ $externalUrl }}" target="_blank" rel="noopener noreferrer"
                                                class="ui-button-secondary mt-3 inline-flex text-xs">
                                                <i class="ph ph-arrow-square-out" aria-hidden="true"></i>
                                                Ver información oficial
                                            </a>
                                        @endif
                                    </div>
                                @endforeach

                                <p class="ui-subtitle mt-4 text-sm leading-6">
                                    No pudimos revisar el contenido completo de esta página. Ábrela para verificar directamente la información publicada por la institución.
                                </p>
                            </article>
                        @endforeach
                    </div>
                </section>
            @endif


            {{-- Orientación para interpretar el resultado --}}
            <section class="ui-panel">
                <div class="flex items-start gap-3">
                    <span
                        class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl"
                        style="background: var(--ui-primary-soft); color: var(--ui-primary);">
                        <i class="ph ph-lightbulb text-lg" aria-hidden="true"></i>
                    </span>

                    <div class="min-w-0 flex-1">
                        <h2 class="ui-title text-xl font-black">
                            Cómo interpretar tus resultados
                        </h2>

                        <p class="ui-subtitle mt-2 text-sm leading-6">
                            Lee el resultado como un punto de partida para conocerte mejor y comparar opciones con calma.
                        </p>

                        @if ($studentAnalysisGuidance->isNotEmpty())
                            <div class="mt-4 grid gap-3 md:grid-cols-2">
                                @foreach ($studentAnalysisGuidance as $guidance)
                                    <article class="rounded-2xl border p-4" style="border-color: var(--ui-border); background: var(--ui-soft);">
                                        <div class="flex items-start gap-3">
                                            <i class="ph {{ $guidance['icon'] }} mt-0.5 text-xl" aria-hidden="true" style="color: var(--ui-primary);"></i>
                                            <div>
                                                <h3 class="font-bold">{{ $guidance['title'] }}</h3>
                                                <p class="ui-subtitle mt-1 text-sm leading-6">{{ $guidance['message'] }}</p>
                                            </div>
                                        </div>
                                    </article>
                                @endforeach
                            </div>
                        @else
                            <p class="ui-subtitle mt-2 text-sm">
                                El análisis no reportó advertencias adicionales para mostrarte.
                            </p>
                        @endif

                        <div class="mt-4 flex flex-col gap-3 rounded-2xl border p-4 sm:flex-row sm:items-center sm:justify-between" style="border-color: var(--ui-border);">
                            <p class="ui-subtitle text-sm leading-6">
                                ¿Algo no queda claro? El tutor puede explicarte el resultado con ejemplos sencillos.
                            </p>
                            <a href="{{ route('aula-virtual.estudiante.orientacion.aporte', ['section' => 'tutor']) }}" class="ui-btn-secondary min-h-11 shrink-0 text-sm">
                                <i class="ph ph-chats-circle" aria-hidden="true"></i>
                                Preguntar al tutor
                            </a>
                        </div>
                    </div>
                </div>
            </section>

        @else
            <section class="ui-panel text-center">
                <span class="mx-auto flex h-14 w-14 items-center justify-center rounded-2xl"
                    style="background: var(--ui-soft);">
                    <i class="ph ph-chart-line-up text-2xl" aria-hidden="true"></i>
                </span>

                <h2 class="ui-title mt-4 text-xl font-black">
                    Todavía no tienes un análisis guardado
                </h2>

                <p class="ui-subtitle mx-auto mt-2 max-w-xl leading-6">
                    Cuando completes los requisitos mínimos podrás generar un análisis basado
                    en la información académica y vocacional disponible.
                </p>
            </section>
        @endif


        {{-- =========================================================
        FUENTES / TUTOR
        ========================================================== --}}
    @elseif (in_array($section, ['fuentes', 'tutor'], true))

        @php
            $isSources = $section === 'fuentes';
        @endphp

        <section class="grid gap-6 lg:grid-cols-[.72fr_.28fr]">
            <div class="ui-panel">
                <p class="ui-kicker">
                    {{ $isSources ? 'Consulta documental' : 'Orientación con evidencia' }}
                </p>

                <div class="mt-1 flex flex-wrap items-center justify-between gap-3">
                    <h2 class="ui-title text-2xl font-black">
                        {{ $isSources ? 'Fuentes e información' : 'Tutor académico-vocacional' }}
                    </h2>

                    @if (!$isSources && !empty($conversationHistory))
                        <form method="POST" action="{{ route('aula-virtual.estudiante.orientacion.aporte.tutor.reset') }}">
                            @csrf
                            <button type="submit" class="ui-btn-secondary text-sm">
                                <i class="ph ph-plus-circle" aria-hidden="true"></i>
                                Nueva conversación
                            </button>
                        </form>
                    @endif
                </div>

                <p class="ui-subtitle mt-3 max-w-3xl leading-7">
                    @if ($isSources)
                        Busca información dentro del catálogo documental disponible.
                        La recuperación puede omitir evidencia y no sustituye la revisión
                        de las fuentes originales.
                    @else
                        Conversa sobre carreras, preparación, materias o pide una explicación
                        paso a paso. Los datos institucionales se respaldan con fuentes; las
                        preguntas pedagógicas pueden usar el modelo local cuando está disponible.
                    @endif
                </p>

                @if (!$isSources && !empty($conversationHistory))
                    <div class="mt-6 space-y-3" aria-label="Historial reciente del tutor">
                        @foreach ($conversationHistory as $turn)
                            <div @class([
                                'max-w-[92%] rounded-2xl border px-4 py-3 text-sm leading-6',
                                'ml-auto' => data_get($turn, 'role') === 'user',
                                'mr-auto' => data_get($turn, 'role') !== 'user',
                            ]) style="border-color: var(--ui-border); background: {{ data_get($turn, 'role') === 'user' ? 'var(--ui-soft)' : 'var(--ui-surface)' }};">
                                <p class="mb-1 text-xs font-bold uppercase tracking-wide" style="color: var(--ui-muted);">
                                    {{ data_get($turn, 'role') === 'user' ? 'Tú' : 'Tutor SAVP' }}
                                </p>
                                <p class="whitespace-pre-wrap">{{ data_get($turn, 'content') }}</p>
                            </div>
                        @endforeach
                    </div>
                @endif

                <form class="mt-6 space-y-4" method="POST"
                    action="{{ route('aula-virtual.estudiante.orientacion.aporte.query') }}" x-data="{ submitting: false }"
                    @submit="submitting = true">
                    @csrf

                    <input type="hidden" name="mode" value="{{ $section }}">

                    <div>
                        <label for="orientation-question" class="block font-bold">
                            {{ $isSources ? '¿Qué información quieres buscar?' : '¿Qué quieres consultar?' }}
                        </label>

                        <textarea id="orientation-question" class="ui-input mt-2 min-h-[130px] w-full resize-y"
                            name="question" minlength="2" maxlength="1000" rows="5" required
                            placeholder="{{ $isSources
            ? 'Ejemplo: materias de primer semestre de Ingeniería de Sistemas'
            : 'Ejemplo: ¿qué evidencia académica debo considerar al explorar Ingeniería Civil?' }}">{{ $question ?? old('question') }}</textarea>

                        <div class="mt-2 flex justify-between gap-3 text-xs" style="color: var(--ui-muted);">
                            <span>
                                Evita incluir datos personales sensibles.
                            </span>

                            <span>
                                Máximo 1000 caracteres
                            </span>
                        </div>
                    </div>

                    <button type="submit" class="ui-btn-primary" :disabled="submitting">
                        <i class="ph"
                            :class="submitting ? 'ph-circle-notch animate-spin' : '{{ $isSources ? 'ph-magnifying-glass' : 'ph-paper-plane-tilt' }}'"
                            aria-hidden="true"></i>

                        <span x-show="!submitting">
                            {{ $isSources ? 'Buscar en las fuentes' : 'Consultar al tutor' }}
                        </span>

                        <span x-show="submitting" x-cloak>
                            Consultando evidencia…
                        </span>
                    </button>
                </form>
            </div>

            <aside class="ui-panel">
                <div class="flex items-start gap-3">
                    <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl"
                        style="background: var(--ui-soft);">
                        <i class="ph ph-shield-check text-lg" aria-hidden="true"></i>
                    </span>

                    <div>
                        <h2 class="font-bold">
                            Respuesta responsable
                        </h2>

                        <p class="ui-subtitle mt-2 text-sm leading-6">
                            La respuesta se apoya en fuentes revisadas. Si la información
                            disponible es insuficiente, el sistema debe indicarlo en lugar de
                            completar la respuesta con información inventada.
                        </p>
                    </div>
                </div>
            </aside>
        </section>


        @isset($queryResult)
            @if (!$queryResult->available)
                <section
                    class="rounded-2xl border border-amber-200 bg-amber-50 p-5 text-amber-800 dark:border-amber-800/60 dark:bg-amber-950/30 dark:text-amber-200"
                    role="alert">
                    <div class="flex items-start gap-3">
                        <i class="ph ph-warning-circle mt-0.5 text-xl" aria-hidden="true"></i>

                        <div>
                            <h2 class="font-bold">
                                No fue posible completar la consulta
                            </h2>

                            <p class="mt-1 text-sm">
                                {{ $queryResult->message }}
                            </p>
                        </div>
                    </div>
                </section>
            @else
                <section class="ui-panel" role="status" aria-live="polite">
                    @if (!$isSources)
                        <div class="border-b pb-5" style="border-color: var(--ui-border);">
                            <div class="flex flex-wrap items-center justify-between gap-3">
                                <p class="ui-kicker">Respuesta</p>
                                @if ($queryAnswerMode)
                                    <span class="inline-flex items-center gap-1.5 rounded-full border px-2.5 py-1 text-xs font-bold"
                                        style="border-color: var(--ui-border); color: var(--ui-muted);">
                                        <i class="ph ph-books" aria-hidden="true"></i>
                                        Tutor de conocimiento validado
                                    </span>
                                @endif
                            </div>

                            <div class="mt-3 whitespace-pre-wrap leading-7">
                                {{ data_get($queryData, 'answer', 'No se generó una respuesta textual.') }}
                            </div>

                            @if (!empty($querySuggestedTopics))
                                <div class="mt-4 flex flex-wrap gap-2" aria-label="Temas sugeridos">
                                    @foreach ($querySuggestedTopics as $topic)
                                        <span class="rounded-full border px-3 py-1.5 text-xs font-semibold"
                                            style="border-color: var(--ui-border); color: var(--ui-muted);">
                                            {{ $topic }}
                                        </span>
                                    @endforeach
                                </div>
                            @endif
                        </div>
                    @endif

                    @if ($queryInsufficient)
                        <div
                            class="mt-5 rounded-2xl border border-amber-200 bg-amber-50 p-4 text-amber-800 dark:border-amber-800/60 dark:bg-amber-950/30 dark:text-amber-200">
                            <div class="flex items-start gap-3">
                                <i class="ph ph-info mt-0.5 text-xl" aria-hidden="true"></i>

                                <div>
                                    <h3 class="font-bold">
                                        Evidencia insuficiente
                                    </h3>

                                    <p class="mt-1 text-sm leading-6">
                                        Los resultados recuperados no son suficientes para
                                        responder con seguridad. Intenta reformular la consulta
                                        indicando carrera, institución, asignatura o contexto.
                                    </p>
                                </div>
                            </div>
                        </div>
                    @endif

                    <div class="mt-6">
                        <div class="flex flex-wrap items-end justify-between gap-3">
                            <div>
                                <p class="ui-kicker">
                                    Evidencia recuperada
                                </p>

                                <h2 class="ui-title mt-1 text-xl font-black">
                                    Fuentes relacionadas con tu consulta
                                </h2>
                            </div>

                            <span class="ui-subtitle text-sm">
                                {{ count($querySources) }} resultado(s)
                            </span>
                        </div>

                        <div class="mt-4 space-y-3">
                            @forelse ($querySources as $source)
                                @php
                                    $sourceReference = data_get($source, 'reference');
                                    $safeSourceReference = is_string($sourceReference)
                                        && in_array(parse_url($sourceReference, PHP_URL_SCHEME), ['http', 'https'], true)
                                        ? $sourceReference
                                        : null;
                                @endphp
                                <article class="rounded-2xl border p-4" style="border-color: var(--ui-border);">
                                    <div class="flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
                                        <div>
                                            <h3 class="font-bold">
                                                {{ data_get($source, 'title', 'Fuente documentada') }}
                                            </h3>

                                            <p class="ui-subtitle mt-1 text-sm">
                                                {{ data_get($source, 'institution', 'Institución no especificada') }}
                                            </p>
                                        </div>

                                        <span @class([
                                            'inline-flex w-fit items-center gap-1.5 rounded-full border px-2.5 py-1 text-xs font-bold',
                                            'border-emerald-200 bg-emerald-50 text-emerald-700 dark:border-emerald-800/60 dark:bg-emerald-950/30 dark:text-emerald-300' => data_get($source, 'official', false),
                                            'border-amber-200 bg-amber-50 text-amber-700 dark:border-amber-800/60 dark:bg-amber-950/30 dark:text-amber-300' => !data_get($source, 'official', false),
                                        ])>
                                            <i class="ph {{ data_get($source, 'official', false) ? 'ph-seal-check' : 'ph-warning-circle' }}"
                                                aria-hidden="true"></i>

                                            {{ data_get($source, 'official', false) ? 'Fuente oficial' : 'Revisar procedencia' }}
                                        </span>
                                    </div>

                                    @if (data_get($source, 'summary'))
                                        <p class="mt-3 leading-7">
                                            {{ data_get($source, 'summary') }}
                                        </p>
                                    @endif

                                    @if ($safeSourceReference)
                                        <a class="mt-3 inline-flex items-center gap-1.5 break-all text-xs font-semibold underline underline-offset-4"
                                            href="{{ $safeSourceReference }}" target="_blank" rel="noopener noreferrer">
                                            Abrir fuente original
                                            <i class="ph ph-arrow-square-out" aria-hidden="true"></i>
                                        </a>
                                    @endif
                                </article>
                            @empty
                                <div class="rounded-2xl border border-dashed p-6 text-center" style="border-color: var(--ui-border);">
                                    <i class="ph ph-file-dashed text-2xl" aria-hidden="true"></i>

                                    <p class="ui-subtitle mt-2">
                                        No se recuperaron fuentes para mostrar.
                                    </p>
                                </div>
                            @endforelse
                        </div>
                    </div>

                    @if (!empty($queryWarnings))
                        <section class="mt-6 rounded-2xl border p-4" style="border-color: var(--ui-border);">
                            <h3 class="flex items-center gap-2 font-bold">
                                <i class="ph ph-warning" aria-hidden="true"></i>
                                Advertencias de la consulta
                            </h3>

                            <ul class="ui-subtitle mt-3 list-disc space-y-2 pl-5 text-sm">
                                @foreach ($queryWarnings as $warning)
                                    <li>
                                        {{ is_array($warning) ? data_get($warning, 'message', '') : $warning }}
                                    </li>
                                @endforeach
                            </ul>
                        </section>
                    @endif

                    <div class="mt-6 rounded-2xl border p-4" style="border-color: var(--ui-border);">
                        <p class="ui-subtitle text-sm leading-6">
                            Las citas te permiten comprobar de dónde proviene la información. Las fuentes disponibles todavía no representan toda la oferta académica de Bolivia.
                        </p>
                    </div>
                </section>
            @endif
        @endisset


        {{-- =========================================================
        FALLBACK DE SECCIÓN
        ========================================================== --}}
    @else

        <section class="ui-panel text-center">
            <i class="ph ph-warning-circle text-3xl" aria-hidden="true"></i>

            <h2 class="ui-title mt-3 text-xl font-black">
                Sección no disponible
            </h2>

            <p class="ui-subtitle mt-2">
                La sección solicitada no existe dentro del módulo de orientación.
            </p>

            <a href="{{ route('aula-virtual.estudiante.orientacion.aporte', ['section' => 'perfil']) }}"
                class="ui-btn-primary mt-5">
                Volver al estado de mi perfil
            </a>
        </section>

    @endif
</div>
@endsection
