@extends('layouts.app')

@section('title', 'Gestión de Cursos')

@section('content')
    <div class="space-y-6">
        {{-- COMPONENTE LIVEWIRE --}}
        <section id="modulo-gestion-asignaturas">
            @livewire('admin.gestion-asignatura')
        </section>
    </div>
@endsection