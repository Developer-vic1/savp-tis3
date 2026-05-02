<div class="space-y-6">

    {{-- ============================================================
    ENCABEZADO INFORMATIVO
    ============================================================ --}}
    <section
        class="relative overflow-hidden rounded-[2rem] border border-[var(--ui-border)] bg-[var(--ui-card)] p-6 shadow-sm">
        <div class="pointer-events-none absolute -right-20 -top-20 h-60 w-60 rounded-full bg-sky-400/10 blur-3xl"></div>
        <div class="pointer-events-none absolute -bottom-24 left-1/4 h-64 w-64 rounded-full bg-emerald-400/10 blur-3xl">
        </div>
        <div class="pointer-events-none absolute bottom-0 right-1/3 h-48 w-48 rounded-full bg-violet-400/10 blur-3xl">
        </div>

        <div class="relative flex flex-col gap-6 xl:flex-row xl:items-center xl:justify-between">
            <div class="max-w-4xl">
                <div class="flex flex-wrap items-center gap-2">
                    <span
                        class="inline-flex items-center gap-2 rounded-full border border-sky-200/70 bg-sky-50 px-3 py-1 text-xs font-black uppercase tracking-[0.16em] text-sky-700 dark:border-sky-400/20 dark:bg-sky-400/10 dark:text-sky-200">
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"
                                d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                        </svg>
                        Auditoría y trazabilidad
                    </span>

                    <span
                        class="inline-flex items-center gap-2 rounded-full border border-emerald-200/70 bg-emerald-50 px-3 py-1 text-xs font-bold text-emerald-700 dark:border-emerald-400/20 dark:bg-emerald-400/10 dark:text-emerald-200">
                        Registro institucional solo lectura
                    </span>
                </div>

                <h2 class="mt-4 text-2xl font-black tracking-tight text-[var(--ui-text)] sm:text-3xl">
                    Bitácora Institucional de Actividades
                </h2>

                <p class="mt-3 max-w-3xl text-sm leading-7 text-[var(--ui-muted)]">
                    Supervisa la actividad registrada en SAVP – TIS 3 mediante una lectura clara de acciones,
                    responsables, módulos afectados, resultados y cambios relevantes. Este módulo preserva la
                    trazabilidad académica-administrativa sin permitir edición o eliminación de eventos.
                </p>
            </div>

            <div class="grid gap-3 sm:grid-cols-3 xl:min-w-[540px]">
                <div class="rounded-2xl border border-[var(--ui-border)] bg-[var(--ui-soft)] px-4 py-3">
                    <p class="text-xs font-bold uppercase tracking-[0.14em] text-[var(--ui-muted)]">
                        Naturaleza
                    </p>
                    <p class="mt-1 text-sm font-black text-[var(--ui-text)]">
                        Auditoría interna
                    </p>
                </div>

                <div class="rounded-2xl border border-[var(--ui-border)] bg-[var(--ui-soft)] px-4 py-3">
                    <p class="text-xs font-bold uppercase tracking-[0.14em] text-[var(--ui-muted)]">
                        Alcance
                    </p>
                    <p class="mt-1 text-sm font-black text-[var(--ui-text)]">
                        Usuarios, estudiantes y academia
                    </p>
                </div>

                <div class="rounded-2xl border border-[var(--ui-border)] bg-[var(--ui-soft)] px-4 py-3">
                    <p class="text-xs font-bold uppercase tracking-[0.14em] text-[var(--ui-muted)]">
                        Integridad
                    </p>
                    <p class="mt-1 text-sm font-black text-[var(--ui-text)]">
                        Sin eliminación física
                    </p>
                </div>
            </div>
        </div>
    </section>

    {{-- ============================================================
    ACCIONES SUPERIORES
    ============================================================ --}}
    <section class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <p class="text-sm font-semibold text-[var(--ui-muted)]">
                Panel de supervisión institucional
            </p>
            <p class="mt-1 text-xs leading-6 text-[var(--ui-muted)]">
                Consulta eventos, responsables, resultados, niveles de importancia, trazabilidad y cambios registrados.
            </p>
        </div>

        <div class="flex flex-wrap items-center gap-2">
            <button type="button" wire:click="actualizarVista" class="ui-btn-secondary inline-flex items-center gap-2">
                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"
                        d="M16.023 9.348h4.992v-.001M2.985 19.644v-4.992m0 0h4.992m-4.993 0 3.181 3.183a8.25 8.25 0 0 0 13.803-3.7M4.031 9.865a8.25 8.25 0 0 1 13.803-3.7l3.181 3.182" />
                </svg>
                Actualizar vista
            </button>

            <button type="button" class="ui-btn-secondary inline-flex items-center gap-2 opacity-70"
                title="Preparado para una siguiente fase">
                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"
                        d="M3 16.5v2.25A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75V16.5M7.5 12 12 16.5m0 0 4.5-4.5M12 16.5V3" />
                </svg>
                Exportar reporte
            </button>
        </div>
    </section>

    {{-- ============================================================
    CARDS RESUMEN
    ============================================================ --}}
    <section class="grid gap-4 sm:grid-cols-2 xl:grid-cols-6">
        <article class="ui-card overflow-hidden rounded-[1.6rem] p-5">
            <div class="flex items-start justify-between gap-3">
                <div>
                    <p class="text-xs font-black uppercase tracking-[0.14em] text-sky-600 dark:text-sky-300">
                        Total eventos
                    </p>
                    <h3 class="mt-3 text-3xl font-black text-[var(--ui-text)]">
                        {{ number_format($totalEventos) }}
                    </h3>
                    <p class="mt-2 text-xs leading-5 text-[var(--ui-muted)]">
                        Registros acumulados en bitácora.
                    </p>
                </div>
                <div class="rounded-2xl bg-sky-100 p-3 text-sky-700 dark:bg-sky-400/10 dark:text-sky-300">
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"
                            d="M19.5 14.25v-2.625a3.375 3.375 0 0 0-3.375-3.375h-1.5A1.125 1.125 0 0 1 13.5 7.125v-1.5A3.375 3.375 0 0 0 10.125 2.25H8.25m0 12.75h7.5m-7.5 3h4.5m2.25-15H6.375A2.625 2.625 0 0 0 3.75 5.875v12.25a2.625 2.625 0 0 0 2.625 2.625h11.25a2.625 2.625 0 0 0 2.625-2.625V7.5L15 2.25Z" />
                    </svg>
                </div>
            </div>
        </article>

        <article class="ui-card overflow-hidden rounded-[1.6rem] p-5">
            <div class="flex items-start justify-between gap-3">
                <div>
                    <p class="text-xs font-black uppercase tracking-[0.14em] text-emerald-600 dark:text-emerald-300">
                        Hoy
                    </p>
                    <h3 class="mt-3 text-3xl font-black text-[var(--ui-text)]">
                        {{ number_format($eventosHoy) }}
                    </h3>
                    <p class="mt-2 text-xs leading-5 text-[var(--ui-muted)]">
                        Actividad registrada en la jornada.
                    </p>
                </div>
                <div
                    class="rounded-2xl bg-emerald-100 p-3 text-emerald-700 dark:bg-emerald-400/10 dark:text-emerald-300">
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"
                            d="M6.75 3v2.25M17.25 3v2.25M3.75 8.25h16.5M4.5 21h15A1.5 1.5 0 0 0 21 19.5V6.75A1.5 1.5 0 0 0 19.5 5.25h-15A1.5 1.5 0 0 0 3 6.75V19.5A1.5 1.5 0 0 0 4.5 21Z" />
                    </svg>
                </div>
            </div>
        </article>

        <article class="ui-card overflow-hidden rounded-[1.6rem] p-5">
            <div class="flex items-start justify-between gap-3">
                <div>
                    <p class="text-xs font-black uppercase tracking-[0.14em] text-emerald-600 dark:text-emerald-300">
                        Correctas
                    </p>
                    <h3 class="mt-3 text-3xl font-black text-[var(--ui-text)]">
                        {{ number_format($eventosExitosos) }}
                    </h3>
                    <p class="mt-2 text-xs leading-5 text-[var(--ui-muted)]">
                        Operaciones finalizadas con éxito.
                    </p>
                </div>
                <div
                    class="rounded-2xl bg-emerald-100 p-3 text-emerald-700 dark:bg-emerald-400/10 dark:text-emerald-300">
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"
                            d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                    </svg>
                </div>
            </div>
        </article>

        <article class="ui-card overflow-hidden rounded-[1.6rem] p-5">
            <div class="flex items-start justify-between gap-3">
                <div>
                    <p class="text-xs font-black uppercase tracking-[0.14em] text-amber-600 dark:text-amber-300">
                        Advertencias
                    </p>
                    <h3 class="mt-3 text-3xl font-black text-[var(--ui-text)]">
                        {{ number_format($eventosAdvertencia) }}
                    </h3>
                    <p class="mt-2 text-xs leading-5 text-[var(--ui-muted)]">
                        Eventos sensibles o preventivos.
                    </p>
                </div>
                <div class="rounded-2xl bg-amber-100 p-3 text-amber-700 dark:bg-amber-400/10 dark:text-amber-300">
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"
                            d="M12 9v3.75m0 3.75h.008v.008H12v-.008ZM10.29 3.86 1.82 18a1.875 1.875 0 0 0 1.607 2.84h17.146A1.875 1.875 0 0 0 22.18 18L13.71 3.86a1.875 1.875 0 0 0-3.42 0Z" />
                    </svg>
                </div>
            </div>
        </article>

        <article class="ui-card overflow-hidden rounded-[1.6rem] p-5">
            <div class="flex items-start justify-between gap-3">
                <div>
                    <p class="text-xs font-black uppercase tracking-[0.14em] text-rose-600 dark:text-rose-300">
                        Fallos
                    </p>
                    <h3 class="mt-3 text-3xl font-black text-[var(--ui-text)]">
                        {{ number_format($eventosFallidos) }}
                    </h3>
                    <p class="mt-2 text-xs leading-5 text-[var(--ui-muted)]">
                        Errores, bloqueos o eventos críticos.
                    </p>
                </div>
                <div class="rounded-2xl bg-rose-100 p-3 text-rose-700 dark:bg-rose-400/10 dark:text-rose-300">
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"
                            d="m9.75 9.75 4.5 4.5m0-4.5-4.5 4.5M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                    </svg>
                </div>
            </div>
        </article>

        <article class="ui-card overflow-hidden rounded-[1.6rem] p-5">
            <div class="flex items-start justify-between gap-3">
                <div>
                    <p class="text-xs font-black uppercase tracking-[0.14em] text-violet-600 dark:text-violet-300">
                        Responsables
                    </p>
                    <h3 class="mt-3 text-3xl font-black text-[var(--ui-text)]">
                        {{ number_format($usuariosConActividad) }}
                    </h3>
                    <p class="mt-2 text-xs leading-5 text-[var(--ui-muted)]">
                        Usuarios con actividad registrada.
                    </p>
                </div>
                <div class="rounded-2xl bg-violet-100 p-3 text-violet-700 dark:bg-violet-400/10 dark:text-violet-300">
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"
                            d="M18 18.72a8.38 8.38 0 0 0-12 0M15 10.5a3 3 0 1 1-6 0 3 3 0 0 1 6 0Zm6 8.25a6.75 6.75 0 0 0-4.2-6.25M3 18.75a6.75 6.75 0 0 1 4.2-6.25" />
                    </svg>
                </div>
            </div>
        </article>
    </section>

    {{-- ============================================================
    ESTADÍSTICAS Y GRÁFICOS
    ============================================================ --}}
    <section class="space-y-4">
        <div class="flex flex-col gap-2 sm:flex-row sm:items-end sm:justify-between">
            <div>
                <h3 class="text-xl font-black text-[var(--ui-text)]">
                    Resumen estadístico institucional
                </h3>
                <p class="mt-1 text-sm text-[var(--ui-muted)]">
                    Lectura visual de actividad por módulo, nivel, periodo reciente y responsables con mayor movimiento.
                </p>
            </div>

            <div
                class="rounded-2xl border border-emerald-200 bg-emerald-50 px-4 py-2 text-xs font-black text-emerald-700 dark:border-emerald-400/20 dark:bg-emerald-400/10 dark:text-emerald-300">
                Éxito general: {{ $porcentajeExito }}%
            </div>
        </div>

        <div class="grid gap-4 xl:grid-cols-4">
            <article class="ui-card rounded-[1.6rem] p-5 xl:col-span-2">
                <div>
                    <h4 class="text-sm font-black text-[var(--ui-text)]">
                        Eventos por módulo
                    </h4>
                    <p class="mt-1 text-xs text-[var(--ui-muted)]">
                        Permite identificar las áreas funcionales con mayor actividad institucional.
                    </p>
                </div>
                <div class="mt-4 h-72" wire:ignore>
                    <canvas id="chartEventosPorModulo"></canvas>
                </div>
            </article>

            <article class="ui-card rounded-[1.6rem] p-5">
                <div>
                    <h4 class="text-sm font-black text-[var(--ui-text)]">
                        Distribución por nivel
                    </h4>
                    <p class="mt-1 text-xs text-[var(--ui-muted)]">
                        Clasificación de eventos informativos, correctos, preventivos y críticos.
                    </p>
                </div>
                <div class="mt-4 h-72" wire:ignore>
                    <canvas id="chartDistribucionNivel"></canvas>
                </div>
            </article>

            <article class="ui-card rounded-[1.6rem] p-5">
                <div>
                    <h4 class="text-sm font-black text-[var(--ui-text)]">
                        Responsables con más actividad
                    </h4>
                    <p class="mt-1 text-xs text-[var(--ui-muted)]">
                        Usuarios con mayor cantidad de eventos registrados.
                    </p>
                </div>
                <div class="mt-4 h-72" wire:ignore>
                    <canvas id="chartUsuariosMasActivos"></canvas>
                </div>
            </article>

            <article class="ui-card rounded-[1.6rem] p-5 xl:col-span-2">
                <div>
                    <h4 class="text-sm font-black text-[var(--ui-text)]">
                        Actividad reciente
                    </h4>
                    <p class="mt-1 text-xs text-[var(--ui-muted)]">
                        Tendencia de eventos durante los últimos siete días.
                    </p>
                </div>
                <div class="mt-4 h-72" wire:ignore>
                    <canvas id="chartActividadReciente"></canvas>
                </div>
            </article>

            <article class="ui-card rounded-[1.6rem] p-5 xl:col-span-2">
                <h4 class="text-sm font-black text-[var(--ui-text)]">
                    Indicadores de lectura rápida
                </h4>
                <p class="mt-1 text-xs text-[var(--ui-muted)]">
                    Métricas derivadas para supervisión directiva y administrativa.
                </p>

                <div class="mt-4 grid gap-3 sm:grid-cols-2 lg:grid-cols-3">
                    @foreach ($miniIndicadores as $indicador)
                        <div class="rounded-2xl border border-[var(--ui-border)] bg-[var(--ui-soft)] px-4 py-3">
                            <p class="text-[11px] font-bold uppercase tracking-[0.12em] text-[var(--ui-muted)]">
                                {{ $indicador['titulo'] }}
                            </p>
                            <p class="mt-2 text-sm font-black text-[var(--ui-text)]">
                                {{ $indicador['valor'] }}
                            </p>
                        </div>
                    @endforeach
                </div>
            </article>
        </div>
    </section>

    {{-- ============================================================
    SELECTOR DE VISTAS
    ============================================================ --}}
    <section class="ui-card rounded-[1.6rem] p-3">
        <div class="flex flex-wrap gap-2">
            @foreach ($vistasDisponibles as $key => $label)
                    <button type="button" wire:click="cambiarVista('{{ $key }}')"
                        class="inline-flex items-center gap-2 rounded-2xl px-4 py-2.5 text-sm font-black transition
                            {{ $vistaActiva === $key
                ? 'bg-[var(--ui-primary)] text-white shadow-sm'
                : 'text-[var(--ui-muted)] hover:bg-[var(--ui-primary-soft)] hover:text-[var(--ui-primary)]' }}">
                        @if ($key === 'tabla')
                            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"
                                    d="M3.75 6.75h16.5M3.75 12h16.5m-16.5 5.25h16.5" />
                            </svg>
                        @elseif ($key === 'modulos')
                            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"
                                    d="M3.75 9.75h16.5m-16.5 0A2.25 2.25 0 0 1 6 7.5h3.879c.597 0 1.17.237 1.591.659l.53.53h6A2.25 2.25 0 0 1 20.25 10.94v5.31A2.25 2.25 0 0 1 18 18.5H6a2.25 2.25 0 0 1-2.25-2.25v-6.5Z" />
                            </svg>
                        @elseif ($key === 'usuarios')
                            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"
                                    d="M18 18.72a8.38 8.38 0 0 0-12 0M15 10.5a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" />
                            </svg>
                        @elseif ($key === 'acciones')
                            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"
                                    d="M10.5 6h9.75M10.5 12h9.75M10.5 18h9.75M3.75 6h.008v.008H3.75V6Zm0 6h.008v.008H3.75V12Zm0 6h.008v.008H3.75V18Z" />
                            </svg>
                        @elseif ($key === 'seguridad')
                            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"
                                    d="M12 3.75 4.5 6.75v5.625c0 4.038 3.06 7.82 7.5 8.875 4.44-1.055 7.5-4.837 7.5-8.875V6.75L12 3.75Z" />
                            </svg>
                        @else
                            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"
                                    d="M12 6v6l3.75 2.25M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                            </svg>
                        @endif

                        {{ $label }}
                    </button>
            @endforeach
        </div>
    </section>

    {{-- ============================================================
    FILTROS
    ============================================================ --}}
    <section class="ui-card rounded-[1.6rem] p-5">
        <div class="grid gap-3 lg:grid-cols-12">
            <div class="lg:col-span-4">
                <label class="mb-1 block text-xs font-black uppercase tracking-[0.12em] text-[var(--ui-muted)]">
                    Búsqueda institucional
                </label>
                <div class="relative">
                    <svg class="pointer-events-none absolute left-4 top-1/2 h-4 w-4 -translate-y-1/2 text-[var(--ui-muted)]"
                        fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"
                            d="m21 21-5.197-5.197M16.5 10.5a6 6 0 1 1-12 0 6 6 0 0 1 12 0Z" />
                    </svg>
                    <input type="text" wire:model.live.debounce.450ms="search" class="ui-input w-full pl-11"
                        placeholder="Buscar por responsable, módulo, registro, descripción, acción o tabla..." />
                </div>
            </div>

            <div class="lg:col-span-2">
                <label class="mb-1 block text-xs font-black uppercase tracking-[0.12em] text-[var(--ui-muted)]">
                    Desde
                </label>
                <input type="date" wire:model.live="fechaDesde" class="ui-input w-full" />
            </div>

            <div class="lg:col-span-2">
                <label class="mb-1 block text-xs font-black uppercase tracking-[0.12em] text-[var(--ui-muted)]">
                    Hasta
                </label>
                <input type="date" wire:model.live="fechaHasta" class="ui-input w-full" />
            </div>

            <div class="lg:col-span-2">
                <label class="mb-1 block text-xs font-black uppercase tracking-[0.12em] text-[var(--ui-muted)]">
                    Responsable
                </label>
                <select wire:model.live="filtroUsuario" class="ui-select w-full">
                    <option value="">Todos</option>
                    <option value="SISTEMA">Sistema</option>
                    @foreach ($usuarios as $usuario)
                        <option value="{{ $usuario->cod_usu }}">
                            {{ $this->nombreUsuarioDesdeModelo($usuario) }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="lg:col-span-2">
                <label class="mb-1 block text-xs font-black uppercase tracking-[0.12em] text-[var(--ui-muted)]">
                    Módulo
                </label>
                <select wire:model.live="filtroModulo" class="ui-select w-full">
                    <option value="">Todos</option>
                    @foreach ($modulos as $modulo)
                        <option value="{{ $modulo }}">{{ $modulo }}</option>
                    @endforeach
                </select>
            </div>

            <div class="lg:col-span-2">
                <label class="mb-1 block text-xs font-black uppercase tracking-[0.12em] text-[var(--ui-muted)]">
                    Nivel
                </label>
                <select wire:model.live="filtroNivel" class="ui-select w-full">
                    <option value="">Todos</option>
                    @foreach ($nivelesDisponibles as $nivel)
                        <option value="{{ $nivel }}">{{ $this->etiquetaNivel($nivel) }}</option>
                    @endforeach
                </select>
            </div>

            <div class="lg:col-span-2">
                <label class="mb-1 block text-xs font-black uppercase tracking-[0.12em] text-[var(--ui-muted)]">
                    Resultado
                </label>
                <select wire:model.live="filtroResultado" class="ui-select w-full">
                    <option value="">Todos</option>
                    @foreach ($resultadosDisponibles as $resultado)
                        <option value="{{ $resultado }}">{{ $this->etiquetaResultado($resultado) }}</option>
                    @endforeach
                </select>
            </div>

            <div class="lg:col-span-2">
                <label class="mb-1 block text-xs font-black uppercase tracking-[0.12em] text-[var(--ui-muted)]">
                    Acción
                </label>
                <select wire:model.live="filtroAccion" class="ui-select w-full">
                    <option value="">Todas</option>
                    @foreach ($acciones as $accion)
                        <option value="{{ $accion }}">{{ $this->traducirAccion($accion) }}</option>
                    @endforeach
                </select>
            </div>

            <div class="lg:col-span-2">
                <label class="mb-1 block text-xs font-black uppercase tracking-[0.12em] text-[var(--ui-muted)]">
                    Entidad
                </label>
                <select wire:model.live="filtroTabla" class="ui-select w-full">
                    <option value="">Todas</option>
                    @foreach ($tablas as $tabla)
                        <option value="{{ $tabla }}">{{ $this->tablaInstitucional($tabla) }}</option>
                    @endforeach
                </select>
            </div>

            <div class="lg:col-span-2">
                <label class="mb-1 block text-xs font-black uppercase tracking-[0.12em] text-[var(--ui-muted)]">
                    Rol
                </label>
                <select wire:model.live="filtroRol" class="ui-select w-full">
                    <option value="">Todos</option>
                    @foreach ($roles as $rol)
                        <option value="{{ $rol }}">{{ $rol }}</option>
                    @endforeach
                </select>
            </div>

            <div class="lg:col-span-1">
                <label class="mb-1 block text-xs font-black uppercase tracking-[0.12em] text-[var(--ui-muted)]">
                    Método
                </label>
                <select wire:model.live="filtroMetodo" class="ui-select w-full">
                    <option value="">Todos</option>
                    @foreach ($metodos as $metodo)
                        <option value="{{ $metodo }}">{{ $metodo }}</option>
                    @endforeach
                </select>
            </div>

            <div class="flex items-end lg:col-span-1">
                <button type="button" wire:click="limpiarFiltros" class="ui-btn-secondary w-full justify-center">
                    Limpiar
                </button>
            </div>
        </div>
    </section>

    {{-- ============================================================
    TABLA GENERAL / SEGURIDAD
    ============================================================ --}}
    @if ($vistaActiva === 'tabla' || $vistaActiva === 'seguridad')
        <section class="ui-card overflow-hidden rounded-[1.6rem]">
            <div
                class="flex flex-col gap-3 border-b border-[var(--ui-border)] p-5 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <h3 class="text-lg font-black text-[var(--ui-text)]">
                        {{ $vistaActiva === 'seguridad' ? 'Eventos sensibles y de seguridad' : 'Registro general de eventos institucionales' }}
                    </h3>
                    <p class="mt-1 text-sm text-[var(--ui-muted)]">
                        {{ $vistaActiva === 'seguridad'
            ? 'Acciones preventivas, advertencias, errores, bloqueos y cambios sensibles que requieren atención administrativa.'
            : 'Consulta cronológica de la actividad registrada en el sistema con lectura humana e información trazable.' }}
                    </p>
                </div>

                <div class="flex items-center gap-2">
                    <select wire:model.live="perPage" class="ui-select">
                        <option value="10">10 filas</option>
                        <option value="15">15 filas</option>
                        <option value="25">25 filas</option>
                        <option value="50">50 filas</option>
                    </select>
                </div>
            </div>

            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-[var(--ui-border)]">
                    <thead class="bg-[var(--ui-soft)]">
                        <tr>
                            <th
                                class="px-5 py-4 text-left text-xs font-black uppercase tracking-[0.12em] text-[var(--ui-muted)]">
                                Fecha
                            </th>
                            <th
                                class="px-5 py-4 text-left text-xs font-black uppercase tracking-[0.12em] text-[var(--ui-muted)]">
                                Responsable
                            </th>
                            <th
                                class="px-5 py-4 text-left text-xs font-black uppercase tracking-[0.12em] text-[var(--ui-muted)]">
                                Hecho institucional
                            </th>
                            <th
                                class="px-5 py-4 text-left text-xs font-black uppercase tracking-[0.12em] text-[var(--ui-muted)]">
                                Área
                            </th>
                            <th
                                class="px-5 py-4 text-left text-xs font-black uppercase tracking-[0.12em] text-[var(--ui-muted)]">
                                Registro afectado
                            </th>
                            <th
                                class="px-5 py-4 text-left text-xs font-black uppercase tracking-[0.12em] text-[var(--ui-muted)]">
                                Nivel
                            </th>
                            <th
                                class="px-5 py-4 text-left text-xs font-black uppercase tracking-[0.12em] text-[var(--ui-muted)]">
                                Resultado
                            </th>
                            <th
                                class="px-5 py-4 text-right text-xs font-black uppercase tracking-[0.12em] text-[var(--ui-muted)]">
                                Revisión
                            </th>
                        </tr>
                    </thead>

                    <tbody class="divide-y divide-[var(--ui-border)] bg-[var(--ui-card)]">
                        @forelse ($eventos as $evento)
                            <tr class="transition hover:bg-[var(--ui-soft)]">
                                <td class="whitespace-nowrap px-5 py-4 align-top">
                                    <p class="text-sm font-black text-[var(--ui-text)]">
                                        {{ $this->fechaCompleta($evento) }}
                                    </p>
                                    <p class="mt-1 text-xs text-[var(--ui-muted)]">
                                        {{ $this->fechaRelativa($evento) }}
                                    </p>
                                </td>

                                <td class="px-5 py-4 align-top">
                                    <div class="flex items-center gap-3">
                                        <div
                                            class="flex h-10 w-10 shrink-0 items-center justify-center rounded-2xl bg-[var(--ui-primary-soft)] text-sm font-black text-[var(--ui-primary)]">
                                            {{ $this->inicialesUsuario($evento) }}
                                        </div>
                                        <div class="min-w-0">
                                            <p class="truncate text-sm font-black text-[var(--ui-text)]">
                                                {{ $this->nombreUsuario($evento) }}
                                            </p>
                                            <p class="mt-1 truncate text-xs text-[var(--ui-muted)]">
                                                {{ $this->rolUsuario($evento) }}
                                            </p>
                                        </div>
                                    </div>
                                </td>

                                <td class="min-w-[360px] px-5 py-4 align-top">
                                    <p class="text-sm font-black text-[var(--ui-text)]">
                                        {{ $this->tituloEvento($evento) }}
                                    </p>
                                    <p class="mt-1 line-clamp-2 text-xs leading-5 text-[var(--ui-muted)]">
                                        {{ \Illuminate\Support\Str::limit($this->descripcionEvento($evento), 150) }}
                                    </p>
                                </td>

                                <td class="px-5 py-4 align-top">
                                    <span class="ui-badge {{ $this->badgeModulo($this->moduloVisible($evento)) }}">
                                        {{ $this->moduloVisible($evento) }}
                                    </span>
                                    <p class="mt-2 text-xs text-[var(--ui-muted)]">
                                        {{ $this->tablaInstitucional($evento->tab_bit) }}
                                    </p>
                                </td>

                                <td class="px-5 py-4 align-top">
                                    <p class="max-w-[230px] truncate text-sm font-bold text-[var(--ui-text)]">
                                        {{ $this->registroInstitucional($evento) }}
                                    </p>
                                    <p class="mt-1 text-xs font-semibold text-[var(--ui-muted)]">
                                        {{ $evento->reg_bit ?: $evento->cod_bit }}
                                    </p>
                                </td>

                                <td class="px-5 py-4 align-top">
                                    <span class="ui-badge {{ $this->badgeNivel($evento->niv_bit) }}">
                                        {{ $this->etiquetaNivel($evento->niv_bit) }}
                                    </span>
                                </td>

                                <td class="px-5 py-4 align-top">
                                    <span class="ui-badge {{ $this->badgeResultado($evento->res_bit) }}">
                                        {{ $this->etiquetaResultado($evento->res_bit) }}
                                    </span>
                                </td>

                                <td class="px-5 py-4 text-right align-top">
                                    <button type="button" wire:click="abrirDetalle('{{ $evento->cod_bit }}')"
                                        class="inline-flex items-center justify-center rounded-xl border border-[var(--ui-border)] bg-[var(--ui-card)] p-2 text-[var(--ui-muted)] transition hover:border-[var(--ui-primary)] hover:text-[var(--ui-primary)]"
                                        title="Ver detalle institucional">
                                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"
                                                d="M2.036 12.322a1.012 1.012 0 0 1 0-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178Z" />
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"
                                                d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" />
                                        </svg>
                                    </button>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="px-5 py-16 text-center">
                                    <div
                                        class="mx-auto flex h-14 w-14 items-center justify-center rounded-2xl bg-[var(--ui-soft)] text-[var(--ui-muted)]">
                                        <svg class="h-7 w-7" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"
                                                d="M19.5 14.25v-2.625a3.375 3.375 0 0 0-3.375-3.375h-1.5A1.125 1.125 0 0 1 13.5 7.125v-1.5A3.375 3.375 0 0 0 10.125 2.25H8.25m0 12.75h7.5m-7.5 3h4.5" />
                                        </svg>
                                    </div>
                                    <h4 class="mt-4 text-base font-black text-[var(--ui-text)]">
                                        No se encontraron eventos
                                    </h4>
                                    <p class="mt-2 text-sm text-[var(--ui-muted)]">
                                        Ajusta los filtros o actualiza la vista para consultar nuevos registros.
                                    </p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="border-t border-[var(--ui-border)] px-5 py-4">
                {{ $eventos->links() }}
            </div>
        </section>
    @endif

    {{-- ============================================================
    VISTA POR MÓDULOS
    ============================================================ --}}
    @if ($vistaActiva === 'modulos')
        <section class="grid gap-4 md:grid-cols-2 xl:grid-cols-3">
            @forelse ($gruposModulo as $grupo)
                <article class="ui-card rounded-[1.6rem] p-5 transition hover:-translate-y-0.5 hover:shadow-md">
                    <div class="flex items-start justify-between gap-4">
                        <div>
                            <div
                                class="flex h-12 w-12 items-center justify-center rounded-2xl bg-sky-100 text-sky-700 dark:bg-sky-400/10 dark:text-sky-300">
                                <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"
                                        d="M3.75 9.75h16.5m-16.5 0A2.25 2.25 0 0 1 6 7.5h3.879c.597 0 1.17.237 1.591.659l.53.53h6A2.25 2.25 0 0 1 20.25 10.94v5.31A2.25 2.25 0 0 1 18 18.5H6a2.25 2.25 0 0 1-2.25-2.25v-6.5Z" />
                                </svg>
                            </div>

                            <h3 class="mt-4 text-lg font-black text-[var(--ui-text)]">
                                {{ $grupo->modulo }}
                            </h3>
                            <p class="mt-1 text-sm text-[var(--ui-muted)]">
                                Última actividad:
                                {{ $grupo->ultima_actividad ? \Carbon\Carbon::parse($grupo->ultima_actividad)->diffForHumans() : 'Sin actividad' }}
                            </p>
                        </div>

                        <span class="ui-badge {{ $this->badgeModulo($grupo->modulo) }}">
                            Área funcional
                        </span>
                    </div>

                    <div class="mt-5 grid grid-cols-3 gap-3">
                        <div class="rounded-2xl bg-[var(--ui-soft)] px-3 py-3">
                            <p class="text-xs text-[var(--ui-muted)]">Eventos</p>
                            <p class="mt-1 text-lg font-black text-[var(--ui-text)]">{{ $grupo->total }}</p>
                        </div>
                        <div class="rounded-2xl bg-emerald-50 px-3 py-3 dark:bg-emerald-400/10">
                            <p class="text-xs text-emerald-700 dark:text-emerald-300">Correctos</p>
                            <p class="mt-1 text-lg font-black text-emerald-700 dark:text-emerald-300">{{ $grupo->exitosos }}</p>
                        </div>
                        <div class="rounded-2xl bg-rose-50 px-3 py-3 dark:bg-rose-400/10">
                            <p class="text-xs text-rose-700 dark:text-rose-300">Fallos</p>
                            <p class="mt-1 text-lg font-black text-rose-700 dark:text-rose-300">{{ $grupo->errores }}</p>
                        </div>
                    </div>

                    <button type="button" wire:click="filtrarModulo('{{ $grupo->modulo }}')"
                        class="ui-btn-secondary mt-5 w-full justify-center">
                        Revisar actividad del módulo
                    </button>
                </article>
            @empty
                <div class="ui-card rounded-[1.6rem] p-8 text-center md:col-span-2 xl:col-span-3">
                    <p class="text-sm font-semibold text-[var(--ui-muted)]">
                        No existen módulos registrados en la bitácora.
                    </p>
                </div>
            @endforelse
        </section>
    @endif

    {{-- ============================================================
    VISTA POR USUARIOS
    ============================================================ --}}
    @if ($vistaActiva === 'usuarios')
        <section class="grid gap-4 md:grid-cols-2 xl:grid-cols-3">
            @forelse ($gruposUsuario as $grupo)
                <article class="ui-card rounded-[1.6rem] p-5 transition hover:-translate-y-0.5 hover:shadow-md">
                    <div class="flex items-start gap-4">
                        <div
                            class="flex h-12 w-12 shrink-0 items-center justify-center rounded-2xl bg-violet-100 text-sm font-black text-violet-700 dark:bg-violet-400/10 dark:text-violet-300">
                            {{ $grupo->usuario_key === 'SISTEMA' ? 'S' : mb_strtoupper(mb_substr($grupo->nombre_visible, 0, 1)) }}
                        </div>

                        <div class="min-w-0 flex-1">
                            <h3 class="truncate text-lg font-black text-[var(--ui-text)]">
                                {{ $grupo->nombre_visible }}
                            </h3>
                            <p class="mt-1 truncate text-sm text-[var(--ui-muted)]">
                                {{ $grupo->correo_visible }}
                            </p>
                            <span class="ui-badge ui-badge-violet mt-3">
                                {{ $grupo->rol_visible }}
                            </span>
                        </div>
                    </div>

                    <div class="mt-5 grid grid-cols-4 gap-2">
                        <div class="rounded-2xl bg-[var(--ui-soft)] px-3 py-3 text-center">
                            <p class="text-xs text-[var(--ui-muted)]">Total</p>
                            <p class="mt-1 text-lg font-black text-[var(--ui-text)]">{{ $grupo->total }}</p>
                        </div>
                        <div class="rounded-2xl bg-emerald-50 px-3 py-3 text-center dark:bg-emerald-400/10">
                            <p class="text-xs text-emerald-700 dark:text-emerald-300">OK</p>
                            <p class="mt-1 text-lg font-black text-emerald-700 dark:text-emerald-300">{{ $grupo->exitosos }}</p>
                        </div>
                        <div class="rounded-2xl bg-amber-50 px-3 py-3 text-center dark:bg-amber-400/10">
                            <p class="text-xs text-amber-700 dark:text-amber-300">Adv.</p>
                            <p class="mt-1 text-lg font-black text-amber-700 dark:text-amber-300">{{ $grupo->advertencias }}</p>
                        </div>
                        <div class="rounded-2xl bg-rose-50 px-3 py-3 text-center dark:bg-rose-400/10">
                            <p class="text-xs text-rose-700 dark:text-rose-300">Err.</p>
                            <p class="mt-1 text-lg font-black text-rose-700 dark:text-rose-300">{{ $grupo->errores }}</p>
                        </div>
                    </div>

                    <p class="mt-4 text-xs text-[var(--ui-muted)]">
                        Última acción:
                        {{ $grupo->ultima_actividad ? \Carbon\Carbon::parse($grupo->ultima_actividad)->diffForHumans() : 'Sin actividad' }}
                    </p>

                    <button type="button" wire:click="filtrarUsuario('{{ $grupo->usuario_key }}')"
                        class="ui-btn-secondary mt-5 w-full justify-center">
                        Revisar actividad del responsable
                    </button>
                </article>
            @empty
                <div class="ui-card rounded-[1.6rem] p-8 text-center md:col-span-2 xl:col-span-3">
                    <p class="text-sm font-semibold text-[var(--ui-muted)]">
                        No existen responsables con actividad registrada.
                    </p>
                </div>
            @endforelse
        </section>
    @endif

    {{-- ============================================================
    VISTA POR ACCIONES
    ============================================================ --}}
    @if ($vistaActiva === 'acciones')
        <section class="grid gap-4 md:grid-cols-2 xl:grid-cols-3">
            @forelse ($gruposAccion as $grupo)
                <article class="ui-card rounded-[1.6rem] p-5 transition hover:-translate-y-0.5 hover:shadow-md">
                    <div class="flex items-start justify-between gap-4">
                        <div>
                            <div
                                class="flex h-12 w-12 items-center justify-center rounded-2xl bg-emerald-100 text-emerald-700 dark:bg-emerald-400/10 dark:text-emerald-300">
                                <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"
                                        d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                                </svg>
                            </div>
                            <h3 class="mt-4 text-lg font-black text-[var(--ui-text)]">
                                {{ $grupo->titulo_humano }}
                            </h3>
                            <p class="mt-1 text-xs font-semibold text-[var(--ui-muted)]">
                                Código técnico disponible en detalle.
                            </p>
                        </div>

                        <span class="ui-badge {{ $this->badgeResultado($grupo->resultado_predominante) }}">
                            {{ $this->etiquetaResultado($grupo->resultado_predominante) }}
                        </span>
                    </div>

                    <div class="mt-5 grid grid-cols-2 gap-3">
                        <div class="rounded-2xl bg-[var(--ui-soft)] px-4 py-3">
                            <p class="text-xs text-[var(--ui-muted)]">Ejecuciones</p>
                            <p class="mt-1 text-xl font-black text-[var(--ui-text)]">{{ $grupo->total }}</p>
                        </div>
                        <div class="rounded-2xl bg-[var(--ui-soft)] px-4 py-3">
                            <p class="text-xs text-[var(--ui-muted)]">Última</p>
                            <p class="mt-1 text-sm font-black text-[var(--ui-text)]">
                                {{ $grupo->ultima_actividad ? \Carbon\Carbon::parse($grupo->ultima_actividad)->diffForHumans() : 'Sin fecha' }}
                            </p>
                        </div>
                    </div>

                    <div class="mt-4 flex flex-wrap gap-2">
                        @foreach ($grupo->modulos as $modulo)
                            <span class="ui-badge {{ $this->badgeModulo($modulo) }}">
                                {{ $modulo }}
                            </span>
                        @endforeach
                    </div>

                    <button type="button" wire:click="filtrarAccion('{{ $grupo->acc_bit }}')"
                        class="ui-btn-secondary mt-5 w-full justify-center">
                        Filtrar este tipo de evento
                    </button>
                </article>
            @empty
                <div class="ui-card rounded-[1.6rem] p-8 text-center md:col-span-2 xl:col-span-3">
                    <p class="text-sm font-semibold text-[var(--ui-muted)]">
                        No existen acciones registradas.
                    </p>
                </div>
            @endforelse
        </section>
    @endif

    {{-- ============================================================
    TIMELINE
    ============================================================ --}}
    @if ($vistaActiva === 'timeline')
        <section class="ui-card rounded-[1.6rem] p-6">
            <div class="mb-6">
                <h3 class="text-lg font-black text-[var(--ui-text)]">
                    Timeline institucional
                </h3>
                <p class="mt-1 text-sm text-[var(--ui-muted)]">
                    Lectura cronológica agrupada por fecha para revisión rápida de la actividad institucional.
                </p>
            </div>

            <div class="space-y-8">
                @forelse ($eventosTimeline as $grupoFecha => $items)
                    <div>
                        <h4 class="mb-4 text-sm font-black uppercase tracking-[0.14em] text-[var(--ui-muted)]">
                            {{ $grupoFecha }}
                        </h4>

                        <div class="relative space-y-4 border-l border-[var(--ui-border)] pl-6">
                            @foreach ($items as $evento)
                                <article class="relative rounded-2xl border border-[var(--ui-border)] bg-[var(--ui-soft)] p-4">
                                    <span
                                        class="absolute -left-[31px] top-5 h-3 w-3 rounded-full ring-4 ring-[var(--ui-card)] {{ $this->colorPuntoNivel($evento->niv_bit) }}">
                                    </span>

                                    <div class="flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
                                        <div>
                                            <p class="text-sm font-black text-[var(--ui-text)]">
                                                {{ $this->tituloEvento($evento) }}
                                            </p>
                                            <p class="mt-1 text-xs text-[var(--ui-muted)]">
                                                {{ $this->fechaCompleta($evento) }} · {{ $this->nombreUsuario($evento) }}
                                            </p>
                                            <p class="mt-2 text-sm leading-6 text-[var(--ui-muted)]">
                                                {{ \Illuminate\Support\Str::limit($this->descripcionEvento($evento), 180) }}
                                            </p>
                                        </div>

                                        <div class="flex shrink-0 flex-wrap gap-2">
                                            <span class="ui-badge {{ $this->badgeNivel($evento->niv_bit) }}">
                                                {{ $this->etiquetaNivel($evento->niv_bit) }}
                                            </span>
                                            <span class="ui-badge {{ $this->badgeResultado($evento->res_bit) }}">
                                                {{ $this->etiquetaResultado($evento->res_bit) }}
                                            </span>
                                        </div>
                                    </div>

                                    <button type="button" wire:click="abrirDetalle('{{ $evento->cod_bit }}')"
                                        class="mt-4 text-xs font-black text-[var(--ui-primary)] hover:underline">
                                        Ver detalle institucional
                                    </button>
                                </article>
                            @endforeach
                        </div>
                    </div>
                @empty
                    <div class="rounded-2xl border border-[var(--ui-border)] bg-[var(--ui-soft)] p-8 text-center">
                        <p class="text-sm font-semibold text-[var(--ui-muted)]">
                            No hay eventos para construir el timeline.
                        </p>
                    </div>
                @endforelse
            </div>
        </section>
    @endif

    {{-- ============================================================
    DRAWER DERECHO DE DETALLE
    ============================================================ --}}
    @if ($drawerDetalle && $eventoDetalle)
        <div class="fixed inset-0 z-50 overflow-hidden">
            <div class="absolute inset-0 bg-slate-950/25 backdrop-blur-[2px]" wire:click="cerrarDetalle"></div>

            <aside
                class="absolute right-0 top-0 flex h-full w-full max-w-4xl flex-col border-l border-[var(--ui-border)] bg-white shadow-2xl dark:bg-slate-950">
                <div
                    class="sticky top-0 z-10 border-b border-[var(--ui-border)] bg-white/95 p-6 backdrop-blur dark:bg-slate-950/95">
                    <div class="flex items-start justify-between gap-4">
                        <div class="max-w-3xl">
                            <div class="flex flex-wrap items-center gap-2">
                                <span class="ui-badge {{ $this->badgeModulo($this->moduloVisible($eventoDetalle)) }}">
                                    {{ $this->moduloVisible($eventoDetalle) }}
                                </span>

                                <span class="ui-badge {{ $this->badgeNivel($eventoDetalle->niv_bit) }}">
                                    {{ $this->etiquetaNivel($eventoDetalle->niv_bit) }}
                                </span>

                                <span class="ui-badge {{ $this->badgeResultado($eventoDetalle->res_bit) }}">
                                    {{ $this->etiquetaResultado($eventoDetalle->res_bit) }}
                                </span>
                            </div>

                            <h3 class="mt-3 text-2xl font-black tracking-tight text-[var(--ui-text)]">
                                {{ $this->accionInstitucional($eventoDetalle->acc_bit) }}
                            </h3>

                            <p class="mt-2 text-sm leading-7 text-[var(--ui-muted)]">
                                {{ $this->descripcionEvento($eventoDetalle) }}
                            </p>

                            <p class="mt-2 text-xs font-semibold text-[var(--ui-muted)]">
                                {{ $this->fechaRelativa($eventoDetalle) }} · {{ $this->fechaCompleta($eventoDetalle) }}
                            </p>
                        </div>

                        <button type="button" wire:click="cerrarDetalle"
                            class="rounded-2xl border border-[var(--ui-border)] p-2 text-[var(--ui-muted)] transition hover:border-rose-300 hover:text-rose-600">
                            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"
                                    d="M6 18 18 6M6 6l12 12" />
                            </svg>
                        </button>
                    </div>
                </div>

                <div class="flex-1 space-y-5 overflow-y-auto p-6">
                    {{-- Resumen ejecutivo --}}
                    <section class="rounded-[1.6rem] border border-[var(--ui-border)] bg-[var(--ui-soft)] p-5">
                        <div class="flex items-start gap-4">
                            <div
                                class="flex h-12 w-12 shrink-0 items-center justify-center rounded-2xl bg-[var(--ui-primary-soft)] text-sm font-black text-[var(--ui-primary)]">
                                {{ $this->inicialesUsuario($eventoDetalle) }}
                            </div>

                            <div>
                                <p class="text-xs font-black uppercase tracking-[0.14em] text-[var(--ui-muted)]">
                                    Resumen institucional
                                </p>
                                <h4 class="mt-2 text-lg font-black text-[var(--ui-text)]">
                                    {{ $this->resumenInstitucionalEvento($eventoDetalle) }}
                                </h4>
                                <p class="mt-2 text-sm leading-7 text-[var(--ui-muted)]">
                                    Este evento forma parte del historial de auditoría del sistema y permite verificar
                                    la continuidad, responsabilidad y trazabilidad de las operaciones institucionales.
                                </p>
                            </div>
                        </div>
                    </section>

                    {{-- Responsable + registro afectado --}}
                    <div class="grid gap-5 lg:grid-cols-2">
                        <section class="rounded-[1.6rem] border border-[var(--ui-border)] bg-[var(--ui-card)] p-5">
                            <h4 class="text-sm font-black text-[var(--ui-text)]">
                                Responsable del evento
                            </h4>

                            <div class="mt-4 flex items-center gap-3">
                                <div
                                    class="flex h-12 w-12 items-center justify-center rounded-2xl bg-violet-100 text-sm font-black text-violet-700 dark:bg-violet-400/10 dark:text-violet-300">
                                    {{ $this->inicialesUsuario($eventoDetalle) }}
                                </div>
                                <div>
                                    <p class="text-sm font-black text-[var(--ui-text)]">
                                        {{ $this->nombreUsuario($eventoDetalle) }}
                                    </p>
                                    <p class="mt-1 text-xs text-[var(--ui-muted)]">
                                        {{ $this->correoUsuario($eventoDetalle) }}
                                    </p>
                                </div>
                            </div>

                            <div class="mt-4 grid gap-3 sm:grid-cols-2">
                                <div class="rounded-2xl bg-[var(--ui-soft)] px-4 py-3">
                                    <p class="text-xs font-bold uppercase tracking-[0.12em] text-[var(--ui-muted)]">
                                        Rol
                                    </p>
                                    <p class="mt-1 text-sm font-black text-[var(--ui-text)]">
                                        {{ $this->rolUsuario($eventoDetalle) }}
                                    </p>
                                </div>

                                <div class="rounded-2xl bg-[var(--ui-soft)] px-4 py-3">
                                    <p class="text-xs font-bold uppercase tracking-[0.12em] text-[var(--ui-muted)]">
                                        Código
                                    </p>
                                    <p class="mt-1 text-sm font-black text-[var(--ui-text)]">
                                        {{ $eventoDetalle->cod_usu ?? 'Sistema' }}
                                    </p>
                                </div>
                            </div>
                        </section>

                        <section class="rounded-[1.6rem] border border-[var(--ui-border)] bg-[var(--ui-card)] p-5">
                            <h4 class="text-sm font-black text-[var(--ui-text)]">
                                Registro afectado
                            </h4>

                            <div class="mt-4 space-y-3">
                                <div class="rounded-2xl bg-[var(--ui-soft)] px-4 py-3">
                                    <p class="text-xs font-bold uppercase tracking-[0.12em] text-[var(--ui-muted)]">
                                        Nombre interpretado
                                    </p>
                                    <p class="mt-1 text-sm font-black text-[var(--ui-text)]">
                                        {{ $this->registroInstitucional($eventoDetalle) }}
                                    </p>
                                </div>

                                <div class="grid gap-3 sm:grid-cols-2">
                                    <div class="rounded-2xl bg-[var(--ui-soft)] px-4 py-3">
                                        <p class="text-xs font-bold uppercase tracking-[0.12em] text-[var(--ui-muted)]">
                                            Entidad
                                        </p>
                                        <p class="mt-1 text-sm font-black text-[var(--ui-text)]">
                                            {{ $this->tablaInstitucional($eventoDetalle->tab_bit) }}
                                        </p>
                                    </div>

                                    <div class="rounded-2xl bg-[var(--ui-soft)] px-4 py-3">
                                        <p class="text-xs font-bold uppercase tracking-[0.12em] text-[var(--ui-muted)]">
                                            Identificador
                                        </p>
                                        <p class="mt-1 break-all text-sm font-black text-[var(--ui-text)]">
                                            {{ $eventoDetalle->reg_bit ?? $eventoDetalle->cod_bit }}
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </section>
                    </div>

                    {{-- Cambios registrados --}}
                    <section class="rounded-[1.6rem] border border-[var(--ui-border)] bg-[var(--ui-card)] p-5">
                        <div class="flex flex-col gap-2 sm:flex-row sm:items-end sm:justify-between">
                            <div>
                                <h4 class="text-sm font-black text-[var(--ui-text)]">
                                    Cambios o datos registrados
                                </h4>
                                <p class="mt-1 text-xs text-[var(--ui-muted)]">
                                    Los nombres de campos se muestran interpretados para facilitar la revisión
                                    administrativa.
                                </p>
                            </div>
                        </div>

                        @php
                            $cambios = $this->cambiosRegistrados($eventoDetalle);
                            $datos = $this->datosRegistrados($eventoDetalle);
                        @endphp

                        @if (!empty($cambios))
                            <div class="mt-4 overflow-hidden rounded-2xl border border-[var(--ui-border)]">
                                <table class="min-w-full divide-y divide-[var(--ui-border)]">
                                    <thead class="bg-[var(--ui-soft)]">
                                        <tr>
                                            <th
                                                class="px-4 py-3 text-left text-xs font-black uppercase tracking-[0.12em] text-[var(--ui-muted)]">
                                                Dato
                                            </th>
                                            <th
                                                class="px-4 py-3 text-left text-xs font-black uppercase tracking-[0.12em] text-[var(--ui-muted)]">
                                                Antes
                                            </th>
                                            <th
                                                class="px-4 py-3 text-left text-xs font-black uppercase tracking-[0.12em] text-[var(--ui-muted)]">
                                                Después
                                            </th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-[var(--ui-border)]">
                                        @foreach ($cambios as $cambio)
                                            <tr class="{{ $cambio['cambio'] ? 'bg-amber-50/60 dark:bg-amber-400/5' : '' }}">
                                                <td class="px-4 py-3 text-xs font-black text-[var(--ui-text)]">
                                                    {{ $cambio['campo'] }}
                                                </td>
                                                <td class="max-w-[260px] px-4 py-3 text-xs text-[var(--ui-muted)]">
                                                    <span class="break-words">{{ $cambio['antes'] }}</span>
                                                </td>
                                                <td class="max-w-[260px] px-4 py-3 text-xs text-[var(--ui-text)]">
                                                    <span class="break-words font-semibold">{{ $cambio['despues'] }}</span>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @elseif (!empty($datos))
                            <div class="mt-4 grid gap-3 sm:grid-cols-2">
                                @foreach ($datos as $dato)
                                    <div class="rounded-2xl border border-[var(--ui-border)] bg-[var(--ui-soft)] px-4 py-3">
                                        <p class="text-xs font-bold uppercase tracking-[0.12em] text-[var(--ui-muted)]">
                                            {{ $dato['campo'] }}
                                        </p>
                                        <p class="mt-1 break-words text-sm font-bold text-[var(--ui-text)]">
                                            {{ $dato['valor'] }}
                                        </p>
                                    </div>
                                @endforeach
                            </div>
                        @else
                            <p class="mt-4 rounded-2xl bg-[var(--ui-soft)] px-4 py-4 text-sm leading-6 text-[var(--ui-muted)]">
                                Este evento no registró valores comparativos. La bitácora conserva el hecho institucional,
                                el responsable, el módulo afectado y el resultado de la operación.
                            </p>
                        @endif
                    </section>

                    @if ($eventoDetalle->err_bit)
                        <section
                            class="rounded-[1.6rem] border border-rose-200 bg-rose-50 p-5 dark:border-rose-400/20 dark:bg-rose-400/10">
                            <h4 class="text-sm font-black text-rose-700 dark:text-rose-300">
                                Error registrado
                            </h4>
                            <p class="mt-2 break-words text-sm leading-6 text-rose-700 dark:text-rose-200">
                                {{ $eventoDetalle->err_bit }}
                            </p>
                        </section>
                    @endif

                    {{-- Detalle técnico secundario --}}
                    <details class="rounded-[1.6rem] border border-[var(--ui-border)] bg-[var(--ui-card)] p-5">
                        <summary class="cursor-pointer text-sm font-black text-[var(--ui-text)]">
                            Información técnica del evento
                        </summary>

                        <p class="mt-2 text-xs leading-6 text-[var(--ui-muted)]">
                            Esta información se conserva para auditoría técnica, depuración y trazabilidad interna del
                            sistema.
                        </p>

                        <div class="mt-4 grid gap-3 sm:grid-cols-2">
                            <div class="rounded-2xl bg-[var(--ui-soft)] px-4 py-3">
                                <p class="text-xs font-bold uppercase tracking-[0.12em] text-[var(--ui-muted)]">
                                    Código de acción
                                </p>
                                <p class="mt-1 break-all text-sm font-bold text-[var(--ui-text)]">
                                    {{ $eventoDetalle->acc_bit ?? 'No registrado' }}
                                </p>
                            </div>

                            <div class="rounded-2xl bg-[var(--ui-soft)] px-4 py-3">
                                <p class="text-xs font-bold uppercase tracking-[0.12em] text-[var(--ui-muted)]">
                                    Código de bitácora
                                </p>
                                <p class="mt-1 break-all text-sm font-bold text-[var(--ui-text)]">
                                    {{ $eventoDetalle->cod_bit }}
                                </p>
                            </div>

                            <div class="rounded-2xl bg-[var(--ui-soft)] px-4 py-3">
                                <p class="text-xs font-bold uppercase tracking-[0.12em] text-[var(--ui-muted)]">
                                    Tabla real
                                </p>
                                <p class="mt-1 break-all text-sm font-bold text-[var(--ui-text)]">
                                    {{ $eventoDetalle->tab_bit ?? 'No registrada' }}
                                </p>
                            </div>

                            <div class="rounded-2xl bg-[var(--ui-soft)] px-4 py-3">
                                <p class="text-xs font-bold uppercase tracking-[0.12em] text-[var(--ui-muted)]">
                                    Registro real
                                </p>
                                <p class="mt-1 break-all text-sm font-bold text-[var(--ui-text)]">
                                    {{ $eventoDetalle->reg_bit ?? 'No registrado' }}
                                </p>
                            </div>

                            <div class="rounded-2xl bg-[var(--ui-soft)] px-4 py-3">
                                <p class="text-xs font-bold uppercase tracking-[0.12em] text-[var(--ui-muted)]">
                                    IP
                                </p>
                                <p class="mt-1 break-all text-sm font-bold text-[var(--ui-text)]">
                                    {{ $eventoDetalle->ip_bit ?? 'No registrada' }}
                                </p>
                            </div>

                            <div class="rounded-2xl bg-[var(--ui-soft)] px-4 py-3">
                                <p class="text-xs font-bold uppercase tracking-[0.12em] text-[var(--ui-muted)]">
                                    Método
                                </p>
                                <p class="mt-1 text-sm font-bold text-[var(--ui-text)]">
                                    {{ $eventoDetalle->met_bit ?? 'No registrado' }}
                                </p>
                            </div>

                            <div class="rounded-2xl bg-[var(--ui-soft)] px-4 py-3 sm:col-span-2">
                                <p class="text-xs font-bold uppercase tracking-[0.12em] text-[var(--ui-muted)]">
                                    Ruta
                                </p>
                                <p class="mt-1 break-all text-sm font-bold text-[var(--ui-text)]">
                                    {{ $eventoDetalle->rut_bit ?? 'No registrada' }}
                                </p>
                            </div>

                            <div class="rounded-2xl bg-[var(--ui-soft)] px-4 py-3 sm:col-span-2">
                                <p class="text-xs font-bold uppercase tracking-[0.12em] text-[var(--ui-muted)]">
                                    Navegador / dispositivo
                                </p>
                                <p class="mt-1 break-words text-xs leading-5 text-[var(--ui-muted)]">
                                    {{ $eventoDetalle->age_bit ?? 'No registrado' }}
                                </p>
                            </div>
                        </div>
                    </details>
                </div>
            </aside>
        </div>
    @endif

    {{-- ============================================================
    SCRIPTS CHART.JS
    ============================================================ --}}
    @script
    <script>
        const initialData = @js($datosGraficos);

        window.__bitacoraCharts = window.__bitacoraCharts || {};

        const chartColors = {
            sky: 'rgba(14, 165, 233, 0.75)',
            emerald: 'rgba(16, 185, 129, 0.75)',
            violet: 'rgba(139, 92, 246, 0.75)',
            amber: 'rgba(245, 158, 11, 0.75)',
            rose: 'rgba(244, 63, 94, 0.75)',
            slate: 'rgba(100, 116, 139, 0.75)',
            grid: 'rgba(148, 163, 184, 0.18)',
        };

        function getChartConstructor() {
            return window.Chart || (typeof Chart !== 'undefined' ? Chart : null);
        }

        function destroyChart(id) {
            if (window.__bitacoraCharts[id]) {
                window.__bitacoraCharts[id].destroy();
                delete window.__bitacoraCharts[id];
            }
        }

        function renderBarChart(id, labels, data, horizontal = false) {
            const ChartConstructor = getChartConstructor();
            const canvas = document.getElementById(id);

            if (!ChartConstructor || !canvas) return;

            destroyChart(id);

            window.__bitacoraCharts[id] = new ChartConstructor(canvas, {
                type: 'bar',
                data: {
                    labels: labels || [],
                    datasets: [{
                        data: data || [],
                        borderWidth: 1,
                        borderRadius: 10,
                        backgroundColor: [
                            chartColors.sky,
                            chartColors.emerald,
                            chartColors.violet,
                            chartColors.amber,
                            chartColors.rose,
                            chartColors.slate,
                        ],
                    }]
                },
                options: {
                    indexAxis: horizontal ? 'y' : 'x',
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: { display: false }
                    },
                    scales: {
                        x: {
                            grid: { color: chartColors.grid },
                            ticks: {
                                color: '#64748b',
                                font: { size: 11, weight: '600' }
                            }
                        },
                        y: {
                            beginAtZero: true,
                            grid: { color: chartColors.grid },
                            ticks: {
                                precision: 0,
                                color: '#64748b',
                                font: { size: 11, weight: '600' }
                            }
                        }
                    }
                }
            });
        }

        function renderDoughnutChart(id, labels, data) {
            const ChartConstructor = getChartConstructor();
            const canvas = document.getElementById(id);

            if (!ChartConstructor || !canvas) return;

            destroyChart(id);

            window.__bitacoraCharts[id] = new ChartConstructor(canvas, {
                type: 'doughnut',
                data: {
                    labels: labels || [],
                    datasets: [{
                        data: data || [],
                        borderWidth: 3,
                        borderColor: 'rgba(255,255,255,0.8)',
                        backgroundColor: [
                            chartColors.sky,
                            chartColors.emerald,
                            chartColors.amber,
                            chartColors.rose,
                            chartColors.violet,
                            chartColors.slate,
                        ],
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    cutout: '68%',
                    plugins: {
                        legend: {
                            position: 'bottom',
                            labels: {
                                color: '#64748b',
                                boxWidth: 10,
                                usePointStyle: true,
                                font: { size: 11, weight: '600' }
                            }
                        }
                    }
                }
            });
        }

        function renderLineChart(id, labels, data) {
            const ChartConstructor = getChartConstructor();
            const canvas = document.getElementById(id);

            if (!ChartConstructor || !canvas) return;

            destroyChart(id);

            window.__bitacoraCharts[id] = new ChartConstructor(canvas, {
                type: 'line',
                data: {
                    labels: labels || [],
                    datasets: [{
                        data: data || [],
                        fill: true,
                        tension: 0.38,
                        borderWidth: 3,
                        pointRadius: 4,
                        pointHoverRadius: 6,
                        borderColor: chartColors.sky,
                        backgroundColor: 'rgba(14, 165, 233, 0.12)',
                        pointBackgroundColor: chartColors.sky,
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: { display: false }
                    },
                    scales: {
                        x: {
                            grid: { color: chartColors.grid },
                            ticks: {
                                color: '#64748b',
                                font: { size: 11, weight: '600' }
                            }
                        },
                        y: {
                            beginAtZero: true,
                            grid: { color: chartColors.grid },
                            ticks: {
                                precision: 0,
                                color: '#64748b',
                                font: { size: 11, weight: '600' }
                            }
                        }
                    }
                }
            });
        }

        function renderBitacoraCharts(data) {
            if (!data) return;

            renderBarChart(
                'chartEventosPorModulo',
                data.eventosPorModulo?.labels || [],
                data.eventosPorModulo?.data || [],
                false
            );

            renderDoughnutChart(
                'chartDistribucionNivel',
                data.distribucionNivel?.labels || [],
                data.distribucionNivel?.data || []
            );

            renderLineChart(
                'chartActividadReciente',
                data.actividadReciente?.labels || [],
                data.actividadReciente?.data || []
            );

            renderBarChart(
                'chartUsuariosMasActivos',
                data.usuariosMasActivos?.labels || [],
                data.usuariosMasActivos?.data || [],
                true
            );
        }

        setTimeout(() => renderBitacoraCharts(initialData), 150);

        Livewire.on('actualizar-graficos-bitacora', (event) => {
            const payload = Array.isArray(event) ? event[0] : event;
            setTimeout(() => renderBitacoraCharts(payload.data || payload), 150);
        });
    </script>
    @endscript
</div>