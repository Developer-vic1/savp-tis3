<div
    x-data="{
        codPer: @entangle('form.cod_per').live,
        email: @entangle('form.email').live,
        role: @entangle('form.role').live,
        password: @entangle('form.password').live,
        confirmation: @entangle('form.password_confirmation').live,

        get emailValido() {
            return /^[a-zA-Z0-9._%+-]+@(gmail\.com|hotmail\.com|outlook\.com|yahoo\.com)$/.test(this.email ?? '')
        },

        get passwordValida() {
            return (this.password ?? '').length >= 8
                && /[A-Z]/.test(this.password ?? '')
                && /[a-z]/.test(this.password ?? '')
                && /[0-9]/.test(this.password ?? '')
                && /[^A-Za-z0-9]/.test(this.password ?? '')
        },

        get passwordsCoinciden() {
            return this.password !== '' && this.password === this.confirmation
        },

        get formularioValido() {
            return this.codPer
                && this.emailValido
                && this.role
                && this.passwordValida
                && this.passwordsCoinciden
        }
    }"
    x-on:usuario-creado.window="
        Swal.fire({
            icon: 'success',
            title: 'Usuario creado',
            text: 'La cuenta de usuario se registró correctamente.',
            confirmButtonText: 'Entendido',
            confirmButtonColor: '#059669'
        });
    "
    x-on:usuarios-sincronizados.window="
        Swal.fire({
            icon: 'success',
            title: 'Datos sincronizados',
            text: `Se sincronizaron ${$event.detail.cantidad} registro(s) faltante(s).`,
            confirmButtonText: 'Entendido',
            confirmButtonColor: '#059669'
        });
    "
    x-on:error-sincronizacion.window="
        Swal.fire({
            icon: 'error',
            title: 'Error de sincronización',
            text: 'No se pudieron sincronizar los datos. Revisa la consola o el log del sistema.',
            confirmButtonText: 'Entendido',
            confirmButtonColor: '#dc2626'
        });
    "
    x-on:usuario-actualizado.window="
        Swal.fire({
            icon: 'success',
            title: 'Usuario actualizado',
            text: 'Los datos de la cuenta fueron actualizados correctamente.',
            confirmButtonText: 'Entendido',
            confirmButtonColor: '#059669'
        });
    "
    x-on:no-puedes-desactivarte.window="
        Swal.fire({
            icon: 'warning',
            title: 'Acción no permitida',
            text: 'No puedes desactivar tu propia cuenta.',
            confirmButtonText: 'Entendido',
            confirmButtonColor: '#f59e0b'
        });
    "
    x-on:usuario-desactivado.window="
        Swal.fire({
            icon: 'success',
            title: 'Usuario desactivado',
            text: 'La cuenta fue desactivada correctamente.',
            confirmButtonText: 'Entendido',
            confirmButtonColor: '#059669'
        });
    "
    x-on:usuario-reactivado.window="
        Swal.fire({
            icon: 'success',
            title: 'Usuario reactivado',
            text: 'La cuenta fue reactivada correctamente.',
            confirmButtonText: 'Entendido',
            confirmButtonColor: '#059669'
        });
    "
    x-on:usuarios-desactivados.window="
        Swal.fire({
            icon: 'success',
            title: 'Usuarios desactivados',
            text: 'Los usuarios seleccionados fueron desactivados correctamente.',
            confirmButtonText: 'Entendido',
            confirmButtonColor: '#059669'
        });
    "
    x-on:usuarios-reactivados.window="
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

        [x-cloak] {
            display: none !important;
        }
    </style>

    {{-- RESUMEN + ACCIÓN PRINCIPAL --}}
    <section class="grid gap-4 xl:grid-cols-12">
        <div class="xl:col-span-8">
            <div class="ui-card rounded-[2rem] p-5">
                <div class="flex flex-col gap-5 md:flex-row md:items-center md:justify-between">
                    <div class="min-w-[180px]">
                        <div class="inline-flex items-center gap-2 rounded-full px-3 py-1 ring-1"
                            style="background: var(--ui-primary-soft); color: var(--ui-primary); --tw-ring-color: var(--ui-primary-border);">
                            <span class="h-2 w-2 rounded-full" style="background: var(--ui-primary);"></span>
                            <p class="text-xs font-semibold uppercase tracking-[0.16em]">
                                Total usuarios
                            </p>
                        </div>

                        <h3 class="mt-3 text-5xl font-black tracking-tight" style="color: var(--ui-text);">
                            {{ $totalUsuarios }}
                        </h3>

                        <p class="mt-2 text-sm" style="color: var(--ui-muted);">
                            Registros de acceso del sistema.
                        </p>
                    </div>

                    <div class="grid flex-1 gap-3 sm:grid-cols-3">
                        <div class="rounded-2xl px-4 py-4 ring-1"
                            style="background: var(--ui-info-soft); --tw-ring-color: var(--ui-info-border);">
                            <div class="flex items-center gap-2">
                                <svg class="h-4 w-4" style="color: var(--ui-info);" fill="none" stroke="currentColor"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"
                                        d="M4.26 10.147 12 5.625l7.74 4.522L12 14.67l-7.74-4.523Z" />
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"
                                        d="M5.25 11.25v4.875c0 .621.504 1.125 1.125 1.125h11.25c.621 0 1.125-.504 1.125-1.125V11.25" />
                                </svg>
                                <p class="text-xs font-semibold uppercase tracking-[0.14em]" style="color: var(--ui-info);">
                                    Estudiantes
                                </p>
                            </div>

                            <p class="mt-2 text-2xl font-black" style="color: var(--ui-text);">
                                {{ $totalEstudiantes }}
                            </p>
                        </div>

                        <div class="rounded-2xl px-4 py-4 ring-1"
                            style="background: var(--ui-primary-soft); --tw-ring-color: var(--ui-primary-border);">
                            <div class="flex items-center gap-2">
                                <svg class="h-4 w-4" style="color: var(--ui-primary);" fill="none" stroke="currentColor"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"
                                        d="M12 6.75a3 3 0 1 1-6 0 3 3 0 0 1 6 0ZM3.75 20.25a5.25 5.25 0 0 1 10.5 0M14.25 6h6M14.25 9h6M14.25 12h4.5" />
                                </svg>
                                <p class="text-xs font-semibold uppercase tracking-[0.14em]" style="color: var(--ui-primary);">
                                    Docentes
                                </p>
                            </div>

                            <p class="mt-2 text-2xl font-black" style="color: var(--ui-text);">
                                {{ $totalDocentes }}
                            </p>
                        </div>

                        <div class="rounded-2xl px-4 py-4 ring-1"
                            style="background: var(--ui-violet-soft); --tw-ring-color: var(--ui-violet-border);">
                            <div class="flex items-center gap-2">
                                <svg class="h-4 w-4" style="color: var(--ui-violet);" fill="none" stroke="currentColor"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"
                                        d="M3.75 21h16.5M4.5 21V7.5A2.25 2.25 0 0 1 6.75 5.25h10.5A2.25 2.25 0 0 1 19.5 7.5V21M9 8.25h1.5M13.5 8.25H15M9 12h1.5m3 0H15M9 15.75h1.5m3 0H15M11.25 21v-3h1.5v3" />
                                </svg>
                                <p class="text-xs font-semibold uppercase tracking-[0.14em]" style="color: var(--ui-violet);">
                                    Administrativos
                                </p>
                            </div>

                            <p class="mt-2 text-2xl font-black" style="color: var(--ui-text);">
                                {{ $totalAdministrativos }}
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="xl:col-span-4">
            <div class="ui-card rounded-[2rem] p-5">
                <div class="flex h-full flex-col justify-center">
                    <button type="button"
                        wire:click="abrirModalCrear"
                        wire:loading.attr="disabled"
                        wire:target="abrirModalCrear"
                        class="ui-btn-primary w-full disabled:cursor-wait disabled:opacity-60">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24"
                            stroke="currentColor" stroke-width="1.8">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                        </svg>
                        <span>Añadir nuevo usuario</span>
                    </button>

                    <p class="mt-3 text-center text-xs leading-5" style="color: var(--ui-muted);">
                        Registra una nueva cuenta y asigna su rol dentro del sistema.
                    </p>
                </div>
            </div>
        </div>
    </section>

    {{-- BARRA OPERATIVA --}}
    <section class="ui-card rounded-[2rem] p-5">
        <div class="grid gap-4 xl:grid-cols-12">
            {{-- BUSCADOR --}}
            <div class="xl:col-span-5">
                <label class="ui-label text-xs uppercase tracking-[0.14em]">
                    Buscar
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

                    <input type="text"
                        wire:model.live.debounce.400ms="search"
                        placeholder="Buscar por nombre, código o correo..."
                        class="w-full border-0 bg-transparent p-0 text-sm focus:outline-none focus:ring-0"
                        style="color: var(--ui-text);">
                </div>
            </div>

            {{-- ROL --}}
            <div class="xl:col-span-2">
                <label class="ui-label text-xs uppercase tracking-[0.14em]">
                    Rol
                </label>

                <div class="relative rounded-2xl border shadow-sm transition focus-within:ring-4"
                    style="background: var(--ui-surface); border-color: var(--ui-border); --tw-ring-color: var(--ui-ring);">
                    <select wire:model.live="rol"
                        class="block w-full appearance-none rounded-2xl border-0 bg-transparent px-4 py-3 text-sm font-medium outline-none ring-0 focus:border-0 focus:outline-none focus:ring-0"
                        style="color: var(--ui-text);">
                        <option value="">Todos los roles</option>
                        @foreach ($rolesDisponibles as $role)
                            <option value="{{ $role->name }}">{{ $role->name }}</option>
                        @endforeach
                    </select>

                    <div class="pointer-events-none absolute inset-y-0 right-3 flex items-center" style="color: var(--ui-muted);">
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
                        class="block w-full appearance-none rounded-2xl border-0 bg-transparent px-4 py-3 text-sm font-medium outline-none ring-0 focus:border-0 focus:outline-none focus:ring-0"
                        style="color: var(--ui-text);">
                        <option value="">Todos</option>
                        <option value="ACTIVO">Activo</option>
                        <option value="INACTIVO">Inactivo</option>
                    </select>

                    <div class="pointer-events-none absolute inset-y-0 right-3 flex items-center" style="color: var(--ui-muted);">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24"
                            stroke="currentColor" stroke-width="1.8">
                            <path stroke-linecap="round" stroke-linejoin="round" d="m19 9-7 7-7-7" />
                        </svg>
                    </div>
                </div>
            </div>

            {{-- GESTIÓN DE SELECCIONADOS --}}
            <div class="xl:col-span-3">
                <label class="ui-label text-xs uppercase tracking-[0.14em]">
                    Gestión de usuarios seleccionados
                </label>

                <div class="relative rounded-2xl border shadow-sm transition focus-within:ring-4"
                    style="background: var(--ui-surface); border-color: var(--ui-border); --tw-ring-color: var(--ui-ring);">
                    <select wire:model.live="accionLote"
                        @disabled(count($selected) === 0)
                        class="block w-full appearance-none rounded-2xl border-0 bg-transparent px-4 py-3 text-sm font-medium outline-none ring-0 focus:border-0 focus:outline-none focus:ring-0 disabled:cursor-not-allowed disabled:opacity-50"
                        style="color: var(--ui-text);">
                        <option value="">Selecciona una acción</option>
                        <option value="activar">Activar seleccionados</option>
                        <option value="inactivar">Desactivar seleccionados</option>
                    </select>

                    <div class="pointer-events-none absolute inset-y-0 right-3 flex items-center" style="color: var(--ui-muted);">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24"
                            stroke="currentColor" stroke-width="1.8">
                            <path stroke-linecap="round" stroke-linejoin="round" d="m19 9-7 7-7-7" />
                        </svg>
                    </div>
                </div>
            </div>
        </div>

        {{-- BARRA SECUNDARIA DE SELECCIÓN --}}
        <div class="ui-card-soft mt-4 flex flex-col gap-3 px-4 py-4 sm:flex-row sm:items-center sm:justify-between">
            <div class="flex items-center gap-3">
                <div class="flex h-10 w-10 items-center justify-center rounded-xl ring-1"
                    style="background: var(--ui-surface); color: var(--ui-muted); --tw-ring-color: var(--ui-border);">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24"
                        stroke="currentColor" stroke-width="1.8">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                    </svg>
                </div>

                <div>
                    <p class="text-sm font-semibold" style="color: var(--ui-text);">
                        Gestión de selección
                    </p>
                    <p class="text-xs" style="color: var(--ui-muted);">
                        <span class="font-semibold" style="color: var(--ui-text);">{{ count($selected) }}</span>
                        usuario(s) seleccionado(s)
                    </p>
                </div>
            </div>

            <div class="flex flex-wrap gap-2">
                @if ($hayUsuariosParaSincronizar)
                    <button type="button"
                        wire:click="sincronizarDatosUsuarios"
                        wire:loading.attr="disabled"
                        wire:target="sincronizarDatosUsuarios"
                        class="inline-flex items-center gap-2 rounded-2xl border px-4 py-2.5 text-sm font-semibold transition disabled:cursor-wait disabled:opacity-60"
                        style="background: var(--ui-warning-soft); color: var(--ui-warning); border-color: var(--ui-warning-border);">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24"
                            stroke="currentColor" stroke-width="1.8">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M16.023 9.348h4.992v-.001M2.985 19.644v-4.992m0 0h4.992m-4.993 0 3.181 3.183a8.25 8.25 0 0 0 13.803-3.7M21.015 4.356v4.992m0 0h-4.992m4.992 0-3.181-3.183a8.25 8.25 0 0 0-13.803 3.7" />
                        </svg>
                        Sincronizar datos
                    </button>
                @endif

                <button type="button"
                    wire:click="aplicarAccionLote"
                    wire:loading.attr="disabled"
                    wire:target="aplicarAccionLote"
                    class="ui-btn-primary px-4 py-2.5 disabled:cursor-not-allowed disabled:opacity-50"
                    @disabled(count($selected) === 0 || $accionLote === '')>
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24"
                        stroke="currentColor" stroke-width="1.8">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v12m6-6H6" />
                    </svg>
                    Aplicar
                </button>

                <button type="button"
                    wire:click="limpiarFiltros"
                    class="ui-btn-secondary px-4 py-2.5">
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
    <section class="ui-table-wrap">
        <div class="overflow-x-auto">
            <table class="ui-table">
                <thead>
                    <tr>
                        <th class="w-12">
                            <input type="checkbox"
                                wire:model.live="selectAll"
                                class="rounded border-slate-300 text-emerald-600 shadow-sm focus:ring-emerald-500">
                        </th>

                        <th>Nombre completo</th>
                        <th>Correo electrónico</th>
                        <th>Rol</th>
                        <th>Referencia</th>
                        <th>Estado</th>
                        <th class="text-center">Acciones</th>
                    </tr>
                </thead>

                <tbody>
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

                            $badgeRolStyle = match ($rolActual) {
                                'Administrador' => 'background: var(--ui-surface-muted); color: var(--ui-text); --tw-ring-color: var(--ui-border);',
                                'Director' => 'background: var(--ui-violet-soft); color: var(--ui-violet); --tw-ring-color: var(--ui-violet-border);',
                                'Docente' => 'background: var(--ui-primary-soft); color: var(--ui-primary); --tw-ring-color: var(--ui-primary-border);',
                                'Estudiante' => 'background: var(--ui-info-soft); color: var(--ui-info); --tw-ring-color: var(--ui-info-border);',
                                'Secretaria', 'Secretaria Académica' => 'background: var(--ui-warning-soft); color: var(--ui-warning); --tw-ring-color: var(--ui-warning-border);',
                                'Regente' => 'background: var(--ui-violet-soft); color: var(--ui-violet); --tw-ring-color: var(--ui-violet-border);',
                                default => 'background: var(--ui-surface-muted); color: var(--ui-muted); --tw-ring-color: var(--ui-border);',
                            };
                        @endphp

                        <tr wire:key="row-{{ $usuario->cod_usu }}"
                            class="transition {{ $esInactivo ? 'opacity-70' : '' }}">
                            {{-- CHECKBOX --}}
                            <td>
                                <input type="checkbox"
                                    wire:model.live="selected"
                                    value="{{ $usuario->cod_usu }}"
                                    @if ($esUsuarioActual) disabled @endif
                                    title="{{ $esUsuarioActual ? 'No puedes seleccionarte para cambios de estado' : 'Seleccionar usuario' }}"
                                    class="rounded border-slate-300 text-emerald-600 shadow-sm focus:ring-emerald-500 disabled:cursor-not-allowed disabled:opacity-40">
                            </td>

                            {{-- NOMBRE --}}
                            <td>
                                <div class="flex items-center gap-3">
                                    <div class="flex h-10 w-10 items-center justify-center rounded-2xl text-sm font-bold ring-1"
                                        style="background: linear-gradient(135deg, var(--ui-primary-soft), var(--ui-info-soft)); color: var(--ui-text-soft); --tw-ring-color: var(--ui-border);">
                                        {{ $inicial }}
                                    </div>

                                    <div class="min-w-0">
                                        <p class="truncate text-sm font-bold" style="color: var(--ui-text);">
                                            {{ $nombreCompleto ?: 'Usuario sin nombre' }}
                                        </p>
                                        <p class="mt-0.5 truncate text-xs" style="color: var(--ui-muted);">
                                            {{ $usuario->cod_usu }}
                                        </p>
                                    </div>
                                </div>
                            </td>

                            {{-- CORREO --}}
                            <td>
                                <p class="text-sm font-medium" style="color: var(--ui-text-soft);">
                                    {{ $usuario->email }}
                                </p>
                            </td>

                            {{-- ROL --}}
                            <td>
                                <span class="inline-flex rounded-full px-3 py-1 text-xs font-semibold ring-1"
                                    style="{{ $badgeRolStyle }}">
                                    {{ $rolActual }}
                                </span>
                            </td>

                            {{-- REFERENCIA --}}
                            <td>
                                <p class="text-sm" style="color: var(--ui-text-soft);">
                                    {{ $referencia }}
                                </p>
                            </td>

                            {{-- ESTADO --}}
                            <td>
                                <div class="flex flex-col items-start gap-1">
                                    @if ($estadoUsuario === 'ACTIVO')
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

                                    @if ($esInactivo)
                                        <span class="text-[10px] font-medium uppercase tracking-[0.12em]" style="color: var(--ui-muted);">
                                            Solo lectura
                                        </span>
                                    @endif
                                </div>
                            </td>

                            {{-- ACCIONES --}}
                            <td>
                                <div class="flex items-center justify-center gap-1.5">
                                    {{-- VER DETALLE --}}
                                    <button type="button"
                                        wire:click="abrirModalVer('{{ $usuario->cod_usu }}')"
                                        wire:loading.attr="disabled"
                                        wire:target="abrirModalVer('{{ $usuario->cod_usu }}')"
                                        class="ui-icon-btn disabled:cursor-wait disabled:opacity-60"
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
                                    <button type="button"
                                        @if ($esInactivo) disabled @endif
                                        wire:click="abrirModalEditar('{{ $usuario->cod_usu }}')"
                                        wire:loading.attr="disabled"
                                        wire:target="abrirModalEditar('{{ $usuario->cod_usu }}')"
                                        class="ui-icon-btn disabled:cursor-not-allowed disabled:opacity-40"
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
                                                class="ui-icon-btn cursor-not-allowed opacity-40"
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
                                                class="ui-icon-btn"
                                                style="color: var(--ui-danger);"
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
                                            class="ui-icon-btn"
                                            style="color: var(--ui-primary);"
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
                                    <div class="mx-auto flex h-16 w-16 items-center justify-center rounded-[1.5rem]"
                                        style="background: var(--ui-surface-muted); color: var(--ui-muted);">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8" fill="none"
                                            viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                d="M17 20h5V4H2v16h5m10 0v-4a2 2 0 0 0-2-2H9a2 2 0 0 0-2 2v4" />
                                        </svg>
                                    </div>

                                    <h3 class="mt-5 text-lg font-bold" style="color: var(--ui-text);">
                                        No se encontraron usuarios
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
            <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:gap-4">
                <p class="text-sm" style="color: var(--ui-muted);">
                    Mostrando
                    <span class="font-semibold" style="color: var(--ui-text);">{{ $usuarios->firstItem() ?? 0 }}</span>
                    -
                    <span class="font-semibold" style="color: var(--ui-text);">{{ $usuarios->lastItem() ?? 0 }}</span>
                    de
                    <span class="font-semibold" style="color: var(--ui-text);">{{ $usuarios->total() }}</span>
                    usuarios
                </p>

                <div>
                    <select wire:model.live="perPage" class="ui-select py-2">
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
        <div wire:key="modal-crear-usuario" class="fixed inset-0 z-50 flex items-center justify-center px-4 py-6 sm:px-6">
            <div class="ui-modal-backdrop" wire:click="cerrarModalCrear"></div>

            <div class="ui-modal w-full max-w-3xl">
                <div class="bg-gradient-to-r from-emerald-600 to-sky-600 px-6 py-5 text-white">
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
                    @error('general')
                        <div class="ui-alert-danger mb-5">
                            {{ $message }}
                        </div>
                    @enderror

                    <div class="grid gap-5 md:grid-cols-2">
                        <div class="md:col-span-2">
                            <label class="ui-label">
                                Persona
                            </label>

                            <select wire:model.live="form.cod_per" class="ui-select">
                                <option value="">Selecciona una persona</option>
                                @foreach ($personasDisponibles as $persona)
                                    <option value="{{ $persona->cod_per }}">
                                        {{ trim($persona->nom_per . ' ' . $persona->ape_pat_per . ' ' . $persona->ape_mat_per) }}
                                        — {{ $persona->cod_per }}
                                    </option>
                                @endforeach
                            </select>

                            @error('form.cod_per')
                                <p class="ui-error">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label class="ui-label">
                                Correo electrónico
                            </label>

                            <input type="email"
                                wire:model.live="form.email"
                                placeholder="usuario@gmail.com"
                                class="ui-input">

                            <template x-if="email && !emailValido">
                                <p class="ui-error">
                                    Ingresa un correo válido: gmail.com, hotmail.com, outlook.com o yahoo.com.
                                </p>
                            </template>

                            <template x-if="emailValido">
                                <p class="mt-2 text-sm font-medium" style="color: var(--ui-primary);">
                                    ✓ Correo válido.
                                </p>
                            </template>

                            @error('form.email')
                                <p class="ui-error">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label class="ui-label">
                                Rol del usuario
                            </label>

                            <select wire:model.live="form.role" class="ui-select">
                                <option value="">Selecciona un rol</option>
                                @foreach ($rolesDisponibles as $role)
                                    <option value="{{ $role->name }}">{{ $role->name }}</option>
                                @endforeach
                            </select>

                            @error('form.role')
                                <p class="ui-error">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="md:col-span-2"
                            x-data="{
                                showPassword: false,
                                showConfirm: false,
                                passwordLocal: @entangle('form.password').live,
                                confirmationLocal: @entangle('form.password_confirmation').live,

                                get hasLength() { return (this.passwordLocal ?? '').length >= 8 },
                                get hasUpper() { return /[A-Z]/.test(this.passwordLocal ?? '') },
                                get hasLower() { return /[a-z]/.test(this.passwordLocal ?? '') },
                                get hasNumber() { return /[0-9]/.test(this.passwordLocal ?? '') },
                                get hasSymbol() { return /[^A-Za-z0-9]/.test(this.passwordLocal ?? '') },
                                get matches() { return this.passwordLocal && this.passwordLocal === this.confirmationLocal },
                                get isValid() {
                                    return this.hasLength && this.hasUpper && this.hasLower && this.hasNumber && this.hasSymbol && this.matches
                                }
                            }">

                            <div class="grid gap-5 md:grid-cols-2">
                                {{-- CONTRASEÑA --}}
                                <div>
                                    <label class="ui-label">
                                        Contraseña
                                    </label>

                                    <div class="relative">
                                        <input :type="showPassword ? 'text' : 'password'"
                                            wire:model.live="form.password"
                                            placeholder="Ingresa una contraseña segura"
                                            class="ui-input pr-12">

                                        <button type="button"
                                            @click="showPassword = !showPassword"
                                            class="absolute inset-y-0 right-3 flex items-center transition"
                                            style="color: var(--ui-muted);">
                                            <svg x-show="!showPassword" xmlns="http://www.w3.org/2000/svg" class="h-5 w-5"
                                                fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                                                <path stroke-linecap="round" stroke-linejoin="round"
                                                    d="M2.25 12s3.75-6.75 9.75-6.75S21.75 12 21.75 12s-3.75 6.75-9.75 6.75S2.25 12 2.25 12Z" />
                                                <path stroke-linecap="round" stroke-linejoin="round"
                                                    d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" />
                                            </svg>

                                            <svg x-show="showPassword" x-cloak xmlns="http://www.w3.org/2000/svg"
                                                class="h-5 w-5" fill="none" viewBox="0 0 24 24"
                                                stroke="currentColor" stroke-width="1.8">
                                                <path stroke-linecap="round" stroke-linejoin="round"
                                                    d="M3 3l18 18M10.58 10.58A2 2 0 0 0 12 14a2 2 0 0 0 1.42-.58M9.88 5.23A9.8 9.8 0 0 1 12 5c6 0 9.75 7 9.75 7a17.8 17.8 0 0 1-3.23 4.12M6.54 6.54C3.82 8.29 2.25 12 2.25 12s3.75 7 9.75 7c1.33 0 2.56-.32 3.67-.84" />
                                            </svg>
                                        </button>
                                    </div>

                                    @error('form.password')
                                        <p class="ui-error">{{ $message }}</p>
                                    @enderror
                                </div>

                                {{-- CONFIRMAR CONTRASEÑA --}}
                                <div>
                                    <label class="ui-label">
                                        Confirmar contraseña
                                    </label>

                                    <div class="relative">
                                        <input :type="showConfirm ? 'text' : 'password'"
                                            wire:model.live="form.password_confirmation"
                                            placeholder="Repite la contraseña"
                                            class="ui-input pr-12">

                                        <button type="button"
                                            @click="showConfirm = !showConfirm"
                                            class="absolute inset-y-0 right-3 flex items-center transition"
                                            style="color: var(--ui-muted);">
                                            <svg x-show="!showConfirm" xmlns="http://www.w3.org/2000/svg" class="h-5 w-5"
                                                fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                                                <path stroke-linecap="round" stroke-linejoin="round"
                                                    d="M2.25 12s3.75-6.75 9.75-6.75S21.75 12 21.75 12s-3.75 6.75-9.75 6.75S2.25 12 2.25 12Z" />
                                                <path stroke-linecap="round" stroke-linejoin="round"
                                                    d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" />
                                            </svg>

                                            <svg x-show="showConfirm" x-cloak xmlns="http://www.w3.org/2000/svg"
                                                class="h-5 w-5" fill="none" viewBox="0 0 24 24"
                                                stroke="currentColor" stroke-width="1.8">
                                                <path stroke-linecap="round" stroke-linejoin="round"
                                                    d="M3 3l18 18M10.58 10.58A2 2 0 0 0 12 14a2 2 0 0 0 1.42-.58M9.88 5.23A9.8 9.8 0 0 1 12 5c6 0 9.75 7 9.75 7a17.8 17.8 0 0 1-3.23 4.12M6.54 6.54C3.82 8.29 2.25 12 2.25 12s3.75 7 9.75 7c1.33 0 2.56-.32 3.67-.84" />
                                            </svg>
                                        </button>
                                    </div>
                                </div>
                            </div>

                            {{-- VALIDACIONES VISUALES --}}
                            <div class="ui-card-soft mt-4 p-4">
                                <p class="mb-3 text-sm font-semibold" style="color: var(--ui-text-soft);">
                                    La contraseña debe contener:
                                </p>

                                <div class="grid gap-2 text-sm sm:grid-cols-2">
                                    <p :style="hasLength ? 'color: var(--ui-primary)' : 'color: var(--ui-muted)'">✓ Mínimo 8 caracteres</p>
                                    <p :style="hasUpper ? 'color: var(--ui-primary)' : 'color: var(--ui-muted)'">✓ Una letra mayúscula</p>
                                    <p :style="hasLower ? 'color: var(--ui-primary)' : 'color: var(--ui-muted)'">✓ Una letra minúscula</p>
                                    <p :style="hasNumber ? 'color: var(--ui-primary)' : 'color: var(--ui-muted)'">✓ Un número</p>
                                    <p :style="hasSymbol ? 'color: var(--ui-primary)' : 'color: var(--ui-muted)'">✓ Un símbolo especial</p>
                                    <p :style="matches ? 'color: var(--ui-primary)' : 'color: var(--ui-muted)'">✓ Las contraseñas coinciden</p>
                                </div>
                            </div>
                        </div>

                        @if (array_key_exists('est_usu', $form))
                            <div class="md:col-span-2">
                                <label class="ui-label">
                                    Estado inicial
                                </label>

                                <select wire:model.live="form.est_usu" class="ui-select">
                                    <option value="ACTIVO">Activo</option>
                                    <option value="INACTIVO">Inactivo</option>
                                </select>

                                @error('form.est_usu')
                                    <p class="ui-error">{{ $message }}</p>
                                @enderror
                            </div>
                        @endif
                    </div>
                </div>

                <div class="ui-modal-footer flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-end">
                    <p x-show="!formularioValido" x-cloak class="text-sm font-medium sm:mr-auto"
                        style="color: var(--ui-danger);">
                        Completa todos los campos correctamente para guardar el usuario.
                    </p>

                    <button type="button" wire:click="cerrarModalCrear" class="ui-btn-secondary">
                        Cancelar
                    </button>

                    <button type="button"
                        wire:click="guardarUsuario"
                        :disabled="!formularioValido"
                        :class="formularioValido
                            ? 'ui-btn-primary'
                            : 'ui-btn cursor-not-allowed bg-slate-300 text-slate-500 shadow-none'"
                        class="rounded-2xl px-5 py-3 text-sm font-semibold transition">
                        Guardar usuario
                    </button>
                </div>
            </div>
        </div>
    @endif

    {{-- MODAL VER USUARIO --}}
    @if ($modalVer && $usuarioDetalle)
        <div wire:key="modal-ver-usuario-{{ $usuarioDetalle->cod_usu }}"
            class="fixed inset-0 z-50 flex items-center justify-center px-4 py-6 sm:px-6">
            <div class="ui-modal-backdrop" wire:click="cerrarModalVer"></div>

            <div class="ui-modal w-full max-w-2xl">
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

                        <button type="button" wire:click="cerrarModalVer"
                            class="rounded-2xl bg-white/10 p-2 text-white transition hover:bg-white/20">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none"
                                viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" />
                            </svg>
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

                    <div class="ui-card-soft mb-5 flex items-center gap-4 p-4">
                        <div class="flex h-16 w-16 items-center justify-center rounded-2xl text-2xl font-black ring-1"
                            style="background: linear-gradient(135deg, var(--ui-primary-soft), var(--ui-info-soft)); color: var(--ui-text-soft); --tw-ring-color: var(--ui-border);">
                            {{ $inicialDetalle }}
                        </div>

                        <div class="min-w-0">
                            <h4 class="truncate text-lg font-black" style="color: var(--ui-text);">
                                {{ $nombreDetalle ?: 'Usuario sin nombre' }}
                            </h4>
                            <p class="mt-1 truncate text-sm" style="color: var(--ui-muted);">
                                {{ $usuarioDetalle->email }}
                            </p>
                        </div>
                    </div>

                    <div class="grid gap-4 sm:grid-cols-2">
                        <div class="ui-card-soft p-4">
                            <p class="text-xs font-semibold uppercase tracking-[0.14em]" style="color: var(--ui-muted);">
                                Estado
                            </p>

                            @if ($esActivoDetalle)
                                <span class="ui-badge-success mt-2">
                                    <span class="h-2 w-2 rounded-full" style="background: var(--ui-primary);"></span>
                                    Activo
                                </span>
                            @else
                                <span class="ui-badge-danger mt-2">
                                    <span class="h-2 w-2 rounded-full" style="background: var(--ui-danger);"></span>
                                    Inactivo
                                </span>
                            @endif
                        </div>

                        <div class="ui-card-soft p-4">
                            <p class="text-xs font-semibold uppercase tracking-[0.14em]" style="color: var(--ui-muted);">
                                Rol
                            </p>
                            <p class="mt-2 font-bold" style="color: var(--ui-text);">
                                {{ $rolDetalle }}
                            </p>
                        </div>

                        <div class="ui-card-soft p-4">
                            <p class="text-xs font-semibold uppercase tracking-[0.14em]" style="color: var(--ui-muted);">
                                Correo electrónico
                            </p>
                            <p class="mt-2 break-all font-bold" style="color: var(--ui-text);">
                                {{ $usuarioDetalle->email }}
                            </p>
                        </div>

                        <div class="ui-card-soft p-4">
                            <p class="text-xs font-semibold uppercase tracking-[0.14em]" style="color: var(--ui-muted);">
                                Fecha de registro
                            </p>
                            <p class="mt-2 font-bold" style="color: var(--ui-text);">
                                {{ $usuarioDetalle->created_at?->format('d/m/Y H:i') ?? 'Sin fecha' }}
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

    {{-- MODAL EDITAR USUARIO --}}
    @if ($modalEditar)
        <div wire:key="modal-editar-usuario" class="fixed inset-0 z-50 flex items-center justify-center px-4 py-6 sm:px-6">
            <div class="ui-modal-backdrop" wire:click="cerrarModalEditar"></div>

            <div
                x-data="{
                    showPassword: false,
                    showConfirm: false,

                    email: @entangle('formEditar.email').live,
                    role: @entangle('formEditar.role').live,
                    estado: @entangle('formEditar.est_usu').live,
                    password: @entangle('formEditar.password').live,
                    confirmPassword: @entangle('formEditar.password_confirmation').live,

                    get emailValido() {
                        return /^[a-zA-Z0-9._%+-]+@(gmail\.com|hotmail\.com|outlook\.com|yahoo\.com)$/.test(this.email ?? '')
                    },

                    get rolValido() {
                        return this.role !== null && this.role !== ''
                    },

                    get estadoValido() {
                        return this.estado !== null && this.estado !== ''
                    },

                    get passwordVacia() {
                        return !this.password || this.password.length === 0
                    },

                    get tieneMinimo() {
                        return (this.password ?? '').length >= 8
                    },

                    get tieneMayuscula() {
                        return /[A-Z]/.test(this.password ?? '')
                    },

                    get tieneMinuscula() {
                        return /[a-z]/.test(this.password ?? '')
                    },

                    get tieneNumero() {
                        return /[0-9]/.test(this.password ?? '')
                    },

                    get tieneSimbolo() {
                        return /[^A-Za-z0-9]/.test(this.password ?? '')
                    },

                    get passwordSegura() {
                        if (this.passwordVacia) return true

                        return this.tieneMinimo
                            && this.tieneMayuscula
                            && this.tieneMinuscula
                            && this.tieneNumero
                            && this.tieneSimbolo
                    },

                    get passwordsCoinciden() {
                        if (this.passwordVacia) return true

                        return this.password === this.confirmPassword
                    },

                    get formularioValido() {
                        return this.emailValido
                            && this.rolValido
                            && this.estadoValido
                            && this.passwordSegura
                            && this.passwordsCoinciden
                    }
                }"
                class="ui-modal w-full max-w-2xl">

                <div class="bg-gradient-to-r from-emerald-600 to-sky-600 px-6 py-5 text-white">
                    <div class="flex items-start justify-between gap-4">
                        <div>
                            <p class="text-xs font-semibold uppercase tracking-[0.18em] text-white/80">
                                Edición de cuenta
                            </p>
                            <h3 class="mt-2 text-2xl font-black">
                                Editar usuario
                            </h3>
                            <p class="mt-2 text-sm text-white/90">
                                Actualiza los datos de acceso del usuario seleccionado.
                            </p>
                        </div>

                        <button type="button" wire:click="cerrarModalEditar"
                            class="rounded-2xl bg-white/10 p-2 text-white transition hover:bg-white/20">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none"
                                viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
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

                        {{-- CORREO --}}
                        <div>
                            <label class="ui-label">
                                Correo electrónico
                            </label>

                            <input type="email"
                                wire:model.live="formEditar.email"
                                placeholder="usuario@gmail.com"
                                class="ui-input"
                                :style="email && !emailValido
                                    ? 'border-color: var(--ui-danger); box-shadow: 0 0 0 4px rgba(220, 38, 38, 0.10);'
                                    : emailValido
                                        ? 'border-color: var(--ui-primary);'
                                        : ''">

                            <template x-if="email && !emailValido">
                                <p class="ui-error">
                                    Ingresa un correo válido: gmail.com, hotmail.com, outlook.com o yahoo.com.
                                </p>
                            </template>

                            <template x-if="emailValido">
                                <p class="mt-2 text-sm font-medium" style="color: var(--ui-primary);">
                                    ✓ Correo válido.
                                </p>
                            </template>

                            @error('formEditar.email')
                                <p class="ui-error">{{ $message }}</p>
                            @enderror
                        </div>

                        {{-- ROL --}}
                        <div>
                            <label class="ui-label">
                                Rol del usuario
                            </label>

                            <select wire:model.live="formEditar.role" class="ui-select">
                                <option value="">Selecciona un rol</option>
                                @foreach ($rolesDisponibles as $role)
                                    <option value="{{ $role->name }}">{{ $role->name }}</option>
                                @endforeach
                            </select>

                            <template x-if="!rolValido">
                                <p class="ui-error">
                                    Debes seleccionar un rol.
                                </p>
                            </template>

                            @error('formEditar.role')
                                <p class="ui-error">{{ $message }}</p>
                            @enderror
                        </div>

                        {{-- ESTADO --}}
                        <div class="md:col-span-2">
                            <label class="ui-label">
                                Estado de cuenta
                            </label>

                            <select wire:model.live="formEditar.est_usu" class="ui-select">
                                <option value="ACTIVO">Activo</option>
                                <option value="INACTIVO">Inactivo</option>
                            </select>

                            @error('formEditar.est_usu')
                                <p class="ui-error">{{ $message }}</p>
                            @enderror
                        </div>

                        {{-- NUEVA CONTRASEÑA --}}
                        <div>
                            <label class="ui-label">
                                Nueva contraseña
                            </label>

                            <div class="relative">
                                <input :type="showPassword ? 'text' : 'password'"
                                    wire:model.live="formEditar.password"
                                    placeholder="Dejar vacío si no deseas cambiarla"
                                    class="ui-input pr-12"
                                    :style="passwordVacia
                                        ? ''
                                        : passwordSegura
                                            ? 'border-color: var(--ui-primary);'
                                            : 'border-color: var(--ui-danger); box-shadow: 0 0 0 4px rgba(220, 38, 38, 0.10);'">

                                <button type="button"
                                    @click="showPassword = !showPassword"
                                    class="absolute inset-y-0 right-3 flex items-center transition"
                                    style="color: var(--ui-muted);">
                                    <svg x-show="!showPassword" xmlns="http://www.w3.org/2000/svg"
                                        class="h-5 w-5" fill="none" viewBox="0 0 24 24"
                                        stroke="currentColor" stroke-width="1.8">
                                        <path stroke-linecap="round" stroke-linejoin="round"
                                            d="M2.25 12s3.75-6.75 9.75-6.75S21.75 12 21.75 12s-3.75 6.75-9.75 6.75S2.25 12 2.25 12Z" />
                                        <path stroke-linecap="round" stroke-linejoin="round"
                                            d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" />
                                    </svg>

                                    <svg x-show="showPassword" x-cloak xmlns="http://www.w3.org/2000/svg"
                                        class="h-5 w-5" fill="none" viewBox="0 0 24 24"
                                        stroke="currentColor" stroke-width="1.8">
                                        <path stroke-linecap="round" stroke-linejoin="round"
                                            d="M3 3l18 18M10.58 10.58A2 2 0 0 0 12 14a2 2 0 0 0 1.42-.58M9.88 5.23A9.8 9.8 0 0 1 12 5c6 0 9.75 7 9.75 7a17.8 17.8 0 0 1-3.23 4.12M6.54 6.54C3.82 8.29 2.25 12 2.25 12s3.75 7 9.75 7c1.33 0 2.56-.32 3.67-.84" />
                                    </svg>
                                </button>
                            </div>

                            @error('formEditar.password')
                                <p class="ui-error">{{ $message }}</p>
                            @enderror
                        </div>

                        {{-- CONFIRMAR CONTRASEÑA --}}
                        <div>
                            <label class="ui-label">
                                Confirmar nueva contraseña
                            </label>

                            <div class="relative">
                                <input :type="showConfirm ? 'text' : 'password'"
                                    wire:model.live="formEditar.password_confirmation"
                                    placeholder="Repite la nueva contraseña"
                                    :disabled="passwordVacia"
                                    class="ui-input pr-12 disabled:cursor-not-allowed disabled:opacity-60"
                                    :style="passwordVacia
                                        ? ''
                                        : passwordsCoinciden
                                            ? 'border-color: var(--ui-primary);'
                                            : 'border-color: var(--ui-danger); box-shadow: 0 0 0 4px rgba(220, 38, 38, 0.10);'">

                                <button type="button"
                                    @click="showConfirm = !showConfirm"
                                    :disabled="passwordVacia"
                                    class="absolute inset-y-0 right-3 flex items-center transition disabled:cursor-not-allowed disabled:opacity-40"
                                    style="color: var(--ui-muted);">
                                    <svg x-show="!showConfirm" xmlns="http://www.w3.org/2000/svg"
                                        class="h-5 w-5" fill="none" viewBox="0 0 24 24"
                                        stroke="currentColor" stroke-width="1.8">
                                        <path stroke-linecap="round" stroke-linejoin="round"
                                            d="M2.25 12s3.75-6.75 9.75-6.75S21.75 12 21.75 12s-3.75 6.75-9.75 6.75S2.25 12 2.25 12Z" />
                                        <path stroke-linecap="round" stroke-linejoin="round"
                                            d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" />
                                    </svg>

                                    <svg x-show="showConfirm" x-cloak xmlns="http://www.w3.org/2000/svg"
                                        class="h-5 w-5" fill="none" viewBox="0 0 24 24"
                                        stroke="currentColor" stroke-width="1.8">
                                        <path stroke-linecap="round" stroke-linejoin="round"
                                            d="M3 3l18 18M10.58 10.58A2 2 0 0 0 12 14a2 2 0 0 0 1.42-.58M9.88 5.23A9.8 9.8 0 0 1 12 5c6 0 9.75 7 9.75 7a17.8 17.8 0 0 1-3.23 4.12M6.54 6.54C3.82 8.29 2.25 12 2.25 12s3.75 7 9.75 7c1.33 0 2.56-.32 3.67-.84" />
                                    </svg>
                                </button>
                            </div>

                            <template x-if="!passwordVacia && !passwordsCoinciden">
                                <p class="ui-error">
                                    Las contraseñas no coinciden.
                                </p>
                            </template>
                        </div>

                        {{-- VALIDACIONES VISUALES --}}
                        <div class="ui-card-soft md:col-span-2 p-4">
                            <p class="mb-3 text-sm font-semibold" style="color: var(--ui-text-soft);">
                                Validación del formulario
                            </p>

                            <div class="grid gap-2 text-sm sm:grid-cols-2">
                                <p :style="emailValido ? 'color: var(--ui-primary)' : 'color: var(--ui-muted)'">
                                    ✓ Correo electrónico válido
                                </p>

                                <p :style="rolValido ? 'color: var(--ui-primary)' : 'color: var(--ui-muted)'">
                                    ✓ Rol seleccionado
                                </p>

                                <p :style="estadoValido ? 'color: var(--ui-primary)' : 'color: var(--ui-muted)'">
                                    ✓ Estado de cuenta seleccionado
                                </p>

                                <p :style="passwordVacia || tieneMinimo ? 'color: var(--ui-primary)' : 'color: var(--ui-muted)'">
                                    ✓ Contraseña con mínimo 8 caracteres
                                </p>

                                <p :style="passwordVacia || tieneMayuscula ? 'color: var(--ui-primary)' : 'color: var(--ui-muted)'">
                                    ✓ Una letra mayúscula
                                </p>

                                <p :style="passwordVacia || tieneMinuscula ? 'color: var(--ui-primary)' : 'color: var(--ui-muted)'">
                                    ✓ Una letra minúscula
                                </p>

                                <p :style="passwordVacia || tieneNumero ? 'color: var(--ui-primary)' : 'color: var(--ui-muted)'">
                                    ✓ Un número
                                </p>

                                <p :style="passwordVacia || tieneSimbolo ? 'color: var(--ui-primary)' : 'color: var(--ui-muted)'">
                                    ✓ Un símbolo especial
                                </p>

                                <p :style="passwordsCoinciden ? 'color: var(--ui-primary)' : 'color: var(--ui-muted)'">
                                    ✓ Confirmación correcta
                                </p>
                            </div>

                            <p x-show="passwordVacia" class="mt-3 text-xs leading-5" style="color: var(--ui-muted);">
                                La contraseña es opcional al editar. Si no deseas cambiarla, deja ambos campos vacíos.
                            </p>
                        </div>
                    </div>
                </div>

                <div class="ui-modal-footer flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-end">
                    <p x-show="!formularioValido" x-cloak class="text-sm font-medium sm:mr-auto"
                        style="color: var(--ui-danger);">
                        Completa correctamente los datos para guardar los cambios.
                    </p>

                    <button type="button" wire:click="cerrarModalEditar" class="ui-btn-secondary">
                        Cancelar
                    </button>

                    <button type="button"
                        wire:click="guardarEdicionUsuario"
                        :disabled="!formularioValido"
                        :class="formularioValido
                            ? 'ui-btn-primary'
                            : 'ui-btn cursor-not-allowed bg-slate-300 text-slate-500 shadow-none'"
                        class="rounded-2xl px-5 py-3 text-sm font-semibold transition">
                        Guardar cambios
                    </button>
                </div>
            </div>
        </div>
    @endif
</div>