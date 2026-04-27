@extends('layouts.app')

@section('title', 'Gestión de usuarios')

@section('content')
    <div class="space-y-6">
        <div>
            <p class="text-sm text-slate-500">Inicio / Usuarios</p>
            <h1 class="text-4xl font-black text-slate-950">Gestión de Usuarios</h1>
        </div>

        @livewire('admin.gestion-usuarios')
    </div>
@endsection