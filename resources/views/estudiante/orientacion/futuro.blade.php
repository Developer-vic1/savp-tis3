@extends('aula-virtual.layouts.app')

@section('title', 'Mi futuro académico | SAVP-TIS3')
@section('page-title', 'Mi futuro académico')

@section('content')
@php
    $analysis = $activity?->analysis_snapshot;
    $careerProfiles = collect(data_get($analysis, 'career_evidence_profiles', []));
    $externalProfiles = collect(data_get($analysis, 'informational_external_careers', []));
    $mode = (string) request('explore', 'analysis');
    $careerKeyFor = static fn ($career): string => \Illuminate\Support\Str::slug((string) data_get($career, 'career_name', data_get($career, 'career_id', '')));
    $catalog = $careerProfiles->concat($externalProfiles)->map(fn ($career) => [
        'key' => $careerKeyFor($career),
        'name' => (string) data_get($career, 'career_name', 'Carrera'),
        'university' => (string) data_get($career, 'university', ''),
    ])->filter(fn ($career) => $career['key'] !== '' && $career['university'] !== '')->values();
    $careers = $catalog->groupBy('key')->map(fn ($items, $key) => [
        'key' => $key,
        'name' => data_get($items->first(), 'name'),
    ])->sortBy('name', SORT_NATURAL | SORT_FLAG_CASE)->values();
    $universities = $catalog->pluck('university')->unique()->sort()->values();
    $requestedCareer = trim((string) request('career', ''));
    $selectedCareer = $careers->contains(fn ($career) => $career['key'] === $requestedCareer) ? $requestedCareer : '';
    $careerUniversities = $selectedCareer === '' ? collect() : $catalog->where('key', $selectedCareer)->pluck('university')->unique()->sort()->values();
    $requestedUniversity = trim((string) request('university', ''));
    $allowedUniversities = $mode === 'universities' ? $universities : $careerUniversities;
    $selectedUniversity = $allowedUniversities->contains($requestedUniversity) ? $requestedUniversity : '';
    $isRelated = static fn ($career): bool => in_array(data_get($career, 'vocational_interest_relation.status'), ['AVAILABLE', 'PARTIAL'], true)
        || (int) data_get($career, 'preparation.relations_with_observed_academic_evidence', 0) > 0
        || (int) data_get($career, 'preparation.related_relations', 0) > 0;
    $interestCareerNames = $careerProfiles->filter($isRelated)
        ->unique(fn ($career) => mb_strtolower(trim((string) data_get($career, 'career_name'))))
        ->take(3)
        ->pluck('career_name')
        ->all();
    $visibleProfiles = match ($mode) {
        'analysis' => $careerProfiles->filter(fn ($career) => in_array(data_get($career, 'career_name'), $interestCareerNames, true))->values(),
        'universities' => $selectedUniversity === '' ? collect() : $careerProfiles->where('university', $selectedUniversity)->values(),
        default => $careerProfiles
            ->when($selectedCareer !== '', fn ($items) => $items->filter(fn ($career) => $careerKeyFor($career) === $selectedCareer))
            ->when($selectedUniversity !== '', fn ($items) => $items->where('university', $selectedUniversity))
            ->values(),
    };
    $visibleExternal = match ($mode) {
        'analysis' => collect(),
        'universities' => $selectedUniversity === '' ? collect() : $externalProfiles->where('university', $selectedUniversity)->values(),
        default => $externalProfiles
            ->when($selectedCareer !== '', fn ($items) => $items->filter(fn ($career) => $careerKeyFor($career) === $selectedCareer))
            ->when($selectedUniversity !== '', fn ($items) => $items->where('university', $selectedUniversity))
            ->values(),
    };
    $modes = [
        'analysis' => ['label' => 'Según mis intereses', 'icon' => 'ph-sparkle', 'text' => 'Universidades que ofrecen las tres carreras presentadas en tu análisis RIASEC.'],
        'universities' => ['label' => 'Elegir universidad', 'icon' => 'ph-buildings', 'text' => 'Selecciona una institución y consulta las carreras documentadas que ofrece.'],
        'all' => ['label' => 'Todas las carreras', 'icon' => 'ph-books', 'text' => 'Revisa el catálogo completo y filtra por carrera cuando lo necesites.'],
    ];
@endphp

