@extends('aula-virtual.layouts.app')

@section('title', $title.' | SAVP-TIS3')
@section('page-title', $title)

@section('content')
    <div class="space-y-6">
        <section class="ui-panel">
            <p class="ui-kicker">Experiencia estudiantil</p>
            <h1 class="ui-title mt-2 text-3xl font-black">{{ $title }}</h1>
            <p class="ui-subtitle mt-3 max-w-3xl">{{ $description }}</p>
        </section>

        @if ($area === 'progreso')
            <section class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
                @foreach ([
                    'Materias' => $academic['metricas']['asignaturas'],
                    'Tareas entregadas' => $academic['metricas']['tareas_entregadas'],
                    'Promedio de tareas' => $academic['metricas']['promedio_actual'],
                    'Asistencia general' => $academic['metricas']['asistencia_general'] === null ? null : $academic['metricas']['asistencia_general'].'%',
                ] as $label => $value)
                    <article class="ui-panel">
                        <p class="ui-muted text-sm">{{ $label }}</p>
                        <p class="mt-2 text-2xl font-black">{{ $value ?? 'Aún sin datos' }}</p>
                    </article>
                @endforeach
            </section>
            @if($officialGrades)
            <section class="ui-panel"><h2 class="ui-title text-xl font-black">Historial de notas oficiales</h2><p class="ui-muted">Las notas oficiales se conservan por gestión y se presentan separadas de las tareas.</p>
                <div class="overflow-x-auto"><table class="ui-table"><thead><tr><th>Gestión</th><th>Curso</th><th>Asignatura</th><th>Periodo</th><th>Nota</th></tr></thead><tbody>
                @forelse($officialGrades as $grade)<tr><td>{{ $grade->planAsignatura?->gestionAcademica?->ani_gea ?? 'Histórico sin contexto' }}</td><td>{{ $grade->planAsignatura?->curso?->nom_cur ?? 'Sin datos' }}</td><td>{{ $grade->asignatura?->nom_asi }}</td><td>{{ $grade->periodoEvaluacion?->nom_pev }}</td><td>{{ $grade->not_cal }}</td></tr>@empty<tr><td colspan="5">Aún no tienes notas oficiales registradas.</td></tr>@endforelse
                </tbody></table></div>{{ $officialGrades->links() }}
            </section>
            @endif
        @elseif($area === 'fuentes')
            @can('Orientacion_Academica_Profesional')
                <section class="ui-panel"><h2 class="ui-title text-xl font-bold">Fuentes para explorar carreras</h2><a class="ui-btn-primary mt-4" href="{{ route('aula-virtual.estudiante.orientacion.aporte', ['section'=>'fuentes']) }}">Consultar fuentes de orientación</a></section>
            @endcan
            <livewire:shared.academic-sources />
            <section class="ui-panel"><h2 class="ui-title text-xl font-black">Materiales publicados en mis materias</h2>
                @forelse($materials ?? [] as $material)<p class="ui-muted mt-3">{{ $material->nom_mat }} · <a class="underline" href="{{ route('aula-virtual.estudiante.curso', $material->cod_cla) }}">Abrir materia</a></p>@empty<p class="ui-muted mt-3">Todavía no hay materiales publicados disponibles.</p>@endforelse
                @if($materials){{ $materials->links() }}@endif
            </section>
        @else
            <section class="ui-panel text-center">
                <h2 class="ui-title text-xl font-black">Aún no hay información suficiente</h2>
                <p class="ui-muted mx-auto mt-3 max-w-2xl">Este espacio ya está preparado para presentar información académica real cuando existan datos o cuando se conecte el aporte ingenieril autorizado.</p>
                <a href="{{ route('aula-virtual.estudiante.asignaturas') }}" class="ui-btn-primary mt-5 inline-flex">Volver a mis materias</a>
            </section>
        @endif
    </div>
@endsection
