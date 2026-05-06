@extends('layouts.app')

@section('title', 'Gestión de Cursos')

@section('content')
    <div class="space-y-6">
        {{-- COMPONENTE LIVEWIRE --}}
        <section id="modulo-gestion-cursos">
            @livewire('admin.gestion-curso')
        </section>

    </div>
@endsection