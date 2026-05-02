<div x-data x-on:persona-creada.window="
        Swal.fire({
            icon: 'success',
            title: 'Persona registrada',
            text: 'El registro de persona fue creado correctamente.',
            confirmButtonText: 'Entendido',
            confirmButtonColor: '#059669'
        });
    " x-on:persona-actualizada.window="
        Swal.fire({
            icon: 'success',
            title: 'Persona actualizada',
            text: 'Los datos de la persona fueron actualizados correctamente.',
            confirmButtonText: 'Entendido',
            confirmButtonColor: '#059669'
        });
    " x-on:persona-desactivada.window="
        Swal.fire({
            icon: 'success',
            title: 'Persona desactivada',
            text: 'El registro fue desactivado correctamente.',
            confirmButtonText: 'Entendido',
            confirmButtonColor: '#059669'
        });
    " x-on:persona-reactivada.window="
        Swal.fire({
            icon: 'success',
            title: 'Persona reactivada',
            text: 'El registro fue reactivado correctamente.',
            confirmButtonText: 'Entendido',
            confirmButtonColor: '#059669'
        });
    " class="space-y-6">

    <style>
        select {
            -webkit-appearance: none;
            -moz-appearance: none;
            appearance: none;
            background-image: none !important;
        }

        select::-ms-expand {
            display: none;
        }

        [x-cloak] {
            display: none !important;
        }
    </style>

    {{-- RESUMEN SUPERIOR --}}
    <section class="grid gap-4 xl:grid-cols-12">
        <div class="xl:col-span-8">
            <div class="ui-card rounded-[2rem] p-5">
                <div class="flex flex-col gap-5 md:flex-row md:items-center md:justify-between">
                    <div>
                        <div class="inline-flex items-center gap-2 rounded-full px-3 py-1 ring-1"
                            style="background: var(--ui-primary-soft); color: var(--ui-primary); --tw-ring-color: var(--ui-primary-border);">
                            <span class="h-2 w-2 rounded-full" style="background: var(--ui-primary);"></span>

                            <p class="text-xs font-semibold uppercase tracking-[0.16em]">
                                Registro de personas
                            </p>
                        </div>

                        <h3 class="mt-3 text-5xl font-black tracking-tight" style="color: var(--ui-text);">
                            {{ $totalPersonas ?? 0 }}
                        </h3>

                        <p class="mt-2 text-sm" style="color: var(--ui-muted);">
                            Personas registradas en el sistema institucional.
                        </p>
                    </div>

                    <div class="grid flex-1 gap-3 sm:grid-cols-3">
                        <div class="rounded-2xl px-4 py-4 ring-1"
                            style="background: var(--ui-primary-soft); --tw-ring-color: var(--ui-primary-border);">
                            <div class="flex items-center gap-2">
                                <svg class="h-4 w-4" style="color: var(--ui-primary);" fill="none" stroke="currentColor"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"
                                        d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                                </svg>

                                <p class="text-xs font-semibold uppercase tracking-[0.14em]"
                                    style="color: var(--ui-primary);">
                                    Activas
                                </p>
                            </div>

                            <p class="mt-2 text-2xl font-black" style="color: var(--ui-text);">
                                {{ $totalActivas ?? 0 }}
                            </p>
                        </div>

                        <div class="rounded-2xl px-4 py-4 ring-1"
                            style="background: var(--ui-danger-soft); --tw-ring-color: var(--ui-danger-border);">
                            <div class="flex items-center gap-2">
                                <svg class="h-4 w-4" style="color: var(--ui-danger);" fill="none" stroke="currentColor"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"
                                        d="M18 12H6m15 0a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                                </svg>

                                <p class="text-xs font-semibold uppercase tracking-[0.14em]"
                                    style="color: var(--ui-danger);">
                                    Inactivas
                                </p>
                            </div>

                            <p class="mt-2 text-2xl font-black" style="color: var(--ui-text);">
                                {{ $totalInactivas ?? 0 }}
                            </p>
                        </div>

                        <div class="rounded-2xl px-4 py-4 ring-1"
                            style="background: var(--ui-info-soft); --tw-ring-color: var(--ui-info-border);">
                            <div class="flex items-center gap-2">
                                <svg class="h-4 w-4" style="color: var(--ui-info);" fill="none" stroke="currentColor"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"
                                        d="M15 9h3.75M15 12h3.75M15 15h3.75M4.5 19.5h15A1.5 1.5 0 0 0 21 18V6a1.5 1.5 0 0 0-1.5-1.5h-15A1.5 1.5 0 0 0 3 6v12a1.5 1.5 0 0 0 1.5 1.5Z" />
                                </svg>

                                <p class="text-xs font-semibold uppercase tracking-[0.14em]"
                                    style="color: var(--ui-info);">
                                    Sin usuario
                                </p>
                            </div>

                            <p class="mt-2 text-2xl font-black" style="color: var(--ui-text);">
                                {{ $totalSinUsuario ?? 0 }}
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="xl:col-span-4">
            <div class="ui-card rounded-[2rem] p-5">
                <div class="flex h-full flex-col justify-center">
                    <button type="button" wire:click="abrirModalCrear" wire:loading.attr="disabled"
                        wire:target="abrirModalCrear"
                        class="ui-btn-primary w-full disabled:cursor-wait disabled:opacity-60">

                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24"
                            stroke="currentColor" stroke-width="1.8">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                        </svg>

                        <span>Registrar persona</span>
                    </button>

                    <p class="mt-3 text-center text-xs leading-5" style="color: var(--ui-muted);">
                        Registra datos personales antes de crear una cuenta de usuario.
                    </p>
                </div>
            </div>
        </div>
    </section>

    {{-- REPORTES VISUALES BASE --}}
    <section class="grid gap-4 xl:grid-cols-12">
        <div class="xl:col-span-4">
            <div wire:ignore class="ui-card rounded-[2rem] p-5">
                <div class="flex items-center justify-between gap-3">
                    <div>
                        <p class="text-xs font-semibold uppercase tracking-[0.16em]" style="color: var(--ui-muted);">
                            Estado de registros
                        </p>
                        <h3 class="mt-1 text-lg font-black" style="color: var(--ui-text);">
                            Activas e inactivas
                        </h3>
                    </div>

                    <div class="flex h-11 w-11 items-center justify-center rounded-2xl ring-1"
                        style="background: var(--ui-primary-soft); color: var(--ui-primary); --tw-ring-color: var(--ui-primary-border);">
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"
                                d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                        </svg>
                    </div>
                </div>

                <div class="mt-4 h-64 rounded-2xl p-3" style="background: var(--ui-surface-soft);">
                    <canvas id="chartEstadoPersonas"></canvas>
                </div>
            </div>
        </div>

        <div class="xl:col-span-4">
            <div wire:ignore class="ui-card rounded-[2rem] p-5">
                <div class="flex items-center justify-between gap-3">
                    <div>
                        <p class="text-xs font-semibold uppercase tracking-[0.16em]" style="color: var(--ui-muted);">
                            Distribución por género
                        </p>
                        <h3 class="mt-1 text-lg font-black" style="color: var(--ui-text);">
                            Datos demográficos
                        </h3>
                    </div>

                    <div class="flex h-11 w-11 items-center justify-center rounded-2xl ring-1"
                        style="background: var(--ui-violet-soft); color: var(--ui-violet); --tw-ring-color: var(--ui-violet-border);">
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"
                                d="M18 18.72a9.094 9.094 0 0 0 3.75-1.32 4.5 4.5 0 0 0-7.5-3.35M6 18.72a9.094 9.094 0 0 1-3.75-1.32 4.5 4.5 0 0 1 7.5-3.35M15 7.5a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" />
                        </svg>
                    </div>
                </div>

                <div class="mt-4 h-64 rounded-2xl p-3" style="background: var(--ui-surface-soft);">
                    <canvas id="chartGeneroPersonas"></canvas>
                </div>
            </div>
        </div>

        <div class="xl:col-span-4">
            <div wire:ignore class="ui-card rounded-[2rem] p-5">
                <div class="flex items-center justify-between gap-3">
                    <div>
                        <p class="text-xs font-semibold uppercase tracking-[0.16em]" style="color: var(--ui-muted);">
                            Cuentas de usuario
                        </p>
                        <h3 class="mt-1 text-lg font-black" style="color: var(--ui-text);">
                            Acceso al sistema
                        </h3>
                    </div>

                    <div class="flex h-11 w-11 items-center justify-center rounded-2xl ring-1"
                        style="background: var(--ui-info-soft); color: var(--ui-info); --tw-ring-color: var(--ui-info-border);">
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"
                                d="M16.5 10.5V6.75a4.5 4.5 0 0 0-9 0v3.75M6.75 10.5h10.5A2.25 2.25 0 0 1 19.5 12.75v6A2.25 2.25 0 0 1 17.25 21H6.75A2.25 2.25 0 0 1 4.5 18.75v-6A2.25 2.25 0 0 1 6.75 10.5Z" />
                        </svg>
                    </div>
                </div>

                <div class="mt-4 h-64 rounded-2xl p-3" style="background: var(--ui-surface-soft);">
                    <canvas id="chartUsuariosPersonas"></canvas>
                </div>
            </div>
        </div>
    </section>

    {{-- BARRA DE FILTROS --}}
    <section class="ui-card rounded-[2rem] p-5">
        <div class="grid gap-4 xl:grid-cols-12">

            {{-- BUSCADOR --}}
            <div class="xl:col-span-4">
                <label class="ui-label text-xs uppercase tracking-[0.14em]">
                    Buscar persona
                </label>

                <div class="flex items-center gap-3 rounded-2xl border px-4 py-3 shadow-sm transition focus-within:ring-4"
                    style="background: var(--ui-surface); border-color: var(--ui-border); color: var(--ui-text); --tw-ring-color: var(--ui-ring);">
                    <div class="shrink-0" style="color: var(--ui-muted);">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24"
                            stroke="currentColor" stroke-width="1.8">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="m21 21-4.35-4.35m1.85-5.15a7 7 0 1 1-14 0 7 7 0 0 1 14 0Z" />
                        </svg>
                    </div>

                    <input type="text" wire:model.live.debounce.400ms="search"
                        placeholder="Buscar por nombre, CI, correo o teléfono..."
                        class="w-full border-0 bg-transparent p-0 text-sm focus:outline-none focus:ring-0"
                        style="color: var(--ui-text);">
                </div>
            </div>

            {{-- GÉNERO --}}
            <div class="xl:col-span-2">
                <label class="ui-label text-xs uppercase tracking-[0.14em]">
                    Género
                </label>

                <div class="relative rounded-2xl border shadow-sm transition focus-within:ring-4"
                    style="background: var(--ui-surface); border-color: var(--ui-border); --tw-ring-color: var(--ui-ring);">
                    <select wire:model.live="genero"
                        class="block w-full appearance-none rounded-2xl border-0 bg-transparent px-4 py-3 text-sm font-medium outline-none focus:ring-0"
                        style="color: var(--ui-text);">
                        <option value="">Todos</option>
                        <option value="M">Masculino</option>
                        <option value="F">Femenino</option>
                    </select>

                    <div class="pointer-events-none absolute inset-y-0 right-3 flex items-center"
                        style="color: var(--ui-muted);">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24"
                            stroke="currentColor" stroke-width="1.8">
                            <path stroke-linecap="round" stroke-linejoin="round" d="m19 9-7 7-7-7" />
                        </svg>
                    </div>
                </div>
            </div>

            {{-- ESTADO --}}
            <div class="xl:col-span-2">
                <label class="ui-label text-xs uppercase tracking-[0.14em]">
                    Estado
                </label>

                <div class="relative rounded-2xl border shadow-sm transition focus-within:ring-4"
                    style="background: var(--ui-surface); border-color: var(--ui-border); --tw-ring-color: var(--ui-ring);">
                    <select wire:model.live="estado"
                        class="block w-full appearance-none rounded-2xl border-0 bg-transparent px-4 py-3 text-sm font-medium outline-none focus:ring-0"
                        style="color: var(--ui-text);">
                        <option value="">Todos</option>
                        <option value="1">Activo</option>
                        <option value="0">Inactivo</option>
                    </select>

                    <div class="pointer-events-none absolute inset-y-0 right-3 flex items-center"
                        style="color: var(--ui-muted);">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24"
                            stroke="currentColor" stroke-width="1.8">
                            <path stroke-linecap="round" stroke-linejoin="round" d="m19 9-7 7-7-7" />
                        </svg>
                    </div>
                </div>
            </div>

            {{-- CUENTA DE USUARIO --}}
            <div class="xl:col-span-2">
                <label class="ui-label text-xs uppercase tracking-[0.14em]">
                    Cuenta
                </label>

                <div class="relative rounded-2xl border shadow-sm transition focus-within:ring-4"
                    style="background: var(--ui-surface); border-color: var(--ui-border); --tw-ring-color: var(--ui-ring);">
                    <select wire:model.live="cuentaUsuario"
                        class="block w-full appearance-none rounded-2xl border-0 bg-transparent px-4 py-3 text-sm font-medium outline-none focus:ring-0"
                        style="color: var(--ui-text);">
                        <option value="">Todas</option>
                        <option value="con_usuario">Con usuario</option>
                        <option value="sin_usuario">Sin usuario</option>
                    </select>

                    <div class="pointer-events-none absolute inset-y-0 right-3 flex items-center"
                        style="color: var(--ui-muted);">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24"
                            stroke="currentColor" stroke-width="1.8">
                            <path stroke-linecap="round" stroke-linejoin="round" d="m19 9-7 7-7-7" />
                        </svg>
                    </div>
                </div>
            </div>

            {{-- LIMPIAR --}}
            <div class="xl:col-span-2">
                <label class="ui-label text-xs uppercase tracking-[0.14em]">
                    Acción
                </label>

                <button type="button" wire:click="limpiarFiltros" class="ui-btn-secondary w-full">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24"
                        stroke="currentColor" stroke-width="1.8">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" />
                    </svg>
                    Limpiar filtros
                </button>
            </div>
        </div>
    </section>

    {{-- TABLA --}}
    <section class="ui-table-wrap">
        <div class="overflow-x-auto">
            <table class="ui-table">
                <thead>
                    <tr>
                        <th>Nombre completo</th>
                        <th>CI</th>
                        <th>Género</th>
                        <th>Contacto</th>
                        <th>Cuenta</th>
                        <th>Estado</th>
                        <th class="text-center">Acciones</th>
                    </tr>
                </thead>

                <tbody>
                    @forelse ($personas as $persona)
                        @php
                            $nombreCompleto = trim(
                                ($persona->nom_per ?? '') . ' ' .
                                ($persona->ape_pat_per ?? '') . ' ' .
                                ($persona->ape_mat_per ?? '')
                            );

                            $inicial = strtoupper(substr($persona->nom_per ?? 'P', 0, 1));
                            $estadoPersona = (bool) $persona->est_per;
                            $tieneUsuario = isset($persona->usuario);
                        @endphp

                        <tr wire:key="persona-{{ $persona->cod_per }}"
                            class="transition {{ !$estadoPersona ? 'opacity-70' : '' }}">

                            {{-- NOMBRE --}}
                            <td>
                                <div class="flex items-center gap-3">
                                    <div class="h-11 w-11 overflow-hidden rounded-2xl ring-1"
                                        style="--tw-ring-color: var(--ui-border);">
                                        @if ($persona->fot_per)
                                            <img src="{{ asset('storage/' . $persona->fot_per) }}"
                                                alt="Foto de {{ $nombreCompleto }}" class="h-full w-full object-cover">
                                        @else
                                            <div class="flex h-full w-full items-center justify-center text-sm font-bold"
                                                style="background: linear-gradient(135deg, var(--ui-primary-soft), var(--ui-info-soft)); color: var(--ui-text-soft);">
                                                {{ $inicial }}
                                            </div>
                                        @endif
                                    </div>

                                    <div class="min-w-0">
                                        <p class="truncate text-sm font-bold" style="color: var(--ui-text);">
                                            {{ $nombreCompleto ?: 'Persona sin nombre' }}
                                        </p>

                                        <p class="mt-0.5 truncate text-xs" style="color: var(--ui-muted);">
                                            {{ $persona->dir_per ?: 'Sin dirección registrada' }}
                                        </p>
                                    </div>
                                </div>
                            </td>

                            {{-- CI --}}
                            <td>
                                <p class="text-sm font-semibold" style="color: var(--ui-text-soft);">
                                    {{ $persona->ci_per }}
                                    @if ($persona->com_per)
                                        - {{ $persona->com_per }}
                                    @endif
                                </p>
                                <p class="text-xs" style="color: var(--ui-muted);">
                                    Exp. {{ $persona->exp_per ?? '—' }}
                                </p>
                            </td>

                            {{-- GÉNERO --}}
                            <td>
                                @if ($persona->gen_per === 'M')
                                    <span class="ui-badge-info">
                                        Masculino
                                    </span>
                                @elseif ($persona->gen_per === 'F')
                                    <span class="ui-badge-violet">
                                        Femenino
                                    </span>
                                @else
                                    <span class="ui-badge-muted">
                                        No definido
                                    </span>
                                @endif
                            </td>

                            {{-- CONTACTO --}}
                            <td>
                                <p class="text-sm font-medium" style="color: var(--ui-text-soft);">
                                    {{ $persona->tel_per ?: 'Sin teléfono' }}
                                </p>
                                <p class="text-xs" style="color: var(--ui-muted);">
                                    {{ $persona->ema_per ?: 'Sin correo' }}
                                </p>
                            </td>

                            {{-- CUENTA --}}
                            <td>
                                @if ($tieneUsuario)
                                    <span class="ui-badge-success">
                                        Con usuario
                                    </span>
                                @else
                                    <span class="ui-badge-warning">
                                        Sin usuario
                                    </span>
                                @endif
                            </td>

                            {{-- ESTADO --}}
                            <td>
                                @if ($estadoPersona)
                                    <span class="ui-badge-success">
                                        <span class="h-2 w-2 rounded-full" style="background: var(--ui-primary);"></span>
                                        Activo
                                    </span>
                                @else
                                    <span class="ui-badge-danger">
                                        <span class="h-2 w-2 rounded-full" style="background: var(--ui-danger);"></span>
                                        Inactivo
                                    </span>
                                @endif
                            </td>

                            {{-- ACCIONES --}}
                            <td>
                                <div class="flex items-center justify-center gap-1.5">
                                    <button type="button" wire:click="abrirModalVer('{{ $persona->cod_per }}')"
                                        wire:loading.attr="disabled" wire:target="abrirModalVer('{{ $persona->cod_per }}')"
                                        class="ui-icon-btn disabled:cursor-wait disabled:opacity-60" title="Ver detalle">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none"
                                            viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                d="M2.036 12.322a1 1 0 0 1 0-.644C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.01 9.963 7.178a1 1 0 0 1 0 .644C20.577 16.49 16.639 19.5 12 19.5c-4.638 0-8.573-3.01-9.964-7.178Z" />
                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" />
                                        </svg>
                                    </button>

                                    <button type="button" wire:click="abrirModalEditar('{{ $persona->cod_per }}')"
                                        wire:loading.attr="disabled"
                                        wire:target="abrirModalEditar('{{ $persona->cod_per }}')"
                                        class="ui-icon-btn disabled:cursor-wait disabled:opacity-60" title="Editar persona">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none"
                                            viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                d="m16.862 4.487 1.687-1.688a2.25 2.25 0 1 1 3.182 3.182L10.582 17.13a4.5 4.5 0 0 1-1.897 1.13L6 19l.74-2.685a4.5 4.5 0 0 1 1.13-1.897L16.862 4.487Z" />
                                        </svg>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-6 py-14 text-center">
                                <div class="mx-auto max-w-md">
                                    <div class="mx-auto flex h-16 w-16 items-center justify-center rounded-[1.5rem]"
                                        style="background: var(--ui-surface-muted); color: var(--ui-muted);">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8" fill="none"
                                            viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                d="M17 20h5V4H2v16h5m10 0v-4a2 2 0 0 0-2-2H9a2 2 0 0 0-2 2v4" />
                                        </svg>
                                    </div>

                                    <h3 class="mt-5 text-lg font-bold" style="color: var(--ui-text);">
                                        No se encontraron personas
                                    </h3>

                                    <p class="mt-2 text-sm leading-6" style="color: var(--ui-muted);">
                                        No existen registros que coincidan con los filtros aplicados.
                                    </p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- PIE --}}
        <div class="flex flex-col gap-4 border-t px-6 py-4 lg:flex-row lg:items-center lg:justify-between"
            style="border-color: var(--ui-border);">
            <p class="text-sm" style="color: var(--ui-muted);">
                Mostrando
                <span class="font-semibold" style="color: var(--ui-text);">{{ $personas->firstItem() ?? 0 }}</span>
                -
                <span class="font-semibold" style="color: var(--ui-text);">{{ $personas->lastItem() ?? 0 }}</span>
                de
                <span class="font-semibold" style="color: var(--ui-text);">{{ $personas->total() }}</span>
                personas
            </p>

            <div>
                {{ $personas->links() }}
            </div>
        </div>
    </section>

    {{-- MODAL CREAR PERSONA --}}
    @if ($modalCrear)
        <div wire:key="modal-crear-persona" class="fixed inset-0 z-50 flex items-center justify-center px-4 py-6 sm:px-6">
            <div class="ui-modal-backdrop" wire:click="cerrarModalCrear"></div>

            <div x-data="{ valido: false }" x-init="$nextTick(() => valido = validarPersonaForm($root))"
                @input="valido = validarPersonaForm($root)" @change="valido = validarPersonaForm($root)"
                class="ui-modal w-full max-w-4xl">

                <div class="bg-gradient-to-r from-emerald-600 to-sky-600 px-6 py-5 text-white">
                    <div class="flex items-start justify-between gap-4">
                        <div>
                            <p class="text-xs font-semibold uppercase tracking-[0.18em] text-white/80">
                                Nuevo registro
                            </p>
                            <h3 class="mt-2 text-2xl font-black">
                                Registrar persona
                            </h3>
                            <p class="mt-2 text-sm text-white/90">
                                Registra los datos personales antes de crear una cuenta de usuario.
                            </p>
                        </div>

                        <button type="button" wire:click="cerrarModalCrear"
                            class="rounded-2xl bg-white/10 p-2 text-white transition hover:bg-white/20">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24"
                                stroke="currentColor" stroke-width="1.8">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" />
                            </svg>
                        </button>
                    </div>
                </div>

                <div class="max-h-[72vh] overflow-y-auto px-6 py-6 ui-scrollbar">
                    <div class="grid gap-5 md:grid-cols-2">
                        <div class="md:col-span-2">
                            <label class="ui-label">
                                Foto de la persona
                            </label>

                            <div class="ui-card-soft flex flex-col gap-4 p-4 sm:flex-row sm:items-center">
                                <div class="h-24 w-24 overflow-hidden rounded-3xl ring-1"
                                    style="background: var(--ui-surface); --tw-ring-color: var(--ui-border);">
                                    @if ($foto)
                                        <img src="{{ $foto->temporaryUrl() }}" class="h-full w-full object-cover">
                                    @else
                                        <div class="flex h-full w-full items-center justify-center text-2xl font-black"
                                            style="background: linear-gradient(135deg, var(--ui-primary-soft), var(--ui-info-soft)); color: var(--ui-text-soft);">
                                            +
                                        </div>
                                    @endif
                                </div>

                                <div class="flex-1">
                                    <input type="file" wire:model="foto" accept="image/*" class="ui-input">

                                    @error('foto')
                                        <p class="ui-error">{{ $message }}</p>
                                    @enderror

                                    <p class="ui-help">
                                        Formatos permitidos: JPG, PNG o WEBP. Tamaño máximo: 2 MB.
                                    </p>
                                </div>
                            </div>
                        </div>

                        <div>
                            <label class="ui-label">Nombre</label>
                            <input type="text" wire:model="form.nom_per" data-field="nombre"
                                @input="$event.target.value = limpiarSoloLetras($event.target.value)" class="ui-input">
                            @error('form.nom_per')
                                <p class="ui-error">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label class="ui-label">Apellido paterno</label>
                            <input type="text" wire:model="form.ape_pat_per" data-field="apellido"
                                @input="$event.target.value = limpiarSoloLetras($event.target.value)" class="ui-input">
                            @error('form.ape_pat_per')
                                <p class="ui-error">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label class="ui-label">Apellido materno</label>
                            <input type="text" wire:model="form.ape_mat_per"
                                @input="$event.target.value = limpiarSoloLetras($event.target.value)" class="ui-input">
                        </div>

                        <div>
                            <label class="ui-label">CI</label>
                            <input type="text" wire:model="form.ci_per" data-field="ci" maxlength="12"
                                @input="$event.target.value = limpiarSoloNumeros($event.target.value)" class="ui-input">
                            @error('form.ci_per')
                                <p class="ui-error">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label class="ui-label">Complemento</label>
                            <input type="text" wire:model="form.com_per" maxlength="4" class="ui-input">
                        </div>

                        <div>
                            <label class="ui-label">Expedido</label>
                            <select wire:model="form.exp_per" data-field="expedido" class="ui-select">
                                <option value="">Seleccionar</option>
                                <option value="LP">LP</option>
                                <option value="CB">CB</option>
                                <option value="SC">SC</option>
                                <option value="OR">OR</option>
                                <option value="PT">PT</option>
                                <option value="CH">CH</option>
                                <option value="TJ">TJ</option>
                                <option value="BN">BN</option>
                                <option value="PD">PD</option>
                            </select>
                            @error('form.exp_per')
                                <p class="ui-error">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label class="ui-label">Fecha de nacimiento</label>
                            <input type="date" wire:model="form.fec_nac_per" data-field="fecha" min="1900-01-01"
                                max="{{ now()->subYears(8)->format('Y-m-d') }}" class="ui-input">
                            @error('form.fec_nac_per')
                                <p class="ui-error">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label class="ui-label">Género</label>
                            <select wire:model="form.gen_per" data-field="genero" class="ui-select">
                                <option value="">Seleccionar</option>
                                <option value="M">Masculino</option>
                                <option value="F">Femenino</option>
                            </select>
                            @error('form.gen_per')
                                <p class="ui-error">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label class="ui-label">Teléfono</label>
                            <input type="text" wire:model="form.tel_per" data-field="telefono" maxlength="8"
                                @input="$event.target.value = limpiarSoloNumeros($event.target.value)" class="ui-input">
                        </div>

                        <div>
                            <label class="ui-label">Correo</label>
                            <input type="email" wire:model="form.ema_per" data-field="email" placeholder="persona@gmail.com"
                                class="ui-input">
                            @error('form.ema_per')
                                <p class="ui-error">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="md:col-span-2">
                            <label class="ui-label">Dirección</label>
                            <input type="text" wire:model="form.dir_per" class="ui-input">
                        </div>

                        <div class="md:col-span-2">
                            <label class="ui-label">Estado</label>
                            <select wire:model="form.est_per" class="ui-select">
                                <option value="1">Activo</option>
                                <option value="0">Inactivo</option>
                            </select>
                        </div>

                        <div class="ui-card-soft md:col-span-2 p-4">
                            <p class="text-sm font-semibold" style="color: var(--ui-text-soft);">
                                Validación del registro
                            </p>
                            <p class="mt-1 text-xs leading-5" style="color: var(--ui-muted);">
                                El botón de guardado se habilita cuando nombre, apellido paterno, CI, expedido, fecha de
                                nacimiento y género son válidos.
                            </p>
                        </div>
                    </div>
                </div>

                <div class="ui-modal-footer flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-end">
                    <p x-show="!valido" x-cloak class="text-sm font-medium sm:mr-auto" style="color: var(--ui-danger);">
                        Completa correctamente los campos obligatorios para guardar.
                    </p>

                    <button type="button" wire:click="cerrarModalCrear" class="ui-btn-secondary">
                        Cancelar
                    </button>

                    <button type="button" wire:click="guardarPersona" wire:loading.attr="disabled"
                        wire:target="guardarPersona" :disabled="!valido" :class="valido
                                ? 'ui-btn-primary'
                                : 'ui-btn cursor-not-allowed bg-slate-300 text-slate-500 shadow-none'"
                        class="rounded-2xl px-5 py-3 text-sm font-semibold transition">
                        Guardar persona
                    </button>
                </div>
            </div>
        </div>
    @endif

    {{-- MODAL VER PERSONA --}}
    @if ($modalVer && $personaDetalle)
        <div wire:key="modal-ver-persona-{{ $personaDetalle->cod_per }}"
            class="fixed inset-0 z-50 flex items-center justify-center px-4 py-6 sm:px-6">
            <div class="ui-modal-backdrop" wire:click="cerrarModalVer"></div>

            <div class="ui-modal w-full max-w-3xl">
                <div class="bg-gradient-to-r from-sky-600 to-emerald-600 px-6 py-5 text-white">
                    <div class="flex items-start justify-between gap-4">
                        <div>
                            <p class="text-xs font-semibold uppercase tracking-[0.18em] text-white/80">
                                Detalle de registro
                            </p>
                            <h3 class="mt-2 text-2xl font-black">
                                Información de la persona
                            </h3>
                        </div>

                        <button type="button" wire:click="cerrarModalVer"
                            class="rounded-2xl bg-white/10 p-2 text-white transition hover:bg-white/20">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24"
                                stroke="currentColor" stroke-width="1.8">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" />
                            </svg>
                        </button>
                    </div>
                </div>

                <div class="px-6 py-6">
                    @php
                        $nombreDetalle = trim(
                            ($personaDetalle->nom_per ?? '') . ' ' .
                            ($personaDetalle->ape_pat_per ?? '') . ' ' .
                            ($personaDetalle->ape_mat_per ?? '')
                        );

                        $estadoDetalle = (bool) $personaDetalle->est_per;
                        $tieneUsuarioDetalle = isset($personaDetalle->usuario);
                    @endphp

                    <div class="ui-card-soft mb-5 flex items-center gap-4 p-4">
                        <div class="h-20 w-20 overflow-hidden rounded-3xl ring-1"
                            style="--tw-ring-color: var(--ui-border);">
                            @if ($personaDetalle->fot_per)
                                <img src="{{ asset('storage/' . $personaDetalle->fot_per) }}" alt="Foto de {{ $nombreDetalle }}"
                                    class="h-full w-full object-cover">
                            @else
                                <div class="flex h-full w-full items-center justify-center text-2xl font-black"
                                    style="background: linear-gradient(135deg, var(--ui-primary-soft), var(--ui-info-soft)); color: var(--ui-text-soft);">
                                    {{ strtoupper(substr($personaDetalle->nom_per ?? 'P', 0, 1)) }}
                                </div>
                            @endif
                        </div>

                        <div class="min-w-0">
                            <h4 class="truncate text-lg font-black" style="color: var(--ui-text);">
                                {{ $nombreDetalle ?: 'Persona sin nombre' }}
                            </h4>

                            @if ($personaDetalle->ema_per)
                                <a href="mailto:{{ $personaDetalle->ema_per }}"
                                    class="mt-2 inline-flex items-center gap-2 rounded-xl px-3 py-2 text-sm font-bold ring-1 transition"
                                    style="background: var(--ui-info-soft); color: var(--ui-info); --tw-ring-color: var(--ui-info-border);">
                                    Correo: {{ $personaDetalle->ema_per }}
                                </a>
                            @else
                                <p class="mt-2 font-bold" style="color: var(--ui-text);">
                                    Sin correo registrado
                                </p>
                            @endif
                        </div>
                    </div>

                    <div class="grid gap-4 sm:grid-cols-2">
                        <div class="ui-card-soft p-4">
                            <p class="text-xs font-semibold uppercase tracking-[0.14em]" style="color: var(--ui-muted);">CI
                            </p>
                            <p class="mt-2 font-bold" style="color: var(--ui-text);">
                                {{ $personaDetalle->ci_per }}
                                @if ($personaDetalle->com_per)
                                    - {{ $personaDetalle->com_per }}
                                @endif
                            </p>
                            <p class="text-xs" style="color: var(--ui-muted);">Exp. {{ $personaDetalle->exp_per ?? '—' }}
                            </p>
                        </div>

                        <div class="ui-card-soft p-4">
                            <p class="text-xs font-semibold uppercase tracking-[0.14em]" style="color: var(--ui-muted);">
                                Género</p>
                            <p class="mt-2 font-bold" style="color: var(--ui-text);">
                                {{ $personaDetalle->gen_per === 'M' ? 'Masculino' : ($personaDetalle->gen_per === 'F' ? 'Femenino' : 'No definido') }}
                            </p>
                        </div>

                        <div class="ui-card-soft p-4">
                            <p class="text-xs font-semibold uppercase tracking-[0.14em]" style="color: var(--ui-muted);">
                                Fecha de nacimiento</p>
                            <p class="mt-2 font-bold" style="color: var(--ui-text);">
                                {{ $personaDetalle->fec_nac_per ?: 'No registrada' }}
                            </p>
                        </div>

                        <div class="ui-card-soft p-4">
                            <p class="text-xs font-semibold uppercase tracking-[0.14em]" style="color: var(--ui-muted);">
                                Teléfono</p>
                            @if ($personaDetalle->tel_per)
                                @php
                                    $telefonoWhatsappDetalle = preg_replace('/\D/', '', $personaDetalle->tel_per);
                                @endphp

                                <a href="https://wa.me/591{{ $telefonoWhatsappDetalle }}" target="_blank"
                                    class="mt-2 inline-flex items-center gap-2 rounded-xl px-3 py-2 text-sm font-bold ring-1 transition"
                                    style="background: var(--ui-primary-soft); color: var(--ui-primary); --tw-ring-color: var(--ui-primary-border);">
                                    {{ $personaDetalle->tel_per }}
                                </a>
                            @else
                                <p class="mt-2 font-bold" style="color: var(--ui-text);">Sin teléfono</p>
                            @endif
                        </div>

                        <div class="ui-card-soft p-4 sm:col-span-2">
                            <p class="text-xs font-semibold uppercase tracking-[0.14em]" style="color: var(--ui-muted);">
                                Dirección</p>
                            <p class="mt-2 font-bold" style="color: var(--ui-text);">
                                {{ $personaDetalle->dir_per ?: 'Sin dirección registrada' }}
                            </p>
                        </div>

                        <div class="ui-card-soft p-4">
                            <p class="text-xs font-semibold uppercase tracking-[0.14em]" style="color: var(--ui-muted);">
                                Estado</p>
                            <p class="mt-2">
                                @if ($estadoDetalle)
                                    <span class="ui-badge-success">Activo</span>
                                @else
                                    <span class="ui-badge-danger">Inactivo</span>
                                @endif
                            </p>
                        </div>

                        <div class="ui-card-soft p-4">
                            <p class="text-xs font-semibold uppercase tracking-[0.14em]" style="color: var(--ui-muted);">
                                Cuenta de usuario</p>
                            <p class="mt-2">
                                @if ($tieneUsuarioDetalle)
                                    <span class="ui-badge-success">Con usuario</span>
                                @else
                                    <span class="ui-badge-warning">Sin usuario</span>
                                @endif
                            </p>
                        </div>
                    </div>
                </div>

                <div class="ui-modal-footer text-right">
                    <button type="button" wire:click="cerrarModalVer" class="ui-btn-secondary">
                        Cerrar
                    </button>
                </div>
            </div>
        </div>
    @endif

    {{-- MODAL EDITAR PERSONA --}}
    @if ($modalEditar)
        <div wire:key="modal-editar-persona" class="fixed inset-0 z-50 flex items-center justify-center px-4 py-6 sm:px-6">
            <div class="ui-modal-backdrop" wire:click="cerrarModalEditar"></div>

            <div x-data="{ valido: false }" x-init="$nextTick(() => valido = validarPersonaForm($root))"
                @input="valido = validarPersonaForm($root)" @change="valido = validarPersonaForm($root)"
                class="ui-modal w-full max-w-4xl">

                <div class="bg-gradient-to-r from-emerald-600 to-sky-600 px-6 py-5 text-white">
                    <div class="flex items-start justify-between gap-4">
                        <div>
                            <p class="text-xs font-semibold uppercase tracking-[0.18em] text-white/80">
                                Edición de registro
                            </p>
                            <h3 class="mt-2 text-2xl font-black">
                                Editar persona
                            </h3>
                            <p class="mt-2 text-sm text-white/90">
                                Actualiza los datos personales sin modificar los códigos internos.
                            </p>
                        </div>

                        <button type="button" wire:click="cerrarModalEditar"
                            class="rounded-2xl bg-white/10 p-2 text-white transition hover:bg-white/20">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24"
                                stroke="currentColor" stroke-width="1.8">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" />
                            </svg>
                        </button>
                    </div>
                </div>

                <div class="max-h-[72vh] overflow-y-auto px-6 py-6 ui-scrollbar">
                    @error('editar_general')
                        <div class="ui-alert-danger mb-5">
                            {{ $message }}
                        </div>
                    @enderror

                    <div class="grid gap-5 md:grid-cols-2">
                        <div class="md:col-span-2">
                            <label class="ui-label">
                                Foto de la persona
                            </label>

                            <div class="ui-card-soft flex flex-col gap-4 p-4 sm:flex-row sm:items-center">
                                <div class="h-24 w-24 overflow-hidden rounded-3xl ring-1"
                                    style="background: var(--ui-surface); --tw-ring-color: var(--ui-border);">
                                    @if ($fotoEditar)
                                        <img src="{{ $fotoEditar->temporaryUrl() }}" class="h-full w-full object-cover">
                                    @elseif (!empty($formEditar['fot_per']))
                                        <img src="{{ asset('storage/' . $formEditar['fot_per']) }}"
                                            class="h-full w-full object-cover">
                                    @else
                                        <div class="flex h-full w-full items-center justify-center text-2xl font-black"
                                            style="background: linear-gradient(135deg, var(--ui-primary-soft), var(--ui-info-soft)); color: var(--ui-text-soft);">
                                            {{ strtoupper(substr($formEditar['nom_per'] ?? 'P', 0, 1)) }}
                                        </div>
                                    @endif
                                </div>

                                <div class="flex-1">
                                    <input type="file" wire:model="fotoEditar" accept="image/*" class="ui-input">

                                    @error('fotoEditar')
                                        <p class="ui-error">{{ $message }}</p>
                                    @enderror

                                    <p class="ui-help">
                                        Formatos permitidos: JPG, PNG o WEBP. Tamaño máximo: 2 MB.
                                    </p>

                                    @if (!empty($formEditar['fot_per']))
                                        <button type="button" wire:click="eliminarFotoEditar"
                                            class="mt-3 rounded-xl border px-3 py-2 text-xs font-semibold transition"
                                            style="background: var(--ui-danger-soft); color: var(--ui-danger); border-color: var(--ui-danger-border);">
                                            Quitar foto actual
                                        </button>
                                    @endif
                                </div>
                            </div>
                        </div>

                        <div>
                            <label class="ui-label">Nombre</label>
                            <input type="text" wire:model="formEditar.nom_per" data-field="nombre"
                                @input="$event.target.value = limpiarSoloLetras($event.target.value)" class="ui-input">
                            @error('formEditar.nom_per')
                                <p class="ui-error">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label class="ui-label">Apellido paterno</label>
                            <input type="text" wire:model="formEditar.ape_pat_per" data-field="apellido"
                                @input="$event.target.value = limpiarSoloLetras($event.target.value)" class="ui-input">
                            @error('formEditar.ape_pat_per')
                                <p class="ui-error">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label class="ui-label">Apellido materno</label>
                            <input type="text" wire:model="formEditar.ape_mat_per"
                                @input="$event.target.value = limpiarSoloLetras($event.target.value)" class="ui-input">
                        </div>

                        <div>
                            <label class="ui-label">CI</label>
                            <input type="text" wire:model="formEditar.ci_per" data-field="ci" maxlength="12"
                                @input="$event.target.value = limpiarSoloNumeros($event.target.value)" class="ui-input">
                            @error('formEditar.ci_per')
                                <p class="ui-error">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label class="ui-label">Complemento</label>
                            <input type="text" wire:model="formEditar.com_per" data-field="complemento" maxlength="4"
                                @input="$event.target.value = limpiarComplementoCi($event.target.value)" class="ui-input">
                        </div>

                        <div>
                            <label class="ui-label">Expedido</label>
                            <select wire:model="formEditar.exp_per" data-field="expedido" class="ui-select">
                                <option value="">Seleccionar</option>
                                <option value="LP">LP</option>
                                <option value="CB">CB</option>
                                <option value="SC">SC</option>
                                <option value="OR">OR</option>
                                <option value="PT">PT</option>
                                <option value="CH">CH</option>
                                <option value="TJ">TJ</option>
                                <option value="BN">BN</option>
                                <option value="PD">PD</option>
                            </select>
                            @error('formEditar.exp_per')
                                <p class="ui-error">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label class="ui-label">Fecha de nacimiento</label>
                            <input type="date" wire:model="formEditar.fec_nac_per" data-field="fecha" min="1900-01-01"
                                max="{{ now()->subYears(10)->format('Y-m-d') }}" class="ui-input">
                            @error('formEditar.fec_nac_per')
                                <p class="ui-error">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label class="ui-label">Género</label>
                            <select wire:model="formEditar.gen_per" data-field="genero" class="ui-select">
                                <option value="">Seleccionar</option>
                                <option value="M">Masculino</option>
                                <option value="F">Femenino</option>
                            </select>
                            @error('formEditar.gen_per')
                                <p class="ui-error">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label class="ui-label">Teléfono</label>
                            <input type="text" wire:model="formEditar.tel_per" data-field="telefono" maxlength="8"
                                @input="$event.target.value = limpiarSoloNumeros($event.target.value)" class="ui-input">
                        </div>

                        <div>
                            <label class="ui-label">Correo</label>
                            <input type="email" wire:model="formEditar.ema_per" data-field="email"
                                placeholder="persona@gmail.com" class="ui-input">
                            @error('formEditar.ema_per')
                                <p class="ui-error">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="md:col-span-2">
                            <label class="ui-label">Dirección</label>
                            <input type="text" wire:model="formEditar.dir_per" class="ui-input">
                        </div>

                        <div class="md:col-span-2">
                            <label class="ui-label">Estado</label>
                            <select wire:model="formEditar.est_per" class="ui-select">
                                <option value="1">Activo</option>
                                <option value="0">Inactivo</option>
                            </select>
                            @error('formEditar.est_per')
                                <p class="ui-error">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="ui-card-soft md:col-span-2 p-4">
                            <p class="text-sm font-semibold" style="color: var(--ui-text-soft);">
                                Validación del registro
                            </p>
                            <p class="mt-1 text-xs leading-5" style="color: var(--ui-muted);">
                                El botón de guardado se habilita cuando los campos obligatorios cumplen el formato definido.
                            </p>
                        </div>
                    </div>
                </div>

                <div class="ui-modal-footer flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-end">
                    <p x-show="!valido" x-cloak class="text-sm font-medium sm:mr-auto" style="color: var(--ui-danger);">
                        Completa correctamente los campos obligatorios para guardar.
                    </p>

                    <button type="button" wire:click="cerrarModalEditar" class="ui-btn-secondary">
                        Cancelar
                    </button>

                    <button type="button" wire:click="actualizarPersona" wire:loading.attr="disabled"
                        wire:target="actualizarPersona" :disabled="!valido" :class="valido
                                ? 'ui-btn-primary'
                                : 'ui-btn cursor-not-allowed bg-slate-300 text-slate-500 shadow-none'"
                        class="rounded-2xl px-5 py-3 text-sm font-semibold transition">
                        Guardar cambios
                    </button>
                </div>
            </div>
        </div>
    @endif

    @push('scripts')
        <script>
            function limpiarSoloLetras(valor) {
                return valor.replace(/[^A-Za-zÁÉÍÓÚáéíóúÑñÜü\s]/g, '');
            }

            function limpiarSoloNumeros(valor) {
                return valor.replace(/[^0-9]/g, '');
            }

            function limpiarComplementoCi(valor) {
                return valor.replace(/[^A-Za-z0-9]/g, '').toUpperCase();
            }

            function validarPersonaForm(root) {
                const nombre = root.querySelector('[data-field="nombre"]')?.value.trim() ?? '';
                const apellido = root.querySelector('[data-field="apellido"]')?.value.trim() ?? '';
                const ci = root.querySelector('[data-field="ci"]')?.value.trim() ?? '';
                const fecha = root.querySelector('[data-field="fecha"]')?.value ?? '';
                const expedido = root.querySelector('[data-field="expedido"]')?.value ?? '';
                const genero = root.querySelector('[data-field="genero"]')?.value ?? '';
                const telefono = root.querySelector('[data-field="telefono"]')?.value.trim() ?? '';
                const email = root.querySelector('[data-field="email"]')?.value.trim() ?? '';

                const nombreValido = nombre.length >= 2;
                const apellidoValido = apellido.length >= 2;
                const ciValido = /^[0-9]{5,12}$/.test(ci);
                const expedidoValido = expedido !== '';
                const generoValido = genero !== '';
                const telefonoValido = telefono === '' || /^[0-9]{7,8}$/.test(telefono);
                const emailValido = email === '' || /^[a-zA-Z0-9._%+-]+@(gmail\.com|hotmail\.com|outlook\.com|yahoo\.com)$/.test(email);

                let fechaValida = false;

                if (fecha) {
                    const nacimiento = new Date(fecha + 'T00:00:00');
                    const minima = new Date('1900-01-01T00:00:00');
                    const maxima = new Date();
                    maxima.setFullYear(maxima.getFullYear() - 8);

                    fechaValida = nacimiento >= minima && nacimiento <= maxima;
                }

                return nombreValido &&
                    apellidoValido &&
                    ciValido &&
                    expedidoValido &&
                    generoValido &&
                    fechaValida &&
                    telefonoValido &&
                    emailValido;
            }

            document.addEventListener('livewire:navigated', iniciarGraficosPersonas);
            document.addEventListener('DOMContentLoaded', iniciarGraficosPersonas);
            window.addEventListener('theme-changed', iniciarGraficosPersonas);

            let chartEstadoPersonas = null;
            let chartGeneroPersonas = null;
            let chartUsuariosPersonas = null;

            function getChartThemePersonas() {
                const styles = getComputedStyle(document.documentElement);

                return {
                    text: styles.getPropertyValue('--ui-text').trim(),
                    muted: styles.getPropertyValue('--ui-muted').trim(),
                    border: styles.getPropertyValue('--ui-border').trim(),
                    surface: styles.getPropertyValue('--ui-surface').trim(),
                    primary: styles.getPropertyValue('--ui-primary').trim(),
                    info: styles.getPropertyValue('--ui-info').trim(),
                    violet: styles.getPropertyValue('--ui-violet').trim(),
                    warning: styles.getPropertyValue('--ui-warning').trim(),
                    danger: styles.getPropertyValue('--ui-danger').trim(),
                };
            }

            function iniciarGraficosPersonas() {
                const estadoCanvas = document.getElementById('chartEstadoPersonas');
                const generoCanvas = document.getElementById('chartGeneroPersonas');
                const usuariosCanvas = document.getElementById('chartUsuariosPersonas');

                if (!estadoCanvas || !generoCanvas || !usuariosCanvas) return;

                if (!window.Chart) {
                    console.warn('Chart.js no está disponible. Verifica resources/js/app.js');
                    return;
                }

                const theme = getChartThemePersonas();

                if (chartEstadoPersonas) chartEstadoPersonas.destroy();
                if (chartGeneroPersonas) chartGeneroPersonas.destroy();
                if (chartUsuariosPersonas) chartUsuariosPersonas.destroy();

                chartEstadoPersonas = new Chart(estadoCanvas, {
                    type: 'doughnut',
                    data: {
                        labels: @json($datosGraficos['estado']['labels']),
                        datasets: [{
                            data: @json($datosGraficos['estado']['data']),
                            backgroundColor: [
                                theme.primary,
                                theme.danger,
                            ],
                            borderColor: theme.surface,
                            borderWidth: 3,
                            hoverOffset: 8,
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
                                    color: theme.muted,
                                    usePointStyle: true,
                                    pointStyle: 'circle',
                                    padding: 18,
                                    font: {
                                        size: 12,
                                        weight: '600',
                                    },
                                }
                            }
                        }
                    }
                });

                chartGeneroPersonas = new Chart(generoCanvas, {
                    type: 'bar',
                    data: {
                        labels: @json($datosGraficos['genero']['labels']),
                        datasets: [{
                            label: 'Personas',
                            data: @json($datosGraficos['genero']['data']),
                            backgroundColor: theme.violet,
                            borderRadius: 10,
                            maxBarThickness: 44,
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: {
                                display: false
                            }
                        },
                        scales: {
                            x: {
                                ticks: {
                                    color: theme.muted,
                                },
                                grid: {
                                    display: false,
                                },
                                border: {
                                    color: theme.border,
                                }
                            },
                            y: {
                                beginAtZero: true,
                                ticks: {
                                    color: theme.muted,
                                    precision: 0,
                                },
                                grid: {
                                    color: theme.border,
                                },
                                border: {
                                    color: theme.border,
                                }
                            }
                        }
                    }
                });

                chartUsuariosPersonas = new Chart(usuariosCanvas, {
                    type: 'doughnut',
                    data: {
                        labels: @json($datosGraficos['usuarios']['labels']),
                        datasets: [{
                            data: @json($datosGraficos['usuarios']['data']),
                            backgroundColor: [
                                theme.info,
                                theme.warning,
                            ],
                            borderColor: theme.surface,
                            borderWidth: 3,
                            hoverOffset: 8,
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
                                    color: theme.muted,
                                    usePointStyle: true,
                                    pointStyle: 'circle',
                                    padding: 18,
                                    font: {
                                        size: 12,
                                        weight: '600',
                                    },
                                }
                            }
                        }
                    }
                });
            }
        </script>
    @endpush
</div>