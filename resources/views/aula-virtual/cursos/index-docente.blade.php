@extends('aula-virtual.layouts.app')

@section('title', 'Mis cursos | SAVP-TIS3')
@section('page-title', 'Mis cursos')

@section('content')
    <div class="space-y-6">
        <section class="ui-panel">
            <div class="flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between">
                <div>
                    <p class="ui-kicker">Cursos asignados</p>
                    <h2 class="ui-title mt-1 text-2xl font-black">Gestión docente del Aula Virtual</h2>
                    <p class="ui-subtitle mt-2 text-sm">Materiales, tareas, asistencia, calificaciones y reportes.</p>
                </div>
                <input class="ui-input max-w-sm" placeholder="Buscar curso o asignatura">
            </div>
        </section>

        @if ($cursos->isEmpty())
            @include('aula-virtual.componentes.empty-state', ['titulo' => 'Cursos asignados.', 'descripcion' => 'Los cursos asignados aparecerán según la planificación académica vigente.'])
        @else
            <div class="grid gap-5 lg:grid-cols-3">
                @foreach ($cursos as $curso)
                    @include('aula-virtual.componentes.course-card', [
                        'curso' => $curso,
                        'resumen' => $servicio->cursoResumen($curso),
                        'href' => route('aula-virtual.docente.curso', $curso->cod_cla),
                        'docente' => true,
                    ])
                @endforeach
            </div>
        @endif
    </div>
@endsection
