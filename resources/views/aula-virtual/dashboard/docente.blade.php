@extends('aula-virtual.layouts.app')

@section('title', 'Aula Virtual Docente | SAVP - TIS 3')
@section('page-title', 'Aula Virtual Docente')

@section('content')
    @php
        $user = auth()->user();
        $persona = $user?->persona;
        $nombre = trim(($persona->nom_per ?? '') . ' ' . ($persona->ape_pat_per ?? '') . ' ' . ($persona->ape_mat_per ?? ''));
        $nombre = $nombre ?: ($user->name ?? $user->email ?? 'Docente');

        $resumen = [
            ['titulo' => 'Cursos asignados', 'valor' => '0', 'descripcion' => 'Pendiente de conexion con clase_virtual.', 'badge' => 'Cursos'],
            ['titulo' => 'Actividades activas', 'valor' => '0', 'descripcion' => 'Actividades listas para gestion por curso.', 'badge' => 'Activo', 'tono' => 'info'],
            ['titulo' => 'Entregas por revisar', 'valor' => '0', 'descripcion' => 'Bandeja de revision de tareas.', 'badge' => 'Revision', 'tono' => 'warning'],
            ['titulo' => 'Asistencias pendientes', 'valor' => '0', 'descripcion' => 'Registro futuro por clase y fecha.', 'badge' => 'Asistencia', 'tono' => 'violet'],
            ['titulo' => 'Cuestionarios activos', 'valor' => '0', 'descripcion' => 'Evaluaciones configurables por curso.', 'badge' => 'Aula', 'tono' => 'info'],
            ['titulo' => 'Alertas academicas', 'valor' => '0', 'descripcion' => 'Seguimiento preventivo del rendimiento.', 'badge' => 'Alertas', 'tono' => 'danger'],
        ];

        $entregas = [
            ['nombre' => 'Actividad diagnostica', 'curso' => '1ro A - Sistemas Informaticos', 'estado' => 'Sin entregas', 'badge' => 'ui-badge-muted'],
            ['nombre' => 'Practica de aula', 'curso' => '2do B - Matematica', 'estado' => 'Por revisar', 'badge' => 'ui-badge-warning'],
            ['nombre' => 'Cuestionario inicial', 'curso' => '3ro A - Comunicacion', 'estado' => 'Programado', 'badge' => 'ui-badge-info'],
        ];
    @endphp

    <div class="space-y-8">
        <section class="rounded-[1.8rem] border p-6 shadow-sm"
            style="background: linear-gradient(135deg, var(--ui-surface), var(--ui-surface-soft)); border-color: var(--ui-border);">
            <div class="flex flex-col gap-5 lg:flex-row lg:items-center lg:justify-between">
                <div>
                    <span class="ui-badge-info">Docente</span>
                    <h2 class="ui-title mt-4 text-3xl font-black tracking-tight">Bienvenido/a, {{ $nombre }}</h2>
                    <p class="ui-subtitle mt-3 text-base leading-8">Panel inicial para organizar cursos, actividades y seguimiento academico.</p>
                </div>

                <a href="#" class="ui-btn-primary">Ver mis cursos</a>
            </div>
        </section>

        <section class="grid gap-5 md:grid-cols-2 xl:grid-cols-3">
            @foreach ($resumen as $item)
                @include('aula-virtual.componentes.tarjeta-acceso', [
                    'titulo' => $item['titulo'],
                    'valor' => $item['valor'],
                    'descripcion' => $item['descripcion'],
                    'badge' => $item['badge'],
                    'tono' => $item['tono'] ?? 'primary',
                ])
            @endforeach
        </section>

        <section>
            <div class="mb-4 flex items-center justify-between gap-4">
                <div>
                    <p class="ui-kicker">Cursos</p>
                    <h2 class="ui-title mt-1 text-2xl font-black">Mis cursos</h2>
                </div>
                <span class="ui-badge-muted">Preparado para clase_virtual</span>
            </div>

            <div class="grid gap-5 lg:grid-cols-3">
                @foreach (['1ro A - Sistemas Informaticos', '2do B - Matematica', '3ro A - Comunicacion'] as $curso)
                    <article class="ui-card ui-card-hover rounded-[1.6rem] p-5">
                        <span class="ui-badge-info">Ejemplo</span>
                        <h3 class="ui-title mt-4 text-xl font-black">{{ $curso }}</h3>
                        <p class="ui-subtitle mt-3 text-sm leading-7">
                            Estructura preparada para materiales, actividades, asistencia y calificaciones por curso.
                        </p>
                    </article>
                @endforeach
            </div>
        </section>

        <section class="grid gap-6 xl:grid-cols-[1.1fr_.9fr]">
            <div>
                <div class="mb-4">
                    <p class="ui-kicker">Revision</p>
                    <h2 class="ui-title mt-1 text-2xl font-black">Entregas recientes</h2>
                </div>

                <div class="space-y-3">
                    @foreach ($entregas as $entrega)
                        <div class="ui-panel flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                            <div>
                                <p class="font-black">{{ $entrega['nombre'] }}</p>
                                <p class="ui-muted mt-1 text-sm">{{ $entrega['curso'] }}</p>
                            </div>
                            <span class="{{ $entrega['badge'] }}">{{ $entrega['estado'] }}</span>
                        </div>
                    @endforeach
                </div>
            </div>

            <div class="space-y-6">
                <section class="ui-panel">
                    <span class="ui-badge-violet">Asistencia</span>
                    <h2 class="ui-title mt-4 text-xl font-black">Registro por curso</h2>
                    <p class="ui-subtitle mt-3 text-sm leading-7">
                        El registro de asistencia se gestionara por curso y quedara conectado a las clases virtuales.
                    </p>
                </section>

                <section class="ui-panel">
                    <span class="ui-badge-info">Reportes</span>
                    <h2 class="ui-title mt-4 text-xl font-black">Reportes academicos</h2>
                    <p class="ui-subtitle mt-3 text-sm leading-7">
                        Resumen futuro de rendimiento, participacion, entregas y alertas academicas del curso.
                    </p>
                </section>
            </div>
        </section>
    </div>
@endsection
