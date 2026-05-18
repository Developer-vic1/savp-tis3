@extends('layouts.app')

@section('title', 'Gestión de Cursos')

@section('content')
    <div class="space-y-6">
        <section id="modulo-gestion-paralelos">
            @livewire('admin.gestion-paralelo')
        </section>
    </div>
@endsection