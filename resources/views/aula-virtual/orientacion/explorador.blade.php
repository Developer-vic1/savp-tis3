@extends('aula-virtual.layouts.app')

@section('title', 'Explorador académico-vocacional | SAVP-TIS3')
@section('page-title', 'Explorador académico-vocacional')

@section('content')
    <section class="ui-panel">
        <h2 class="ui-title text-2xl font-black">Preguntas por pasos</h2>
        <p class="ui-subtitle mt-3 text-sm leading-7">Selecciona una valoración de 1 a 5 para cada dimensión. El avance se guardará como parte del seguimiento académico cuando el registro esté habilitado.</p>

        <div class="mt-6 space-y-5">
            @foreach ($dimensiones as $key => $dimension)
                <div class="rounded-lg border p-4" style="border-color: var(--ui-border);">
                    <label class="ui-label">{{ $dimension }}</label>
                    <input type="range" min="1" max="5" value="3" class="w-full accent-[var(--savp-green)]">
                </div>
            @endforeach
        </div>

        <div class="mt-6 flex flex-wrap gap-2">
            @include('aula-virtual.componentes.icon-action-button', ['icon' => 'guardar', 'label' => 'Guardar avance'])
            @include('aula-virtual.componentes.icon-action-button', ['href' => route('aula-virtual.estudiante.orientacion.resultados'), 'icon' => 'enviar', 'label' => 'Finalizar'])
        </div>
    </section>
@endsection
