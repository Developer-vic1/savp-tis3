@extends('layouts.app')

@section('title', 'Inicio '.$actor)

@section('content')
    @php
        $labels = [
            'estudiantes_activos' => 'Estudiantes activos',
            'cursos_activos' => 'Cursos activos',
            'inscripciones_activas' => 'Inscripciones activas',
            'docentes_activos' => 'Docentes activos',
            'calificaciones_registradas' => 'Calificaciones registradas',
            'personas_registradas' => 'Personas registradas',
            'cuentas_activas' => 'Cuentas activas',
        ];
    @endphp

    <div class="space-y-6">
        <section class="ui-card card-shadow rounded-[2rem] p-6 sm:p-8">
            <p class="text-sm font-semibold uppercase tracking-[0.18em]" style="color: var(--ui-primary);">
                Espacio de trabajo · {{ $actor }}
            </p>
            <h1 class="ui-title mt-3 text-3xl font-black">Supervisión y trabajo institucional</h1>
            <p class="ui-muted mt-3 max-w-3xl">
                Información real con el alcance correspondiente a tu rol. Las acciones no autorizadas no están disponibles en este espacio.
            </p>
            <p class="ui-muted mt-2 text-sm">Gestión activa: {{ $gestion ?: 'Aún no definida' }}</p>
            @if ($actor === 'Regente')
                <p class="ui-alert-warning mt-4" role="status">Las consultas se limitan a los grados asignados por Administración en cada gestión. Si no hay asignaciones activas, las consultas no mostrarán registros.</p>
            @endif
        </section>

        <section aria-label="Indicadores institucionales" class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
            @foreach ($metrics as $key => $value)
                <article class="ui-card card-shadow rounded-3xl p-5">
                    <p class="text-sm font-medium" style="color: var(--ui-muted);">{{ $labels[$key] ?? str($key)->replace('_', ' ')->title() }}</p>
                    <p class="mt-2 text-3xl font-black" style="color: var(--ui-text);">{{ $value ?? 'Sin datos' }}</p>
                </article>
            @endforeach
        </section>

        <section class="ui-card card-shadow rounded-[2rem] p-6 sm:p-8">
            <h2 class="ui-title text-xl font-black">Áreas de trabajo</h2>
            <div class="mt-5 grid gap-4 md:grid-cols-2 xl:grid-cols-3">
                @foreach ($modules as $module)
                    <a href="{{ route($module['route'], $module['params']) }}" class="ui-card-soft scroll-mt-28 rounded-2xl p-5 transition hover:bg-[var(--ui-primary-soft)]">
                        <h3 class="font-bold" style="color: var(--ui-text);">{{ $module['label'] }}</h3>
                        <p class="ui-muted mt-2 text-sm leading-6">{{ $module['group'] }}</p>
                    </a>
                @endforeach
            </div>
        </section>
    </div>
@endsection
