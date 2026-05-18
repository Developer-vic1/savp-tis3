@extends('layouts.app')

@section('title', 'Gestión de Turnos')

@section('content')
    <div class="space-y-6">
        <section id="modulo-gestion-turnos">
            @livewire('admin.gestion-turnos')
        </section>
    </div>
@endsection