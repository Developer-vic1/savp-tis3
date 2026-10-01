@extends('aula-virtual.layouts.app')

@section('title', 'Revisar entregas | SAVP-TIS3')
@section('page-title', 'Revisar entregas')

@section('content')
    <div class="space-y-6">
        <section class="ui-panel">
            <p class="ui-kicker">Tarea</p>
            <h2 class="ui-title mt-2 text-2xl font-black">{{ $tarea->tit_tar }}</h2>
            <p class="ui-subtitle mt-2 text-sm">Lista de estudiantes, estado de entrega, archivo, puntaje y retroalimentación.</p>
            <a class="ui-btn-secondary mt-3" href="{{ route('aula-virtual.docente.curso', ['curso' => $tarea->cod_cla, 'tab' => 'entregas']) }}">Volver al curso</a>
            <form method="GET" class="mt-4 grid gap-3 sm:grid-cols-3">
                <label class="ui-label">Estudiante<input name="search" value="{{ $filters['search'] ?? '' }}" maxlength="100" class="ui-input" autocomplete="off"></label>
                <label class="ui-label">Estado<select name="state" class="ui-input"><option value="">Todos</option>@foreach(['PENDIENTE','ENTREGADO','ENTREGADO_TARDE','CALIFICADO','DEVUELTO','ANULADO'] as $state)<option value="{{ $state }}" @selected(($filters['state'] ?? '') === $state)>{{ str_replace('_', ' ', $state) }}</option>@endforeach</select></label>
                <button class="ui-btn-primary self-end" type="submit">Filtrar</button>
            </form>
        </section>

        <section class="space-y-4">
            @forelse ($entregas as $entrega)
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
                    @if($entrega->tex_ent)<p class="ui-subtitle mt-3 whitespace-pre-wrap">{{ $entrega->tex_ent }}</p>@endif
                    @if($entrega->obs_ent)<p class="ui-muted mt-2">Devolución: {{ $entrega->obs_ent }}</p>@endif
                    @if($entrega->calificacion)<p class="ui-subtitle mt-2">Puntaje registrado: {{ $entrega->calificacion->pun_obt }} / {{ $tarea->pun_max_tar }}. {{ $entrega->calificacion->com_cal }}</p>@endif
                    @can('grade', $entrega)
                    @if(in_array($entrega->est_ent, ['ENTREGADO', 'ENTREGADO_TARDE', 'CALIFICADO'], true))
                    <form method="POST" action="{{ route('aula-virtual.docente.entregas.calificar', $entrega->cod_ent) }}" data-confirm="{{ $entrega->calificacion ? 'Se rectificará el puntaje y se conservará la trazabilidad del cambio. Indica el motivo.' : 'Se registrará la calificación de esta entrega.' }}" class="mt-4 grid gap-3 lg:grid-cols-[160px_1fr_auto]">
                        @csrf
                        <label class="ui-label">Puntaje<input name="pun_obt" type="number" required min="0" max="{{ $tarea->pun_max_tar }}" step="0.01" value="{{ $entrega->calificacion?->pun_obt }}" class="ui-input"></label>
                        <label class="ui-label">{{ $entrega->calificacion ? 'Motivo de rectificación' : 'Retroalimentación' }}<textarea name="com_cal" maxlength="2000" @required((bool)$entrega->calificacion) class="ui-input">{{ $entrega->calificacion?->com_cal }}</textarea></label>
                        <div class="self-end">@include('aula-virtual.componentes.icon-action-button', ['type' => 'submit', 'icon' => 'calificar', 'label' => $entrega->calificacion ? 'Rectificar' : 'Calificar'])</div>
                    </form>
                    @endif
                    @endcan
                    @can('returnForCorrection', $entrega)
                    @if(in_array($entrega->est_ent, ['ENTREGADO', 'ENTREGADO_TARDE'], true))
                    <form method="POST" action="{{ route('aula-virtual.docente.entregas.devolver', $entrega->cod_ent) }}" class="mt-4 flex flex-col gap-3 sm:flex-row sm:items-end" data-confirm="El estudiante podrá corregir y reenviar esta entrega.">
                        @csrf
                        <label class="ui-label flex-1">Motivo de devolución<textarea name="obs_ent" required maxlength="2000" class="ui-input"></textarea></label>
                        <button type="submit" class="ui-btn-secondary">Devolver para corrección</button>
                    </form>
                    @endif
                    @endcan
                </article>
            @empty
                @include('aula-virtual.componentes.empty-state', ['titulo' => 'Entregas por revisar.', 'descripcion' => 'Las entregas enviadas por estudiantes aparecerán en esta bandeja.'])
            @endforelse
            {{ $entregas->links() }}
        </section>
    </div>
@endsection
