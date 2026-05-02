@extends('layouts.app')

@section('title', 'Gestión de usuarios')

@section('content')
    <div class="space-y-6">
        {{-- ENCABEZADO --}}
        <section class="ui-card rounded-[2rem] p-6 sm:p-7">
            <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
                <div>
                    <div class="inline-flex items-center gap-2 rounded-full px-3 py-1 ring-1"
                        style="background: var(--ui-primary-soft); color: var(--ui-primary); --tw-ring-color: var(--ui-primary-border);">
                        <a href="{{ route('dashboard') }}"
                            class="text-xs font-semibold uppercase tracking-[0.18em] transition hover:underline">
                            Inicio
                        </a>

                        <svg class="h-3.5 w-3.5 opacity-70" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m9 18 6-6-6-6" />
                        </svg>

                        <span class="text-xs font-semibold uppercase tracking-[0.18em] opacity-70">Usuarios</span>
                    </div>

                    <h1 class="ui-title mt-3 text-4xl font-black tracking-tight">
                        Gestión de Usuarios
                    </h1>

                    <p class="ui-muted mt-2 max-w-2xl text-sm leading-6">
                        Administra el registro general de usuarios del sistema.
                    </p>
                </div>
            </div>
        </section>

        @livewire('admin.gestion-usuarios')
    </div>
@endsection