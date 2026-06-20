@extends('aula-virtual.layouts.app')

@section('title', 'Materiales | SAVP-TIS3')
@section('page-title', 'Materiales del curso')

@section('content')
    <div class="space-y-6">
        <section class="ui-panel">
            <h2 class="ui-title text-2xl font-black">{{ $curso->nom_cla }}</h2>
            <p class="ui-subtitle mt-2 text-sm">Recursos publicados, programados u ocultos según permisos del curso.</p>
        </section>

        <section class="grid gap-4">
            @forelse ($materiales as $material)
                <article class="ui-panel flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                    <div>
                        <p class="ui-title font-black">{{ $material->nom_mat }}</p>
                        <p class="ui-muted text-sm">{{ ucfirst(strtolower($material->tip_mat)) }}</p>
                    </div>
                    <div class="flex flex-wrap gap-2">
                        @include('aula-virtual.componentes.status-badge', ['estado' => $material->est_mat === 'ACTIVO' ? 'Publicado' : 'Oculto'])
                        @if ($material->rut_mat)
                            @include('aula-virtual.componentes.icon-action-button', ['href' => route('aula-virtual.materiales.descargar', $material->cod_mat), 'icon' => 'descargar', 'label' => 'Descargar', 'variant' => 'secondary'])
                        @endif
                    </div>
                </article>
            @empty
                @include('aula-virtual.componentes.empty-state', ['titulo' => 'Materiales recientes.', 'descripcion' => 'Los materiales publicados para este curso aparecerán aquí.'])
            @endforelse
        </section>
    </div>
@endsection
