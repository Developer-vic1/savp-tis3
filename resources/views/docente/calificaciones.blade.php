@extends('aula-virtual.layouts.app')
@section('title', 'Calificaciones oficiales del curso')
@section('content')
<div class="space-y-6">
    <section class="ui-panel">
        <a class="ui-muted underline" href="{{ route('aula-virtual.docente.curso', $course->cod_cla) }}">Volver al curso</a>
        <h1 class="ui-title mt-3 text-3xl font-black">{{ $course->nom_cla }}</h1>
        <p class="ui-muted mt-2">Notas oficiales por periodo · Gestión {{ $course->planAsignatura?->gestionAcademica?->ani_gea }}. Las notas de tareas permanecen separadas.</p>
    </section>
    @if(session('status'))<p role="status" class="ui-alert-success">{{ session('status') }}</p>@endif
    @if($errors->any())<div role="alert" class="ui-alert-danger">@foreach($errors->all() as $error)<p>{{ $error }}</p>@endforeach</div>@endif
    @if(!$ready)
        <p class="ui-alert-warning">El historial por gestión está pendiente de aplicación autorizada. Las notas antiguas se conservan sin atribuirles una gestión automáticamente.</p>
    @else
        @can('calificaciones.gestionar.curso')
        <livewire:shared.teacher-grade-form :curso="$course->cod_cla" />
        @endcan
        <div class="ui-card overflow-x-auto" tabindex="0" aria-label="Notas oficiales">
            <table class="ui-table text-sm">
                <thead><tr>@foreach(['Estudiante', 'Periodo', 'Nota', 'Observación', 'Acción'] as $label)<th scope="col">{{ $label }}</th>@endforeach</tr></thead>
                <tbody>@forelse($grades as $grade)
                    <tr>
                        <td>{{ $grade->estudiante?->persona?->nom_per }} {{ $grade->estudiante?->persona?->ape_pat_per }}</td>
                        <td>{{ $grade->periodoEvaluacion?->nom_pev }}</td>
                        <td><input aria-label="Nota de {{ $grade->cod_est }}" form="grade-{{ $grade->cod_cal }}" name="not_cal" value="{{ $grade->not_cal }}" type="number" min="0" max="100" step="0.01" required class="ui-input w-28"></td>
                        <td><input aria-label="Observación de {{ $grade->cod_est }}" form="grade-{{ $grade->cod_cal }}" name="obs_cal" value="{{ $grade->obs_cal }}" maxlength="255" class="ui-input min-w-48"></td>
                        <td>@can('calificaciones.gestionar.curso')
                            <form id="grade-{{ $grade->cod_cal }}" method="POST" action="{{ route('docente.cursos.calificaciones.update', [$course->cod_cla, $grade]) }}">@csrf @method('PUT')
                                <button class="ui-btn-secondary">Guardar</button>
                            </form>
                            @if($grade->est_cal === 'ACTIVO')<button type="button" class="ui-btn-secondary mt-2" x-data @click="$dispatch('review-official-grade', {id: @js($grade->cod_cal)}); window.scrollTo({top:0,behavior:'smooth'})">Revisar con asistencia</button>@endif
                        @else Solo lectura @endcan</td>
                    </tr>
                @empty<tr><td colspan="5" class="ui-muted">No hay notas oficiales registradas para esta asignación.</td></tr>@endforelse</tbody>
            </table>
        </div>
        {{ $grades->links() }}
    @endif
</div>
@endsection
