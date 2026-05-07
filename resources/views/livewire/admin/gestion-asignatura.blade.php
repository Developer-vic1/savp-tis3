<div class="space-y-6" x-data="{
        color(name) {
            return getComputedStyle(document.documentElement).getPropertyValue(name).trim();
        },

        toast(icon, title, text) {
            Swal.fire({
                icon,
                title,
                text,
                confirmButtonText: 'Entendido',
                confirmButtonColor: this.color('--ui-primary') || '#059669',
                background: this.color('--ui-surface') || '#ffffff',
                color: this.color('--ui-text') || '#0f172a'
            });
        },

        init() {
            window.addEventListener('asignatura-creada', event => {
                this.toast('success', 'Asignatura registrada', event.detail.mensaje ?? 'La asignatura fue registrada correctamente.');
            });

            window.addEventListener('asignatura-actualizada', event => {
                this.toast('success', 'Asignatura actualizada', event.detail.mensaje ?? 'La asignatura fue actualizada correctamente.');
            });

            window.addEventListener('asignatura-desactivada', event => {
                this.toast('success', 'Asignatura desactivada', event.detail.mensaje ?? 'La asignatura fue desactivada correctamente.');
            });

            window.addEventListener('asignatura-reactivada', event => {
                this.toast('success', 'Asignatura reactivada', event.detail.mensaje ?? 'La asignatura fue reactivada correctamente.');
            });

            window.addEventListener('advertencia-general', event => {
                Swal.fire({
                    icon: 'warning',
                    title: 'Revisión requerida',
                    text: event.detail.mensaje ?? 'Revisa la información antes de continuar.',
                    confirmButtonText: 'Entendido',
                    confirmButtonColor: this.color('--ui-warning') || '#d97706',
                    background: this.color('--ui-surface') || '#ffffff',
                    color: this.color('--ui-text') || '#0f172a'
                });
            });

            window.addEventListener('error-general', event => {
                Swal.fire({
                    icon: 'error',
                    title: 'No se pudo completar la acción',
                    text: event.detail.mensaje ?? 'Ocurrió un error inesperado.',
                    confirmButtonText: 'Entendido',
                    confirmButtonColor: this.color('--ui-danger') || '#dc2626',
                    background: this.color('--ui-surface') || '#ffffff',
                    color: this.color('--ui-text') || '#0f172a'
                });
            });

            window.addEventListener('confirmar-desactivar', event => {
                Swal.fire({
                    icon: 'warning',
                    title: event.detail.titulo ?? '¿Desactivar asignatura?',
                    text: event.detail.mensaje ?? 'La asignatura será desactivada.',
                    showCancelButton: true,
                    confirmButtonText: 'Sí, desactivar',
                    cancelButtonText: 'Cancelar',
                    confirmButtonColor: this.color('--ui-warning') || '#d97706',
                    cancelButtonColor: this.color('--ui-muted') || '#64748b',
                    background: this.color('--ui-surface') || '#ffffff',
                    color: this.color('--ui-text') || '#0f172a',
                    reverseButtons: true
                }).then(result => {
                    if (result.isConfirmed) {
                        $wire.desactivarAsignatura(event.detail.codigo);
                    }
                });
            });

            window.addEventListener('confirmar-reactivar', event => {
                Swal.fire({
                    icon: 'question',
                    title: event.detail.titulo ?? '¿Reactivar asignatura?',
                    text: event.detail.mensaje ?? 'La asignatura volverá a estar disponible.',
                    showCancelButton: true,
                    confirmButtonText: 'Sí, reactivar',
                    cancelButtonText: 'Cancelar',
                    confirmButtonColor: this.color('--ui-primary') || '#059669',
                    cancelButtonColor: this.color('--ui-muted') || '#64748b',
                    background: this.color('--ui-surface') || '#ffffff',
                    color: this.color('--ui-text') || '#0f172a',
                    reverseButtons: true
                }).then(result => {
                    if (result.isConfirmed) {
                        $wire.reactivarAsignatura(event.detail.codigo);
                    }
                });
            });
        }
    }">
    {{-- ENCABEZADO --}}
    <section class="ui-card overflow-hidden">
        <div class="relative p-6 lg:p-8" style="background:
                radial-gradient(circle at top left, var(--ui-primary-soft), transparent 34%),
                radial-gradient(circle at top right, var(--ui-violet-soft), transparent 32%),
                linear-gradient(135deg, var(--ui-surface), var(--ui-surface-soft));">
            <div class="grid gap-6 lg:grid-cols-[1.35fr_.65fr] lg:items-center">
                <div class="space-y-5">
                    <div class="inline-flex items-center gap-2 rounded-full border px-4 py-2 text-xs font-black uppercase tracking-[0.18em]"
                        style="border-color: var(--ui-primary-border); background: var(--ui-primary-soft); color: var(--ui-primary);">
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M9.813 15.904 9 18.75l-.813-2.846a4.5 4.5 0 0 0-3.09-3.09L2.25 12l2.846-.813a4.5 4.5 0 0 0 3.09-3.09L9 5.25l.813 2.846a4.5 4.5 0 0 0 3.09 3.09L15.75 12l-2.846.813a4.5 4.5 0 0 0-3.09 3.09Z" />
                        </svg>
                        Motor académico activo
                    </div>

                    <div>
                        <h2 class="ui-title text-3xl font-black tracking-tight md:text-4xl">
                            Gestión de Asignaturas Inteligente
                        </h2>

                        <p class="ui-muted mt-3 max-w-4xl text-sm leading-7 md:text-base">
                            Catálogo institucional para registrar, validar y controlar materias usadas en planes de
                            asignatura,
                            horarios, evaluaciones y calificaciones. El sistema reconoce materias oficiales, redacta
                            entradas
                            informales, advierte coincidencias y bloquea registros académicamente indescifrables.
                        </p>
                    </div>

                    <div class="flex flex-col gap-3 sm:flex-row">
                        <button type="button" wire:click="abrirModalCrear" class="ui-btn-primary">
                            <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="1.8"
                                viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                            </svg>
                            Registrar asignatura
                        </button>

                        <button type="button" wire:click="abrirModalCatalogo" class="ui-btn-secondary">
                            <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="1.8"
                                viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M12 6.042A8.967 8.967 0 0 0 6 3.75c-1.052 0-2.062.18-3 .512v14.25A8.987 8.987 0 0 1 6 18c2.305 0 4.408.867 6 2.292m0-14.25A8.967 8.967 0 0 1 18 3.75c1.052 0 2.062.18 3 .512v14.25A8.987 8.987 0 0 0 18 18a8.967 8.967 0 0 0-6 2.292m0-14.25v14.25" />
                            </svg>
                            Catálogo sugerido
                        </button>
                    </div>
                </div>

                <div class="ui-card-soft p-5">
                    <div class="flex items-center gap-4">
                        <div class="flex h-14 w-14 items-center justify-center rounded-2xl"
                            style="background: var(--ui-primary-soft); color: var(--ui-primary); border: 1px solid var(--ui-primary-border);">
                            <svg class="h-7 w-7" fill="none" stroke="currentColor" stroke-width="1.6"
                                viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M4.26 10.147a60.436 60.436 0 0 0-.491 6.347A48.62 48.62 0 0 1 12 20.904a48.62 48.62 0 0 1 8.232-4.41 60.46 60.46 0 0 0-.491-6.347m-15.482 0A50.57 50.57 0 0 0 12 3.493a50.57 50.57 0 0 0 7.74 6.654m-15.48 0a50.984 50.984 0 0 1 15.48 0" />
                            </svg>
                        </div>

                        <div>
                            <p class="ui-title text-sm font-black">Control curricular</p>
                            <p class="ui-muted mt-1 text-xs leading-5">
                                Normalización, similitud, redacción profesional y trazabilidad académica.
                            </p>
                        </div>
                    </div>

                    <div class="mt-5 grid grid-cols-2 gap-3">
                        <div class="rounded-2xl border p-3"
                            style="border-color: var(--ui-primary-border); background: var(--ui-primary-soft);">
                            <p class="text-xs font-bold" style="color: var(--ui-primary);">Activas</p>
                            <p class="mt-1 text-xl font-black" style="color: var(--ui-primary);">
                                {{ $resumen['activas'] ?? 0 }}</p>
                        </div>

                        <div class="rounded-2xl border p-3"
                            style="border-color: var(--ui-warning-border); background: var(--ui-warning-soft);">
                            <p class="text-xs font-bold" style="color: var(--ui-warning);">Sin uso</p>
                            <p class="mt-1 text-xl font-black" style="color: var(--ui-warning);">
                                {{ $resumen['sin_uso'] ?? 0 }}</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    {{-- ALERTA --}}
    <section class="ui-alert-info">
        <div class="flex gap-3">
            <svg class="mt-0.5 h-5 w-5 flex-none" fill="none" stroke="currentColor" stroke-width="1.8"
                viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round"
                    d="m11.25 11.25.041-.02a.75.75 0 0 1 1.063.852l-.708 2.836a.75.75 0 0 0 1.063.853l.041-.021M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Zm-9-3.75h.008v.008H12V8.25Z" />
            </svg>

            <p class="leading-6">
                Las asignaturas no se eliminan físicamente. Si una asignatura deja de utilizarse, debe desactivarse
                para mantener trazabilidad académica y conservar su historial institucional.
            </p>
        </div>
    </section>

    {{-- RESUMEN --}}
    <section class="grid gap-4 sm:grid-cols-2 xl:grid-cols-6">
        <article class="ui-card ui-card-hover p-5">
            <div class="flex items-center justify-between">
                <p class="ui-muted text-xs font-black uppercase tracking-wider">Total</p>
                <span class="rounded-xl p-2" style="background: var(--ui-info-soft); color: var(--ui-info);">
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="1.7" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M8.25 6.75h12M8.25 12h12m-12 5.25h12M3.75 6.75h.007v.008H3.75V6.75Zm.007 5.25H3.75v.008h.007V12Zm0 5.25H3.75v.008h.007v-.008Z" />
                    </svg>
                </span>
            </div>
            <p class="ui-title mt-4 text-3xl font-black">{{ $resumen['total'] ?? 0 }}</p>
            <p class="ui-muted mt-1 text-xs">Asignaturas registradas</p>
        </article>

        <article class="ui-card ui-card-hover p-5">
            <div class="flex items-center justify-between">
                <p class="ui-muted text-xs font-black uppercase tracking-wider">Activas</p>
                <span class="rounded-xl p-2" style="background: var(--ui-primary-soft); color: var(--ui-primary);">
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="1.7" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                    </svg>
                </span>
            </div>
            <p class="ui-title mt-4 text-3xl font-black">{{ $resumen['activas'] ?? 0 }}</p>
            <p class="ui-muted mt-1 text-xs">Disponibles para planificación</p>
        </article>

        <article class="ui-card ui-card-hover p-5">
            <div class="flex items-center justify-between">
                <p class="ui-muted text-xs font-black uppercase tracking-wider">Inactivas</p>
                <span class="rounded-xl p-2" style="background: var(--ui-warning-soft); color: var(--ui-warning);">
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="1.7" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M18.364 18.364A9 9 0 0 0 5.636 5.636m12.728 12.728A9 9 0 0 1 5.636 5.636m12.728 12.728L5.636 5.636" />
                    </svg>
                </span>
            </div>
            <p class="ui-title mt-4 text-3xl font-black">{{ $resumen['inactivas'] ?? 0 }}</p>
            <p class="ui-muted mt-1 text-xs">Conservan historial</p>
        </article>

        <article class="ui-card ui-card-hover p-5">
            <div class="flex items-center justify-between">
                <p class="ui-muted text-xs font-black uppercase tracking-wider">Con uso</p>
                <span class="rounded-xl p-2" style="background: var(--ui-violet-soft); color: var(--ui-violet);">
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="1.7" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M3.75 4.875c0-.621.504-1.125 1.125-1.125h4.5c.621 0 1.125.504 1.125 1.125v4.5c0 .621-.504 1.125-1.125 1.125h-4.5A1.125 1.125 0 0 1 3.75 9.375v-4.5Zm0 9.75c0-.621.504-1.125 1.125-1.125h4.5c.621 0 1.125.504 1.125 1.125v4.5c0 .621-.504 1.125-1.125 1.125h-4.5a1.125 1.125 0 0 1-1.125-1.125v-4.5Zm9.75-9.75c0-.621.504-1.125 1.125-1.125h4.5c.621 0 1.125.504 1.125 1.125v4.5c0 .621-.504 1.125-1.125 1.125h-4.5A1.125 1.125 0 0 1 13.5 9.375v-4.5Z" />
                    </svg>
                </span>
            </div>
            <p class="ui-title mt-4 text-3xl font-black">{{ $resumen['con_uso'] ?? 0 }}</p>
            <p class="ui-muted mt-1 text-xs">Planes o calificaciones</p>
        </article>

        <article class="ui-card ui-card-hover p-5">
            <div class="flex items-center justify-between">
                <p class="ui-muted text-xs font-black uppercase tracking-wider">Sin uso</p>
                <span class="rounded-xl p-2" style="background: var(--ui-info-soft); color: var(--ui-info);">
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="1.7" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M12 6v6l3 3m6-3a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                    </svg>
                </span>
            </div>
            <p class="ui-title mt-4 text-3xl font-black">{{ $resumen['sin_uso'] ?? 0 }}</p>
            <p class="ui-muted mt-1 text-xs">Disponibles para configurar</p>
        </article>

        <article class="ui-card ui-card-hover p-5">
            <div class="flex items-center justify-between">
                <p class="ui-muted text-xs font-black uppercase tracking-wider">Horas</p>
                <span class="rounded-xl p-2" style="background: var(--ui-primary-soft); color: var(--ui-primary);">
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="1.7" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M12 8.25v4.5l3 1.5M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                    </svg>
                </span>
            </div>
            <p class="ui-title mt-4 text-3xl font-black">{{ $resumen['horas'] ?? 0 }}</p>
            <p class="ui-muted mt-1 text-xs">Carga académica total</p>
        </article>
    </section>

    {{-- FILTROS --}}
    <section class="ui-card p-4">
        <div class="grid gap-3 lg:grid-cols-[1.4fr_.7fr_.8fr_.5fr_auto]">
            <div>
                <label class="ui-label">Buscar</label>
                <div class="relative">
                    <svg class="pointer-events-none absolute left-4 top-1/2 h-5 w-5 -translate-y-1/2 ui-muted"
                        fill="none" stroke="currentColor" stroke-width="1.7" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="m21 21-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 5.196a7.5 7.5 0 0 0 10.607 10.607Z" />
                    </svg>

                    <input type="search" wire:model.live.debounce.400ms="search" class="ui-input pl-11"
                        placeholder="Buscar asignatura, código o sigla...">
                </div>
            </div>

            <div>
                <label class="ui-label">Estado</label>
                <select wire:model.live="estado" class="ui-select">
                    <option value="">Todos</option>
                    @foreach ($estadosDisponibles as $valor => $texto)
                        <option value="{{ $valor }}">{{ $texto }}</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="ui-label">Uso académico</label>
                <select wire:model.live="usoAcademico" class="ui-select">
                    @foreach ($opcionesUsoAcademico as $valor => $texto)
                        <option value="{{ $valor }}">{{ $texto }}</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="ui-label">Mostrar</label>
                <select wire:model.live="perPage" class="ui-select">
                    <option value="5">5</option>
                    <option value="10">10</option>
                    <option value="15">15</option>
                    <option value="25">25</option>
                </select>
            </div>

            <div class="flex items-end">
                <button type="button" wire:click="limpiarFiltros" class="ui-btn-secondary w-full">
                    Limpiar
                </button>
            </div>
        </div>
    </section>

    {{-- TABLA --}}
    <section class="ui-table-wrap">
        <div class="flex flex-col gap-3 border-b p-5 md:flex-row md:items-center md:justify-between"
            style="border-color: var(--ui-border);">
            <div>
                <h3 class="ui-title text-lg font-black">Catálogo institucional de asignaturas</h3>
                <p class="ui-muted mt-1 text-sm">
                    Control académico con uso, estado, sugerencias inteligentes y trazabilidad.
                </p>
            </div>

            <div wire:loading class="text-sm font-bold" style="color: var(--ui-primary);">
                Actualizando información...
            </div>
        </div>

        <div class="overflow-x-auto ui-scrollbar">
            <table class="ui-table">
                <thead>
                    <tr>
                        <th>
                            <button type="button" wire:click="ordenarPor('cod_asi')"
                                class="hover:underline">Código</button>
                        </th>
                        <th>
                            <button type="button" wire:click="ordenarPor('nom_asi')"
                                class="hover:underline">Asignatura</button>
                        </th>
                        <th>
                            <button type="button" wire:click="ordenarPor('sig_asi')"
                                class="hover:underline">Sigla</button>
                        </th>
                        <th>
                            <button type="button" wire:click="ordenarPor('hor_asi')"
                                class="hover:underline">Horas</button>
                        </th>
                        <th>Uso académico</th>
                        <th>Estado</th>
                        <th class="text-right">Acciones</th>
                    </tr>
                </thead>

                <tbody>
                    @forelse ($asignaturas as $asignatura)
                        @php
                            $uso = $asignatura->uso_academico ?? [
                                'planes' => 0,
                                'calificaciones' => 0,
                                'horarios' => 0,
                                'total' => 0,
                                'tiene_uso' => false,
                                'texto' => 'Sin uso académico',
                            ];

                            $analisis = $asignatura->analisis_inteligente ?? [];
                            $estadoInteligente = $analisis['estado_inteligente'] ?? '';
                        @endphp

                        <tr wire:key="asignatura-{{ $asignatura->cod_asi }}">
                            <td class="whitespace-nowrap">
                                <span class="ui-badge-muted font-black">{{ $asignatura->cod_asi }}</span>
                            </td>

                            <td class="min-w-[300px]">
                                <div class="flex items-center gap-3">
                                    <div class="flex h-11 w-11 flex-none items-center justify-center rounded-2xl text-sm font-black"
                                        style="background: var(--ui-primary-soft); color: var(--ui-primary); border: 1px solid var(--ui-primary-border);">
                                        {{ mb_substr($asignatura->nom_asi, 0, 1) }}
                                    </div>

                                    <div>
                                        <p class="ui-title font-black">{{ $asignatura->nom_asi }}</p>

                                        <div class="mt-1 flex flex-wrap gap-2">
                                            @if ($estadoInteligente === 'RECONOCIDA')
                                                <span class="ui-badge-success">Reconocida</span>
                                            @elseif ($estadoInteligente === 'REDACTABLE')
                                                <span class="ui-badge-info">Redactable</span>
                                            @elseif ($estadoInteligente === 'REQUIERE_REVISION')
                                                <span class="ui-badge-warning">Revisión</span>
                                            @elseif ($estadoInteligente === 'BLOQUEADA')
                                                <span class="ui-badge-danger">No validada</span>
                                            @else
                                                <span class="ui-badge-muted">Sin análisis</span>
                                            @endif

                                            @if (!empty($analisis['area']))
                                                <span class="ui-badge-violet">{{ $analisis['area'] }}</span>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            </td>

                            <td class="whitespace-nowrap">
                                <span class="ui-badge-violet font-black">{{ $asignatura->sig_asi }}</span>
                            </td>

                            <td class="whitespace-nowrap">
                                <span class="ui-title text-sm font-black">{{ $asignatura->hor_asi }} h</span>
                            </td>

                            <td class="min-w-[220px]">
                                @if ($uso['tiene_uso'] ?? false)
                                    <div class="space-y-1">
                                        <span class="ui-badge-info">Con uso académico</span>
                                        <p class="ui-muted text-xs">{{ $uso['texto'] }}</p>
                                    </div>
                                @else
                                    <div class="space-y-1">
                                        <span class="ui-badge-muted">Sin uso académico</span>
                                        <p class="ui-muted text-xs">Disponible para configurar</p>
                                    </div>
                                @endif
                            </td>

                            <td class="whitespace-nowrap">
                                @if ($asignatura->est_asi === 'ACTIVO')
                                    <span class="ui-badge-success">
                                        <span class="h-2 w-2 rounded-full" style="background: var(--ui-primary);"></span>
                                        Activo
                                    </span>
                                @else
                                    <span class="ui-badge-warning">
                                        <span class="h-2 w-2 rounded-full" style="background: var(--ui-warning);"></span>
                                        Inactivo
                                    </span>
                                @endif
                            </td>

                            <td class="whitespace-nowrap text-right">
                                <div class="inline-flex items-center gap-1.5">
                                    <button type="button" wire:click="abrirModalDetalle('{{ $asignatura->cod_asi }}')"
                                        class="ui-icon-btn" title="Ver detalle">
                                        <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="1.7"
                                            viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                d="M2.036 12.322a1.012 1.012 0 0 1 0-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178Z" />
                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" />
                                        </svg>
                                    </button>

                                    <button type="button" wire:click="abrirModalEditar('{{ $asignatura->cod_asi }}')"
                                        class="ui-icon-btn" title="Editar">
                                        <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="1.7"
                                            viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                d="m16.862 4.487 1.687-1.688a1.875 1.875 0 1 1 2.652 2.652L10.582 16.07a4.5 4.5 0 0 1-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 0 1 1.13-1.897l8.932-8.931Zm0 0L19.5 7.125" />
                                        </svg>
                                    </button>

                                    @if ($asignatura->est_asi === 'ACTIVO')
                                        <button type="button" wire:click="solicitarDesactivar('{{ $asignatura->cod_asi }}')"
                                            class="ui-icon-btn" title="Desactivar" style="color: var(--ui-warning);">
                                            <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="1.7"
                                                viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round"
                                                    d="M18.364 18.364A9 9 0 0 0 5.636 5.636m12.728 12.728A9 9 0 0 1 5.636 5.636m12.728 12.728L5.636 5.636" />
                                            </svg>
                                        </button>
                                    @else
                                        <button type="button" wire:click="solicitarReactivar('{{ $asignatura->cod_asi }}')"
                                            class="ui-icon-btn" title="Reactivar" style="color: var(--ui-primary);">
                                            <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="1.7"
                                                viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round"
                                                    d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                                            </svg>
                                        </button>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="py-16 text-center">
                                <div class="mx-auto flex max-w-md flex-col items-center px-5">
                                    <div class="flex h-16 w-16 items-center justify-center rounded-3xl"
                                        style="background: var(--ui-primary-soft); color: var(--ui-primary); border: 1px solid var(--ui-primary-border);">
                                        <svg class="h-8 w-8" fill="none" stroke="currentColor" stroke-width="1.7"
                                            viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                d="M12 6.042A8.967 8.967 0 0 0 6 3.75c-1.052 0-2.062.18-3 .512v14.25A8.987 8.987 0 0 1 6 18c2.305 0 4.408.867 6 2.292m0-14.25A8.967 8.967 0 0 1 18 3.75c1.052 0 2.062.18 3 .512v14.25A8.987 8.987 0 0 0 18 18a8.967 8.967 0 0 0-6 2.292m0-14.25v14.25" />
                                        </svg>
                                    </div>

                                    <h4 class="ui-title mt-4 text-lg font-black">No se encontraron asignaturas</h4>
                                    <p class="ui-muted mt-2 text-sm leading-6">
                                        Ajusta los filtros o registra una nueva asignatura usando el motor inteligente.
                                    </p>

                                    <button type="button" wire:click="abrirModalCrear" class="ui-btn-primary mt-5">
                                        Registrar asignatura
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($asignaturas->hasPages())
            <div class="border-t px-5 py-4" style="border-color: var(--ui-border);">
                {{ $asignaturas->links() }}
            </div>
        @endif
    </section>

    {{-- MODAL CREAR --}}
    @if ($modalCrear)
        <div class="fixed inset-0 z-50 flex items-center justify-center p-4">
            <div class="ui-modal-backdrop"></div>

            <div class="ui-modal max-h-[92vh] w-full max-w-6xl overflow-y-auto ui-scrollbar">
                <div class="ui-modal-header sticky top-0 z-10 flex items-start justify-between">
                    <div>
                        <h3 class="ui-title text-2xl font-black">Registrar nueva asignatura</h3>
                        <p class="ui-muted mt-1 text-sm">
                            El motor inteligente analizará, redactará o bloqueará la materia según su validez académica.
                        </p>
                    </div>

                    <button type="button" wire:click="cerrarModalCrear" class="ui-icon-btn">
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>

                <div class="grid gap-6 p-6 lg:grid-cols-[.95fr_1.05fr]">
                    <div class="space-y-5">
                        <div>
                            <label class="ui-label">Nombre de la asignatura</label>
                            <input type="text" wire:model.live.debounce.500ms="form.nom_asi" class="ui-input"
                                placeholder="Ej. Matemática, Programación, Robótica Educativa...">
                            @error('form.nom_asi')
                                <p class="ui-error">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="grid gap-4 sm:grid-cols-2">
                            <div>
                                <label class="ui-label">Sigla</label>
                                <input type="text" wire:model.live="form.sig_asi" class="ui-input uppercase"
                                    placeholder="MAT">
                                @error('form.sig_asi')
                                    <p class="ui-error">{{ $message }}</p>
                                @enderror
                            </div>

                            <div>
                                <label class="ui-label">Horas académicas</label>
                                <input type="number" min="1" max="80" wire:model.live="form.hor_asi" class="ui-input">
                                @error('form.hor_asi')
                                    <p class="ui-error">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>

                        <div>
                            <label class="ui-label">Estado inicial</label>
                            <select wire:model.live="form.est_asi" class="ui-select">
                                @foreach ($estadosDisponibles as $valor => $texto)
                                    <option value="{{ $valor }}">{{ $texto }}</option>
                                @endforeach
                            </select>
                            @error('form.est_asi')
                                <p class="ui-error">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="ui-alert-info">
                            Si el sistema no logra descifrar la entrada como asignatura académica, bloqueará el registro
                            y mostrará soporte académico.
                        </div>
                    </div>

                    @php
                        $analisis = $analisisCrear;
                    @endphp

                    @includeWhen(false, 'nada')

                    {{-- PANEL INTELIGENTE CREAR --}}
                    <div class="space-y-5">
                        @php
                            $estadoPanel = $analisis['estado_inteligente'] ?? '';
                            $requiereSoporte = $analisis['requiere_soporte'] ?? false;
                            $advertencias = $analisis['advertencias'] ?? [];
                            $coincidencias = $analisis['coincidencias'] ?? [];
                            $carreras = $analisis['carreras_relacionadas'] ?? [];

                            $panelClass = match ($estadoPanel) {
                                'RECONOCIDA' => 'ui-alert-success',
                                'REDACTABLE' => 'ui-alert-info',
                                'REQUIERE_REVISION' => 'ui-alert-warning',
                                'BLOQUEADA' => 'ui-alert-danger',
                                default => 'ui-card-soft',
                            };
                        @endphp

                        <div class="{{ $panelClass }} p-5">
                            <div class="flex items-start justify-between gap-4">
                                <div>
                                    <p class="text-xs font-black uppercase tracking-[0.16em]">Análisis inteligente</p>
                                    <h4 class="mt-2 text-xl font-black">
                                        @if ($estadoPanel === 'RECONOCIDA')
                                            Asignatura reconocida
                                        @elseif ($estadoPanel === 'REDACTABLE')
                                            Redacción sugerida
                                        @elseif ($estadoPanel === 'REQUIERE_REVISION')
                                            Requiere revisión
                                        @elseif ($estadoPanel === 'BLOQUEADA')
                                            Creación bloqueada
                                        @else
                                            Vista previa académica
                                        @endif
                                    </h4>
                                </div>

                                <div class="rounded-2xl px-4 py-2 text-right" style="background: rgba(0,0,0,.08);">
                                    <p class="text-xs font-bold opacity-80">Confianza</p>
                                    <p class="text-2xl font-black">{{ $analisis['confianza'] ?? 0 }}%</p>
                                </div>
                            </div>

                            <p class="mt-4 text-sm leading-6">
                                {{ $analisis['mensaje'] ?? 'Escribe una asignatura para activar el análisis inteligente.' }}
                            </p>

                            @if ($requiereSoporte)
                                <div class="mt-4 rounded-2xl border p-4"
                                    style="border-color: var(--ui-danger-border); background: var(--ui-danger-soft); color: var(--ui-danger);">
                                    <p class="text-sm font-black">Soporte académico</p>
                                    <p class="mt-1 text-sm leading-6">
                                        No se pudo descifrar esta entrada como asignatura válida. Contacta con soporte al
                                        <span class="font-black">{{ $analisis['telefono_soporte'] ?? '75836807' }}</span>.
                                    </p>
                                </div>
                            @endif
                        </div>

                        <div class="grid gap-4 md:grid-cols-2">
                            <div class="ui-card-soft p-4">
                                <p class="ui-muted text-xs font-black uppercase tracking-wider">Nombre sugerido</p>
                                <p class="ui-title mt-2 text-lg font-black">
                                    {{ $analisis['nombre'] ?: 'Pendiente de análisis' }}</p>
                            </div>

                            <div class="ui-card-soft p-4">
                                <p class="ui-muted text-xs font-black uppercase tracking-wider">Sigla sugerida</p>
                                <p class="mt-2 text-lg font-black" style="color: var(--ui-violet);">
                                    {{ $analisis['sigla'] ?: '-' }}</p>
                            </div>

                            <div class="ui-card-soft p-4">
                                <p class="ui-muted text-xs font-black uppercase tracking-wider">Horas sugeridas</p>
                                <p class="mt-2 text-lg font-black" style="color: var(--ui-primary);">
                                    {{ $analisis['horas'] ?? 0 }} h</p>
                            </div>

                            <div class="ui-card-soft p-4">
                                <p class="ui-muted text-xs font-black uppercase tracking-wider">Estado inteligente</p>
                                <p class="ui-title mt-2 text-sm font-black">{{ $estadoPanel ?: 'SIN_ANALISIS' }}</p>
                            </div>
                        </div>

                        <div class="ui-card-soft p-5">
                            <h5 class="ui-title text-sm font-black">Clasificación académica</h5>

                            <div class="mt-4 grid gap-4 md:grid-cols-3">
                                <div>
                                    <p class="ui-muted text-xs font-black uppercase tracking-wider">Área</p>
                                    <p class="ui-title mt-1 text-sm font-bold">{{ $analisis['area'] ?: 'No definida' }}</p>
                                </div>

                                <div>
                                    <p class="ui-muted text-xs font-black uppercase tracking-wider">Tipo</p>
                                    <p class="ui-title mt-1 text-sm font-bold">{{ $analisis['tipo'] ?: 'No definido' }}</p>
                                </div>

                                <div>
                                    <p class="ui-muted text-xs font-black uppercase tracking-wider">Nivel</p>
                                    <p class="ui-title mt-1 text-sm font-bold">{{ $analisis['nivel'] ?: 'No definido' }}</p>
                                </div>
                            </div>

                            @if (!empty($analisis['descripcion']))
                                <p class="ui-muted mt-4 text-sm leading-6">{{ $analisis['descripcion'] }}</p>
                            @endif
                        </div>

                        @if (!empty($advertencias))
                            <div class="ui-alert-warning">
                                <h5 class="font-black">Advertencias institucionales</h5>
                                <ul class="mt-3 space-y-2">
                                    @foreach ($advertencias as $advertencia)
                                        <li class="flex gap-2 text-sm leading-6">
                                            <span class="mt-2 h-1.5 w-1.5 flex-none rounded-full"
                                                style="background: var(--ui-warning);"></span>
                                            <span>{{ $advertencia }}</span>
                                        </li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif

                        @if (!empty($coincidencias))
                            <div class="ui-alert-danger">
                                <h5 class="font-black">Coincidencias similares detectadas</h5>
                                <div class="mt-3 space-y-2">
                                    @foreach (array_slice($coincidencias, 0, 4) as $coincidencia)
                                        <div class="rounded-2xl border p-3" style="border-color: var(--ui-danger-border);">
                                            <div class="flex items-center justify-between gap-3">
                                                <div>
                                                    <p class="text-sm font-black">{{ $coincidencia['nombre'] ?? '-' }}</p>
                                                    <p class="mt-1 text-xs opacity-80">
                                                        {{ $coincidencia['codigo'] ?? '-' }} · {{ $coincidencia['sigla'] ?? '-' }}
                                                    </p>
                                                </div>
                                                <span class="rounded-full px-3 py-1 text-xs font-black"
                                                    style="background: var(--ui-danger-soft);">
                                                    {{ $coincidencia['similitud'] ?? 0 }}%
                                                </span>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        @endif

                        @if (!empty($carreras))
                            <div class="ui-alert-info">
                                <h5 class="font-black">Carreras relacionadas</h5>
                                <div class="mt-3 flex flex-wrap gap-2">
                                    @foreach (array_slice($carreras, 0, 10) as $carrera)
                                        <span class="ui-badge-info">{{ $carrera }}</span>
                                    @endforeach
                                </div>
                            </div>
                        @endif
                    </div>
                </div>

                <div class="ui-modal-footer flex flex-col-reverse gap-3 sm:flex-row sm:justify-end">
                    <button type="button" wire:click="cerrarModalCrear" class="ui-btn-secondary">
                        Cancelar
                    </button>

                    <button type="button" wire:click="usarSugerenciaCrear" class="ui-btn-secondary">
                        Usar sugerencia
                    </button>

                    <button type="button" wire:click="guardarAsignatura" wire:loading.attr="disabled"
                        class="ui-btn-primary">
                        <span wire:loading.remove wire:target="guardarAsignatura">Guardar asignatura</span>
                        <span wire:loading wire:target="guardarAsignatura">Guardando...</span>
                    </button>
                </div>
            </div>
        </div>
    @endif

    {{-- MODAL EDITAR --}}
    @if ($modalEditar)
        <div class="fixed inset-0 z-50 flex items-center justify-center p-4">
            <div class="ui-modal-backdrop"></div>

            <div class="ui-modal max-h-[92vh] w-full max-w-6xl overflow-y-auto ui-scrollbar">
                <div class="ui-modal-header sticky top-0 z-10 flex items-start justify-between">
                    <div>
                        <h3 class="ui-title text-2xl font-black">Editar asignatura</h3>
                        <p class="ui-muted mt-1 text-sm">
                            Si la asignatura tiene historial académico, solo se recomiendan correcciones menores.
                        </p>
                    </div>

                    <button type="button" wire:click="cerrarModalEditar" class="ui-icon-btn">
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>

                <div class="grid gap-6 p-6 lg:grid-cols-[.95fr_1.05fr]">
                    <div class="space-y-5">
                        <div>
                            <label class="ui-label">Código</label>
                            <input type="text" wire:model="formEditar.cod_asi" disabled class="ui-field-readonly w-full">
                        </div>

                        <div>
                            <label class="ui-label">Nombre de la asignatura</label>
                            <input type="text" wire:model.live.debounce.500ms="formEditar.nom_asi" class="ui-input">
                            @error('formEditar.nom_asi')
                                <p class="ui-error">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="grid gap-4 sm:grid-cols-2">
                            <div>
                                <label class="ui-label">Sigla</label>
                                <input type="text" wire:model.live="formEditar.sig_asi" class="ui-input uppercase">
                                @error('formEditar.sig_asi')
                                    <p class="ui-error">{{ $message }}</p>
                                @enderror
                            </div>

                            <div>
                                <label class="ui-label">Horas académicas</label>
                                <input type="number" min="1" max="80" wire:model.live="formEditar.hor_asi" class="ui-input">
                                @error('formEditar.hor_asi')
                                    <p class="ui-error">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>

                        <div>
                            <label class="ui-label">Estado</label>
                            <select wire:model.live="formEditar.est_asi" class="ui-select">
                                @foreach ($estadosDisponibles as $valor => $texto)
                                    <option value="{{ $valor }}">{{ $texto }}</option>
                                @endforeach
                            </select>
                            @error('formEditar.est_asi')
                                <p class="ui-error">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    @php
                        $analisis = $analisisEditar;
                        $estadoPanel = $analisis['estado_inteligente'] ?? '';
                        $requiereSoporte = $analisis['requiere_soporte'] ?? false;
                        $advertencias = $analisis['advertencias'] ?? [];
                        $coincidencias = $analisis['coincidencias'] ?? [];
                        $carreras = $analisis['carreras_relacionadas'] ?? [];

                        $panelClass = match ($estadoPanel) {
                            'RECONOCIDA' => 'ui-alert-success',
                            'REDACTABLE' => 'ui-alert-info',
                            'REQUIERE_REVISION' => 'ui-alert-warning',
                            'BLOQUEADA' => 'ui-alert-danger',
                            default => 'ui-card-soft',
                        };
                    @endphp

                    <div class="space-y-5">
                        <div class="{{ $panelClass }} p-5">
                            <div class="flex items-start justify-between gap-4">
                                <div>
                                    <p class="text-xs font-black uppercase tracking-[0.16em]">Análisis inteligente</p>
                                    <h4 class="mt-2 text-xl font-black">
                                        @if ($estadoPanel === 'RECONOCIDA')
                                            Asignatura reconocida
                                        @elseif ($estadoPanel === 'REDACTABLE')
                                            Redacción sugerida
                                        @elseif ($estadoPanel === 'REQUIERE_REVISION')
                                            Requiere revisión
                                        @elseif ($estadoPanel === 'BLOQUEADA')
                                            Edición bloqueada
                                        @else
                                            Vista previa académica
                                        @endif
                                    </h4>
                                </div>

                                <div class="rounded-2xl px-4 py-2 text-right" style="background: rgba(0,0,0,.08);">
                                    <p class="text-xs font-bold opacity-80">Confianza</p>
                                    <p class="text-2xl font-black">{{ $analisis['confianza'] ?? 0 }}%</p>
                                </div>
                            </div>

                            <p class="mt-4 text-sm leading-6">
                                {{ $analisis['mensaje'] ?? 'Edita la asignatura para activar el análisis inteligente.' }}
                            </p>

                            @if ($requiereSoporte)
                                <div class="mt-4 rounded-2xl border p-4"
                                    style="border-color: var(--ui-danger-border); background: var(--ui-danger-soft); color: var(--ui-danger);">
                                    <p class="text-sm font-black">Soporte académico</p>
                                    <p class="mt-1 text-sm leading-6">
                                        No se pudo descifrar esta entrada como asignatura válida. Contacta con soporte al
                                        <span class="font-black">{{ $analisis['telefono_soporte'] ?? '75836807' }}</span>.
                                    </p>
                                </div>
                            @endif
                        </div>

                        <div class="grid gap-4 md:grid-cols-2">
                            <div class="ui-card-soft p-4">
                                <p class="ui-muted text-xs font-black uppercase tracking-wider">Nombre sugerido</p>
                                <p class="ui-title mt-2 text-lg font-black">
                                    {{ $analisis['nombre'] ?: 'Pendiente de análisis' }}</p>
                            </div>

                            <div class="ui-card-soft p-4">
                                <p class="ui-muted text-xs font-black uppercase tracking-wider">Sigla sugerida</p>
                                <p class="mt-2 text-lg font-black" style="color: var(--ui-violet);">
                                    {{ $analisis['sigla'] ?: '-' }}</p>
                            </div>

                            <div class="ui-card-soft p-4">
                                <p class="ui-muted text-xs font-black uppercase tracking-wider">Horas sugeridas</p>
                                <p class="mt-2 text-lg font-black" style="color: var(--ui-primary);">
                                    {{ $analisis['horas'] ?? 0 }} h</p>
                            </div>

                            <div class="ui-card-soft p-4">
                                <p class="ui-muted text-xs font-black uppercase tracking-wider">Estado inteligente</p>
                                <p class="ui-title mt-2 text-sm font-black">{{ $estadoPanel ?: 'SIN_ANALISIS' }}</p>
                            </div>
                        </div>

                        <div class="ui-card-soft p-5">
                            <h5 class="ui-title text-sm font-black">Clasificación académica</h5>

                            <div class="mt-4 grid gap-4 md:grid-cols-3">
                                <div>
                                    <p class="ui-muted text-xs font-black uppercase tracking-wider">Área</p>
                                    <p class="ui-title mt-1 text-sm font-bold">{{ $analisis['area'] ?: 'No definida' }}</p>
                                </div>

                                <div>
                                    <p class="ui-muted text-xs font-black uppercase tracking-wider">Tipo</p>
                                    <p class="ui-title mt-1 text-sm font-bold">{{ $analisis['tipo'] ?: 'No definido' }}</p>
                                </div>

                                <div>
                                    <p class="ui-muted text-xs font-black uppercase tracking-wider">Nivel</p>
                                    <p class="ui-title mt-1 text-sm font-bold">{{ $analisis['nivel'] ?: 'No definido' }}</p>
                                </div>
                            </div>

                            @if (!empty($analisis['descripcion']))
                                <p class="ui-muted mt-4 text-sm leading-6">{{ $analisis['descripcion'] }}</p>
                            @endif
                        </div>

                        @if (!empty($advertencias))
                            <div class="ui-alert-warning">
                                <h5 class="font-black">Advertencias institucionales</h5>
                                <ul class="mt-3 space-y-2">
                                    @foreach ($advertencias as $advertencia)
                                        <li class="flex gap-2 text-sm leading-6">
                                            <span class="mt-2 h-1.5 w-1.5 flex-none rounded-full"
                                                style="background: var(--ui-warning);"></span>
                                            <span>{{ $advertencia }}</span>
                                        </li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif

                        @if (!empty($coincidencias))
                            <div class="ui-alert-danger">
                                <h5 class="font-black">Coincidencias similares detectadas</h5>
                                <div class="mt-3 space-y-2">
                                    @foreach (array_slice($coincidencias, 0, 4) as $coincidencia)
                                        <div class="rounded-2xl border p-3" style="border-color: var(--ui-danger-border);">
                                            <div class="flex items-center justify-between gap-3">
                                                <div>
                                                    <p class="text-sm font-black">{{ $coincidencia['nombre'] ?? '-' }}</p>
                                                    <p class="mt-1 text-xs opacity-80">
                                                        {{ $coincidencia['codigo'] ?? '-' }} · {{ $coincidencia['sigla'] ?? '-' }}
                                                    </p>
                                                </div>
                                                <span class="rounded-full px-3 py-1 text-xs font-black"
                                                    style="background: var(--ui-danger-soft);">
                                                    {{ $coincidencia['similitud'] ?? 0 }}%
                                                </span>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        @endif

                        @if (!empty($carreras))
                            <div class="ui-alert-info">
                                <h5 class="font-black">Carreras relacionadas</h5>
                                <div class="mt-3 flex flex-wrap gap-2">
                                    @foreach (array_slice($carreras, 0, 10) as $carrera)
                                        <span class="ui-badge-info">{{ $carrera }}</span>
                                    @endforeach
                                </div>
                            </div>
                        @endif
                    </div>
                </div>

                <div class="ui-modal-footer flex flex-col-reverse gap-3 sm:flex-row sm:justify-end">
                    <button type="button" wire:click="cerrarModalEditar" class="ui-btn-secondary">
                        Cancelar
                    </button>

                    <button type="button" wire:click="usarSugerenciaEditar" class="ui-btn-secondary">
                        Usar sugerencia
                    </button>

                    <button type="button" wire:click="guardarEdicionAsignatura" wire:loading.attr="disabled"
                        class="ui-btn-primary">
                        <span wire:loading.remove wire:target="guardarEdicionAsignatura">Guardar cambios</span>
                        <span wire:loading wire:target="guardarEdicionAsignatura">Guardando...</span>
                    </button>
                </div>
            </div>
        </div>
    @endif

    {{-- MODAL DETALLE --}}
    @if ($modalDetalle)
        <div class="fixed inset-0 z-50 flex items-center justify-center p-4">
            <div class="ui-modal-backdrop"></div>

            <div class="ui-modal max-h-[92vh] w-full max-w-4xl overflow-y-auto ui-scrollbar">
                <div class="ui-modal-header flex items-start justify-between">
                    <div>
                        <h3 class="ui-title text-2xl font-black">Detalle de asignatura</h3>
                        <p class="ui-muted mt-1 text-sm">Información académica, uso institucional y análisis inteligente.
                        </p>
                    </div>

                    <button type="button" wire:click="cerrarModalDetalle" class="ui-icon-btn">
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>

                @php
                    $detalleUso = $detalleAsignatura['uso'] ?? [];
                    $detalleAnalisis = $detalleAsignatura['analisis'] ?? [];
                @endphp

                <div class="space-y-5 p-6">
                    <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
                        <div class="ui-card-soft p-4">
                            <p class="ui-muted text-xs font-black uppercase tracking-wider">Código</p>
                            <p class="ui-title mt-2 text-lg font-black">{{ $detalleAsignatura['codigo'] ?? '-' }}</p>
                        </div>

                        <div class="ui-card-soft p-4">
                            <p class="ui-muted text-xs font-black uppercase tracking-wider">Sigla</p>
                            <p class="mt-2 text-lg font-black" style="color: var(--ui-violet);">
                                {{ $detalleAsignatura['sigla'] ?? '-' }}</p>
                        </div>

                        <div class="ui-card-soft p-4">
                            <p class="ui-muted text-xs font-black uppercase tracking-wider">Horas</p>
                            <p class="ui-title mt-2 text-lg font-black">{{ $detalleAsignatura['horas'] ?? 0 }} h</p>
                        </div>

                        <div class="ui-card-soft p-4">
                            <p class="ui-muted text-xs font-black uppercase tracking-wider">Estado</p>
                            <p class="mt-2 text-lg font-black"
                                style="color: {{ ($detalleAsignatura['estado'] ?? '') === 'ACTIVO' ? 'var(--ui-primary)' : 'var(--ui-warning)' }};">
                                {{ $detalleAsignatura['estado'] ?? '-' }}
                            </p>
                        </div>
                    </div>

                    <div class="ui-card-soft p-5">
                        <p class="ui-muted text-xs font-black uppercase tracking-wider">Asignatura</p>
                        <h4 class="ui-title mt-2 text-2xl font-black">{{ $detalleAsignatura['nombre'] ?? '-' }}</h4>
                    </div>

                    <div class="grid gap-4 md:grid-cols-3">
                        <div class="rounded-3xl border p-5"
                            style="border-color: var(--ui-info-border); background: var(--ui-info-soft); color: var(--ui-info);">
                            <p class="text-sm font-black">Planes de asignatura</p>
                            <p class="mt-3 text-3xl font-black">{{ $detalleUso['planes'] ?? 0 }}</p>
                        </div>

                        <div class="rounded-3xl border p-5"
                            style="border-color: var(--ui-violet-border); background: var(--ui-violet-soft); color: var(--ui-violet);">
                            <p class="text-sm font-black">Calificaciones</p>
                            <p class="mt-3 text-3xl font-black">{{ $detalleUso['calificaciones'] ?? 0 }}</p>
                        </div>

                        <div class="rounded-3xl border p-5"
                            style="border-color: var(--ui-primary-border); background: var(--ui-primary-soft); color: var(--ui-primary);">
                            <p class="text-sm font-black">Horarios vinculados</p>
                            <p class="mt-3 text-3xl font-black">{{ $detalleUso['horarios'] ?? 0 }}</p>
                        </div>
                    </div>

                    <div class="ui-card-soft p-5">
                        <h4 class="ui-title text-lg font-black">Análisis inteligente</h4>

                        <div class="mt-4 grid gap-4 md:grid-cols-2">
                            <div>
                                <p class="ui-muted text-xs font-black uppercase tracking-wider">Área</p>
                                <p class="ui-title mt-1 text-sm font-bold">{{ $detalleAnalisis['area'] ?? 'No definida' }}
                                </p>
                            </div>

                            <div>
                                <p class="ui-muted text-xs font-black uppercase tracking-wider">Tipo</p>
                                <p class="ui-title mt-1 text-sm font-bold">{{ $detalleAnalisis['tipo'] ?? 'No definido' }}
                                </p>
                            </div>

                            <div>
                                <p class="ui-muted text-xs font-black uppercase tracking-wider">Nivel</p>
                                <p class="ui-title mt-1 text-sm font-bold">{{ $detalleAnalisis['nivel'] ?? 'No definido' }}
                                </p>
                            </div>

                            <div>
                                <p class="ui-muted text-xs font-black uppercase tracking-wider">Confianza</p>
                                <p class="mt-1 text-sm font-black" style="color: var(--ui-primary);">
                                    {{ $detalleAnalisis['confianza'] ?? 0 }}%</p>
                            </div>
                        </div>

                        @if (!empty($detalleAnalisis['descripcion']))
                            <p class="ui-muted mt-4 text-sm leading-6">{{ $detalleAnalisis['descripcion'] }}</p>
                        @endif

                        @if (!empty($detalleAnalisis['carreras_relacionadas']))
                            <div class="mt-4">
                                <p class="ui-muted text-xs font-black uppercase tracking-wider">Carreras relacionadas</p>
                                <div class="mt-2 flex flex-wrap gap-2">
                                    @foreach (array_slice($detalleAnalisis['carreras_relacionadas'], 0, 10) as $carrera)
                                        <span class="ui-badge-info">{{ $carrera }}</span>
                                    @endforeach
                                </div>
                            </div>
                        @endif
                    </div>

                    <div class="ui-alert-success">
                        <p class="font-black">Recomendación institucional</p>
                        <p class="mt-2 leading-6">
                            {{ $detalleAsignatura['recomendacion'] ?? 'Sin recomendación disponible.' }}</p>
                    </div>
                </div>
            </div>
        </div>
    @endif

    {{-- MODAL CATÁLOGO --}}
    @if ($modalCatalogo)
        <div class="fixed inset-0 z-50 flex items-center justify-center p-4">
            <div class="ui-modal-backdrop"></div>

            <div class="ui-modal max-h-[92vh] w-full max-w-6xl overflow-y-auto ui-scrollbar">
                <div class="ui-modal-header sticky top-0 z-10 flex items-start justify-between">
                    <div>
                        <h3 class="ui-title text-2xl font-black">Catálogo sugerido de asignaturas</h3>
                        <p class="ui-muted mt-1 text-sm">
                            Selecciona una asignatura base para cargarla en el formulario de registro.
                        </p>
                    </div>

                    <button type="button" wire:click="cerrarModalCatalogo" class="ui-icon-btn">
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>

                <div class="grid gap-4 p-6 md:grid-cols-2 xl:grid-cols-3">
                    @foreach ($catalogoInteligente as $item)
                        <article class="ui-card-soft p-5 transition hover:-translate-y-0.5">
                            <div class="flex items-start justify-between gap-3">
                                <div>
                                    <h4 class="ui-title font-black">{{ $item['nombre'] }}</h4>
                                    <p class="ui-muted mt-1 text-xs">{{ $item['area'] }}</p>
                                </div>

                                <span class="ui-badge-violet">{{ $item['sigla'] }}</span>
                            </div>

                            <div class="mt-4 flex flex-wrap gap-2">
                                <span class="ui-badge-info">{{ $item['horas'] }} h</span>
                                <span class="ui-badge-violet">{{ $item['tipo'] }}</span>
                                <span class="ui-badge-success">{{ $item['nivel'] }}</span>
                            </div>

                            <button type="button" wire:click="usarDesdeCatalogo('{{ $item['sigla'] }}')"
                                class="ui-btn-primary mt-5 w-full">
                                Usar esta asignatura
                            </button>
                        </article>
                    @endforeach
                </div>
            </div>
        </div>
    @endif
</div>