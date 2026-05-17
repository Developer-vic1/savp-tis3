<div
    class="space-y-6"
    x-data="{
        color(name) {
            return getComputedStyle(document.documentElement).getPropertyValue(name).trim();
        },

        alerta(icon, title, text, colorVar = '--ui-primary') {
            Swal.fire({
                icon,
                title,
                text,
                confirmButtonText: 'Entendido',
                confirmButtonColor: this.color(colorVar) || '#059669',
                background: this.color('--ui-surface') || '#ffffff',
                color: this.color('--ui-text') || '#0f172a'
            });
        },

        init() {
            window.addEventListener('paralelo-creado', event => {
                this.alerta('success', 'Paralelo registrado', event.detail.mensaje ?? 'El paralelo fue registrado correctamente.');
            });

            window.addEventListener('paralelo-actualizado', event => {
                this.alerta('success', 'Paralelo actualizado', event.detail.mensaje ?? 'El paralelo fue actualizado correctamente.');
            });

            window.addEventListener('paralelo-desactivado', event => {
                this.alerta('success', 'Paralelo desactivado', event.detail.mensaje ?? 'El paralelo fue desactivado correctamente.');
            });

            window.addEventListener('paralelo-reactivado', event => {
                this.alerta('success', 'Paralelo reactivado', event.detail.mensaje ?? 'El paralelo fue reactivado correctamente.');
            });

            window.addEventListener('advertencia-general', event => {
                this.alerta('warning', 'Revisión requerida', event.detail.mensaje ?? 'Revisa la información antes de continuar.', '--ui-warning');
            });

            window.addEventListener('duplicado-inactivo', event => {
                this.alerta('warning', 'Histórico recuperable', event.detail.mensaje ?? 'Ya existe un paralelo inactivo con ese nombre.', '--ui-warning');
            });

            window.addEventListener('error-general', event => {
                this.alerta('error', 'No se pudo completar la acción', event.detail.mensaje ?? 'Ocurrió un error inesperado.', '--ui-danger');
            });

            window.addEventListener('confirmar-desactivar-paralelo', event => {
                Swal.fire({
                    icon: event.detail.riesgo === 'ALTO' ? 'error' : 'warning',
                    title: event.detail.titulo ?? '¿Desactivar paralelo?',
                    text: event.detail.mensaje ?? 'Este paralelo será desactivado lógicamente.',
                    showCancelButton: true,
                    confirmButtonText: event.detail.riesgo === 'ALTO' ? 'Desactivar de todos modos' : 'Sí, desactivar',
                    cancelButtonText: 'Cancelar',
                    confirmButtonColor: event.detail.riesgo === 'ALTO' ? (this.color('--ui-danger') || '#dc2626') : (this.color('--ui-warning') || '#d97706'),
                    cancelButtonColor: this.color('--ui-muted') || '#64748b',
                    background: this.color('--ui-surface') || '#ffffff',
                    color: this.color('--ui-text') || '#0f172a',
                    reverseButtons: true
                }).then(result => {
                    if (result.isConfirmed) {
                        $wire.desactivarParalelo(event.detail.codigo);
                    }
                });
            });

            window.addEventListener('confirmar-reactivar-paralelo', event => {
                Swal.fire({
                    icon: 'question',
                    title: event.detail.titulo ?? '¿Reactivar paralelo?',
                    text: event.detail.mensaje ?? 'El paralelo volverá a estar disponible.',
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
                        $wire.reactivarParalelo(event.detail.codigo);
                    }
                });
            });
        }
    }"
