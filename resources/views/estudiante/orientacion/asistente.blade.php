@extends('aula-virtual.layouts.app')

@section('title', 'Asistente de estudio | SAVP-TIS3')
@section('page-title', 'Asistente de estudio')

@section('content')
@php
    $conversationHistory = $conversationHistory ?? [];
    $resultData = isset($queryResult) ? ($queryResult->data ?? []) : [];
    $resultSources = data_get($resultData, 'sources', data_get($resultData, 'results', []));
    $insufficient = (bool) data_get($resultData, 'insufficient_evidence', false);
@endphp

<div class="mx-auto max-w-6xl space-y-6">
    <section class="ui-panel">
        <div class="flex flex-col gap-4 lg:flex-row lg:items-start lg:justify-between">
            <div class="max-w-3xl">
                <p class="ui-kicker">Conversación de apoyo</p>
                <h1 class="ui-title mt-2 text-3xl font-black">Pregunta, practica y comprende</h1>
                <p class="ui-subtitle mt-3 leading-7">Este asistente tiene un propósito distinto al análisis vocacional: ayudarte a estudiar, explicar conceptos y responder preguntas normales con el contexto académico autorizado.</p>
            </div>
            @if(!empty($conversationHistory))
                <form method="POST" action="{{ route('estudiante.asistente.reset') }}">@csrf<button class="ui-btn-secondary" type="submit"><i class="ph ph-plus-circle" aria-hidden="true"></i>Nueva conversación</button></form>
            @endif
        </div>
    </section>

    <section class="grid gap-6 lg:grid-cols-[minmax(0,1fr)_300px]">
        <div class="ui-panel">
            @if(empty($conversationHistory))
                <div class="rounded-2xl border border-dashed p-5" style="border-color: var(--ui-border);">
                    <h2 class="font-black">¡Hola! ¿Qué quieres aprender hoy?</h2>
                    <p class="ui-subtitle mt-2 text-sm leading-6">Puedes pedir una explicación, ejemplos, pasos para resolver un ejercicio o información documentada sobre una carrera.</p>
                    <div class="mt-4 flex flex-wrap gap-2 text-xs"><span class="rounded-full border px-3 py-1.5" style="border-color: var(--ui-border);">Explícame álgebra paso a paso</span><span class="rounded-full border px-3 py-1.5" style="border-color: var(--ui-border);">¿Qué materias lleva Ingeniería Civil?</span><span class="rounded-full border px-3 py-1.5" style="border-color: var(--ui-border);">Ayúdame a organizar mi estudio</span></div>
                </div>
            @else
                <div class="space-y-3" aria-label="Historial reciente del asistente">
                    @foreach($conversationHistory as $turn)
                        <article @class(['max-w-[92%] rounded-2xl border px-4 py-3 text-sm leading-6', 'ml-auto' => data_get($turn,'role') === 'user', 'mr-auto' => data_get($turn,'role') !== 'user']) style="border-color: var(--ui-border); background: {{ data_get($turn,'role') === 'user' ? 'var(--ui-soft)' : 'var(--ui-surface)' }};">
                            <p class="mb-1 text-xs font-bold uppercase tracking-wide" style="color: var(--ui-muted);">{{ data_get($turn,'role') === 'user' ? 'Tú' : 'Asistente SAVP' }}</p>
                            <p class="whitespace-pre-wrap">{{ data_get($turn,'content') }}</p>
                        </article>
                    @endforeach
                </div>
            @endif

            <form method="POST" action="{{ route('estudiante.asistente.query') }}" class="mt-6 space-y-4" x-data="{ submitting: false }" @submit="submitting = true">
                @csrf
                <label for="assistant-question" class="block font-bold">¿Qué quieres consultar?</label>
                <textarea id="assistant-question" name="question" class="ui-input min-h-[140px] w-full resize-y" minlength="2" maxlength="1000" required placeholder="Escribe una pregunta con tus propias palabras…">{{ $question ?? old('question') }}</textarea>
                <div class="flex flex-wrap items-center justify-between gap-3"><p class="ui-subtitle text-xs">No incluyas contraseñas ni datos personales sensibles.</p><button class="ui-btn-primary" type="submit" :disabled="submitting"><i class="ph ph-paper-plane-tilt" aria-hidden="true"></i><span x-show="!submitting">Enviar pregunta</span><span x-show="submitting" x-cloak>Consultando…</span></button></div>
            </form>
        </div>

        <aside class="space-y-4">
            <section class="ui-panel"><i class="ph ph-chats-circle text-2xl" aria-hidden="true" style="color: var(--ui-primary);"></i><h2 class="mt-3 font-black">Para qué sirve</h2><ul class="ui-subtitle mt-3 list-disc space-y-2 pl-5 text-sm"><li>Explicar temas académicos.</li><li>Dar ejemplos y pasos de estudio.</li><li>Consultar carreras con fuentes.</li><li>Reconocer cuando falta evidencia.</li></ul></section>
            <section class="ui-panel"><i class="ph ph-shield-check text-2xl" aria-hidden="true" style="color: var(--ui-primary);"></i><h2 class="mt-3 font-black">Respuesta responsable</h2><p class="ui-subtitle mt-2 text-sm leading-6">No inventa datos institucionales. Cuando una respuesta depende de información universitaria, muestra las fuentes recuperadas o indica que no existe evidencia suficiente.</p></section>
        </aside>
    </section>

    @isset($queryResult)
        @if(!$queryResult->available)
            <section class="rounded-2xl border border-amber-200 bg-amber-50 p-5 text-amber-800 dark:border-amber-800/60 dark:bg-amber-950/30 dark:text-amber-200"><h2 class="font-bold">No fue posible completar la consulta</h2><p class="mt-2 text-sm">{{ $queryResult->message }}</p></section>
        @elseif($insufficient)
            <section class="ui-panel"><h2 class="font-black">Necesito más información para responder con seguridad</h2><p class="ui-subtitle mt-2">Prueba indicando la asignatura, carrera o concepto exacto que deseas comprender.</p></section>
        @endif

        @if(!empty($resultSources))
            <section class="ui-panel"><p class="ui-kicker">Fuentes relacionadas</p><h2 class="ui-title mt-1 text-xl font-black">Información utilizada en la respuesta</h2><div class="mt-4 grid gap-4 md:grid-cols-2">@foreach($resultSources as $source)@php $url=data_get($source,'reference',data_get($source,'url')); @endphp<article class="rounded-2xl border p-4" style="border-color: var(--ui-border);"><h3 class="font-bold">{{ data_get($source,'title','Fuente documentada') }}</h3>@if(is_string($url) && str_starts_with($url,'http'))<a href="{{ $url }}" target="_blank" rel="noopener noreferrer" class="ui-btn-secondary mt-3 inline-flex text-sm">Abrir fuente</a>@endif</article>@endforeach</div></section>
        @endif
    @endisset
</div>
@endsection
