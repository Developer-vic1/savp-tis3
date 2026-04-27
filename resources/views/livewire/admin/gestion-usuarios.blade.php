<div x-data x-on:usuario-creado.window="
        Swal.fire({
            icon: 'success',
            title: 'Usuario creado',
            text: 'La cuenta de usuario se registró correctamente.',
            confirmButtonText: 'Entendido',
            confirmButtonColor: '#059669'
        });
    " x-on:usuarios-sincronizados.window="
    Swal.fire({
        icon: 'success',
        title: 'Datos sincronizados',
        text: `Se sincronizaron ${$event.detail.cantidad} registro(s) faltante(s).`,
        confirmButtonText: 'Entendido',
        confirmButtonColor: '#059669'
    });
    " x-on:error-sincronizacion.window="
    Swal.fire({
        icon: 'error',
        title: 'Error de sincronización',
        text: 'No se pudieron sincronizar los datos. Revisa la consola o el log del sistema.',
        confirmButtonText: 'Entendido',
        confirmButtonColor: '#dc2626'
    });
    " x-on:usuario-actualizado.window="
    Swal.fire({
        icon: 'success',
        title: 'Usuario actualizado',
        text: 'Los datos de la cuenta fueron actualizados correctamente.',
        confirmButtonText: 'Entendido',
        confirmButtonColor: '#059669'
    });
    " x-on:no-puedes-desactivarte.window="
        Swal.fire({
            icon: 'warning',
            title: 'Acción no permitida',
            text: 'No puedes desactivar tu propia cuenta.',
            confirmButtonText: 'Entendido',
            confirmButtonColor: '#f59e0b'
        });
    " x-on:usuario-desactivado.window="
        Swal.fire({
            icon: 'success',
            title: 'Usuario desactivado',
            text: 'La cuenta fue desactivada correctamente.',
            confirmButtonText: 'Entendido',
            confirmButtonColor: '#059669'
        });
    " x-on:usuario-reactivado.window="
        Swal.fire({
            icon: 'success',
            title: 'Usuario reactivado',
            text: 'La cuenta fue reactivada correctamente.',
            confirmButtonText: 'Entendido',
            confirmButtonColor: '#059669'
        });
    " x-on:usuarios-desactivados.window="
    Swal.fire({
        icon: 'success',
        title: 'Usuarios desactivados',
        text: 'Los usuarios seleccionados fueron desactivados correctamente.',
        confirmButtonText: 'Entendido',
        confirmButtonColor: '#059669'
    });
    " x-on:usuarios-reactivados.window="
        Swal.fire({
            icon: 'success',
            title: 'Usuarios reactivados',
            text: 'Los usuarios seleccionados fueron reactivados correctamente.',
            confirmButtonText: 'Entendido',
            confirmButtonColor: '#059669'
        });

    "
    class="space-y-6">

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
    </style>

    {{-- RESUMEN + ACCIÓN PRINCIPAL --}}
    <section class="grid gap-4 xl:grid-cols-12">
        <div class="xl:col-span-8">
            <div class="rounded-[2rem] border border-slate-200 bg-white/90 p-5 shadow-sm backdrop-blur-sm">
                <div class="flex flex-col gap-5 md:flex-row md:items-center md:justify-between">
                    <div class="min-w-[180px]">
                        <p class="text-xs font-semibold uppercase tracking-[0.16em] text-slate-500">
                            Total usuarios
                        </p>
                        <h3 class="mt-2 text-5xl font-black tracking-tight text-slate-950">
                            {{ $totalUsuarios }}
                        </h3>
                        <p class="mt-2 text-sm text-slate-500">
                            Registros de acceso del sistema.
                        </p>
                    </div>

                    <div class="grid flex-1 gap-3 sm:grid-cols-3">
                        <div class="rounded-2xl bg-sky-50 px-4 py-4 ring-1 ring-sky-100">
                            <p class="text-xs font-semibold uppercase tracking-[0.14em] text-sky-700">
                                Estudiantes
                            </p>
                            <p class="mt-2 text-2xl font-black text-slate-900">
                                {{ $totalEstudiantes }}
                            </p>
                        </div>

                        <div class="rounded-2xl bg-emerald-50 px-4 py-4 ring-1 ring-emerald-100">
                            <p class="text-xs font-semibold uppercase tracking-[0.14em] text-emerald-700">
                                Docentes
                            </p>
                            <p class="mt-2 text-2xl font-black text-slate-900">
                                {{ $totalDocentes }}
                            </p>
                        </div>

                        <div class="rounded-2xl bg-violet-50 px-4 py-4 ring-1 ring-violet-100">
                            <p class="text-xs font-semibold uppercase tracking-[0.14em] text-violet-700">
                                Administrativos
                            </p>
                            <p class="mt-2 text-2xl font-black text-slate-900">
                                {{ $totalAdministrativos }}
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="xl:col-span-4">
            <div class="rounded-[2rem] border border-slate-200 bg-white/90 p-5 shadow-sm backdrop-blur-sm">
                <div class="flex h-full flex-col justify-center">
                    <button wire:click="abrirModalCrear"
                        class="inline-flex w-full items-center justify-center gap-2 rounded-2xl bg-gradient-to-r from-emerald-600 to-sky-600 px-5 py-3.5 text-sm font-semibold text-white shadow-lg shadow-emerald-500/20 transition hover:-translate-y-0.5 hover:shadow-xl">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24"
                            stroke="currentColor" stroke-width="1.8">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                        </svg>
                        <span>Añadir nuevo usuario</span>
                    </button>

                    <p class="mt-3 text-center text-xs leading-5 text-slate-500">
                        Registra una nueva cuenta y asigna su rol dentro del sistema.
                    </p>
                </div>
            </div>
        </div>
    </section>

    {{-- BARRA OPERATIVA --}}
    <section class="rounded-[2rem] border border-slate-200 bg-white/90 p-5 shadow-sm backdrop-blur-sm">
        <div class="grid gap-4 xl:grid-cols-12">
            {{-- BUSCADOR --}}
            <div class="xl:col-span-5">
                <label class="mb-2 block text-xs font-semibold uppercase tracking-[0.14em] text-slate-500">
                    Buscar
                </label>

                <div
                    class="flex items-center gap-3 rounded-2xl border border-slate-300 bg-white px-4 py-3 shadow-sm transition focus-within:border-emerald-500 focus-within:ring-4 focus-within:ring-emerald-100">
                    <div class="shrink-0 text-slate-400">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24"
                            stroke="currentColor" stroke-width="1.8">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="m21 21-4.35-4.35m1.85-5.15a7 7 0 1 1-14 0 7 7 0 0 1 14 0Z" />
                        </svg>
                    </div>

                    <input type="text" wire:model.live.debounce.400ms="search"
                        placeholder="Buscar por nombre, código o correo..."
                        class="w-full border-0 bg-transparent p-0 text-sm text-slate-700 placeholder:text-slate-400 focus:outline-none focus:ring-0">
                </div>
            </div>

            {{-- ROL --}}
            <div class="xl:col-span-2">
                <label class="mb-2 block text-xs font-semibold uppercase tracking-[0.14em] text-slate-500">
                    Rol
                </label>

                <div
                    class="relative rounded-2xl border border-slate-300 bg-white shadow-sm transition focus-within:border-sky-500 focus-within:ring-4 focus-within:ring-sky-100">
                    <select wire:model.live="rol"
                        class="block w-full appearance-none rounded-2xl border-0 bg-transparent px-4 py-3 text-sm font-medium text-slate-700 outline-none ring-0 focus:border-0 focus:outline-none focus:ring-0">
                        <option value="">Todos los roles</option>
                        @foreach ($rolesDisponibles as $role)
                            <option value="{{ $role->name }}">{{ $role->name }}</option>
                        @endforeach
                    </select>

                    <div class="pointer-events-none absolute inset-y-0 right-3 flex items-center text-slate-400">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24"
                            stroke="currentColor" stroke-width="1.8">
                            <path stroke-linecap="round" stroke-linejoin="round" d="m19 9-7 7-7-7" />
                        </svg>
                    </div>
                </div>
            </div>

            {{-- ESTADO --}}
            <div class="xl:col-span-2">
                <label class="mb-2 block text-xs font-semibold uppercase tracking-[0.14em] text-slate-500">
                    Estado
                </label>

                <div
                    class="relative rounded-2xl border border-slate-300 bg-white shadow-sm transition focus-within:border-sky-500 focus-within:ring-4 focus-within:ring-sky-100">
                    <select wire:model.live="estado"
                        class="block w-full appearance-none rounded-2xl border-0 bg-transparent px-4 py-3 text-sm font-medium text-slate-700 outline-none ring-0 focus:border-0 focus:outline-none focus:ring-0">
                        <option value="">Todos</option>
                        <option value="ACTIVO">Activo</option>
                        <option value="INACTIVO">Inactivo</option>
                    </select>

                    <div class="pointer-events-none absolute inset-y-0 right-3 flex items-center text-slate-400">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24"
                            stroke="currentColor" stroke-width="1.8">
                            <path stroke-linecap="round" stroke-linejoin="round" d="m19 9-7 7-7-7" />
                        </svg>
                    </div>
                </div>
            </div>

            {{-- GESTIÓN DE SELECCIONADOS --}}
            <div class="xl:col-span-3">
                <label class="mb-2 block text-xs font-semibold uppercase tracking-[0.14em] text-slate-500">
                    Gestión de usuarios seleccionados
                </label>

                <div
                    class="relative rounded-2xl border border-slate-300 bg-white shadow-sm transition focus-within:border-sky-500 focus-within:ring-4 focus-within:ring-sky-100">
                    <select wire:model.live="accionLote" @disabled(count($selected) === 0)
                        class="block w-full appearance-none rounded-2xl border-0 bg-transparent px-4 py-3 text-sm font-medium text-slate-700 outline-none ring-0 focus:border-0 focus:outline-none focus:ring-0 disabled:cursor-not-allowed disabled:text-slate-400">
                        <option value="">Selecciona una acción</option>
                        <option value="activar">Activar seleccionados</option>
                        <option value="inactivar">Desactivar seleccionados</option>
                    </select>

                    <div class="pointer-events-none absolute inset-y-0 right-3 flex items-center text-slate-400">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24"
                            stroke="currentColor" stroke-width="1.8">
                            <path stroke-linecap="round" stroke-linejoin="round" d="m19 9-7 7-7-7" />
                        </svg>
                    </div>
                </div>
            </div>
        </div>

        {{-- BARRA SECUNDARIA DE SELECCIÓN --}}
        <div
            class="mt-4 flex flex-col gap-3 rounded-2xl border border-slate-200 bg-slate-50/70 px-4 py-4 sm:flex-row sm:items-center sm:justify-between">
            <div class="flex items-center gap-3">
                <div
                    class="flex h-10 w-10 items-center justify-center rounded-xl bg-white text-slate-500 ring-1 ring-slate-200">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24"
                        stroke="currentColor" stroke-width="1.8">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                    </svg>
                </div>

                <div>
                    <p class="text-sm font-semibold text-slate-800">
                        Gestión de selección
                    </p>
                    <p class="text-xs text-slate-500">
                        <span class="font-semibold text-slate-700">{{ count($selected) }}</span>
                        usuario(s) seleccionado(s)
                    </p>
                </div>
            </div>

            <div class="flex flex-wrap gap-2">
                
                @if ($hayUsuariosParaSincronizar)
                    <button type="button" wire:click="sincronizarDatosUsuarios"
                        class="inline-flex items-center gap-2 rounded-2xl border border-amber-300 bg-amber-50 px-4 py-2.5 text-sm font-semibold text-amber-700 transition hover:bg-amber-100">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24"
                            stroke="currentColor" stroke-width="1.8">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M16.023 9.348h4.992v-.001M2.985 19.644v-4.992m0 0h4.992m-4.993 0 3.181 3.183a8.25 8.25 0 0 0 13.803-3.7M21.015 4.356v4.992m0 0h-4.992m4.992 0-3.181-3.183a8.25 8.25 0 0 0-13.803 3.7" />
                        </svg>
                        Sincronizar datos
                    </button>
                @endif
                <button type="button" wire:click="aplicarAccionLote"
                    class="inline-flex items-center gap-2 rounded-2xl bg-gradient-to-r from-emerald-600 to-sky-600 px-4 py-2.5 text-sm font-semibold text-white shadow-md shadow-emerald-500/20 transition hover:-translate-y-0.5 hover:shadow-lg disabled:cursor-not-allowed disabled:opacity-50"
                    @disabled(count($selected) === 0 || $accionLote === '')>
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24"
                        stroke="currentColor" stroke-width="1.8">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v12m6-6H6" />
                    </svg>
                    Aplicar
                </button>

                <button wire:click="limpiarFiltros"
                    class="inline-flex items-center gap-2 rounded-2xl border border-slate-300 bg-white px-4 py-2.5 text-sm font-medium text-slate-700 transition hover:bg-slate-100">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24"
                        stroke="currentColor" stroke-width="1.8">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" />
                    </svg>
                    Limpiar
                </button>
            </div>
        </div>
    </section>

    {{-- TABLA --}}
    <section class="overflow-hidden rounded-[2rem] border border-slate-200 bg-white/90 shadow-sm backdrop-blur-sm">
        <div class="overflow-x-auto">
            <table class="min-w-full border-separate border-spacing-0">
                <thead>
                    <tr class="bg-slate-50/90">
                        <th class="border-b border-slate-200 px-4 py-3 text-left">
                            <input type="checkbox" wire:model.live="selectAll"
                                class="rounded border-slate-300 text-emerald-600 shadow-sm focus:ring-emerald-500">
                        </th>

                        <th
                            class="border-b border-slate-200 px-4 py-3 text-left text-xs font-semibold uppercase tracking-[0.14em] text-slate-500">
                            Nombre completo
                        </th>

                        <th
                            class="border-b border-slate-200 px-4 py-3 text-left text-xs font-semibold uppercase tracking-[0.14em] text-slate-500">
                            Correo electrónico
                        </th>

                        <th
                            class="border-b border-slate-200 px-4 py-3 text-left text-xs font-semibold uppercase tracking-[0.14em] text-slate-500">
                            Rol
                        </th>

                        <th
                            class="border-b border-slate-200 px-4 py-3 text-left text-xs font-semibold uppercase tracking-[0.14em] text-slate-500">
                            Referencia
                        </th>

                        <th
                            class="border-b border-slate-200 px-4 py-3 text-left text-xs font-semibold uppercase tracking-[0.14em] text-slate-500">
                            Estado
                        </th>

                        <th
                            class="border-b border-slate-200 px-4 py-3 text-center text-xs font-semibold uppercase tracking-[0.14em] text-slate-500">
                            Acciones
                        </th>
                    </tr>
                </thead>

                <tbody class="bg-white">
                    @forelse ($usuarios as $usuario)
                        @php
                            $persona = $usuario->persona;
                            $rolActual = $usuario->roles->first()?->name ?? 'Sin rol';

                            $nombreCompleto = trim(
                                ($persona?->nom_per ?? '') . ' ' .
                                ($persona?->ape_pat_per ?? '') . ' ' .
                                ($persona?->ape_mat_per ?? '')
                            );

                            $estadoUsuario = isset($usuario->est_usu) ? $usuario->est_usu : 'ACTIVO';
                            $esInactivo = $estadoUsuario === 'INACTIVO';
                            $esUsuarioActual = auth()->user()?->cod_usu === $usuario->cod_usu;

                            $referencia = match ($rolActual) {
                                'Estudiante' => 'Estudiante registrado',
                                'Docente' => 'Docente del sistema',
                                'Administrador' => 'Administrador del sistema',
                                'Director' => 'Dirección institucional',
                                'Secretaria', 'Secretaria Académica' => 'Apoyo administrativo',
                                'Regente' => 'Supervisión académica',
                                default => '—',
                            };

                            $inicial = strtoupper(substr($persona?->nom_per ?? 'U', 0, 1));

                            $badgeRol = match ($rolActual) {
                                'Administrador' => 'bg-slate-900 text-white',
                                'Director' => 'bg-violet-100 text-violet-700',
                                'Docente' => 'bg-emerald-100 text-emerald-700',
                                'Estudiante' => 'bg-sky-100 text-sky-700',
                                'Secretaria', 'Secretaria Académica' => 'bg-amber-100 text-amber-700',
                                'Regente' => 'bg-fuchsia-100 text-fuchsia-700',
                                default => 'bg-slate-100 text-slate-600',
                            };
                        @endphp

                        <tr wire:key="row-{{ $usuario->cod_usu }}" class="transition hover:bg-slate-50/70 {{ $esInactivo ? 'bg-slate-50/60 opacity-80' : '' }}">
                            {{-- CHECKBOX --}}
                            <td class="border-b border-slate-200 px-4 py-3">
                            <input type="checkbox"
                                wire:model.live="selected"
                                value="{{ $usuario->cod_usu }}"
                            @if ($esUsuarioActual) disabled @endif
                                title="{{ $esUsuarioActual ? 'No puedes seleccionarte para cambios de estado' : 'Seleccionar usuario' }}"
                                class="rounded border-slate-300 text-emerald-600 shadow-sm focus:ring-emerald-500 disabled:cursor-not-allowed disabled:opacity-40">
                            </td>

                            {{-- NOMBRE --}}
                            <td class="border-b border-slate-200 px-4 py-3">
                                <div class="flex items-center gap-3">
                                    <div
                                        class="flex h-10 w-10 items-center justify-center rounded-2xl bg-gradient-to-br from-emerald-100 to-sky-100 text-sm font-bold text-slate-700 ring-1 ring-slate-200">
                                        {{ $inicial }}
                                    </div>

                                    <div class="min-w-0">
                                        <p class="truncate text-sm font-bold text-slate-900">
                                            {{ $nombreCompleto ?: 'Usuario sin nombre' }}
                                        </p>
                                        <p class="mt-0.5 truncate text-xs text-slate-500">
                                            {{ $usuario->cod_usu }}
                                        </p>
                                    </div>
                                </div>
                            </td>

                            {{-- CORREO --}}
                            <td class="border-b border-slate-200 px-4 py-3">
                                <p class="text-sm font-medium text-slate-700">{{ $usuario->email }}</p>
                            </td>

                            {{-- ROL --}}
                            <td class="border-b border-slate-200 px-4 py-3">
                                <span class="inline-flex rounded-full px-3 py-1 text-xs font-semibold {{ $badgeRol }}">
                                    {{ $rolActual }}
                                </span>
                            </td>

                            {{-- REFERENCIA --}}
                            <td class="border-b border-slate-200 px-4 py-3">
                                <p class="text-sm text-slate-700">{{ $referencia }}</p>
                            </td>

                            {{-- ESTADO --}}
                            <td class="border-b border-slate-200 px-4 py-3">
                                <div class="flex flex-col items-start gap-1">
                                    @if ($estadoUsuario === 'ACTIVO')
                                        <span
                                            class="inline-flex items-center gap-2 rounded-full bg-emerald-50 px-3 py-1 text-xs font-semibold text-emerald-700 ring-1 ring-emerald-200">
                                            <span class="h-2 w-2 rounded-full bg-emerald-500"></span>
                                            Activo
                                        </span>
                                    @else
                                        <span
                                            class="inline-flex items-center gap-2 rounded-full bg-rose-50 px-3 py-1 text-xs font-semibold text-rose-700 ring-1 ring-rose-200">
                                            <span class="h-2 w-2 rounded-full bg-rose-500"></span>
                                            Inactivo
                                        </span>
                                    @endif

                                    @if ($esInactivo)
                                        <span class="text-[10px] font-medium uppercase tracking-[0.12em] text-slate-400">
                                            Solo lectura
                                        </span>
                                    @endif
                                </div>
                            </td>

                            {{-- ACCIONES --}}
                            <td class="border-b border-slate-200 px-4 py-3">
                                <div class="flex items-center justify-center gap-1.5">
                                    {{-- VER DETALLE --}}
                                    <button type="button" wire:click="abrirModalVer('{{ $usuario->cod_usu }}')"
                                        class="rounded-xl p-2 transition {{ $esInactivo ? 'text-slate-400 hover:bg-slate-100' : 'text-slate-500 hover:bg-sky-50 hover:text-sky-700' }}"
                                        title="Ver detalle">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none"
                                            viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                d="M2.036 12.322a1 1 0 0 1 0-.644C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.01 9.963 7.178a1 1 0 0 1 0 .644C20.577 16.49 16.639 19.5 12 19.5c-4.638 0-8.573-3.01-9.964-7.178Z" />
                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" />
                                        </svg>
                                    </button>

                                    {{-- EDITAR --}}
                                    <button type="button" @if ($esInactivo) disabled @endif
                                        wire:click="abrirModalEditar('{{ $usuario->cod_usu }}')"
                                        class="rounded-xl p-2 transition {{ $esInactivo ? 'cursor-not-allowed text-slate-300' : 'text-slate-500 hover:bg-emerald-50 hover:text-emerald-700' }}"
                                        title="{{ $esInactivo ? 'No disponible para usuarios inactivos' : 'Editar usuario' }}">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none"
                                            viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                d="m16.862 4.487 1.687-1.688a2.25 2.25 0 1 1 3.182 3.182L10.582 17.13a4.5 4.5 0 0 1-1.897 1.13L6 19l.74-2.685a4.5 4.5 0 0 1 1.13-1.897L16.862 4.487Z" />
                                        </svg>
                                    </button>

                                    @if (!$esInactivo)
                                        @if ($esUsuarioActual)
                                            <button type="button"
                                                x-on:click="
                                                    Swal.fire({
                                                        icon: 'warning',
                                                        title: 'Acción no permitida',
                                                        text: 'No puedes desactivar tu propia cuenta.',
                                                        confirmButtonText: 'Entendido',
                                                        confirmButtonColor: '#f59e0b'
                                                    });
                                                "
                                                class="cursor-not-allowed rounded-xl p-2 text-slate-300 transition"
                                                title="No puedes desactivar tu propia cuenta">

                                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none"
                                                    viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M18 12H6" />
                                                    <circle cx="12" cy="12" r="9" />
                                                </svg>
                                            </button>
                                        @else
                                            <button type="button"
                                                x-on:click="
                                                    Swal.fire({
                                                        title: '¿Desactivar usuario?',
                                                        text: 'El usuario dejará de tener acceso al sistema hasta que vuelva a ser activado.',
                                                        icon: 'warning',
                                                        showCancelButton: true,
                                                        confirmButtonText: 'Sí, desactivar',
                                                        cancelButtonText: 'Cancelar',
                                                        confirmButtonColor: '#dc2626',
                                                        cancelButtonColor: '#64748b',
                                                        reverseButtons: true
                                                    }).then((result) => {
                                                        if (result.isConfirmed) {
                                                            $wire.desactivarUsuario(@js($usuario->cod_usu));
                                                        }
                                                    });
                                                "
                                                class="rounded-xl p-2 text-slate-500 transition hover:bg-rose-50 hover:text-rose-700"
                                                title="Desactivar usuario">

                                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none"
                                                    viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M18 12H6" />
                                                    <circle cx="12" cy="12" r="9" />
                                                </svg>
                                            </button>
                                        @endif
                                    @else
                                        <button type="button"
                                            x-on:click="
                                                Swal.fire({
                                                    title: '¿Reactivar usuario?',
                                                    text: 'El usuario volverá a tener acceso al sistema.',
                                                    icon: 'question',
                                                    showCancelButton: true,
                                                    confirmButtonText: 'Sí, reactivar',
                                                    cancelButtonText: 'Cancelar',
                                                    confirmButtonColor: '#059669',
                                                    cancelButtonColor: '#64748b',
                                                    reverseButtons: true
                                                }).then((result) => {
                                                    if (result.isConfirmed) {
                                                        $wire.reactivarUsuario(@js($usuario->cod_usu));
                                                    }
                                                });
                                            "
                                            class="rounded-xl p-2 text-slate-500 transition hover:bg-emerald-50 hover:text-emerald-700"
                                            title="Reactivar usuario">

                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none"
                                                viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v12m6-6H6" />
                                                <circle cx="12" cy="12" r="9" />
                                            </svg>
                                        </button>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-6 py-14 text-center">
                                <div class="mx-auto max-w-md">
                                    <div
                                        class="mx-auto flex h-16 w-16 items-center justify-center rounded-[1.5rem] bg-slate-100 text-slate-400">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8" fill="none"
                                            viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                d="M17 20h5V4H2v16h5m10 0v-4a2 2 0 0 0-2-2H9a2 2 0 0 0-2 2v4" />
                                        </svg>
                                    </div>

                                    <h3 class="mt-5 text-lg font-bold text-slate-900">
                                        No se encontraron usuarios
                                    </h3>
                                    <p class="mt-2 text-sm leading-6 text-slate-500">
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
        <div
            class="flex flex-col gap-4 border-t border-slate-200 px-6 py-4 lg:flex-row lg:items-center lg:justify-between">
            <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:gap-4">
                <p class="text-sm text-slate-500">
                    Mostrando
                    <span class="font-semibold text-slate-700">{{ $usuarios->firstItem() ?? 0 }}</span>
                    -
                    <span class="font-semibold text-slate-700">{{ $usuarios->lastItem() ?? 0 }}</span>
                    de
                    <span class="font-semibold text-slate-700">{{ $usuarios->total() }}</span>
                    usuarios
                </p>

                <div>
                    <select wire:model.live="perPage"
                        class="rounded-2xl border border-slate-300 bg-white px-4 py-2 text-sm text-slate-700 shadow-sm transition focus:border-sky-500 focus:outline-none focus:ring-4 focus:ring-sky-100">
                        <option value="10">10 por página</option>
                        <option value="15">15 por página</option>
                        <option value="20">20 por página</option>
                        <option value="30">30 por página</option>
                    </select>
                </div>
            </div>

            <div>
                {{ $usuarios->links() }}
            </div>
        </div>
    </section>

    {{-- MODAL CREAR --}}
    @if ($modalCrear)
        <div class="fixed inset-0 z-50 flex items-center justify-center px-4 py-6 sm:px-6">
            <div class="absolute inset-0 bg-slate-950/50 backdrop-blur-sm" wire:click="cerrarModalCrear"></div>

            <div
                class="relative z-10 w-full max-w-3xl overflow-hidden rounded-[2rem] border border-slate-200 bg-white shadow-2xl">
                <div class="border-b border-slate-200 bg-gradient-to-r from-emerald-600 to-sky-600 px-6 py-5 text-white">
                    <div class="flex items-start justify-between gap-4">
                        <div>
                            <p class="text-xs font-semibold uppercase tracking-[0.18em] text-white/80">
                                Nuevo acceso
                            </p>
                            <h3 class="mt-2 text-2xl font-black tracking-tight">
                                Crear usuario
                            </h3>
                            <p class="mt-2 text-sm text-white/90">
                                Registra una cuenta para una persona que aún no tenga acceso al sistema.
                            </p>
                        </div>

                        <button wire:click="cerrarModalCrear"
                            class="rounded-2xl bg-white/10 p-2 text-white transition hover:bg-white/20">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24"
                                stroke="currentColor" stroke-width="1.8">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" />
                            </svg>
                        </button>
                    </div>
                </div>

                <div class="px-6 py-6">
                    @error('general')
                        <div class="mb-5 rounded-2xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-700">
                            {{ $message }}
                        </div>
                    @enderror

                    <div class="grid gap-5 md:grid-cols-2">
                        <div class="md:col-span-2">
                            <label class="mb-2 block text-sm font-semibold text-slate-700">
                                Persona
                            </label>
                            <select wire:model="form.cod_per"
                                class="w-full rounded-2xl border border-slate-300 bg-white px-4 py-3.5 text-sm text-slate-700 shadow-sm transition focus:border-emerald-500 focus:outline-none focus:ring-4 focus:ring-emerald-100">
                                <option value="">Selecciona una persona</option>
                                @foreach ($personasDisponibles as $persona)
                                    <option value="{{ $persona->cod_per }}">
                                        {{ trim($persona->nom_per . ' ' . $persona->ape_pat_per . ' ' . $persona->ape_mat_per) }}
                                        — {{ $persona->cod_per }}
                                    </option>
                                @endforeach
                            </select>
                            @error('form.cod_per')
                                <p class="mt-2 text-sm text-rose-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label class="mb-2 block text-sm font-semibold text-slate-700">
                                Correo electrónico
                            </label>
                            <input type="email" wire:model="form.email" placeholder="usuario@gmail.com"
                                class="w-full rounded-2xl border border-slate-300 bg-white px-4 py-3.5 text-sm text-slate-700 shadow-sm transition focus:border-emerald-500 focus:outline-none focus:ring-4 focus:ring-emerald-100">
                            @error('form.email')
                                <p class="mt-2 text-sm text-rose-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label class="mb-2 block text-sm font-semibold text-slate-700">
                                Rol del usuario
                            </label>
                            <select wire:model="form.role"
                                class="w-full rounded-2xl border border-slate-300 bg-white px-4 py-3.5 text-sm text-slate-700 shadow-sm transition focus:border-sky-500 focus:outline-none focus:ring-4 focus:ring-sky-100">
                                <option value="">Selecciona un rol</option>
                                @foreach ($rolesDisponibles as $role)
                                    <option value="{{ $role->name }}">{{ $role->name }}</option>
                                @endforeach
                            </select>
                            @error('form.role')
                                <p class="mt-2 text-sm text-rose-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label class="mb-2 block text-sm font-semibold text-slate-700">
                                Contraseña
                            </label>
                            <input type="password" wire:model="form.password" placeholder="Ingresa una contraseña segura"
                                class="w-full rounded-2xl border border-slate-300 bg-white px-4 py-3.5 text-sm text-slate-700 shadow-sm transition focus:border-emerald-500 focus:outline-none focus:ring-4 focus:ring-emerald-100">
                            @error('form.password')
                                <p class="mt-2 text-sm text-rose-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label class="mb-2 block text-sm font-semibold text-slate-700">
                                Confirmar contraseña
                            </label>
                            <input type="password" wire:model="form.password_confirmation"
                                placeholder="Repite la contraseña"
                                class="w-full rounded-2xl border border-slate-300 bg-white px-4 py-3.5 text-sm text-slate-700 shadow-sm transition focus:border-emerald-500 focus:outline-none focus:ring-4 focus:ring-emerald-100">
                        </div>

                        @if (array_key_exists('est_usu', $form))
                            <div class="md:col-span-2">
                                <label class="mb-2 block text-sm font-semibold text-slate-700">
                                    Estado inicial
                                </label>
                                <select wire:model="form.est_usu"
                                    class="w-full rounded-2xl border border-slate-300 bg-white px-4 py-3.5 text-sm text-slate-700 shadow-sm transition focus:border-sky-500 focus:outline-none focus:ring-4 focus:ring-sky-100">
                                    <option value="ACTIVO">Activo</option>
                                    <option value="INACTIVO">Inactivo</option>
                                </select>
                                @error('form.est_usu')
                                    <p class="mt-2 text-sm text-rose-600">{{ $message }}</p>
                                @enderror
                            </div>
                        @endif
                    </div>

                    <div class="mt-6 rounded-[1.5rem] border border-sky-200 bg-sky-50 px-4 py-4">
                        <p class="text-sm font-semibold text-sky-800">
                            Recomendaciones
                        </p>
                        <ul class="mt-2 space-y-1 text-sm text-sky-700">
                            <li>• Selecciona una persona sin cuenta previa.</li>
                            <li>• Asigna un rol según sus funciones dentro del sistema.</li>
                            <li>• Usa una contraseña segura para el acceso inicial.</li>
                        </ul>
                    </div>
                </div>

                <div class="flex flex-col gap-3 border-t border-slate-200 bg-slate-50 px-6 py-5 sm:flex-row sm:justify-end">
                    <button wire:click="cerrarModalCrear"
                        class="rounded-2xl border border-slate-300 bg-white px-5 py-3 text-sm font-semibold text-slate-700 transition hover:bg-slate-100">
                        Cancelar
                    </button>

                    <button wire:click="guardarUsuario"
                        class="rounded-2xl bg-gradient-to-r from-emerald-600 to-sky-600 px-5 py-3 text-sm font-semibold text-white shadow-lg shadow-emerald-500/20 transition hover:-translate-y-0.5 hover:shadow-xl">
                        Guardar usuario
                    </button>
                </div>
            </div>
        </div>
    @endif

    {{-- MODAL VER USUARIO --}}
    @if ($modalVer && $usuarioDetalle)
        <div class="fixed inset-0 z-50 flex items-center justify-center px-4 py-6 sm:px-6">
            <div class="absolute inset-0 bg-slate-950/50 backdrop-blur-sm" wire:click="cerrarModalVer"></div>

            <div class="relative z-10 w-full max-w-2xl overflow-hidden rounded-[2rem] border border-slate-200 bg-white shadow-2xl">
                <div class="bg-gradient-to-r from-sky-600 to-emerald-600 px-6 py-5 text-white">
                    <div class="flex items-start justify-between gap-4">
                        <div>
                            <p class="text-xs font-semibold uppercase tracking-[0.18em] text-white/80">
                                Detalle de cuenta
                            </p>
                            <h3 class="mt-2 text-2xl font-black">
                                Información del usuario
                            </h3>
                            <p class="mt-2 text-sm text-white/90">
                                Consulta los datos principales de acceso y perfil asociado.
                            </p>
                        </div>

                        <button wire:click="cerrarModalVer"
                            class="rounded-2xl bg-white/10 p-2 text-white transition hover:bg-white/20">
                            ✕
                        </button>
                    </div>
                </div>

                <div class="px-6 py-6">
                    @php
                        $personaDetalle = $usuarioDetalle->persona;

                        $nombreDetalle = trim(
                            ($personaDetalle?->nom_per ?? '') . ' ' .
                            ($personaDetalle?->ape_pat_per ?? '') . ' ' .
                            ($personaDetalle?->ape_mat_per ?? '')
                        );

                        $rolDetalle = $usuarioDetalle->roles->first()?->name ?? 'Sin rol';
                        $estadoDetalle = $usuarioDetalle->est_usu ?? 'ACTIVO';
                        $esActivoDetalle = $estadoDetalle === 'ACTIVO';
                        $inicialDetalle = strtoupper(substr($personaDetalle?->nom_per ?? 'U', 0, 1));
                    @endphp

                    <div class="mb-5 flex items-center gap-4 rounded-[1.5rem] border border-slate-200 bg-slate-50 p-4">
                        <div
                            class="flex h-16 w-16 items-center justify-center rounded-2xl bg-gradient-to-br from-emerald-100 to-sky-100 text-2xl font-black text-slate-700 ring-1 ring-slate-200">
                            {{ $inicialDetalle }}
                        </div>

                        <div class="min-w-0">
                            <h4 class="truncate text-lg font-black text-slate-900">
                                {{ $nombreDetalle ?: 'Usuario sin nombre' }}
                            </h4>
                            <p class="mt-1 truncate text-sm text-slate-500">
                                {{ $usuarioDetalle->email }}
                            </p>
                        </div>
                    </div>

                    <div class="grid gap-4 sm:grid-cols-2">
                        <div class="rounded-2xl bg-slate-50 p-4 ring-1 ring-slate-200">
                            <p class="text-xs font-semibold uppercase tracking-[0.14em] text-slate-500">
                                Estado
                            </p>

                            @if ($esActivoDetalle)
                                <span
                                    class="mt-2 inline-flex items-center gap-2 rounded-full bg-emerald-50 px-3 py-1 text-xs font-semibold text-emerald-700 ring-1 ring-emerald-200">
                                    <span class="h-2 w-2 rounded-full bg-emerald-500"></span>
                                    Activo
                                </span>
                            @else
                                <span
                                    class="mt-2 inline-flex items-center gap-2 rounded-full bg-rose-50 px-3 py-1 text-xs font-semibold text-rose-700 ring-1 ring-rose-200">
                                    <span class="h-2 w-2 rounded-full bg-rose-500"></span>
                                    Inactivo
                                </span>
                            @endif
                        </div>

                        <div class="rounded-2xl bg-slate-50 p-4 ring-1 ring-slate-200">
                            <p class="text-xs font-semibold uppercase tracking-[0.14em] text-slate-500">
                                Rol
                            </p>
                            <p class="mt-2 font-bold text-slate-900">
                                {{ $rolDetalle }}
                            </p>
                        </div>

                        <div class="rounded-2xl bg-slate-50 p-4 ring-1 ring-slate-200">
                            <p class="text-xs font-semibold uppercase tracking-[0.14em] text-slate-500">
                                Correo electrónico
                            </p>
                            <p class="mt-2 break-all font-bold text-slate-900">
                                {{ $usuarioDetalle->email }}
                            </p>
                        </div>

                        <div class="rounded-2xl bg-slate-50 p-4 ring-1 ring-slate-200">
                            <p class="text-xs font-semibold uppercase tracking-[0.14em] text-slate-500">
                                Fecha de registro
                            </p>
                            <p class="mt-2 font-bold text-slate-900">
                                {{ $usuarioDetalle->created_at?->format('d/m/Y H:i') ?? 'Sin fecha' }}
                            </p>
                        </div>
                    </div>
                </div>

                <div class="border-t border-slate-200 bg-slate-50 px-6 py-5 text-right">
                    <button wire:click="cerrarModalVer"
                        class="rounded-2xl border border-slate-300 bg-white px-5 py-3 text-sm font-semibold text-slate-700 transition hover:bg-slate-100">
                        Cerrar
                    </button>
                </div>
            </div>
        </div>
    @endif

    {{-- MODAL EDITAR USUARIO --}}
    @if ($modalEditar)
        <div class="fixed inset-0 z-50 flex items-center justify-center px-4 py-6 sm:px-6">
            <div class="absolute inset-0 bg-slate-950/50 backdrop-blur-sm" wire:click="cerrarModalEditar"></div>

            <div class="relative z-10 w-full max-w-2xl overflow-hidden rounded-[2rem] border border-slate-200 bg-white shadow-2xl">
                <div class="bg-gradient-to-r from-emerald-600 to-sky-600 px-6 py-5 text-white">
                    <div class="flex items-start justify-between gap-4">
                        <div>
                            <p class="text-xs font-semibold uppercase tracking-[0.18em] text-white/80">
                                Edición de cuenta
                            </p>
                            <h3 class="mt-2 text-2xl font-black">
                                Editar usuario
                            </h3>
                        </div>

                        <button wire:click="cerrarModalEditar"
                            class="rounded-2xl bg-white/10 p-2 text-white transition hover:bg-white/20">
                            ✕
                        </button>
                    </div>
                </div>

                <div class="px-6 py-6">
                    @error('editar_general')
                        <div class="mb-5 rounded-2xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-700">
                            {{ $message }}
                        </div>
                    @enderror

                    <div class="grid gap-5 md:grid-cols-2">

                        <div>
                            <label class="mb-2 block text-sm font-semibold text-slate-700">
                                Correo electrónico
                            </label>
                            <input type="email" wire:model="formEditar.email"
                                class="w-full rounded-2xl border border-slate-300 bg-white px-4 py-3.5 text-sm text-slate-700 shadow-sm transition focus:border-emerald-500 focus:outline-none focus:ring-4 focus:ring-emerald-100">
                            @error('formEditar.email')
                                <p class="mt-2 text-sm text-rose-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label class="mb-2 block text-sm font-semibold text-slate-700">
                                Rol del usuario
                            </label>
                            <select wire:model="formEditar.role"
                                class="w-full rounded-2xl border border-slate-300 bg-white px-4 py-3.5 text-sm text-slate-700 shadow-sm transition focus:border-sky-500 focus:outline-none focus:ring-4 focus:ring-sky-100">
                                <option value="">Selecciona un rol</option>
                                @foreach ($rolesDisponibles as $role)
                                    <option value="{{ $role->name }}">{{ $role->name }}</option>
                                @endforeach
                            </select>
                            @error('formEditar.role')
                                <p class="mt-2 text-sm text-rose-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="md:col-span-2">
                            <label class="mb-2 block text-sm font-semibold text-slate-700">
                                Estado de cuenta
                            </label>
                            <select wire:model="formEditar.est_usu"
                                class="w-full rounded-2xl border border-slate-300 bg-white px-4 py-3.5 text-sm text-slate-700 shadow-sm transition focus:border-sky-500 focus:outline-none focus:ring-4 focus:ring-sky-100">
                                <option value="ACTIVO">Activo</option>
                                <option value="INACTIVO">Inactivo</option>
                            </select>
                            @error('formEditar.est_usu')
                                <p class="mt-2 text-sm text-rose-600">{{ $message }}</p>
                            @enderror
                        </div>
                        <div>
                            <label class="mb-2 block text-sm font-semibold text-slate-700">
                                Nueva contraseña
                            </label>
                            <input type="password" wire:model="formEditar.password"
                                placeholder="Dejar vacío si no deseas cambiarla"
                                class="w-full rounded-2xl border border-slate-300 bg-white px-4 py-3.5 text-sm text-slate-700 shadow-sm transition focus:border-emerald-500 focus:outline-none focus:ring-4 focus:ring-emerald-100">
                            @error('formEditar.password')
                                <p class="mt-2 text-sm text-rose-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label class="mb-2 block text-sm font-semibold text-slate-700">
                                Confirmar nueva contraseña
                            </label>
                            <input type="password" wire:model="formEditar.password_confirmation"
                                placeholder="Repite la nueva contraseña"
                                class="w-full rounded-2xl border border-slate-300 bg-white px-4 py-3.5 text-sm text-slate-700 shadow-sm transition focus:border-emerald-500 focus:outline-none focus:ring-4 focus:ring-emerald-100">
                        </div>
                    </div>
                </div>

                <div class="flex flex-col gap-3 border-t border-slate-200 bg-slate-50 px-6 py-5 sm:flex-row sm:justify-end">
                    <button wire:click="cerrarModalEditar"
                        class="rounded-2xl border border-slate-300 bg-white px-5 py-3 text-sm font-semibold text-slate-700 transition hover:bg-slate-100">
                        Cancelar
                    </button>

                    <button wire:click="guardarEdicionUsuario"
                        class="rounded-2xl bg-gradient-to-r from-emerald-600 to-sky-600 px-5 py-3 text-sm font-semibold text-white shadow-lg shadow-emerald-500/20 transition hover:-translate-y-0.5 hover:shadow-xl">
                        Guardar cambios
                    </button>
                </div>
            </div>
        </div>
    @endif
</div>
