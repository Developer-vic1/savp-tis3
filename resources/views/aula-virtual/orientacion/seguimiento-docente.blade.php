@extends('aula-virtual.layouts.app')

@section('title', 'Seguimiento orientación | SAVP-TIS3')
@section('page-title', 'Seguimiento de orientación')

@section('content')
    <div class="space-y-6">
        <section class="ui-panel">
            <p class="ui-kicker">Orientación académica-profesional</p>
            <h2 class="ui-title mt-2 text-2xl font-black">Seguimiento docente</h2>
            <p class="ui-subtitle mt-3 text-sm">Actividades registradas en la gestión de un curso asignado y con inscripción vigente. Esta consulta no modifica respuestas ni genera compatibilidades profesionales.</p>
            <form method="GET" class="mt-4 grid gap-3 sm:grid-cols-3">
                <label class="ui-label">Estudiante<input class="ui-input" name="search" value="{{ $filters['search'] ?? '' }}" maxlength="100"></label>
                <label class="ui-label">Estado<select class="ui-select" name="estado"><option value="">Todos</option>@foreach(['pendiente','en_proceso','finalizado','revisado','requiere_seguimiento'] as $state)<option value="{{ $state }}" @selected(($filters['estado'] ?? '') === $state)>{{ str_replace('_', ' ', $state) }}</option>@endforeach</select></label>
                <button class="ui-btn-primary self-end" type="submit">Filtrar</button>
            </form>
        </section>

        @if ($rows->isEmpty())
            @include('aula-virtual.componentes.empty-state', ['titulo' => 'Seguimiento de orientación.', 'descripcion' => 'Los estudiantes de cursos asignados aparecerán para acompañamiento docente.'])
        @else
            <div class="grid gap-5 lg:grid-cols-2">
                @foreach ($rows as $row)
                    <article class="ui-panel">
                        <h3 class="ui-title text-lg font-black">{{ trim(($row->estudiante?->persona?->nom_per ?? '').' '.($row->estudiante?->persona?->ape_pat_per ?? '')) }}</h3>
                        <dl class="ui-muted mt-3 space-y-2">
                            <div><dt>Gestión</dt><dd>{{ $row->gestionAcademica?->ani_gea ?? 'Sin gestión registrada' }}</dd></div>
                            <div><dt>Estado registrado</dt><dd>{{ str_replace('_', ' ', $row->estado) }}</dd></div>
                            <div><dt>Avance registrado</dt><dd>{{ $row->avance }}%</dd></div>
                            <div><dt>Perfil orientativo local</dt><dd>{{ $row->resultado ? ($dimensiones[$row->resultado->perfil_predominante] ?? 'Sin perfil identificado') : 'Sin resultado finalizado' }}</dd></div>
                        </dl>
                    </article>
                @endforeach
            </div>
        @endif
        {{ $rows->links() }}
    </div>
@endsection
