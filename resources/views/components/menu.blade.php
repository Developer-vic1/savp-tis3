{{--
SAVP - TIS 3 | Sidebar principal mejorado
Reemplazo completo para:
resources/views/components/menu.blade.php

Características:
- Phosphor Icons Duotone (sin SVG manuales)
- Paleta institucional verde / teal / sky
- Microanimaciones por módulo
- Acordeón: solo un grupo abierto a la vez
- Grupo activo según la ruta actual
- Tooltips animados al contraer el sidebar
- Persistencia del estado abierto/cerrado en localStorage
- Estudiantes solo en Comunidad Educativa
- Calificaciones movidas a Gestión Académica
- Mantiene @can / @canany y las rutas actuales del repositorio
--}}

@once
    {{-- Carga únicamente el peso duotone de Phosphor Icons. --}}
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@phosphor-icons/web@2.1.2/src/duotone/style.css">
@endonce

@php
    $dashboardActive = request()->routeIs('dashboard');

    $adminActive = request()->routeIs([
        'admin.gestion-personas',
        'admin.gestion-usuarios',
        'admin.personal-institucional',
        'admin.bitacora',
    ]);

    $academicoActive = request()->routeIs([
        'admin.gestion-academica',
        'admin.gestion-cursos',
        'admin.gestion-paralelos',
        'admin.gestion-turnos',
        'admin.gestion-asignaturas',
        'admin.especialidades-tecnicas',
        'admin.periodo-evaluacion',
        'admin.planes-asignatura',
        'admin.calificaciones',
    ]);

    $comunidadActive = request()->routeIs([
        'admin.gestion-estudiantes',
        'admin.gestion-docentes',
        'admin.gestion-inscripciones',
        'admin.institucion-procedencia',
        'admin.tipo-vinculacion-estudiante',
    ]);

    $reportesActive = request()->routeIs([
        'admin.reportes-academicos',
        'admin.reportes-administrativos',
        'admin.reportes.*',
    ]);

    $initialGroup = match (true) {
        $adminActive => 'admin',
        $academicoActive => 'academico',
        $comunidadActive => 'comunidad',
        $reportesActive => 'reportes',
        default => null,
    };

    $rolActual = Auth::user()?->getRoleNames()->first() ?? 'Usuario';
    $inicialRol = strtoupper(substr($rolActual, 0, 1));
@endphp

