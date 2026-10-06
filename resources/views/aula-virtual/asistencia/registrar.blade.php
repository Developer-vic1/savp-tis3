@extends('aula-virtual.layouts.app')

@section('title', 'Registrar asistencia | SAVP-TIS3')
@section('page-title', 'Registrar asistencia')

@section('content')
    <form method="POST" action="{{ route('aula-virtual.docente.asistencia.guardar', $curso->cod_cla) }}" class="space-y-6">
        @csrf
        @if($errors->any())<div role="alert" class="ui-alert-danger">@foreach($errors->all() as $error)<p>{{ $error }}</p>@endforeach</div>@endif
        @if($bloques->isEmpty())<p role="status" class="ui-alert-warning">Este plan no tiene detalles de horario oficiales. La asistencia requiere un día y bloque registrado.</p>@endif
        <section class="ui-panel">
            <div class="grid gap-4 lg:grid-cols-3">
                <div>
                    <label class="ui-label">Curso</label>
                    <div class="ui-field-readonly">{{ $curso->nom_cla }}</div>
                </div>
                <div>
                    <label class="ui-label">Fecha</label>
                    <input type="date" name="fec_asi_cla" value="{{ old('fec_asi_cla', $fechaAsistencia) }}" class="ui-input" required>
                </div>
                <div>
                    <label class="ui-label">Trimestre</label>
                    <select class="ui-select"><option>Trimestre actual</option></select>
                </div>
            </div>
        </section>

        <section class="ui-panel">
            <div class="mb-4 flex items-center justify-between gap-3">
                <h2 class="ui-title text-xl font-black">Lista de estudiantes</h2>
                @include('aula-virtual.componentes.icon-action-button', ['type' => 'submit', 'icon' => 'guardar', 'label' => 'Guardar asistencia'])
            </div>

            <div class="space-y-3">
                @forelse ($curso->estudiantes as $inscrito)
                    @php
                        $persona = $inscrito->estudiante?->persona;
                        $nombre = trim(($persona->nom_per ?? '') . ' ' . ($persona->ape_pat_per ?? '') . ' ' . ($persona->ape_mat_per ?? ''));
                    @endphp
                    <article class="grid gap-3 rounded-lg border p-4 lg:grid-cols-[1fr_220px_1fr]" style="border-color: var(--ui-border);">
                        <p class="font-black">{{ $nombre ?: 'Estudiante registrado' }}</p>
                        <select name="asistencias[{{ $inscrito->cod_est }}][cod_est_asi]" class="ui-select">
                            @foreach ($estados as $estado)
                                <option value="{{ $estado->cod_est_asi }}">{{ $estado->nom_est_asi }}</option>
                            @endforeach
                        </select>
                        <input type="number" name="asistencias[{{ $inscrito->cod_est }}][min_retraso]" min="0" max="300" value="0" class="ui-input" aria-label="Minutos de retraso">
                        <input name="asistencias[{{ $inscrito->cod_est }}][obs_asi_est]" class="ui-input" placeholder="Observación rápida">
                    </article>
                @empty
                    @include('aula-virtual.componentes.empty-state', ['titulo' => 'Estudiantes asignados.', 'descripcion' => 'Los estudiantes inscritos al curso aparecerán para registrar asistencia.'])
                @endforelse
            </div>
        </section>
    </form>
@endsection
