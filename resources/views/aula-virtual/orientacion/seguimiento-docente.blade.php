@extends('aula-virtual.layouts.app')

@section('title', 'Seguimiento orientación | SAVP-TIS3')
@section('page-title', 'Seguimiento de orientación')

@section('content')
    <div class="space-y-6">
        <section class="ui-panel">
            <p class="ui-kicker">Orientación académica-profesional</p>
            <h2 class="ui-title mt-2 text-2xl font-black">Seguimiento docente</h2>
            <p class="ui-subtitle mt-3 text-sm">Ver avance, perfil principal, compatibilidad, observaciones y reportes individuales.</p>
        </section>

        @if ($cursos->isEmpty())
            @include('aula-virtual.componentes.empty-state', ['titulo' => 'Seguimiento de orientación.', 'descripcion' => 'Los estudiantes de cursos asignados aparecerán para acompañamiento docente.'])
        @else
            <div class="grid gap-5 lg:grid-cols-2">
                @foreach ($cursos as $curso)
                    <article class="ui-panel">
                        <h3 class="ui-title text-lg font-black">{{ $curso->nom_cla }}</h3>
                        <p class="ui-muted mt-2 text-sm">{{ $curso->estudiantes->where('est_cla_est', 'ACTIVO')->count() }} estudiantes asignados</p>
                        @include('aula-virtual.componentes.status-badge', ['estado' => 'En proceso'])
                    </article>
                @endforeach
            </div>
        @endif
    </div>
@endsection
