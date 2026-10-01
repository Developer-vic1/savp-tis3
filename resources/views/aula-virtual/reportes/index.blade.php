@extends('aula-virtual.layouts.app')

@section('title', 'Reportes Aula Virtual | SAVP-TIS3')
@section('page-title', 'Reportes Aula Virtual')

@section('content')
    <div class="space-y-6">
        <section class="ui-panel">
            <p class="ui-kicker">SAVP-TIS3</p>
            <h2 class="ui-title mt-2 text-2xl font-black">Consolidado de mis cursos</h2>
            <p class="ui-subtitle mt-3 text-sm">Estudiantes con inscripción vigente, materiales publicados, tareas publicadas o cerradas y sesiones de asistencia cerradas.</p>
            <form method="GET" class="mt-4 grid gap-3 sm:grid-cols-3">
                <label class="ui-label">Curso o asignatura<input class="ui-input" name="search" maxlength="100" value="{{ $filters['search'] ?? '' }}"></label>
                <label class="ui-label">Año de gestión<input class="ui-input" name="gestion" inputmode="numeric" maxlength="4" value="{{ $filters['gestion'] ?? '' }}"></label>
                <button class="ui-btn-primary self-end" type="submit">Filtrar</button>
            </form>
        </section>

        <section class="grid gap-4 lg:grid-cols-2">
            @forelse ($consolidados as $reporte)
                <article class="ui-panel">
                    <h3 class="ui-title text-lg font-black">{{ $reporte['curso']->nom_cla }}</h3>
                    <a class="ui-btn-secondary mt-2" href="{{ route('aula-virtual.docente.curso', $reporte['curso']->cod_cla) }}">Abrir curso</a>
                    <a class="ui-btn-primary mt-2" href="{{ route('aula-virtual.docente.reportes.curso.pdf', $reporte['curso']->cod_cla) }}">Descargar consolidado PDF</a>
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
        {{ $cursos->links() }}
    </div>
@endsection
