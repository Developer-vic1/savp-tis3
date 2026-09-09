@php
    use Illuminate\Support\Facades\Route;

    $user = Auth::user();
    $persona = $user?->persona;

    /*
    |--------------------------------------------------------------------------
    | Nombre del usuario
    |--------------------------------------------------------------------------
    */

    $nombreCompleto = trim(
        ($persona->nom_per ?? '') . ' ' . ($persona->ape_pat_per ?? '') . ' ' . ($persona->ape_mat_per ?? ''),
    );

    $nombreUsuario = $nombreCompleto ?: $user?->email ?? 'Usuario';

    $inicialUsuario = mb_strtoupper(mb_substr($nombreUsuario, 0, 1));

    /*
    |--------------------------------------------------------------------------
    | Rol visual
    |--------------------------------------------------------------------------
    |
    | Esto es únicamente presentación.
    | La seguridad real sigue dependiendo de Spatie + Policies.
    */

    if ($user?->hasRole('Super Admin')) {
        $rol = 'Super Administrador';
    } elseif ($user?->hasRole('Admin')) {
        $rol = 'Administrador';
    } elseif ($user?->hasRole('Docente') || $user?->can('Aula_Virtual_Docente')) {
        $rol = 'Docente';
    } elseif ($user?->hasRole('Estudiante') || $user?->can('Aula_Virtual_Estudiante')) {
        $rol = 'Estudiante';
    } else {
        $rol = $user?->getRoleNames()->first() ?? 'Aula Virtual';
    }

    /*
    |--------------------------------------------------------------------------
    | Fecha
    |--------------------------------------------------------------------------
    */

    $fechaActual = now()->locale('es')->translatedFormat('D, d M');

    /*
    |--------------------------------------------------------------------------
    | Construcción del buscador
    |--------------------------------------------------------------------------
    |
    | El buscador contiene:
    |
    | 1. Destinos de navegación permitidos.
    | 2. Cursos que CursoVirtualService verificó previamente.
    |
    */

    $elementosBusqueda = collect();

    $agregarAcceso = function (string $label, string $routeName, string $subtitle, string $keywords = '') use (
        &$elementosBusqueda,
    ) {
        if (!Route::has($routeName)) {
            return;
        }

        $elementosBusqueda->push([
            'type' => 'page',
            'label' => $label,
            'subtitle' => $subtitle,
            'keywords' => $keywords,
            'context' => 'Acceso',
            'url' => route($routeName),
        ]);
    };

    /*
    |--------------------------------------------------------------------------
    | Accesos comunes
    |--------------------------------------------------------------------------
    */

    $agregarAcceso(
        'Inicio',
        'aula-virtual.inicio',
        'Página principal del Aula Virtual',
        'inicio dashboard principal aula virtual',
    );

    /*
    |--------------------------------------------------------------------------
    | Accesos estudiante
    |--------------------------------------------------------------------------
    */

    if ($user?->can('Mis_Asignaturas')) {
        $agregarAcceso(
            'Mis asignaturas',
            'aula-virtual.estudiante.asignaturas',
            'Consultar mis asignaturas y cursos',
            'asignaturas materias cursos estudiante',
        );
    }

    if ($user?->can('Tareas_Aula')) {
        if (Route::has('aula-virtual.estudiante.asignaturas')) {
            $elementosBusqueda->push([
                'type' => 'page',
                'label' => 'Actividades y tareas',
                'subtitle' => 'Selecciona una asignatura para consultar sus actividades',
                'keywords' => 'tareas actividades pendientes entregas estudiante',
                'context' => 'Estudiante',
                'url' => route('aula-virtual.estudiante.asignaturas'),
            ]);
        }
    }

    if ($user?->can('Materiales_Aula')) {
        if ($user?->can('Aula_Virtual_Estudiante') && Route::has('aula-virtual.estudiante.asignaturas')) {
            $elementosBusqueda->push([
                'type' => 'page',
                'label' => 'Materiales',
                'subtitle' => 'Materiales de mis asignaturas',
                'keywords' => 'materiales archivos documentos clases',
                'context' => 'Estudiante',
                'url' => route('aula-virtual.estudiante.asignaturas'),
            ]);
        }
    }

    if ($user?->can('Calificaciones_Aula')) {
        if ($user?->can('Aula_Virtual_Estudiante') && Route::has('aula-virtual.estudiante.asignaturas')) {
            $elementosBusqueda->push([
                'type' => 'page',
                'label' => 'Calificaciones',
                'subtitle' => 'Consultar mis resultados académicos',
                'keywords' => 'calificaciones notas resultados puntajes',
                'context' => 'Estudiante',
                'url' => route('aula-virtual.estudiante.asignaturas'),
            ]);
        }
    }

    if ($user?->can('Asistencia_Aula') && $user?->can('Aula_Virtual_Estudiante')) {
        $agregarAcceso(
            'Mi asistencia',
            'aula-virtual.estudiante.asistencia',
            'Consultar mi registro de asistencia',
            'asistencia faltas retrasos presentes estudiante',
        );
    }

    if ($user?->can('Orientacion_Academica_Profesional') && $user?->can('Aula_Virtual_Estudiante')) {
        $agregarAcceso(
            'Orientación académica-profesional',
            'aula-virtual.estudiante.orientacion',
            'Explorar orientación académica y profesional',
            'orientacion vocacional profesional carrera intereses',
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Accesos docente
    |--------------------------------------------------------------------------
    */

    if ($user?->can('Mis_Cursos')) {
        $agregarAcceso(
            'Mis cursos',
            'aula-virtual.docente.cursos',
            'Gestionar mis cursos asignados',
            'cursos materias docente clases',
        );
    }

    if (
        $user?->can('Materiales_Aula') &&
        $user?->can('Aula_Virtual_Docente') &&
        Route::has('aula-virtual.docente.cursos')
    ) {
        $elementosBusqueda->push([
            'type' => 'page',
            'label' => 'Materiales de cursos',
            'subtitle' => 'Publicar y administrar materiales',
            'keywords' => 'materiales archivos documentos docente',
            'context' => 'Docente',
            'url' => route('aula-virtual.docente.cursos'),
        ]);
    }

    if (
        $user?->can('Tareas_Aula') &&
        $user?->can('Aula_Virtual_Docente') &&
        Route::has('aula-virtual.docente.cursos')
    ) {
        $elementosBusqueda->push([
            'type' => 'page',
            'label' => 'Tareas',
            'subtitle' => 'Crear y administrar tareas',
            'keywords' => 'tareas actividades crear editar docente',
            'context' => 'Docente',
            'url' => route('aula-virtual.docente.cursos'),
        ]);
    }

    if ($user?->can('Entregas_Aula') && Route::has('aula-virtual.docente.cursos')) {
        $elementosBusqueda->push([
            'type' => 'page',
            'label' => 'Entregas',
            'subtitle' => 'Revisar entregas de estudiantes',
            'keywords' => 'entregas trabajos estudiantes revisar',
            'context' => 'Docente',
            'url' => route('aula-virtual.docente.cursos'),
        ]);
    }

    if (
        $user?->can('Asistencia_Aula') &&
        $user?->can('Aula_Virtual_Docente') &&
        Route::has('aula-virtual.docente.cursos')
    ) {
        $elementosBusqueda->push([
            'type' => 'page',
            'label' => 'Registrar asistencia',
            'subtitle' => 'Selecciona un curso para registrar asistencia',
            'keywords' => 'asistencia presentes faltas retrasos docente',
            'context' => 'Docente',
            'url' => route('aula-virtual.docente.cursos'),
        ]);
    }

    if (
        $user?->can('Calificaciones_Aula') &&
        $user?->can('Aula_Virtual_Docente') &&
        Route::has('aula-virtual.docente.cursos')
    ) {
        $elementosBusqueda->push([
            'type' => 'page',
            'label' => 'Calificaciones',
            'subtitle' => 'Calificar entregas de estudiantes',
            'keywords' => 'calificaciones notas puntajes revisar docente',
            'context' => 'Docente',
            'url' => route('aula-virtual.docente.cursos'),
        ]);
    }

    if ($user?->can('Reportes_Aula')) {
        $agregarAcceso(
            'Reportes',
            'aula-virtual.docente.reportes',
            'Consultar reportes del Aula Virtual',
            'reportes estadísticas resultados docente',
        );
    }

    if ($user?->can('Orientacion_Academica_Profesional') && $user?->can('Aula_Virtual_Docente')) {
        $agregarAcceso(
            'Seguimiento de orientación',
            'aula-virtual.docente.orientacion.seguimiento',
            'Seguimiento académico-profesional',
            'orientacion seguimiento estudiantes docente',
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Cursos autorizados
    |--------------------------------------------------------------------------
    */

    foreach ($cursosBusqueda ?? collect() as $cursoBusqueda) {
        $elementosBusqueda->push([
            'type' => 'course',

            'label' => $cursoBusqueda['label'],

            'subtitle' => $cursoBusqueda['subtitle'],

            'keywords' => $cursoBusqueda['keywords'],

            'context' => $cursoBusqueda['contexto'],

            'status' => $cursoBusqueda['estado'],

            'url' => $cursoBusqueda['url'],
        ]);
    }

    /*
    |--------------------------------------------------------------------------
    | Portal principal y perfil
    |--------------------------------------------------------------------------
    */

    if (Route::has('welcome')) {
        $elementosBusqueda->push([
            'type' => 'page',
            'label' => 'Portal principal',
            'subtitle' => 'Volver al sistema SAVP',
            'keywords' => 'portal welcome sistema principal savp',
            'context' => 'Sistema',
            'url' => route('welcome'),
        ]);
    }

    if (Route::has('profile.show')) {
        $elementosBusqueda->push([
            'type' => 'page',
            'label' => 'Mi perfil',
            'subtitle' => 'Administrar mi cuenta',
            'keywords' => 'perfil usuario cuenta contraseña seguridad',
            'context' => 'Cuenta',
            'url' => route('profile.show'),
        ]);
    }

    /*
    |--------------------------------------------------------------------------
    | Eliminar duplicados
    |--------------------------------------------------------------------------
    */

    $elementosBusqueda = $elementosBusqueda->unique(fn($item) => $item['label'] . '|' . $item['url'])->values();
@endphp


<header x-data="{
    searchOpen: false,
    userOpen: false,
    query: '',

    items: @js($elementosBusqueda),

    normalize(value) {
        return (value ?? '')
            .toString()
            .normalize('NFD')
            .replace(/[\u0300-\u036f]/g, '')
            .toLowerCase()
            .trim();
    },

    get results() {
        const search = this.normalize(this.query);

        if (!search) {
            return this.items.slice(0, 10);
        }

        return this.items
            .filter((item) => {
                const searchable = this.normalize(
                    [
                        item.label,
                        item.subtitle,
                        item.keywords,
                        item.context,
                        item.status
                    ]
                    .filter(Boolean)
                    .join(' ')
                );

                return searchable.includes(search);
            })
            .slice(0, 12);
    },

    openSearch() {
        this.userOpen = false;
        this.searchOpen = true;
        this.query = '';

        this.$nextTick(() => {
            this.$refs.searchInput?.focus();
        });
    },

    closeSearch() {
        this.searchOpen = false;
        this.query = '';
    }
}" @keydown.window.ctrl.k.prevent="openSearch()" @keydown.window.meta.k.prevent="openSearch()"
    @keydown.window.escape="
        if (searchOpen) {
            closeSearch();
        } else {
            userOpen = false;
        }
    "
    class="sticky top-0 z-30 px-4 pt-4 sm:px-6 lg:px-8">

    {{-- ========================================================= --}}
    {{-- BARRA SUPERIOR --}}
    {{-- ========================================================= --}}

    <div class="aula-shell rounded-[1.6rem] border px-4 py-3 shadow-sm sm:px-5 lg:px-6">
        <div class="flex items-center justify-between gap-3">

            {{-- ================================================= --}}
            {{-- IZQUIERDA --}}
            {{-- ================================================= --}}

            <div class="flex min-w-0 items-center gap-3">

                {{-- Menú móvil --}}
                <button type="button" class="ui-icon-btn shrink-0 border lg:hidden"
                    style="
                        border-color: var(--ui-border);
                        background: var(--ui-surface);
                    "
                    @click="mobileSidebar = true" title="Abrir menú" aria-label="Abrir menú">
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"
                            d="M4 6h16M4 12h16M4 18h16" />
                    </svg>
                </button>


                {{-- Título --}}
                <div class="min-w-0">

                    @hasSection('breadcrumb')
                        <div class="mb-1 truncate text-[11px] font-semibold uppercase tracking-[0.14em]"
                            style="color: var(--ui-muted);">
                            @yield('breadcrumb')
                        </div>
                    @else
                        <p class="truncate text-[11px] font-bold uppercase tracking-[0.16em]"
                            style="color: var(--ui-primary);">
                            SAVP-TIS3 · Aula Virtual
                        </p>
                    @endif

                    <h1 class="truncate text-lg font-black tracking-tight sm:text-xl lg:text-2xl"
                        style="color: var(--ui-text);">
                        @yield('page-title', 'Inicio')
                    </h1>

                </div>
            </div>


            {{-- ================================================= --}}
            {{-- DERECHA --}}
            {{-- ================================================= --}}

            <div class="flex shrink-0 items-center gap-2 sm:gap-3">

                {{-- ================================================= --}}
                {{-- BUSCADOR --}}
                {{-- ================================================= --}}

                <button type="button" @click="openSearch()"
                    class="group flex h-11 items-center gap-2 rounded-xl border px-3 text-sm font-semibold transition hover:-translate-y-0.5 hover:shadow-sm md:min-w-[190px] md:justify-between"
                    style="
                        border-color: var(--ui-border);
                        background: var(--ui-surface);
                        color: var(--ui-muted);
                    "
                    title="Buscar en Aula Virtual (Ctrl + K)" aria-label="Buscar en Aula Virtual"
                    aria-haspopup="dialog">
                    <span class="flex min-w-0 items-center gap-2">
                        <svg class="h-5 w-5 shrink-0 transition group-hover:text-[var(--ui-primary)]" fill="none"
                            stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                            <circle cx="11" cy="11" r="6.5" stroke-width="1.8" />

                            <path stroke-linecap="round" stroke-width="1.8" d="m16 16 4 4" />
                        </svg>

                        <span class="hidden truncate md:inline">
                            Buscar...
                        </span>
                    </span>

                    <span class="hidden rounded-md border px-1.5 py-0.5 text-[10px] font-bold lg:inline"
                        style="
                            border-color: var(--ui-border);
                            color: var(--ui-muted);
                        ">
                        Ctrl K
                    </span>
                </button>


                {{-- ================================================= --}}
                {{-- FECHA --}}
                {{-- ================================================= --}}

                <div class="hidden h-11 items-center gap-2 rounded-xl border px-3 text-xs font-semibold xl:flex"
                    style="
                        border-color: var(--ui-border);
                        background: var(--ui-surface);
                        color: var(--ui-muted);
                    "
                    title="{{ now()->locale('es')->translatedFormat('l, d \d\e F \d\e Y') }}">
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"
                            d="M6.75 3v2.25M17.25 3v2.25M3.75 9h16.5M5.25 5.25h13.5A1.5 1.5 0 0 1 20.25 6.75v12A1.5 1.5 0 0 1 18.75 20.25H5.25a1.5 1.5 0 0 1-1.5-1.5v-12a1.5 1.5 0 0 1 1.5-1.5Z" />
                    </svg>

                    {{ $fechaActual }}
                </div>


                {{-- ================================================= --}}
                {{-- TEMA --}}
                {{-- ================================================= --}}

                <button type="button" onclick="window.themeManager?.toggle()"
                    class="ui-icon-btn h-11 w-11 shrink-0 border"
                    style="
                        border-color: var(--ui-border);
                        background: var(--ui-surface);
                    "
                    title="Cambiar tema" aria-label="Cambiar tema claro u oscuro">

                    {{-- Luna --}}
                    <svg class="h-5 w-5 dark:hidden" fill="none" stroke="currentColor" viewBox="0 0 24 24"
                        aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"
                            d="M21.752 15.002A9.72 9.72 0 0 1 18 15.75 9.75 9.75 0 0 1 8.25 6c0-1.33.266-2.597.748-3.752A9.753 9.753 0 0 0 3 11.25 9.75 9.75 0 0 0 12.75 21a9.753 9.753 0 0 0 9.002-5.998Z" />
                    </svg>

                    {{-- Sol --}}
                    <svg class="hidden h-5 w-5 dark:block" fill="none" stroke="currentColor" viewBox="0 0 24 24"
                        aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"
                            d="M12 3v2.25m0 13.5V21m9-9h-2.25M5.25 12H3m15.364-6.364-1.591 1.591M7.227 16.773l-1.591 1.591m12.728 0-1.591-1.591M7.227 7.227 5.636 5.636M15.75 12a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0Z" />
                    </svg>

                </button>


                {{-- ================================================= --}}
                {{-- USUARIO --}}
                {{-- ================================================= --}}

                <div class="relative">

                    <button type="button"
                        @click="
                            userOpen = !userOpen;
                            searchOpen = false;
                        "
                        :aria-expanded="userOpen.toString()" aria-haspopup="menu"
                        class="group flex h-11 items-center gap-2 rounded-xl border p-1.5 pr-2 transition hover:-translate-y-0.5 hover:shadow-sm sm:min-w-[195px]"
                        style="
                            border-color: var(--ui-border);
                            background: var(--ui-surface);
                            color: var(--ui-text);
                        ">

                        {{-- Avatar --}}
                        <span
                            class="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg text-xs font-black text-white shadow-sm"
                            style="
                                background: var(--savp-green);
                            ">
                            {{ $inicialUsuario }}
                        </span>


                        {{-- Nombre --}}
                        <span class="hidden min-w-0 flex-1 text-left sm:block">
                            <span class="block truncate text-xs font-black">
                                {{ $nombreUsuario }}
                            </span>

                            <span class="block truncate text-[11px]" style="color: var(--ui-muted);">
                                {{ $rol }}
                            </span>
                        </span>


                        {{-- Chevron --}}
                        <svg class="hidden h-4 w-4 shrink-0 transition-transform sm:block"
                            :class="{ 'rotate-180': userOpen }" fill="none" stroke="currentColor" viewBox="0 0 24 24"
                            aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"
                                d="m6 9 6 6 6-6" />
                        </svg>

                    </button>


                    {{-- ================================================= --}}
                    {{-- MENÚ USUARIO --}}
                    {{-- ================================================= --}}

                    <div x-show="userOpen" x-cloak x-transition @click.outside="userOpen = false" role="menu"
                        class="absolute right-0 mt-3 w-[19rem] overflow-hidden rounded-2xl border shadow-xl"
                        style="
                            background: var(--ui-surface);
                            border-color: var(--ui-border);
                            color: var(--ui-text);
                        ">

                        {{-- Información --}}
                        <div class="border-b p-4" style="border-color: var(--ui-border);">
                            <div class="flex items-center gap-3">

                                <span
                                    class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl text-sm font-black text-white"
                                    style="
                                        background: var(--savp-green);
                                    ">
                                    {{ $inicialUsuario }}
                                </span>

                                <div class="min-w-0">
                                    <p class="truncate text-sm font-black">
                                        {{ $nombreUsuario }}
                                    </p>

                                    <p class="mt-0.5 truncate text-xs" style="color: var(--ui-muted);">
                                        {{ $user?->email }}
                                    </p>

                                    <p class="mt-2">
                                        <span class="ui-badge-info">
                                            {{ $rol }}
                                        </span>
                                    </p>
                                </div>

                            </div>
                        </div>


                        {{-- Opciones --}}
                        <div class="p-2">

                            @if (Route::has('profile.show'))
                                <a href="{{ route('profile.show') }}" role="menuitem"
                                    class="flex items-center gap-3 rounded-xl px-3 py-2.5 text-sm font-medium transition hover:bg-[var(--ui-surface-muted)]"
                                    style="color: var(--ui-text-soft);">
                                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"
                                        aria-hidden="true">
                                        <circle cx="12" cy="8" r="3.5" stroke-width="1.8" />

                                        <path stroke-linecap="round" stroke-width="1.8"
                                            d="M5 20c.75-4 3.1-6 7-6s6.25 2 7 6" />
                                    </svg>

                                    Mi perfil
                                </a>
                            @endif


                            @if (Route::has('welcome'))
                                <a href="{{ route('welcome') }}" role="menuitem"
                                    class="flex items-center gap-3 rounded-xl px-3 py-2.5 text-sm font-medium transition hover:bg-[var(--ui-surface-muted)]"
                                    style="color: var(--ui-text-soft);">
                                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"
                                        aria-hidden="true">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"
                                            d="M3 10.5 12 3l9 7.5M5.25 9.75V21h13.5V9.75M9 21v-6h6v6" />
                                    </svg>

                                    Portal principal
                                </a>
                            @endif


                            <div class="my-2 border-t" style="border-color: var(--ui-border);"></div>


                            <form method="POST" action="{{ route('logout') }}">
                                @csrf

                                <button type="submit" role="menuitem"
                                    class="flex w-full items-center gap-3 rounded-xl px-3 py-2.5 text-sm font-semibold transition hover:bg-[var(--ui-danger-soft)]"
                                    style="color: var(--ui-danger);">
                                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"
                                        aria-hidden="true">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"
                                            d="M15.75 9V5.25A2.25 2.25 0 0 0 13.5 3h-6A2.25 2.25 0 0 0 5.25 5.25v13.5A2.25 2.25 0 0 0 7.5 21h6a2.25 2.25 0 0 0 2.25-2.25V15m3 0 3-3m0 0-3-3m3 3H9" />
                                    </svg>

                                    Cerrar sesión
                                </button>
                            </form>

                        </div>
                    </div>

                </div>
            </div>
        </div>
    </div>


    {{-- ========================================================= --}}
    {{-- BUSCADOR GLOBAL --}}
    {{-- ========================================================= --}}

    <div x-show="searchOpen" x-cloak x-transition.opacity
        class="fixed inset-0 z-[90] flex items-start justify-center bg-slate-950/50 px-4 pt-[8vh] backdrop-blur-sm"
        @click.self="closeSearch()" role="dialog" aria-modal="true" aria-label="Buscar en Aula Virtual">

        <div x-show="searchOpen" x-transition
            class="w-full max-w-2xl overflow-hidden rounded-[1.5rem] border shadow-2xl"
            style="
                background: var(--ui-surface);
                border-color: var(--ui-border);
                color: var(--ui-text);
            ">

            {{-- Input --}}
            <div class="flex items-center gap-3 border-b px-4 py-3" style="border-color: var(--ui-border);">

                <svg class="h-5 w-5 shrink-0" style="color: var(--ui-primary);" fill="none" stroke="currentColor"
                    viewBox="0 0 24 24" aria-hidden="true">
                    <circle cx="11" cy="11" r="6.5" stroke-width="1.8" />

                    <path stroke-linecap="round" stroke-width="1.8" d="m16 16 4 4" />
                </svg>


                <input x-ref="searchInput" x-model.debounce.120ms="query" type="search" autocomplete="off"
                    placeholder="Buscar curso, materia o sección..."
                    class="min-w-0 flex-1 border-0 bg-transparent px-0 py-2 text-base font-medium outline-none ring-0 focus:border-0 focus:ring-0"
                    style="
                        color: var(--ui-text);
                    "
                    aria-label="Buscar en Aula Virtual">


                <button type="button" @click="closeSearch()" class="ui-icon-btn" title="Cerrar búsqueda"
                    aria-label="Cerrar búsqueda">
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"
                        aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"
                            d="M6 18 18 6M6 6l12 12" />
                    </svg>
                </button>

            </div>


            {{-- Información --}}
            <div class="flex items-center justify-between px-4 pb-2 pt-3">
                <p class="text-[11px] font-bold uppercase tracking-[0.15em]" style="color: var(--ui-muted);"
                    x-text="
                        query.trim()
                            ? 'Resultados'
                            : 'Accesos sugeridos'
                    ">
                </p>

                <p class="text-xs" style="color: var(--ui-muted);" x-show="results.length > 0">
                    <span x-text="results.length"></span>
                    resultado<span x-show="results.length !== 1">s</span>
                </p>
            </div>


            {{-- Resultados --}}
            <div class="ui-scrollbar max-h-[60vh] overflow-y-auto px-2 pb-2">

                <template x-for="(item, index) in results" :key="item.url + '-' + index">
                    <a :href="item.url"
                        class="group flex items-center gap-3 rounded-xl px-3 py-3 transition hover:bg-[var(--ui-primary-soft)]">

                        {{-- Icono --}}
                        <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl border transition group-hover:border-[var(--ui-primary)]"
                            style="
                                border-color: var(--ui-border);
                                background: var(--ui-bg-soft);
                            ">

                            {{-- Curso --}}
                            <template x-if="item.type === 'course'">
                                <svg class="h-5 w-5" style="color: var(--ui-primary);" fill="none"
                                    stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"
                                        d="M12 4.5 2.75 9 12 13.5 21.25 9 12 4.5Zm-6.5 6.25v4.5c0 1.5 2.91 3.75 6.5 3.75s6.5-2.25 6.5-3.75v-4.5" />
                                </svg>
                            </template>

                            {{-- Página --}}
                            <template x-if="item.type !== 'course'">
                                <svg class="h-5 w-5" style="color: var(--ui-muted);" fill="none"
                                    stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"
                                        d="M4.5 6.75h15M4.5 12h15M4.5 17.25h9" />
                                </svg>
                            </template>

                        </div>


                        {{-- Texto --}}
                        <div class="min-w-0 flex-1">

                            <div class="flex items-center gap-2">
                                <p class="truncate text-sm font-bold" x-text="item.label"></p>

                                <span x-show="item.type === 'course'"
                                    class="hidden rounded-md px-2 py-0.5 text-[10px] font-bold sm:inline"
                                    style="
                                        background: var(--ui-primary-soft);
                                        color: var(--ui-primary);
                                    "
                                    x-text="item.context"></span>
                            </div>

                            <p class="mt-0.5 truncate text-xs" style="color: var(--ui-muted);"
                                x-text="item.subtitle"></p>

                        </div>


                        {{-- Flecha --}}
                        <svg class="h-4 w-4 shrink-0 opacity-40 transition group-hover:translate-x-0.5 group-hover:opacity-100"
                            fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"
                                d="m9 18 6-6-6-6" />
                        </svg>

                    </a>
                </template>


                {{-- Sin resultados --}}
                <div x-show="
                        query.trim() !== ''
                        && results.length === 0
                    "
                    x-cloak class="px-6 py-10 text-center">

                    <div class="mx-auto flex h-14 w-14 items-center justify-center rounded-2xl"
                        style="
                            background: var(--ui-bg-soft);
                            color: var(--ui-muted);
                        ">
                        <svg class="h-7 w-7" fill="none" stroke="currentColor" viewBox="0 0 24 24"
                            aria-hidden="true">
                            <circle cx="11" cy="11" r="6.5" stroke-width="1.8" />

                            <path stroke-linecap="round" stroke-width="1.8"
                                d="m16 16 4 4M8.75 8.75l4.5 4.5m0-4.5-4.5 4.5" />
                        </svg>
                    </div>

                    <p class="mt-4 text-sm font-black">
                        No hay resultados
                    </p>

                    <p class="mx-auto mt-1 max-w-sm text-xs leading-5" style="color: var(--ui-muted);">
                        No encontramos ningún curso o acceso
                        disponible que coincida con
                        <strong style="color: var(--ui-text);" x-text="'“' + query + '”'"></strong>.
                    </p>

                </div>

            </div>


            {{-- Footer --}}
            <div class="flex flex-wrap items-center justify-between gap-2 border-t px-4 py-3 text-[11px]"
                style="
                    border-color: var(--ui-border);
                    color: var(--ui-muted);
                ">
                <span>
                    Solo se muestran recursos a los que tienes acceso.
                </span>

                <span class="hidden sm:inline">
                    ESC para cerrar · Ctrl K para buscar
                </span>
            </div>

        </div>
    </div>

</header>
