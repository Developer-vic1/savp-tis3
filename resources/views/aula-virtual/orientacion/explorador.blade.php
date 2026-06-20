@extends('aula-virtual.layouts.app')

@section('title', 'Explorador académico-vocacional | SAVP-TIS3')
@section('page-title', 'Explorador académico-vocacional')

@section('content')
    <section class="ui-panel">
        <h2 class="ui-title text-2xl font-black">Explorador académico-vocacional</h2>
        <p class="ui-subtitle mt-3 text-sm leading-7">Abre el cuestionario institucional de 30 preguntas y guarda tu avance cuando lo necesites.</p>
        <div class="mt-6">
            <livewire:aula-virtual.orientacion.explorador-vocacional />
        </div>
    </section>
@endsection
