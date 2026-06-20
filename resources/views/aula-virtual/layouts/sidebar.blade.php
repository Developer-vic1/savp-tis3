@php
    $user = Auth::user();
    $rolAula = $user?->can('Aula_Virtual_Docente') || $user?->hasRole('Docente')
        ? 'Docente'
        : ($user?->can('Aula_Virtual_Estudiante') || $user?->hasRole('Estudiante') ? 'Estudiante' : 'Aula Virtual');

    $linksEstudiante = [
        ['perm' => 'Mis_Asignaturas', 'label' => 'Mis asignaturas'],
        ['perm' => 'Actividades_Aula', 'label' => 'Actividades'],
        ['perm' => 'Cuestionarios_Aula', 'label' => 'Cuestionarios'],
        ['perm' => 'Mis_Archivos', 'label' => 'Mis archivos'],
        ['perm' => 'Calificaciones_Aula', 'label' => 'Calificaciones'],
        ['perm' => 'Calendario_Aula', 'label' => 'Calendario'],
        ['perm' => 'Notificaciones_Aula', 'label' => 'Notificaciones'],
        ['perm' => 'Perfil_Academico', 'label' => 'Perfil academico'],
        ['perm' => 'Orientacion_Academica_Profesional', 'label' => 'Orientacion academica-profesional'],
        ['perm' => 'Seguridad_Cuenta', 'label' => 'Seguridad de la cuenta'],
    ];

    $linksDocente = [
        ['perm' => 'Mis_Cursos', 'label' => 'Mis cursos'],
        ['perm' => 'Estudiantes_Curso', 'label' => 'Estudiantes'],
        ['perm' => 'Materiales_Aula', 'label' => 'Materiales'],
        ['perm' => 'Actividades_Aula', 'label' => 'Actividades'],
        ['perm' => 'Cuestionarios_Aula', 'label' => 'Cuestionarios'],
        ['perm' => 'Asistencia_Aula', 'label' => 'Asistencia'],
        ['perm' => 'Calificaciones_Aula', 'label' => 'Calificaciones'],
        ['perm' => 'Calendario_Aula', 'label' => 'Calendario'],
        ['perm' => 'Notificaciones_Aula', 'label' => 'Notificaciones'],
        ['perm' => 'Reportes_Aula', 'label' => 'Reportes'],
        ['perm' => 'Seguridad_Cuenta', 'label' => 'Seguridad de la cuenta'],
    ];
@endphp

<div x-show="mobileSidebar" x-cloak class="fixed inset-0 z-40 bg-slate-950/45 backdrop-blur-sm lg:hidden"
    @click="mobileSidebar = false"></div>

