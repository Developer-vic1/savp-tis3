@extends('aula-virtual.layouts.app')

@section('title', 'Mi futuro académico | SAVP-TIS3')
@section('page-title', 'Mi futuro académico')

@section('content')
@php
    $safeSourceUrl = static fn ($url): bool => is_string($url)
        && filter_var($url, FILTER_VALIDATE_URL) !== false
        && in_array(parse_url($url, PHP_URL_SCHEME), ['http', 'https'], true)
        && parse_url($url, PHP_URL_HOST)
        && parse_url($url, PHP_URL_USER) === null
        && parse_url($url, PHP_URL_PASS) === null;
    $institutionKey = static fn ($name): string => \Illuminate\Support\Str::lower(\Illuminate\Support\Str::ascii(trim((string) $name)));
    $analysis = $activity?->analysis_snapshot;
    $analysisCurrent = \App\Support\Orientacion\ExperienciaIntereses::perfilVigente($activity?->riasec_public, $activity?->riasec_score)
        && is_array($analysis)
        && \App\Services\AporteIngenieril\DTO\ContratoAporteIngenierilV2::respuestaValida('analysis_v2', $analysis);
    $comparacion = \App\Support\Orientacion\ComparacionCarreras::presentar($activity?->riasec_public, $activity?->riasec_score, is_array($analysis) ? $analysis : null);
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
    $universityOptions = $universities->map(fn ($university) => ['valor' => $university, 'etiqueta' => $university])->prepend(['valor' => '', 'etiqueta' => 'Selecciona una universidad'])->all();
    $careerOptionsForFilter = $careers->map(fn ($career) => ['valor' => $career['key'], 'etiqueta' => $career['name']])->prepend(['valor' => '', 'etiqueta' => 'Todas las carreras'])->all();
    $requestedCareer = trim((string) request('career', ''));
    $selectedCareer = $careers->contains(fn ($career) => $career['key'] === $requestedCareer) ? $requestedCareer : '';
    $careerUniversities = $selectedCareer === '' ? collect() : $catalog->where('key', $selectedCareer)->pluck('university')->unique()->sort()->values();
    $careerUniversityOptions = $careerUniversities->map(fn ($university) => ['valor' => $university, 'etiqueta' => $university])->prepend(['valor' => '', 'etiqueta' => $selectedCareer === '' ? 'Primero elige una carrera' : 'Todas las universidades de esta carrera'])->all();
    $relatedCareerKeys = ['ingenieria-de-sistemas' => ['ingenieria-de-sistemas-e-innovacion-digital', 'ingenieria-de-sistemas-computacionales']];
    $relatedCareers = $careers->filter(fn ($career) => in_array($career['key'], $relatedCareerKeys[$selectedCareer] ?? [], true));
    $requestedUniversity = trim((string) request('university', ''));
    $allowedUniversities = $mode === 'universities' ? $universities : $careerUniversities;
    $selectedUniversity = $allowedUniversities->contains($requestedUniversity) ? $requestedUniversity : '';
    $isRelated = static fn ($career): bool => collect(data_get($career, 'vocational_interest_relation.evidence', []))->isNotEmpty()
        || (int) data_get($career, 'preparation.relations_with_observed_academic_evidence', 0) > 0;
    $interestCareerNames = $careerProfiles->filter($isRelated)
        ->unique(fn ($career) => \Illuminate\Support\Str::slug((string) data_get($career, 'career_name')))
        ->pluck('career_name')->all();
    $visibleProfiles = match ($mode) {
        'analysis' => $analysisCurrent ? $careerProfiles->filter(fn ($career) => in_array(data_get($career, 'career_name'), $interestCareerNames, true))->values() : $careerProfiles,
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
        'analysis' => ['label' => $analysisCurrent ? 'Desde mi análisis' : 'Explorar opciones', 'icon' => 'ph-sparkle', 'text' => $analysisCurrent ? 'Primero mira las carreras; después compara universidades, mallas y costos por verificar.' : 'Consulta información documentada mientras completas un nuevo estudio.'],
        'universities' => ['label' => 'Elegir universidad', 'icon' => 'ph-buildings', 'text' => 'Selecciona una institución y consulta las carreras documentadas que ofrece.'],
        'all' => ['label' => 'Todas las carreras', 'icon' => 'ph-books', 'text' => 'Revisa el catálogo completo y filtra por carrera cuando lo necesites.'],
        'compare' => ['label' => 'Comparar carreras', 'icon' => 'ph-scales', 'text' => 'Contrasta dos o tres programas, sus intereses compartidos y sus materias.'],
    ];