<div x-data="{
        activeGroup: @js($initialGroup),
        tooltip: { show: false, text: '', top: 0 },

        toggleGroup(group) {
            if (!sidebarOpen) {
                sidebarOpen = true;
                this.activeGroup = group;
                this.tooltip.show = false;
                return;
            }

            this.activeGroup = this.activeGroup === group ? null : group;
        },

        showTip(event, text) {
            if (sidebarOpen) return;

            const rect = event.currentTarget.getBoundingClientRect();
            this.tooltip.text = text;
            this.tooltip.top = rect.top + (rect.height / 2);
            this.tooltip.show = true;
        },

        hideTip() {
            this.tooltip.show = false;
        }
    }" x-init="
        const savedSidebar = localStorage.getItem('savp-sidebar-open');

        if (savedSidebar !== null) {
            sidebarOpen = savedSidebar === 'true';
        }

        $watch('sidebarOpen', value => {
            localStorage.setItem('savp-sidebar-open', value ? 'true' : 'false');
            if (value) tooltip.show = false;
        });
    "
    class="fixed left-0 top-0 z-40 flex h-screen flex-col border-r shadow-xl backdrop-blur-xl transition-[width] duration-300 ease-out"
    :class="sidebarOpen ? 'w-72' : 'w-20'"
    style="background: color-mix(in srgb, var(--ui-surface) 94%, transparent); border-color: var(--ui-border); color: var(--ui-text);">

    {{-- ================================================================
    MARCA / LOGO
    ================================================================= --}}
    <div class="relative border-b px-3 py-4" style="border-color: var(--ui-border);">
        <a href="{{ route('dashboard') }}"
            class="flex min-w-0 items-center gap-3 overflow-hidden rounded-2xl transition-all duration-200"
            :class="sidebarOpen ? 'justify-start pr-8' : 'justify-center'">

            <div class="relative flex h-11 w-11 shrink-0 items-center justify-center overflow-hidden rounded-2xl border shadow-sm transition-all duration-300 hover:-translate-y-0.5 hover:shadow-md"
                style="background: var(--ui-surface); border-color: var(--ui-border);">
                <div class="absolute inset-0 bg-gradient-to-br from-emerald-500/10 via-teal-500/5 to-sky-500/10"></div>
                <img src="{{ asset('image/LOGO FT3 A.jpg') }}" alt="Logo Franz Tamayo N°3"
                    class="relative h-8 w-8 object-contain">
            </div>

            <div x-show="sidebarOpen" x-transition.opacity.duration.150ms x-cloak class="min-w-0">
                <p class="truncate text-sm font-black leading-tight" style="color: var(--ui-text);">
                    Franz Tamayo N°3
                </p>
                <p class="mt-0.5 truncate text-[10px] font-bold uppercase tracking-[0.19em]"
                    style="color: var(--ui-primary);">
                    SAVP · TIS 3
                </p>
            </div>
        </a>

        {{-- Botón flotante de contraer / expandir --}}
        <button type="button" @click="sidebarOpen = !sidebarOpen"
            @mouseenter="showTip($event, sidebarOpen ? 'Contraer menú' : 'Expandir menú')" @mouseleave="hideTip()"
            class="absolute -right-3 top-1/2 z-20 flex h-7 w-7 -translate-y-1/2 items-center justify-center rounded-full border shadow-md transition-all duration-200 hover:scale-110"
            style="background: var(--ui-surface); border-color: var(--ui-border); color: var(--ui-primary);"
            :aria-label="sidebarOpen ? 'Contraer menú' : 'Expandir menú'">
            <i class="ph-duotone ph-caret-left inline-block text-[15px] leading-none transition-transform duration-300"
                :class="{ 'rotate-180': !sidebarOpen }"></i>
        </button>
    </div>

    {{-- ================================================================
    TARJETA DE ROL
    ================================================================= --}}
    <div class="px-3 pt-3">
        <div @mouseenter="showTip($event, '{{ addslashes($rolActual) }}')" @mouseleave="hideTip()"
            class="relative overflow-hidden rounded-2xl border p-3 shadow-sm transition-all duration-300 hover:-translate-y-0.5 hover:shadow-md"
            style="border-color: color-mix(in srgb, var(--ui-primary) 22%, var(--ui-border)); background: linear-gradient(135deg, color-mix(in srgb, var(--ui-primary-soft) 88%, var(--ui-surface)), color-mix(in srgb, var(--ui-info-soft) 72%, var(--ui-surface)));">

            <div class="pointer-events-none absolute -right-6 -top-6 h-20 w-20 rounded-full bg-emerald-500/10 blur-xl">
            </div>
            <div class="pointer-events-none absolute -bottom-7 -left-7 h-20 w-20 rounded-full bg-sky-500/10 blur-xl">
            </div>

            <template x-if="sidebarOpen">
                <div class="relative flex items-center gap-3">
                    <div
                        class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-emerald-500/10 text-emerald-600 ring-1 ring-emerald-500/20 dark:text-emerald-400">
                        <i class="ph-duotone ph-shield-check text-[1.35rem] leading-none"></i>
                    </div>

                    <div class="min-w-0">
                        <p class="text-[10px] font-bold uppercase tracking-[0.17em]" style="color: var(--ui-muted);">
                            Panel actual
                        </p>
                        <p class="mt-0.5 truncate text-sm font-black" style="color: var(--ui-text);">
                            {{ $rolActual }}
                        </p>
                    </div>
                </div>
            </template>

            <template x-if="!sidebarOpen">
                <div class="relative flex justify-center">
                    <div
                        class="flex h-9 w-9 items-center justify-center rounded-xl bg-emerald-500/10 text-sm font-black text-emerald-600 ring-1 ring-emerald-500/20 dark:text-emerald-400">
                        {{ $inicialRol }}
                    </div>
                </div>
            </template>
        </div>
    </div>

    {{-- ================================================================
    NAVEGACIÓN
    ================================================================= --}}
    <nav class="ui-scrollbar flex-1 overflow-y-auto py-4" :class="sidebarOpen ? 'px-3' : 'px-2'">

        {{-- ==================== PRINCIPAL ==================== --}}
        @canany(['Panel_Administrador', 'Panel_Director', 'Panel_Docente', 'Panel_Estudiante', 'Panel_Secretaria', 'Panel_Regente'])
            <div>
                <p x-show="sidebarOpen" x-transition.opacity.duration.150ms x-cloak
                    class="mb-2 px-2 text-[10px] font-bold uppercase tracking-[0.20em]" style="color: var(--ui-muted);">
                    Principal
                </p>

                <a href="{{ route('dashboard') }}" @mouseenter="showTip($event, 'Panel principal')" @mouseleave="hideTip()"
                    @if($dashboardActive) aria-current="page" @endif
                    class="group relative flex items-center gap-3 overflow-hidden rounded-2xl py-2.5 text-sm font-semibold transition-all duration-200 before:absolute before:left-0 before:top-1/2 before:h-7 before:w-[3px] before:-translate-y-1/2 before:rounded-full before:transition-all
                            {{ $dashboardActive
            ? 'bg-[var(--ui-primary-soft)] text-[var(--ui-primary)] shadow-sm ring-1 ring-[var(--ui-primary-border)] before:bg-[var(--ui-primary)] before:opacity-100'
            : 'text-[var(--ui-text-soft)] before:opacity-0 hover:bg-[var(--ui-surface-muted)] hover:text-[var(--ui-text)]' }}"
                    :class="sidebarOpen ? 'px-3' : 'justify-center px-2'">

                    <span
                        class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-emerald-500/10 text-emerald-600 ring-1 ring-emerald-500/20 transition-all duration-200 group-hover:-translate-y-0.5 group-hover:scale-105 group-hover:bg-emerald-500/20 dark:text-emerald-400">
                        <i class="ph-duotone ph-house inline-block text-[1.35rem] leading-none"></i>
                    </span>

                    <span x-show="sidebarOpen" x-transition.opacity.duration.150ms x-cloak class="truncate">
                        Panel principal
                    </span>
                </a>
            </div>
        @endcanany

        {{-- ==================== GESTIÓN ==================== --}}
        @canany([
                'Gestion_Usuarios',
                'Registro_Personas',
                'Personal_Institucional',
                'Bitacora',
                'Gestion_Academica',
                'Cursos',
                'Paralelos',
                'Turnos',
                'Asignaturas',
                'Especialidades_Tecnicas',
                'Periodo_Evaluacion',
                'Planes_Asignatura',
                'Calificaciones',
                'Estudiantes',
                'Docentes',
                'Inscripciones',
                'Institucion_Procedencia',
                'Tipo_Vinculacion_Estudiante'
            ])
            <div class="mt-4">
                <p x-show="sidebarOpen" x-transition.opacity.duration.150ms x-cloak
                    class="mb-2 px-2 text-[10px] font-bold uppercase tracking-[0.20em]" style="color: var(--ui-muted);">
                    Gestión
                </p>

                <div class="space-y-1.5">
                    {{-- ---------------- ADMINISTRACIÓN ---------------- --}}
                    @canany(['Gestion_Usuarios', 'Registro_Personas', 'Personal_Institucional', 'Bitacora'])
                            <div>
                                <button type="button" @click="toggleGroup('admin')" @mouseenter="showTip($event, 'Administración')"
                                    @mouseleave="hideTip()" :aria-expanded="(sidebarOpen && activeGroup === 'admin').toString()"
                                    aria-controls="savp-menu-admin"
                                    class="group relative flex w-full items-center overflow-hidden rounded-2xl py-2.5 text-sm font-semibold transition-all duration-200 before:absolute before:left-0 before:top-1/2 before:h-7 before:w-[3px] before:-translate-y-1/2 before:rounded-full before:transition-all
                                                {{ $adminActive
                        ? 'bg-[var(--ui-primary-soft)] text-[var(--ui-primary)] ring-1 ring-[var(--ui-primary-border)] before:bg-[var(--ui-primary)] before:opacity-100'
                        : 'text-[var(--ui-text-soft)] before:opacity-0 hover:bg-[var(--ui-surface-muted)] hover:text-[var(--ui-text)]' }}"
                                    :class="sidebarOpen ? 'justify-between px-3' : 'justify-center px-2'">

                                    <span class="flex min-w-0 items-center gap-3">
                                        <span
                                            class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-emerald-500/10 text-emerald-600 ring-1 ring-emerald-500/20 transition-all duration-200 group-hover:bg-emerald-500/20 dark:text-emerald-400">
                                            <i
                                                class="ph-duotone ph-gear-six inline-block text-[1.35rem] leading-none transition-transform duration-300 group-hover:rotate-12 group-hover:scale-110"></i>
                                        </span>

                                        <span x-show="sidebarOpen" x-transition.opacity.duration.150ms x-cloak class="truncate">
                                            Administración
                                        </span>
                                    </span>

                                    <i x-show="sidebarOpen" x-cloak
                                        class="ph-duotone ph-caret-down ml-2 shrink-0 text-sm transition-transform duration-300"
                                        :class="{ 'rotate-180': activeGroup === 'admin' }"></i>
                                </button>

                                <div id="savp-menu-admin" x-show="sidebarOpen && activeGroup === 'admin'"
                                    x-transition:enter="transition ease-out duration-200"
                                    x-transition:enter-start="opacity-0 -translate-y-1"
                                    x-transition:enter-end="opacity-100 translate-y-0"
                                    x-transition:leave="transition ease-in duration-150"
                                    x-transition:leave-start="opacity-100 translate-y-0"
                                    x-transition:leave-end="opacity-0 -translate-y-1" x-cloak
                                    class="ml-5 mt-2 space-y-1 border-l pl-3" style="border-color: var(--ui-border);">

                                    @can('Registro_Personas')
                                                <a href="{{ route('admin.gestion-personas') }}"
                                                    @if(request()->routeIs('admin.gestion-personas')) aria-current="page" @endif
                                                    class="group/child flex items-center gap-2.5 rounded-xl px-2.5 py-2 text-[13px] font-medium transition-all duration-200
                                                                    {{ request()->routeIs('admin.gestion-personas')
                                        ? 'bg-[var(--ui-primary-soft)] text-[var(--ui-primary)]'
                                        : 'text-[var(--ui-muted)] hover:bg-[var(--ui-surface-muted)] hover:text-[var(--ui-text)]' }}">
                                                    <span
                                                        class="flex h-7 w-7 shrink-0 items-center justify-center rounded-lg bg-emerald-500/10 text-emerald-600 transition-transform duration-200 group-hover/child:scale-110 dark:text-emerald-400">
                                                        <i class="ph-duotone ph-identification-card text-base leading-none"></i>
                                                    </span>
                                                    <span class="truncate">Registro de personas</span>
                                                </a>
                                    @endcan

                                    @can('Gestion_Usuarios')
                                                <a href="{{ route('admin.gestion-usuarios') }}"
                                                    @if(request()->routeIs('admin.gestion-usuarios')) aria-current="page" @endif
                                                    class="group/child flex items-center gap-2.5 rounded-xl px-2.5 py-2 text-[13px] font-medium transition-all duration-200
                                                                    {{ request()->routeIs('admin.gestion-usuarios')
                                        ? 'bg-[var(--ui-primary-soft)] text-[var(--ui-primary)]'
                                        : 'text-[var(--ui-muted)] hover:bg-[var(--ui-surface-muted)] hover:text-[var(--ui-text)]' }}">
                                                    <span
                                                        class="flex h-7 w-7 shrink-0 items-center justify-center rounded-lg bg-emerald-500/10 text-emerald-600 transition-transform duration-200 group-hover/child:scale-110 dark:text-emerald-400">
                                                        <i class="ph-duotone ph-users text-base leading-none"></i>
                                                    </span>
                                                    <span class="truncate">Gestión de usuarios</span>
                                                </a>
                                    @endcan

                                    @can('Personal_Institucional')
                                                <a href="{{ route('admin.personal-institucional') }}"
                                                    @if(request()->routeIs('admin.personal-institucional')) aria-current="page" @endif
                                                    class="group/child flex items-center gap-2.5 rounded-xl px-2.5 py-2 text-[13px] font-medium transition-all duration-200
                                                                    {{ request()->routeIs('admin.personal-institucional')
                                        ? 'bg-[var(--ui-primary-soft)] text-[var(--ui-primary)]'
                                        : 'text-[var(--ui-muted)] hover:bg-[var(--ui-surface-muted)] hover:text-[var(--ui-text)]' }}">
                                                    <span
                                                        class="flex h-7 w-7 shrink-0 items-center justify-center rounded-lg bg-emerald-500/10 text-emerald-600 transition-transform duration-200 group-hover/child:scale-110 dark:text-emerald-400">
                                                        <i class="ph-duotone ph-buildings text-base leading-none"></i>
                                                    </span>
                                                    <span class="truncate">Personal institucional</span>
                                                </a>
                                    @endcan

                                    @can('Bitacora')
                                                <a href="{{ route('admin.bitacora') }}" @if(request()->routeIs('admin.bitacora'))
                                                aria-current="page" @endif
                                                    class="group/child flex items-center gap-2.5 rounded-xl px-2.5 py-2 text-[13px] font-medium transition-all duration-200
                                                                    {{ request()->routeIs('admin.bitacora')
                                        ? 'bg-[var(--ui-primary-soft)] text-[var(--ui-primary)]'
                                        : 'text-[var(--ui-muted)] hover:bg-[var(--ui-surface-muted)] hover:text-[var(--ui-text)]' }}">
                                                    <span
                                                        class="flex h-7 w-7 shrink-0 items-center justify-center rounded-lg bg-emerald-500/10 text-emerald-600 transition-transform duration-200 group-hover/child:-rotate-6 group-hover/child:scale-110 dark:text-emerald-400">
                                                        <i class="ph-duotone ph-clock-counter-clockwise text-base leading-none"></i>
                                                    </span>
                                                    <span class="truncate">Bitácora</span>
                                                </a>
                                    @endcan
                                </div>
                            </div>
                    @endcanany

                    {{-- ---------------- GESTIÓN ACADÉMICA ---------------- --}}
                    @canany(['Gestion_Academica', 'Cursos', 'Paralelos', 'Turnos', 'Asignaturas', 'Especialidades_Tecnicas', 'Periodo_Evaluacion', 'Planes_Asignatura', 'Calificaciones'])
                            <div>
                                <button type="button" @click="toggleGroup('academico')"
                                    @mouseenter="showTip($event, 'Gestión académica')" @mouseleave="hideTip()"
                                    :aria-expanded="(sidebarOpen && activeGroup === 'academico').toString()"
                                    aria-controls="savp-menu-academico"
                                    class="group relative flex w-full items-center overflow-hidden rounded-2xl py-2.5 text-sm font-semibold transition-all duration-200 before:absolute before:left-0 before:top-1/2 before:h-7 before:w-[3px] before:-translate-y-1/2 before:rounded-full before:transition-all
                                                {{ $academicoActive
                        ? 'bg-[var(--ui-info-soft)] text-[var(--ui-info)] ring-1 ring-[var(--ui-info-border)] before:bg-[var(--ui-info)] before:opacity-100'
                        : 'text-[var(--ui-text-soft)] before:opacity-0 hover:bg-[var(--ui-surface-muted)] hover:text-[var(--ui-text)]' }}"
                                    :class="sidebarOpen ? 'justify-between px-3' : 'justify-center px-2'">

                                    <span class="flex min-w-0 items-center gap-3">
                                        <span
                                            class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-sky-500/10 text-sky-600 ring-1 ring-sky-500/20 transition-all duration-200 group-hover:-translate-y-0.5 group-hover:bg-sky-500/20 dark:text-sky-400">
                                            <i
                                                class="ph-duotone ph-graduation-cap inline-block text-[1.35rem] leading-none transition-transform duration-200 group-hover:scale-110"></i>
                                        </span>

                                        <span x-show="sidebarOpen" x-transition.opacity.duration.150ms x-cloak class="truncate">
                                            Gestión académica
                                        </span>
                                    </span>

                                    <i x-show="sidebarOpen" x-cloak
                                        class="ph-duotone ph-caret-down ml-2 shrink-0 text-sm transition-transform duration-300"
                                        :class="{ 'rotate-180': activeGroup === 'academico' }"></i>
                                </button>

                                <div id="savp-menu-academico" x-show="sidebarOpen && activeGroup === 'academico'"
                                    x-transition:enter="transition ease-out duration-200"
                                    x-transition:enter-start="opacity-0 -translate-y-1"
                                    x-transition:enter-end="opacity-100 translate-y-0"
                                    x-transition:leave="transition ease-in duration-150"
                                    x-transition:leave-start="opacity-100 translate-y-0"
                                    x-transition:leave-end="opacity-0 -translate-y-1" x-cloak
                                    class="ml-5 mt-2 space-y-1 border-l pl-3" style="border-color: var(--ui-border);">

                                    @can('Gestion_Academica')
                                        <a href="{{ route('admin.gestion-academica') }}"
                                            class="group/child flex items-center gap-2.5 rounded-xl px-2.5 py-2 text-[13px] font-medium transition-all duration-200 {{ request()->routeIs('admin.gestion-academica') ? 'bg-[var(--ui-info-soft)] text-[var(--ui-info)]' : 'text-[var(--ui-muted)] hover:bg-[var(--ui-surface-muted)] hover:text-[var(--ui-text)]' }}">
                                            <span
                                                class="flex h-7 w-7 shrink-0 items-center justify-center rounded-lg bg-sky-500/10 text-sky-600 transition-transform duration-200 group-hover/child:scale-110 dark:text-sky-400"><i
                                                    class="ph-duotone ph-calendar-dots text-base leading-none"></i></span>
                                            <span class="truncate">Gestión académica</span>
                                        </a>
                                    @endcan

                                    @can('Cursos')
                                        <a href="{{ route('admin.gestion-cursos') }}"
                                            class="group/child flex items-center gap-2.5 rounded-xl px-2.5 py-2 text-[13px] font-medium transition-all duration-200 {{ request()->routeIs('admin.gestion-cursos') ? 'bg-[var(--ui-info-soft)] text-[var(--ui-info)]' : 'text-[var(--ui-muted)] hover:bg-[var(--ui-surface-muted)] hover:text-[var(--ui-text)]' }}">
                                            <span
                                                class="flex h-7 w-7 shrink-0 items-center justify-center rounded-lg bg-sky-500/10 text-sky-600 transition-transform duration-200 group-hover/child:scale-110 dark:text-sky-400"><i
                                                    class="ph-duotone ph-books text-base leading-none"></i></span>
                                            <span class="truncate">Cursos</span>
                                        </a>
                                    @endcan

                                    @can('Paralelos')
                                        <a href="{{ route('admin.gestion-paralelos') }}"
                                            class="group/child flex items-center gap-2.5 rounded-xl px-2.5 py-2 text-[13px] font-medium transition-all duration-200 {{ request()->routeIs('admin.gestion-paralelos') ? 'bg-[var(--ui-info-soft)] text-[var(--ui-info)]' : 'text-[var(--ui-muted)] hover:bg-[var(--ui-surface-muted)] hover:text-[var(--ui-text)]' }}">
                                            <span
                                                class="flex h-7 w-7 shrink-0 items-center justify-center rounded-lg bg-sky-500/10 text-sky-600 transition-transform duration-200 group-hover/child:scale-110 dark:text-sky-400"><i
                                                    class="ph-duotone ph-squares-four text-base leading-none"></i></span>
                                            <span class="truncate">Paralelos</span>
                                        </a>
                                    @endcan

                                    @can('Turnos')
                                        <a href="{{ route('admin.gestion-turnos') }}"
                                            class="group/child flex items-center gap-2.5 rounded-xl px-2.5 py-2 text-[13px] font-medium transition-all duration-200 {{ request()->routeIs('admin.gestion-turnos') ? 'bg-[var(--ui-info-soft)] text-[var(--ui-info)]' : 'text-[var(--ui-muted)] hover:bg-[var(--ui-surface-muted)] hover:text-[var(--ui-text)]' }}">
                                            <span
                                                class="flex h-7 w-7 shrink-0 items-center justify-center rounded-lg bg-sky-500/10 text-sky-600 transition-transform duration-200 group-hover/child:rotate-6 group-hover/child:scale-110 dark:text-sky-400"><i
                                                    class="ph-duotone ph-clock text-base leading-none"></i></span>
                                            <span class="truncate">Turnos</span>
                                        </a>
                                    @endcan

                                    @can('Asignaturas')
                                        <a href="{{ route('admin.gestion-asignaturas') }}"
                                            class="group/child flex items-center gap-2.5 rounded-xl px-2.5 py-2 text-[13px] font-medium transition-all duration-200 {{ request()->routeIs('admin.gestion-asignaturas') ? 'bg-[var(--ui-info-soft)] text-[var(--ui-info)]' : 'text-[var(--ui-muted)] hover:bg-[var(--ui-surface-muted)] hover:text-[var(--ui-text)]' }}">
                                            <span
                                                class="flex h-7 w-7 shrink-0 items-center justify-center rounded-lg bg-sky-500/10 text-sky-600 transition-transform duration-200 group-hover/child:scale-110 dark:text-sky-400"><i
                                                    class="ph-duotone ph-book-open-text text-base leading-none"></i></span>
                                            <span class="truncate">Asignaturas</span>
                                        </a>
                                    @endcan

                                    @can('Especialidades_Tecnicas')
                                        <a href="{{ route('admin.especialidades-tecnicas') }}"
                                            class="group/child flex items-center gap-2.5 rounded-xl px-2.5 py-2 text-[13px] font-medium transition-all duration-200 {{ request()->routeIs('admin.especialidades-tecnicas') ? 'bg-[var(--ui-info-soft)] text-[var(--ui-info)]' : 'text-[var(--ui-muted)] hover:bg-[var(--ui-surface-muted)] hover:text-[var(--ui-text)]' }}">
                                            <span
                                                class="flex h-7 w-7 shrink-0 items-center justify-center rounded-lg bg-sky-500/10 text-sky-600 transition-transform duration-200 group-hover/child:rotate-6 group-hover/child:scale-110 dark:text-sky-400"><i
                                                    class="ph-duotone ph-wrench text-base leading-none"></i></span>
                                            <span class="truncate">Especialidades técnicas</span>
                                        </a>
                                    @endcan

                                    @can('Periodo_Evaluacion')
                                        <a href="{{ route('admin.periodo-evaluacion') }}"
                                            class="group/child flex items-center gap-2.5 rounded-xl px-2.5 py-2 text-[13px] font-medium transition-all duration-200 {{ request()->routeIs('admin.periodo-evaluacion') ? 'bg-[var(--ui-info-soft)] text-[var(--ui-info)]' : 'text-[var(--ui-muted)] hover:bg-[var(--ui-surface-muted)] hover:text-[var(--ui-text)]' }}">
                                            <span
                                                class="flex h-7 w-7 shrink-0 items-center justify-center rounded-lg bg-sky-500/10 text-sky-600 transition-transform duration-200 group-hover/child:scale-110 dark:text-sky-400"><i
                                                    class="ph-duotone ph-calendar-check text-base leading-none"></i></span>
                                            <span class="truncate">Periodo de evaluación</span>
                                        </a>
                                    @endcan

                                    @can('Planes_Asignatura')
                                        <a href="{{ route('admin.planes-asignatura') }}"
                                            class="group/child flex items-center gap-2.5 rounded-xl px-2.5 py-2 text-[13px] font-medium transition-all duration-200 {{ request()->routeIs('admin.planes-asignatura') ? 'bg-[var(--ui-info-soft)] text-[var(--ui-info)]' : 'text-[var(--ui-muted)] hover:bg-[var(--ui-surface-muted)] hover:text-[var(--ui-text)]' }}">
                                            <span
                                                class="flex h-7 w-7 shrink-0 items-center justify-center rounded-lg bg-sky-500/10 text-sky-600 transition-transform duration-200 group-hover/child:scale-110 dark:text-sky-400"><i
                                                    class="ph-duotone ph-notebook text-base leading-none"></i></span>
                                            <span class="truncate">Planes de asignatura</span>
                                        </a>
                                    @endcan

                                    @can('Calificaciones')
                                        <a href="{{ route('admin.calificaciones') }}"
                                            class="group/child flex items-center gap-2.5 rounded-xl px-2.5 py-2 text-[13px] font-medium transition-all duration-200 {{ request()->routeIs('admin.calificaciones') ? 'bg-[var(--ui-info-soft)] text-[var(--ui-info)]' : 'text-[var(--ui-muted)] hover:bg-[var(--ui-surface-muted)] hover:text-[var(--ui-text)]' }}">
                                            <span
                                                class="flex h-7 w-7 shrink-0 items-center justify-center rounded-lg bg-sky-500/10 text-sky-600 transition-transform duration-200 group-hover/child:scale-110 dark:text-sky-400"><i
                                                    class="ph-duotone ph-exam text-base leading-none"></i></span>
                                            <span class="truncate">Calificaciones</span>
                                        </a>
                                    @endcan
                                </div>
                            </div>
                    @endcanany

                    {{-- ---------------- COMUNIDAD EDUCATIVA ---------------- --}}
                    @canany(['Estudiantes', 'Docentes', 'Inscripciones', 'Institucion_Procedencia', 'Tipo_Vinculacion_Estudiante'])
                            <div>
                                <button type="button" @click="toggleGroup('comunidad')"
                                    @mouseenter="showTip($event, 'Comunidad educativa')" @mouseleave="hideTip()"
                                    :aria-expanded="(sidebarOpen && activeGroup === 'comunidad').toString()"
                                    aria-controls="savp-menu-comunidad"
                                    class="group relative flex w-full items-center overflow-hidden rounded-2xl py-2.5 text-sm font-semibold transition-all duration-200 before:absolute before:left-0 before:top-1/2 before:h-7 before:w-[3px] before:-translate-y-1/2 before:rounded-full before:transition-all
                                                {{ $comunidadActive
                        ? 'bg-cyan-500/10 text-cyan-700 ring-1 ring-cyan-500/20 before:bg-cyan-500 before:opacity-100 dark:text-cyan-300'
                        : 'text-[var(--ui-text-soft)] before:opacity-0 hover:bg-[var(--ui-surface-muted)] hover:text-[var(--ui-text)]' }}"
                                    :class="sidebarOpen ? 'justify-between px-3' : 'justify-center px-2'">

                                    <span class="flex min-w-0 items-center gap-3">
                                        <span
                                            class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-cyan-500/10 text-cyan-600 ring-1 ring-cyan-500/20 transition-all duration-200 group-hover:bg-cyan-500/20 dark:text-cyan-400">
                                            <i
                                                class="ph-duotone ph-users-three inline-block text-[1.35rem] leading-none transition-transform duration-200 group-hover:scale-110"></i>
                                        </span>

                                        <span x-show="sidebarOpen" x-transition.opacity.duration.150ms x-cloak class="truncate">
                                            Comunidad educativa
                                        </span>
                                    </span>

                                    <i x-show="sidebarOpen" x-cloak
                                        class="ph-duotone ph-caret-down ml-2 shrink-0 text-sm transition-transform duration-300"
                                        :class="{ 'rotate-180': activeGroup === 'comunidad' }"></i>
                                </button>

                                <div id="savp-menu-comunidad" x-show="sidebarOpen && activeGroup === 'comunidad'"
                                    x-transition:enter="transition ease-out duration-200"
                                    x-transition:enter-start="opacity-0 -translate-y-1"
                                    x-transition:enter-end="opacity-100 translate-y-0"
                                    x-transition:leave="transition ease-in duration-150"
                                    x-transition:leave-start="opacity-100 translate-y-0"
                                    x-transition:leave-end="opacity-0 -translate-y-1" x-cloak
                                    class="ml-5 mt-2 space-y-1 border-l pl-3" style="border-color: var(--ui-border);">

                                    @can('Estudiantes')
                                        <a href="{{ route('admin.gestion-estudiantes') }}"
                                            class="group/child flex items-center gap-2.5 rounded-xl px-2.5 py-2 text-[13px] font-medium transition-all duration-200 {{ request()->routeIs('admin.gestion-estudiantes') ? 'bg-cyan-500/10 text-cyan-700 dark:text-cyan-300' : 'text-[var(--ui-muted)] hover:bg-[var(--ui-surface-muted)] hover:text-[var(--ui-text)]' }}">
                                            <span
                                                class="flex h-7 w-7 shrink-0 items-center justify-center rounded-lg bg-cyan-500/10 text-cyan-600 transition-transform duration-200 group-hover/child:scale-110 dark:text-cyan-400"><i
                                                    class="ph-duotone ph-student text-base leading-none"></i></span>
                                            <span class="truncate">Estudiantes</span>
                                        </a>
                                    @endcan

                                    @can('Docentes')
                                        <a href="{{ route('admin.gestion-docentes') }}"
                                            class="group/child flex items-center gap-2.5 rounded-xl px-2.5 py-2 text-[13px] font-medium transition-all duration-200 {{ request()->routeIs('admin.gestion-docentes') ? 'bg-cyan-500/10 text-cyan-700 dark:text-cyan-300' : 'text-[var(--ui-muted)] hover:bg-[var(--ui-surface-muted)] hover:text-[var(--ui-text)]' }}">
                                            <span
                                                class="flex h-7 w-7 shrink-0 items-center justify-center rounded-lg bg-cyan-500/10 text-cyan-600 transition-transform duration-200 group-hover/child:scale-110 dark:text-cyan-400"><i
                                                    class="ph-duotone ph-chalkboard-teacher text-base leading-none"></i></span>
                                            <span class="truncate">Docentes</span>
                                        </a>
                                    @endcan

                                    @can('Inscripciones')
                                        <a href="{{ route('admin.gestion-inscripciones') }}"
                                            class="group/child flex items-center gap-2.5 rounded-xl px-2.5 py-2 text-[13px] font-medium transition-all duration-200 {{ request()->routeIs('admin.gestion-inscripciones') ? 'bg-cyan-500/10 text-cyan-700 dark:text-cyan-300' : 'text-[var(--ui-muted)] hover:bg-[var(--ui-surface-muted)] hover:text-[var(--ui-text)]' }}">
                                            <span
                                                class="flex h-7 w-7 shrink-0 items-center justify-center rounded-lg bg-cyan-500/10 text-cyan-600 transition-transform duration-200 group-hover/child:scale-110 dark:text-cyan-400"><i
                                                    class="ph-duotone ph-note-pencil text-base leading-none"></i></span>
                                            <span class="truncate">Inscripciones</span>
                                        </a>
                                    @endcan

                                    @can('Institucion_Procedencia')
                                        <a href="{{ route('admin.institucion-procedencia') }}"
                                            class="group/child flex items-center gap-2.5 rounded-xl px-2.5 py-2 text-[13px] font-medium transition-all duration-200 {{ request()->routeIs('admin.institucion-procedencia') ? 'bg-cyan-500/10 text-cyan-700 dark:text-cyan-300' : 'text-[var(--ui-muted)] hover:bg-[var(--ui-surface-muted)] hover:text-[var(--ui-text)]' }}">
                                            <span
                                                class="flex h-7 w-7 shrink-0 items-center justify-center rounded-lg bg-cyan-500/10 text-cyan-600 transition-transform duration-200 group-hover/child:-translate-y-0.5 group-hover/child:scale-110 dark:text-cyan-400"><i
                                                    class="ph-duotone ph-building text-base leading-none"></i></span>
                                            <span class="truncate">Institución de procedencia</span>
                                        </a>
                                    @endcan

                                    @can('Tipo_Vinculacion_Estudiante')
                                        <a href="{{ route('admin.tipo-vinculacion-estudiante') }}"
                                            class="group/child flex items-center gap-2.5 rounded-xl px-2.5 py-2 text-[13px] font-medium transition-all duration-200 {{ request()->routeIs('admin.tipo-vinculacion-estudiante') ? 'bg-cyan-500/10 text-cyan-700 dark:text-cyan-300' : 'text-[var(--ui-muted)] hover:bg-[var(--ui-surface-muted)] hover:text-[var(--ui-text)]' }}">
                                            <span
                                                class="flex h-7 w-7 shrink-0 items-center justify-center rounded-lg bg-cyan-500/10 text-cyan-600 transition-transform duration-200 group-hover/child:rotate-6 group-hover/child:scale-110 dark:text-cyan-400"><i
                                                    class="ph-duotone ph-link text-base leading-none"></i></span>
                                            <span class="truncate">Tipo de vinculación</span>
                                        </a>
                                    @endcan
                                </div>
                            </div>
                    @endcanany
                </div>
            </div>
        @endcanany

        {{-- ==================== INFORMACIÓN ==================== --}}
        @canany(['Reportes_Academicos', 'Reportes_Administrativos'])
            <div class="mt-4">
                <p x-show="sidebarOpen" x-transition.opacity.duration.150ms x-cloak
                    class="mb-2 px-2 text-[10px] font-bold uppercase tracking-[0.20em]" style="color: var(--ui-muted);">
                    Información
                </p>

                <div>
                    <button type="button" @click="toggleGroup('reportes')" @mouseenter="showTip($event, 'Reportes')"
                        @mouseleave="hideTip()" :aria-expanded="(sidebarOpen && activeGroup === 'reportes').toString()"
                        aria-controls="savp-menu-reportes"
                        class="group relative flex w-full items-center overflow-hidden rounded-2xl py-2.5 text-sm font-semibold transition-all duration-200 before:absolute before:left-0 before:top-1/2 before:h-7 before:w-[3px] before:-translate-y-1/2 before:rounded-full before:transition-all
                                {{ $reportesActive
            ? 'bg-teal-500/10 text-teal-700 ring-1 ring-teal-500/20 before:bg-teal-500 before:opacity-100 dark:text-teal-300'
            : 'text-[var(--ui-text-soft)] before:opacity-0 hover:bg-[var(--ui-surface-muted)] hover:text-[var(--ui-text)]' }}"
                        :class="sidebarOpen ? 'justify-between px-3' : 'justify-center px-2'">

                        <span class="flex min-w-0 items-center gap-3">
                            <span
                                class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-teal-500/10 text-teal-600 ring-1 ring-teal-500/20 transition-all duration-200 group-hover:-translate-y-0.5 group-hover:bg-teal-500/20 dark:text-teal-400">
                                <i
                                    class="ph-duotone ph-chart-bar inline-block text-[1.35rem] leading-none transition-transform duration-200 group-hover:scale-110"></i>
                            </span>

                            <span x-show="sidebarOpen" x-transition.opacity.duration.150ms x-cloak class="truncate">
                                Reportes
                            </span>
                        </span>

                        <i x-show="sidebarOpen" x-cloak
                            class="ph-duotone ph-caret-down ml-2 shrink-0 text-sm transition-transform duration-300"
                            :class="{ 'rotate-180': activeGroup === 'reportes' }"></i>
                    </button>

                    <div id="savp-menu-reportes" x-show="sidebarOpen && activeGroup === 'reportes'"
                        x-transition:enter="transition ease-out duration-200"
                        x-transition:enter-start="opacity-0 -translate-y-1"
                        x-transition:enter-end="opacity-100 translate-y-0"
                        x-transition:leave="transition ease-in duration-150"
                        x-transition:leave-start="opacity-100 translate-y-0"
                        x-transition:leave-end="opacity-0 -translate-y-1" x-cloak class="ml-5 mt-2 space-y-1 border-l pl-3"
                        style="border-color: var(--ui-border);">

                        @can('Reportes_Academicos')
                            <a href="{{ route('admin.reportes-academicos') }}"
                                class="group/child flex items-center gap-2.5 rounded-xl px-2.5 py-2 text-[13px] font-medium transition-all duration-200 {{ request()->routeIs('admin.reportes-academicos') ? 'bg-teal-500/10 text-teal-700 dark:text-teal-300' : 'text-[var(--ui-muted)] hover:bg-[var(--ui-surface-muted)] hover:text-[var(--ui-text)]' }}">
                                <span
                                    class="flex h-7 w-7 shrink-0 items-center justify-center rounded-lg bg-teal-500/10 text-teal-600 transition-transform duration-200 group-hover/child:scale-110 dark:text-teal-400"><i
                                        class="ph-duotone ph-chart-line-up text-base leading-none"></i></span>
                                <span class="truncate">Reportes académicos</span>
                            </a>
                        @endcan

                        @can('Reportes_Administrativos')
                            <a href="{{ route('admin.reportes-administrativos') }}"
                                class="group/child flex items-center gap-2.5 rounded-xl px-2.5 py-2 text-[13px] font-medium transition-all duration-200 {{ request()->routeIs('admin.reportes-administrativos') ? 'bg-teal-500/10 text-teal-700 dark:text-teal-300' : 'text-[var(--ui-muted)] hover:bg-[var(--ui-surface-muted)] hover:text-[var(--ui-text)]' }}">
                                <span
                                    class="flex h-7 w-7 shrink-0 items-center justify-center rounded-lg bg-teal-500/10 text-teal-600 transition-transform duration-200 group-hover/child:scale-110 dark:text-teal-400"><i
                                        class="ph-duotone ph-file-text text-base leading-none"></i></span>
                                <span class="truncate">Reportes administrativos</span>
                            </a>
                        @endcan
                    </div>
                </div>
            </div>
        @endcanany
    </nav>

    {{-- ================================================================
    FOOTER / CUENTA
    ================================================================= --}}
    <div class="border-t py-3" :class="sidebarOpen ? 'px-3' : 'px-2'" style="border-color: var(--ui-border);">
        @can('Mi_Perfil')
            <a href="{{ route('profile.show') }}" @mouseenter="showTip($event, 'Mi perfil')" @mouseleave="hideTip()"
                class="group flex items-center gap-3 rounded-2xl py-2.5 text-sm font-semibold transition-all duration-200
                        {{ request()->routeIs('profile.show')
            ? 'bg-[var(--ui-primary-soft)] text-[var(--ui-primary)]'
            : 'text-[var(--ui-text-soft)] hover:bg-[var(--ui-surface-muted)] hover:text-[var(--ui-text)]' }}" :class="sidebarOpen ? 'px-3' : 'justify-center px-2'">
                <span
                    class="flex h-9 w-9 shrink-0 items-center justify-center rounded-xl bg-slate-500/10 text-slate-600 ring-1 ring-slate-500/10 transition-all duration-200 group-hover:scale-105 dark:text-slate-300">
                    <i class="ph-duotone ph-user-circle text-xl leading-none"></i>
                </span>
                <span x-show="sidebarOpen" x-cloak>Mi perfil</span>
            </a>
        @endcan

        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button type="submit" @mouseenter="showTip($event, 'Cerrar sesión')" @mouseleave="hideTip()"
                class="group mt-1 flex w-full items-center gap-3 rounded-2xl py-2.5 text-sm font-semibold transition-all duration-200 hover:bg-[var(--ui-danger-soft)]"
                style="color: var(--ui-danger);" :class="sidebarOpen ? 'px-3' : 'justify-center px-2'">
                <span
                    class="flex h-9 w-9 shrink-0 items-center justify-center rounded-xl bg-rose-500/10 text-rose-600 ring-1 ring-rose-500/10 transition-all duration-200 group-hover:translate-x-0.5 group-hover:scale-105 dark:text-rose-400">
                    <i class="ph-duotone ph-sign-out text-xl leading-none"></i>
                </span>
                <span x-show="sidebarOpen" x-cloak>Cerrar sesión</span>
            </button>
        </form>
    </div>

    {{-- ================================================================
    TOOLTIP DEL MODO CONTRAÍDO
    ================================================================= --}}
    <div x-show="tooltip.show && !sidebarOpen" x-transition:enter="transition ease-out duration-150"
        x-transition:enter-start="opacity-0 -translate-x-1" x-transition:enter-end="opacity-100 translate-x-0"
        x-transition:leave="transition ease-in duration-100" x-transition:leave-start="opacity-100 translate-x-0"
        x-transition:leave-end="opacity-0 -translate-x-1" x-cloak
        class="pointer-events-none fixed left-[5.6rem] z-[70] -translate-y-1/2 whitespace-nowrap rounded-xl border px-3 py-2 text-xs font-semibold shadow-xl"
        :style="`top: ${tooltip.top}px; background: var(--ui-surface); color: var(--ui-text); border-color: var(--ui-border);`">
        <span x-text="tooltip.text"></span>
        <span class="absolute -left-1 top-1/2 h-2 w-2 -translate-y-1/2 rotate-45 border-b border-l"
            style="background: var(--ui-surface); border-color: var(--ui-border);"></span>
    </div>
</div>