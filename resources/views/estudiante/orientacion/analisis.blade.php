@extends('aula-virtual.layouts.app')
@section('title', 'Mi análisis | SAVP-TIS3')
@section('page-title', 'Mi análisis')
@section('content')
@php
    $vigente = $experiencia['vigente'];
    $analysis = $vigente && is_array($activity?->analysis_snapshot) && \App\Services\AporteIngenieril\DTO\ContratoAporteIngenierilV2::respuestaValida('analysis_v2', $activity->analysis_snapshot) ? $activity->analysis_snapshot : null;
    $profileReady = (bool) data_get($precheck, 'ready', false);
    $comparacion = \App\Support\Orientacion\ComparacionCarreras::presentar($activity?->riasec_public, $activity?->riasec_score, $analysis, $payload ?? []);
    $carrerasExploracion = collect($comparacion['carreras']);
    $careerOptions = collect(data_get($analysis, 'career_evidence_profiles', []))->filter(fn ($career) => in_array(data_get($career, 'vocational_interest_relation.status'), ['AVAILABLE', 'PARTIAL'], true) && count(data_get($career, 'vocational_interest_relation.evidence', [])) > 0)->unique(fn ($career) => mb_strtolower(trim((string) data_get($career, 'career_name'))))->take(3)->values();
    $enlaceSeguro = static fn ($url) => is_string($url) && filter_var($url, FILTER_VALIDATE_URL) && in_array(strtolower((string) parse_url($url, PHP_URL_SCHEME)), ['http', 'https'], true) && ! parse_url($url, PHP_URL_USER) && ! parse_url($url, PHP_URL_PASS) ? $url : null;
@endphp
<div class="intereses-espacio">
    <section class="intereses-hero" aria-labelledby="analisis-cabecera"><div><div class="intereses-etiqueta"><i class="ph-duotone ph-fingerprint" aria-hidden="true"></i>2 · Comprender</div><h1 id="analisis-cabecera">Entiende tu perfil. Encuentra tu siguiente paso.</h1><p>Lee qué expresan tus respuestas, cómo aporta tu trayectoria y qué referencias puedes investigar para explorar carreras.</p><div class="intereses-acciones"><a class="ui-btn-primary" href="{{ $vigente ? '#mi-analisis' : route('estudiante.intereses') }}">{{ $vigente ? 'Revisar mi estudio' : 'Completar mi cuestionario' }}<i class="ph-duotone ph-arrow-right" aria-hidden="true"></i></a><a class="ui-btn-secondary" href="{{ route('estudiante.futuro') }}">Explorar carreras y universidades</a></div></div><div class="intereses-ilustracion" aria-hidden="true"><div class="intereses-orbita"><i class="ph-duotone ph-fingerprint"></i><span>Tus intereses</span></div><div class="intereses-orbita"><i class="ph-duotone ph-path"></i><span>Tu trayectoria</span></div><div class="intereses-orbita"><i class="ph-duotone ph-lightbulb"></i><span>Tus motivos</span></div></div></section>
    @include('estudiante.orientacion.partes.recorrido', ['etapa' => 'comprender'])
    @if($draft ?? null)<section class="intereses-aviso"><h2 class="font-bold">Tienes un cuestionario en borrador</h2><p>El perfil y el estudio conservan tus últimas respuestas completas. Termina el borrador para actualizar tus intereses.</p><a class="ui-btn-secondary mt-3" href="{{ route('estudiante.intereses') }}#cuestionario">Continuar mi borrador<i class="ph-duotone ph-arrow-right" aria-hidden="true"></i></a></section>@endif
    @if(session('orientation_notice'))<section class="intereses-aviso" role="status"><h2 class="font-bold">{{ data_get(session('orientation_notice'), 'title') }}</h2>@foreach(['message', 'preserved', 'next_step'] as $campo)<p>{{ data_get(session('orientation_notice'), $campo) }}</p>@endforeach</section>@endif
    @if(!$vigente)
        <section class="intereses-panel"><p class="intereses-etiqueta">Tu primer paso</p><h2 class="mt-2">{{ $experiencia['anterior'] ? 'Tu perfil anterior necesita actualizarse' : 'Todavía no tienes un perfil completo' }}</h2><p class="intereses-texto mt-3">{{ $experiencia['anterior'] ? 'El resultado anterior sigue guardado. Necesitamos respuestas del cuestionario actual para explicar tu perfil sin mezclar escalas.' : 'Responde las 30 actividades en la ventana del cuestionario. Aquí podrás comprender el resultado después de guardarlo.' }}</p><a class="ui-btn-primary mt-4" href="{{ route('estudiante.intereses') }}">Ir al cuestionario<i class="ph-duotone ph-arrow-right" aria-hidden="true"></i></a></section>
    @endif
    @include('estudiante.orientacion.partes.analisis-intereses')
    @if($vigente)
        <x-plegable-institucional etiqueta="Cómo se forma tu perfil de intereses" descripcion="Revisa tus seis áreas, el código Holland y el cálculo de tus respuestas." icono="ph-fingerprint">
            @include('estudiante.orientacion.partes.perfil-intereses')
        </x-plegable-institucional>
    @endif
    <x-plegable-institucional etiqueta="Tus notas y tu trayectoria" descripcion="Consulta tus calificaciones por gestión y compara los promedios de tus materias." icono="ph-student">
        @include('estudiante.orientacion.partes.trayectoria-intereses')
    </x-plegable-institucional>
</div>
@endsection
