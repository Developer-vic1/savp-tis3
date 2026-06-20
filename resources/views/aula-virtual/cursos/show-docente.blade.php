@extends('aula-virtual.layouts.app')

@section('title', 'Curso docente | SAVP-TIS3')
@section('page-title', $curso->planAsignatura?->asignatura?->nom_asi ?? $curso->nom_cla)

@section('content')
    <div class="space-y-6">
        <section class="ui-panel">
            <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
                <div>
                    <p class="ui-kicker">Gestión del curso</p>
                    <h2 class="ui-title mt-1 text-2xl font-black">{{ $curso->nom_cla }}</h2>
                    <p class="ui-subtitle mt-2 text-sm">Participantes, materiales, tareas, asistencia, calificaciones y reportes.</p>
                </div>
                <div class="flex flex-wrap gap-2">
                    @include('aula-virtual.componentes.icon-action-button', ['href' => route('aula-virtual.docente.asistencia.registrar', $curso->cod_cla), 'icon' => 'asistencia', 'label' => 'Asistencia', 'variant' => 'secondary'])
                    @include('aula-virtual.componentes.icon-action-button', ['href' => route('aula-virtual.materiales.index', $curso->cod_cla), 'icon' => 'crear-material', 'label' => 'Materiales'])
                </div>
            </div>
        </section>

        @include('aula-virtual.componentes.trimester-tabs')

        <section class="grid gap-6 xl:grid-cols-2">
            <div class="ui-panel">
                <h3 class="ui-title text-xl font-black">Crear material</h3>
                <form method="POST" action="{{ route('aula-virtual.docente.materiales.store', $curso->cod_cla) }}" enctype="multipart/form-data" class="mt-4 space-y-4">
                    @csrf
                    <input name="nom_mat" class="ui-input" placeholder="Nombre del material" required>
                    <select name="tip_mat" class="ui-select" required>
                        @foreach (['PDF','DOCUMENTO','PRESENTACION','IMAGEN','ENLACE','VIDEO','OTRO'] as $tipo)
                            <option value="{{ $tipo === 'PRESENTACION' ? 'DOCUMENTO' : $tipo }}">{{ ucfirst(strtolower($tipo)) }}</option>
                        @endforeach
                    </select>
                    <input name="url_mat" class="ui-input" placeholder="Enlace del recurso">
                    @include('aula-virtual.componentes.file-upload-box', ['name' => 'archivo'])
                    @include('aula-virtual.componentes.icon-action-button', ['type' => 'submit', 'icon' => 'guardar', 'label' => 'Guardar'])
                </form>
            </div>

            <div class="ui-panel">
                <h3 class="ui-title text-xl font-black">Crear tarea</h3>
                <form method="POST" action="{{ route('aula-virtual.docente.tareas.store', $curso->cod_cla) }}" class="mt-4 space-y-4">
                    @csrf
                    <input name="tit_tar" class="ui-input" placeholder="Título de la tarea" required>
                    <textarea name="des_tar" class="ui-textarea" rows="4" placeholder="Instrucciones"></textarea>
                    <div class="grid gap-3 sm:grid-cols-2">
                        <select name="tip_tar" class="ui-select"><option value="TAREA">Tarea</option><option value="PRACTICA">Práctica</option><option value="PROYECTO">Proyecto</option></select>
                        <input name="pun_max_tar" type="number" min="1" max="1000" value="100" class="ui-input" required>
                    </div>
                    <input name="fec_lim_tar" type="datetime-local" class="ui-input">
                    <label class="flex items-center gap-2 text-sm font-bold"><input type="checkbox" name="perm_ent_tardia" value="1"> Permitir entregas tardías</label>
                    <select name="est_tar" class="ui-select"><option value="BORRADOR">Programado</option><option value="PUBLICADA">Publicado</option></select>
                    @include('aula-virtual.componentes.icon-action-button', ['type' => 'submit', 'icon' => 'crear-tarea', 'label' => 'Guardar'])
                </form>
            </div>
        </section>

        <section class="ui-panel">
            <h3 class="ui-title text-xl font-black">Tareas</h3>
            <div class="mt-4 space-y-3">
                @forelse ($curso->tareas as $tarea)
                    <article class="flex flex-col gap-3 rounded-lg border p-4 sm:flex-row sm:items-center sm:justify-between" style="border-color: var(--ui-border);">
                        <div>
                            <p class="font-black">{{ $tarea->tit_tar }}</p>
                            <p class="ui-muted text-sm">{{ ucfirst(strtolower($tarea->tip_tar)) }}</p>
                        </div>
                        @include('aula-virtual.componentes.icon-action-button', ['href' => route('aula-virtual.docente.tareas.revisar', $tarea->cod_tar), 'icon' => 'revisar', 'label' => 'Revisar', 'variant' => 'secondary'])
                    </article>
                @empty
                    @include('aula-virtual.componentes.empty-state', ['titulo' => 'Tareas activas.', 'descripcion' => 'Las tareas creadas para este curso aparecerán aquí.'])
                @endforelse
            </div>
        </section>
    </div>
@endsection
