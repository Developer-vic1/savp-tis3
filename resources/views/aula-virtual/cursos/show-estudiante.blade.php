@extends('aula-virtual.layouts.app')

@section('title', 'Detalle del curso | SAVP-TIS3')
@section('page-title', $curso->planAsignatura?->asignatura?->nom_asi ?? $curso->nom_cla)

@section('content')
    <div class="space-y-6">
        <section class="ui-panel">
            <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
                <div>
                    <p class="ui-kicker">Resumen del curso</p>
                    <h2 class="ui-title mt-1 text-2xl font-black">{{ $curso->nom_cla }}</h2>
                    <p class="ui-subtitle mt-2 text-sm">Actividades, recursos, calificaciones y seguimiento académico.</p>
                </div>
                @include('aula-virtual.componentes.progress-bar', ['value' => $resumen['progreso'] ?? 0, 'label' => 'Progreso'])
            </div>
        </section>

        @include('aula-virtual.componentes.trimester-tabs')

        <section class="grid gap-6 xl:grid-cols-[1fr_.9fr]">
            <div class="ui-panel">
                <h3 class="ui-title text-xl font-black">Actividades</h3>
                <div class="mt-4 space-y-3">
                    @forelse ($curso->tareas->where('est_tar', 'PUBLICADA') as $tarea)
                        <article class="rounded-lg border p-4" style="border-color: var(--ui-border);">
                            <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                                <div>
                                    <p class="font-black">{{ $tarea->tit_tar }}</p>
                                    <p class="ui-muted text-sm">{{ optional($tarea->fec_lim_tar)->format('d/m/Y H:i') ?: 'Fecha definida por el docente' }}</p>
                                </div>
                                @include('aula-virtual.componentes.icon-action-button', ['href' => route('aula-virtual.estudiante.tareas.entregar', $tarea->cod_tar), 'icon' => 'entregar', 'label' => 'Entregar'])
                            </div>
                        </article>
                    @empty
                        @include('aula-virtual.componentes.empty-state', ['titulo' => 'Actividades pendientes.', 'descripcion' => 'Las actividades publicadas aparecerán en esta sección.'])
                    @endforelse
                </div>
            </div>

            <div class="ui-panel">
                <h3 class="ui-title text-xl font-black">Recursos</h3>
                <div class="mt-4 space-y-3">
                    @forelse ($curso->materiales->where('est_mat', 'ACTIVO') as $material)
                        <article class="flex items-center justify-between gap-3 rounded-lg border p-4" style="border-color: var(--ui-border);">
                            <div>
                                <p class="font-black">{{ $material->nom_mat }}</p>
                                <p class="ui-muted text-sm">{{ ucfirst(strtolower($material->tip_mat)) }}</p>
                            </div>
                            @if ($material->rut_mat)
                                @include('aula-virtual.componentes.icon-action-button', ['href' => route('aula-virtual.materiales.descargar', $material->cod_mat), 'icon' => 'descargar', 'label' => 'Descargar', 'variant' => 'secondary'])
                            @endif
                        </article>
                    @empty
                        @include('aula-virtual.componentes.empty-state', ['titulo' => 'Materiales recientes.', 'descripcion' => 'Los materiales publicados por el docente aparecerán aquí.'])
                    @endforelse
                </div>
            </div>
        </section>
    </div>
@endsection
