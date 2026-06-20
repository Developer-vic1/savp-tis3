@extends('aula-virtual.layouts.app')

@section('title', 'Orientación académica-profesional | SAVP-TIS3')
@section('page-title', 'Orientación académica-profesional')

@section('content')
    <div class="space-y-6">
        <section class="ui-panel">
            <p class="ui-kicker">Explorador académico-vocacional</p>
            <h2 class="ui-title mt-2 text-2xl font-black">Resumen</h2>
            <p class="ui-subtitle mt-3 text-sm leading-7">{{ $resumen['mensaje'] }}</p>
        </section>

        <section class="grid gap-5 md:grid-cols-2 xl:grid-cols-4">
            @include('aula-virtual.componentes.metric-card', ['titulo' => 'Estado', 'valor' => $resumen['estado']])
            @include('aula-virtual.componentes.metric-card', ['titulo' => 'Avance', 'valor' => $resumen['avance'] . '%'])
            @include('aula-virtual.componentes.metric-card', ['titulo' => 'Perfil', 'valor' => 'En proceso'])
            @include('aula-virtual.componentes.metric-card', ['titulo' => 'Compatibilidad', 'valor' => $resumen['compatibilidad_principal'] ?? 'Disponible al finalizar'])
        </section>

        <section class="ui-panel">
            <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <h3 class="ui-title text-xl font-black">Dimensiones de orientación</h3>
                    <p class="ui-subtitle mt-2 text-sm">Resultados visibles del explorador académico-vocacional.</p>
                </div>
                @include('aula-virtual.componentes.icon-action-button', ['href' => route('aula-virtual.estudiante.orientacion.explorador'), 'icon' => 'orientacion', 'label' => 'Explorador'])
            </div>
            <div class="mt-5 grid gap-3 md:grid-cols-2 xl:grid-cols-3">
                @foreach ($dimensiones as $dimension)
                    <div class="rounded-lg border p-4 font-bold" style="border-color: var(--ui-border);">{{ $dimension }}</div>
                @endforeach
            </div>
        </section>
    </div>
@endsection
