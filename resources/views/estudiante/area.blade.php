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
            <livewire:shared.academic-sources />
            <section class="ui-panel"><h2 class="ui-title text-xl font-black">Materiales publicados en mis materias</h2>
                @forelse($materials ?? [] as $material)<p class="ui-muted mt-3">{{ $material->nom_mat }} · <a class="underline" href="{{ route('aula-virtual.estudiante.curso', $material->cod_cla) }}">Abrir materia</a></p>@empty<p class="ui-muted mt-3">Todavía no hay materiales publicados disponibles.</p>@endforelse
                @if($materials){{ $materials->links() }}@endif
            </section>
        @elseif($area === 'preparacion')
            <section class="ui-panel"><h2 class="ui-title text-xl font-black">Actividades pendientes</h2>
                @forelse($academic['pendientes'] as $task)<p class="ui-muted mt-3">{{ $task->tit_tar }} · {{ $task->fec_lim_tar?->format('d/m/Y H:i') ?? 'Sin fecha límite' }}</p>@empty<p class="ui-muted mt-3">No hay actividades pendientes registradas en tus materias.</p>@endforelse
            </section>
        @elseif($area === 'asistente')
            <livewire:shared.study-assistant />
        @elseif($area === 'futuro')
            <section class="ui-panel"><h2 class="ui-title text-xl font-bold">Opciones registradas para explorar</h2>
                <p class="ui-muted mt-2">Las opciones son orientativas y requieren acompañamiento. No determinan qué debes estudiar.</p>
                @forelse($orientation?->carreras ?? [] as $career)
                    <article class="ui-card-soft mt-4 p-4"><h3 class="font-bold">Podrías explorar {{ $career->carrera }}</h3><p class="ui-muted mt-2">{{ $career->razon }}</p></article>
                @empty
                    <p class="ui-muted mt-4">Aún no hay opciones académicas verificadas para mostrar.</p>
                @endforelse
                <a class="ui-btn-secondary mt-4" href="{{ route('estudiante.intereses') }}">Revisar mis intereses</a>
            </section>
        @elseif($area === 'plan')
            <livewire:shared.academic-plan />
            <a class="ui-btn-secondary" href="{{ route('estudiante.area', ['area' => 'preparacion']) }}">Ver actividades pendientes</a>
        @else
            <section class="ui-panel text-center">
                <h2 class="ui-title text-xl font-black">Aún no hay información suficiente</h2>
                <p class="ui-muted mx-auto mt-3 max-w-2xl">Este espacio ya está preparado para presentar información académica real cuando existan datos o cuando se conecte el aporte ingenieril autorizado.</p>
                <a href="{{ route('aula-virtual.estudiante.asignaturas') }}" class="ui-btn-primary mt-5 inline-flex">Volver a mis materias</a>
            </section>
        @endif
    </div>
@endsection
