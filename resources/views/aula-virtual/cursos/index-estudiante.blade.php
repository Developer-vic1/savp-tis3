@extends('aula-virtual.layouts.app')

@section('title', 'Mis asignaturas | SAVP-TIS3')
@section('page-title', 'Mis asignaturas')

@section('content')
    <div class="space-y-6">
        <section class="ui-panel">
            <div class="flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between">
                <div>
                    <p class="ui-kicker">Asignaturas inscritas</p>
                    <h2 class="ui-title mt-1 text-2xl font-black">Cursos asignados</h2>
                    <p class="ui-subtitle mt-2 text-sm">Información académica disponible según inscripción.</p>
                </div>
                <div class="grid gap-3 sm:grid-cols-3">
                    <input class="ui-input" placeholder="Buscar asignatura">
                    <select class="ui-select"><option>Gestión vigente</option></select>
                    <select class="ui-select"><option>Trimestre actual</option></select>
                </div>
            </div>
        </section>

        @if ($cursos->isEmpty())
            @include('aula-virtual.componentes.empty-state', ['titulo' => 'Asignaturas inscritas.', 'descripcion' => 'Cursos asignados aparecerán cuando la inscripción esté habilitada.'])
        @else
            <div class="grid gap-5 lg:grid-cols-3">
                @foreach ($cursos as $curso)
                    @include('aula-virtual.componentes.course-card', [
                        'curso' => $curso,
                        'resumen' => $servicio->cursoResumen($curso, $servicio->estudianteDeUsuario(auth()->user())),
                        'href' => route('aula-virtual.estudiante.curso', $curso->cod_cla),
                    ])
                @endforeach
            </div>
        @endif
    </div>
@endsection