<aside class="fixed left-0 top-0 z-50 flex h-screen flex-col border-r shadow-md transition-all duration-300 lg:z-40"
    :class="[
        sidebarOpen ? 'lg:w-72' : 'lg:w-20',
        mobileSidebar ? 'translate-x-0 w-72' : '-translate-x-full w-72 lg:translate-x-0'
    ]"
    style="background: color-mix(in srgb, var(--ui-surface) 94%, transparent); border-color: var(--ui-border); color: var(--ui-text);">
    <div class="flex items-center justify-between border-b p-4" style="border-color: var(--ui-border);">
        <a href="{{ route('aula-virtual.inicio') }}" class="flex min-w-0 items-center gap-3">
            <div class="flex h-11 w-11 shrink-0 items-center justify-center rounded-2xl shadow-sm ring-1"
                style="background: var(--ui-surface); --tw-ring-color: var(--ui-border);">
                <img src="{{ asset('image/LOGO FT3 A.jpg') }}" alt="Logo Franz Tamayo" class="h-8 w-8 object-contain">
            </div>

            <div x-show="sidebarOpen || mobileSidebar" x-cloak class="min-w-0">
                <p class="truncate text-sm font-black">Aula Virtual</p>
                <p class="text-[11px] font-semibold uppercase tracking-[0.16em]" style="color: var(--ui-primary);">
                    SAVP - TIS 3
                </p>
            </div>
        </a>

        <button type="button" class="ui-icon-btn hidden lg:inline-flex" @click="sidebarOpen = !sidebarOpen"
            title="Contraer menu">
            <svg class="h-5 w-5 transition-transform duration-300" :class="{ 'rotate-180': !sidebarOpen }"
                fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M15 19l-7-7 7-7" />
            </svg>
        </button>

        <button type="button" class="ui-icon-btn lg:hidden" @click="mobileSidebar = false" title="Cerrar menu">
            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M6 18 18 6M6 6l12 12" />
            </svg>
        </button>
    </div>

    <div class="px-4 pt-4">
        <div class="overflow-hidden rounded-2xl bg-gradient-to-r from-emerald-600 to-sky-600 p-4 text-white shadow-sm">
            <div x-show="sidebarOpen || mobileSidebar" x-cloak>
                <p class="text-xs font-semibold uppercase tracking-[0.18em] text-emerald-100">Perfil LMS</p>
                <p class="mt-2 text-lg font-black">{{ $rolAula }}</p>
                <p class="mt-1 text-sm leading-5 text-white/90">Acceso exclusivo mediante cuenta Google registrada.</p>
            </div>

            <div x-show="!sidebarOpen && !mobileSidebar" x-cloak class="text-center text-lg font-black">
                {{ strtoupper(substr($rolAula, 0, 1)) }}
            </div>
        </div>
    </div>

    <nav class="ui-scrollbar flex-1 space-y-3 overflow-y-auto px-3 py-4">
        <div>
            <p x-show="sidebarOpen || mobileSidebar" x-cloak class="mb-2 px-2 text-[11px] font-semibold uppercase tracking-[0.18em]"
                style="color: var(--ui-muted);">Principal</p>

            <a href="{{ route('aula-virtual.inicio') }}"
                class="group flex items-center gap-3 rounded-2xl px-3 py-3 text-sm font-semibold transition"
                style="{{ request()->routeIs('aula-virtual.inicio') ? 'background: var(--ui-primary-soft); color: var(--ui-primary);' : 'color: var(--ui-text-soft);' }}">
                <svg class="h-5 w-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"
                        d="M3 10.5 12 3l9 7.5M5.25 9.75V21h13.5V9.75M9 21v-6h6v6" />
                </svg>
                <span x-show="sidebarOpen || mobileSidebar" x-cloak>Inicio</span>
            </a>
        </div>

        @canany(['Aula_Virtual_Estudiante', 'Mis_Asignaturas', 'Actividades_Aula', 'Cuestionarios_Aula', 'Mis_Archivos', 'Calificaciones_Aula', 'Calendario_Aula', 'Notificaciones_Aula', 'Perfil_Academico', 'Orientacion_Academica_Profesional'])
            <div>
                <p x-show="sidebarOpen || mobileSidebar" x-cloak class="mb-2 px-2 text-[11px] font-semibold uppercase tracking-[0.18em]"
                    style="color: var(--ui-muted);">Estudiante</p>

                <div class="space-y-1">
                    @foreach ($linksEstudiante as $link)
                        @can($link['perm'])
                            <a href="#"
                                class="flex items-center gap-3 rounded-xl px-3 py-2.5 text-sm font-medium transition hover:bg-[var(--ui-primary-soft)]"
                                style="color: var(--ui-muted);"
                                onmouseover="this.style.color='var(--ui-primary)'"
                                onmouseout="this.style.color='var(--ui-muted)'">
                                <svg class="h-5 w-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"
                                        d="M9 6.75h11.25M9 12h11.25M9 17.25h11.25M3.75 6.75h.008v.008H3.75V6.75Zm0 5.25h.008v.008H3.75V12Zm0 5.25h.008v.008H3.75v-.008Z" />
                                </svg>
                                <span x-show="sidebarOpen || mobileSidebar" x-cloak>{{ $link['label'] }}</span>
                            </a>
                        @endcan
                    @endforeach
                </div>
            </div>
        @endcanany

        @canany(['Aula_Virtual_Docente', 'Mis_Cursos', 'Estudiantes_Curso', 'Materiales_Aula', 'Actividades_Aula', 'Cuestionarios_Aula', 'Asistencia_Aula', 'Calificaciones_Aula', 'Calendario_Aula', 'Notificaciones_Aula', 'Reportes_Aula'])
            <div>
                <p x-show="sidebarOpen || mobileSidebar" x-cloak class="mb-2 px-2 text-[11px] font-semibold uppercase tracking-[0.18em]"
                    style="color: var(--ui-muted);">Docente</p>

                <div class="space-y-1">
                    @foreach ($linksDocente as $link)
                        @can($link['perm'])
                            <a href="#"
                                class="flex items-center gap-3 rounded-xl px-3 py-2.5 text-sm font-medium transition hover:bg-[var(--ui-info-soft)]"
                                style="color: var(--ui-muted);"
                                onmouseover="this.style.color='var(--ui-info)'"
                                onmouseout="this.style.color='var(--ui-muted)'">
                                <svg class="h-5 w-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"
                                        d="M12 6.75c-2.25-1.5-5.25-1.5-7.5 0v11.25c2.25-1.5 5.25-1.5 7.5 0m0-11.25c2.25-1.5 5.25-1.5 7.5 0v11.25c-2.25-1.5-5.25-1.5-7.5 0m0-11.25v11.25" />
                                </svg>
                                <span x-show="sidebarOpen || mobileSidebar" x-cloak>{{ $link['label'] }}</span>
                            </a>
                        @endcan
                    @endforeach
                </div>
            </div>
        @endcanany
    </nav>

    <div class="border-t p-3" style="border-color: var(--ui-border);">
        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button type="submit" class="flex w-full items-center gap-3 rounded-2xl px-3 py-3 text-sm font-semibold transition"
                style="color: var(--ui-danger);"
                onmouseover="this.style.background='var(--ui-danger-soft)'"
                onmouseout="this.style.background='transparent'">
                <svg class="h-5 w-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"
                        d="M15.75 9V5.25A2.25 2.25 0 0 0 13.5 3h-6A2.25 2.25 0 0 0 5.25 5.25v13.5A2.25 2.25 0 0 0 7.5 21h6a2.25 2.25 0 0 0 2.25-2.25V15m3 0 3-3m0 0-3-3m3 3H9" />
                </svg>
                <span x-show="sidebarOpen || mobileSidebar" x-cloak>Cerrar sesion</span>
            </button>
        </form>
    </div>
</aside>
