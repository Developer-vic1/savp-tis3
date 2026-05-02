<div class="space-y-6">

    {{-- ENCABEZADO INFORMATIVO --}}
    <section class="ui-card overflow-hidden rounded-[2rem]">
        <div class="relative px-6 py-6 sm:px-7">
            <div class="absolute right-0 top-0 h-32 w-32 rounded-full bg-emerald-400/10 blur-3xl"></div>
            <div class="absolute bottom-0 left-1/3 h-28 w-28 rounded-full bg-sky-400/10 blur-3xl"></div>
            <div class="absolute -bottom-8 right-1/4 h-28 w-28 rounded-full bg-violet-400/10 blur-3xl"></div>

            <div class="relative flex flex-col gap-5 xl:flex-row xl:items-center xl:justify-between">
                <div>
                    <p class="text-xs font-black uppercase tracking-[0.18em] text-emerald-600 dark:text-emerald-300">
                        Gestión académica estudiantil
                    </p>

                    <h2 class="mt-2 text-2xl font-black tracking-tight text-slate-950 dark:text-white sm:text-3xl">
                        Control de matrícula, inscripción y especialidad técnica
                    </h2>

                    <p class="mt-2 max-w-4xl text-sm leading-7 text-slate-600 dark:text-slate-300">
                        Administra estudiantes desde una perspectiva académica, administrativa y técnica.
                        Organiza la información por tabla general, cursos, especialidades, procedencia y tarjetas
                        para facilitar la toma de decisiones institucionales.
                    </p>
                </div>

                <div class="grid gap-3 sm:grid-cols-3 xl:min-w-[520px]">
                    <div class="rounded-2xl border border-emerald-200 bg-emerald-50 px-4 py-3 dark:border-emerald-400/20 dark:bg-emerald-400/10">
                        <p class="text-xs font-black uppercase tracking-[0.14em] text-emerald-700 dark:text-emerald-300">
                            Gestión actual
                        </p>
                        <p class="mt-1 text-sm font-black text-slate-900 dark:text-white">
                            {{ $nombreGestionActual }}
                        </p>
                    </div>

                    <div class="rounded-2xl border border-sky-200 bg-sky-50 px-4 py-3 dark:border-sky-400/20 dark:bg-sky-400/10">
                        <p class="text-xs font-black uppercase tracking-[0.14em] text-sky-700 dark:text-sky-300">
                            Inscripción
                        </p>
                        <p class="mt-1 text-sm font-black text-slate-900 dark:text-white">
                            Gestión activa
                        </p>
                    </div>

                    <div class="rounded-2xl border border-violet-200 bg-violet-50 px-4 py-3 dark:border-violet-400/20 dark:bg-violet-400/10">
                        <p class="text-xs font-black uppercase tracking-[0.14em] text-violet-700 dark:text-violet-300">
                            Modalidad
                        </p>
                        <p class="mt-1 text-sm font-black text-slate-900 dark:text-white">
                            Técnico Humanístico
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    {{-- ACCIONES SUPERIORES --}}
    <section class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
        <div>
            <p class="text-sm font-semibold text-slate-500 dark:text-slate-400">
                Vista institucional de estudiantes
            </p>
            <h3 class="text-xl font-black text-slate-950 dark:text-white">
                Organización académica, técnica y administrativa
            </h3>
        </div>

        <div class="flex flex-wrap items-center gap-3">
            <button type="button"
                wire:click="abrirModalRegistrar"
                wire:loading.attr="disabled"
                class="ui-btn-primary inline-flex items-center gap-2">
                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"
                        d="M12 4.5v15m7.5-7.5h-15" />
                </svg>
                Registrar estudiante
            </button>

            <button type="button"
                class="ui-btn-secondary inline-flex items-center gap-2"
                onclick="window.Swal ? Swal.fire({icon:'info',title:'Reporte en preparación',text:'La exportación se integrará en una siguiente fase.',confirmButtonColor:'#059669'}) : alert('Reporte en preparación')">
                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"
                        d="M3 16.5v2.25A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75V16.5M7.5 10.5 12 15m0 0 4.5-4.5M12 15V3" />
                </svg>
                Exportar reporte
            </button>
        </div>
    </section>

    {{-- CARDS DE RESUMEN --}}
    <section class="grid gap-4 sm:grid-cols-2 xl:grid-cols-6">
        <article class="ui-card-soft rounded-2xl p-5">
            <div class="flex h-12 w-12 items-center justify-center rounded-2xl bg-sky-100 text-sky-700 dark:bg-sky-400/10 dark:text-sky-300">
                <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"
                        d="M15 19.128a9.38 9.38 0 0 0 2.625.372 9.337 9.337 0 0 0 4.121-.952 4.125 4.125 0 0 0-7.533-2.493M12 12a3.75 3.75 0 1 0 0-7.5 3.75 3.75 0 0 0 0 7.5Z" />
                </svg>
            </div>
            <p class="mt-4 text-xs font-black uppercase tracking-[0.14em] text-slate-500 dark:text-slate-400">
                Total estudiantes
            </p>
            <p class="mt-1 text-3xl font-black text-slate-950 dark:text-white">
                {{ $totalEstudiantes }}
            </p>
            <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">
                Registrados en el sistema
            </p>
        </article>

        <article class="ui-card-soft rounded-2xl p-5">
            <div class="flex h-12 w-12 items-center justify-center rounded-2xl bg-emerald-100 text-emerald-700 dark:bg-emerald-400/10 dark:text-emerald-300">
                <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"
                        d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                </svg>
            </div>
            <p class="mt-4 text-xs font-black uppercase tracking-[0.14em] text-slate-500 dark:text-slate-400">
                Activos
            </p>
            <p class="mt-1 text-3xl font-black text-slate-950 dark:text-white">
                {{ $estudiantesActivos }}
            </p>
            <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">
                Habilitados académicamente
            </p>
        </article>

        <article class="ui-card-soft rounded-2xl p-5">
            <div class="flex h-12 w-12 items-center justify-center rounded-2xl bg-rose-100 text-rose-700 dark:bg-rose-400/10 dark:text-rose-300">
                <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"
                        d="M18.364 18.364A9 9 0 0 0 5.636 5.636m12.728 12.728L5.636 5.636" />
                </svg>
            </div>
            <p class="mt-4 text-xs font-black uppercase tracking-[0.14em] text-slate-500 dark:text-slate-400">
                Inactivos
            </p>
            <p class="mt-1 text-3xl font-black text-slate-950 dark:text-white">
                {{ $estudiantesInactivos }}
            </p>
            <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">
                Retirados o desactivados
            </p>
        </article>

        <article class="ui-card-soft rounded-2xl p-5">
            <div class="flex h-12 w-12 items-center justify-center rounded-2xl bg-violet-100 text-violet-700 dark:bg-violet-400/10 dark:text-violet-300">
                <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"
                        d="M19.5 14.25v-2.625a3.375 3.375 0 0 0-3.375-3.375h-1.5A1.125 1.125 0 0 1 13.5 7.125v-1.5A3.375 3.375 0 0 0 10.125 2.25H8.25M8.25 15h7.5M8.25 18h7.5" />
                </svg>
            </div>
            <p class="mt-4 text-xs font-black uppercase tracking-[0.14em] text-slate-500 dark:text-slate-400">
                Inscritos
            </p>
            <p class="mt-1 text-3xl font-black text-slate-950 dark:text-white">
                {{ $inscritosGestionActual }}
            </p>
            <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">
                Gestión {{ $nombreGestionActual }}
            </p>
        </article>

        <article class="ui-card-soft rounded-2xl p-5">
            <div class="flex h-12 w-12 items-center justify-center rounded-2xl bg-amber-100 text-amber-700 dark:bg-amber-400/10 dark:text-amber-300">
                <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"
                        d="M12 9v3.75m0 3.75h.008v.008H12v-.008ZM10.29 3.86 1.82 18a1.875 1.875 0 0 0 1.607 2.84h17.146A1.875 1.875 0 0 0 22.18 18L13.71 3.86a1.875 1.875 0 0 0-3.42 0Z" />
                </svg>
            </div>
            <p class="mt-4 text-xs font-black uppercase tracking-[0.14em] text-slate-500 dark:text-slate-400">
                Sin inscripción
            </p>
            <p class="mt-1 text-3xl font-black text-slate-950 dark:text-white">
                {{ $sinInscripcionGestionActual }}
            </p>
            <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">
                Requieren revisión
            </p>
        </article>

        <article class="ui-card-soft rounded-2xl p-5">
            <div class="flex h-12 w-12 items-center justify-center rounded-2xl bg-indigo-100 text-indigo-700 dark:bg-indigo-400/10 dark:text-indigo-300">
                <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"
                        d="M3.75 6.75h16.5M3.75 12h16.5m-16.5 5.25h16.5" />
                </svg>
            </div>
            <p class="mt-4 text-xs font-black uppercase tracking-[0.14em] text-slate-500 dark:text-slate-400">
                Especialidades
            </p>
            <p class="mt-1 text-3xl font-black text-slate-950 dark:text-white">
                {{ $totalEspecialidadesConEstudiantes }}
            </p>
            <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">
                Con estudiantes asignados
            </p>
        </article>
    </section>

    {{-- SELECTOR DE FORMATO DE VISTA --}}
    <section class="ui-card-soft rounded-[1.6rem] p-4">
        <div class="flex flex-col gap-4 xl:flex-row xl:items-center xl:justify-between">
            <div>
                <p class="text-xs font-black uppercase tracking-[0.16em] text-slate-500 dark:text-slate-400">
                    Tipo de vista
                </p>
                <p class="mt-1 text-sm font-semibold text-slate-600 dark:text-slate-300">
                    Cambia la forma de explorar estudiantes según la necesidad institucional.
                </p>
            </div>

            <div class="flex flex-wrap gap-2">
                <button type="button" wire:click="cambiarVista('todos')"
                    class="inline-flex items-center gap-2 rounded-2xl px-4 py-2.5 text-sm font-black transition
                    {{ $vistaActiva === 'todos' ? 'bg-emerald-600 text-white shadow-lg shadow-emerald-500/20' : 'text-slate-600 hover:bg-slate-100 dark:text-slate-300 dark:hover:bg-slate-800' }}">
                    Tabla general
                </button>

                <button type="button" wire:click="cambiarVista('cursos')"
                    class="inline-flex items-center gap-2 rounded-2xl px-4 py-2.5 text-sm font-black transition
                    {{ $vistaActiva === 'cursos' ? 'bg-sky-600 text-white shadow-lg shadow-sky-500/20' : 'text-slate-600 hover:bg-slate-100 dark:text-slate-300 dark:hover:bg-slate-800' }}">
                    Cursos
                </button>

                <button type="button" wire:click="cambiarVista('especialidades')"
                    class="inline-flex items-center gap-2 rounded-2xl px-4 py-2.5 text-sm font-black transition
                    {{ $vistaActiva === 'especialidades' ? 'bg-violet-600 text-white shadow-lg shadow-violet-500/20' : 'text-slate-600 hover:bg-slate-100 dark:text-slate-300 dark:hover:bg-slate-800' }}">
                    Especialidades
                </button>

                <button type="button" wire:click="cambiarVista('procedencias')"
                    class="inline-flex items-center gap-2 rounded-2xl px-4 py-2.5 text-sm font-black transition
                    {{ $vistaActiva === 'procedencias' ? 'bg-amber-600 text-white shadow-lg shadow-amber-500/20' : 'text-slate-600 hover:bg-slate-100 dark:text-slate-300 dark:hover:bg-slate-800' }}">
                    Procedencia
                </button>

                <button type="button" wire:click="cambiarVista('tarjetas')"
                    class="inline-flex items-center gap-2 rounded-2xl px-4 py-2.5 text-sm font-black transition
                    {{ $vistaActiva === 'tarjetas' ? 'bg-slate-800 text-white shadow-lg shadow-slate-500/20 dark:bg-slate-200 dark:text-slate-950' : 'text-slate-600 hover:bg-slate-100 dark:text-slate-300 dark:hover:bg-slate-800' }}">
                    Tarjetas
                </button>
            </div>
        </div>

        @if ($vistaActiva === 'cursos' && $cursoCarpetaSeleccionado)
            <div class="mt-4 flex justify-end">
                <button type="button" wire:click="limpiarCursoCarpeta" class="ui-btn-secondary">
                    Cerrar carpeta de curso
                </button>
            </div>
        @endif

        @if ($vistaActiva === 'especialidades' && $especialidadCarpetaSeleccionada)
            <div class="mt-4 flex justify-end">
                <button type="button" wire:click="limpiarEspecialidadCarpeta" class="ui-btn-secondary">
                    Cerrar carpeta de especialidad
                </button>
            </div>
        @endif

        @if ($vistaActiva === 'procedencias' && $procedenciaCarpetaSeleccionada)
            <div class="mt-4 flex justify-end">
                <button type="button" wire:click="limpiarProcedenciaCarpeta" class="ui-btn-secondary">
                    Cerrar carpeta de procedencia
                </button>
            </div>
        @endif
    </section>

    {{-- FILTROS --}}
    <section class="ui-card rounded-[1.7rem] p-5">
        <div class="grid gap-4 xl:grid-cols-[1.5fr_.75fr_.75fr_.9fr_.9fr_.8fr_.8fr_auto]">
            <div>
                <label class="ui-label">Buscar estudiante</label>
                <div class="relative">
                    <span class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-4 text-slate-400">
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"
                                d="m21 21-5.197-5.197M10.5 18a7.5 7.5 0 1 1 0-15 7.5 7.5 0 0 1 0 15Z" />
                        </svg>
                    </span>
                    <input type="text"
                        wire:model.live.debounce.450ms="search"
                        class="ui-input w-full pl-11"
                        placeholder="Nombre, CI, RUD/RUDE, correo, teléfono o procedencia...">
                </div>
            </div>

            <div>
                <label class="ui-label">Curso</label>
                <select wire:model.live="filtroCurso" class="ui-select w-full">
                    <option value="">Todos</option>
                    @foreach ($cursos as $curso)
                        <option value="{{ $curso->cod_cur }}">{{ $curso->nom_cur }}</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="ui-label">Paralelo</label>
                <select wire:model.live="filtroParalelo" class="ui-select w-full">
                    <option value="">Todos</option>
                    @foreach ($paralelos as $paralelo)
                        <option value="{{ $paralelo->cod_par }}">{{ $paralelo->nom_par }}</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="ui-label">Especialidad</label>
                <select wire:model.live="filtroEspecialidad" class="ui-select w-full">
                    <option value="">Todas</option>
                    @foreach ($especialidades as $especialidad)
                        <option value="{{ $especialidad->cod_esp }}">{{ $especialidad->nom_esp }}</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="ui-label">Procedencia</label>
                <select wire:model.live="filtroProcedencia" class="ui-select w-full">
                    <option value="">Todas</option>
                    @foreach ($institucionesProcedencia as $institucion)
                        <option value="{{ $institucion->cod_ipe }}">{{ $institucion->nom_ipe }}</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="ui-label">Estado</label>
                <select wire:model.live="filtroEstado" class="ui-select w-full">
                    <option value="">Todos</option>
                    @foreach ($estadosEstudiante as $estado)
                        <option value="{{ $estado }}">{{ $this->estadoEstudianteLabel($estado) }}</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="ui-label">Inscripción</label>
                <select wire:model.live="filtroInscripcion" class="ui-select w-full">
                    <option value="">Todas</option>
                    <option value="INSCRITO">Inscrito</option>
                    <option value="PENDIENTE">Pendiente</option>
                    <option value="SIN_INSCRIPCION">Sin inscripción</option>
                </select>
            </div>

            <div class="flex items-end">
                <button type="button" wire:click="limpiarFiltros" class="ui-btn-secondary w-full justify-center">
                    Limpiar
                </button>
            </div>
        </div>
    </section>

    {{-- VISTA POR CURSOS --}}
    @if ($vistaActiva === 'cursos' && ! $cursoCarpetaSeleccionado)
        <section class="grid gap-5 sm:grid-cols-2 xl:grid-cols-3">
            @forelse ($cursosCarpeta as $curso)
                <button type="button"
                    wire:click="seleccionarCursoCarpeta('{{ $curso->cod_cur }}')"
                    class="group ui-card-soft rounded-[1.8rem] p-6 text-left transition hover:-translate-y-1 hover:shadow-xl">
                    <div class="flex items-start justify-between gap-4">
                        <div class="flex h-14 w-14 items-center justify-center rounded-2xl bg-sky-100 text-sky-700 transition group-hover:bg-sky-600 group-hover:text-white dark:bg-sky-400/10 dark:text-sky-300">
                            <svg class="h-7 w-7" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"
                                    d="M2.25 12.75V6.75A2.25 2.25 0 0 1 4.5 4.5h4.879c.597 0 1.169.237 1.591.659l1.371 1.371c.422.422.994.659 1.591.659H19.5a2.25 2.25 0 0 1 2.25 2.25v7.811A2.25 2.25 0 0 1 19.5 19.5h-15a2.25 2.25 0 0 1-2.25-2.25v-4.5Z" />
                            </svg>
                        </div>

                        <span class="ui-badge-info">Curso</span>
                    </div>

                    <h3 class="mt-5 text-xl font-black text-slate-950 dark:text-white">
                        {{ $curso->nom_cur }}
                    </h3>

                    <p class="mt-2 text-sm leading-6 text-slate-500 dark:text-slate-400">
                        Carpeta académica con estudiantes inscritos en la gestión {{ $nombreGestionActual }}.
                    </p>

                    <div class="mt-5 grid grid-cols-3 gap-3">
                        <div class="rounded-2xl bg-slate-100 px-3 py-3 text-center dark:bg-slate-800">
                            <p class="text-lg font-black text-slate-950 dark:text-white">
                                {{ $curso->total_estudiantes_curso }}
                            </p>
                            <p class="text-[11px] font-bold text-slate-500 dark:text-slate-400">Estudiantes</p>
                        </div>

                        <div class="rounded-2xl bg-emerald-50 px-3 py-3 text-center dark:bg-emerald-400/10">
                            <p class="text-lg font-black text-emerald-700 dark:text-emerald-300">
                                {{ $curso->total_inscritos_curso }}
                            </p>
                            <p class="text-[11px] font-bold text-slate-500 dark:text-slate-400">Inscritos</p>
                        </div>

                        <div class="rounded-2xl bg-violet-50 px-3 py-3 text-center dark:bg-violet-400/10">
                            <p class="text-lg font-black text-violet-700 dark:text-violet-300">
                                {{ $curso->total_paralelos_curso }}
                            </p>
                            <p class="text-[11px] font-bold text-slate-500 dark:text-slate-400">Paralelos</p>
                        </div>
                    </div>

                    <div class="mt-5 flex items-center justify-between text-sm font-bold text-sky-700 dark:text-sky-300">
                        <span>Abrir carpeta</span>
                        <svg class="h-5 w-5 transition group-hover:translate-x-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"
                                d="M13.5 4.5 21 12m0 0-7.5 7.5M21 12H3" />
                        </svg>
                    </div>
                </button>
            @empty
                <div class="ui-card col-span-full rounded-[1.8rem] p-8 text-center">
                    <p class="text-lg font-black text-slate-950 dark:text-white">No existen cursos activos.</p>
                    <p class="mt-2 text-sm text-slate-500 dark:text-slate-400">
                        Registra cursos para organizar a los estudiantes por carpetas académicas.
                    </p>
                </div>
            @endforelse
        </section>
    @endif

    {{-- VISTA POR ESPECIALIDADES --}}
    @if ($vistaActiva === 'especialidades' && ! $especialidadCarpetaSeleccionada)
        <section class="grid gap-5 sm:grid-cols-2 xl:grid-cols-3">
            @forelse ($especialidadesCarpeta as $especialidad)
                <button type="button"
                    wire:click="seleccionarEspecialidadCarpeta('{{ $especialidad->cod_esp }}')"
                    class="group ui-card-soft rounded-[1.8rem] p-6 text-left transition hover:-translate-y-1 hover:shadow-xl">
                    <div class="flex items-start justify-between gap-4">
                        <div class="flex h-14 w-14 items-center justify-center rounded-2xl bg-violet-100 text-violet-700 transition group-hover:bg-violet-600 group-hover:text-white dark:bg-violet-400/10 dark:text-violet-300">
                            <svg class="h-7 w-7" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"
                                    d="M9.75 3.104v5.714a2.25 2.25 0 0 1-.659 1.591L5.16 14.34M14.25 3.104v5.714a2.25 2.25 0 0 0 .659 1.591l3.931 3.931M5.16 14.34A6.75 6.75 0 1 0 18.84 14.34" />
                            </svg>
                        </div>

                        <span class="ui-badge-violet">Especialidad</span>
                    </div>

                    <h3 class="mt-5 text-xl font-black text-slate-950 dark:text-white">
                        {{ $especialidad->nom_esp }}
                    </h3>

                    <p class="mt-2 line-clamp-2 text-sm leading-6 text-slate-500 dark:text-slate-400">
                        {{ $especialidad->des_esp ?? 'Carpeta técnica con estudiantes asignados a esta especialidad.' }}
                    </p>

                    <div class="mt-5 grid grid-cols-3 gap-3">
                        <div class="rounded-2xl bg-slate-100 px-3 py-3 text-center dark:bg-slate-800">
                            <p class="text-lg font-black text-slate-950 dark:text-white">
                                {{ $especialidad->total_estudiantes_especialidad }}
                            </p>
                            <p class="text-[11px] font-bold text-slate-500 dark:text-slate-400">Total</p>
                        </div>

                        <div class="rounded-2xl bg-emerald-50 px-3 py-3 text-center dark:bg-emerald-400/10">
                            <p class="text-lg font-black text-emerald-700 dark:text-emerald-300">
                                {{ $especialidad->total_activos_especialidad }}
                            </p>
                            <p class="text-[11px] font-bold text-slate-500 dark:text-slate-400">Activos</p>
                        </div>

                        <div class="rounded-2xl bg-violet-50 px-3 py-3 text-center dark:bg-violet-400/10">
                            <p class="text-lg font-black text-violet-700 dark:text-violet-300">
                                {{ $especialidad->total_inscritos_especialidad }}
                            </p>
                            <p class="text-[11px] font-bold text-slate-500 dark:text-slate-400">Inscritos</p>
                        </div>
                    </div>

                    <div class="mt-5 flex items-center justify-between text-sm font-bold text-violet-700 dark:text-violet-300">
                        <span>Abrir especialidad</span>
                        <svg class="h-5 w-5 transition group-hover:translate-x-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"
                                d="M13.5 4.5 21 12m0 0-7.5 7.5M21 12H3" />
                        </svg>
                    </div>
                </button>
            @empty
                <div class="ui-card col-span-full rounded-[1.8rem] p-8 text-center">
                    <p class="text-lg font-black text-slate-950 dark:text-white">No existen especialidades activas.</p>
                </div>
            @endforelse
        </section>
    @endif

    {{-- VISTA POR PROCEDENCIA --}}
    @if ($vistaActiva === 'procedencias' && ! $procedenciaCarpetaSeleccionada)
        <section class="grid gap-5 sm:grid-cols-2 xl:grid-cols-3">
            @forelse ($procedenciasCarpeta as $procedencia)
                <button type="button"
                    wire:click="seleccionarProcedenciaCarpeta('{{ $procedencia->cod_ipe }}')"
                    class="group ui-card-soft rounded-[1.8rem] p-6 text-left transition hover:-translate-y-1 hover:shadow-xl">
                    <div class="flex items-start justify-between gap-4">
                        <div class="flex h-14 w-14 items-center justify-center rounded-2xl bg-amber-100 text-amber-700 transition group-hover:bg-amber-600 group-hover:text-white dark:bg-amber-400/10 dark:text-amber-300">
                            <svg class="h-7 w-7" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"
                                    d="M12 21V3m0 0 7.5 4.5M12 3 4.5 7.5M4.5 7.5v9L12 21l7.5-4.5v-9" />
                            </svg>
                        </div>

                        <span class="ui-badge-warning">Procedencia</span>
                    </div>

                    <h3 class="mt-5 text-xl font-black text-slate-950 dark:text-white">
                        {{ $procedencia->nom_ipe }}
                    </h3>

                    <p class="mt-2 text-sm leading-6 text-slate-500 dark:text-slate-400">
                        Estudiantes registrados según unidad educativa o institución de procedencia.
                    </p>

                    <div class="mt-5 grid grid-cols-3 gap-3">
                        <div class="rounded-2xl bg-slate-100 px-3 py-3 text-center dark:bg-slate-800">
                            <p class="text-lg font-black text-slate-950 dark:text-white">
                                {{ $procedencia->total_estudiantes_procedencia }}
                            </p>
                            <p class="text-[11px] font-bold text-slate-500 dark:text-slate-400">Total</p>
                        </div>

                        <div class="rounded-2xl bg-emerald-50 px-3 py-3 text-center dark:bg-emerald-400/10">
                            <p class="text-lg font-black text-emerald-700 dark:text-emerald-300">
                                {{ $procedencia->total_activos_procedencia }}
                            </p>
                            <p class="text-[11px] font-bold text-slate-500 dark:text-slate-400">Activos</p>
                        </div>

                        <div class="rounded-2xl bg-sky-50 px-3 py-3 text-center dark:bg-sky-400/10">
                            <p class="text-lg font-black text-sky-700 dark:text-sky-300">
                                {{ $procedencia->total_inscritos_procedencia }}
                            </p>
                            <p class="text-[11px] font-bold text-slate-500 dark:text-slate-400">Inscritos</p>
                        </div>
                    </div>

                    <div class="mt-5 flex items-center justify-between text-sm font-bold text-amber-700 dark:text-amber-300">
                        <span>Abrir procedencia</span>
                        <svg class="h-5 w-5 transition group-hover:translate-x-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"
                                d="M13.5 4.5 21 12m0 0-7.5 7.5M21 12H3" />
                        </svg>
                    </div>
                </button>
            @empty
                <div class="ui-card col-span-full rounded-[1.8rem] p-8 text-center">
                    <p class="text-lg font-black text-slate-950 dark:text-white">
                        No existen instituciones de procedencia registradas.
                    </p>
                </div>
            @endforelse
        </section>
    @endif

    {{-- TABLA PRINCIPAL --}}
    @if (
        $vistaActiva === 'todos' ||
        ($vistaActiva === 'cursos' && $cursoCarpetaSeleccionado) ||
        ($vistaActiva === 'especialidades' && $especialidadCarpetaSeleccionada) ||
        ($vistaActiva === 'procedencias' && $procedenciaCarpetaSeleccionada)
    )
        <section class="ui-table-wrap overflow-hidden rounded-[1.8rem]">
            <div class="flex flex-col gap-3 border-b border-slate-200 px-5 py-4 dark:border-slate-700 lg:flex-row lg:items-center lg:justify-between">
                <div>
                    <h3 class="text-lg font-black text-slate-950 dark:text-white">
                        @if ($vistaActiva === 'cursos' && $cursoCarpetaSeleccionado)
                            Estudiantes de la carpeta de curso
                        @elseif ($vistaActiva === 'especialidades' && $especialidadCarpetaSeleccionada)
                            Estudiantes de la especialidad seleccionada
                        @elseif ($vistaActiva === 'procedencias' && $procedenciaCarpetaSeleccionada)
                            Estudiantes por institución de procedencia
                        @else
                            Todos los estudiantes
                        @endif
                    </h3>
                    <p class="text-sm text-slate-500 dark:text-slate-400">
                        Vista académica con inscripción, especialidad técnica, procedencia y estado institucional.
                    </p>
                </div>

                <div class="flex items-center gap-3">
                    <span class="text-sm font-semibold text-slate-500 dark:text-slate-400">Mostrar</span>
                    <select wire:model.live="perPage" class="ui-select">
                        <option value="5">5</option>
                        <option value="10">10</option>
                        <option value="15">15</option>
                        <option value="25">25</option>
                    </select>
                </div>
            </div>

            <div class="overflow-x-auto">
                <table class="ui-table min-w-[1280px]">
                    <thead>
                        <tr>
                            <th>Estudiante</th>
                            <th>CI</th>
                            <th>Curso / Paralelo</th>
                            <th>Especialidad</th>
                            <th>Procedencia</th>
                            <th>Vinculación</th>
                            <th>Inscripción</th>
                            <th>Estado</th>
                            <th class="text-right">Acciones</th>
                        </tr>
                    </thead>

                    <tbody>
                        @forelse ($estudiantes as $estudiante)
                            @php
                                $persona = $estudiante->persona;
                                $inscripcionEstado = $this->obtenerEstadoInscripcion($estudiante);
                                $inscripcionActual = $this->inscripcionActual($estudiante);
                                $nombreVinculacion = $estudiante->tipoVinculacion->nom_tve ?? 'Sin vinculación';
                            @endphp

                            <tr wire:key="estudiante-row-{{ $estudiante->cod_est }}">
                                <td>
                                    <div class="flex items-center gap-3">
                                        <div class="flex h-11 w-11 shrink-0 items-center justify-center rounded-2xl bg-gradient-to-br from-emerald-500 to-sky-500 text-sm font-black text-white">
                                            {{ $this->iniciales($persona) }}
                                        </div>

                                        <div>
                                            <p class="font-black text-slate-950 dark:text-white">
                                                {{ $this->nombreCompleto($persona) }}
                                            </p>
                                            <p class="text-xs font-semibold text-slate-500 dark:text-slate-400">
                                                RUD/RUDE: {{ $estudiante->rud_est ?? 'No registrado' }}
                                            </p>
                                        </div>
                                    </div>
                                </td>

                                <td>
                                    <p class="font-bold text-slate-800 dark:text-slate-100">
                                        {{ $this->ciCompleto($persona) }}
                                    </p>
                                </td>

                                <td>
                                    @if ($inscripcionActual)
                                        <p class="font-black text-slate-900 dark:text-white">
                                            {{ $inscripcionActual->curso->nom_cur ?? 'Sin curso' }}
                                        </p>
                                        <p class="text-xs text-slate-500 dark:text-slate-400">
                                            Paralelo {{ $inscripcionActual->paralelo->nom_par ?? 'Sin paralelo' }}
                                        </p>
                                    @else
                                        <span class="ui-badge-warning">Pendiente</span>
                                    @endif
                                </td>

                                <td>
                                    @if ($estudiante->especialidad)
                                        <span class="ui-badge-violet">
                                            {{ $estudiante->especialidad->nom_esp }}
                                        </span>
                                    @else
                                        <span class="ui-badge-muted">
                                            Técnica general
                                        </span>
                                    @endif
                                </td>

                                <td>
                                    <p class="max-w-[180px] truncate text-sm font-bold text-slate-700 dark:text-slate-200">
                                        {{ $estudiante->institucionProcedencia->nom_ipe ?? 'Sin procedencia' }}
                                    </p>
                                </td>

                                <td>
                                    <span class="{{ $this->badgeVinculacion($nombreVinculacion) }}">
                                        {{ $nombreVinculacion }}
                                    </span>
                                </td>

                                <td>
                                    <span class="{{ $this->badgeInscripcion($inscripcionEstado) }}">
                                        {{ $this->estadoInscripcionLabel($inscripcionEstado) }}
                                    </span>
                                </td>

                                <td>
                                    <span class="{{ $this->badgeEstadoEstudiante($estudiante->est_est ?? 'ACTIVO') }}">
                                        {{ $this->estadoEstudianteLabel($estudiante->est_est ?? 'ACTIVO') }}
                                    </span>
                                </td>

                                <td>
                                    <div class="flex justify-end gap-2">
                                        <button type="button" wire:click="abrirPanelDetalle('{{ $estudiante->cod_est }}')" class="ui-icon-btn" title="Ver detalle">
                                            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"
                                                    d="M2.036 12.322a1 1 0 0 1 0-.644C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.01 9.963 7.178a1 1 0 0 1 0 .644C20.577 16.49 16.639 19.5 12 19.5c-4.638 0-8.573-3.01-9.964-7.178Z" />
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"
                                                    d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" />
                                            </svg>
                                        </button>

                                        <button type="button" wire:click="abrirModalEditar('{{ $estudiante->cod_est }}')" class="ui-icon-btn" title="Editar">
                                            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"
                                                    d="m16.862 4.487 1.687-1.688a1.875 1.875 0 1 1 2.652 2.652L10.582 16.07a4.5 4.5 0 0 1-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 0 1 1.13-1.897l8.932-8.931Z" />
                                            </svg>
                                        </button>

                                        <button type="button" wire:click="abrirModalInscripcion('{{ $estudiante->cod_est }}')" class="ui-icon-btn" title="Inscripción">
                                            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"
                                                    d="M12 6.75c-2.25-1.5-5.25-1.5-7.5 0v11.25c2.25-1.5 5.25-1.5 7.5 0m0-11.25c2.25-1.5 5.25-1.5 7.5 0v11.25c-2.25-1.5-5.25-1.5-7.5 0m0-11.25v11.25" />
                                            </svg>
                                        </button>

                                        <button type="button" wire:click="abrirModalHistorial('{{ $estudiante->cod_est }}')" class="ui-icon-btn" title="Historial">
                                            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"
                                                    d="M12 6v6h4.5M21 12a9 9 0 1 1-3.6-7.2" />
                                            </svg>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="9">
                                    <div class="py-12 text-center">
                                        <p class="text-lg font-black text-slate-950 dark:text-white">
                                            No se encontraron estudiantes
                                        </p>
                                        <p class="mt-2 text-sm text-slate-500 dark:text-slate-400">
                                            Ajusta los filtros o registra un nuevo estudiante.
                                        </p>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="border-t border-slate-200 px-5 py-4 dark:border-slate-700">
                {{ $estudiantes->links() }}
            </div>
        </section>
    @endif

    {{-- VISTA TARJETAS --}}
    @if ($vistaActiva === 'tarjetas')
        <section class="grid gap-5 md:grid-cols-2 xl:grid-cols-3">
            @forelse ($estudiantes as $estudiante)
                @php
                    $persona = $estudiante->persona;
                    $inscripcionEstado = $this->obtenerEstadoInscripcion($estudiante);
                    $inscripcionActual = $this->inscripcionActual($estudiante);
                @endphp

                <article class="ui-card-soft rounded-[1.8rem] p-5 transition hover:-translate-y-1 hover:shadow-xl">
                    <div class="flex items-start gap-4">
                        <div class="flex h-14 w-14 shrink-0 items-center justify-center rounded-2xl bg-gradient-to-br from-emerald-500 to-sky-500 text-base font-black text-white">
                            {{ $this->iniciales($persona) }}
                        </div>

                        <div class="min-w-0 flex-1">
                            <h3 class="truncate text-lg font-black text-slate-950 dark:text-white">
                                {{ $this->nombreCompleto($persona) }}
                            </h3>
                            <p class="text-xs font-semibold text-slate-500 dark:text-slate-400">
                                RUD/RUDE: {{ $estudiante->rud_est ?? 'No registrado' }}
                            </p>
                        </div>
                    </div>

                    <div class="mt-5 grid gap-3">
                        <div class="rounded-2xl bg-slate-100 px-4 py-3 dark:bg-slate-800">
                            <p class="text-xs font-black uppercase tracking-[0.14em] text-slate-500 dark:text-slate-400">
                                Curso actual
                            </p>
                            <p class="mt-1 text-sm font-black text-slate-950 dark:text-white">
                                @if ($inscripcionActual)
                                    {{ $inscripcionActual->curso->nom_cur ?? 'Sin curso' }} ·
                                    {{ $inscripcionActual->paralelo->nom_par ?? 'Sin paralelo' }}
                                @else
                                    Sin inscripción
                                @endif
                            </p>
                        </div>

                        <div class="rounded-2xl bg-slate-100 px-4 py-3 dark:bg-slate-800">
                            <p class="text-xs font-black uppercase tracking-[0.14em] text-slate-500 dark:text-slate-400">
                                Procedencia
                            </p>
                            <p class="mt-1 truncate text-sm font-black text-slate-950 dark:text-white">
                                {{ $estudiante->institucionProcedencia->nom_ipe ?? 'Sin procedencia' }}
                            </p>
                        </div>

                        <div class="flex flex-wrap gap-2">
                            <span class="{{ $this->badgeInscripcion($inscripcionEstado) }}">
                                {{ $this->estadoInscripcionLabel($inscripcionEstado) }}
                            </span>

                            <span class="{{ $this->badgeEstadoEstudiante($estudiante->est_est ?? 'ACTIVO') }}">
                                {{ $this->estadoEstudianteLabel($estudiante->est_est ?? 'ACTIVO') }}
                            </span>

                            @if ($estudiante->especialidad)
                                <span class="ui-badge-violet">
                                    {{ $estudiante->especialidad->nom_esp }}
                                </span>
                            @endif
                        </div>
                    </div>

                    <div class="mt-5 flex justify-between gap-2">
                        <button type="button" wire:click="abrirPanelDetalle('{{ $estudiante->cod_est }}')" class="ui-btn-secondary flex-1 justify-center">
                            Ver detalle
                        </button>
                        <button type="button" wire:click="abrirModalInscripcion('{{ $estudiante->cod_est }}')" class="ui-btn-primary flex-1 justify-center">
                            Inscribir
                        </button>
                    </div>
                </article>
            @empty
                <div class="ui-card col-span-full rounded-[1.8rem] p-8 text-center">
                    <p class="text-lg font-black text-slate-950 dark:text-white">
                        No hay estudiantes para mostrar.
                    </p>
                </div>
            @endforelse
        </section>

        <div class="ui-card rounded-[1.4rem] px-5 py-4">
            {{ $estudiantes->links() }}
        </div>
    @endif

    {{-- DRAWER DETALLE DERECHO --}}
    @if ($panelDetalle && $estudianteDetalle)
        <div class="fixed inset-0 z-50">
            <div class="absolute inset-0 bg-slate-950/50 backdrop-blur-sm" wire:click="cerrarPanelDetalle"></div>

            <aside class="absolute right-0 top-0 h-full w-full max-w-2xl overflow-y-auto border-l border-slate-200 bg-white shadow-2xl dark:border-slate-700 dark:bg-slate-950">
                @php
                    $personaDetalle = $estudianteDetalle->persona;
                    $detalleInscripcion = $this->inscripcionActual($estudianteDetalle);
                    $detalleInscripcionEstado = $this->obtenerEstadoInscripcion($estudianteDetalle);
                @endphp

                <div class="sticky top-0 z-10 border-b border-slate-200 bg-white/90 px-6 py-5 backdrop-blur dark:border-slate-700 dark:bg-slate-950/90">
                    <div class="flex items-start justify-between gap-4">
                        <div class="flex min-w-0 items-center gap-4">
                            <div class="flex h-14 w-14 shrink-0 items-center justify-center rounded-2xl bg-gradient-to-br from-emerald-500 to-sky-500 text-base font-black text-white shadow-lg shadow-emerald-500/20">
                                {{ $this->iniciales($personaDetalle) }}
                            </div>

                            <div class="min-w-0">
                                <p class="text-xs font-black uppercase tracking-[0.18em] text-emerald-600 dark:text-emerald-300">
                                    Detalle del estudiante
                                </p>

                                <h3 class="mt-1 truncate text-2xl font-black text-slate-950 dark:text-white">
                                    {{ $this->nombreCompleto($personaDetalle) }}
                                </h3>

                                <div class="mt-2 flex flex-wrap gap-2">
                                    <span class="{{ $this->badgeEstadoEstudiante($estudianteDetalle->est_est ?? 'ACTIVO') }}">
                                        {{ $this->estadoEstudianteLabel($estudianteDetalle->est_est ?? 'ACTIVO') }}
                                    </span>

                                    <span class="{{ $this->badgeInscripcion($detalleInscripcionEstado) }}">
                                        {{ $this->estadoInscripcionLabel($detalleInscripcionEstado) }}
                                    </span>
                                </div>
                            </div>
                        </div>

                        <button type="button" wire:click="cerrarPanelDetalle" class="ui-icon-btn">
                            <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"
                                    d="M6 18 18 6M6 6l12 12" />
                            </svg>
                        </button>
                    </div>
                </div>

                <div class="space-y-5 p-6">
                    <section class="rounded-[1.6rem] border border-emerald-200 bg-gradient-to-br from-emerald-50 via-white to-sky-50 p-5 dark:border-emerald-400/20 dark:from-emerald-400/10 dark:via-slate-900 dark:to-sky-400/10">
                        <div class="flex items-start justify-between gap-4">
                            <div>
                                <p class="text-xs font-black uppercase tracking-[0.16em] text-emerald-700 dark:text-emerald-300">
                                    Resumen académico
                                </p>
                                <h4 class="mt-1 text-lg font-black text-slate-950 dark:text-white">
                                    Situación actual del estudiante
                                </h4>
                            </div>

                            <div class="rounded-2xl bg-white/80 px-3 py-2 text-right shadow-sm dark:bg-slate-950/60">
                                <p class="text-xs font-bold text-slate-500 dark:text-slate-400">Gestión</p>
                                <p class="text-sm font-black text-slate-950 dark:text-white">
                                    {{ $nombreGestionActual }}
                                </p>
                            </div>
                        </div>

                        <div class="mt-5 grid gap-3 sm:grid-cols-3">
                            <div class="rounded-2xl bg-white/80 p-4 shadow-sm dark:bg-slate-950/50">
                                <p class="text-xs font-bold uppercase text-slate-500 dark:text-slate-400">Curso</p>
                                <p class="mt-1 font-black text-slate-950 dark:text-white">
                                    {{ $detalleInscripcion->curso->nom_cur ?? 'Sin curso' }}
                                </p>
                            </div>

                            <div class="rounded-2xl bg-white/80 p-4 shadow-sm dark:bg-slate-950/50">
                                <p class="text-xs font-bold uppercase text-slate-500 dark:text-slate-400">Paralelo</p>
                                <p class="mt-1 font-black text-slate-950 dark:text-white">
                                    {{ $detalleInscripcion->paralelo->nom_par ?? 'Sin paralelo' }}
                                </p>
                            </div>

                            <div class="rounded-2xl bg-white/80 p-4 shadow-sm dark:bg-slate-950/50">
                                <p class="text-xs font-bold uppercase text-slate-500 dark:text-slate-400">RUD/RUDE</p>
                                <p class="mt-1 font-black text-slate-950 dark:text-white">
                                    {{ $estudianteDetalle->rud_est ?? 'No registrado' }}
                                </p>
                            </div>
                        </div>
                    </section>

                    <section class="rounded-[1.6rem] border border-slate-200 bg-slate-50 p-5 dark:border-slate-700 dark:bg-slate-900/70">
                        <h4 class="text-lg font-black text-slate-950 dark:text-white">
                            Información personal
                        </h4>

                        <div class="mt-4 grid gap-4 sm:grid-cols-2">
                            <div>
                                <p class="text-xs font-bold uppercase text-slate-500 dark:text-slate-400">CI</p>
                                <p class="font-bold text-slate-900 dark:text-white">
                                    {{ $this->ciCompleto($personaDetalle) }}
                                </p>
                            </div>

                            <div>
                                <p class="text-xs font-bold uppercase text-slate-500 dark:text-slate-400">Edad</p>
                                <p class="font-bold text-slate-900 dark:text-white">
                                    {{ $this->edad($personaDetalle) ? $this->edad($personaDetalle) . ' años' : 'No registrada' }}
                                </p>
                            </div>

                            <div>
                                <p class="text-xs font-bold uppercase text-slate-500 dark:text-slate-400">Teléfono</p>
                                <p class="font-bold text-slate-900 dark:text-white">
                                    {{ $personaDetalle->tel_per ?? 'No registrado' }}
                                </p>
                            </div>

                            <div>
                                <p class="text-xs font-bold uppercase text-slate-500 dark:text-slate-400">Correo</p>
                                <p class="break-words font-bold text-slate-900 dark:text-white">
                                    {{ $personaDetalle->ema_per ?? 'No registrado' }}
                                </p>
                            </div>

                            <div class="sm:col-span-2">
                                <p class="text-xs font-bold uppercase text-slate-500 dark:text-slate-400">Dirección</p>
                                <p class="font-bold text-slate-900 dark:text-white">
                                    {{ $personaDetalle->dir_per ?? 'No registrada' }}
                                </p>
                            </div>
                        </div>
                    </section>

                    <section class="rounded-[1.6rem] border border-slate-200 bg-white p-5 dark:border-slate-700 dark:bg-slate-900">
                        <h4 class="text-lg font-black text-slate-950 dark:text-white">
                            Información académica-administrativa
                        </h4>

                        <div class="mt-4 grid gap-4 sm:grid-cols-2">
                            <div>
                                <p class="text-xs font-bold uppercase text-slate-500 dark:text-slate-400">Inscripción</p>
                                <span class="{{ $this->badgeInscripcion($detalleInscripcionEstado) }}">
                                    {{ $this->estadoInscripcionLabel($detalleInscripcionEstado) }}
                                </span>
                            </div>

                            <div>
                                <p class="text-xs font-bold uppercase text-slate-500 dark:text-slate-400">Vinculación</p>
                                <p class="font-bold text-slate-900 dark:text-white">
                                    {{ $estudianteDetalle->tipoVinculacion->nom_tve ?? 'Sin vinculación' }}
                                </p>
                            </div>

                            <div class="sm:col-span-2">
                                <p class="text-xs font-bold uppercase text-slate-500 dark:text-slate-400">Procedencia</p>
                                <p class="font-bold text-slate-900 dark:text-white">
                                    {{ $estudianteDetalle->institucionProcedencia->nom_ipe ?? 'Sin procedencia' }}
                                </p>
                            </div>
                        </div>
                    </section>

                    <section class="rounded-[1.6rem] border border-violet-200 bg-violet-50 p-5 dark:border-violet-400/20 dark:bg-violet-400/10">
                        <h4 class="text-lg font-black text-slate-950 dark:text-white">
                            Formación técnica
                        </h4>

                        <p class="mt-3 text-sm leading-7 text-slate-600 dark:text-slate-300">
                            {{ $estudianteDetalle->especialidad->nom_esp ?? 'El estudiante aún no tiene una especialidad técnica asignada o se encuentra en Técnica Tecnológica General.' }}
                        </p>

                        @if ($estudianteDetalle->especialidad?->des_esp)
                            <p class="mt-2 text-sm leading-7 text-slate-500 dark:text-slate-400">
                                {{ $estudianteDetalle->especialidad->des_esp }}
                            </p>
                        @endif
                    </section>

                    <section class="sticky bottom-0 -mx-6 -mb-6 border-t border-slate-200 bg-white/90 p-6 backdrop-blur dark:border-slate-700 dark:bg-slate-950/90">
                        <div class="grid gap-3 sm:grid-cols-2">
                            @if ($personaDetalle?->tel_per)
                                <a href="https://wa.me/591{{ preg_replace('/\D/', '', $personaDetalle->tel_per) }}" target="_blank" class="ui-btn-primary justify-center">
                                    Contactar por WhatsApp
                                </a>
                            @endif

                            @if ($personaDetalle?->ema_per)
                                <a href="mailto:{{ $personaDetalle->ema_per }}" class="ui-btn-secondary justify-center">
                                    Enviar correo
                                </a>
                            @endif

                            <button type="button" wire:click="abrirModalHistorial('{{ $estudianteDetalle->cod_est }}')" class="ui-btn-secondary justify-center">
                                Ver historial
                            </button>

                            <button type="button" wire:click="abrirModalInscripcion('{{ $estudianteDetalle->cod_est }}')" class="ui-btn-secondary justify-center">
                                Ver inscripción
                            </button>
                        </div>
                    </section>
                </div>
            </aside>
        </div>
    @endif

    {{-- MODAL REGISTRAR / EDITAR --}}
    @if ($modalRegistrar || $modalEditar)
        <div class="ui-modal-backdrop fixed inset-0 z-50 flex items-center justify-center p-4">
            <div class="absolute inset-0 bg-slate-950/50 backdrop-blur-sm"
                wire:click="{{ $modalRegistrar ? 'cerrarModalRegistrar' : 'cerrarModalEditar' }}"></div>

            <div class="ui-modal relative z-10 w-full max-w-3xl rounded-[2rem]">
                <div class="ui-modal-header">
                    <div>
                        <p class="text-xs font-black uppercase tracking-[0.18em] text-emerald-600 dark:text-emerald-300">
                            {{ $modalRegistrar ? 'Nuevo registro' : 'Actualizar información' }}
                        </p>
                        <h3 class="text-2xl font-black text-slate-950 dark:text-white">
                            {{ $modalRegistrar ? 'Registrar estudiante' : 'Editar estudiante' }}
                        </h3>
                    </div>

                    <button type="button" wire:click="{{ $modalRegistrar ? 'cerrarModalRegistrar' : 'cerrarModalEditar' }}" class="ui-icon-btn">
                        <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M6 18 18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>

                <div class="grid gap-5 p-6 md:grid-cols-2">
                    <div class="md:col-span-2">
                        <label class="ui-label">Persona</label>
                        <select wire:model.live="formEstudiante.cod_per" class="ui-select w-full" @disabled($modalEditar)>
                            <option value="">Seleccionar persona</option>

                            @if ($modalEditar && $estudianteDetalle?->persona)
                                <option value="{{ $estudianteDetalle->persona->cod_per }}">
                                    {{ $this->nombreCompleto($estudianteDetalle->persona) }} · {{ $this->ciCompleto($estudianteDetalle->persona) }}
                                </option>
                            @endif

                            @foreach ($personasDisponibles as $persona)
                                <option value="{{ $persona->cod_per }}">
                                    {{ $this->nombreCompleto($persona) }} · {{ $this->ciCompleto($persona) }}
                                </option>
                            @endforeach
                        </select>
                        @error('formEstudiante.cod_per') <p class="ui-error">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label class="ui-label">RUD/RUDE</label>
                        <input type="text" wire:model.live="formEstudiante.rud_est" class="ui-input w-full" placeholder="Ej: 123456789">
                        @error('formEstudiante.rud_est') <p class="ui-error">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label class="ui-label">Estado</label>
                        <select wire:model.live="formEstudiante.est_est" class="ui-select w-full">
                            @foreach ($estadosEstudiante as $estado)
                                <option value="{{ $estado }}">{{ $this->estadoEstudianteLabel($estado) }}</option>
                            @endforeach
                        </select>
                        @error('formEstudiante.est_est') <p class="ui-error">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label class="ui-label">Tipo de vinculación</label>
                        <select wire:model.live="formEstudiante.cod_tve" class="ui-select w-full">
                            <option value="">Seleccionar</option>
                            @foreach ($tiposVinculacion as $tipo)
                                <option value="{{ $tipo->cod_tve }}">{{ $tipo->nom_tve }}</option>
                            @endforeach
                        </select>
                        @error('formEstudiante.cod_tve') <p class="ui-error">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label class="ui-label">Institución de procedencia</label>
                        <select wire:model.live="formEstudiante.cod_ipe" class="ui-select w-full">
                            <option value="">Seleccionar</option>
                            @foreach ($institucionesProcedencia as $institucion)
                                <option value="{{ $institucion->cod_ipe }}">{{ $institucion->nom_ipe }}</option>
                            @endforeach
                        </select>
                        @error('formEstudiante.cod_ipe') <p class="ui-error">{{ $message }}</p> @enderror
                    </div>

                    <div class="md:col-span-2">
                        <label class="ui-label">Especialidad técnica</label>
                        <select wire:model.live="formEstudiante.cod_esp" class="ui-select w-full">
                            <option value="">Técnica Tecnológica General / Sin especialidad</option>
                            @foreach ($especialidades as $especialidad)
                                <option value="{{ $especialidad->cod_esp }}">{{ $especialidad->nom_esp }}</option>
                            @endforeach
                        </select>
                        @error('formEstudiante.cod_esp') <p class="ui-error">{{ $message }}</p> @enderror
                    </div>
                </div>

                @if (! $this->puedeGuardarEstudiante())
                    <div class="px-6 pb-2">
                        <div class="rounded-2xl border border-amber-200 bg-amber-50 px-4 py-3 text-sm font-semibold text-amber-700 dark:border-amber-400/20 dark:bg-amber-400/10 dark:text-amber-300">
                            Completa persona, RUD/RUDE, vinculación, procedencia y estado para habilitar el guardado.
                        </div>
                    </div>
                @endif

                <div class="ui-modal-footer">
                    <button type="button" wire:click="{{ $modalRegistrar ? 'cerrarModalRegistrar' : 'cerrarModalEditar' }}" class="ui-btn-secondary">
                        Cancelar
                    </button>

                    <button type="button"
                        wire:click="{{ $modalRegistrar ? 'guardarEstudiante' : 'actualizarEstudiante' }}"
                        wire:loading.attr="disabled"
                        @disabled(! $this->puedeGuardarEstudiante())
                        class="{{ $this->puedeGuardarEstudiante() ? 'ui-btn-primary' : 'ui-btn-secondary cursor-not-allowed opacity-60' }}">
                        <span wire:loading.remove>
                            {{ $modalRegistrar ? 'Guardar estudiante' : 'Actualizar estudiante' }}
                        </span>
                        <span wire:loading>Procesando...</span>
                    </button>
                </div>
            </div>
        </div>
    @endif

    {{-- MODAL INSCRIPCIÓN --}}
    @if ($modalInscripcion && $estudianteDetalle)
        <div class="ui-modal-backdrop fixed inset-0 z-50 flex items-center justify-center p-4">
            <div class="absolute inset-0 bg-slate-950/50 backdrop-blur-sm" wire:click="cerrarModalInscripcion"></div>

            <div class="ui-modal relative z-10 w-full max-w-2xl rounded-[2rem]">
                <div class="ui-modal-header">
                    <div>
                        <p class="text-xs font-black uppercase tracking-[0.18em] text-sky-600 dark:text-sky-300">
                            Inscripción académica
                        </p>
                        <h3 class="text-2xl font-black text-slate-950 dark:text-white">
                            {{ $this->nombreCompleto($estudianteDetalle->persona) }}
                        </h3>
                    </div>

                    <button type="button" wire:click="cerrarModalInscripcion" class="ui-icon-btn">
                        <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M6 18 18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>

                <div class="grid gap-5 p-6 md:grid-cols-2">
                    <div>
                        <label class="ui-label">Gestión académica</label>
                        <select wire:model.live="formInscripcion.cod_gea" class="ui-select w-full">
                            <option value="">Seleccionar</option>
                            @foreach ($gestiones as $gestion)
                                <option value="{{ $gestion->cod_gea }}">
                                    {{ $gestion->ani_gea ?? $gestion->cod_gea }}
                                </option>
                            @endforeach
                        </select>
                        @error('formInscripcion.cod_gea') <p class="ui-error">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label class="ui-label">Fecha de inscripción</label>
                        <input type="date" wire:model.live="formInscripcion.fec_ins" class="ui-input w-full">
                        @error('formInscripcion.fec_ins') <p class="ui-error">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label class="ui-label">Curso</label>
                        <select wire:model.live="formInscripcion.cod_cur" class="ui-select w-full">
                            <option value="">Seleccionar</option>
                            @foreach ($cursos as $curso)
                                <option value="{{ $curso->cod_cur }}">{{ $curso->nom_cur }}</option>
                            @endforeach
                        </select>
                        @error('formInscripcion.cod_cur') <p class="ui-error">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label class="ui-label">Paralelo</label>
                        <select wire:model.live="formInscripcion.cod_par" class="ui-select w-full">
                            <option value="">Seleccionar</option>
                            @foreach ($paralelosFormulario as $paralelo)
                                <option value="{{ $paralelo->cod_par }}">{{ $paralelo->nom_par }}</option>
                            @endforeach
                        </select>
                        @error('formInscripcion.cod_par') <p class="ui-error">{{ $message }}</p> @enderror
                        <p class="ui-help mt-1">
                            El paralelo se asigna dentro de la inscripción académica; no pertenece directamente al curso.
                        </p>
                    </div>

                    <div class="md:col-span-2">
                        <label class="ui-label">Estado de inscripción</label>
                        <select wire:model.live="formInscripcion.est_ins" class="ui-select w-full">
                            <option value="ACTIVO">Inscrito</option>
                            <option value="PENDIENTE">Pendiente</option>
                            <option value="INACTIVO">Inactivo</option>
                        </select>
                        @error('formInscripcion.est_ins') <p class="ui-error">{{ $message }}</p> @enderror
                    </div>
                </div>

                @if (! $this->puedeGuardarInscripcion())
                    <div class="px-6 pb-2">
                        <div class="rounded-2xl border border-amber-200 bg-amber-50 px-4 py-3 text-sm font-semibold text-amber-700 dark:border-amber-400/20 dark:bg-amber-400/10 dark:text-amber-300">
                            Selecciona gestión, curso, paralelo y estado de inscripción para habilitar el guardado.
                        </div>
                    </div>
                @endif

                <div class="ui-modal-footer">
                    <button type="button" wire:click="cerrarModalInscripcion" class="ui-btn-secondary">
                        Cancelar
                    </button>

                    <button type="button"
                        wire:click="guardarInscripcion"
                        wire:loading.attr="disabled"
                        @disabled(! $this->puedeGuardarInscripcion())
                        class="{{ $this->puedeGuardarInscripcion() ? 'ui-btn-primary' : 'ui-btn-secondary cursor-not-allowed opacity-60' }}">
                        <span wire:loading.remove>Guardar inscripción</span>
                        <span wire:loading>Procesando...</span>
                    </button>
                </div>
            </div>
        </div>
    @endif

    {{-- MODAL HISTORIAL --}}
    @if ($modalHistorial && $estudianteDetalle)
        <div class="ui-modal-backdrop fixed inset-0 z-50 flex items-center justify-center p-4">
            <div class="absolute inset-0 bg-slate-950/50 backdrop-blur-sm" wire:click="cerrarModalHistorial"></div>

            <div class="ui-modal relative z-10 w-full max-w-3xl rounded-[2rem]">
                <div class="ui-modal-header">
                    <div>
                        <p class="text-xs font-black uppercase tracking-[0.18em] text-violet-600 dark:text-violet-300">
                            Historial académico
                        </p>
                        <h3 class="text-2xl font-black text-slate-950 dark:text-white">
                            {{ $this->nombreCompleto($estudianteDetalle->persona) }}
                        </h3>
                    </div>

                    <button type="button" wire:click="cerrarModalHistorial" class="ui-icon-btn">
                        <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M6 18 18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>

                <div class="p-6">
                    <div class="space-y-4">
                        @forelse ($estudianteDetalle->inscripciones->sortByDesc('cod_gea') as $inscripcion)
                            <div class="rounded-2xl border border-slate-200 bg-slate-50 p-4 dark:border-slate-700 dark:bg-slate-900">
                                <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                                    <div>
                                        <p class="font-black text-slate-950 dark:text-white">
                                            {{ $inscripcion->curso->nom_cur ?? 'Sin curso' }} · Paralelo
                                            {{ $inscripcion->paralelo->nom_par ?? 'Sin paralelo' }}
                                        </p>
                                        <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">
                                            Gestión {{ $inscripcion->gestionAcademica->ani_gea ?? $inscripcion->cod_gea }}
                                        </p>
                                    </div>

                                    <span class="ui-badge-info">Registro académico</span>
                                </div>
                            </div>
                        @empty
                            <div class="rounded-2xl border border-amber-200 bg-amber-50 p-5 text-center dark:border-amber-400/20 dark:bg-amber-400/10">
                                <p class="font-black text-slate-950 dark:text-white">
                                    Sin historial de inscripciones.
                                </p>
                                <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">
                                    Cuando se registre una inscripción, aparecerá en este espacio.
                                </p>
                            </div>
                        @endforelse
                    </div>
                </div>

                <div class="ui-modal-footer">
                    <button type="button" wire:click="cerrarModalHistorial" class="ui-btn-secondary">
                        Cerrar
                    </button>
                </div>
            </div>
        </div>
    @endif

    {{-- SWEETALERT LIVEWIRE --}}
    @once
        <script>
            document.addEventListener('livewire:init', () => {
                Livewire.on('success-general', (event) => {
                    const data = Array.isArray(event) ? event[0] : event;

                    if (window.Swal) {
                        Swal.fire({
                            icon: 'success',
                            title: 'Operación realizada',
                            text: data?.mensaje ?? 'La acción se completó correctamente.',
                            confirmButtonText: 'Entendido',
                            confirmButtonColor: '#059669',
                            timer: 2200,
                            timerProgressBar: true
                        });
                    } else {
                        alert(data?.mensaje ?? 'La acción se completó correctamente.');
                    }
                });

                Livewire.on('error-general', (event) => {
                    const data = Array.isArray(event) ? event[0] : event;

                    if (window.Swal) {
                        Swal.fire({
                            icon: 'error',
                            title: 'No se pudo completar la acción',
                            text: data?.mensaje ?? 'Revisa los datos e intenta nuevamente.',
                            confirmButtonText: 'Entendido',
                            confirmButtonColor: '#dc2626'
                        });
                    } else {
                        alert(data?.mensaje ?? 'Revisa los datos e intenta nuevamente.');
                    }
                });
            });
        </script>
    @endonce
</div>