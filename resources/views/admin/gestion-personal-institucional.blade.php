@extends('layouts.app')

@section('title', 'Gestión de docentes')

@section('content')
    <div class="space-y-6">

        {{-- ENCABEZADO --}}
        <section class="ui-card rounded-[2rem] p-6 sm:p-7">
            <div class="max-w-4xl">
                {{-- Breadcrumb --}}
                <div class="inline-flex items-center gap-2 rounded-full px-3 py-1 ring-1"
                    style="background: var(--ui-primary-soft); color: var(--ui-primary); --tw-ring-color: var(--ui-primary-border);">

                    <a href="{{ route('dashboard') }}"
                        class="text-xs font-semibold uppercase tracking-[0.18em] transition hover:underline">
                        Inicio
                    </a>

                    <svg class="h-3.5 w-3.5 opacity-70" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m9 18 6-6-6-6" />
                    </svg>

                    <span class="text-xs font-semibold uppercase tracking-[0.18em]">
                        Personal institucional
                    </span>
                </div>

                {{-- Título --}}
                <h1 class="ui-title mt-3 text-4xl font-black tracking-tight">
                    Gestión de Docentes
                </h1>

                {{-- Descripción --}}
                <p class="ui-muted mt-2 max-w-3xl text-sm leading-6">
                    Administra la información profesional del personal docente, sus especialidades técnicas,
                    asignaciones académicas y carga horaria institucional.
                </p>
            </div>
        </section>

        @livewire('admin.personal-institucional')
    </div>
@endsection