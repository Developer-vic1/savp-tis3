@extends('aula-virtual.layouts.app')

@section('title', 'Curso docente | SAVP-TIS3')
@section('page-title', $curso->planAsignatura?->asignatura?->nom_asi ?? $curso->nom_cla)

@section('content')
    <div class="space-y-6">
        <!-- Panel Principal del Curso -->
        <section class="ui-panel">
            <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
                <div>
                    <p class="ui-kicker">Gestión del curso</p>
                    <h2 class="ui-title mt-1 text-2xl font-black">{{ $curso->nom_cla }}</h2>
                    <p class="ui-subtitle mt-2 text-sm">
                        {{ $curso->planAsignatura?->curso?->nom_cur ?? '' }} {{ $curso->planAsignatura?->paralelo?->nom_par ?? '' }} — 
                        Turno: {{ $curso->planAsignatura?->turno?->nom_tur ?? '' }}
                    </p>
                </div>
                <div class="flex flex-wrap gap-2">
                    @include('aula-virtual.componentes.icon-action-button', ['href' => route('aula-virtual.docente.asistencia.registrar', $curso->cod_cla), 'icon' => 'asistencia', 'label' => 'Control Asistencia', 'variant' => 'secondary'])
                    @include('aula-virtual.componentes.icon-action-button', ['href' => route('aula-virtual.materiales.index', $curso->cod_cla), 'icon' => 'crear-material', 'label' => 'Ver Materiales'])
                </div>
            </div>
        </section>

        @include('aula-virtual.componentes.trimester-tabs')

        <!-- Paneles Reactivos de Creación (Material y Tarea) -->
        <div class="space-y-6">
            <livewire:aula-virtual.tareas.crear-tarea :codCla="$curso->cod_cla" />
            <livewire:aula-virtual.materiales.crear-material :codCla="$curso->cod_cla" />
        </div>

        <!-- Listado de Tareas del Curso -->
        <section class="ui-panel">
            <div class="flex items-center justify-between border-b border-gray-100 dark:border-gray-700 pb-3 mb-4">
                <h3 class="ui-title text-xl font-black">Actividades y Tareas del Curso</h3>
                <span class="text-xs font-semibold px-2.5 py-1 rounded-full bg-indigo-50 text-indigo-700 dark:bg-indigo-900/30 dark:text-indigo-300">
                    Total: {{ $curso->tareas->count() }}
                </span>
            </div>

            <div class="space-y-3">
                @forelse ($curso->tareas->sortByDesc('created_at') as $tarea)
                    <article class="flex flex-col gap-3 rounded-lg border p-4 sm:flex-row sm:items-center sm:justify-between transition hover:shadow-sm" style="border-color: var(--ui-border);">
                        <div>
                            <div class="flex items-center gap-2 mb-1">
                                <span class="px-2 py-0.5 text-xs font-bold rounded {{ $tarea->est_tar === 'PUBLICADA' ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-700' }}">
                                    {{ $tarea->est_tar }}
                                </span>
                                <span class="text-xs text-gray-500 font-medium">({{ $tarea->tip_tar }})</span>
                            </div>
                            <p class="font-black text-base text-gray-900 dark:text-white">{{ $tarea->tit_tar }}</p>
                            <p class="ui-muted text-xs mt-1">
                                Límite: {{ $tarea->fec_lim_tar ? \Carbon\Carbon::parse($tarea->fec_lim_tar)->format('d/m/Y H:i') : 'Sin límite' }} — 
                                Puntaje: {{ (int)$tarea->pun_max_tar }} pts
                            </p>
                        </div>
                        <div class="flex items-center gap-2">
                            @include('aula-virtual.componentes.icon-action-button', ['href' => route('aula-virtual.docente.tareas.revisar', $tarea->cod_tar), 'icon' => 'revisar', 'label' => 'Revisar entregas (' . $tarea->entregas->count() . ')', 'variant' => 'secondary'])
                        </div>
                    </article>
                @empty
                    @include('aula-virtual.componentes.empty-state', ['titulo' => 'Tareas activas.', 'descripcion' => 'Las tareas creadas para este curso aparecerán aquí.'])
                @endforelse
            </div>
        </section>
    </div>
@endsection
