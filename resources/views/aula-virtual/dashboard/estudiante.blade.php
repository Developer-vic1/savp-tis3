@extends('aula-virtual.layouts.app')

@section('title', 'SAVP-TIS3 | Aula Virtual')
@section('page-title', 'SAVP-TIS3 Aula Virtual')

@section('content')
    @php
        $user = auth()->user();
        $persona = $user?->persona;
        $nombre = trim(($persona->nom_per ?? '') . ' ' . ($persona->ape_pat_per ?? '') . ' ' . ($persona->ape_mat_per ?? ''));
        $nombre = $nombre ?: ($user->email ?? 'Estudiante');
        $metricas = $metricas ?? [];
    @endphp

    <div class="space-y-8">
        @can('Orientacion_Academica_Profesional')
            <section class="ui-panel"><h2 class="ui-title text-xl font-bold">Mi orientación</h2><p class="ui-subtitle mt-2">Revisa tu perfil, completa tus intereses y explora carreras con evidencia.</p><a class="ui-btn-primary mt-4 inline-flex" href="{{ route('aula-virtual.estudiante.orientacion.aporte') }}">Ver el estado de mi perfil</a></section>
        @endcan
        <section class="rounded-lg border p-6 shadow-sm" style="background: var(--ui-surface); border-color: var(--ui-border);">
            <div class="flex flex-col gap-5 lg:flex-row lg:items-center lg:justify-between">
                <div>
                    @include('aula-virtual.componentes.status-badge', ['estado' => 'En proceso'])
                    <h2 class="ui-title mt-4 text-3xl font-black tracking-tight">Bienvenido/a, {{ $nombre }}</h2>
                    <p class="ui-subtitle mt-3 text-base leading-8">
                        Información académica disponible según inscripción.
                    </p>
                </div>

                @include('aula-virtual.componentes.icon-action-button', [
                    'href' => route('aula-virtual.estudiante.asignaturas'),
                    'icon' => 'entrar',
                    'label' => 'Aulas virtuales',
                ])
            </div>
        </section>

        <section class="grid gap-5 md:grid-cols-2 xl:grid-cols-3">
            @include('aula-virtual.componentes.metric-card', ['titulo' => 'Inscripciones vigentes', 'valor' => $metricas['inscripciones_activas'] ?? 0, 'descripcion' => 'Registros académicos oficiales.'])
            @include('aula-virtual.componentes.metric-card', ['titulo' => 'Aulas virtuales', 'valor' => $metricas['asignaturas'] ?? 0, 'descripcion' => 'Clases LMS activas vinculadas a tu inscripción.'])
            @include('aula-virtual.componentes.metric-card', ['titulo' => 'Actividades pendientes', 'valor' => $metricas['actividades_pendientes'] ?? 0, 'descripcion' => 'Actividades pendientes.'])
            @include('aula-virtual.componentes.metric-card', ['titulo' => 'Tareas entregadas', 'valor' => $metricas['tareas_entregadas'] ?? 0, 'descripcion' => 'Entregas registradas.'])
            @include('aula-virtual.componentes.metric-card', ['titulo' => 'Promedio LMS (%)', 'valor' => $metricas['promedio_actual'] ?? 'Sin calificaciones', 'descripcion' => 'Puntajes de tareas normalizados según su máximo.'])
            @include('aula-virtual.componentes.metric-card', ['titulo' => 'Asistencia general', 'valor' => isset($metricas['asistencia_general']) ? $metricas['asistencia_general'] . '%' : 'Disponible según registro', 'descripcion' => 'Seguimiento de asistencia.'])
            @include('aula-virtual.componentes.metric-card', ['titulo' => 'Orientación en proceso', 'valor' => $metricas['orientacion_en_proceso'] ?? 0, 'descripcion' => 'Orientación académica-profesional.'])
        </section>

        <section class="ui-panel" aria-label="Inscripciones oficiales">
            <h2 class="ui-title text-2xl font-black">Inscripciones oficiales</h2>
            <p class="ui-muted mt-2 text-sm">Esta información proviene de tus inscripciones registradas; es independiente de la creación de aulas virtuales.</p>
            @forelse($inscripciones ?? collect() as $inscripcion)
                <div class="mt-4 rounded-xl border p-4" style="border-color: var(--ui-border);">
                    <strong class="block">{{ $inscripcion->curso?->nom_cur ?? 'Curso no disponible' }}</strong>
                    <p class="ui-muted mt-1 text-sm">Gestión {{ $inscripcion->gestionAcademica?->ani_gea ?? 'no disponible' }} · Paralelo {{ $inscripcion->paralelo?->nom_par ?? 'no disponible' }} · Turno {{ $inscripcion->turno?->nom_tur ?? 'no disponible' }}</p>
                </div>
            @empty
                <p class="ui-muted mt-4">No hay inscripciones activas registradas para tu cuenta.</p>
            @endforelse
        </section>

        <section>
            <div class="mb-4 flex flex-col gap-3 sm:flex-row sm:items-end sm:justify-between">
                <div>
                    <p class="ui-kicker">Clases LMS</p>
                    <h2 class="ui-title mt-1 text-2xl font-black">Aulas virtuales</h2>
                </div>
            </div>

            @if (($cursos ?? collect())->isEmpty())
                @include('aula-virtual.componentes.empty-state', [
                    'titulo' => 'Aún no hay aulas virtuales activas.',
                    'descripcion' => ($inscripciones ?? collect())->isNotEmpty()
                        ? 'Tu inscripción oficial está registrada, pero todavía no tiene una clase LMS activa vinculada.'
                        : 'La lista aparecerá cuando exista una inscripción y una clase LMS activa vinculada.',
                ])
            @else
                <div class="grid gap-5 lg:grid-cols-3">
                    @foreach ($cursos as $curso)
                        @include('aula-virtual.componentes.course-card', [
                            'curso' => $curso,
                            'resumen' => app(\App\Services\AulaVirtual\CursoVirtualService::class)->cursoResumen($curso, app(\App\Services\AulaVirtual\CursoVirtualService::class)->estudianteDeUsuario(auth()->user())),
                            'href' => route('aula-virtual.estudiante.curso', $curso->cod_cla),
                        ])
                    @endforeach
                </div>
            @endif
        </section>

        <section class="grid gap-6 xl:grid-cols-[1.1fr_.9fr]">
            <div>
                <div class="mb-4">
                    <p class="ui-kicker">Actividades pendientes</p>
                    <h2 class="ui-title mt-1 text-2xl font-black">Tareas próximas a vencer</h2>
                </div>

                @if (($pendientes ?? collect())->isEmpty())
                    @include('aula-virtual.componentes.empty-state', [
                        'titulo' => 'Actividades pendientes.',
                        'descripcion' => 'No existen actividades abiertas para revisión en este momento.',
                    ])
                @else
                    <div class="space-y-3">
                        @foreach ($pendientes->take(5) as $tarea)
                            <article class="ui-panel flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                                <div>
                                    <p class="font-black">{{ $tarea->tit_tar }}</p>
                                    <p class="ui-muted mt-1 text-sm">{{ $tarea->claseVirtual?->planAsignatura?->asignatura?->nom_asi }}</p>
                                </div>
                                @include('aula-virtual.componentes.icon-action-button', [
                                    'href' => route('aula-virtual.estudiante.tareas.entregar', $tarea->cod_tar),
                                    'icon' => 'entregar',
                                    'label' => 'Entregar',
                                ])
                            </article>
                        @endforeach
                    </div>
                @endif
            </div>

            <div class="space-y-6">
                <section class="ui-panel">
                    @include('aula-virtual.componentes.status-badge', ['estado' => 'En proceso'])
                    <h2 class="ui-title mt-4 text-xl font-black">Orientación académica-profesional</h2>
                    <p class="ui-subtitle mt-3 text-sm leading-7">
                        El perfil oficial separa intereses, preparación y evidencia. El explorador local anterior conserva su historial y no es el instrumento RIASEC oficial.
                    </p>
                    <div class="mt-5 flex flex-wrap gap-2">
                        <livewire:aula-virtual.orientacion.explorador-vocacional :auto-open="false" />
                        @include('aula-virtual.componentes.icon-action-button', [
                            'href' => route('aula-virtual.estudiante.orientacion.aporte'),
                            'icon' => 'entrar',
                            'label' => 'Ver orientación',
                            'variant' => 'secondary',
                        ])
                    </div>
                </section>

                <section class="ui-panel">
                    <h2 class="ui-title text-xl font-black">Calendario académico</h2>
                    <p class="ui-subtitle mt-3 text-sm leading-7">
                        Cursos asignados, materiales recientes, actividades pendientes y notificaciones institucionales.
                    </p>
                </section>
            </div>
        </section>
    </div>
@endsection
