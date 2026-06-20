@extends('aula-virtual.layouts.app')

@section('title', 'Aula Virtual Estudiante | SAVP - TIS 3')
@section('page-title', 'Aula Virtual Estudiante')

@section('content')
    @php
        $user = auth()->user();
        $persona = $user?->persona;
        $nombre = trim(($persona->nom_per ?? '') . ' ' . ($persona->ape_pat_per ?? '') . ' ' . ($persona->ape_mat_per ?? ''));
        $nombre = $nombre ?: ($user->name ?? $user->email ?? 'Estudiante');

        $resumen = [
            ['titulo' => 'Asignaturas activas', 'valor' => '0', 'descripcion' => 'Pendiente de conexion con clase_virtual y clase_estudiante.', 'badge' => 'LMS'],
            ['titulo' => 'Actividades pendientes', 'valor' => '0', 'descripcion' => 'Sin actividades asignadas por ahora.', 'badge' => 'Pendiente', 'tono' => 'warning'],
            ['titulo' => 'Cuestionarios disponibles', 'valor' => '0', 'descripcion' => 'Los cuestionarios se habilitaran por asignatura.', 'badge' => 'Aula', 'tono' => 'info'],
            ['titulo' => 'Promedio actual', 'valor' => '--', 'descripcion' => 'Calculo disponible cuando existan calificaciones.', 'badge' => 'Notas', 'tono' => 'violet'],
            ['titulo' => 'Proximas fechas', 'valor' => '0', 'descripcion' => 'Calendario academico del Aula Virtual.', 'badge' => 'Agenda', 'tono' => 'info'],
            ['titulo' => 'Notificaciones recientes', 'valor' => '0', 'descripcion' => 'Avisos institucionales y de cursos.', 'badge' => 'Nuevo'],
        ];

        $actividades = [
            ['nombre' => 'Revision de material introductorio', 'curso' => 'Comunicacion y Lenguajes', 'estado' => 'Proxima', 'badge' => 'ui-badge-info'],
            ['nombre' => 'Practica guiada en aula', 'curso' => 'Sistemas Informaticos', 'estado' => 'Pendiente', 'badge' => 'ui-badge-warning'],
            ['nombre' => 'Cierre de unidad', 'curso' => 'Matematica', 'estado' => 'Vencida', 'badge' => 'ui-badge-danger'],
        ];
    @endphp

    <div class="space-y-8">
        <section class="rounded-[1.8rem] border p-6 shadow-sm"
            style="background: linear-gradient(135deg, var(--ui-surface), var(--ui-surface-soft)); border-color: var(--ui-border);">
            <div class="flex flex-col gap-5 lg:flex-row lg:items-center lg:justify-between">
                <div>
                    <span class="ui-badge-success">Estudiante</span>
                    <h2 class="ui-title mt-4 text-3xl font-black tracking-tight">Bienvenido/a, {{ $nombre }}</h2>
                    <p class="ui-subtitle mt-3 text-base leading-8">Informacion academica pendiente</p>
                </div>

                <a href="#" class="ui-btn-primary">Ver mis asignaturas</a>
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
                    <h2 class="ui-title mt-1 text-2xl font-black">Mis asignaturas</h2>
                </div>
                <span class="ui-badge-muted">Preparado para clase_estudiante</span>
            </div>

            <div class="grid gap-5 lg:grid-cols-3">
                @foreach (['Matematica', 'Sistemas Informaticos', 'Comunicacion y Lenguajes'] as $asignatura)
                    <article class="ui-card ui-card-hover rounded-[1.6rem] p-5">
                        <span class="ui-badge-info">Ejemplo</span>
                        <h3 class="ui-title mt-4 text-xl font-black">{{ $asignatura }}</h3>
                        <p class="ui-subtitle mt-3 text-sm leading-7">
                            La informacion real se conectara con la clase virtual asignada al estudiante.
                        </p>
                    </article>
                @endforeach
            </div>
        </section>

        <section class="grid gap-6 xl:grid-cols-[1.1fr_.9fr]">
            <div>
                <div class="mb-4">
                    <p class="ui-kicker">Seguimiento</p>
                    <h2 class="ui-title mt-1 text-2xl font-black">Actividades proximas</h2>
                </div>

                <div class="space-y-3">
                    @foreach ($actividades as $actividad)
                        <div class="ui-panel flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                            <div>
                                <p class="font-black">{{ $actividad['nombre'] }}</p>
                                <p class="ui-muted mt-1 text-sm">{{ $actividad['curso'] }}</p>
                            </div>
                            <span class="{{ $actividad['badge'] }}">{{ $actividad['estado'] }}</span>
                        </div>
                    @endforeach
                </div>
            </div>

            <div class="space-y-6">
                <section class="ui-panel">
                    <span class="ui-badge-violet">Orientacion</span>
                    <h2 class="ui-title mt-4 text-xl font-black">Orientacion academica-profesional</h2>
                    <p class="ui-subtitle mt-3 text-sm leading-7">
                        Aqui se visualizaran recomendaciones academicas y profesionales generadas a partir del
                        rendimiento, cuestionarios, asistencia y evolucion del estudiante.
                    </p>
                </section>

                <section class="ui-panel">
                    <span class="ui-badge-info">Calendario rapido</span>
                    <ul class="mt-4 space-y-3 text-sm">
                        <li class="flex items-center justify-between gap-3">
                            <span class="ui-subtitle">Publicacion de materiales</span>
                            <span class="ui-muted">Pendiente</span>
                        </li>
                        <li class="flex items-center justify-between gap-3">
                            <span class="ui-subtitle">Entrega de actividades</span>
                            <span class="ui-muted">Pendiente</span>
                        </li>
                    </ul>
                </section>
            </div>
        </section>
    </div>
@endsection
