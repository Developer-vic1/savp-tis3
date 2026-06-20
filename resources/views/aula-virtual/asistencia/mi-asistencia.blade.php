@extends('aula-virtual.layouts.app')

@section('title', 'Mi asistencia | SAVP-TIS3')
@section('page-title', 'Mi asistencia')

@section('content')
    <div class="space-y-6">
        <section class="grid gap-5 md:grid-cols-2 xl:grid-cols-5">
            @include('aula-virtual.componentes.metric-card', ['titulo' => 'Porcentaje', 'valor' => isset($resumen['porcentaje']) ? $resumen['porcentaje'] . '%' : 'Disponible según registro'])
            @include('aula-virtual.componentes.metric-card', ['titulo' => 'Presentes', 'valor' => $resumen['presentes'] ?? 0])
            @include('aula-virtual.componentes.metric-card', ['titulo' => 'Tardanzas', 'valor' => $resumen['tardanzas'] ?? 0])
            @include('aula-virtual.componentes.metric-card', ['titulo' => 'Faltas', 'valor' => $resumen['faltas'] ?? 0])
            @include('aula-virtual.componentes.metric-card', ['titulo' => 'Justificadas', 'valor' => $resumen['justificadas'] ?? 0])
        </section>

        <section class="ui-panel">
            <h2 class="ui-title text-xl font-black">Historial reciente</h2>
            <div class="mt-4 space-y-3">
                @forelse ($registros as $registro)
                    <article class="flex flex-col gap-2 rounded-lg border p-4 sm:flex-row sm:items-center sm:justify-between" style="border-color: var(--ui-border);">
                        <div>
                            <p class="font-black">{{ $registro->asistenciaClase?->claseVirtual?->planAsignatura?->asignatura?->nom_asi }}</p>
                            <p class="ui-muted text-sm">{{ optional($registro->asistenciaClase?->fec_asi_cla)->format('d/m/Y') }}</p>
                        </div>
                        @include('aula-virtual.componentes.status-badge', ['estado' => $registro->estadoAsistencia?->nom_est_asi ?? 'Pendiente'])
                    </article>
                @empty
                    @include('aula-virtual.componentes.empty-state', ['titulo' => 'Historial reciente.', 'descripcion' => 'Los registros de asistencia aparecerán cuando el docente los guarde.'])
                @endforelse
            </div>
        </section>
    </div>
@endsection
