<div
    x-data="{
        modalNueva: @entangle('showCreateModal').live,
        drawerDetalle: @entangle('showDetailDrawer').live,
        modalCierre: @entangle('showCloseModal').live,

        anio: @entangle('form.anio').live,
        nombre: @entangle('form.nombre').live,
        fechaInicio: @entangle('form.fecha_inicio').live,
        fechaFin: @entangle('form.fecha_fin').live,
        modalidad: @entangle('form.modalidad').live,
        estado: @entangle('form.estado').live,
        descripcion: @entangle('form.descripcion').live,
        copiarEstructura: @entangle('form.copiar_estructura').live,
        crearPeriodos: @entangle('form.crear_periodos').live,

        get anioValido() {
            const value = parseInt(this.anio);
            return !isNaN(value) && value >= 2020 && value <= 2100;
        },

        get nombreValido() {
            return (this.nombre || '').trim().length >= 5;
        },

        get fechaInicioValida() {
            return Boolean(this.fechaInicio);
        },

        get fechaFinValida() {
            if (!this.fechaInicio || !this.fechaFin) return false;
            return new Date(this.fechaFin) >= new Date(this.fechaInicio);
        },

        get modalidadValida() {
            return (this.modalidad || '').trim().length >= 3;
        },

        get estadoValido() {
            return ['ACTIVO', 'PLANIFICADO', 'CERRADO', 'ARCHIVADO', 'INACTIVO'].includes(this.estado);
        },

        get descripcionValida() {
            return (this.descripcion || '').length <= 500;
        },

        get puedeGuardarGestion() {
            return this.anioValido
                && this.nombreValido
                && this.fechaInicioValida
                && this.fechaFinValida
                && this.modalidadValida
                && this.estadoValido
                && this.descripcionValida;
        }
    }"
    x-on:keydown.escape.window="
        if (modalNueva) modalNueva = false;
        if (drawerDetalle) drawerDetalle = false;
        if (modalCierre) modalCierre = false;
    "
    class="space-y-6">

    @once
        <script>
            window.addEventListener('gestion-academica-alerta', event => {
                const data = event.detail || {};

                if (window.Swal) {
                    Swal.fire({
                        icon: data.icon || 'info',
                        title: data.title || 'Información',
                        text: data.text || '',
                        confirmButtonText: 'Entendido',
                        confirmButtonColor: '#059669'
                    });
                }
            });
        </script>
    @endonce

    {{-- ============================================================
        AVISO SI NO EXISTE TABLA
    ============================================================ --}}
    @unless ($tablaDisponible)
        <section
            class="rounded-[2rem] border border-amber-300 bg-amber-50 p-5 text-amber-950 shadow-sm dark:border-amber-500/40 dark:bg-amber-950 dark:text-amber-100">
            <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
                <div>
                    <p class="text-sm font-black uppercase tracking-[0.16em]">
                        Configuración pendiente
                    </p>

                    <h3 class="mt-2 text-xl font-black">
                        Gestión académica aún no disponible
                    </h3>

                    <p class="mt-2 max-w-4xl text-sm leading-7">
                        El módulo no mostrará datos estimados. Cuando la estructura esté disponible, se visualizarán
                        únicamente registros reales del sistema.
                    </p>
                </div>

                <div
                    class="rounded-2xl border border-amber-300 bg-amber-100 px-4 py-3 text-sm font-black text-amber-950 dark:border-amber-500/40 dark:bg-amber-900 dark:text-amber-100">
                    Sin registros disponibles
                </div>
            </div>
        </section>
    @endunless

    {{-- ============================================================
        HEADER
    ============================================================ --}}
    <section class="overflow-hidden rounded-[2rem] border border-[var(--ui-border)] bg-[var(--ui-card)] shadow-sm">
        <div class="border-b border-[var(--ui-border)] bg-[var(--ui-soft)] px-5 py-4 sm:px-6">
            <div class="flex flex-wrap items-center gap-2 text-sm font-semibold">
                <a href="{{ route('dashboard') }}"
                    class="text-[var(--ui-muted)] transition hover:text-[var(--ui-primary)]">
                    Inicio
                </a>

                <span class="text-[var(--ui-muted)]">/</span>

                <span class="text-[var(--ui-primary)]">
                    Gestión Académica
                </span>
            </div>
        </div>

        <div class="p-5 sm:p-6">
            <div class="flex flex-col gap-6 xl:flex-row xl:items-end xl:justify-between">
                <div class="max-w-5xl">
                    <div class="flex flex-wrap items-center gap-2">
                        <span
                            class="inline-flex items-center gap-2 rounded-full border border-sky-300 bg-sky-50 px-3 py-1 text-xs font-black uppercase tracking-[0.14em] text-sky-800 dark:border-sky-500/30 dark:bg-sky-950 dark:text-sky-300">
                            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"
                                    d="M12 6v6l4 2m5-2a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                            </svg>
                            Centro anual académico
                        </span>

                        <span
                            class="inline-flex rounded-full border border-[var(--ui-border)] bg-[var(--ui-card)] px-3 py-1 text-xs font-bold text-[var(--ui-muted)]">
                            Técnico Humanístico
                        </span>
                    </div>

                    <h1 class="mt-4 text-3xl font-black tracking-tight text-[var(--ui-text)] sm:text-4xl">
                        Gestión Académica
                    </h1>

                    <p class="mt-3 max-w-4xl text-sm leading-7 text-[var(--ui-muted)]">
                        Centro de control para administrar ciclos académicos, estructura anual, inscripciones,
                        planificación, especialidades técnicas, historial institucional y cierre académico.
                    </p>
                </div>

                <div class="flex flex-wrap gap-3">
                    <button type="button"
                        wire:click="abrirNuevaGestion"
                        class="inline-flex items-center justify-center gap-2 rounded-2xl bg-gradient-to-r from-emerald-600 to-sky-600 px-5 py-3 text-sm font-black text-white shadow-lg shadow-emerald-500/20 transition hover:-translate-y-0.5 hover:shadow-xl">
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"
                                d="M12 4.5v15m7.5-7.5h-15" />
                        </svg>
                        Nueva gestión académica
                    </button>

                    <button type="button"
                        wire:click="exportarGestion('{{ $gestionActiva['id'] ?? '' }}')"
                        class="inline-flex items-center justify-center gap-2 rounded-2xl border border-[var(--ui-border)] bg-[var(--ui-card)] px-5 py-3 text-sm font-bold text-[var(--ui-text)] shadow-sm transition hover:bg-[var(--ui-soft)]">
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"
                                d="M12 3v12m0 0 4-4m-4 4-4-4M4.5 21h15" />
                        </svg>
                        Exportar historial
                    </button>
                </div>
            </div>
        </div>
    </section>

    {{-- ============================================================
        GESTIÓN ACTIVA
    ============================================================ --}}
    @if ($gestionActiva)
        <section
            class="overflow-hidden rounded-[2rem] border border-emerald-300 bg-[var(--ui-card)] shadow-sm dark:border-emerald-500/30">
            <div class="bg-gradient-to-r from-emerald-700 to-sky-700 px-6 py-6 text-white sm:px-7">
                <div class="flex flex-col gap-6 xl:flex-row xl:items-start xl:justify-between">
                    <div class="max-w-4xl">
                        <div class="flex flex-wrap items-center gap-2">
                            <span
                                class="inline-flex rounded-full bg-white px-3 py-1 text-xs font-black uppercase tracking-[0.16em] text-emerald-800">
                                Gestión activa
                            </span>

                            <span
                                class="inline-flex rounded-full bg-emerald-100 px-3 py-1 text-xs font-black text-emerald-800">
                                {{ $gestionActiva['estado'] }}
                            </span>

                            <span
                                class="inline-flex rounded-full bg-sky-100 px-3 py-1 text-xs font-black text-sky-800">
                                Ciclo {{ $gestionActiva['anio'] }}
                            </span>
                        </div>

                        <h2 class="mt-4 text-3xl font-black tracking-tight">
                            {{ $gestionActiva['nombre'] }}
                        </h2>

                        <p class="mt-3 max-w-3xl text-sm leading-7 text-emerald-50">
                            Gestión académica habilitada para inscripciones, planificación de asignaturas,
                            especialidades técnicas y seguimiento institucional del ciclo vigente.
                        </p>
                    </div>

                    <div class="grid gap-3 sm:grid-cols-2 xl:min-w-[420px]">
                        <div class="rounded-2xl border border-white bg-white p-4 text-slate-900">
                            <p class="text-xs font-bold uppercase tracking-[0.14em] text-slate-500">
                                Inicio oficial
                            </p>

                            <p class="mt-1 text-lg font-black">
                                {{ $gestionActiva['fecha_inicio'] ? \Carbon\Carbon::parse($gestionActiva['fecha_inicio'])->format('d/m/Y') : 'Sin registro' }}
                            </p>
                        </div>

                        <div class="rounded-2xl border border-white bg-white p-4 text-slate-900">
                            <p class="text-xs font-bold uppercase tracking-[0.14em] text-slate-500">
                                Finalización
                            </p>

                            <p class="mt-1 text-lg font-black">
                                {{ $gestionActiva['fecha_fin'] ? \Carbon\Carbon::parse($gestionActiva['fecha_fin'])->format('d/m/Y') : 'Sin registro' }}
                            </p>
                        </div>
                    </div>
                </div>
            </div>

            <div class="bg-[var(--ui-card)] p-6 sm:p-7">
                <div class="grid gap-5 lg:grid-cols-4">
                    <div class="rounded-2xl border border-[var(--ui-border)] bg-[var(--ui-soft)] p-4">
                        <p class="text-xs font-bold uppercase tracking-[0.14em] text-[var(--ui-muted)]">
                            Días transcurridos
                        </p>

                        <p class="mt-2 text-2xl font-black text-[var(--ui-text)]">
                            {{ $gestionActiva['dias_transcurridos'] }} días
                        </p>
                    </div>

                    <div class="rounded-2xl border border-[var(--ui-border)] bg-[var(--ui-soft)] p-4">
                        <p class="text-xs font-bold uppercase tracking-[0.14em] text-[var(--ui-muted)]">
                            Progreso temporal
                        </p>

                        <div class="mt-3 h-3 overflow-hidden rounded-full bg-[var(--ui-border)]">
                            <div class="h-full rounded-full bg-gradient-to-r from-emerald-500 to-sky-500"
                                style="width: {{ $gestionActiva['progreso'] }}%">
                            </div>
                        </div>

                        <p class="mt-2 text-sm font-black text-emerald-600 dark:text-emerald-300">
                            {{ $gestionActiva['progreso'] }}%
                        </p>
                    </div>

                    <div class="rounded-2xl border border-[var(--ui-border)] bg-[var(--ui-soft)] p-4">
                        <p class="text-xs font-bold uppercase tracking-[0.14em] text-[var(--ui-muted)]">
                            Días restantes
                        </p>

                        <p class="mt-2 text-2xl font-black text-[var(--ui-text)]">
                            {{ $gestionActiva['dias_restantes'] }} días
                        </p>
                    </div>

                    <div class="rounded-2xl border border-amber-300 bg-amber-50 p-4 dark:border-amber-500/30 dark:bg-amber-950">
                        <p class="text-xs font-bold uppercase tracking-[0.14em] text-amber-700 dark:text-amber-300">
                            Cierre institucional
                        </p>

                        <p class="mt-2 text-2xl font-black text-amber-700 dark:text-amber-300">
                            ABIERTO
                        </p>
                    </div>
                </div>

                <div class="mt-5 grid gap-3 border-t border-[var(--ui-border)] pt-5 sm:grid-cols-2 xl:grid-cols-4">
                    <button type="button"
                        wire:click="abrirDetalle('{{ $gestionActiva['id'] }}')"
                        class="inline-flex items-center justify-center rounded-2xl border border-[var(--ui-border)] bg-[var(--ui-card)] px-4 py-3 text-sm font-bold text-[var(--ui-text)] transition hover:bg-[var(--ui-soft)]">
                        Ver expediente
                    </button>

                    <button type="button"
                        wire:click="cambiarVista('periodos')"
                        class="inline-flex items-center justify-center rounded-2xl border border-violet-300 bg-violet-50 px-4 py-3 text-sm font-black text-violet-800 transition hover:bg-violet-100 dark:border-violet-500/30 dark:bg-violet-950 dark:text-violet-300">
                        Ver periodos
                    </button>

                    <button type="button"
                        wire:click="exportarGestion('{{ $gestionActiva['id'] }}')"
                        class="inline-flex items-center justify-center rounded-2xl border border-sky-300 bg-sky-50 px-4 py-3 text-sm font-black text-sky-800 transition hover:bg-sky-100 dark:border-sky-500/30 dark:bg-sky-950 dark:text-sky-300">
                        Exportar gestión
                    </button>

                    <button type="button"
                        wire:click="prepararCierre('{{ $gestionActiva['id'] }}')"
                        class="inline-flex items-center justify-center rounded-2xl border border-amber-300 bg-amber-50 px-4 py-3 text-sm font-black text-amber-800 transition hover:bg-amber-100 dark:border-amber-500/30 dark:bg-amber-950 dark:text-amber-300">
                        Revisar cierre
                    </button>
                </div>
            </div>
        </section>
    @else
        <section
            class="rounded-[2rem] border border-sky-300 bg-sky-50 p-6 shadow-sm dark:border-sky-500/30 dark:bg-sky-950 sm:p-7">
            <div class="flex flex-col gap-5 lg:flex-row lg:items-center lg:justify-between">
                <div>
                    <p class="text-sm font-black uppercase tracking-[0.18em] text-sky-800 dark:text-sky-300">
                        Sin gestión activa
                    </p>

                    <h2 class="mt-2 text-2xl font-black text-slate-950 dark:text-white">
                        No existe una gestión académica activa
                    </h2>

                    <p class="mt-2 max-w-4xl text-sm leading-7 text-slate-700 dark:text-slate-300">
                        Registra una nueva gestión académica o activa una gestión planificada para iniciar el control
                        anual de inscripciones, planificación y cierre institucional.
                    </p>
                </div>

                <button type="button"
                    wire:click="abrirNuevaGestion"
                    class="inline-flex items-center justify-center gap-2 rounded-2xl bg-gradient-to-r from-emerald-600 to-sky-600 px-5 py-3 text-sm font-black text-white shadow-lg shadow-emerald-500/20 transition hover:-translate-y-0.5 hover:shadow-xl">
                    Crear gestión académica
                </button>
            </div>
        </section>
    @endif

    {{-- ============================================================
        RESUMEN
    ============================================================ --}}
    <section class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
        @foreach ($resumen as $card)
            <article
                class="rounded-[1.5rem] border border-[var(--ui-border)] bg-[var(--ui-card)] p-5 shadow-sm transition hover:-translate-y-0.5 hover:shadow-lg">
                <div class="flex items-start justify-between gap-4">
                    <div>
                        <p class="text-sm font-bold text-[var(--ui-muted)]">
                            {{ $card['titulo'] }}
                        </p>

                        <p class="mt-3 text-3xl font-black text-[var(--ui-text)]">
                            {{ $card['valor'] }}
                        </p>

                        <p class="mt-2 text-xs font-semibold text-[var(--ui-muted)]">
                            {{ $card['descripcion'] }}
                        </p>
                    </div>

                    <div
                        class="flex h-11 w-11 items-center justify-center rounded-2xl border {{ $this->colorClass($card['color']) }}">
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"
                                d="M4.5 6.75A2.25 2.25 0 0 1 6.75 4.5h10.5a2.25 2.25 0 0 1 2.25 2.25v10.5a2.25 2.25 0 0 1-2.25 2.25H6.75a2.25 2.25 0 0 1-2.25-2.25V6.75Z" />
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"
                                d="M8.25 9.75h7.5M8.25 14.25h4.5" />
                        </svg>
                    </div>
                </div>
            </article>
        @endforeach
    </section>

    {{-- ============================================================
        NAVEGACIÓN
    ============================================================ --}}
    <section class="rounded-[1.5rem] border border-[var(--ui-border)] bg-[var(--ui-card)] p-2 shadow-sm">
        <div class="flex gap-2 overflow-x-auto">
            @foreach ([
                'general' => 'Vista general',
                'anios' => 'Por años',
                'periodos' => 'Periodos',
                'estructura' => 'Estructura anual',
                'inscripciones' => 'Inscripciones',
                'reportes' => 'Reportes',
                'cierre' => 'Cierre',
            ] as $key => $label)
                <button type="button"
                    wire:click="cambiarVista('{{ $key }}')"
                    class="inline-flex shrink-0 items-center justify-center rounded-2xl px-4 py-2.5 text-sm font-black transition
                    {{ $vista === $key
                        ? 'bg-[var(--ui-primary-soft)] text-[var(--ui-primary)]'
                        : 'text-[var(--ui-muted)] hover:bg-[var(--ui-soft)] hover:text-[var(--ui-text)]' }}">
                    {{ $label }}
                </button>
            @endforeach
        </div>
    </section>

    {{-- ============================================================
        FILTROS
    ============================================================ --}}
    <section class="rounded-[2rem] border border-[var(--ui-border)] bg-[var(--ui-card)] p-5 shadow-sm">
        <div class="grid gap-4 lg:grid-cols-[1fr,180px,190px,auto]">
            <div>
                <label class="mb-2 block text-sm font-bold text-[var(--ui-text)]">
                    Buscar gestión
                </label>

                <input type="text"
                    wire:model.live.debounce.400ms="busqueda"
                    class="ui-input block w-full"
                    placeholder="Buscar por año o estado de gestión..." />
            </div>

            <div>
                <label class="mb-2 block text-sm font-bold text-[var(--ui-text)]">
                    Año
                </label>

                <select wire:model.live="filtroAnio" class="ui-input block w-full">
                    <option value="">Todos</option>
                    @foreach ($aniosDisponibles as $anio)
                        <option value="{{ $anio }}">{{ $anio }}</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="mb-2 block text-sm font-bold text-[var(--ui-text)]">
                    Estado
                </label>

                <select wire:model.live="filtroEstado" class="ui-input block w-full">
                    <option value="">Todos</option>
                    <option value="ACTIVO">Activo</option>
                    <option value="PLANIFICADO">Planificado</option>
                    <option value="CERRADO">Cerrado</option>
                    <option value="ARCHIVADO">Archivado</option>
                    <option value="INACTIVO">Inactivo</option>
                </select>
            </div>

            <div class="flex items-end">
                <button type="button"
                    wire:click="limpiarFiltros"
                    class="inline-flex w-full items-center justify-center rounded-2xl border border-[var(--ui-border)] bg-[var(--ui-card)] px-5 py-3 text-sm font-bold text-[var(--ui-text)] transition hover:bg-[var(--ui-soft)]">
                    Limpiar filtros
                </button>
            </div>
        </div>
    </section>

    {{-- ============================================================
        CONTENIDO PRINCIPAL
    ============================================================ --}}
    <section class="grid gap-6 xl:grid-cols-[1.15fr,0.85fr]">

        <div class="space-y-6">

            {{-- HISTORIAL --}}
            <section class="rounded-[2rem] border border-[var(--ui-border)] bg-[var(--ui-card)] p-5 shadow-sm sm:p-6">
                <div class="mb-5">
                    <p class="text-sm font-black uppercase tracking-[0.18em] text-[var(--ui-primary)]">
                        Historial académico por gestión
                    </p>

                    <h2 class="mt-2 text-2xl font-black text-[var(--ui-text)]">
                        Expedientes anuales
                    </h2>

                    <p class="mt-2 text-sm leading-6 text-[var(--ui-muted)]">
                        Cada gestión representa un ciclo anual real registrado en el sistema.
                    </p>
                </div>

                <div class="grid gap-4">
                    @forelse ($gestiones as $gestion)
                        <article
                            class="rounded-[1.5rem] border border-[var(--ui-border)] bg-[var(--ui-soft)] p-5 transition hover:border-[var(--ui-primary)]">
                            <div class="flex flex-col gap-5 lg:flex-row lg:items-center lg:justify-between">
                                <div class="flex items-start gap-4">
                                    <div
                                        class="flex h-14 w-14 shrink-0 items-center justify-center rounded-2xl border border-[var(--ui-border)] bg-[var(--ui-card)] text-[var(--ui-primary)]">
                                        <svg class="h-7 w-7" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"
                                                d="M3.75 6.75A2.25 2.25 0 0 1 6 4.5h4.19c.597 0 1.17.237 1.591.659l1.06 1.06c.422.421.995.659 1.591.659H18A2.25 2.25 0 0 1 20.25 9.128V17.25A2.25 2.25 0 0 1 18 19.5H6a2.25 2.25 0 0 1-2.25-2.25V6.75Z" />
                                        </svg>
                                    </div>

                                    <div>
                                        <div class="flex flex-wrap items-center gap-2">
                                            <h3 class="text-lg font-black text-[var(--ui-text)]">
                                                {{ $gestion['nombre'] }}
                                            </h3>

                                            <span
                                                class="rounded-full border px-3 py-1 text-xs font-black {{ $this->badgeEstadoClass($gestion['estado']) }}">
                                                {{ $gestion['estado'] }}
                                            </span>

                                            <span
                                                class="rounded-full border border-[var(--ui-border)] bg-[var(--ui-card)] px-3 py-1 text-xs font-bold text-[var(--ui-muted)]">
                                                Ciclo {{ $gestion['anio'] }}
                                            </span>
                                        </div>

                                        <p class="mt-2 text-sm leading-6 text-[var(--ui-muted)]">
                                            Expediente académico anual para inscripciones, planes académicos, especialidades
                                            técnicas y cierre institucional.
                                        </p>

                                        <div class="mt-3 flex flex-wrap gap-2 text-xs font-bold text-[var(--ui-muted)]">
                                            <span class="rounded-full border border-[var(--ui-border)] bg-[var(--ui-card)] px-3 py-1">
                                                Inscripciones: {{ $gestion['estudiantes'] }}
                                            </span>

                                            <span class="rounded-full border border-[var(--ui-border)] bg-[var(--ui-card)] px-3 py-1">
                                                Plan asignatura: {{ $gestion['planes_asignatura'] ?? 0 }}
                                            </span>

                                            <span class="rounded-full border border-[var(--ui-border)] bg-[var(--ui-card)] px-3 py-1">
                                                Plan especialidad: {{ $gestion['planes_especialidad'] ?? 0 }}
                                            </span>

                                            <span class="rounded-full border border-[var(--ui-border)] bg-[var(--ui-card)] px-3 py-1">
                                                Actualización: {{ $gestion['ultima_actualizacion'] }}
                                            </span>
                                        </div>
                                    </div>
                                </div>

                                <div class="flex shrink-0 flex-wrap gap-2 lg:justify-end">
                                    <button type="button"
                                        wire:click="abrirDetalle('{{ $gestion['id'] }}')"
                                        class="rounded-2xl border border-[var(--ui-border)] bg-[var(--ui-card)] px-4 py-2 text-sm font-bold text-[var(--ui-text)] transition hover:bg-[var(--ui-soft)]">
                                        Abrir expediente
                                    </button>

                                    <button type="button"
                                        wire:click="exportarGestion('{{ $gestion['id'] }}')"
                                        class="rounded-2xl border border-[var(--ui-border)] bg-[var(--ui-card)] px-4 py-2 text-sm font-bold text-[var(--ui-text)] transition hover:bg-[var(--ui-soft)]">
                                        Exportar
                                    </button>

                                    @if ($gestion['estado'] === 'PLANIFICADO')
                                        <button type="button"
                                            wire:click="activarGestion('{{ $gestion['id'] }}')"
                                            class="rounded-2xl border border-emerald-300 bg-emerald-50 px-4 py-2 text-sm font-black text-emerald-800 transition hover:bg-emerald-100 dark:border-emerald-500/30 dark:bg-emerald-950 dark:text-emerald-300">
                                            Activar
                                        </button>
                                    @endif

                                    @if ($gestion['estado'] === 'ACTIVO')
                                        <button type="button"
                                            wire:click="prepararCierre('{{ $gestion['id'] }}')"
                                            class="rounded-2xl border border-amber-300 bg-amber-50 px-4 py-2 text-sm font-black text-amber-800 transition hover:bg-amber-100 dark:border-amber-500/30 dark:bg-amber-950 dark:text-amber-300">
                                            Cierre
                                        </button>
                                    @endif
                                </div>
                            </div>
                        </article>
                    @empty
                        <div class="rounded-2xl border border-[var(--ui-border)] bg-[var(--ui-soft)] p-8 text-center">
                            <p class="text-sm font-black text-[var(--ui-text)]">
                                No existen gestiones académicas registradas.
                            </p>

                            <p class="mt-2 text-sm leading-6 text-[var(--ui-muted)]">
                                Crea una nueva gestión académica para iniciar el control anual de inscripciones,
                                planificación y cierre institucional.
                            </p>

                            <button type="button"
                                wire:click="abrirNuevaGestion"
                                class="mt-5 inline-flex items-center justify-center rounded-2xl bg-gradient-to-r from-emerald-600 to-sky-600 px-5 py-3 text-sm font-black text-white shadow-lg shadow-emerald-500/20 transition hover:-translate-y-0.5 hover:shadow-xl">
                                Crear primera gestión académica
                            </button>
                        </div>
                    @endforelse
                </div>

                <div class="mt-5">
                    {{ $gestiones->links() }}
                </div>
            </section>

            {{-- PERIODOS --}}
            <section class="rounded-[2rem] border border-[var(--ui-border)] bg-[var(--ui-card)] p-5 shadow-sm sm:p-6">
                <div class="mb-5">
                    <p class="text-sm font-black uppercase tracking-[0.18em] text-violet-600 dark:text-violet-300">
                        Periodos académicos
                    </p>

                    <h2 class="mt-2 text-2xl font-black text-[var(--ui-text)]">
                        Catálogo institucional de periodos
                    </h2>

                    <p class="mt-2 text-sm leading-6 text-[var(--ui-muted)]">
                        Los periodos se muestran como configuración general de evaluación académica.
                    </p>
                </div>

                @if (count($periodos) > 0)
                    <div class="grid gap-4 lg:grid-cols-3">
                        @foreach ($periodos as $periodo)
                            <article class="rounded-[1.5rem] border border-[var(--ui-border)] bg-[var(--ui-soft)] p-4">
                                <div class="flex items-center justify-between gap-3">
                                    <h3 class="text-sm font-black text-[var(--ui-text)]">
                                        {{ $periodo['nombre'] }}
                                    </h3>

                                    <span
                                        class="rounded-full border px-3 py-1 text-[10px] font-black {{ $this->badgeEstadoClass($periodo['estado']) }}">
                                        {{ $periodo['estado'] }}
                                    </span>
                                </div>

                                <p class="mt-3 text-xs font-semibold text-[var(--ui-muted)]">
                                    Orden institucional:
                                    <span class="font-black text-[var(--ui-text)]">
                                        {{ $periodo['orden'] ?? 'Sin orden' }}
                                    </span>
                                </p>
                            </article>
                        @endforeach
                    </div>
                @else
                    <div class="rounded-2xl border border-[var(--ui-border)] bg-[var(--ui-soft)] p-6 text-center">
                        <p class="text-sm font-black text-[var(--ui-text)]">
                            No hay periodos académicos registrados.
                        </p>

                        <p class="mt-2 text-sm leading-6 text-[var(--ui-muted)]">
                            Puedes crear periodos base al registrar una gestión, siempre que aún no existan periodos.
                        </p>
                    </div>
                @endif
            </section>
        </div>

        <div class="space-y-6">

            {{-- CIERRE --}}
            <section class="rounded-[2rem] border border-[var(--ui-border)] bg-[var(--ui-card)] p-5 shadow-sm sm:p-6">
                <div class="mb-5 flex items-start justify-between gap-4">
                    <div>
                        <p class="text-sm font-black uppercase tracking-[0.18em] text-amber-600 dark:text-amber-300">
                            Control institucional
                        </p>

                        <h2 class="mt-2 text-2xl font-black text-[var(--ui-text)]">
                            Estado de cierre
                        </h2>

                        <p class="mt-2 text-sm leading-6 text-[var(--ui-muted)]">
                            El cierre revisa inscripciones y planes académicos vinculados a la gestión activa.
                        </p>
                    </div>

                    <span
                        class="rounded-full border border-amber-300 bg-amber-50 px-3 py-1 text-xs font-black text-amber-800 dark:border-amber-500/30 dark:bg-amber-950 dark:text-amber-300">
                        Revisión
                    </span>
                </div>

                <div class="space-y-3">
                    @foreach ($pendientesCierre as $pendiente)
                        <div
                            class="flex items-center justify-between rounded-2xl border border-[var(--ui-border)] bg-[var(--ui-soft)] px-4 py-3">
                            <p class="text-sm font-bold text-[var(--ui-text)]">
                                {{ $pendiente['titulo'] }}
                            </p>

                            <span
                                class="rounded-full border px-3 py-1 text-xs font-black {{ $this->colorClass($pendiente['color']) }}">
                                {{ $pendiente['valor'] }}
                            </span>
                        </div>
                    @endforeach
                </div>

                <div
                    class="mt-5 rounded-2xl border border-amber-300 bg-amber-50 px-4 py-3 text-sm leading-6 text-amber-900 dark:border-amber-500/30 dark:bg-amber-950 dark:text-amber-200">
                    No se cerrará la gestión si existen inscripciones pendientes, planes de asignatura incompletos
                    o planes de especialidad incompletos.
                </div>

                <button type="button"
                    wire:click="prepararCierre('{{ $gestionActiva['id'] ?? '' }}')"
                    class="mt-4 inline-flex w-full items-center justify-center rounded-2xl bg-gradient-to-r from-amber-500 to-orange-500 px-5 py-3 text-sm font-black text-white shadow-lg shadow-amber-500/20 transition hover:-translate-y-0.5 hover:shadow-xl">
                    Revisar cierre académico
                </button>
            </section>

            {{-- ESTRUCTURA --}}
            <section class="rounded-[2rem] border border-[var(--ui-border)] bg-[var(--ui-card)] p-5 shadow-sm sm:p-6">
                <div class="mb-5">
                    <p class="text-sm font-black uppercase tracking-[0.18em] text-sky-600 dark:text-sky-300">
                        Estructura académica anual
                    </p>

                    <h2 class="mt-2 text-2xl font-black text-[var(--ui-text)]">
                        Organización institucional
                    </h2>

                    <p class="mt-2 text-sm leading-6 text-[var(--ui-muted)]">
                        Vista basada únicamente en registros reales del sistema.
                    </p>
                </div>

                <div class="grid gap-3 sm:grid-cols-2">
                    @foreach ($estructura as $item)
                        <article class="rounded-2xl border border-[var(--ui-border)] bg-[var(--ui-soft)] p-4">
                            <p class="text-xs font-bold uppercase tracking-[0.12em] text-[var(--ui-muted)]">
                                {{ $item['titulo'] }}
                            </p>

                            <p class="mt-2 text-2xl font-black text-[var(--ui-text)]">
                                {{ $item['valor'] }}
                            </p>

                            <p class="mt-1 text-xs font-semibold text-[var(--ui-muted)]">
                                {{ $item['detalle'] }}
                            </p>
                        </article>
                    @endforeach
                </div>
            </section>

            {{-- ACTIVIDAD --}}
            <section class="rounded-[2rem] border border-[var(--ui-border)] bg-[var(--ui-card)] p-5 shadow-sm sm:p-6">
                <div class="mb-5">
                    <p class="text-sm font-black uppercase tracking-[0.18em] text-[var(--ui-primary)]">
                        Actividad reciente
                    </p>

                    <h2 class="mt-2 text-2xl font-black text-[var(--ui-text)]">
                        Movimiento institucional
                    </h2>

                    <p class="mt-2 text-sm leading-6 text-[var(--ui-muted)]">
                        Actividad leída desde bitácora institucional cuando exista registro.
                    </p>
                </div>

                @if (count($actividadReciente) > 0)
                    <div class="space-y-3">
                        @foreach ($actividadReciente as $actividad)
                            <article class="rounded-2xl border border-[var(--ui-border)] bg-[var(--ui-soft)] p-4">
                                <div class="flex items-start justify-between gap-3">
                                    <div>
                                        <p class="text-sm font-black text-[var(--ui-text)]">
                                            {{ $actividad['evento'] }}
                                        </p>

                                        <p class="mt-1 text-xs text-[var(--ui-muted)]">
                                            {{ $actividad['fecha'] }} · {{ $actividad['responsable'] }} · {{ $actividad['modulo'] }}
                                        </p>
                                    </div>

                                    <span
                                        class="rounded-full border border-emerald-300 bg-emerald-50 px-3 py-1 text-[10px] font-black text-emerald-800 dark:border-emerald-500/30 dark:bg-emerald-950 dark:text-emerald-300">
                                        {{ $actividad['resultado'] }}
                                    </span>
                                </div>
                            </article>
                        @endforeach
                    </div>
                @else
                    <div class="rounded-2xl border border-[var(--ui-border)] bg-[var(--ui-soft)] p-6 text-center">
                        <p class="text-sm font-black text-[var(--ui-text)]">
                            No existe actividad reciente registrada.
                        </p>

                        <p class="mt-2 text-sm leading-6 text-[var(--ui-muted)]">
                            Cuando se registren acciones en bitácora, aparecerán en esta sección.
                        </p>
                    </div>
                @endif
            </section>
        </div>
    </section>

    {{-- ============================================================
        MODAL NUEVA GESTIÓN - SIN TRANSPARENCIA
    ============================================================ --}}
    <div x-show="modalNueva"
        x-cloak
        class="fixed inset-0 z-50 flex items-center justify-center bg-slate-950 p-4">

        <section
            x-show="modalNueva"
            x-transition
            class="max-h-[92vh] w-full max-w-5xl overflow-y-auto rounded-[2rem] border border-slate-700 bg-white p-6 shadow-2xl dark:bg-slate-950">

            <div class="flex items-start justify-between gap-4 border-b border-[var(--ui-border)] pb-5">
                <div>
                    <p class="text-sm font-black uppercase tracking-[0.18em] text-[var(--ui-primary)]">
                        Nueva gestión académica
                    </p>

                    <h2 class="mt-2 text-2xl font-black text-[var(--ui-text)]">
                        Registrar nuevo ciclo institucional
                    </h2>

                    <p class="mt-2 max-w-3xl text-sm leading-6 text-[var(--ui-muted)]">
                        Registra el ciclo académico anual, definiendo sus fechas oficiales y el estado inicial para
                        organizar inscripciones, planificación académica y cierre institucional.
                    </p>
                </div>

                <button type="button"
                    wire:click="cerrarModal"
                    class="rounded-2xl border border-[var(--ui-border)] bg-[var(--ui-card)] px-4 py-2 text-sm font-bold text-[var(--ui-text)] transition hover:bg-[var(--ui-soft)]">
                    Cerrar
                </button>
            </div>

            <form wire:submit.prevent="crearGestionAcademica" class="mt-6 space-y-5">
                <div class="grid gap-5 md:grid-cols-2">
                    <div>
                        <label class="mb-2 block text-sm font-bold text-[var(--ui-text)]">
                            Año de gestión
                        </label>

                        <input type="number"
                            x-model="anio"
                            class="ui-input block w-full"
                            placeholder="2027"
                            min="2020"
                            max="2100">

                        <p x-show="!anioValido" x-cloak class="mt-2 text-xs font-bold text-rose-500">
                            El año debe estar entre 2020 y 2100.
                        </p>

                        @error('form.anio')
                            <p class="mt-2 text-xs font-bold text-rose-500">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label class="mb-2 block text-sm font-bold text-[var(--ui-text)]">
                            Nombre de gestión
                        </label>

                        <input type="text"
                            x-model="nombre"
                            class="ui-input block w-full"
                            placeholder="Gestión Académica 2027">

                        <p class="mt-2 text-xs font-semibold text-[var(--ui-muted)]">
                            Este nombre identifica la gestión dentro del módulo académico.
                        </p>
                    </div>

                    <div>
                        <label class="mb-2 block text-sm font-bold text-[var(--ui-text)]">
                            Fecha inicio
                        </label>

                        <input type="date"
                            x-model="fechaInicio"
                            class="ui-input block w-full">

                        <p x-show="!fechaInicioValida" x-cloak class="mt-2 text-xs font-bold text-rose-500">
                            La fecha de inicio es obligatoria para control temporal.
                        </p>

                        @error('form.fecha_inicio')
                            <p class="mt-2 text-xs font-bold text-rose-500">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label class="mb-2 block text-sm font-bold text-[var(--ui-text)]">
                            Fecha fin
                        </label>

                        <input type="date"
                            x-model="fechaFin"
                            class="ui-input block w-full">

                        <p x-show="!fechaFinValida" x-cloak class="mt-2 text-xs font-bold text-rose-500">
                            La fecha final debe ser igual o posterior a la fecha inicial.
                        </p>

                        @error('form.fecha_fin')
                            <p class="mt-2 text-xs font-bold text-rose-500">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label class="mb-2 block text-sm font-bold text-[var(--ui-text)]">
                            Modalidad
                        </label>

                        <input type="text"
                            x-model="modalidad"
                            class="ui-input block w-full"
                            placeholder="Técnico Humanístico">

                        <p class="mt-2 text-xs font-semibold text-[var(--ui-muted)]">
                            Referencia institucional para distinguir el enfoque académico de la gestión.
                        </p>
                    </div>

                    <div>
                        <label class="mb-2 block text-sm font-bold text-[var(--ui-text)]">
                            Estado inicial
                        </label>

                        <select x-model="estado" class="ui-input block w-full">
                            <option value="ACTIVO">ACTIVO</option>
                            <option value="PLANIFICADO">PLANIFICADO</option>
                            <option value="CERRADO">CERRADO</option>
                            <option value="ARCHIVADO">ARCHIVADO</option>
                            <option value="INACTIVO">INACTIVO</option>
                        </select>

                        <p x-show="!estadoValido" x-cloak class="mt-2 text-xs font-bold text-rose-500">
                            Selecciona un estado válido.
                        </p>

                        @error('form.estado')
                            <p class="mt-2 text-xs font-bold text-rose-500">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="md:col-span-2">
                        <label class="mb-2 block text-sm font-bold text-[var(--ui-text)]">
                            Descripción institucional
                        </label>

                        <textarea x-model="descripcion"
                            rows="4"
                            maxlength="500"
                            class="ui-input block w-full resize-none"
                            placeholder="Ejemplo: ciclo académico orientado a la planificación, inscripción, seguimiento y cierre institucional."></textarea>

                        <div class="mt-2 flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
                            <p x-show="!descripcionValida" x-cloak class="text-xs font-bold text-rose-500">
                                La descripción no debe superar los 500 caracteres.
                            </p>

                            <p class="text-xs font-semibold text-[var(--ui-muted)] sm:ml-auto">
                                <span x-text="(descripcion || '').length"></span>/500 caracteres
                            </p>
                        </div>
                    </div>
                </div>

                <div class="grid gap-3 md:grid-cols-2">
                    <label
                        class="flex cursor-pointer items-start gap-3 rounded-2xl border border-[var(--ui-border)] bg-[var(--ui-soft)] p-4">
                        <input type="checkbox"
                            x-model="crearPeriodos"
                            class="mt-1 rounded border-[var(--ui-border)] text-emerald-600 focus:ring-emerald-500">

                        <span>
                            <span class="block text-sm font-black text-[var(--ui-text)]">
                                Crear periodos base si no existen
                            </span>

                            <span class="mt-1 block text-xs leading-5 text-[var(--ui-muted)]">
                                Se crearán periodos base únicamente si todavía no existen periodos registrados.
                            </span>
                        </span>
                    </label>

                    <label
                        class="flex cursor-not-allowed items-start gap-3 rounded-2xl border border-[var(--ui-border)] bg-[var(--ui-soft)] p-4">
                        <input type="checkbox"
                            x-model="copiarEstructura"
                            disabled
                            class="mt-1 rounded border-[var(--ui-border)] text-emerald-600 focus:ring-emerald-500">

                        <span>
                            <span class="block text-sm font-black text-[var(--ui-text)]">
                                Copiar estructura anterior
                            </span>

                            <span class="mt-1 block text-xs leading-5 text-[var(--ui-muted)]">
                                Opción reservada para una siguiente fase de planificación académica.
                            </span>
                        </span>
                    </label>
                </div>

                <div
                    class="rounded-2xl border border-sky-300 bg-sky-50 px-4 py-3 text-sm leading-6 text-sky-900 dark:border-sky-500/30 dark:bg-sky-950 dark:text-sky-200">
                    Regla institucional: solo puede existir una gestión académica ACTIVA. Las demás pueden quedar como
                    PLANIFICADO, CERRADO, ARCHIVADO o INACTIVO.
                </div>

                <div class="flex flex-col gap-3 border-t border-[var(--ui-border)] pt-5 sm:flex-row sm:items-center sm:justify-between">
                    <div>
                        <p class="text-sm font-black text-[var(--ui-text)]">
                            Guardar gestión académica
                        </p>

                        <p class="mt-1 text-xs leading-5 text-[var(--ui-muted)]">
                            El registro se habilita cuando los datos cumplen las condiciones mínimas.
                        </p>
                    </div>

                    <div class="flex flex-wrap gap-3">
                        <button type="button"
                            wire:click="cerrarModal"
                            class="rounded-2xl border border-[var(--ui-border)] bg-[var(--ui-card)] px-5 py-3 text-sm font-bold text-[var(--ui-text)] transition hover:bg-[var(--ui-soft)]">
                            Cancelar
                        </button>

                        <button type="submit"
                            x-bind:disabled="!puedeGuardarGestion"
                            wire:loading.attr="disabled"
                            x-bind:class="puedeGuardarGestion
                                ? 'bg-gradient-to-r from-emerald-600 to-sky-600 text-white shadow-lg shadow-emerald-500/20 hover:-translate-y-0.5 hover:shadow-xl'
                                : 'cursor-not-allowed border border-[var(--ui-border)] bg-[var(--ui-soft)] text-[var(--ui-muted)]'"
                            class="inline-flex items-center justify-center rounded-2xl px-5 py-3 text-sm font-black transition">
                            Guardar gestión académica
                        </button>
                    </div>
                </div>
            </form>
        </section>
    </div>

    {{-- ============================================================
        MODAL CIERRE - SIN TRANSPARENCIA
    ============================================================ --}}
    <div x-show="modalCierre"
        x-cloak
        class="fixed inset-0 z-50 flex items-center justify-center bg-slate-950 p-4">

        <section x-show="modalCierre"
            x-transition
            class="w-full max-w-3xl rounded-[2rem] border border-slate-700 bg-white p-6 shadow-2xl dark:bg-slate-950">

            <div class="flex items-start justify-between gap-4 border-b border-[var(--ui-border)] pb-5">
                <div>
                    <p class="text-sm font-black uppercase tracking-[0.18em] text-amber-600 dark:text-amber-300">
                        Revisión de cierre institucional
                    </p>

                    <h2 class="mt-2 text-2xl font-black text-[var(--ui-text)]">
                        Cierre de gestión académica
                    </h2>

                    <p class="mt-2 max-w-2xl text-sm leading-6 text-[var(--ui-muted)]">
                        El sistema revisa inscripciones y planes académicos antes de permitir el cierre de la gestión.
                    </p>
                </div>

                <button type="button"
                    wire:click="$set('showCloseModal', false)"
                    class="rounded-2xl border border-[var(--ui-border)] bg-[var(--ui-card)] px-4 py-2 text-sm font-bold text-[var(--ui-text)] transition hover:bg-[var(--ui-soft)]">
                    Cerrar
                </button>
            </div>

            <div class="mt-6 space-y-5">
                <div class="rounded-2xl border border-[var(--ui-border)] bg-[var(--ui-soft)] p-4">
                    <p class="text-xs font-bold uppercase tracking-[0.14em] text-[var(--ui-muted)]">
                        Gestión revisada
                    </p>

                    <p class="mt-2 text-lg font-black text-[var(--ui-text)]">
                        {{ $revisionCierre['gestion'] ?? 'Sin gestión seleccionada' }}
                    </p>
                </div>

                <div class="grid gap-4 sm:grid-cols-3">
                    <div class="rounded-2xl border border-[var(--ui-border)] bg-[var(--ui-soft)] p-4">
                        <p class="text-sm font-bold text-[var(--ui-muted)]">
                            Inscripciones pendientes
                        </p>

                        <p class="mt-2 text-3xl font-black {{ ($revisionCierre['inscripciones_pendientes'] ?? 0) > 0 ? 'text-amber-600 dark:text-amber-300' : 'text-emerald-600 dark:text-emerald-300' }}">
                            {{ $revisionCierre['inscripciones_pendientes'] ?? 0 }}
                        </p>
                    </div>

                    <div class="rounded-2xl border border-[var(--ui-border)] bg-[var(--ui-soft)] p-4">
                        <p class="text-sm font-bold text-[var(--ui-muted)]">
                            Plan asignatura incompleto
                        </p>

                        <p class="mt-2 text-3xl font-black {{ ($revisionCierre['planes_asignatura_incompletos'] ?? 0) > 0 ? 'text-amber-600 dark:text-amber-300' : 'text-emerald-600 dark:text-emerald-300' }}">
                            {{ $revisionCierre['planes_asignatura_incompletos'] ?? 0 }}
                        </p>
                    </div>

                    <div class="rounded-2xl border border-[var(--ui-border)] bg-[var(--ui-soft)] p-4">
                        <p class="text-sm font-bold text-[var(--ui-muted)]">
                            Plan especialidad incompleto
                        </p>

                        <p class="mt-2 text-3xl font-black {{ ($revisionCierre['planes_especialidad_incompletos'] ?? 0) > 0 ? 'text-amber-600 dark:text-amber-300' : 'text-emerald-600 dark:text-emerald-300' }}">
                            {{ $revisionCierre['planes_especialidad_incompletos'] ?? 0 }}
                        </p>
                    </div>
                </div>

                @if (($revisionCierre['puede_cerrar'] ?? false) === true)
                    <div
                        class="rounded-2xl border border-emerald-300 bg-emerald-50 px-4 py-3 text-sm leading-6 text-emerald-900 dark:border-emerald-500/30 dark:bg-emerald-950 dark:text-emerald-200">
                        La gestión no presenta pendientes críticos. Puedes cerrarla y conservarla como historial institucional.
                    </div>
                @else
                    <div
                        class="rounded-2xl border border-amber-300 bg-amber-50 px-4 py-3 text-sm leading-6 text-amber-900 dark:border-amber-500/30 dark:bg-amber-950 dark:text-amber-200">
                        El cierre está bloqueado porque existen procesos académicos pendientes o aún no se completó la revisión.
                    </div>
                @endif

                <div class="flex flex-col gap-3 border-t border-[var(--ui-border)] pt-5 sm:flex-row sm:items-center sm:justify-between">
                    <div>
                        <p class="text-sm font-black text-[var(--ui-text)]">
                            Confirmar cierre académico
                        </p>

                        <p class="mt-1 text-xs leading-5 text-[var(--ui-muted)]">
                            Al cerrar la gestión, quedará disponible como historial institucional anual.
                        </p>
                    </div>

                    <div class="flex flex-wrap gap-3">
                        <button type="button"
                            wire:click="$set('showCloseModal', false)"
                            class="rounded-2xl border border-[var(--ui-border)] bg-[var(--ui-card)] px-5 py-3 text-sm font-bold text-[var(--ui-text)] transition hover:bg-[var(--ui-soft)]">
                            Cancelar
                        </button>

                        <button type="button"
                            wire:click="confirmarCierreGestion"
                            @disabled(($revisionCierre['puede_cerrar'] ?? false) !== true)
                            class="rounded-2xl px-5 py-3 text-sm font-black transition
                            {{ ($revisionCierre['puede_cerrar'] ?? false) === true
                                ? 'bg-gradient-to-r from-amber-500 to-orange-500 text-white shadow-lg shadow-amber-500/20 hover:-translate-y-0.5 hover:shadow-xl'
                                : 'cursor-not-allowed border border-[var(--ui-border)] bg-[var(--ui-soft)] text-[var(--ui-muted)]' }}">
                            Cerrar gestión académica
                        </button>
                    </div>
                </div>
            </div>
        </section>
    </div>

    {{-- ============================================================
        DRAWER DETALLE - FONDO SÓLIDO
    ============================================================ --}}
    <div x-show="drawerDetalle"
        x-cloak
        class="fixed inset-0 z-50 bg-slate-950">

        <div class="absolute inset-y-0 right-0 flex w-full justify-end">
            <section
                x-show="drawerDetalle"
                x-transition
                class="h-full w-full max-w-3xl overflow-y-auto border-l border-slate-700 bg-white p-6 shadow-2xl dark:bg-slate-950">

                @if ($gestionSeleccionada)
                    <div class="flex items-start justify-between gap-4 border-b border-[var(--ui-border)] pb-5">
                        <div>
                            <div class="flex flex-wrap items-center gap-2">
                                <span
                                    class="rounded-full border px-3 py-1 text-xs font-black {{ $this->badgeEstadoClass($gestionSeleccionada['estado']) }}">
                                    {{ $gestionSeleccionada['estado'] }}
                                </span>

                                <span
                                    class="rounded-full border border-[var(--ui-border)] bg-[var(--ui-soft)] px-3 py-1 text-xs font-bold text-[var(--ui-muted)]">
                                    Ciclo {{ $gestionSeleccionada['anio'] }}
                                </span>
                            </div>

                            <h2 class="mt-4 text-2xl font-black text-[var(--ui-text)]">
                                Expediente de {{ $gestionSeleccionada['nombre'] }}
                            </h2>

                            <p class="mt-2 text-sm leading-6 text-[var(--ui-muted)]">
                                Consulta institucional del ciclo académico seleccionado.
                            </p>
                        </div>

                        <button type="button"
                            wire:click="cerrarDetalle"
                            class="rounded-2xl border border-[var(--ui-border)] bg-[var(--ui-card)] px-4 py-2 text-sm font-bold text-[var(--ui-text)] transition hover:bg-[var(--ui-soft)]">
                            Cerrar
                        </button>
                    </div>

                    <div class="mt-6 space-y-6">
                        <section class="rounded-[1.5rem] border border-[var(--ui-border)] bg-[var(--ui-soft)] p-5">
                            <h3 class="text-sm font-black uppercase tracking-[0.16em] text-[var(--ui-primary)]">
                                Datos de gestión
                            </h3>

                            <div class="mt-5 grid gap-4 sm:grid-cols-2">
                                <div>
                                    <p class="text-xs font-bold uppercase tracking-[0.12em] text-[var(--ui-muted)]">
                                        Año de gestión
                                    </p>

                                    <p class="mt-1 text-sm font-black text-[var(--ui-text)]">
                                        {{ $gestionSeleccionada['anio'] ?? 'Sin registro' }}
                                    </p>
                                </div>

                                <div>
                                    <p class="text-xs font-bold uppercase tracking-[0.12em] text-[var(--ui-muted)]">
                                        Tipo de expediente
                                    </p>

                                    <p class="mt-1 text-sm font-black text-[var(--ui-text)]">
                                        Historial académico anual
                                    </p>
                                </div>

                                <div>
                                    <p class="text-xs font-bold uppercase tracking-[0.12em] text-[var(--ui-muted)]">
                                        Fecha inicio
                                    </p>

                                    <p class="mt-1 text-sm font-black text-[var(--ui-text)]">
                                        {{ $gestionSeleccionada['fecha_inicio'] ? \Carbon\Carbon::parse($gestionSeleccionada['fecha_inicio'])->format('d/m/Y') : 'Sin registro' }}
                                    </p>
                                </div>

                                <div>
                                    <p class="text-xs font-bold uppercase tracking-[0.12em] text-[var(--ui-muted)]">
                                        Fecha fin
                                    </p>

                                    <p class="mt-1 text-sm font-black text-[var(--ui-text)]">
                                        {{ $gestionSeleccionada['fecha_fin'] ? \Carbon\Carbon::parse($gestionSeleccionada['fecha_fin'])->format('d/m/Y') : 'Sin registro' }}
                                    </p>
                                </div>
                            </div>
                        </section>

                        <section class="grid gap-4 sm:grid-cols-2">
                            <div class="rounded-2xl border border-[var(--ui-border)] bg-[var(--ui-soft)] p-4">
                                <p class="text-xs font-bold uppercase tracking-[0.12em] text-[var(--ui-muted)]">
                                    Inscripciones
                                </p>

                                <p class="mt-2 text-3xl font-black text-[var(--ui-text)]">
                                    {{ $gestionSeleccionada['estudiantes'] }}
                                </p>
                            </div>

                            <div class="rounded-2xl border border-[var(--ui-border)] bg-[var(--ui-soft)] p-4">
                                <p class="text-xs font-bold uppercase tracking-[0.12em] text-[var(--ui-muted)]">
                                    Planes de asignatura
                                </p>

                                <p class="mt-2 text-3xl font-black text-[var(--ui-text)]">
                                    {{ $gestionSeleccionada['planes_asignatura'] ?? 0 }}
                                </p>
                            </div>

                            <div class="rounded-2xl border border-[var(--ui-border)] bg-[var(--ui-soft)] p-4">
                                <p class="text-xs font-bold uppercase tracking-[0.12em] text-[var(--ui-muted)]">
                                    Planes de especialidad
                                </p>

                                <p class="mt-2 text-3xl font-black text-[var(--ui-text)]">
                                    {{ $gestionSeleccionada['planes_especialidad'] ?? 0 }}
                                </p>
                            </div>

                            <div class="rounded-2xl border border-[var(--ui-border)] bg-[var(--ui-soft)] p-4">
                                <p class="text-xs font-bold uppercase tracking-[0.12em] text-[var(--ui-muted)]">
                                    Periodos generales
                                </p>

                                <p class="mt-2 text-3xl font-black text-[var(--ui-text)]">
                                    {{ $gestionSeleccionada['periodos'] }}
                                </p>
                            </div>
                        </section>

                        <section class="grid gap-3 sm:grid-cols-2">
                            <button type="button"
                                wire:click="exportarGestion('{{ $gestionSeleccionada['id'] }}')"
                                class="rounded-2xl border border-rose-300 bg-rose-50 px-4 py-3 text-sm font-black text-rose-800 transition hover:bg-rose-100 dark:border-rose-500/30 dark:bg-rose-950 dark:text-rose-300">
                                Exportar PDF
                            </button>

                            <button type="button"
                                wire:click="exportarGestion('{{ $gestionSeleccionada['id'] }}')"
                                class="rounded-2xl border border-emerald-300 bg-emerald-50 px-4 py-3 text-sm font-black text-emerald-800 transition hover:bg-emerald-100 dark:border-emerald-500/30 dark:bg-emerald-950 dark:text-emerald-300">
                                Exportar Excel
                            </button>

                            @if ($gestionSeleccionada['estado'] === 'ACTIVO')
                                <button type="button"
                                    wire:click="prepararCierre('{{ $gestionSeleccionada['id'] }}')"
                                    class="rounded-2xl border border-amber-300 bg-amber-50 px-4 py-3 text-sm font-black text-amber-800 transition hover:bg-amber-100 dark:border-amber-500/30 dark:bg-amber-950 dark:text-amber-300">
                                    Preparar cierre
                                </button>
                            @endif
                        </section>
                    </div>
                @else
                    <div class="rounded-2xl border border-[var(--ui-border)] bg-[var(--ui-soft)] p-8 text-center">
                        <p class="text-sm font-black text-[var(--ui-text)]">
                            No existe gestión seleccionada.
                        </p>

                        <p class="mt-2 text-sm text-[var(--ui-muted)]">
                            Selecciona una gestión académica para ver su detalle.
                        </p>
                    </div>
                @endif
            </section>
        </div>
    </div>
</div>