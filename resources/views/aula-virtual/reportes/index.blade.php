@extends('aula-virtual.layouts.app')

@section('title', 'Reportes Aula Virtual | SAVP-TIS3')
@section('page-title', 'Reportes Aula Virtual')

@section('content')
    <div class="space-y-6">
        <section class="ui-panel">
            <p class="ui-kicker">SAVP-TIS3</p>
            <h2 class="ui-title mt-2 text-2xl font-black">Reportes institucionales del Aula Virtual</h2>
            <p class="ui-subtitle mt-3 text-sm">Asistencia, tareas, entregas, calificaciones, orientación y seguimiento docente.</p>
        </section>

        <section class="grid gap-4 lg:grid-cols-2">
            @forelse ($consolidados as $reporte)
                <article class="ui-panel">
                    <h3 class="ui-title text-lg font-black">{{ $reporte['curso']->nom_cla }}</h3>
                    <dl class="mt-4 grid gap-3 sm:grid-cols-2">
                        <div><dt class="ui-muted">Estudiantes</dt><dd class="font-bold">{{ $reporte['estudiantes'] }}</dd></div>
                        <div><dt class="ui-muted">Materiales</dt><dd class="font-bold">{{ $reporte['materiales'] }}</dd></div>
                        <div><dt class="ui-muted">Tareas</dt><dd class="font-bold">{{ $reporte['tareas'] }}</dd></div>
                        <div><dt class="ui-muted">Asistencias</dt><dd class="font-bold">{{ $reporte['asistencias'] }}</dd></div>
                    </dl>
                </article>
            @empty
                @include('aula-virtual.componentes.empty-state', ['titulo' => 'Reporte consolidado institucional.', 'descripcion' => 'Los reportes se generarán con cursos asignados y registros académicos disponibles.'])
            @endforelse
        </section>
    </div>
@endsection
