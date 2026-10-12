@extends('layouts.app')

@section('title', 'Carreras y mallas de UNIFRANZ | SAVP-TIS3')

@section('content')
    <div class="mx-auto max-w-7xl space-y-6">
        <section class="ui-panel">
            <p class="ui-kicker">Universidades · Administración</p>
            <h1 class="ui-title mt-2 text-2xl font-black sm:text-3xl">Colección académica de UNIFRANZ</h1>
            <p class="ui-subtitle mt-3 max-w-4xl">Descubre cada carrera en el índice oficial y conserva sus fichas y mallas. La lectura digital permite revisar el contenido publicado y preparar las fuentes que ampliarán la orientación de nuestros estudiantes.</p>
            <div class="mt-4 flex flex-wrap gap-3">
                <a href="{{ route('conocimiento.directorio-nacional', ['buscar' => 'UNIFRANZ']) }}" class="ui-btn-secondary"><i class="ph-duotone ph-arrow-left" aria-hidden="true"></i> Volver a Universidades</a>
                <a href="{{ route('conocimiento.fuentes.index') }}" class="ui-btn-secondary"><i class="ph-duotone ph-shield-check" aria-hidden="true"></i> Revisar fuentes individuales</a>
            </div>
        </section>
        @include('workspaces.partes.coleccion-universitaria')
    </div>
@endsection
