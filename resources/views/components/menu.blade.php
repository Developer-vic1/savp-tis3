<div
    class="fixed top-0 left-0 z-40 h-screen bg-white/90 backdrop-blur-xl border-r border-slate-200 shadow-md flex flex-col transition-all duration-300"
    :class="sidebarOpen ? 'w-72' : 'w-20'">

    {{-- HEADER / MARCA --}}
    <div class="flex items-center justify-between p-4 border-b border-slate-200">
        <a href="{{ route('dashboard') }}" class="flex items-center space-x-3 overflow-hidden">
            <div class="flex h-11 w-11 items-center justify-center rounded-2xl bg-white shadow ring-1 ring-slate-200 shrink-0">
                <img src="{{ asset('image/LOGO FT3 A.jpg') }}"
                    alt="Logo Franz Tamayo"
                    class="h-8 w-8 object-contain">
            </div>

            <div x-show="sidebarOpen" x-cloak>
                <p class="font-bold text-sm text-slate-900 leading-tight">Franz Tamayo N°3</p>
                <p class="text-[11px] uppercase tracking-[0.18em] font-semibold text-emerald-700">
                    SAVP – TIS 3
                </p>
            </div>
        </a>

        <button @click="sidebarOpen = !sidebarOpen"
            class="p-2 rounded-xl text-slate-600 hover:bg-slate-100 transition shrink-0">
            <svg xmlns="http://www.w3.org/2000/svg"
                class="h-5 w-5 transition-transform duration-300"
                :class="{ 'rotate-180': !sidebarOpen }"
                fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M15 19l-7-7 7-7" />
            </svg>
        </button>
    </div>

    {{-- TARJETA DEL ROL --}}
    <div class="px-4 pt-4">
        <div class="rounded-2xl bg-gradient-to-r from-emerald-600 to-sky-600 p-4 text-white">
            <template x-if="sidebarOpen">
                <div>
                    <p class="text-xs font-semibold uppercase tracking-[0.18em] text-emerald-100">
                        Panel actual
                    </p>
                    <p class="mt-2 text-lg font-bold">
                        {{ Auth::user()->getRoleNames()->first() ?? 'Usuario' }}
                    </p>
                    <p class="mt-1 text-sm text-white/90">
                        Acceso institucional según permisos asignados.
                    </p>
                </div>
            </template>

            <template x-if="!sidebarOpen">
                <div class="flex justify-center">
                    <span class="text-lg font-bold">
                        {{ strtoupper(substr(Auth::user()->getRoleNames()->first() ?? 'U', 0, 1)) }}
                    </span>
                </div>
            </template>
        </div>
    </div>

    {{-- NAVEGACIÓN --}}
    <nav class="flex-1 overflow-y-auto px-3 py-4 space-y-3">

        {{-- PRINCIPAL --}}
        @canany(['Panel_Administrador', 'Panel_Director', 'Panel_Docente', 'Panel_Estudiante', 'Panel_Secretaria', 'Panel_Regente'])
            <div>
                <p x-show="sidebarOpen" x-cloak
                    class="px-2 mb-2 text-[11px] uppercase tracking-[0.18em] font-semibold text-slate-500">
                    Principal
                </p>

                <a href="{{ route('dashboard') }}"
                    class="flex items-center gap-3 rounded-2xl px-3 py-3 text-sm font-medium text-slate-700 hover:bg-emerald-50 hover:text-emerald-700 transition">
                    <span class="text-lg shrink-0">🏠</span>
                    <span x-show="sidebarOpen" x-cloak>Panel</span>
                </a>
            </div>
        @endcanany

        {{-- ADMINISTRACIÓN --}}
        @canany(['Gestion_Usuarios', 'Registro_Personas', 'Personal_Institucional', 'Estudiantes', 'Bitacora'])
            <div x-data="{ openAdmin: true }">
                <button @click="openAdmin = !openAdmin"
                    class="flex items-center justify-between w-full rounded-2xl px-3 py-3 text-sm font-medium text-slate-700 hover:bg-slate-50 transition">
                    <div class="flex items-center gap-3">
                        <span class="text-lg shrink-0">🛠️</span>
                        <span x-show="sidebarOpen" x-cloak>Administración</span>
                    </div>

                    <svg x-show="sidebarOpen" x-cloak xmlns="http://www.w3.org/2000/svg"
                        class="h-4 w-4 transition-transform duration-300 shrink-0"
                        :class="{ 'rotate-180': openAdmin }"
                        fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M19 9l-7 7-7-7" />
                    </svg>
                </button>

                <div x-show="openAdmin" x-collapse x-cloak class="mt-2 space-y-1 pl-3">
                    @can('Gestion_Usuarios')
                        <a href="{{ route('admin.gestion-usuarios') }}"
                            class="flex items-center gap-3 rounded-xl px-3 py-2 text-sm text-slate-600 hover:bg-emerald-50 hover:text-emerald-700 transition">
                            <span class="shrink-0">👥</span>
                            <span x-show="sidebarOpen" x-cloak>Gestión de usuarios</span>
                        </a>
                    @endcan

                    @can('Registro_Personas')
                        <a href="#"
                            class="flex items-center gap-3 rounded-xl px-3 py-2 text-sm text-slate-600 hover:bg-emerald-50 hover:text-emerald-700 transition">
                            <span class="shrink-0">🧾</span>
                            <span x-show="sidebarOpen" x-cloak>Registro de personas</span>
                        </a>
                    @endcan

                    @can('Personal_Institucional')
                        <a href="#"
                            class="flex items-center gap-3 rounded-xl px-3 py-2 text-sm text-slate-600 hover:bg-emerald-50 hover:text-emerald-700 transition">
                            <span class="shrink-0">🏫</span>
                            <span x-show="sidebarOpen" x-cloak>Personal institucional</span>
                        </a>
                    @endcan

                    @can('Estudiantes')
                        <a href="#"
                            class="flex items-center gap-3 rounded-xl px-3 py-2 text-sm text-slate-600 hover:bg-emerald-50 hover:text-emerald-700 transition">
                            <span class="shrink-0">🎓</span>
                            <span x-show="sidebarOpen" x-cloak>Estudiantes</span>
                        </a>
                    @endcan

                    @can('Bitacora')
                        <a href="#"
                            class="flex items-center gap-3 rounded-xl px-3 py-2 text-sm text-slate-600 hover:bg-emerald-50 hover:text-emerald-700 transition">
                            <span class="shrink-0">📜</span>
                            <span x-show="sidebarOpen" x-cloak>Bitácora</span>
                        </a>
                    @endcan
                </div>
            </div>
        @endcanany

        {{-- ACADÉMICO --}}
        @canany(['Gestion_Academica', 'Cursos', 'Paralelos', 'Turnos', 'Asignaturas', 'Especialidades_Tecnicas', 'Periodo_Evaluacion', 'Planes_Asignatura'])
            <div x-data="{ openAcademico: false }">
                <button @click="openAcademico = !openAcademico"
                    class="flex items-center justify-between w-full rounded-2xl px-3 py-3 text-sm font-medium text-slate-700 hover:bg-slate-50 transition">
                    <div class="flex items-center gap-3">
                        <span class="text-lg shrink-0">📘</span>
                        <span x-show="sidebarOpen" x-cloak>Académico</span>
                    </div>

                    <svg x-show="sidebarOpen" x-cloak xmlns="http://www.w3.org/2000/svg"
                        class="h-4 w-4 transition-transform duration-300 shrink-0"
                        :class="{ 'rotate-180': openAcademico }"
                        fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M19 9l-7 7-7-7" />
                    </svg>
                </button>

                <div x-show="openAcademico" x-collapse x-cloak class="mt-2 space-y-1 pl-3">
                    @can('Gestion_Academica')
                        <a href="#"
                            class="flex items-center gap-3 rounded-xl px-3 py-2 text-sm text-slate-600 hover:bg-sky-50 hover:text-sky-700 transition">
                            <span class="shrink-0">🗂️</span>
                            <span x-show="sidebarOpen" x-cloak>Gestión académica</span>
                        </a>
                    @endcan

                    @can('Cursos')
                        <a href="#"
                            class="flex items-center gap-3 rounded-xl px-3 py-2 text-sm text-slate-600 hover:bg-sky-50 hover:text-sky-700 transition">
                            <span class="shrink-0">🎓</span>
                            <span x-show="sidebarOpen" x-cloak>Cursos</span>
                        </a>
                    @endcan

                    @can('Paralelos')
                        <a href="#"
                            class="flex items-center gap-3 rounded-xl px-3 py-2 text-sm text-slate-600 hover:bg-sky-50 hover:text-sky-700 transition">
                            <span class="shrink-0">🧩</span>
                            <span x-show="sidebarOpen" x-cloak>Paralelos</span>
                        </a>
                    @endcan

                    @can('Turnos')
                        <a href="#"
                            class="flex items-center gap-3 rounded-xl px-3 py-2 text-sm text-slate-600 hover:bg-sky-50 hover:text-sky-700 transition">
                            <span class="shrink-0">🕒</span>
                            <span x-show="sidebarOpen" x-cloak>Turnos</span>
                        </a>
                    @endcan

                    @can('Asignaturas')
                        <a href="#"
                            class="flex items-center gap-3 rounded-xl px-3 py-2 text-sm text-slate-600 hover:bg-sky-50 hover:text-sky-700 transition">
                            <span class="shrink-0">📚</span>
                            <span x-show="sidebarOpen" x-cloak>Asignaturas</span>
                        </a>
                    @endcan

                    @can('Especialidades_Tecnicas')
                        <a href="#"
                            class="flex items-center gap-3 rounded-xl px-3 py-2 text-sm text-slate-600 hover:bg-sky-50 hover:text-sky-700 transition">
                            <span class="shrink-0">🛠️</span>
                            <span x-show="sidebarOpen" x-cloak>Especialidades técnicas</span>
                        </a>
                    @endcan

                    @can('Periodo_Evaluacion')
                        <a href="#"
                            class="flex items-center gap-3 rounded-xl px-3 py-2 text-sm text-slate-600 hover:bg-sky-50 hover:text-sky-700 transition">
                            <span class="shrink-0">🗓️</span>
                            <span x-show="sidebarOpen" x-cloak>Periodo de evaluación</span>
                        </a>
                    @endcan

                    @can('Planes_Asignatura')
                        <a href="#"
                            class="flex items-center gap-3 rounded-xl px-3 py-2 text-sm text-slate-600 hover:bg-sky-50 hover:text-sky-700 transition">
                            <span class="shrink-0">📑</span>
                            <span x-show="sidebarOpen" x-cloak>Planes de asignatura</span>
                        </a>
                    @endcan
                </div>
            </div>
        @endcanany

        {{-- COMUNIDAD EDUCATIVA --}}
        @canany(['Estudiantes', 'Docentes', 'Inscripciones', 'Institucion_Procedencia', 'Tipo_Vinculacion_Estudiante'])
            <div x-data="{ openComunidad: false }">
                <button @click="openComunidad = !openComunidad"
                    class="flex items-center justify-between w-full rounded-2xl px-3 py-3 text-sm font-medium text-slate-700 hover:bg-slate-50 transition">
                    <div class="flex items-center gap-3">
                        <span class="text-lg shrink-0">👨‍🏫</span>
                        <span x-show="sidebarOpen" x-cloak>Comunidad educativa</span>
                    </div>

                    <svg x-show="sidebarOpen" x-cloak xmlns="http://www.w3.org/2000/svg"
                        class="h-4 w-4 transition-transform duration-300 shrink-0"
                        :class="{ 'rotate-180': openComunidad }"
                        fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M19 9l-7 7-7-7" />
                    </svg>
                </button>

                <div x-show="openComunidad" x-collapse x-cloak class="mt-2 space-y-1 pl-3">
                    @can('Estudiantes')
                        <a href="#"
                            class="flex items-center gap-3 rounded-xl px-3 py-2 text-sm text-slate-600 hover:bg-emerald-50 hover:text-emerald-700 transition">
                            <span class="shrink-0">🧑‍🎓</span>
                            <span x-show="sidebarOpen" x-cloak>Estudiantes</span>
                        </a>
                    @endcan

                    @can('Docentes')
                        <a href="#"
                            class="flex items-center gap-3 rounded-xl px-3 py-2 text-sm text-slate-600 hover:bg-emerald-50 hover:text-emerald-700 transition">
                            <span class="shrink-0">🧑‍🏫</span>
                            <span x-show="sidebarOpen" x-cloak>Docentes</span>
                        </a>
                    @endcan

                    @can('Inscripciones')
                        <a href="#"
                            class="flex items-center gap-3 rounded-xl px-3 py-2 text-sm text-slate-600 hover:bg-emerald-50 hover:text-emerald-700 transition">
                            <span class="shrink-0">📝</span>
                            <span x-show="sidebarOpen" x-cloak>Inscripciones</span>
                        </a>
                    @endcan

                    @can('Institucion_Procedencia')
                        <a href="#"
                            class="flex items-center gap-3 rounded-xl px-3 py-2 text-sm text-slate-600 hover:bg-emerald-50 hover:text-emerald-700 transition">
                            <span class="shrink-0">🏛️</span>
                            <span x-show="sidebarOpen" x-cloak>Institución de procedencia</span>
                        </a>
                    @endcan

                    @can('Tipo_Vinculacion_Estudiante')
                        <a href="#"
                            class="flex items-center gap-3 rounded-xl px-3 py-2 text-sm text-slate-600 hover:bg-emerald-50 hover:text-emerald-700 transition">
                            <span class="shrink-0">🔗</span>
                            <span x-show="sidebarOpen" x-cloak>Tipo de vinculación</span>
                        </a>
                    @endcan
                </div>
            </div>
        @endcanany

        {{-- EVALUACIÓN Y REPORTES --}}
        @canany(['Calificaciones', 'Reportes_Academicos', 'Reportes_Administrativos'])
            <div x-data="{ openEvaluacion: false }">
                <button @click="openEvaluacion = !openEvaluacion"
                    class="flex items-center justify-between w-full rounded-2xl px-3 py-3 text-sm font-medium text-slate-700 hover:bg-slate-50 transition">
                    <div class="flex items-center gap-3">
                        <span class="text-lg shrink-0">📊</span>
                        <span x-show="sidebarOpen" x-cloak>Evaluación y reportes</span>
                    </div>

                    <svg x-show="sidebarOpen" x-cloak xmlns="http://www.w3.org/2000/svg"
                        class="h-4 w-4 transition-transform duration-300 shrink-0"
                        :class="{ 'rotate-180': openEvaluacion }"
                        fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M19 9l-7 7-7-7" />
                    </svg>
                </button>

                <div x-show="openEvaluacion" x-collapse x-cloak class="mt-2 space-y-1 pl-3">
                    @can('Calificaciones')
                        <a href="#"
                            class="flex items-center gap-3 rounded-xl px-3 py-2 text-sm text-slate-600 hover:bg-sky-50 hover:text-sky-700 transition">
                            <span class="shrink-0">🧮</span>
                            <span x-show="sidebarOpen" x-cloak>Calificaciones</span>
                        </a>
                    @endcan

                    @can('Reportes_Academicos')
                        <a href="#"
                            class="flex items-center gap-3 rounded-xl px-3 py-2 text-sm text-slate-600 hover:bg-sky-50 hover:text-sky-700 transition">
                            <span class="shrink-0">📈</span>
                            <span x-show="sidebarOpen" x-cloak>Reportes académicos</span>
                        </a>
                    @endcan

                    @can('Reportes_Administrativos')
                        <a href="#"
                            class="flex items-center gap-3 rounded-xl px-3 py-2 text-sm text-slate-600 hover:bg-sky-50 hover:text-sky-700 transition">
                            <span class="shrink-0">🧾</span>
                            <span x-show="sidebarOpen" x-cloak>Reportes administrativos</span>
                        </a>
                    @endcan
                </div>
            </div>
        @endcanany
    </nav>

    {{-- FOOTER --}}
    <div class="border-t border-slate-200 p-3">
        @can('Mi_Perfil')
            <a href="{{ route('profile.show') }}"
                class="flex items-center gap-3 rounded-2xl px-3 py-3 text-sm font-medium text-slate-700 hover:bg-slate-50 transition">
                <span class="text-lg shrink-0">👤</span>
                <span x-show="sidebarOpen" x-cloak>Mi perfil</span>
            </a>
        @endcan

        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button type="submit"
                class="mt-2 flex w-full items-center gap-3 rounded-2xl px-3 py-3 text-sm font-medium text-red-600 hover:bg-red-50 transition">
                <span class="text-lg shrink-0">⏻</span>
                <span x-show="sidebarOpen" x-cloak>Cerrar sesión</span>
            </button>
        </form>
    </div>
</div>