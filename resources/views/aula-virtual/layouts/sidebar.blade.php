@php
    $user = Auth::user();

    /*
    |--------------------------------------------------------------------------
    | Perfil visible dentro del Aula Virtual
    |--------------------------------------------------------------------------
    | El rol mostrado es únicamente informativo.
    | La seguridad real continúa dependiendo de Spatie + rutas + Policies.
    */
    if ($user?->hasRole('Super Admin') || $user?->hasRole('Admin')) {
        $rolAula = 'Administrador';
    } elseif ($user?->can('Aula_Virtual_Docente') || $user?->hasRole('Docente')) {
        $rolAula = 'Docente';
    } elseif ($user?->can('Aula_Virtual_Estudiante') || $user?->hasRole('Estudiante')) {
        $rolAula = 'Estudiante';
    } else {
        $rolAula = 'Aula Virtual';
    }

    /*
    |--------------------------------------------------------------------------
    | Navegación del estudiante
    |--------------------------------------------------------------------------
    | icon:
    |   Identifica el SVG que se mostrará.
    |
    | active:
    |   Permite destacar correctamente la opción cuando se navega por
    |   subrutas relacionadas.
    */
    $linksEstudiante = [
        [
            'perm' => 'Mis_Asignaturas',
            'label' => 'Mis asignaturas',
            'route' => 'aula-virtual.estudiante.asignaturas',
            'icon' => 'asignaturas',
            'active' => ['aula-virtual.estudiante.asignaturas', 'aula-virtual.estudiante.curso*'],
        ],
        [
            'perm' => 'Tareas_Aula',
            'label' => 'Actividades pendientes',
            'route' => 'aula-virtual.inicio',
            'icon' => 'tareas',
            'active' => ['aula-virtual.estudiante.tareas*', 'aula-virtual.estudiante.actividades*'],
        ],
        [
            'perm' => 'Materiales_Aula',
            'label' => 'Materiales',
            'route' => 'aula-virtual.estudiante.asignaturas',
            'icon' => 'materiales',
            'active' => ['aula-virtual.estudiante.materiales*'],
        ],
        [
            'perm' => 'Calificaciones_Aula',
            'label' => 'Calificaciones',
            'route' => 'aula-virtual.estudiante.asignaturas',
            'icon' => 'calificaciones',
            'active' => ['aula-virtual.estudiante.calificaciones*'],
        ],
        [
            'perm' => 'Asistencia_Aula',
            'label' => 'Mi asistencia',
            'route' => 'aula-virtual.estudiante.asistencia',
            'icon' => 'asistencia',
            'active' => ['aula-virtual.estudiante.asistencia*'],
        ],
        [
            'perm' => 'Orientacion_Academica_Profesional',
            'label' => 'Orientación académica-profesional',
            'route' => 'aula-virtual.estudiante.orientacion',
            'icon' => 'orientacion',
            'active' => ['aula-virtual.estudiante.orientacion*'],
        ],
    ];

    /*
    |--------------------------------------------------------------------------
    | Navegación del docente
    |--------------------------------------------------------------------------
    */
    $linksDocente = [
        [
            'perm' => 'Mis_Cursos',
            'label' => 'Mis cursos',
            'route' => 'aula-virtual.docente.cursos',
            'icon' => 'cursos',
            'active' => ['aula-virtual.docente.cursos', 'aula-virtual.docente.curso*'],
        ],
        [
            'perm' => 'Materiales_Aula',
            'label' => 'Materiales',
            'route' => 'aula-virtual.docente.cursos',
            'icon' => 'materiales',
            'active' => ['aula-virtual.docente.materiales*'],
        ],
        [
            'perm' => 'Tareas_Aula',
            'label' => 'Tareas',
            'route' => 'aula-virtual.docente.cursos',
            'icon' => 'tareas',
            'active' => ['aula-virtual.docente.tareas*'],
        ],
        [
            'perm' => 'Entregas_Aula',
            'label' => 'Entregas',
            'route' => 'aula-virtual.docente.cursos',
            'icon' => 'entregas',
            'active' => ['aula-virtual.docente.entregas*'],
        ],
        [
            'perm' => 'Asistencia_Aula',
            'label' => 'Asistencia',
            'route' => 'aula-virtual.docente.cursos',
            'icon' => 'asistencia',
            'active' => ['aula-virtual.docente.asistencia*'],
        ],
        [
            'perm' => 'Calificaciones_Aula',
            'label' => 'Calificaciones',
            'route' => 'aula-virtual.docente.cursos',
            'icon' => 'calificaciones',
            'active' => ['aula-virtual.docente.calificaciones*', 'aula-virtual.docente.revisar*'],
        ],
        [
            'perm' => 'Reportes_Aula',
            'label' => 'Reportes',
            'route' => 'aula-virtual.docente.reportes',
            'icon' => 'reportes',
            'active' => ['aula-virtual.docente.reportes*'],
        ],
        [
            'perm' => 'Orientacion_Academica_Profesional',
            'label' => 'Seguimiento orientación',
            'route' => 'aula-virtual.docente.orientacion.seguimiento',
            'icon' => 'orientacion',
            'active' => ['aula-virtual.docente.orientacion*'],
        ],
    ];