<div class="mx-auto max-w-7xl space-y-6">
    <section class="ui-panel">
        <p class="ui-kicker">Exploración académica</p>
        <h1 class="ui-title mt-2 text-3xl font-black">Carreras, universidades y planes de estudio</h1>
        <p class="ui-subtitle mt-3 max-w-3xl leading-7">Este espacio sirve exclusivamente para explorar opciones académicas. Tu análisis de intereses permanece separado en “Mis intereses”.</p>

        <nav class="mt-6 grid gap-3 md:grid-cols-3" aria-label="Formas de explorar el futuro académico">
            @foreach ($modes as $modeKey => $modeMeta)
                @php $active = $mode === $modeKey; @endphp
                <a href="{{ route('estudiante.futuro', ['explore' => $modeKey]) }}" @if($active) aria-current="page" @endif class="rounded-2xl border p-4 transition hover:-translate-y-0.5 hover:shadow-sm" style="border-color: {{ $active ? 'var(--ui-primary)' : 'var(--ui-border)' }}; background: {{ $active ? 'var(--ui-soft)' : 'var(--ui-surface)' }};">
                    <div class="flex items-start gap-3"><span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl" style="background: {{ $active ? 'var(--ui-primary)' : 'var(--ui-soft)' }}; color: {{ $active ? 'white' : 'var(--ui-primary)' }};"><i class="ph {{ $modeMeta['icon'] }}" aria-hidden="true"></i></span><span><strong class="block">{{ $modeMeta['label'] }}</strong><span class="ui-subtitle mt-1 block text-sm leading-5">{{ $modeMeta['text'] }}</span></span></div>
                </a>
            @endforeach
        </nav>
    </section>

    @if (!$analysis)
        <section class="ui-panel text-center"><i class="ph ph-compass text-3xl" aria-hidden="true"></i><h2 class="ui-title mt-3 text-xl font-black">Primero completa tu análisis de intereses</h2><p class="ui-subtitle mt-2">Después podrás explorar opciones con información contextualizada.</p><a href="{{ route('estudiante.intereses') }}" class="ui-btn-primary mt-5 inline-flex">Ir a Mis intereses</a></section>
    @else
        @if ($mode === 'universities')
            <section class="ui-panel">
                <form method="GET" action="{{ route('estudiante.futuro') }}" class="grid gap-4 md:grid-cols-[minmax(0,1fr)_auto] md:items-end">
                    <input type="hidden" name="explore" value="universities">
                    <label><span class="mb-2 block text-sm font-bold">Universidad que quieres explorar</span><select name="university" class="ui-input w-full" required><option value="">Selecciona una universidad</option>@foreach($universities as $university)<option value="{{ $university }}" @selected($selectedUniversity === $university)>{{ $university }}</option>@endforeach</select><span class="ui-subtitle mt-2 block text-xs">El sistema no elige la institución por ti.</span></label>
                    <button class="ui-btn-primary min-h-11" type="submit"><i class="ph ph-buildings" aria-hidden="true"></i>Ver carreras</button>
                </form>
            </section>
        @elseif ($mode === 'all')
            <section class="ui-panel">
                <form method="GET" action="{{ route('estudiante.futuro') }}" class="grid gap-4 lg:grid-cols-[minmax(0,1fr)_minmax(0,1fr)_auto] lg:items-end">
                    <input type="hidden" name="explore" value="all">
                    <label><span class="mb-2 block text-sm font-bold">Carrera</span><select name="career" class="ui-input w-full"><option value="">Todas las carreras</option>@foreach($careers as $career)<option value="{{ $career['key'] }}" @selected($selectedCareer === $career['key'])>{{ $career['name'] }}</option>@endforeach</select></label>
                    <label><span class="mb-2 block text-sm font-bold">Universidad</span><select name="university" class="ui-input w-full" @disabled($selectedCareer === '')>@if($selectedCareer === '')<option value="">Primero elige una carrera</option>@else<option value="">Todas las universidades que ofrecen esta carrera</option>@foreach($careerUniversities as $university)<option value="{{ $university }}" @selected($selectedUniversity === $university)>{{ $university }}</option>@endforeach@endif</select></label>
                    <button class="ui-btn-primary min-h-11" type="submit"><i class="ph ph-funnel" aria-hidden="true"></i>Aplicar filtros</button>
                </form>
            </section>
        @else
            <section class="ui-panel"><div class="flex items-start gap-3"><i class="ph ph-info mt-0.5 text-xl" aria-hidden="true" style="color: var(--ui-primary);"></i><div><h2 class="font-black">Universidades para tus tres opciones de exploración</h2><p class="ui-subtitle mt-1 text-sm leading-6"><strong class="text-current">No es un ranking.</strong> Aquí ves qué universidades documentadas ofrecen las carreras mostradas en “Mis intereses”. Tú decides cuál institución consultar.</p></div></div></section>
        @endif

        @if ($mode === 'universities' && $selectedUniversity === '')
            <section class="ui-panel text-center"><i class="ph ph-buildings text-3xl" aria-hidden="true"></i><h2 class="ui-title mt-3 text-xl font-black">Elige una universidad para ver sus carreras</h2><p class="ui-subtitle mt-2">Luego podrás revisar materias, duración y malla oficial.</p></section>
        @else
            <section class="ui-panel">
                <div class="flex flex-wrap items-end justify-between gap-3"><div><p class="ui-kicker">Opciones disponibles</p><h2 class="ui-title mt-1 text-2xl font-black">{{ $mode === 'analysis' ? 'Universidades y carreras relacionadas' : 'Perfiles académicos' }}</h2></div><span class="ui-subtitle text-sm">{{ $visibleProfiles->count() + $visibleExternal->count() }} opción(es)</span></div>
                <div class="mt-6 grid gap-5 xl:grid-cols-2">
                    @forelse ($visibleProfiles as $career)
                        @php
                            $program = data_get($career, 'academic_program', []);
                            $subjects = collect(data_get($program, 'documented_subjects', []))->filter()->take(8);
                            $areas = collect(data_get($program, 'knowledge_areas', []))->filter()->take(8);
                            $sources = collect(data_get($program, 'sources', []));
                        @endphp
                        <article class="rounded-2xl border p-5" style="border-color: var(--ui-border);">
                            <p class="ui-kicker">{{ data_get($career, 'university', 'Universidad') }}</p>
                            <h3 class="ui-title mt-1 text-xl font-black">{{ data_get($career, 'career_name', 'Carrera') }}</h3>
                            @if(data_get($program, 'professional_profile'))<p class="ui-subtitle mt-3 leading-6">{{ data_get($program, 'professional_profile') }}</p>@endif
                            <dl class="mt-4 grid grid-cols-2 gap-3 text-sm"><div class="rounded-xl p-3" style="background: var(--ui-soft);"><dt class="font-bold">Grado</dt><dd class="ui-subtitle mt-1">{{ data_get($program, 'degree', 'Por verificar') }}</dd></div><div class="rounded-xl p-3" style="background: var(--ui-soft);"><dt class="font-bold">Duración</dt><dd class="ui-subtitle mt-1">{{ data_get($program, 'duration', 'Por verificar') }}</dd></div></dl>
                            @if($areas->isNotEmpty())<div class="mt-4"><h4 class="font-bold">Áreas de formación</h4><div class="mt-2 flex flex-wrap gap-2">@foreach($areas as $area)<span class="rounded-full border px-3 py-1 text-xs" style="border-color: var(--ui-border);">{{ $area }}</span>@endforeach</div></div>@endif
                            <div class="mt-4"><h4 class="font-bold">Materias documentadas</h4>@if($subjects->isNotEmpty())<ul class="ui-subtitle mt-2 grid gap-2 text-sm sm:grid-cols-2">@foreach($subjects as $subject)<li class="flex gap-2"><i class="ph ph-check mt-0.5" aria-hidden="true" style="color: var(--ui-primary);"></i>{{ $subject }}</li>@endforeach</ul>@else<p class="ui-subtitle mt-2 text-sm">La malla existe, pero sus materias todavía no están estructuradas para mostrarlas sin inferencias.</p>@endif</div>
                            <div class="mt-5 flex flex-wrap gap-2">@foreach($sources as $source)@php $url = data_get($source, 'reference'); @endphp @if(is_string($url) && in_array(parse_url($url, PHP_URL_SCHEME), ['http','https'], true))<a href="{{ $url }}" target="_blank" rel="noopener noreferrer" class="ui-btn-secondary text-sm"><i class="ph ph-arrow-square-out" aria-hidden="true"></i>Ver malla oficial</a>@endif @endforeach</div>
                        </article>
                    @empty
                        <div class="rounded-2xl border border-dashed p-6 text-center xl:col-span-2" style="border-color: var(--ui-border);"><h3 class="font-bold">No hay carreras documentadas para esta selección</h3><p class="ui-subtitle mt-2">Prueba otra universidad o consulta el catálogo completo.</p></div>
                    @endforelse
                </div>
            </section>

            @if($visibleExternal->isNotEmpty())
                <section class="ui-panel"><p class="ui-kicker">Información pendiente de revisión</p><h2 class="ui-title mt-1 text-xl font-black">Páginas oficiales todavía no incorporadas al análisis</h2><div class="mt-4 grid gap-4 lg:grid-cols-2">@foreach($visibleExternal as $career)<article class="rounded-2xl border p-4" style="border-color: var(--ui-border);"><p class="text-sm font-bold">{{ data_get($career, 'university') }}</p><h3 class="mt-1 text-lg font-black">{{ data_get($career, 'career_name') }}</h3>@foreach(data_get($career, 'sources', []) as $source)@php $url=data_get($source,'reference'); @endphp @if(is_string($url) && str_starts_with($url,'http'))<a href="{{ $url }}" target="_blank" rel="noopener noreferrer" class="ui-btn-secondary mt-3 inline-flex text-sm">Ver información oficial</a>@endif @endforeach</article>@endforeach</div></section>
            @endif
        @endif
    @endif
</div>
@endsection
