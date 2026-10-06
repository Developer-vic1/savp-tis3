@extends('layouts.app')

@section('title', 'Gestión de Cursos')

@section('content')
    <div class="space-y-6">
        @if(app(\App\Services\RoleDashboardResolver::class)->roleFor(auth()->user())==='Docente')
            <div class="ui-card-soft p-4 flex items-center gap-3" role="status"><i class="ph-duotone ph-shield-check" aria-hidden="true"></i><span><strong>Gestión de cursos autorizada</strong><br><span class="ui-muted text-sm">Conservas tu rol Docente. Los cambios requieren su motivo, respaldo y validación institucional.</span></span></div>
        @endif
        {{-- COMPONENTE LIVEWIRE --}}
        <section id="modulo-gestion-cursos">
            @livewire('admin.gestion-curso')
        </section>

    </div>
@endsection
