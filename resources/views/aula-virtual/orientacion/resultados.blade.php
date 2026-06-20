@extends('aula-virtual.layouts.app')

@section('title', 'Resultados de orientación | SAVP-TIS3')
@section('page-title', 'Resultados de orientación')

@section('content')
    <div class="space-y-6">
        <section class="ui-panel">
            <h2 class="ui-title text-2xl font-black">Carreras sugeridas</h2>
            <p class="ui-subtitle mt-3 text-sm leading-7">{{ $resumen['mensaje'] }}</p>
        </section>

        @include('aula-virtual.componentes.empty-state', [
            'titulo' => 'Carreras sugeridas.',
            'descripcion' => 'Las sugerencias aparecerán al finalizar el explorador y se complementarán con rendimiento académico y acompañamiento docente.',
        ])
    </div>
@endsection