@endphp

<div class="futuro-estudiante mx-auto max-w-7xl space-y-6">
    <section class="ui-panel futuro-cabecera">
        <p class="ui-kicker">Exploración académica</p>
        <h1 class="ui-title mt-2 text-3xl font-black">Carreras, universidades y planes de estudio</h1>
        <p class="ui-subtitle mt-3 max-w-3xl leading-7">Imagina qué te gustaría aprender. Abre una carrera, descubre sus materias y revisa dónde se estudia antes de dar tu siguiente paso.</p>
        <a href="{{ route('estudiante.directorio-nacional') }}" class="ui-btn-secondary mt-4 inline-flex"><i class="ph-duotone ph-map-trifold" aria-hidden="true"></i> Explorar el directorio nacional</a>
        <ol class="futuro-recorrido mt-5" aria-label="Tu recorrido de orientación">
            <li><a href="{{ route('estudiante.intereses') }}"><i class="ph-duotone ph-pencil-line" aria-hidden="true"></i><span><small>01 · Responder</small>Mis intereses</span></a></li>
            <li><a href="{{ route('estudiante.analisis') }}"><i class="ph-duotone ph-fingerprint" aria-hidden="true"></i><span><small>02 · Comprender</small>Mi análisis</span></a></li>
            <li aria-current="step"><span><i class="ph-duotone ph-compass" aria-hidden="true"></i><span><small>03 · Explorar</small>Mi futuro académico</span></span></li>
            <li><a href="{{ route('estudiante.plan') }}"><i class="ph-duotone ph-target" aria-hidden="true"></i><span><small>04 · Prepararme</small>Mi plan</span></a></li>
        </ol>

        <nav class="mt-6 grid gap-3 md:grid-cols-2 xl:grid-cols-4" aria-label="Formas de explorar el futuro académico">
            @foreach ($modes as $modeKey => $modeMeta)
                @php $active = $mode === $modeKey; @endphp
                <a href="{{ route('estudiante.futuro', ['explore' => $modeKey]) }}" @if($active) aria-current="page" @endif class="futuro-modo {{ $active ? 'is-active' : '' }}">
                    <div class="flex items-start gap-3"><span class="futuro-icono"><i class="ph-duotone {{ $modeMeta['icon'] }}" aria-hidden="true"></i></span><span><strong class="block">{{ $modeMeta['label'] }}</strong><span class="ui-subtitle mt-1 block text-sm leading-5">{{ $modeMeta['text'] }}</span></span></div>
                </a>
            @endforeach
        </nav>
    </section>

    @if (!$analysis)
        <section class="ui-panel futuro-vacio text-center"><span class="futuro-icono mx-auto"><i class="ph-duotone ph-compass text-3xl" aria-hidden="true"></i></span><h2 class="ui-title mt-3 text-xl font-black">Primero genera tu estudio de orientación</h2><p class="ui-subtitle mt-2">En Mi análisis puedes revisar tu perfil y la información necesaria para generar el estudio. Cuando esté listo, aquí podrás explorar carreras y materias documentadas.</p><a href="{{ route('estudiante.analisis') }}" class="ui-btn-primary mt-5 inline-flex">Ir a Mi análisis<i class="ph-duotone ph-arrow-right" aria-hidden="true"></i></a></section>
    @else
        @if($mode === 'compare')
            @include('estudiante.orientacion.partes.comparacion-carreras', ['destinoComparacion' => route('estudiante.futuro')])
        @endif
        @if($mode !== 'compare')
        @if(!$analysisCurrent)
            <section class="ui-panel"><div class="flex items-start gap-3"><span class="futuro-icono"><i class="ph-duotone ph-clock-counter-clockwise" aria-hidden="true"></i></span><div><h2 class="font-black">Hace falta actualizar tu estudio</h2><p class="ui-subtitle mt-1 text-sm leading-6">Necesitamos un cuestionario completo y un estudio actualizado para mostrar su evidencia. Puedes leer las carreras y fuentes documentadas sin presentar una interpretación personal del estudio anterior.</p><a href="{{ route('estudiante.intereses') }}" class="ui-btn-secondary mt-3 inline-flex">Completar Mis intereses</a></div></div></section>
        @endif
        @if ($mode === 'universities')
            <section class="ui-panel">
                <form method="GET" action="{{ route('estudiante.futuro') }}" class="grid gap-4 md:grid-cols-[minmax(0,1fr)_auto] md:items-end" x-data="{ universidad: @js($selectedUniversity) }">
                    <input type="hidden" name="explore" value="universities">
                    <div><x-selector-institucional identificador="futuro-universidad" etiqueta="Universidad que quieres explorar" nombre="university" enlace="universidad" :opciones="$universityOptions" :valorInicial="$selectedUniversity" descripcionId="futuro-universidad-ayuda" /><span id="futuro-universidad-ayuda" class="ui-subtitle mt-2 block text-xs">Elige una universidad para habilitar la consulta. El sistema no elige la institución por ti.</span></div>
                    <button class="ui-btn-primary min-h-11" type="submit" x-bind:disabled="universidad === ''" @disabled($selectedUniversity === '')><i class="ph-duotone ph-buildings" aria-hidden="true"></i>Ver carreras</button>
                </form>
            </section>
        @elseif ($mode === 'all')
            <section class="ui-panel">
                <form method="GET" action="{{ route('estudiante.futuro') }}" class="grid gap-4 lg:grid-cols-2" x-data="{ carrera: @js($selectedCareer), universidad: @js($selectedUniversity), catalogo: @js($catalog) }">
                    <input type="hidden" name="explore" value="all">
                    <x-selector-institucional identificador="futuro-carrera" etiqueta="Carrera" nombre="career" enlace="carrera" :opciones="$careerOptionsForFilter" :valorInicial="$selectedCareer" x-on:cambio-selector="universidad = ''; $dispatch('futuro-carrera-cambiada', { carrera: valor })" />
                    <x-selector-institucional identificador="futuro-carrera-universidad" etiqueta="Universidad" nombre="university" enlace="universidad" :opciones="$careerUniversityOptions" :valorInicial="$selectedUniversity" descripcionId="futuro-carrera-universidad-ayuda" x-effect="$refs.control.disabled = carrera === ''" x-on:futuro-carrera-cambiada.window="opciones = [{ valor: '', etiqueta: $event.detail.carrera === '' ? 'Primero elige una carrera' : 'Todas las universidades de esta carrera' }, ...Array.from(new Set(catalogo.filter(item => item.key === $event.detail.carrera).map(item => item.university))).sort().map(nombre => ({ valor: nombre, etiqueta: nombre }))]; valor = ''" />
                    <div class="flex flex-wrap items-center justify-between gap-3 lg:col-span-2">
                        <span id="futuro-carrera-universidad-ayuda" class="ui-subtitle text-xs">Elige una carrera para ver sus universidades; puedes dejar la universidad abierta para comparar todas sus fichas.</span>
                        <button class="ui-btn-primary min-h-11" type="submit"><i class="ph-duotone ph-arrow-right" aria-hidden="true"></i>{{ $selectedCareer !== '' ? 'Ver universidades' : 'Ver carreras' }}</button>
                    </div>
                </form>
            </section>
        @elseif($analysisCurrent)
            <section class="ui-panel"><div class="flex items-start gap-3"><span class="futuro-icono"><i class="ph-duotone ph-lightbulb text-xl" aria-hidden="true"></i></span><div><h2 class="font-black">Primero la carrera que te interesa</h2><p class="ui-subtitle mt-1 text-sm leading-6">Revisa las referencias disponibles para cada carrera. Después podrás ver qué universidades la ofrecen, sus mallas y qué información económica falta confirmar. La cobertura de fuentes no ordena tus preferencias.</p></div></div></section>
        @endif

        @if ($mode === 'all' && $selectedCareer !== '')
            <section class="ui-panel" aria-labelledby="futuro-instituciones-carrera"><p class="ui-kicker">Ahora revisa dónde estudiarla</p><h2 id="futuro-instituciones-carrera" class="ui-title mt-1 text-xl font-black">Universidades registradas para {{ data_get($careers->firstWhere('key', $selectedCareer), 'name') }}</h2><p class="ui-subtitle mt-2 text-sm">Compara la malla de cada sede y consulta directamente los aranceles, becas y requisitos vigentes. El estudio no contiene cifras económicas verificadas.</p>
                @if($relatedCareers->isNotEmpty())<div class="mt-4 border-t pt-4" style="border-color: var(--ui-border);"><h3 class="font-bold">Programas con un nombre relacionado</h3><p class="ui-subtitle mt-1 text-sm">Tienen enfoques y planes propios; revisa cada uno antes de tratarlos como la misma carrera.</p><div class="mt-3 flex flex-wrap gap-2">@foreach($relatedCareers as $relatedCareer)<a class="ui-btn-secondary" href="{{ route('estudiante.futuro', ['explore' => 'all', 'career' => $relatedCareer['key']]) }}">{{ $relatedCareer['name'] }}<i class="ph-duotone ph-arrow-right" aria-hidden="true"></i></a>@endforeach</div></div>@endif
            </section>
        @endif

        @if ($mode === 'analysis' && $analysisCurrent)
            <section class="ui-panel" aria-labelledby="futuro-carreras-primero"><p class="ui-kicker">Explora sin elegir institución todavía</p><h2 id="futuro-carreras-primero" class="ui-title mt-1 text-2xl font-black">Carreras de tu estudio y del catálogo</h2><p class="ui-subtitle mt-2 text-sm">La coincidencia describe una referencia ocupacional documentada. No se transfiere a todas las universidades ni calcula ingreso, éxito o precio.</p>
                <div class="mt-5 grid gap-4 md:grid-cols-2 xl:grid-cols-3">
                    @foreach($comparacion['carreras'] as $carreraResumen)
                        @php
                            $referenciaResumen = $carreraResumen['referencia_principal'];
                        @endphp
                        <article class="futuro-carrera flex flex-col p-5"><p class="ui-kicker">Carrera para explorar</p><h3 class="ui-title mt-2 text-xl font-black">{{ $carreraResumen['nombre'] }}</h3>
                            @if(data_get($referenciaResumen, 'cantidad') !== null)<p class="ui-subtitle mt-3 text-sm">{{ $referenciaResumen['cantidad'] }} de {{ $referenciaResumen['denominador_estudiante'] }} áreas destacadas aparecen en una referencia ocupacional del estudio.</p><p class="ui-muted mt-1 text-xs">Referencia vinculada a {{ implode(' y ', $referenciaResumen['universidades_origen']) }}.</p>@else<p class="ui-subtitle mt-3 text-sm">Relación de intereses pendiente de documentar. Puedes revisar sus materias.</p>@endif
                            @php
                                $fichasDocumentadas = collect($carreraResumen['ofertas'])->where('estado', 'DOCUMENTADA')->count();
                                $fuentesPendientes = collect($carreraResumen['ofertas'])->where('estado', 'PENDIENTE')->count();
                            @endphp
                            <p class="ui-subtitle mt-3 text-sm">{{ $fichasDocumentadas }} {{ $fichasDocumentadas === 1 ? 'programa con ficha' : 'programas con ficha' }} en el estudio{{ $fuentesPendientes > 0 ? ', y '.$fuentesPendientes.' '.($fuentesPendientes === 1 ? 'fuente pendiente' : 'fuentes pendientes').' de revisión' : '' }}.</p>
                            <a class="ui-btn-primary mt-5" href="{{ route('estudiante.futuro', ['explore' => 'all', 'career' => $carreraResumen['clave']]) }}">Ver universidades y mallas<i class="ph-duotone ph-arrow-right" aria-hidden="true"></i></a>
                        </article>
                    @endforeach
                </div>
            </section>
        @elseif ($mode === 'universities' && $selectedUniversity === '')
            <section class="ui-panel text-center"><i class="ph-duotone ph-buildings text-3xl" aria-hidden="true"></i><h2 class="ui-title mt-3 text-xl font-black">Elige una universidad para ver sus carreras</h2><p class="ui-subtitle mt-2">Luego podrás revisar materias, duración y malla oficial.</p></section>
        @else
            <section class="ui-panel">
                <div class="flex flex-wrap items-end justify-between gap-3"><div><p class="ui-kicker">Opciones disponibles</p><h2 class="ui-title mt-1 text-2xl font-black">{{ $mode === 'analysis' && $analysisCurrent ? 'Opciones con referencias en tu estudio' : 'Perfiles académicos documentados' }}</h2></div><span class="ui-subtitle text-sm">{{ $visibleProfiles->count() }} {{ $visibleProfiles->count() === 1 ? 'ficha documentada' : 'fichas documentadas' }}</span></div>
                <p class="ui-subtitle mt-2 text-sm">Cada ficha conecta una carrera con una universidad. Puedes abrir varias para comparar qué aprenderías.</p>
                @if($activity?->analysis_completed_at)<p class="ui-muted mt-2 text-xs">{{ $analysisCurrent ? 'Basado en tu estudio guardado' : 'Información conservada del estudio anterior' }} el {{ $activity->analysis_completed_at->copy()->timezone('America/La_Paz')->format('d/m/Y H:i') }} (Bolivia). Confirma la oferta y la versión vigente en la universidad.</p>@endif
                <div class="mt-6 grid items-start gap-5 {{ $visibleProfiles->count() > 1 ? 'xl:grid-cols-2' : '' }}">
                    @forelse ($visibleProfiles as $career)
                        @php
                            $program = data_get($career, 'academic_program', []);
                            $subjects = collect(data_get($program, 'documented_subjects', []))->filter(fn ($subject) => is_string($subject) && trim($subject) !== '')->unique()->values();
                            $areas = collect(data_get($program, 'knowledge_areas', []))->filter(fn ($area) => is_string($area) && trim($area) !== '')->unique();
                            $programSources = collect(data_get($program, 'sources', []))->filter(fn ($source) => $safeSourceUrl(data_get($source, 'reference')))->unique('reference');
                            $sources = $programSources->concat(collect(data_get($career, 'sources', [])))->filter(fn ($source) => $safeSourceUrl(data_get($source, 'reference')))->unique('reference');
                            $sameInstitution = static fn ($source): bool => $institutionKey(data_get($source, 'institution')) !== '' && $institutionKey(data_get($source, 'institution')) === $institutionKey(data_get($career, 'university'));
                            // El currículo escolar y las referencias ocupacionales no son mallas universitarias.
                            $curriculumSources = $programSources->filter(fn ($source) => data_get($source, 'source_type') === 'OFFICIAL_CURRICULUM_PDF' && $sameInstitution($source));
                            $careerPageSources = $sources->filter(fn ($source) => data_get($source, 'source_type') === 'OFFICIAL_CAREER_HTML' && $sameInstitution($source));
                            $academicSources = $curriculumSources->concat($careerPageSources)->unique('reference');
                            $otherSources = $sources->reject(fn ($source) => $academicSources->contains('reference', data_get($source, 'reference')));
                            $interestEvidence = $analysisCurrent ? collect(data_get($career, 'vocational_interest_relation.evidence', []))->filter(fn ($item) => is_string(data_get($item, 'statement')) && trim(data_get($item, 'statement')) !== '') : collect();
                            $academicEvidence = $analysisCurrent ? collect(data_get($career, 'preparation.academic_evidence', []))->filter(fn ($item) => data_get($item, 'record_subject') && data_get($item, 'initial_subject'))->unique(fn ($item) => data_get($item, 'record_subject').'|'.data_get($item, 'initial_subject')) : collect();
                            $hasAcademicEvidence = $analysisCurrent && (int) data_get($career, 'preparation.relations_with_observed_academic_evidence', 0) > 0;
                            $fuentesEconomicas = \App\Support\Orientacion\FuentesEconomicasUniversitarias::paraPrograma(data_get($career, 'career_id'));
                        @endphp
                        <article class="futuro-carrera p-5">
                            <p class="ui-kicker">{{ data_get($career, 'university', 'Universidad') }}</p>
                            <h3 class="ui-title mt-1 text-xl font-black">{{ data_get($career, 'career_name', 'Carrera') }}</h3>
                            @if(data_get($program, 'professional_profile'))<p class="ui-subtitle mt-3 leading-6">{{ data_get($program, 'professional_profile') }}</p>@endif
                            <div class="mt-3 flex flex-wrap gap-2" aria-label="Evidencia para explorar esta carrera">
                                @if($interestEvidence->isNotEmpty())<span class="ui-badge ui-badge-info"><i class="ph-duotone ph-fingerprint" aria-hidden="true"></i>Referencia de intereses</span>@endif
                                @if($hasAcademicEvidence)<span class="ui-badge ui-badge-success"><i class="ph-duotone ph-books" aria-hidden="true"></i>Evidencia de tus materias</span>@endif
                                @if(!$hasAcademicEvidence && $interestEvidence->isEmpty())<span class="ui-badge">Exploración del catálogo</span>@endif
                            </div>
                            <dl class="mt-4 grid grid-cols-2 gap-3 text-sm"><div class="futuro-dato"><dt class="font-bold">Grado</dt><dd class="ui-subtitle mt-1">{{ data_get($program, 'degree') ?: 'Por verificar' }}</dd></div><div class="futuro-dato"><dt class="font-bold">Duración</dt><dd class="ui-subtitle mt-1">{{ data_get($program, 'duration') ?: 'Por verificar' }}</dd></div></dl>
                            <p class="ui-subtitle mt-4 text-sm"><strong>Costos y becas:</strong> el estudio aún no tiene aranceles ni condiciones comparables. Revisa en las fuentes oficiales el monto, la cobertura, los requisitos y la vigencia para esta sede antes de decidir.</p>
                            @if($fuentesEconomicas)<div class="mt-3 flex flex-wrap gap-2">@foreach($fuentesEconomicas as $fuenteEconomica)<a class="ui-btn-secondary text-sm" href="{{ $fuenteEconomica['url'] }}" target="_blank" rel="noopener noreferrer">{{ $fuenteEconomica['titulo'] }}<span class="sr-only"> (nueva pestaña)</span><i class="ph-duotone ph-arrow-square-out" aria-hidden="true"></i></a>@endforeach</div>@endif
                            <details class="futuro-detalle mt-4">
                                <summary><span class="futuro-abrir">Explorar esta opción</span><span class="futuro-cerrar">Cerrar detalle</span><span class="futuro-cantidad">{{ $subjects->isEmpty() ? 'Malla por revisar' : $subjects->count().' '.($subjects->count() === 1 ? 'materia documentada' : 'materias documentadas') }}</span><i class="ph-duotone ph-caret-down" aria-hidden="true"></i></summary>
                                <div class="futuro-contenido space-y-5 pt-5">
                                    <section>
                                        <h4 class="flex items-center gap-2 font-bold"><i class="ph-duotone ph-lightbulb" aria-hidden="true"></i>¿Por qué aparece esta carrera?</h4>
                                        @if($interestEvidence->isNotEmpty())<ul class="ui-subtitle mt-2 space-y-2 text-sm leading-6">@foreach($interestEvidence as $item)<li>{{ data_get($item, 'statement') }} @if(data_get($item, 'is_inference'))<span class="font-bold">Es una relación propuesta que requiere revisión.</span>@endif</li>@endforeach</ul>@endif
                                        @if($hasAcademicEvidence)<p class="ui-subtitle mt-2 text-sm leading-6">Tu estudio contiene {{ (int) data_get($career, 'preparation.relations_with_observed_academic_evidence') }} relación(es) entre evidencia de tus materias y conocimientos de esta carrera. Eso describe tu preparación observada; no demuestra que esta carrera sea la que más te interesa.</p>@endif
                                        @if(!$hasAcademicEvidence && $interestEvidence->isEmpty())<p class="ui-subtitle mt-2 text-sm leading-6">La estás viendo como parte de la información académica documentada. Tu estudio no aporta una relación personal vigente suficiente para esta opción.</p>@endif
                                    </section>
                                    @if($academicEvidence->isNotEmpty())
                                        <section><h4 class="font-bold">Un puente desde lo que ya estudias</h4><p class="ui-subtitle mt-1 text-sm">Estas relaciones provienen de tu estudio guardado. No equivalen a convalidación de materias.</p><ul class="mt-3 space-y-2">@foreach($academicEvidence as $item)<li class="futuro-puente"><span>{{ data_get($item, 'record_subject') }}@if(data_get($item, 'record_period'))<small>{{ data_get($item, 'record_period') }}</small>@endif</span><i class="ph-duotone ph-arrow-right" aria-hidden="true"></i><span>{{ data_get($item, 'initial_subject') }}<small>{{ match(data_get($item, 'relation_status')) { 'DIRECTLY_DOCUMENTED' => 'Relación documentada', 'DOCUMENT_SUPPORTED_INFERENCE' => 'Relación interpretada con respaldo documental', 'HYPOTHESIS' => 'Relación propuesta por revisar', default => 'Respaldo insuficiente para confirmar la relación' } }}</small></span></li>@endforeach</ul></section>
                                    @endif
                                    @if($areas->isNotEmpty())<section><h4 class="font-bold">Qué áreas explorarías</h4><div class="mt-2 flex flex-wrap gap-2">@foreach($areas as $area)<span class="futuro-area">{{ \Illuminate\Support\Str::ucfirst($area) }}</span>@endforeach</div></section>@endif
                                    <section><h4 class="font-bold">Materias que podrías empezar a aprender</h4>
                                        @if($subjects->isNotEmpty())<p class="ui-subtitle mt-1 text-sm">Materias iniciales documentadas; consulta la malla oficial para conocer semestres, requisitos y el plan completo.</p><ul class="futuro-materias mt-3">@foreach($subjects as $subject)<li><i class="ph-duotone ph-book-open" aria-hidden="true"></i><span>{{ $subject }}</span></li>@endforeach</ul>
                                        @elseif($curriculumSources->isNotEmpty())<p class="ui-subtitle mt-2 text-sm">Hay una fuente curricular enlazada, pero todavía no tenemos sus materias estructuradas. Abre la malla original para revisarlas.</p>
                                        @else<p class="ui-subtitle mt-2 text-sm">Falta una malla curricular verificable para mostrar las materias de esta opción. Consulta el programa en la universidad.</p>@endif
                                    </section>
                                    <section class="futuro-fuentes"><h4 class="font-bold">Comprueba el plan en su fuente</h4><p class="ui-subtitle mt-1 text-sm">La oferta puede cambiar según sede y gestión. El enlace abre la fuente registrada en otra pestaña.</p><div class="mt-3 flex flex-wrap gap-2">@forelse($academicSources as $source)<a href="{{ data_get($source, 'reference') }}" target="_blank" rel="noopener noreferrer" class="ui-btn-secondary text-sm"><i class="ph-duotone ph-arrow-square-out" aria-hidden="true"></i>{{ data_get($source, 'source_type') === 'OFFICIAL_CURRICULUM_PDF' ? 'Ver malla oficial' : 'Ver fuente del programa' }}<span class="sr-only"> de {{ data_get($career, 'career_name') }}, {{ data_get($career, 'university') }} (nueva pestaña)</span></a>@empty<p class="ui-subtitle text-sm">No hay un enlace curricular o del programa verificado para esta institución en el estudio.</p>@endforelse</div></section>
                                    @if($otherSources->isNotEmpty())<details class="futuro-fuentes"><summary class="cursor-pointer py-2 font-bold">Otras referencias del estudio</summary><p class="ui-subtitle mt-2 text-sm">Son documentos usados para el contexto escolar o las referencias ocupacionales. No representan el plan de esta carrera.</p><ul class="mt-3 space-y-3">@foreach($otherSources as $source)<li><a href="{{ data_get($source, 'reference') }}" target="_blank" rel="noopener noreferrer" class="ui-btn-secondary text-sm"><i class="ph-duotone ph-arrow-square-out" aria-hidden="true"></i>{{ data_get($source, 'title') ?: 'Referencia documentada sin título' }}<span class="sr-only"> (nueva pestaña)</span></a>@if(data_get($source, 'institution'))<p class="ui-subtitle mt-1 text-xs">{{ data_get($source, 'institution') }}</p>@endif</li>@endforeach</ul></details>@endif
                                </div>
                            </details>
                        </article>
                    @empty
                        <div class="rounded-2xl border border-dashed p-6 text-center xl:col-span-2" style="border-color: var(--ui-border);"><h3 class="font-bold">No hay carreras documentadas para esta selección</h3><p class="ui-subtitle mt-2">Prueba otra universidad o consulta el catálogo completo.</p></div>
                    @endforelse
                </div>
            </section>

            @if($visibleExternal->isNotEmpty())
                <section class="ui-panel">
                    <p class="ui-kicker">Información pendiente de revisión</p>
                    <h2 class="ui-title mt-1 text-xl font-black">Páginas registradas todavía no incorporadas al análisis</h2>
                    <p class="ui-subtitle mt-2 text-sm">Estas fuentes amplían tu exploración. No tienen una valoración de relación con tu perfil.</p>
                    <div class="mt-4 grid gap-4 lg:grid-cols-2">
                        @foreach($visibleExternal as $career)
                            @php
                                $fuentesEconomicasExternas = \App\Support\Orientacion\FuentesEconomicasUniversitarias::paraPrograma(data_get($career, 'career_id'));
                            @endphp
                            <article class="futuro-carrera p-4">
                                <p class="text-sm font-bold">{{ data_get($career, 'university') }}</p>
                                <h3 class="mt-1 text-lg font-black">{{ data_get($career, 'career_name') }}</h3>
                                @foreach(data_get($career, 'sources', []) as $source)
                                    @php $url = \App\Support\Orientacion\EnlaceUniversitario::vigente(data_get($source, 'reference')); @endphp
                                    @if($safeSourceUrl($url))
                                        <a href="{{ $url }}" target="_blank" rel="noopener noreferrer" class="ui-btn-secondary mt-3 inline-flex text-sm">{{ data_get($source, 'title') ?: 'Consultar referencia registrada' }}<span class="sr-only"> (nueva pestaña)</span></a>
                                    @endif
                                @endforeach
                                @foreach($fuentesEconomicasExternas as $fuenteEconomica)
                                    <a href="{{ $fuenteEconomica['url'] }}" target="_blank" rel="noopener noreferrer" class="ui-btn-secondary mt-3 inline-flex text-sm">{{ $fuenteEconomica['titulo'] }}<span class="sr-only"> (nueva pestaña)</span></a>
                                @endforeach
                                <p class="ui-muted mt-3 text-xs">Confirma oferta vigente, aranceles y ayudas económicas directamente con la institución.</p>
                            </article>
                        @endforeach
                    </div>
                </section>
            @endif
        @endif
        @endif
        <section class="ui-panel futuro-siguiente"><div><p class="ui-kicker">Un paso que depende de ti</p><h2 class="ui-title mt-1 text-xl font-black">¿Qué te gustaría investigar primero?</h2><p class="ui-subtitle mt-2">Después de revisar una malla, elige una materia que te dé curiosidad y una duda para consultar a la universidad. Puedes convertirlas en una meta de tu plan.</p></div><a class="ui-btn-primary shrink-0" href="{{ route('estudiante.plan') }}"><i class="ph-duotone ph-target" aria-hidden="true"></i>Organizar mi siguiente paso</a></section>
    @endif
</div>
@endsection
