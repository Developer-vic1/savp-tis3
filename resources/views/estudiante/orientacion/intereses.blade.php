@extends('aula-virtual.layouts.app')
@section('title', 'Mis intereses | SAVP-TIS3')
@section('page-title', 'Cuestionario de intereses')
@section('content')
@php
    $vigente = $experiencia['vigente'];
    $actualizarPorCambio = $actualizarPorCambio ?? false;
    $instrumentItems = collect(data_get($instrument?->data, 'items', []));
    $responseScale = data_get($instrument?->data, 'response_scale', []);
    $mismaVersion = $vigente && data_get($activity?->riasec_public, 'instrument_version') === data_get($instrument?->data, 'instrument_version');
    $draft = $draft ?? null;
    $borradorCompatible = $draft && data_get($draft->riasec_public, 'instrument_version') === data_get($instrument?->data, 'instrument_version');
    $respuestasGuardadas = $borradorCompatible ? data_get($draft->riasec_public, 'responses', []) : ($mismaVersion && !$actualizarPorCambio ? data_get($activity?->riasec_public, 'responses', []) : []);
    $storedResponses = collect($respuestasGuardadas)->mapWithKeys(fn ($r) => [(string) $r['item_id'] => $r['value']])->all();
    $submittedResponses = old('responses', $storedResponses);
    $formResponses = is_array($submittedResponses) ? array_filter($submittedResponses, static fn ($value) => (is_int($value) || is_string($value)) && filter_var($value, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1, 'max_range' => 5]]) !== false) : [];
    $formResponses = array_intersect_key($formResponses, array_flip(range(1, 30)));
    $enlaceSeguro = static fn ($url) => is_string($url) && filter_var($url, FILTER_VALIDATE_URL) && in_array(strtolower((string) parse_url($url, PHP_URL_SCHEME)), ['http', 'https'], true) && ! parse_url($url, PHP_URL_USER) && ! parse_url($url, PHP_URL_PASS) ? $url : null;
@endphp
<div class="intereses-espacio">
    <section class="intereses-hero" aria-labelledby="intereses-titulo">
        <div><div class="intereses-etiqueta"><i class="ph-duotone ph-pencil-line" aria-hidden="true"></i>1 · Responder</div><h1 id="intereses-titulo">Empieza por lo que te gustaría hacer.</h1><p>30 actividades para reconocer tus intereses. Guarda tu borrador y retómalo otro día; al completar todas, podrás comprender tu perfil en Mi análisis.</p><div class="intereses-acciones"><a class="ui-btn-primary" href="#cuestionario">{{ $borradorCompatible ? 'Continuar mi borrador' : ($actualizarPorCambio ? 'Responder nuevamente' : ($vigente ? 'Revisar mi cuestionario' : 'Comenzar mi cuestionario')) }}<i class="ph-duotone ph-arrow-down" aria-hidden="true"></i></a><a class="ui-btn-secondary" href="{{ route('estudiante.analisis') }}">Ir a Mi análisis<i class="ph-duotone ph-arrow-right" aria-hidden="true"></i></a></div></div>
        <div class="intereses-ilustracion" aria-hidden="true"><div class="intereses-orbita"><i class="ph-duotone ph-pencil-line"></i><span>Tus respuestas</span></div><div class="intereses-orbita"><i class="ph-duotone ph-heart"></i><span>Tus intereses</span></div><div class="intereses-orbita"><i class="ph-duotone ph-fingerprint"></i><span>Tu perfil</span></div></div>
    </section>
    @include('estudiante.orientacion.partes.recorrido', ['etapa' => 'responder'])
    @if($actualizarPorCambio)
        <section class="intereses-aviso" role="status"><h2 class="font-bold">Tu nuevo enfoque empieza por tus respuestas actuales</h2><p>Solicitaste cambiar de carrera. Responde nuevamente las 30 actividades pensando en lo que te interesa hoy. Dejamos vacías las respuestas del perfil anterior; ese cuestionario y tu avance siguen guardados.</p><p>Si guardaste un borrador, puedes retomarlo. Después de completar el cuestionario, genera el estudio y confirma el nuevo enfoque en Mi plan.</p></section>
    @endif
    @if($draft && !$borradorCompatible)<section class="intereses-aviso"><h2 class="font-bold">Tu borrador pertenece a otra versión del cuestionario</h2><p>Sigue conservado en tu cuenta. Este formulario usa las actividades y la escala de la versión actual; comienza un borrador para esta versión.</p></section>@endif
    @if(session('orientation_notice'))<section class="intereses-aviso" role="status"><h2 class="font-bold">{{ data_get(session('orientation_notice'), 'title') }}</h2>@foreach(['message', 'preserved', 'next_step'] as $campo)<p>{{ data_get(session('orientation_notice'), $campo) }}</p>@endforeach</section>@endif
    @if($errors->any())<section class="intereses-aviso" role="alert"><h2 class="font-bold">Revisa las actividades pendientes</h2><ul class="mt-2 list-inside list-disc">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></section>@endif
    @if($experiencia['anterior'])<section class="intereses-aviso"><h2 class="font-bold"><i class="ph-duotone ph-clock-counter-clockwise mr-1" aria-hidden="true"></i>Actualicemos tu cuestionario</h2><p>Tu resultado anterior sigue guardado. Usa otra escala; los ceros que aparecían no representaban tus intereses. Completa este cuestionario para obtener un perfil que podamos explicar.</p></section>@endif
    <div class="intereses-panel">@include('estudiante.orientacion.partes.explicacion-cuestionario')</div>
    <div class="intereses-disposicion">
        @include('estudiante.orientacion.partes.cuestionario-intereses')
        <aside class="intereses-aside"><section id="como-funciona" class="intereses-panel"><i class="ph-duotone ph-lightbulb" aria-hidden="true"></i><h3>¿Para qué hacemos esto?</h3><p>Para empezar por tus propias preferencias, antes de investigar carreras y universidades.</p><ol class="mt-3"><li>Lee la actividad e imagina realizarla.</li><li>Elige cuánto te gustaría hacerla.</li><li>Revisa las pendientes y guarda.</li></ol></section><section class="intereses-panel"><i class="ph-duotone ph-heart" aria-hidden="true"></i><h3>Tu respuesta tiene valor</h3><p>Elige lo que te interesa a ti. No respondas para conseguir una carrera concreta ni para agradar a otra persona.</p><p class="mt-3">El avance solo cuenta respuestas. Puedes volver a cualquier bloque antes de guardar.</p></section></aside>
    </div>
</div>
@endsection
