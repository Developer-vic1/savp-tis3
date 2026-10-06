@extends('aula-virtual.layouts.app')

@section('title', 'Mi plan | SAVP-TIS3')
@section('page-title', 'Mi plan')

@section('content')
<div class="mx-auto max-w-6xl space-y-6">
    <section class="ui-panel">
        <p class="ui-kicker">Planificación personal</p>
        <h1 class="ui-title mt-2 text-3xl font-black">Define metas claras y acciones alcanzables</h1>
        <p class="ui-subtitle mt-3 max-w-3xl leading-7">
            Este espacio sirve únicamente para organizar tus objetivos personales.
            Tus intereses, carreras y actividades académicas se consultan en sus ventanas correspondientes.
        </p>
    </section>

    <section class="grid gap-4 md:grid-cols-3" aria-label="Pasos para construir un plan académico">
        @foreach ([
            ['number' => '1', 'title' => 'Define una meta', 'text' => 'Escribe un resultado concreto que quieras alcanzar.'],
            ['number' => '2', 'title' => 'Elige una acción', 'text' => 'Indica el primer paso que depende de ti.'],
            ['number' => '3', 'title' => 'Revisa tu avance', 'text' => 'Actualiza el estado y explica cada cambio importante.'],
        ] as $step)
            <article class="ui-panel">
                <span class="flex h-10 w-10 items-center justify-center rounded-xl font-black text-white" style="background: var(--ui-primary);">{{ $step['number'] }}</span>
                <h2 class="ui-title mt-4 text-lg font-black">{{ $step['title'] }}</h2>
                <p class="ui-subtitle mt-2 text-sm leading-6">{{ $step['text'] }}</p>
            </article>
        @endforeach
    </section>

    <livewire:shared.academic-plan />

    <section class="ui-panel">
        <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h2 class="ui-title text-xl font-black">¿Necesitas revisar tus pendientes?</h2>
                <p class="ui-subtitle mt-2">
                    Tienes {{ $academic['metricas']['actividades_pendientes'] }} actividad(es) pendiente(s) registrada(s) en tus materias.
                </p>
            </div>
            <a class="ui-btn-secondary inline-flex shrink-0" href="{{ route('estudiante.preparacion') }}">
                <i class="ph ph-brain" aria-hidden="true"></i>Ver Mi preparación
            </a>
        </div>
    </section>
</div>
@endsection