>
    {{-- ENCABEZADO --}}
    <section class="ui-card overflow-hidden">
        <div
            class="relative p-6 lg:p-8"
            style="background:
                radial-gradient(circle at top left, var(--ui-primary-soft), transparent 34%),
                radial-gradient(circle at top right, var(--ui-violet-soft), transparent 32%),
                linear-gradient(135deg, var(--ui-surface), var(--ui-surface-soft));"
        >
            <div class="grid gap-6 lg:grid-cols-[1.35fr_.65fr] lg:items-center">
                <div class="space-y-5">
                    <div
                        class="inline-flex items-center gap-2 rounded-full border px-4 py-2 text-xs font-black uppercase tracking-[0.18em]"
                        style="border-color: var(--ui-primary-border); background: var(--ui-primary-soft); color: var(--ui-primary);"
                    >
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M18 18.72a9.094 9.094 0 0 0 3.741-.479 3 3 0 0 0-4.682-2.72m.941 3.198.001.031c0 .225-.012.447-.037.666A11.944 11.944 0 0 1 12 21c-2.17 0-4.207-.576-5.963-1.584A6.062 6.062 0 0 1 6 18.719m12 0a5.971 5.971 0 0 0-.941-3.197m0 0A5.995 5.995 0 0 0 12 12.75a5.995 5.995 0 0 0-5.058 2.772m0 0a5.971 5.971 0 0 0-.941 3.197" />
                        </svg>
                        Control académico recuperable
                    </div>

                    <div>
                        <h2 class="ui-title text-3xl font-black tracking-tight md:text-4xl">
                            Gestión de Paralelos
                        </h2>

                        <p class="ui-muted mt-3 max-w-4xl text-sm leading-7 md:text-base">
                            Administra los grupos académicos institucionales utilizados para organizar estudiantes,
                            cursos, turnos, planes de asignatura, especialidades y horarios, conservando trazabilidad
                            cuando un paralelo deja de utilizarse.
                        </p>
                    </div>

                    <div class="flex flex-col gap-3 sm:flex-row">
                        <button type="button" wire:click="abrirModalCrear" class="ui-btn-primary">
                            <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                            </svg>
                            Registrar paralelo
                        </button>

                        <button type="button" wire:click="abrirModalHistoricos" class="ui-btn-secondary">
                            <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6l3 3m6-3a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                            </svg>
                            Ver históricos
                        </button>

                        <button type="button" wire:click="abrirModalCatalogo" class="ui-btn-secondary">
                            <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M3 13.125C3 12.504 3.504 12 4.125 12h3.75c.621 0 1.125.504 1.125 1.125v6.75C9 20.496 8.496 21 7.875 21h-3.75A1.125 1.125 0 0 1 3 19.875v-6.75ZM9.75 8.625c0-.621.504-1.125 1.125-1.125h3.75c.621 0 1.125.504 1.125 1.125v11.25c0 .621-.504 1.125-1.125 1.125h-3.75a1.125 1.125 0 0 1-1.125-1.125V8.625ZM16.5 4.125C16.5 3.504 17.004 3 17.625 3h3.75C21.996 3 22.5 3.504 22.5 4.125v15.75c0 .621-.504 1.125-1.125 1.125h-3.75a1.125 1.125 0 0 1-1.125-1.125V4.125Z" />
                            </svg>
                            Distribución estudiantil
                        </button>
                    </div>
                </div>

                <div class="ui-card-soft p-5">
                    <div class="flex items-center gap-4">
                        <div
                            class="flex h-14 w-14 items-center justify-center rounded-2xl"
                            style="background: var(--ui-primary-soft); color: var(--ui-primary); border: 1px solid var(--ui-primary-border);"
                        >
                            <svg class="h-7 w-7" fill="none" stroke="currentColor" stroke-width="1.6" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M18 18.72a9.094 9.094 0 0 0 3.741-.479 3 3 0 0 0-4.682-2.72m.941 3.198.001.031A11.944 11.944 0 0 1 12 21c-2.17 0-4.207-.576-5.963-1.584A6.062 6.062 0 0 1 6 18.719m12 0a5.971 5.971 0 0 0-.941-3.197A5.995 5.995 0 0 0 12 12.75a5.995 5.995 0 0 0-5.058 2.772A5.971 5.971 0 0 0 6 18.719" />
                            </svg>
                        </div>

                        <div>
                            <p class="ui-title text-sm font-black">Organización institucional</p>
                            <p class="ui-muted mt-1 text-xs leading-5">
                                Paralelos activos, históricos recuperables y distribución estudiantil.
                            </p>
                        </div>
                    </div>

                    <div class="mt-5 grid grid-cols-2 gap-3">
                        <div class="rounded-2xl border p-3" style="border-color: var(--ui-primary-border); background: var(--ui-primary-soft);">
                            <p class="text-xs font-bold" style="color: var(--ui-primary);">Activos</p>
                            <p class="mt-1 text-xl font-black" style="color: var(--ui-primary);">{{ $resumen['activos'] ?? 0 }}</p>
                        </div>

                        <div class="rounded-2xl border p-3" style="border-color: var(--ui-warning-border); background: var(--ui-warning-soft);">
                            <p class="text-xs font-bold" style="color: var(--ui-warning);">Históricos</p>
                            <p class="mt-1 text-xl font-black" style="color: var(--ui-warning);">{{ $resumen['historicos'] ?? 0 }}</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    {{-- ALERTA --}}
    <section class="ui-alert-info">
        <div class="flex gap-3">
            <svg class="mt-0.5 h-5 w-5 flex-none" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="m11.25 11.25.041-.02a.75.75 0 0 1 1.063.852l-.708 2.836a.75.75 0 0 0 1.063.853l.041-.021M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Zm-9-3.75h.008v.008H12V8.25Z" />
            </svg>

            <p class="leading-6">
                Los paralelos no se eliminan físicamente. Si un paralelo deja de utilizarse, se desactiva para conservar
                su historial académico y puede reactivarse cuando la institución lo necesite.
            </p>
        </div>
    </section>

    {{-- RESUMEN --}}
    <section class="grid gap-4 sm:grid-cols-2 xl:grid-cols-6">
        <article class="ui-card ui-card-hover p-5">
            <p class="ui-muted text-xs font-black uppercase tracking-wider">Paralelos activos</p>
            <p class="ui-title mt-4 text-3xl font-black">{{ $resumen['activos'] ?? 0 }}</p>
            <p class="ui-muted mt-1 text-xs">Disponibles para planificación</p>
        </article>

        <article class="ui-card ui-card-hover p-5">
            <p class="ui-muted text-xs font-black uppercase tracking-wider">Históricos recuperables</p>
            <p class="mt-4 text-3xl font-black" style="color: var(--ui-warning);">{{ $resumen['historicos'] ?? 0 }}</p>
            <p class="ui-muted mt-1 text-xs">Pueden reactivarse</p>
        </article>

        <article class="ui-card ui-card-hover p-5">
            <p class="ui-muted text-xs font-black uppercase tracking-wider">Estudiantes asignados</p>
            <p class="ui-title mt-4 text-3xl font-black">{{ $resumen['estudiantes'] ?? 0 }}</p>
            <p class="ui-muted mt-1 text-xs">En todos los paralelos</p>
        </article>

        <article class="ui-card ui-card-hover p-5">
            <p class="ui-muted text-xs font-black uppercase tracking-wider">Con estudiantes</p>
            <p class="mt-4 text-3xl font-black" style="color: var(--ui-primary);">{{ $resumen['con_estudiantes'] ?? 0 }}</p>
            <p class="ui-muted mt-1 text-xs">Al menos un estudiante</p>
        </article>

        <article class="ui-card ui-card-hover p-5">
            <p class="ui-muted text-xs font-black uppercase tracking-wider">Sin uso actual</p>
            <p class="mt-4 text-3xl font-black" style="color: var(--ui-warning);">{{ $resumen['sin_uso'] ?? 0 }}</p>
            <p class="ui-muted mt-1 text-xs">Revisar desactivación</p>
        </article>

        <article class="ui-card ui-card-hover p-5">
            <p class="ui-muted text-xs font-black uppercase tracking-wider">Planes vinculados</p>
            <p class="mt-4 text-3xl font-black" style="color: var(--ui-violet);">{{ $resumen['planes_vinculados'] ?? 0 }}</p>
            <p class="ui-muted mt-1 text-xs">Asignatura y especialidad</p>
        </article>
    </section>

    {{-- DISTRIBUCIÓN Y RECOMENDACIONES --}}
    <section class="grid gap-4 xl:grid-cols-[1.4fr_.6fr]">
        <article class="ui-card p-5">
            <h3 class="ui-title text-lg font-black">Estado de organización académica</h3>
            <p class="ui-muted mt-2 text-sm leading-6">
                La institución puede revisar qué paralelos concentran estudiantes, cuáles están sin uso y cuáles
                deberían mantenerse activos por planificación académica.
            </p>

            <div class="mt-5 space-y-3">
                @forelse ($distribucion as $item)
                    @php
                        $maximo = max(collect($distribucion)->max('valor') ?: 1, 1);
                        $porcentaje = min(100, round(($item['valor'] / $maximo) * 100));
                    @endphp

                    <div>
                        <div class="mb-1 flex items-center justify-between gap-3">
                            <span class="ui-title text-sm font-black">{{ $item['nombre'] }}</span>
                            <span class="ui-muted text-xs">{{ $item['texto'] }}</span>
                        </div>

                        <div class="h-2.5 overflow-hidden rounded-full" style="background: var(--ui-surface-soft);">
                            <div
                                class="h-full rounded-full"
                                style="width: {{ $porcentaje }}%; background: var(--ui-primary);"
                            ></div>
                        </div>
                    </div>
                @empty
                    <p class="ui-muted text-sm">Aún no existen estudiantes distribuidos por paralelo.</p>
                @endforelse
            </div>
        </article>

        <article class="ui-card p-5">
            <h3 class="ui-title text-lg font-black">Recomendaciones del sistema</h3>

            <div class="mt-4 space-y-3">
                @foreach ($recomendaciones as $recomendacion)
                    @php
                        $clase = match ($recomendacion['tipo'] ?? 'info') {
                            'success' => 'ui-alert-success',
                            'warning' => 'ui-alert-warning',
                            'danger' => 'ui-alert-danger',
                            default => 'ui-alert-info',
                        };
                    @endphp

                    <div class="{{ $clase }}">
                        <p class="font-black">{{ $recomendacion['titulo'] }}</p>
                        <p class="mt-1 text-sm leading-6">{{ $recomendacion['mensaje'] }}</p>
                    </div>
                @endforeach
            </div>
        </article>
    </section>

    {{-- FILTROS --}}
    <section class="ui-card p-4">
        <div class="grid gap-3 lg:grid-cols-[1.2fr_.65fr_.9fr_.65fr_.55fr_auto]">
            <div>
                <label class="ui-label">Buscar</label>
                <input
                    type="search"
                    wire:model.live.debounce.400ms="search"
                    class="ui-input"
                    placeholder="Buscar paralelo por nombre..."
                >
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
                <label class="ui-label">Impacto</label>
                <select wire:model.live="impacto" class="ui-select">
                    @foreach ($opcionesImpacto as $valor => $texto)
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
        <div class="flex flex-col gap-3 border-b p-5 md:flex-row md:items-center md:justify-between" style="border-color: var(--ui-border);">
            <div>
                <h3 class="ui-title text-lg font-black">Paralelos institucionales</h3>
                <p class="ui-muted mt-1 text-sm">
                    No se muestran códigos internos. La gestión se enfoca en disponibilidad, estudiantes, uso académico e impacto.
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
                        <th><button type="button" wire:click="ordenarPor('nom_par')" class="hover:underline">Paralelo</button></th>
                        <th>Disponibilidad</th>
                        <th>Estudiantes</th>
                        <th>Uso académico</th>
                        <th>Impacto</th>
                        <th><button type="button" wire:click="ordenarPor('est_par')" class="hover:underline">Estado</button></th>
                        <th class="text-right">Acciones</th>
                    </tr>
                </thead>

                <tbody>
                    @forelse ($paralelos as $paralelo)
                        @php
                            $uso = $paralelo->uso_academico ?? [];
                            $impactoParalelo = $paralelo->impacto_academico ?? [];
                            $disponibilidad = $paralelo->disponibilidad ?? [];

                            $impactoBadge = match ($impactoParalelo['nivel'] ?? '') {
                                'ALTO' => 'ui-badge-success',
                                'MEDIO' => 'ui-badge-violet',
                                'BAJO' => 'ui-badge-info',
                                'HISTORICO' => 'ui-badge-warning',
                                default => 'ui-badge-muted',
                            };

                            $disponibilidadBadge = match ($disponibilidad['estado'] ?? '') {
                                'DISPONIBLE' => 'ui-badge-success',
                                'HISTORICO' => 'ui-badge-warning',
                                'SIN_USO' => 'ui-badge-warning',
                                default => 'ui-badge-muted',
                            };
                        @endphp

                        <tr wire:key="paralelo-{{ $paralelo->cod_par }}">
                            <td>
                                <div class="flex items-center gap-3">
                                    <div
                                        class="flex h-11 w-11 items-center justify-center rounded-2xl text-sm font-black"
                                        style="background: var(--ui-primary-soft); color: var(--ui-primary); border: 1px solid var(--ui-primary-border);"
                                    >
                                        {{ mb_substr($paralelo->nom_par, 0, 2) }}
                                    </div>

                                    <div>
                                        <p class="ui-title font-black">Paralelo {{ $paralelo->nom_par }}</p>
                                        <p class="ui-muted text-xs">Grupo académico institucional</p>
                                    </div>
                                </div>
                            </td>

                            <td>
                                <span class="{{ $disponibilidadBadge }}">
                                    {{ $disponibilidad['texto'] ?? 'Sin información' }}
                                </span>
                            </td>

                            <td>
                                <p class="ui-title text-sm font-black">{{ $uso['estudiantes'] ?? 0 }} estudiantes</p>
                                <p class="ui-muted text-xs">{{ $uso['cursos'] ?? 0 }} cursos vinculados</p>
                            </td>

                            <td>
                                <p class="ui-title text-sm font-bold">{{ $uso['texto'] ?? 'Sin uso académico' }}</p>
                            </td>

                            <td>
                                <span class="{{ $impactoBadge }}">{{ $impactoParalelo['texto'] ?? 'Sin uso' }}</span>
                            </td>

                            <td>
                                @if ($paralelo->est_par === 'ACTIVO')
                                    <span class="ui-badge-success">Activo</span>
                                @else
                                    <span class="ui-badge-warning">Inactivo</span>
                                @endif
                            </td>

                            <td class="text-right">
                                <div class="inline-flex items-center gap-1.5">
                                    <button type="button" wire:click="abrirModalDetalle('{{ $paralelo->cod_par }}')" class="ui-icon-btn" title="Ver detalle">
                                        <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="1.7" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 0 1 0-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178Z" />
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" />
                                        </svg>
                                    </button>

                                    <button type="button" wire:click="abrirModalEditar('{{ $paralelo->cod_par }}')" class="ui-icon-btn" title="Editar">
                                        <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="1.7" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="m16.862 4.487 1.687-1.688a1.875 1.875 0 1 1 2.652 2.652L10.582 16.07a4.5 4.5 0 0 1-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 0 1 1.13-1.897l8.932-8.931Zm0 0L19.5 7.125" />
                                        </svg>
                                    </button>

                                    @if ($paralelo->est_par === 'ACTIVO')
                                        <button type="button" wire:click="solicitarDesactivar('{{ $paralelo->cod_par }}')" class="ui-icon-btn" style="color: var(--ui-warning);" title="Desactivar">
                                            <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="1.7" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M18.364 18.364A9 9 0 0 0 5.636 5.636m12.728 12.728A9 9 0 0 1 5.636 5.636m12.728 12.728L5.636 5.636" />
                                            </svg>
                                        </button>
                                    @else
                                        <button type="button" wire:click="solicitarReactivar('{{ $paralelo->cod_par }}')" class="ui-icon-btn" style="color: var(--ui-primary);" title="Reactivar">
                                            <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="1.7" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                                            </svg>
                                        </button>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="py-16 text-center">
                                <div class="mx-auto max-w-md">
                                    <h4 class="ui-title text-lg font-black">No se encontraron paralelos</h4>
                                    <p class="ui-muted mt-2 text-sm leading-6">
                                        Ajusta los filtros o registra un nuevo paralelo institucional.
                                    </p>
                                    <button type="button" wire:click="abrirModalCrear" class="ui-btn-primary mt-5">
                                        Registrar paralelo
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($paralelos->hasPages())
            <div class="border-t px-5 py-4" style="border-color: var(--ui-border);">
                {{ $paralelos->links() }}
            </div>
        @endif
    </section>

    {{-- MODAL CREAR --}}
    @if ($modalCrear)
        <div class="fixed inset-0 z-50 flex items-center justify-center p-4">
            <div class="ui-modal-backdrop"></div>

            <div class="ui-modal max-h-[92vh] w-full max-w-5xl overflow-y-auto ui-scrollbar">
                <div class="ui-modal-header flex items-start justify-between">
                    <div>
                        <h3 class="ui-title text-2xl font-black">Registrar paralelo</h3>
                        <p class="ui-muted mt-1 text-sm">
                            Registra solo el grupo académico. No incluyas curso, turno, gestión, aula ni docente.
                        </p>
                    </div>

                    <button type="button" wire:click="cerrarModalCrear" class="ui-icon-btn">
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>

                <div class="grid gap-6 p-6 lg:grid-cols-[.9fr_1.1fr]">
                    <div class="space-y-5">
                        <div>
                            <label class="ui-label">Nombre del paralelo</label>
                            <input
                                type="text"
                                wire:model.live.debounce.400ms="form.nom_par"
                                class="ui-input"
                                placeholder="Ej. A, B, C o Único"
                            >
                            @error('form.nom_par')
                                <p class="ui-error">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label class="ui-label">Estado inicial</label>
                            <select wire:model.live="form.est_par" class="ui-select">
                                @foreach ($estadosDisponibles as $valor => $texto)
                                    <option value="{{ $valor }}">{{ $texto }}</option>
                                @endforeach
                            </select>
                            @error('form.est_par')
                                <p class="ui-error">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="ui-alert-info">
                            Registra solo el grupo académico. Ejemplos válidos: A, B, C, Único. Ejemplos no válidos:
                            1ro A, A mañana, Paralelo 2026 o aula 3.
                        </div>
                    </div>

                    @php
                        $estadoAnalisis = $analisisCrear['estado_inteligente'] ?? '';
                        $panelClase = match ($estadoAnalisis) {
                            'VALIDO' => 'ui-alert-success',
                            'REDACTABLE' => 'ui-alert-info',
                            'DUPLICADO_ACTIVO' => 'ui-alert-danger',
                            'DUPLICADO_INACTIVO' => 'ui-alert-warning',
                            'REQUIERE_REVISION' => 'ui-alert-warning',
                            'BLOQUEADO' => 'ui-alert-danger',
                            default => 'ui-card-soft',
                        };
                    @endphp

                    <div class="space-y-5">
                        <div class="{{ $panelClase }}">
                            <div class="flex items-start justify-between gap-4">
                                <div>
                                    <p class="text-xs font-black uppercase tracking-[0.16em]">Vista previa inteligente</p>
                                    <h4 class="mt-2 text-xl font-black">
                                        @if ($estadoAnalisis === 'DUPLICADO_INACTIVO')
                                            Histórico recuperable
                                        @elseif ($estadoAnalisis === 'DUPLICADO_ACTIVO')
                                            Duplicado activo
                                        @elseif ($estadoAnalisis === 'BLOQUEADO')
                                            Registro bloqueado
                                        @elseif ($estadoAnalisis === 'REDACTABLE')
                                            Redacción sugerida
                                        @elseif ($estadoAnalisis === 'VALIDO')
                                            Paralelo válido
                                        @else
                                            Revisión académica
                                        @endif
                                    </h4>
                                </div>

                                <div class="rounded-2xl px-4 py-2 text-right" style="background: rgba(0,0,0,.08);">
                                    <p class="text-xs font-bold opacity-80">Confianza</p>
                                    <p class="text-2xl font-black">{{ $analisisCrear['confianza'] ?? 0 }}%</p>
                                </div>
                            </div>

                            <p class="mt-4 text-sm leading-6">
                                {{ $analisisCrear['mensaje'] ?? 'Escribe un paralelo para activar el análisis.' }}
                            </p>
                        </div>

                        <div class="grid gap-4 sm:grid-cols-2">
                            <div class="ui-card-soft p-4">
                                <p class="ui-muted text-xs font-black uppercase tracking-wider">Paralelo sugerido</p>
                                <p class="ui-title mt-2 text-xl font-black">
                                    {{ $analisisCrear['nombre_sugerido'] ?: 'Pendiente' }}
                                </p>
                            </div>

                            <div class="ui-card-soft p-4">
                                <p class="ui-muted text-xs font-black uppercase tracking-wider">Resultado</p>
                                <p class="mt-2 text-sm font-black" style="color: var(--ui-primary);">
                                    {{ ($analisisCrear['puede_crear'] ?? false) ? 'Puede registrarse' : (($analisisCrear['puede_reactivar'] ?? false) ? 'Puede reactivarse' : 'No puede registrarse') }}
                                </p>
                            </div>
                        </div>
                        @if (! $puedeGuardarCrear && !empty($bloqueoCrearMensaje))
                            <div class="ui-alert-danger">
                                <p class="font-black">Registro bloqueado</p>
                                <p class="mt-2 text-sm leading-6">
                                    {{ $bloqueoCrearMensaje }}
                                </p>
                            </div>
                        @endif
                        @if (($analisisCrear['puede_reactivar'] ?? false) && !empty($analisisCrear['nombre_existente']))
                            <div class="ui-alert-warning">
                                <p class="font-black">Paralelo encontrado: {{ $analisisCrear['nombre_existente'] }}</p>
                                <p class="mt-2 text-sm leading-6">
                                    Estado: {{ $analisisCrear['estado_existente'] ?? 'INACTIVO' }}.
                                    @if (!empty($analisisCrear['ultima_bitacora']['fecha']))
                                        Última desactivación: {{ $analisisCrear['ultima_bitacora']['fecha'] }}.
                                    @endif
                                </p>
                                <button type="button" wire:click="reactivarExistenteDesdeAnalisisCrear" class="ui-btn-primary mt-4">
                                    Reactivar paralelo existente
                                </button>
                            </div>
                        @endif

                        @if (!empty($analisisCrear['advertencias']))
                            <div class="ui-alert-warning">
                                <p class="font-black">Advertencias</p>
                                <ul class="mt-2 space-y-1 text-sm leading-6">
                                    @foreach ($analisisCrear['advertencias'] as $advertencia)
                                        <li>{{ $advertencia }}</li>
                                    @endforeach
                                </ul>
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

                    <button
                        type="button"
                        wire:click="guardarParalelo"
                        wire:loading.attr="disabled"
                        @disabled(! $puedeGuardarCrear)
                        class="ui-btn-primary {{ ! $puedeGuardarCrear ? 'cursor-not-allowed opacity-50 grayscale' : '' }}"
                        title="{{ ! $puedeGuardarCrear ? ($bloqueoCrearMensaje ?? 'Completa una entrada válida para registrar el paralelo.') : 'Guardar paralelo' }}"
                    >
                        <span wire:loading.remove wire:target="guardarParalelo">
                            {{ $puedeGuardarCrear ? 'Guardar paralelo' : 'Registro bloqueado' }}
                        </span>
                        <span wire:loading wire:target="guardarParalelo">Guardando...</span>
                    </button>
                </div>
            </div>
        </div>
    @endif

    {{-- MODAL EDITAR --}}
    @if ($modalEditar)
        <div class="fixed inset-0 z-50 flex items-center justify-center p-4">
            <div class="ui-modal-backdrop"></div>

            <div class="ui-modal max-h-[92vh] w-full max-w-5xl overflow-y-auto ui-scrollbar">
                <div class="ui-modal-header flex items-start justify-between">
                    <div>
                        <h3 class="ui-title text-2xl font-black">Editar paralelo</h3>
                        <p class="ui-muted mt-1 text-sm">
                            Si tiene estudiantes o planificación, solo se permiten correcciones menores.
                        </p>
                    </div>

                    <button type="button" wire:click="cerrarModalEditar" class="ui-icon-btn">
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>

                <div class="grid gap-6 p-6 lg:grid-cols-[.9fr_1.1fr]">
                    <div class="space-y-5">
                        <div>
                            <label class="ui-label">Nombre del paralelo</label>
                            <input type="text" wire:model.live.debounce.400ms="formEditar.nom_par" class="ui-input">
                            @error('formEditar.nom_par')
                                <p class="ui-error">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label class="ui-label">Estado</label>
                            <select wire:model.live="formEditar.est_par" class="ui-select">
                                @foreach ($estadosDisponibles as $valor => $texto)
                                    <option value="{{ $valor }}">{{ $texto }}</option>
                                @endforeach
                            </select>
                            @error('formEditar.est_par')
                                <p class="ui-error">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="ui-alert-warning">
                            Cambiar A por B no es una corrección menor si ya existe historial académico. Usa edición solo
                            para normalizar nombres, no para cambiar la identidad del grupo.
                        </div>
                    </div>

                    @php
                        $estadoAnalisisEditar = $analisisEditar['estado_inteligente'] ?? '';
                        $panelEditar = match ($estadoAnalisisEditar) {
                            'VALIDO' => 'ui-alert-success',
                            'REDACTABLE' => 'ui-alert-info',
                            'DUPLICADO_ACTIVO' => 'ui-alert-danger',
                            'DUPLICADO_INACTIVO' => 'ui-alert-warning',
                            'REQUIERE_REVISION' => 'ui-alert-warning',
                            'BLOQUEADO' => 'ui-alert-danger',
                            default => 'ui-card-soft',
                        };
                    @endphp

                    <div class="space-y-5">
                        <div class="{{ $panelEditar }}">
                            <p class="text-xs font-black uppercase tracking-[0.16em]">Análisis inteligente</p>
                            <h4 class="mt-2 text-xl font-black">{{ $analisisEditar['nombre_sugerido'] ?: 'Pendiente' }}</h4>
                            <p class="mt-3 text-sm leading-6">{{ $analisisEditar['mensaje'] ?? 'Edita el paralelo para analizarlo.' }}</p>
                        </div>

                        <div class="grid gap-4 sm:grid-cols-2">
                            <div class="ui-card-soft p-4">
                                <p class="ui-muted text-xs font-black uppercase tracking-wider">Sugerencia</p>
                                <p class="ui-title mt-2 text-xl font-black">{{ $analisisEditar['nombre_sugerido'] ?: 'Pendiente' }}</p>
                            </div>

                            <div class="ui-card-soft p-4">
                                <p class="ui-muted text-xs font-black uppercase tracking-wider">Confianza</p>
                                <p class="mt-2 text-xl font-black" style="color: var(--ui-primary);">{{ $analisisEditar['confianza'] ?? 0 }}%</p>
                            </div>
                        </div>

                        @if (!empty($analisisEditar['advertencias']))
                            <div class="ui-alert-warning">
                                <p class="font-black">Advertencias</p>
                                <ul class="mt-2 space-y-1 text-sm leading-6">
                                    @foreach ($analisisEditar['advertencias'] as $advertencia)
                                        <li>{{ $advertencia }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif
                    </div>
                </div>

                <div class="ui-modal-footer flex flex-col-reverse gap-3 sm:flex-row sm:justify-end">
                    <button type="button" wire:click="cerrarModalEditar" class="ui-btn-secondary">Cancelar</button>
                    <button type="button" wire:click="usarSugerenciaEditar" class="ui-btn-secondary">Usar sugerencia</button>
                    <button type="button" wire:click="guardarEdicionParalelo" wire:loading.attr="disabled" class="ui-btn-primary">
                        <span wire:loading.remove wire:target="guardarEdicionParalelo">Guardar cambios</span>
                        <span wire:loading wire:target="guardarEdicionParalelo">Guardando...</span>
                    </button>
                </div>
            </div>
        </div>
    @endif

    {{-- MODAL DETALLE --}}
    @if ($modalDetalle)
        <div class="fixed inset-0 z-50 flex items-center justify-center p-4">
            <div class="ui-modal-backdrop"></div>

            <div class="ui-modal max-h-[92vh] w-full max-w-5xl overflow-y-auto ui-scrollbar">
                <div class="ui-modal-header flex items-start justify-between">
                    <div>
                        <h3 class="ui-title text-2xl font-black">Detalle del Paralelo {{ $detalleParalelo['nombre'] ?? '' }}</h3>
                        <p class="ui-muted mt-1 text-sm">Información administrativa, estudiantes, uso académico y recomendación institucional.</p>
                    </div>

                    <button type="button" wire:click="cerrarModalDetalle" class="ui-icon-btn">
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>

                @php
                    $detalleUso = $detalleParalelo['uso'] ?? [];
                    $detalleDisponibilidad = $detalleParalelo['disponibilidad'] ?? [];
                    $detalleImpacto = $detalleParalelo['impacto'] ?? [];
                    $detalleBitacora = $detalleParalelo['ultima_bitacora'] ?? null;
                    $detalleCursos = $detalleParalelo['distribucion_cursos'] ?? [];
                @endphp

                <div class="space-y-5 p-6">
                    <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
                        <div class="ui-card-soft p-4">
                            <p class="ui-muted text-xs font-black uppercase tracking-wider">Estado</p>
                            <p class="mt-2 text-lg font-black" style="color: {{ ($detalleParalelo['estado'] ?? '') === 'ACTIVO' ? 'var(--ui-primary)' : 'var(--ui-warning)' }};">
                                {{ $detalleParalelo['estado'] ?? '-' }}
                            </p>
                        </div>

                        <div class="ui-card-soft p-4">
                            <p class="ui-muted text-xs font-black uppercase tracking-wider">Disponibilidad</p>
                            <p class="ui-title mt-2 text-lg font-black">{{ $detalleDisponibilidad['texto'] ?? '-' }}</p>
                        </div>

                        <div class="ui-card-soft p-4">
                            <p class="ui-muted text-xs font-black uppercase tracking-wider">Estudiantes</p>
                            <p class="ui-title mt-2 text-lg font-black">{{ $detalleUso['estudiantes'] ?? 0 }}</p>
                        </div>

                        <div class="ui-card-soft p-4">
                            <p class="ui-muted text-xs font-black uppercase tracking-wider">Impacto</p>
                            <p class="ui-title mt-2 text-lg font-black">{{ $detalleImpacto['texto'] ?? '-' }}</p>
                        </div>
                    </div>

                    <div class="grid gap-4 md:grid-cols-3">
                        <div class="ui-card-soft p-5">
                            <p class="ui-muted text-xs font-black uppercase tracking-wider">Cursos vinculados</p>
                            <p class="ui-title mt-3 text-3xl font-black">{{ $detalleUso['cursos'] ?? 0 }}</p>
                        </div>

                        <div class="ui-card-soft p-5">
                            <p class="ui-muted text-xs font-black uppercase tracking-wider">Planes de asignatura</p>
                            <p class="ui-title mt-3 text-3xl font-black">{{ $detalleUso['planes_asignatura'] ?? 0 }}</p>
                        </div>

                        <div class="ui-card-soft p-5">
                            <p class="ui-muted text-xs font-black uppercase tracking-wider">Horarios vinculados</p>
                            <p class="ui-title mt-3 text-3xl font-black">{{ $detalleUso['horarios'] ?? 0 }}</p>
                        </div>
                    </div>

                    <div class="ui-card-soft p-5">
                        <h4 class="ui-title text-lg font-black">Distribución estudiantil</h4>

                        <div class="mt-4 space-y-3">
                            @forelse ($detalleCursos as $curso)
                                <div class="flex items-center justify-between gap-4">
                                    <span class="ui-title text-sm font-bold">{{ $curso['curso'] }}</span>
                                    <span class="ui-muted text-sm">{{ $curso['estudiantes'] }} estudiantes</span>
                                </div>
                            @empty
                                <p class="ui-muted text-sm">No existe distribución por curso registrada para este paralelo.</p>
                            @endforelse
                        </div>
                    </div>

                    @if ($detalleBitacora)
                        <div class="ui-card-soft p-5">
                            <h4 class="ui-title text-lg font-black">Última acción registrada</h4>
                            <p class="ui-muted mt-2 text-sm leading-6">
                                {{ $detalleBitacora['descripcion'] ?? 'Acción registrada.' }}
                            </p>
                            <p class="ui-muted mt-2 text-xs">
                                Fecha: {{ $detalleBitacora['fecha'] ?? '-' }} · Acción: {{ $detalleBitacora['accion'] ?? '-' }}
                            </p>
                        </div>
                    @endif

                    <div class="ui-alert-success">
                        <p class="font-black">Recomendación institucional</p>
                        <p class="mt-2 leading-6">{{ $detalleParalelo['recomendacion'] ?? 'Sin recomendación disponible.' }}</p>
                    </div>
                </div>
            </div>
        </div>
    @endif

    {{-- MODAL CATÁLOGO --}}
    @if ($modalCatalogo)
        <div class="fixed inset-0 z-50 flex items-center justify-center p-4">
            <div class="ui-modal-backdrop"></div>

            <div class="ui-modal max-h-[92vh] w-full max-w-5xl overflow-y-auto ui-scrollbar">
                <div class="ui-modal-header flex items-start justify-between">
                    <div>
                        <h3 class="ui-title text-2xl font-black">Distribución estudiantil y catálogo sugerido</h3>
                        <p class="ui-muted mt-1 text-sm">Usa paralelos simples y recuperables para mantener la organización académica limpia.</p>
                    </div>

                    <button type="button" wire:click="cerrarModalCatalogo" class="ui-icon-btn">
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>

                <div class="grid gap-4 p-6 md:grid-cols-2 xl:grid-cols-3">
                    @foreach ($catalogoSugerido as $item)
                        <article class="ui-card-soft p-5">
                            <h4 class="ui-title text-xl font-black">Paralelo {{ $item['nombre'] }}</h4>
                            <p class="ui-muted mt-2 text-sm leading-6">{{ $item['descripcion'] }}</p>

                            <div class="mt-4 flex flex-wrap gap-2">
                                <span class="ui-badge-info">{{ $item['tipo'] }}</span>
                                @if ($item['recomendado'])
                                    <span class="ui-badge-success">Recomendado</span>
                                @else
                                    <span class="ui-badge-warning">Uso excepcional</span>
                                @endif
                            </div>

                            <button type="button" wire:click="usarDesdeCatalogo('{{ $item['nombre'] }}')" class="ui-btn-primary mt-5 w-full">
                                Usar este paralelo
                            </button>
                        </article>
                    @endforeach
                </div>
            </div>
        </div>
    @endif

    {{-- MODAL HISTÓRICOS --}}
    @if ($modalHistoricos)
        <div class="fixed inset-0 z-50 flex items-center justify-center p-4">
            <div class="ui-modal-backdrop"></div>

            <div class="ui-modal max-h-[92vh] w-full max-w-4xl overflow-y-auto ui-scrollbar">
                <div class="ui-modal-header flex items-start justify-between">
                    <div>
                        <h3 class="ui-title text-2xl font-black">Históricos recuperables</h3>
                        <p class="ui-muted mt-1 text-sm">Paralelos inactivos que conservan trazabilidad académica.</p>
                    </div>

                    <button type="button" wire:click="cerrarModalHistoricos" class="ui-icon-btn">
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>

                <div class="space-y-3 p-6">
                    @forelse ($historicos as $historico)
                        <div class="ui-card-soft p-5">
                            <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                                <div>
                                    <h4 class="ui-title font-black">Paralelo {{ $historico['nombre'] }}</h4>
                                    <p class="ui-muted mt-1 text-sm">
                                        {{ $historico['uso']['texto'] ?? 'Historial conservado' }}
                                    </p>

                                    @if (!empty($historico['ultima_bitacora']['fecha']))
                                        <p class="ui-muted mt-1 text-xs">
                                            Última desactivación: {{ $historico['ultima_bitacora']['fecha'] }}
                                        </p>
                                    @endif
                                </div>

                                <button type="button" wire:click="solicitarReactivar('{{ $historico['codigo'] }}')" class="ui-btn-primary">
                                    Reactivar
                                </button>
                            </div>
                        </div>
                    @empty
                        <p class="ui-muted text-sm">No existen paralelos históricos recuperables.</p>
                    @endforelse
                </div>
            </div>
        </div>
    @endif
</div>