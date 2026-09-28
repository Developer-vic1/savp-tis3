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
        <form class="ui-panel space-y-4" method="POST" action="{{ route('docente.cursos.calificaciones.store', $course->cod_cla) }}">@csrf
            <h2 class="ui-title text-xl font-bold">Registrar nota oficial</h2>
            <div class="grid gap-4 md:grid-cols-2">
                <label class="ui-label">Estudiante inscrito<select name="cod_est" class="ui-select" required><option value="">Seleccionar estudiante</option>@foreach($students as $student)<option value="{{ $student->cod_est }}" @selected(old('cod_est') === $student->cod_est)>{{ $student->persona?->nom_per }} {{ $student->persona?->ape_pat_per }} · {{ $student->cod_est }}</option>@endforeach</select></label>
                <label class="ui-label">Periodo<select name="cod_pev" class="ui-select" required><option value="">Seleccionar periodo</option>@foreach($periods as $period)<option value="{{ $period->cod_pev }}" @selected(old('cod_pev') === $period->cod_pev)>{{ $period->nom_pev }}</option>@endforeach</select></label>
                <label class="ui-label">Nota sobre 100<input name="not_cal" type="number" min="0" max="100" step="0.01" class="ui-input" value="{{ old('not_cal') }}" required></label>
                <label class="ui-label">Observación<input name="obs_cal" maxlength="255" class="ui-input" value="{{ old('obs_cal') }}"></label>
            </div>
            <button class="ui-btn-primary" type="submit">Registrar nota</button>
        </form>
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
                        @else Solo lectura @endcan</td>
                    </tr>
                @empty<tr><td colspan="5" class="ui-muted">No hay notas oficiales registradas para esta asignación.</td></tr>@endforelse</tbody>
            </table>
        </div>
        {{ $grades->links() }}
    @endif
</div>
@endsection
