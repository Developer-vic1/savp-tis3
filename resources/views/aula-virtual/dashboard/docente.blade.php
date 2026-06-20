@extends('aula-virtual.layouts.app')

@section('title', 'SAVP-TIS3 | Aula Virtual Docente')
@section('page-title', 'SAVP-TIS3 Aula Virtual')

@section('content')
    @php
        $user = auth()->user();
        $persona = $user?->persona;
        $nombre = trim(($persona->nom_per ?? '') . ' ' . ($persona->ape_pat_per ?? '') . ' ' . ($persona->ape_mat_per ?? ''));
        $nombre = $nombre ?: ($user->email ?? 'Docente');
        $metricas = $metricas ?? [];
    @endphp

    <div class="space-y-8">
        <section class="rounded-lg border p-6 shadow-sm" style="background: var(--ui-surface); border-color: var(--ui-border);">
            <div class="flex flex-col gap-5 lg:flex-row lg:items-center lg:justify-between">
                <div>
                    @include('aula-virtual.componentes.status-badge', ['estado' => 'Publicado'])
                    <h2 class="ui-title mt-4 text-3xl font-black tracking-tight">Bienvenido/a, {{ $nombre }}</h2>
                    <p class="ui-subtitle mt-3 text-base leading-8">
                        Cursos asignados, seguimiento académico, asistencia y orientación académica-profesional.
                    </p>
                </div>

                @include('aula-virtual.componentes.icon-action-button', [
                    'href' => route('aula-virtual.docente.cursos'),
                    'icon' => 'entrar',
                    'label' => 'Mis cursos',
                ])
            </div>
        </section>

        <section class="grid gap-5 md:grid-cols-2 xl:grid-cols-3">
            @include('aula-virtual.componentes.metric-card', ['titulo' => 'Cursos asignados', 'valor' => $metricas['cursos_asignados'] ?? 0, 'descripcion' => 'Cursos asignados.'])
            @include('aula-virtual.componentes.metric-card', ['titulo' => 'Estudiantes asignados', 'valor' => $metricas['estudiantes_asignados'] ?? 0, 'descripcion' => 'Participantes inscritos.'])
            @include('aula-virtual.componentes.metric-card', ['titulo' => 'Tareas activas', 'valor' => $metricas['tareas_activas'] ?? 0, 'descripcion' => 'Actividades publicadas.'])
            @include('aula-virtual.componentes.metric-card', ['titulo' => 'Entregas por revisar', 'valor' => $metricas['entregas_por_revisar'] ?? 0, 'descripcion' => 'Bandeja de revisión.'])
            @include('aula-virtual.componentes.metric-card', ['titulo' => 'Asistencias pendientes', 'valor' => $metricas['asistencias_pendientes'] ?? 0, 'descripcion' => 'Registros por curso.'])
            @include('aula-virtual.componentes.metric-card', ['titulo' => 'Seguimiento orientación', 'valor' => $metricas['seguimiento_orientacion'] ?? 0, 'descripcion' => 'Acompañamiento académico-profesional.'])
        </section>

        <section>
            <div class="mb-4 flex flex-col gap-3 sm:flex-row sm:items-end sm:justify-between">
                <div>
                    <p class="ui-kicker">Mis cursos</p>
                    <h2 class="ui-title mt-1 text-2xl font-black">Cursos asignados</h2>
                </div>
                <div class="flex flex-wrap gap-2">
                    @include('aula-virtual.componentes.icon-action-button', ['href' => route('aula-virtual.docente.reportes'), 'icon' => 'reporte', 'label' => 'Reportes', 'variant' => 'secondary'])
                    @include('aula-virtual.componentes.icon-action-button', ['href' => route('aula-virtual.docente.orientacion.seguimiento'), 'icon' => 'orientacion', 'label' => 'Orientación', 'variant' => 'secondary'])
                </div>
            </div>

            @if (($cursos ?? collect())->isEmpty())
                @include('aula-virtual.componentes.empty-state', [
                    'titulo' => 'Cursos asignados.',
                    'descripcion' => 'Los cursos asignados aparecerán según la planificación académica vigente.',
                ])
            @else
                <div class="grid gap-5 lg:grid-cols-3">
                    @foreach ($cursos as $curso)
                        @include('aula-virtual.componentes.course-card', [
                            'curso' => $curso,
                            'resumen' => app(\App\Services\AulaVirtual\CursoVirtualService::class)->cursoResumen($curso),
                            'href' => route('aula-virtual.docente.curso', $curso->cod_cla),
                            'docente' => true,
                        ])
                    @endforeach
                </div>
            @endif
        </section>

        <section class="grid gap-6 xl:grid-cols-3">
            @foreach ([
                ['icon' => 'crear-material', 'label' => 'Crear material'],
                ['icon' => 'crear-tarea', 'label' => 'Crear tarea'],
                ['icon' => 'asistencia', 'label' => 'Registrar asistencia'],
            ] as $accion)
                <article class="ui-panel">
                    <h3 class="ui-title text-lg font-black">{{ $accion['label'] }}</h3>
                    <p class="ui-subtitle mt-2 text-sm leading-7">Selecciona un curso asignado para ejecutar esta acción.</p>
                </article>
            @endforeach
        </section>
    </div>
@endsection
