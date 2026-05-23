@extends('layouts.app')

@section('title', 'Gestión de Inscripciones')

@section('content')
    <div class="space-y-6">
        <section id="modulo-gestion-inscripciones">
            @livewire('admin.gestion-inscripciones')
        </section>
    </div>
@endsection