@endphp

{{-- Overlay móvil --}}
<div x-show="mobileSidebar" x-cloak class="fixed inset-0 z-40 bg-slate-950/45 backdrop-blur-sm lg:hidden"
    @click="mobileSidebar = false" aria-hidden="true"></div>

{{-- Sidebar --}}
<aside class="fixed left-0 top-0 z-50 flex h-screen flex-col border-r shadow-md transition-all duration-300 lg:z-40"
    :class="[
        sidebarOpen ? 'lg:w-72' : 'lg:w-20',
        mobileSidebar ?
        'translate-x-0 w-72' :
        '-translate-x-full w-72 lg:translate-x-0'
    ]"
    style="
        background: color-mix(in srgb, var(--ui-surface) 94%, transparent);
        border-color: var(--ui-border);
        color: var(--ui-text);
    "
    aria-label="Navegación principal del Aula Virtual">
    {{-- ========================================================= --}}
    {{-- ENCABEZADO --}}
    {{-- ========================================================= --}}
    <div class="flex items-center justify-between border-b p-4" style="border-color: var(--ui-border);">
        <a href="{{ route('aula-virtual.inicio') }}" class="flex min-w-0 items-center gap-3"
            aria-label="Ir al inicio del Aula Virtual">
            <div class="flex h-11 w-11 shrink-0 items-center justify-center rounded-2xl shadow-sm ring-1"
                style="
                    background: var(--ui-surface);
                    --tw-ring-color: var(--ui-border);
                ">
                <img src="{{ asset('image/LOGO FT3 A.jpg') }}" alt="Logo Franz Tamayo" class="h-8 w-8 object-contain">
            </div>

            <div x-show="sidebarOpen || mobileSidebar" x-cloak class="min-w-0">
                <p class="truncate text-sm font-black">
                    SAVP-TIS3
                </p>

                <p class="text-[11px] font-semibold uppercase tracking-[0.16em]" style="color: var(--ui-primary);">
                    Aula Virtual
                </p>
            </div>
        </a>

        {{-- Contraer escritorio --}}
        <button type="button" class="ui-icon-btn hidden lg:inline-flex" @click="sidebarOpen = !sidebarOpen"
            :aria-label="sidebarOpen ? 'Contraer menú' : 'Expandir menú'"
            :title="sidebarOpen ? 'Contraer menú' : 'Expandir menú'">
            <svg class="h-5 w-5 transition-transform duration-300" :class="{ 'rotate-180': !sidebarOpen }"
                fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M15 19l-7-7 7-7" />
            </svg>
        </button>

        {{-- Cerrar móvil --}}
        <button type="button" class="ui-icon-btn lg:hidden" @click="mobileSidebar = false" title="Cerrar menú"
            aria-label="Cerrar menú">
            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M6 18 18 6M6 6l12 12" />
            </svg>
        </button>
    </div>

    {{-- ========================================================= --}}
    {{-- PERFIL --}}
    {{-- ========================================================= --}}
    <div class="px-4 pt-4">
        <div class="overflow-hidden rounded-xl p-4 text-white shadow-sm" style="background: var(--savp-green);">
            <div x-show="sidebarOpen || mobileSidebar" x-cloak>
                <p class="text-xs font-semibold uppercase tracking-[0.18em] text-emerald-100">
                    Perfil académico
                </p>

                <p class="mt-2 text-lg font-black">
                    {{ $rolAula }}
                </p>

                <p class="mt-1 text-sm leading-5 text-white/90">
                    Acceso según rol y permisos asignados.
                </p>
            </div>

            <div x-show="!sidebarOpen && !mobileSidebar" x-cloak class="text-center text-lg font-black"
                :title="'{{ $rolAula }}'">
                {{ strtoupper(mb_substr($rolAula, 0, 1)) }}
            </div>
        </div>
    </div>

    {{-- ========================================================= --}}
    {{-- NAVEGACIÓN --}}
    {{-- ========================================================= --}}
    <nav class="ui-scrollbar flex-1 space-y-4 overflow-y-auto px-3 py-4">
        {{-- ===================================================== --}}
        {{-- PRINCIPAL --}}
        {{-- ===================================================== --}}
        <div>
            <p x-show="sidebarOpen || mobileSidebar" x-cloak
                class="mb-2 px-2 text-[11px] font-semibold uppercase tracking-[0.18em]" style="color: var(--ui-muted);">
                Principal
            </p>

            @php
                $inicioActivo = request()->routeIs('aula-virtual.inicio');
            @endphp

            <a href="{{ route('aula-virtual.inicio') }}" @if ($inicioActivo) aria-current="page" @endif
                aria-label="Inicio" title="Inicio"
                class="group relative flex items-center gap-3 rounded-xl px-3 py-2.5 text-sm font-semibold transition-all duration-200
                    {{ $inicioActivo
                        ? 'bg-[var(--ui-primary-soft)] text-[var(--ui-primary)]'
                        : 'text-[var(--ui-muted)] hover:bg-[var(--ui-primary-soft)] hover:text-[var(--ui-primary)]' }}">
                {{-- Indicador lateral --}}
                @if ($inicioActivo)
                    <span class="absolute bottom-2 left-0 top-2 w-1 rounded-r-full"
                        style="background: var(--ui-primary);" aria-hidden="true"></span>
                @endif

                {{-- Casa --}}
                <svg class="h-5 w-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"
                    aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"
                        d="M3 10.5 12 3l9 7.5M5.25 9.75V21h13.5V9.75M9 21v-6h6v6" />
                </svg>

                <span x-show="sidebarOpen || mobileSidebar" x-cloak>
                    Inicio
                </span>
            </a>
        </div>

        {{-- ===================================================== --}}
        {{-- ESTUDIANTE --}}
        {{-- ===================================================== --}}
        @canany(['Aula_Virtual_Estudiante', 'Mis_Asignaturas', 'Actividades_Aula', 'Tareas_Aula', 'Cuestionarios_Aula',
            'Mis_Archivos', 'Materiales_Aula', 'Calificaciones_Aula', 'Asistencia_Aula', 'Calendario_Aula',
            'Notificaciones_Aula', 'Perfil_Academico', 'Orientacion_Academica_Profesional'])
            <div>
                <p x-show="sidebarOpen || mobileSidebar" x-cloak
                    class="mb-2 px-2 text-[11px] font-semibold uppercase tracking-[0.18em]" style="color: var(--ui-muted);">
                    Estudiante
                </p>

                <div class="space-y-1">
                    @foreach ($linksEstudiante as $link)
                        @can($link['perm'])
                            @php
                                $isActive = false;

                                foreach ($link['active'] ?? [] as $pattern) {
                                    if (request()->routeIs($pattern)) {
                                        $isActive = true;
                                        break;
                                    }
                                }

                                $url = Route::has($link['route'])
                                    ? route($link['route'])
                                    : route('aula-virtual.inicio');
                            @endphp

                            <a href="{{ $url }}" @if ($isActive) aria-current="page" @endif
                                aria-label="{{ $link['label'] }}" title="{{ $link['label'] }}"
                                class="group relative flex items-center gap-3 rounded-xl px-3 py-2.5 text-sm font-medium transition-all duration-200
                                    {{ $isActive
                                        ? 'bg-[var(--ui-primary-soft)] text-[var(--ui-primary)] font-semibold'
                                        : 'text-[var(--ui-muted)] hover:bg-[var(--ui-primary-soft)] hover:text-[var(--ui-primary)]' }}">
                                @if ($isActive)
                                    <span class="absolute bottom-2 left-0 top-2 w-1 rounded-r-full"
                                        style="background: var(--ui-primary);" aria-hidden="true"></span>
                                @endif

                                {{-- ============================= --}}
                                {{-- ICONOS ESTUDIANTE --}}
                                {{-- ============================= --}}
                                @switch($link['icon'])
                                    {{-- Mis asignaturas --}}
                                    @case('asignaturas')
                                        <svg class="h-5 w-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"
                                            aria-hidden="true">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"
                                                d="M4.5 5.25A2.25 2.25 0 0 1 6.75 3h4.5A2.25 2.25 0 0 1 13.5 5.25V21a3.75 3.75 0 0 0-3.75-3.75h-3A2.25 2.25 0 0 1 4.5 15V5.25Zm15 0A2.25 2.25 0 0 0 17.25 3h-3A2.25 2.25 0 0 0 12 5.25V21a3.75 3.75 0 0 1 3.75-3.75h1.5A2.25 2.25 0 0 0 19.5 15V5.25Z" />
                                        </svg>
                                    @break

                                    {{-- Tareas / actividades --}}
                                    @case('tareas')
                                        <svg class="h-5 w-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"
                                            aria-hidden="true">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"
                                                d="M9 5.25H6.75A2.25 2.25 0 0 0 4.5 7.5v11.25A2.25 2.25 0 0 0 6.75 21h10.5a2.25 2.25 0 0 0 2.25-2.25V7.5a2.25 2.25 0 0 0-2.25-2.25H15M9 5.25A3 3 0 0 1 12 2.25a3 3 0 0 1 3 3M9 5.25h6M9 12l2 2 4-4" />
                                        </svg>
                                    @break

                                    {{-- Materiales --}}
                                    @case('materiales')
                                        <svg class="h-5 w-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"
                                            aria-hidden="true">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"
                                                d="M3.75 6.75A2.25 2.25 0 0 1 6 4.5h4.19c.597 0 1.17.237 1.591.659l1.31 1.31c.422.422.994.659 1.591.659H18A2.25 2.25 0 0 1 20.25 9.38v7.87A2.25 2.25 0 0 1 18 19.5H6a2.25 2.25 0 0 1-2.25-2.25V6.75Z" />
                                        </svg>
                                    @break

                                    {{-- Calificaciones --}}
                                    @case('calificaciones')
                                        <svg class="h-5 w-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"
                                            aria-hidden="true">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"
                                                d="M4.5 19.5V12m5 7.5V8m5 11.5V4.5m5 15V10" />
                                        </svg>
                                    @break

                                    {{-- Asistencia --}}
                                    @case('asistencia')
                                        <svg class="h-5 w-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"
                                            aria-hidden="true">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"
                                                d="M6.75 3v2.25M17.25 3v2.25M3.75 9h16.5M5.25 5.25h13.5A1.5 1.5 0 0 1 20.25 6.75v12A1.5 1.5 0 0 1 18.75 20.25H5.25a1.5 1.5 0 0 1-1.5-1.5v-12a1.5 1.5 0 0 1 1.5-1.5Zm3 8.25 2.25 2.25 5.25-5.25" />
                                        </svg>
                                    @break

                                    {{-- Orientación --}}
                                    @case('orientacion')
                                        <svg class="h-5 w-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"
                                            aria-hidden="true">
                                            <circle cx="12" cy="12" r="8.25" stroke-width="1.8" />
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"
                                                d="m14.75 9.25-1.65 3.85-3.85 1.65 1.65-3.85 3.85-1.65Z" />
                                        </svg>
                                    @break

                                    @default
                                        <svg class="h-5 w-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"
                                            aria-hidden="true">
                                            <circle cx="12" cy="12" r="8.25" stroke-width="1.8" />
                                        </svg>
                                @endswitch

                                <span x-show="sidebarOpen || mobileSidebar" x-cloak class="min-w-0 truncate">
                                    {{ $link['label'] }}
                                </span>
                            </a>
                        @endcan
                    @endforeach
                </div>
            </div>
        @endcanany

        {{-- ===================================================== --}}
        {{-- DOCENTE --}}
        {{-- ===================================================== --}}
        @canany(['Aula_Virtual_Docente', 'Mis_Cursos', 'Estudiantes_Curso', 'Materiales_Aula', 'Tareas_Aula',
            'Entregas_Aula', 'Actividades_Aula', 'Cuestionarios_Aula', 'Asistencia_Aula', 'Calificaciones_Aula',
            'Calendario_Aula', 'Notificaciones_Aula', 'Reportes_Aula', 'Orientacion_Academica_Profesional'])
            <div>
                <p x-show="sidebarOpen || mobileSidebar" x-cloak
                    class="mb-2 px-2 text-[11px] font-semibold uppercase tracking-[0.18em]"
                    style="color: var(--ui-muted);">
                    Docente
                </p>

                <div class="space-y-1">
                    @foreach ($linksDocente as $link)
                        @can($link['perm'])
                            @php
                                $isActive = false;

                                foreach ($link['active'] ?? [] as $pattern) {
                                    if (request()->routeIs($pattern)) {
                                        $isActive = true;
                                        break;
                                    }
                                }

                                $url = Route::has($link['route'])
                                    ? route($link['route'])
                                    : route('aula-virtual.inicio');
                            @endphp

                            <a href="{{ $url }}" @if ($isActive) aria-current="page" @endif
                                aria-label="{{ $link['label'] }}" title="{{ $link['label'] }}"
                                class="group relative flex items-center gap-3 rounded-xl px-3 py-2.5 text-sm font-medium transition-all duration-200
                                    {{ $isActive
                                        ? 'bg-[var(--ui-primary-soft)] text-[var(--ui-primary)] font-semibold'
                                        : 'text-[var(--ui-muted)] hover:bg-[var(--ui-primary-soft)] hover:text-[var(--ui-primary)]' }}">
                                @if ($isActive)
                                    <span class="absolute bottom-2 left-0 top-2 w-1 rounded-r-full"
                                        style="background: var(--ui-primary);" aria-hidden="true"></span>
                                @endif

                                {{-- ============================= --}}
                                {{-- ICONOS DOCENTE --}}
                                {{-- ============================= --}}
                                @switch($link['icon'])
                                    {{-- Cursos --}}
                                    @case('cursos')
                                        <svg class="h-5 w-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"
                                            aria-hidden="true">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"
                                                d="M12 4.5 2.75 9 12 13.5 21.25 9 12 4.5Zm-6.5 6.25v4.5c0 1.5 2.91 3.75 6.5 3.75s6.5-2.25 6.5-3.75v-4.5" />
                                        </svg>
                                    @break

                                    {{-- Materiales --}}
                                    @case('materiales')
                                        <svg class="h-5 w-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"
                                            aria-hidden="true">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"
                                                d="M3.75 6.75A2.25 2.25 0 0 1 6 4.5h4.19c.597 0 1.17.237 1.591.659l1.31 1.31c.422.422.994.659 1.591.659H18A2.25 2.25 0 0 1 20.25 9.38v7.87A2.25 2.25 0 0 1 18 19.5H6a2.25 2.25 0 0 1-2.25-2.25V6.75Z" />
                                        </svg>
                                    @break

                                    {{-- Tareas --}}
                                    @case('tareas')
                                        <svg class="h-5 w-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"
                                            aria-hidden="true">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"
                                                d="M9 5.25H6.75A2.25 2.25 0 0 0 4.5 7.5v11.25A2.25 2.25 0 0 0 6.75 21h10.5a2.25 2.25 0 0 0 2.25-2.25V7.5a2.25 2.25 0 0 0-2.25-2.25H15M9 5.25A3 3 0 0 1 12 2.25a3 3 0 0 1 3 3M9 5.25h6M9 12l2 2 4-4" />
                                        </svg>
                                    @break

                                    {{-- Entregas --}}
                                    @case('entregas')
                                        <svg class="h-5 w-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"
                                            aria-hidden="true">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"
                                                d="M4.5 4.5h15v13.5h-15V4.5Zm0 9h4l1.5 2.25h4l1.5-2.25h4M12 3v8m0 0 3-3m-3 3-3-3" />
                                        </svg>
                                    @break

                                    {{-- Asistencia --}}
                                    @case('asistencia')
                                        <svg class="h-5 w-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"
                                            aria-hidden="true">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"
                                                d="M6.75 3v2.25M17.25 3v2.25M3.75 9h16.5M5.25 5.25h13.5A1.5 1.5 0 0 1 20.25 6.75v12A1.5 1.5 0 0 1 18.75 20.25H5.25a1.5 1.5 0 0 1-1.5-1.5v-12a1.5 1.5 0 0 1 1.5-1.5Zm3 8.25 2.25 2.25 5.25-5.25" />
                                        </svg>
                                    @break

                                    {{-- Calificaciones --}}
                                    @case('calificaciones')
                                        <svg class="h-5 w-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"
                                            aria-hidden="true">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"
                                                d="M4.5 19.5V12m5 7.5V8m5 11.5V4.5m5 15V10" />
                                        </svg>
                                    @break

                                    {{-- Reportes --}}
                                    @case('reportes')
                                        <svg class="h-5 w-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"
                                            aria-hidden="true">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"
                                                d="M6.75 3h7.5L19.5 8.25V21H6.75A2.25 2.25 0 0 1 4.5 18.75V5.25A2.25 2.25 0 0 1 6.75 3Zm7.5 0v5.25h5.25M8.25 17.25v-3m3 3v-5.25m3 5.25v-7.5" />
                                        </svg>
                                    @break

                                    {{-- Orientación --}}
                                    @case('orientacion')
                                        <svg class="h-5 w-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"
                                            aria-hidden="true">
                                            <circle cx="12" cy="12" r="8.25" stroke-width="1.8" />
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"
                                                d="m14.75 9.25-1.65 3.85-3.85 1.65 1.65-3.85 3.85-1.65Z" />
                                        </svg>
                                    @break

                                    @default
                                        <svg class="h-5 w-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"
                                            aria-hidden="true">
                                            <circle cx="12" cy="12" r="8.25" stroke-width="1.8" />
                                        </svg>
                                @endswitch

                                <span x-show="sidebarOpen || mobileSidebar" x-cloak class="min-w-0 truncate">
                                    {{ $link['label'] }}
                                </span>
                            </a>
                        @endcan
                    @endforeach
                </div>
            </div>
        @endcanany
    </nav>

    {{-- ========================================================= --}}
    {{-- CERRAR SESIÓN --}}
    {{-- ========================================================= --}}
    <div class="border-t p-3" style="border-color: var(--ui-border);">
        <form method="POST" action="{{ route('logout') }}">
            @csrf

            <button type="submit"
                class="group flex w-full items-center gap-3 rounded-xl px-3 py-3 text-sm font-semibold transition-all duration-200 hover:bg-[var(--ui-danger-soft)]"
                style="color: var(--ui-danger);" title="Cerrar sesión" aria-label="Cerrar sesión">
                <svg class="h-5 w-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"
                    aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"
                        d="M15.75 9V5.25A2.25 2.25 0 0 0 13.5 3h-6A2.25 2.25 0 0 0 5.25 5.25v13.5A2.25 2.25 0 0 0 7.5 21h6a2.25 2.25 0 0 0 2.25-2.25V15m3 0 3-3m0 0-3-3m3 3H9" />
                </svg>

                <span x-show="sidebarOpen || mobileSidebar" x-cloak>
                    Cerrar sesión
                </span>
            </button>
        </form>
    </div>
</aside>
