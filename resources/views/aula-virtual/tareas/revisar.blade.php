@extends('aula-virtual.layouts.app')

@section('title', 'Revisar entregas | SAVP-TIS3')
@section('page-title', 'Revisar entregas')

@section('content')
    <div class="space-y-6">
        <section class="ui-panel">
            <p class="ui-kicker">Tarea</p>
            <h2 class="ui-title mt-2 text-2xl font-black">{{ $tarea->tit_tar }}</h2>
            <p class="ui-subtitle mt-2 text-sm">Lista de estudiantes, estado de entrega, archivo, puntaje y retroalimentación.</p>
        </section>

        <section class="space-y-4">
            @forelse ($tarea->entregas as $entrega)
                @php
                    $persona = $entrega->estudiante?->persona;
                    $nombre = trim(($persona->nom_per ?? '') . ' ' . ($persona->ape_pat_per ?? '') . ' ' . ($persona->ape_mat_per ?? ''));
                    $estado = match ($entrega->est_ent) {
                        'ENTREGADO' => 'Entregado',
                        'ENTREGADO_TARDE' => 'Tardío',
                        'CALIFICADO' => 'Revisado',
                        'DEVUELTO' => 'Devuelto',
                        default => 'Pendiente',
                    };
                @endphp
                <article class="ui-panel">
                    <div class="flex flex-col gap-3 lg:flex-row lg:items-start lg:justify-between">
                        <div>
                            <p class="ui-title text-lg font-black">{{ $nombre ?: 'Estudiante registrado' }}</p>
                            <p class="ui-muted mt-1 text-sm">{{ optional($entrega->fec_ent)->format('d/m/Y H:i') ?: 'Entrega en proceso' }}</p>
                            @include('aula-virtual.componentes.status-badge', ['estado' => $estado])
                        </div>
                        <div class="flex flex-wrap gap-2">
                            @foreach ($entrega->archivos as $archivo)
                                @include('aula-virtual.componentes.icon-action-button', ['href' => route('aula-virtual.entregas.archivos.descargar', $archivo->cod_ent_arc), 'icon' => 'descargar', 'label' => 'Descargar', 'variant' => 'secondary'])
                            @endforeach
                        </div>
                    </div>
                    <form method="POST" action="{{ route('aula-virtual.docente.entregas.calificar', $entrega->cod_ent) }}" class="mt-4 grid gap-3 lg:grid-cols-[160px_1fr_auto]">
                        @csrf
                        <input name="pun_obt" type="number" min="0" max="{{ $tarea->pun_max_tar }}" step="0.01" value="{{ $entrega->calificacion?->pun_obt }}" class="ui-input" placeholder="Puntaje">
                        <input name="com_cal" class="ui-input" value="{{ $entrega->calificacion?->com_cal }}" placeholder="Retroalimentación">
                        @include('aula-virtual.componentes.icon-action-button', ['type' => 'submit', 'icon' => 'calificar', 'label' => 'Calificar'])
                    </form>
                </article>
            @empty
                @include('aula-virtual.componentes.empty-state', ['titulo' => 'Entregas por revisar.', 'descripcion' => 'Las entregas enviadas por estudiantes aparecerán en esta bandeja.'])
            @endforelse
        </section>
    </div>
@endsection
