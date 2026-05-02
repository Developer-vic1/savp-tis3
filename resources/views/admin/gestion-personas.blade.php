@extends('layouts.app')

@section('title', 'Gestión de personas')

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

                        <span class="text-xs font-semibold uppercase tracking-[0.18em] opacity-70">Personas</span>
                    </div>

                    <h1 class="ui-title mt-3 text-4xl font-black tracking-tight">
                        Gestión de Personas
                    </h1>

                    <p class="ui-muted mt-2 max-w-2xl text-sm leading-6">
                        Administra el registro general de personas vinculadas al sistema institucional.
                    </p>
                </div>

                <div class="hidden rounded-2xl px-4 py-3 ring-1 lg:block"
                    style="background: var(--ui-surface-soft); color: var(--ui-muted); --tw-ring-color: var(--ui-border);">
                    <div class="flex items-center gap-3">
                        <svg class="h-5 w-5" style="color: var(--ui-primary);" fill="none" stroke="currentColor"
                            viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"
                                d="M15 9h3.75M15 12h3.75M15 15h3.75M4.5 19.5h15A1.5 1.5 0 0 0 21 18V6a1.5 1.5 0 0 0-1.5-1.5h-15A1.5 1.5 0 0 0 3 6v12a1.5 1.5 0 0 0 1.5 1.5Zm6-10.125a2.625 2.625 0 1 1-5.25 0 2.625 2.625 0 0 1 5.25 0ZM12 17.25c-.9-1.285-2.395-2.25-4.125-2.25S4.65 15.965 3.75 17.25" />
                        </svg>

                        <div>
                            <p class="text-xs font-semibold uppercase tracking-[0.14em]">
                                Módulo activo
                            </p>
                            <p class="text-sm font-bold" style="color: var(--ui-text);">
                                Registro personal
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        @livewire('admin.gestion-personas')
    </div>
@endsection