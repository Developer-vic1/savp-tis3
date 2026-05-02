@extends('layouts.app')

@section('title', 'Bitácora de Actividades')

@section('content')
    <div class="space-y-6">
        <div class="flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between">
            <div>
                <div class="flex flex-wrap items-center gap-2 text-sm font-semibold">
                    <a href="{{ route('dashboard') }}"
                        class="text-[var(--ui-muted)] transition hover:text-[var(--ui-primary)]">
                        Inicio
                    </a>

                    <span class="text-[var(--ui-muted)]">/</span>

                    <span class="text-[var(--ui-primary)]">
                        Bitácora
                    </span>
                </div>

                <h1 class="mt-2 text-3xl font-black tracking-tight text-[var(--ui-text)] sm:text-4xl">
                    Bitácora de Actividades
                </h1>

                <p class="mt-2 max-w-3xl text-sm leading-7 text-[var(--ui-muted)]">
                    Registra, consulta y analiza todas las actividades y eventos que suceden dentro del sistema.
                </p>
            </div>
        </div>

        @livewire('admin.bitacora')
    </div>
@endsection