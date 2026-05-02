<div class="fixed left-0 top-0 z-40 flex h-screen flex-col border-r shadow-md backdrop-blur-xl transition-all duration-300"
    :class="sidebarOpen ? 'w-72' : 'w-20'"
    style="background: color-mix(in srgb, var(--ui-surface) 92%, transparent); border-color: var(--ui-border); color: var(--ui-text);">

    {{-- HEADER / MARCA --}}
    <div class="flex items-center justify-between border-b p-4" style="border-color: var(--ui-border);">
        <a href="{{ route('dashboard') }}" class="flex items-center space-x-3 overflow-hidden">
            <div class="flex h-11 w-11 shrink-0 items-center justify-center rounded-2xl shadow-sm ring-1"
                style="background: var(--ui-surface); --tw-ring-color: var(--ui-border);">
                <img src="{{ asset('image/LOGO FT3 A.jpg') }}" alt="Logo Franz Tamayo" class="h-8 w-8 object-contain">
            </div>

            <div x-show="sidebarOpen" x-cloak>
                <p class="text-sm font-black leading-tight" style="color: var(--ui-text);">
                    Franz Tamayo N°3
                </p>
                <p class="text-[11px] font-semibold uppercase tracking-[0.18em]" style="color: var(--ui-primary);">
                    SAVP – TIS 3
                </p>
            </div>
        </a>

        <button type="button" @click="sidebarOpen = !sidebarOpen"
            class="shrink-0 rounded-xl p-2 transition hover:bg-[var(--ui-surface-muted)]"
            style="color: var(--ui-muted);" title="Contraer menú">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 transition-transform duration-300"
                :class="{ 'rotate-180': !sidebarOpen }" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M15 19l-7-7 7-7" />
            </svg>
        </button>
    </div>

    {{-- TARJETA DEL ROL --}}
    <div class="px-4 pt-4">
        <div class="overflow-hidden rounded-2xl bg-gradient-to-r from-emerald-600 to-sky-600 p-4 text-white shadow-sm">
            <template x-if="sidebarOpen">
                <div>
                    <div class="flex items-center gap-2">
                        <svg class="h-4 w-4 text-emerald-100" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"
                                d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                        </svg>
                        <p class="text-xs font-semibold uppercase tracking-[0.18em] text-emerald-100">
                            Panel actual
                        </p>
                    </div>

                    <p class="mt-2 text-lg font-black">
                        {{ Auth::user()->getRoleNames()->first() ?? 'Usuario' }}
                    </p>

                    <p class="mt-1 text-sm leading-5 text-white/90">
                        Acceso institucional según permisos asignados.
                    </p>
                </div>
            </template>

            <template x-if="!sidebarOpen">
                <div class="flex justify-center">
                    <span class="text-lg font-black">
                        {{ strtoupper(substr(Auth::user()->getRoleNames()->first() ?? 'U', 0, 1)) }}
                    </span>
                </div>
            </template>
        </div>
    </div>

    {{-- NAVEGACIÓN --}}
    <nav class="ui-scrollbar flex-1 space-y-3 overflow-y-auto px-3 py-4">

        {{-- PRINCIPAL --}}
        @canany(['Panel_Administrador', 'Panel_Director', 'Panel_Docente', 'Panel_Estudiante', 'Panel_Secretaria', 'Panel_Regente'])
            <div>
                <p x-show="sidebarOpen" x-cloak class="mb-2 px-2 text-[11px] font-semibold uppercase tracking-[0.18em]"
                    style="color: var(--ui-muted);">
                    Principal
                </p>

                <a href="{{ route('dashboard') }}"
                    class="group flex items-center gap-3 rounded-2xl px-3 py-3 text-sm font-semibold transition" style="{{ request()->routeIs('dashboard')
            ? 'background: var(--ui-primary-soft); color: var(--ui-primary);'
            : 'color: var(--ui-text-soft);' }}"
                    onmouseover="this.style.background='var(--ui-primary-soft)'; this.style.color='var(--ui-primary)'"
                    onmouseout="this.style.background='{{ request()->routeIs('dashboard') ? 'var(--ui-primary-soft)' : 'transparent' }}'; this.style.color='{{ request()->routeIs('dashboard') ? 'var(--ui-primary)' : 'var(--ui-text-soft)' }}'">

                    <svg class="h-5 w-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"
                            d="M3 10.5 12 3l9 7.5M5.25 9.75V21h13.5V9.75M9 21v-6h6v6" />
                    </svg>

                    <span x-show="sidebarOpen" x-cloak>Panel principal</span>
                </a>
            </div>
        @endcanany

        {{-- ADMINISTRACIÓN --}}
        @canany(['Gestion_Usuarios', 'Registro_Personas', 'Personal_Institucional', 'Estudiantes', 'Bitacora'])
            <div x-data="{ openAdmin: true }">
                <button type="button" @click="openAdmin = !openAdmin"
                    class="flex w-full items-center justify-between rounded-2xl px-3 py-3 text-sm font-semibold transition hover:bg-[var(--ui-surface-muted)]"
                    style="color: var(--ui-text-soft);">

                    <div class="flex items-center gap-3">
                        {{-- Icono administración: ajustes institucionales --}}
                        <svg class="h-5 w-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"
                                d="M9.594 3.94c.09-.542.56-.94 1.11-.94h2.592c.55 0 1.02.398 1.11.94l.213 1.278c.063.374.313.686.66.84.347.153.75.117 1.064-.096l1.074-.72a1.125 1.125 0 0 1 1.45.12l1.832 1.832c.389.389.44 1.002.12 1.45l-.72 1.074c-.213.314-.249.717-.096 1.064.154.347.466.597.84.66l1.278.213c.542.09.94.56.94 1.11v2.592c0 .55-.398 1.02-.94 1.11l-1.278.213a1.125 1.125 0 0 0-.84.66 1.125 1.125 0 0 0 .096 1.064l.72 1.074c.32.448.269 1.061-.12 1.45l-1.832 1.832a1.125 1.125 0 0 1-1.45.12l-1.074-.72a1.125 1.125 0 0 0-1.064-.096 1.125 1.125 0 0 0-.66.84l-.213 1.278c-.09.542-.56.94-1.11.94h-2.592c-.55 0-1.02-.398-1.11-.94l-.213-1.278a1.125 1.125 0 0 0-.66-.84 1.125 1.125 0 0 0-1.064.096l-1.074.72a1.125 1.125 0 0 1-1.45-.12l-1.832-1.832a1.125 1.125 0 0 1-.12-1.45l.72-1.074c.213-.314.249-.717.096-1.064a1.125 1.125 0 0 0-.84-.66l-1.278-.213c-.542-.09-.94-.56-.94-1.11v-2.592c0-.55.398-1.02.94-1.11l1.278-.213c.374-.063.686-.313.84-.66a1.125 1.125 0 0 0-.096-1.064l-.72-1.074a1.125 1.125 0 0 1 .12-1.45l1.832-1.832a1.125 1.125 0 0 1 1.45-.12l1.074.72c.314.213.717.249 1.064.096.347-.154.597-.466.66-.84l.213-1.278Z" />
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"
                                d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" />
                        </svg>

                        <span x-show="sidebarOpen" x-cloak>Administración</span>
                    </div>

                    <svg x-show="sidebarOpen" x-cloak xmlns="http://www.w3.org/2000/svg"
                        class="h-4 w-4 shrink-0 transition-transform duration-300" :class="{ 'rotate-180': openAdmin }"
                        fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M19 9l-7 7-7-7" />
                    </svg>
                </button>

                <div x-show="openAdmin" x-transition:enter="transition ease-out duration-200"
                    x-transition:enter-start="opacity-0 -translate-y-1" x-transition:enter-end="opacity-100 translate-y-0"
                    x-transition:leave="transition ease-in duration-150"
                    x-transition:leave-start="opacity-100 translate-y-0" x-transition:leave-end="opacity-0 -translate-y-1"
                    class="mt-2 space-y-1 pl-3">

                    @can('Gestion_Usuarios')
                            <a href="{{ route('admin.gestion-usuarios') }}"
                                class="flex items-center gap-3 rounded-xl px-3 py-2 text-sm font-medium transition" style="{{ request()->routeIs('admin.gestion-usuarios')
                        ? 'background: var(--ui-primary-soft); color: var(--ui-primary);'
                        : 'color: var(--ui-muted);' }}"
                                onmouseover="this.style.background='var(--ui-primary-soft)'; this.style.color='var(--ui-primary)'"
                                onmouseout="this.style.background='{{ request()->routeIs('admin.gestion-usuarios') ? 'var(--ui-primary-soft)' : 'transparent' }}'; this.style.color='{{ request()->routeIs('admin.gestion-usuarios') ? 'var(--ui-primary)' : 'var(--ui-muted)' }}'">

                                {{-- Usuarios y roles --}}
                                <svg class="h-5 w-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"
                                        d="M15 19.128a9.38 9.38 0 0 0 2.625.372 9.337 9.337 0 0 0 4.121-.952 4.125 4.125 0 0 0-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.162-.786-3.07M15 19.128v.106A12.318 12.318 0 0 1 8.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.106a6.375 6.375 0 0 1 12.75 0Zm-3.75-11.25a3.375 3.375 0 1 1-6.75 0 3.375 3.375 0 0 1 6.75 0Zm8.25 2.25a2.625 2.625 0 1 1-5.25 0 2.625 2.625 0 0 1 5.25 0Z" />
                                </svg>

                                <span x-show="sidebarOpen" x-cloak>Gestión de usuarios</span>
                            </a>
                    @endcan

                    @can('Registro_Personas')
                            <a href="{{ route('admin.gestion-personas') }}"
                                class="flex items-center gap-3 rounded-xl px-3 py-2 text-sm font-medium transition" style="{{ request()->routeIs('admin.gestion-personas')
                        ? 'background: var(--ui-primary-soft); color: var(--ui-primary);'
                        : 'color: var(--ui-muted);' }}"
                                onmouseover="this.style.background='var(--ui-primary-soft)'; this.style.color='var(--ui-primary)'"
                                onmouseout="this.style.background='{{ request()->routeIs('admin.gestion-personas') ? 'var(--ui-primary-soft)' : 'transparent' }}'; this.style.color='{{ request()->routeIs('admin.gestion-personas') ? 'var(--ui-primary)' : 'var(--ui-muted)' }}'">

                                {{-- Registro de personas / expediente --}}
                                <svg class="h-5 w-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"
                                        d="M15 9h3.75M15 12h3.75M15 15h3.75M4.5 19.5h15A1.5 1.5 0 0 0 21 18V6a1.5 1.5 0 0 0-1.5-1.5h-15A1.5 1.5 0 0 0 3 6v12a1.5 1.5 0 0 0 1.5 1.5Zm6-10.125a2.625 2.625 0 1 1-5.25 0 2.625 2.625 0 0 1 5.25 0ZM12 17.25c-.9-1.285-2.395-2.25-4.125-2.25S4.65 15.965 3.75 17.25" />
                                </svg>

                                <span x-show="sidebarOpen" x-cloak>Registro de personas</span>
                            </a>
                    @endcan

                    @can('Personal_Institucional')
                            <a href="{{ route('admin.personal-institucional') }}"
                                class="flex items-center gap-3 rounded-xl px-3 py-2 text-sm font-medium transition" style="{{ request()->routeIs('admin.personal-institucional')
                        ? 'background: var(--ui-primary-soft); color: var(--ui-primary);'
                        : 'color: var(--ui-muted);' }}"
                                onmouseover="this.style.background='var(--ui-primary-soft)'; this.style.color='var(--ui-primary)'"
                                onmouseout="this.style.background='{{ request()->routeIs('admin.personal-institucional') ? 'var(--ui-primary-soft)' : 'transparent' }}'; this.style.color='{{ request()->routeIs('admin.personal-institucional') ? 'var(--ui-primary)' : 'var(--ui-muted)' }}'">

                                {{-- Personal institucional / edificio académico --}}
                                <svg class="h-5 w-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"
                                        d="M3.75 21h16.5M4.5 21V7.5A2.25 2.25 0 0 1 6.75 5.25h10.5A2.25 2.25 0 0 1 19.5 7.5V21M9 8.25h1.5M13.5 8.25H15M9 12h1.5m3 0H15M9 15.75h1.5m3 0H15M11.25 21v-3h1.5v3" />
                                </svg>

                                <span x-show="sidebarOpen" x-cloak>Personal institucional</span>
                            </a>
                    @endcan

                    @can('Estudiantes')
                        <a href="{{ route('admin.gestion-estudiantes') }}"
                            class="flex items-center gap-3 rounded-xl px-3 py-2 text-sm font-medium transition hover:bg-[var(--ui-primary-soft)]"
                            style="color: var(--ui-muted);"
                            onmouseover="this.style.color='var(--ui-primary)'"
                            onmouseout="this.style.color='var(--ui-muted)'">

                            {{-- Estudiantes --}}
                            <svg class="h-5 w-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"
                                    d="M4.26 10.147 12 5.625l7.74 4.522L12 14.67l-7.74-4.523Z" />
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"
                                    d="M5.25 11.25v4.875c0 .621.504 1.125 1.125 1.125h11.25c.621 0 1.125-.504 1.125-1.125V11.25M12 14.625V21" />
                            </svg>

                            <span x-show="sidebarOpen" x-cloak>Estudiantes</span>
                        </a>
                    @endcan
                    @can('Bitacora')
                        <a href="{{ route('admin.bitacora') }}"
                            class="flex items-center gap-3 rounded-xl px-3 py-2 text-sm font-medium transition hover:bg-[var(--ui-primary-soft)]"
                            style="color: var(--ui-muted);" onmouseover="this.style.color='var(--ui-primary)'"
                            onmouseout="this.style.color='var(--ui-muted)'">

                            {{-- Bitácora / auditoría --}}
                            <svg class="h-5 w-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"
                                    d="M12 6v6l3 2M21 12A9 9 0 1 1 3 12a9 9 0 0 1 18 0Z" />
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"
                                    d="M7.5 3.75 6 2.25m10.5 1.5L18 2.25" />
                            </svg>

                            <span x-show="sidebarOpen" x-cloak>Bitácora</span>
                        </a>
                    @endcan
                </div>
            </div>
        @endcanany

        {{-- ACADÉMICO --}}
        @canany(['Gestion_Academica', 'Cursos', 'Paralelos', 'Turnos', 'Asignaturas', 'Especialidades_Tecnicas', 'Periodo_Evaluacion', 'Planes_Asignatura'])
            <div x-data="{ openAcademico: false }">
                <button type="button" @click="openAcademico = !openAcademico"
                    class="flex w-full items-center justify-between rounded-2xl px-3 py-3 text-sm font-semibold transition hover:bg-[var(--ui-surface-muted)]"
                    style="color: var(--ui-text-soft);">

                    <div class="flex items-center gap-3">
                        {{-- Académico / libro abierto --}}
                        <svg class="h-5 w-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"
                                d="M12 6.75c-2.25-1.5-5.25-1.5-7.5 0v11.25c2.25-1.5 5.25-1.5 7.5 0m0-11.25c2.25-1.5 5.25-1.5 7.5 0v11.25c-2.25-1.5-5.25-1.5-7.5 0m0-11.25v11.25" />
                        </svg>

                        <span x-show="sidebarOpen" x-cloak>Académico</span>
                    </div>

                    <svg x-show="sidebarOpen" x-cloak xmlns="http://www.w3.org/2000/svg"
                        class="h-4 w-4 shrink-0 transition-transform duration-300" :class="{ 'rotate-180': openAcademico }"
                        fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M19 9l-7 7-7-7" />
                    </svg>
                </button>

                <div x-show="openAcademico" x-transition:enter="transition ease-out duration-200"
                    x-transition:enter-start="opacity-0 -translate-y-1" x-transition:enter-end="opacity-100 translate-y-0"
                    x-transition:leave="transition ease-in duration-150"
                    x-transition:leave-start="opacity-100 translate-y-0" x-transition:leave-end="opacity-0 -translate-y-1"
                    class="mt-2 space-y-1 pl-3">

                    @can('Gestion_Academica')
                        <a href="{{ route('admin.gestion-academica') }}"
                            class="flex items-center gap-3 rounded-xl px-3 py-2 text-sm font-medium transition hover:bg-[var(--ui-info-soft)]"
                            style="color: var(--ui-muted);" onmouseover="this.style.color='var(--ui-info)'"
                            onmouseout="this.style.color='var(--ui-muted)'">

                            {{-- Gestión académica / calendario --}}
                            <svg class="h-5 w-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"
                                    d="M6.75 3v2.25m10.5-2.25v2.25M3.75 8.25h16.5M5.25 5.25h13.5A1.5 1.5 0 0 1 20.25 6.75v12A1.5 1.5 0 0 1 18.75 20.25H5.25A1.5 1.5 0 0 1 3.75 18.75v-12A1.5 1.5 0 0 1 5.25 5.25Z" />
                            </svg>

                            <span x-show="sidebarOpen" x-cloak>Gestión académica</span>
                        </a>
                    @endcan

                    @can('Cursos')
                        <a href="#"
                            class="flex items-center gap-3 rounded-xl px-3 py-2 text-sm font-medium transition hover:bg-[var(--ui-info-soft)]"
                            style="color: var(--ui-muted);" onmouseover="this.style.color='var(--ui-info)'"
                            onmouseout="this.style.color='var(--ui-muted)'">

                            {{-- Cursos / niveles --}}
                            <svg class="h-5 w-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"
                                    d="M4.5 6.75h15M4.5 12h15M4.5 17.25h15M7.5 4.5v15M16.5 4.5v15" />
                            </svg>

                            <span x-show="sidebarOpen" x-cloak>Cursos</span>
                        </a>
                    @endcan

                    @can('Paralelos')
                        <a href="#"
                            class="flex items-center gap-3 rounded-xl px-3 py-2 text-sm font-medium transition hover:bg-[var(--ui-info-soft)]"
                            style="color: var(--ui-muted);" onmouseover="this.style.color='var(--ui-info)'"
                            onmouseout="this.style.color='var(--ui-muted)'">

                            {{-- Paralelos / ramas --}}
                            <svg class="h-5 w-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"
                                    d="M6 6h.01M6 18h.01M18 6h.01M18 18h.01M6 6h12M6 18h12M12 6v12" />
                            </svg>

                            <span x-show="sidebarOpen" x-cloak>Paralelos</span>
                        </a>
                    @endcan

                    @can('Turnos')
                        <a href="#"
                            class="flex items-center gap-3 rounded-xl px-3 py-2 text-sm font-medium transition hover:bg-[var(--ui-info-soft)]"
                            style="color: var(--ui-muted);" onmouseover="this.style.color='var(--ui-info)'"
                            onmouseout="this.style.color='var(--ui-muted)'">

                            {{-- Turnos / reloj --}}
                            <svg class="h-5 w-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"
                                    d="M12 6v6l4 2M21 12A9 9 0 1 1 3 12a9 9 0 0 1 18 0Z" />
                            </svg>

                            <span x-show="sidebarOpen" x-cloak>Turnos</span>
                        </a>
                    @endcan

                    @can('Asignaturas')
                        <a href="#"
                            class="flex items-center gap-3 rounded-xl px-3 py-2 text-sm font-medium transition hover:bg-[var(--ui-info-soft)]"
                            style="color: var(--ui-muted);" onmouseover="this.style.color='var(--ui-info)'"
                            onmouseout="this.style.color='var(--ui-muted)'">

                            {{-- Asignaturas / documento académico --}}
                            <svg class="h-5 w-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"
                                    d="M19.5 14.25v-7.5A2.25 2.25 0 0 0 17.25 4.5H6.75A2.25 2.25 0 0 0 4.5 6.75v10.5A2.25 2.25 0 0 0 6.75 19.5h6.75M8.25 8.25h7.5M8.25 12h7.5M8.25 15.75h3.75" />
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"
                                    d="M15 18.75 17.25 21 21 16.5" />
                            </svg>

                            <span x-show="sidebarOpen" x-cloak>Asignaturas</span>
                        </a>
                    @endcan

                    @can('Especialidades_Tecnicas')
                        <a href="#"
                            class="flex items-center gap-3 rounded-xl px-3 py-2 text-sm font-medium transition hover:bg-[var(--ui-info-soft)]"
                            style="color: var(--ui-muted);" onmouseover="this.style.color='var(--ui-info)'"
                            onmouseout="this.style.color='var(--ui-muted)'">

                            {{-- Especialidades técnicas / herramientas --}}
                            <svg class="h-5 w-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"
                                    d="M11.42 15.17 17.25 21A2.652 2.652 0 0 0 21 17.25l-5.83-5.83M11.42 15.17 5.86 20.73a2.121 2.121 0 0 1-3-3l5.56-5.56M11.42 15.17l3.75-3.75M8.25 8.25l-2.5-2.5L3 8.5 5.5 11l2.75-2.75Zm8.25-2.25 1.5-1.5 1.5 1.5-1.5 1.5-1.5-1.5Z" />
                            </svg>

                            <span x-show="sidebarOpen" x-cloak>Especialidades técnicas</span>
                        </a>
                    @endcan

                    @can('Periodo_Evaluacion')
                        <a href="#"
                            class="flex items-center gap-3 rounded-xl px-3 py-2 text-sm font-medium transition hover:bg-[var(--ui-info-soft)]"
                            style="color: var(--ui-muted);" onmouseover="this.style.color='var(--ui-info)'"
                            onmouseout="this.style.color='var(--ui-muted)'">

                            {{-- Periodo evaluación / reloj calendario --}}
                            <svg class="h-5 w-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"
                                    d="M6.75 3v2.25m10.5-2.25v2.25M3.75 8.25h16.5M7.5 12h3m-3 3h6M5.25 5.25h13.5A1.5 1.5 0 0 1 20.25 6.75v12A1.5 1.5 0 0 1 18.75 20.25H5.25A1.5 1.5 0 0 1 3.75 18.75v-12A1.5 1.5 0 0 1 5.25 5.25Z" />
                            </svg>

                            <span x-show="sidebarOpen" x-cloak>Periodo de evaluación</span>
                        </a>
                    @endcan

                    @can('Planes_Asignatura')
                        <a href="#"
                            class="flex items-center gap-3 rounded-xl px-3 py-2 text-sm font-medium transition hover:bg-[var(--ui-info-soft)]"
                            style="color: var(--ui-muted);" onmouseover="this.style.color='var(--ui-info)'"
                            onmouseout="this.style.color='var(--ui-muted)'">

                            {{-- Planes / planificación --}}
                            <svg class="h-5 w-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"
                                    d="M9 6.75h11.25M9 12h11.25M9 17.25h11.25M3.75 6.75h.008v.008H3.75V6.75Zm0 5.25h.008v.008H3.75V12Zm0 5.25h.008v.008H3.75v-.008Z" />
                            </svg>

                            <span x-show="sidebarOpen" x-cloak>Planes de asignatura</span>
                        </a>
                    @endcan
                </div>
            </div>
        @endcanany

        {{-- COMUNIDAD EDUCATIVA --}}
        @canany(['Estudiantes', 'Docentes', 'Inscripciones', 'Institucion_Procedencia', 'Tipo_Vinculacion_Estudiante'])
            <div x-data="{ openComunidad: false }">
                <button type="button" @click="openComunidad = !openComunidad"
                    class="flex w-full items-center justify-between rounded-2xl px-3 py-3 text-sm font-semibold transition hover:bg-[var(--ui-surface-muted)]"
                    style="color: var(--ui-text-soft);">

                    <div class="flex items-center gap-3">
                        {{-- Comunidad educativa / personas --}}
                        <svg class="h-5 w-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"
                                d="M18 18.72a9.094 9.094 0 0 0 3.75-1.32 4.5 4.5 0 0 0-7.5-3.35M6 18.72a9.094 9.094 0 0 1-3.75-1.32 4.5 4.5 0 0 1 7.5-3.35M15 7.5a3 3 0 1 1-6 0 3 3 0 0 1 6 0Zm-9 3a2.25 2.25 0 1 1 0-4.5 2.25 2.25 0 0 1 0 4.5Zm12 0a2.25 2.25 0 1 1 0-4.5 2.25 2.25 0 0 1 0 4.5Zm-9 5.25h6A3.75 3.75 0 0 1 18.75 19.5v.75H5.25v-.75A3.75 3.75 0 0 1 9 15.75Z" />
                        </svg>

                        <span x-show="sidebarOpen" x-cloak>Comunidad educativa</span>
                    </div>

                    <svg x-show="sidebarOpen" x-cloak xmlns="http://www.w3.org/2000/svg"
                        class="h-4 w-4 shrink-0 transition-transform duration-300" :class="{ 'rotate-180': openComunidad }"
                        fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M19 9l-7 7-7-7" />
                    </svg>
                </button>

                <div x-show="openComunidad" x-transition:enter="transition ease-out duration-200"
                    x-transition:enter-start="opacity-0 -translate-y-1" x-transition:enter-end="opacity-100 translate-y-0"
                    x-transition:leave="transition ease-in duration-150"
                    x-transition:leave-start="opacity-100 translate-y-0" x-transition:leave-end="opacity-0 -translate-y-1"
                    class="mt-2 space-y-1 pl-3">

                    @can('Estudiantes')
                        <a href="#"
                            class="flex items-center gap-3 rounded-xl px-3 py-2 text-sm font-medium transition hover:bg-[var(--ui-primary-soft)]"
                            style="color: var(--ui-muted);" onmouseover="this.style.color='var(--ui-primary)'"
                            onmouseout="this.style.color='var(--ui-muted)'">

                            {{-- Estudiantes --}}
                            <svg class="h-5 w-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"
                                    d="M4.26 10.147 12 5.625l7.74 4.522L12 14.67l-7.74-4.523Z" />
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"
                                    d="M5.25 11.25v4.875c0 .621.504 1.125 1.125 1.125h11.25c.621 0 1.125-.504 1.125-1.125V11.25" />
                            </svg>

                            <span x-show="sidebarOpen" x-cloak>Estudiantes</span>
                        </a>
                    @endcan

                    @can('Docentes')
                        <a href="#"
                            class="flex items-center gap-3 rounded-xl px-3 py-2 text-sm font-medium transition hover:bg-[var(--ui-primary-soft)]"
                            style="color: var(--ui-muted);" onmouseover="this.style.color='var(--ui-primary)'"
                            onmouseout="this.style.color='var(--ui-muted)'">

                            {{-- Docentes / profesor --}}
                            <svg class="h-5 w-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"
                                    d="M12 6.75a3 3 0 1 1-6 0 3 3 0 0 1 6 0ZM3.75 20.25a5.25 5.25 0 0 1 10.5 0M14.25 6h6M14.25 9h6M14.25 12h4.5" />
                            </svg>

                            <span x-show="sidebarOpen" x-cloak>Docentes</span>
                        </a>
                    @endcan

                    @can('Inscripciones')
                        <a href="#"
                            class="flex items-center gap-3 rounded-xl px-3 py-2 text-sm font-medium transition hover:bg-[var(--ui-primary-soft)]"
                            style="color: var(--ui-muted);" onmouseover="this.style.color='var(--ui-primary)'"
                            onmouseout="this.style.color='var(--ui-muted)'">

                            {{-- Inscripciones / documento firmado --}}
                            <svg class="h-5 w-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"
                                    d="M6.75 3.75h7.5L19.5 9v11.25H6.75V3.75Z" />
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"
                                    d="M14.25 3.75V9h5.25M8.25 13.5h6M8.25 16.5h4.5M15 18.75l1.5 1.5L20.25 16.5" />
                            </svg>

                            <span x-show="sidebarOpen" x-cloak>Inscripciones</span>
                        </a>
                    @endcan

                    @can('Institucion_Procedencia')
                        <a href="#"
                            class="flex items-center gap-3 rounded-xl px-3 py-2 text-sm font-medium transition hover:bg-[var(--ui-primary-soft)]"
                            style="color: var(--ui-muted);" onmouseover="this.style.color='var(--ui-primary)'"
                            onmouseout="this.style.color='var(--ui-muted)'">

                            {{-- Institución procedencia / edificio externo --}}
                            <svg class="h-5 w-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"
                                    d="M3.75 21h16.5M6 21V6.75L12 3l6 3.75V21M9 9.75h.01M12 9.75h.01M15 9.75h.01M9 13.5h.01M12 13.5h.01M15 13.5h.01M10.5 21v-3h3v3" />
                            </svg>

                            <span x-show="sidebarOpen" x-cloak>Institución de procedencia</span>
                        </a>
                    @endcan

                    @can('Tipo_Vinculacion_Estudiante')
                        <a href="#"
                            class="flex items-center gap-3 rounded-xl px-3 py-2 text-sm font-medium transition hover:bg-[var(--ui-primary-soft)]"
                            style="color: var(--ui-muted);" onmouseover="this.style.color='var(--ui-primary)'"
                            onmouseout="this.style.color='var(--ui-muted)'">

                            {{-- Tipo vinculación / enlace --}}
                            <svg class="h-5 w-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"
                                    d="M13.19 8.688a4.5 4.5 0 0 1 6.364 6.364l-1.768 1.768a4.5 4.5 0 0 1-6.364-6.364M10.81 15.312a4.5 4.5 0 0 1-6.364-6.364L6.214 7.18a4.5 4.5 0 0 1 6.364 6.364" />
                            </svg>

                            <span x-show="sidebarOpen" x-cloak>Tipo de vinculación</span>
                        </a>
                    @endcan
                </div>
            </div>
        @endcanany

        {{-- EVALUACIÓN Y REPORTES --}}
        @canany(['Calificaciones', 'Reportes_Academicos', 'Reportes_Administrativos'])
            <div x-data="{ openEvaluacion: false }">
                <button type="button" @click="openEvaluacion = !openEvaluacion"
                    class="flex w-full items-center justify-between rounded-2xl px-3 py-3 text-sm font-semibold transition hover:bg-[var(--ui-surface-muted)]"
                    style="color: var(--ui-text-soft);">

                    <div class="flex items-center gap-3">
                        {{-- Evaluación / gráfico con check --}}
                        <svg class="h-5 w-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"
                                d="M3 3v18h18M7.5 15.75v-4.5M12 15.75V7.5M16.5 15.75v-2.25M15.75 19.5l1.5 1.5L21 17.25" />
                        </svg>

                        <span x-show="sidebarOpen" x-cloak>Evaluación y reportes</span>
                    </div>

                    <svg x-show="sidebarOpen" x-cloak xmlns="http://www.w3.org/2000/svg"
                        class="h-4 w-4 shrink-0 transition-transform duration-300" :class="{ 'rotate-180': openEvaluacion }"
                        fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M19 9l-7 7-7-7" />
                    </svg>
                </button>

                <div x-show="openEvaluacion" x-transition:enter="transition ease-out duration-200"
                    x-transition:enter-start="opacity-0 -translate-y-1" x-transition:enter-end="opacity-100 translate-y-0"
                    x-transition:leave="transition ease-in duration-150"
                    x-transition:leave-start="opacity-100 translate-y-0" x-transition:leave-end="opacity-0 -translate-y-1"
                    class="mt-2 space-y-1 pl-3">

                    @can('Calificaciones')
                        <a href="#"
                            class="flex items-center gap-3 rounded-xl px-3 py-2 text-sm font-medium transition hover:bg-[var(--ui-info-soft)]"
                            style="color: var(--ui-muted);" onmouseover="this.style.color='var(--ui-info)'"
                            onmouseout="this.style.color='var(--ui-muted)'">

                            {{-- Calificaciones / nota --}}
                            <svg class="h-5 w-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"
                                    d="M9 12.75 11.25 15 15 9.75M6.75 3.75h10.5A2.25 2.25 0 0 1 19.5 6v12A2.25 2.25 0 0 1 17.25 20.25H6.75A2.25 2.25 0 0 1 4.5 18V6A2.25 2.25 0 0 1 6.75 3.75Z" />
                            </svg>

                            <span x-show="sidebarOpen" x-cloak>Calificaciones</span>
                        </a>
                    @endcan

                    @can('Reportes_Academicos')
                        <a href="#"
                            class="flex items-center gap-3 rounded-xl px-3 py-2 text-sm font-medium transition hover:bg-[var(--ui-info-soft)]"
                            style="color: var(--ui-muted);" onmouseover="this.style.color='var(--ui-info)'"
                            onmouseout="this.style.color='var(--ui-muted)'">

                            {{-- Reportes académicos / documento --}}
                            <svg class="h-5 w-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"
                                    d="M6.75 3.75h7.5L19.5 9v11.25H6.75V3.75Z" />
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"
                                    d="M14.25 3.75V9h5.25M8.25 13.5h7.5M8.25 16.5h5.25" />
                            </svg>

                            <span x-show="sidebarOpen" x-cloak>Reportes académicos</span>
                        </a>
                    @endcan

                    @can('Reportes_Administrativos')
                        <a href="#"
                            class="flex items-center gap-3 rounded-xl px-3 py-2 text-sm font-medium transition hover:bg-[var(--ui-info-soft)]"
                            style="color: var(--ui-muted);" onmouseover="this.style.color='var(--ui-info)'"
                            onmouseout="this.style.color='var(--ui-muted)'">

                            {{-- Reportes administrativos / gráfico --}}
                            <svg class="h-5 w-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"
                                    d="M3.75 3.75v16.5h16.5M7.5 16.5v-4.5M12 16.5V7.5M16.5 16.5v-7.5" />
                            </svg>

                            <span x-show="sidebarOpen" x-cloak>Reportes administrativos</span>
                        </a>
                    @endcan
                </div>
            </div>
        @endcanany
    </nav>

    {{-- FOOTER --}}
    <div class="border-t p-3" style="border-color: var(--ui-border);">
        @can('Mi_Perfil')
            <a href="{{ route('profile.show') }}"
                class="flex items-center gap-3 rounded-2xl px-3 py-3 text-sm font-semibold transition hover:bg-[var(--ui-surface-muted)]"
                style="color: var(--ui-text-soft);">

                {{-- Perfil --}}
                <svg class="h-5 w-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"
                        d="M15.75 6a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0ZM4.5 20.25a8.25 8.25 0 1 1 15 0" />
                </svg>

                <span x-show="sidebarOpen" x-cloak>Mi perfil</span>
            </a>
        @endcan

        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button type="submit"
                class="mt-2 flex w-full items-center gap-3 rounded-2xl px-3 py-3 text-sm font-semibold transition"
                style="color: var(--ui-danger);" onmouseover="this.style.background='var(--ui-danger-soft)'"
                onmouseout="this.style.background='transparent'">

                {{-- Cerrar sesión --}}
                <svg class="h-5 w-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"
                        d="M15.75 9V5.25A2.25 2.25 0 0 0 13.5 3h-6A2.25 2.25 0 0 0 5.25 5.25v13.5A2.25 2.25 0 0 0 7.5 21h6a2.25 2.25 0 0 0 2.25-2.25V15m3 0 3-3m0 0-3-3m3 3H9" />
                </svg>

                <span x-show="sidebarOpen" x-cloak>Cerrar sesión</span>
            </button>
        </form>
    </div>
</div>