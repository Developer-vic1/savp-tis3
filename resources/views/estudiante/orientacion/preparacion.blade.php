@extends('aula-virtual.layouts.app')

@section('title', 'Mi preparación | SAVP-TIS3')
@section('page-title', 'Mi preparación')

@section('content')
<div class="mx-auto max-w-6xl space-y-6">
    <section class="ui-panel">
        <p class="ui-kicker">Preparación académica</p>
        <h1 class="ui-title mt-2 text-3xl font-black">Organiza lo que necesitas reforzar ahora</h1>
        <p class="ui-subtitle mt-3 max-w-3xl leading-7">
            Esta ventana reúne únicamente tu carga académica y tus actividades pendientes.
            No modifica tu análisis de intereses ni elige una carrera o universidad por ti.
        </p>
    </section>

    <section class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3" aria-label="Resumen de preparación académica">
        @foreach ([
            ['label' => 'Materias activas', 'value' => $academic['metricas']['asignaturas'], 'icon' => 'ph-books'],
            ['label' => 'Actividades pendientes', 'value' => $academic['metricas']['actividades_pendientes'], 'icon' => 'ph-list-checks'],
            ['label' => 'Tareas entregadas', 'value' => $academic['metricas']['tareas_entregadas'], 'icon' => 'ph-check-circle'],
        ] as $metric)
            <article class="ui-panel">
                <div class="flex items-center justify-between gap-3">
                    <div>
                        <p class="ui-muted text-sm">{{ $metric['label'] }}</p>
                        <p class="mt-2 text-3xl font-black">{{ $metric['value'] }}</p>
                    </div>
                    <span class="flex h-12 w-12 items-center justify-center rounded-2xl" style="background: var(--ui-soft); color: var(--ui-primary);">
                        <i class="ph {{ $metric['icon'] }} text-xl" aria-hidden="true"></i>
                    </span>
                </div>
            </article>
        @endforeach
    </section>

    <section class="ui-panel">
        <div class="flex flex-wrap items-end justify-between gap-3">
            <div>
                <p class="ui-kicker">Prioridad actual</p>
                <h2 class="ui-title mt-1 text-2xl font-black">Actividades pendientes</h2>
                <p class="ui-subtitle mt-2">Empieza por las entregas con fecha más cercana y abre la materia para revisar sus instrucciones.</p>
            </div>
            <span class="rounded-full border px-3 py-1 text-sm font-bold" style="border-color: var(--ui-border);">
                {{ $academic['pendientes']->count() }} pendiente(s)
            </span>
        </div>

        <div class="mt-6 grid gap-4 md:grid-cols-2">
            @forelse($academic['pendientes'] as $tarea)
                <article class="rounded-2xl border p-5" style="border-color: var(--ui-border);">
                    <p class="ui-kicker">{{ $tarea->claseVirtual?->planAsignatura?->asignatura?->nom_asi ?? 'Actividad académica' }}</p>
                    <h3 class="ui-title mt-1 text-lg font-black">{{ $tarea->tit_tar }}</h3>
                    <p class="ui-subtitle mt-3 flex items-center gap-2 text-sm">
                        <i class="ph ph-calendar" aria-hidden="true"></i>
                        {{ $tarea->fec_lim_tar?->format('d/m/Y H:i') ?? 'Sin fecha límite registrada' }}
                    </p>
                    @if($tarea->cod_cla)
                        <a class="ui-btn-secondary mt-4 inline-flex text-sm" href="{{ route('estudiante.materia', $tarea->cod_cla) }}">
                            <i class="ph ph-arrow-square-out" aria-hidden="true"></i>Abrir materia
                        </a>
                    @endif
                </article>
            @empty
                <div class="rounded-2xl border border-dashed p-7 text-center md:col-span-2" style="border-color: var(--ui-border);">
                    <i class="ph ph-check-circle text-3xl" aria-hidden="true" style="color: var(--ui-primary);"></i>
                    <h3 class="mt-3 font-black">No tienes actividades pendientes registradas</h3>
                    <p class="ui-subtitle mt-2">Puedes revisar tus materias o avanzar con tu planificación personal.</p>
                </div>
            @endforelse
        </div>
    </section>

    <section class="ui-panel">
        <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h2 class="ui-title text-xl font-black">Convierte tu preparación en próximos pasos</h2>
                <p class="ui-subtitle mt-2">Cuando tengas claras tus prioridades, registra una meta concreta en “Mi plan”.</p>
            </div>
            <a class="ui-btn-primary inline-flex shrink-0" href="{{ route('estudiante.plan') }}">
                <i class="ph ph-target" aria-hidden="true"></i>Ir a Mi plan
            </a>
        </div>
    </section>
</div>
@endsection
