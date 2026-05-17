<div x-data="gestionPersonasPage()" x-init="init()"
    x-on:persona-creada.window="notify('success', 'Persona registrada', 'El registro fue creado correctamente.')"
    x-on:persona-actualizada.window="notify('success', 'Persona actualizada', 'Los datos fueron actualizados correctamente.')"
    x-on:persona-desactivada.window="notify('success', 'Registro desactivado', 'La persona quedó inactiva sin eliminación física.')"
    x-on:persona-reactivada.window="notify('success', 'Registro reactivado', 'La persona volvió a estar activa.')"
    x-on:error-general.window="notify('error', 'No se pudo completar', $event.detail?.mensaje || 'Revisa los datos e intenta nuevamente.')"
    x-on:success-general.window="notify('success', 'Acción completada', $event.detail?.mensaje || 'La acción fue realizada correctamente.')"
    class="space-y-6">

    @php
        $nombrePersona = function ($persona) {
            return trim(collect([
                $persona->nom_per ?? null,
                $persona->ape_pat_per ?? null,
                $persona->ape_mat_per ?? null,
            ])->filter()->implode(' '));
        };

        $inicialPersona = function ($persona) {
            return strtoupper(mb_substr($persona->nom_per ?? 'P', 0, 1));
        };

        $direccionPersona = function ($persona) {
            $partes = collect([
                $persona->cal_per ? 'Calle ' . $persona->cal_per : null,
                $persona->ave_per ? 'Av. ' . $persona->ave_per : null,
                $persona->zona_per ? 'Zona ' . $persona->zona_per : null,
                $persona->num_per ? '#' . $persona->num_per : null,
                $persona->ref_per ? 'Ref. ' . $persona->ref_per : null,
                $persona->ciu_per ? 'Ciudad ' . $persona->ciu_per : null,
                $persona->mun_per && $persona->mun_per !== $persona->ciu_per ? 'Municipio ' . $persona->mun_per : null,
                $persona->dep_per ? 'Departamento ' . $persona->dep_per : null,
            ])->filter()->implode(', ');

            return $partes ?: ($persona->dir_per ?: 'Sin dirección registrada');
        };

        $estadoAnalisisCrear = $analisisPersona['estado_inteligente'] ?? 'INCOMPLETO';
        $estadoAnalisisEditar = $analisisPersonaEditar['estado_inteligente'] ?? 'INCOMPLETO';

        $puedeContinuarCrear = ($analisisPersona['puede_continuar'] ?? true) === true;
        $puedeContinuarEditar = ($analisisPersonaEditar['puede_continuar'] ?? true) === true;

        $badgeRevision = function ($estado, $puedeContinuar = true) {
            if (!$puedeContinuar || $estado === 'BLOQUEADO') {
                return 'border-rose-200 bg-rose-50 text-rose-700 dark:border-rose-500/30 dark:bg-rose-950 dark:text-rose-300';
            }

            return match ($estado) {
                'VALIDO' => 'border-emerald-200 bg-emerald-50 text-emerald-700 dark:border-emerald-500/30 dark:bg-emerald-950 dark:text-emerald-300',
                'OBSERVADO' => 'border-amber-200 bg-amber-50 text-amber-700 dark:border-amber-500/30 dark:bg-amber-950 dark:text-amber-300',
                'RECUPERABLE' => 'border-violet-200 bg-violet-50 text-violet-700 dark:border-violet-500/30 dark:bg-violet-950 dark:text-violet-300',
                default => 'border-slate-200 bg-slate-50 text-slate-600 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-300',
            };
        };

        $pdfDisponible = method_exists($this, 'exportarPersonasPdf');
    @endphp

    <style>
        [x-cloak] {
            display: none !important;
        }

        select {
            -webkit-appearance: none;
            -moz-appearance: none;
            appearance: none;
            background-image: none !important;
        }

        select::-ms-expand {
            display: none;
        }

        .gp-appear {
            animation: gpAppear .28s ease-out both;
        }

        .gp-rise {
            animation: gpRise .32s cubic-bezier(.22, .75, .25, 1) both;
        }

        .gp-pop {
            animation: gpPop .24s cubic-bezier(.22, .75, .25, 1) both;
        }

        .gp-shimmer {
            position: relative;
            overflow: hidden;
        }

        .gp-shimmer::after {
            content: "";
            position: absolute;
            inset: 0;
            transform: translateX(-120%);
            background: linear-gradient(90deg, transparent, rgba(255, 255, 255, .14), transparent);
            animation: gpShimmer 2.6s ease-in-out infinite;
        }

        @keyframes gpAppear {
            from {
                opacity: 0;
            }

            to {
                opacity: 1;
            }
        }

        @keyframes gpRise {
            from {
                opacity: 0;
                transform: translateY(14px) scale(.985);
            }

            to {
                opacity: 1;
                transform: translateY(0) scale(1);
            }
        }

        @keyframes gpPop {
            from {
                opacity: 0;
                transform: translateY(10px) scale(.96);
            }

            to {
                opacity: 1;
                transform: translateY(0) scale(1);
            }
        }

        @keyframes gpShimmer {
            0% {
                transform: translateX(-120%);
            }

            55%,
            100% {
                transform: translateX(120%);
            }
        }

        .gp-scroll::-webkit-scrollbar {
            width: 10px;
        }

        .gp-scroll::-webkit-scrollbar-track {
            background: transparent;
        }

        .gp-scroll::-webkit-scrollbar-thumb {
            border-radius: 999px;
            background: color-mix(in srgb, var(--ui-muted) 35%, transparent);
            border: 3px solid transparent;
            background-clip: content-box;
        }

        .gp-overlay {
            background: rgba(15, 23, 42, .38);
            backdrop-filter: blur(14px);
        }

        .dark .gp-overlay {
            background: rgba(2, 6, 23, .72);
        }

        .gp-input-shell {
            background: var(--ui-surface);
            border-color: var(--ui-border);
            color: var(--ui-text);
            --tw-ring-color: var(--ui-ring);
        }

        .gp-input-shell:focus-within {
            box-shadow: 0 0 0 4px var(--ui-ring);
            border-color: var(--ui-primary-border);
        }

        .gp-view-btn {
            border-color: var(--ui-border);
            background: var(--ui-surface);
            color: var(--ui-muted);
        }

        .gp-view-btn-active {
            border-color: var(--ui-primary-border);
            background: var(--ui-primary-soft);
            color: var(--ui-primary);
            box-shadow: 0 12px 28px rgba(15, 23, 42, .08);
        }
    </style>

    {{-- CABECERA INSTITUCIONAL --}}
    <section class="ui-card relative overflow-hidden rounded-[2rem] p-5 gp-rise">
        <div class="pointer-events-none absolute -left-24 -top-24 h-72 w-72 rounded-full blur-3xl"
            style="background: color-mix(in srgb, var(--ui-primary) 16%, transparent);"></div>
        <div class="pointer-events-none absolute -right-24 -bottom-24 h-72 w-72 rounded-full blur-3xl"
            style="background: color-mix(in srgb, var(--ui-info) 14%, transparent);"></div>

        <div class="relative flex flex-col gap-5 xl:flex-row xl:items-center xl:justify-between">
            <div>
                <div class="inline-flex items-center gap-2 rounded-full px-3 py-1 ring-1"
                    style="background: var(--ui-primary-soft); color: var(--ui-primary); --tw-ring-color: var(--ui-primary-border);">
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.8">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M15 9h3.75M15 12h3.75M15 15h3.75M4.5 19.5h15A1.5 1.5 0 0 0 21 18V6a1.5 1.5 0 0 0-1.5-1.5h-15A1.5 1.5 0 0 0 3 6v12a1.5 1.5 0 0 0 1.5 1.5Z" />
                    </svg>
                    <span class="text-xs font-black uppercase tracking-[0.16em]">
                        Registro personal
                    </span>
                </div>

                <h1 class="mt-4 text-3xl font-black tracking-tight md:text-4xl" style="color: var(--ui-text);">
                    Gestión de Personas
                </h1>

                <p class="mt-2 max-w-4xl text-sm leading-7" style="color: var(--ui-muted);">
                    Administra datos personales, identificación, contacto, fotografía y dirección normalizada. La
                    asignación de usuario, rol y permisos se realiza posteriormente desde Gestión de Usuarios.
                </p>
            </div>

            <div class="flex flex-col gap-3 sm:flex-row">
                <button type="button" wire:click="abrirModalCrear" wire:loading.attr="disabled"
                    wire:target="abrirModalCrear"
                    class="ui-btn-primary justify-center disabled:cursor-wait disabled:opacity-60">
                    <svg wire:loading.remove wire:target="abrirModalCrear" class="h-5 w-5" fill="none"
                        stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.8">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                    </svg>
                    <svg wire:loading wire:target="abrirModalCrear" class="h-5 w-5 animate-spin" fill="none"
                        viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4">
                        </circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 0 1 8-8v4a4 4 0 0 0-4 4H4z"></path>
                    </svg>
                    Registrar persona
                </button>

                @if ($pdfDisponible)
                    <button type="button" wire:click="exportarPersonasPdf('listado')" wire:loading.attr="disabled"
                        wire:target="exportarPersonasPdf"
                        class="ui-btn-secondary justify-center disabled:cursor-wait disabled:opacity-60">
                        <svg wire:loading.remove wire:target="exportarPersonasPdf" class="h-5 w-5" fill="none"
                            stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.8">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M19.5 14.25v-2.625A3.375 3.375 0 0 0 16.125 8.25h-1.5A1.125 1.125 0 0 1 13.5 7.125v-1.5A3.375 3.375 0 0 0 10.125 2.25H8.25M8.25 15h7.5M8.25 18h4.5M3.75 4.5v15A2.25 2.25 0 0 0 6 21.75h12A2.25 2.25 0 0 0 20.25 19.5V9.75a4.5 4.5 0 0 0-4.5-4.5h-9.75A2.25 2.25 0 0 0 3.75 4.5Z" />
                        </svg>
                        <svg wire:loading wire:target="exportarPersonasPdf" class="h-5 w-5 animate-spin" fill="none"
                            viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4">
                            </circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 0 1 8-8v4a4 4 0 0 0-4 4H4z"></path>
                        </svg>
                        Exportar PDF
                    </button>
                @else
                    <button type="button" disabled class="ui-btn-secondary justify-center cursor-not-allowed opacity-60"
                        title="Agrega el método exportarPersonasPdf en el componente para habilitar esta acción.">
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.8">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M19.5 14.25v-2.625A3.375 3.375 0 0 0 16.125 8.25h-1.5A1.125 1.125 0 0 1 13.5 7.125v-1.5A3.375 3.375 0 0 0 10.125 2.25H8.25M8.25 15h7.5M8.25 18h4.5M3.75 4.5v15A2.25 2.25 0 0 0 6 21.75h12A2.25 2.25 0 0 0 20.25 19.5V9.75a4.5 4.5 0 0 0-4.5-4.5h-9.75A2.25 2.25 0 0 0 3.75 4.5Z" />
                        </svg>
                        Exportar PDF
                    </button>
                @endif
            </div>
        </div>
    </section>

    {{-- RESUMEN ORDENADO --}}
    <section class="grid gap-4 md:grid-cols-2 xl:grid-cols-4">
        <article class="ui-card rounded-[2rem] p-5 transition hover:-translate-y-1 hover:shadow-lg gp-rise">
            <div class="flex items-start justify-between gap-4">
                <div>
                    <p class="text-xs font-black uppercase tracking-[0.16em]" style="color: var(--ui-muted);">
                        Total registros
                    </p>
                    <h2 class="mt-3 text-4xl font-black" style="color: var(--ui-text);">
                        {{ $totalPersonas ?? 0 }}
                    </h2>
                    <p class="mt-2 text-xs leading-5" style="color: var(--ui-muted);">
                        Base general de identidad personal.
                    </p>
                </div>

                <div class="flex h-12 w-12 items-center justify-center rounded-2xl ring-1"
                    style="background: var(--ui-surface-soft); color: var(--ui-muted); --tw-ring-color: var(--ui-border);">
                    <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.8">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M15 19.128a9.38 9.38 0 0 0 2.625.372 4.875 4.875 0 0 0 0-9.75 9.38 9.38 0 0 0-2.625.372M4.875 19.5a4.875 4.875 0 0 1 0-9.75c.914 0 1.77.252 2.5.69m0 0a7.5 7.5 0 0 1 9.25 0m-9.25 0A7.5 7.5 0 0 0 12 21a7.5 7.5 0 0 0 4.625-1.56" />
                    </svg>
                </div>
            </div>
        </article>

        <article class="ui-card rounded-[2rem] p-5 transition hover:-translate-y-1 hover:shadow-lg gp-rise">
            <div class="flex items-start justify-between gap-4">
                <div>
                    <p class="text-xs font-black uppercase tracking-[0.16em]" style="color: var(--ui-primary);">
                        Activas
                    </p>
                    <h2 class="mt-3 text-4xl font-black" style="color: var(--ui-text);">
                        {{ $totalActivas ?? 0 }}
                    </h2>
                    <p class="mt-2 text-xs leading-5" style="color: var(--ui-muted);">
                        Disponibles para vinculación posterior.
                    </p>
                </div>

                <div class="flex h-12 w-12 items-center justify-center rounded-2xl ring-1"
                    style="background: var(--ui-primary-soft); color: var(--ui-primary); --tw-ring-color: var(--ui-primary-border);">
                    <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.8">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                    </svg>
                </div>
            </div>
        </article>

        <article class="ui-card rounded-[2rem] p-5 transition hover:-translate-y-1 hover:shadow-lg gp-rise">
            <div class="flex items-start justify-between gap-4">
                <div>
                    <p class="text-xs font-black uppercase tracking-[0.16em]" style="color: var(--ui-danger);">
                        Inactivas
                    </p>
                    <h2 class="mt-3 text-4xl font-black" style="color: var(--ui-text);">
                        {{ $totalInactivas ?? 0 }}
                    </h2>
                    <p class="mt-2 text-xs leading-5" style="color: var(--ui-muted);">
                        Conservadas para trazabilidad.
                    </p>
                </div>

                <div class="flex h-12 w-12 items-center justify-center rounded-2xl ring-1"
                    style="background: var(--ui-danger-soft); color: var(--ui-danger); --tw-ring-color: var(--ui-danger-border);">
                    <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.8">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M18 12H6m15 0a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                    </svg>
                </div>
            </div>
        </article>

        <article class="ui-card rounded-[2rem] p-5 transition hover:-translate-y-1 hover:shadow-lg gp-rise">
            <div class="flex items-start justify-between gap-4">
                <div>
                    <p class="text-xs font-black uppercase tracking-[0.16em]" style="color: var(--ui-warning);">
                        Sin usuario
                    </p>
                    <h2 class="mt-3 text-4xl font-black" style="color: var(--ui-text);">
                        {{ $totalSinUsuario ?? 0 }}
                    </h2>
                    <p class="mt-2 text-xs leading-5" style="color: var(--ui-muted);">
                        Pendientes de acceso si corresponde.
                    </p>
                </div>

                <div class="flex h-12 w-12 items-center justify-center rounded-2xl ring-1"
                    style="background: var(--ui-warning-soft); color: var(--ui-warning); --tw-ring-color: var(--ui-warning-border);">
                    <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.8">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M16.5 10.5V6.75a4.5 4.5 0 0 0-9 0v3.75M6.75 10.5h10.5A2.25 2.25 0 0 1 19.5 12.75v6A2.25 2.25 0 0 1 17.25 21H6.75A2.25 2.25 0 0 1 4.5 18.75v-6A2.25 2.25 0 0 1 6.75 10.5Z" />
                    </svg>
                </div>
            </div>
        </article>
    </section>

    {{-- GRÁFICOS --}}
    <section class="grid gap-4 xl:grid-cols-12">
        <div class="xl:col-span-4">
            <div wire:ignore class="ui-card rounded-[2rem] p-5 transition hover:-translate-y-1 hover:shadow-lg">
                <div class="flex items-center justify-between gap-3">
                    <div>
                        <p class="text-xs font-black uppercase tracking-[0.16em]" style="color: var(--ui-muted);">
                            Estado de registros
                        </p>
                        <h3 class="mt-1 text-lg font-black" style="color: var(--ui-text);">
                            Activas e inactivas
                        </h3>
                    </div>
                    <div class="flex h-11 w-11 items-center justify-center rounded-2xl ring-1"
                        style="background: var(--ui-primary-soft); color: var(--ui-primary); --tw-ring-color: var(--ui-primary-border);">
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.8">
                            <path stroke-linecap="round" stroke-linejoin="round"
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
            <div wire:ignore class="ui-card rounded-[2rem] p-5 transition hover:-translate-y-1 hover:shadow-lg">
                <div class="flex items-center justify-between gap-3">
                    <div>
                        <p class="text-xs font-black uppercase tracking-[0.16em]" style="color: var(--ui-muted);">
                            Distribución por género
                        </p>
                        <h3 class="mt-1 text-lg font-black" style="color: var(--ui-text);">
                            Datos demográficos
                        </h3>
                    </div>
                    <div class="flex h-11 w-11 items-center justify-center rounded-2xl ring-1"
                        style="background: var(--ui-violet-soft); color: var(--ui-violet); --tw-ring-color: var(--ui-violet-border);">
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.8">
                            <path stroke-linecap="round" stroke-linejoin="round"
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
            <div wire:ignore class="ui-card rounded-[2rem] p-5 transition hover:-translate-y-1 hover:shadow-lg">
                <div class="flex items-center justify-between gap-3">
                    <div>
                        <p class="text-xs font-black uppercase tracking-[0.16em]" style="color: var(--ui-muted);">
                            Cuentas de usuario
                        </p>
                        <h3 class="mt-1 text-lg font-black" style="color: var(--ui-text);">
                            Acceso al sistema
                        </h3>
                    </div>
                    <div class="flex h-11 w-11 items-center justify-center rounded-2xl ring-1"
                        style="background: var(--ui-info-soft); color: var(--ui-info); --tw-ring-color: var(--ui-info-border);">
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.8">
                            <path stroke-linecap="round" stroke-linejoin="round"
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

    {{-- FILTROS / ACCIONES --}}
    <section class="ui-card rounded-[2rem] p-5">
        <div class="grid gap-4 xl:grid-cols-12">
            <div class="xl:col-span-3">
                <label class="ui-label text-xs uppercase tracking-[0.14em]">Buscar persona</label>
                <div
                    class="gp-input-shell flex items-center gap-3 rounded-2xl border px-4 py-3 shadow-sm transition focus-within:ring-4">
                    <svg class="h-5 w-5 shrink-0" style="color: var(--ui-muted);" fill="none" stroke="currentColor"
                        viewBox="0 0 24 24" stroke-width="1.8">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="m21 21-4.35-4.35m1.85-5.15a7 7 0 1 1-14 0 7 7 0 0 1 14 0Z" />
                    </svg>
                    <input type="text" wire:model.live.debounce.400ms="search" placeholder="Nombre, CI, correo..."
                        class="w-full border-0 bg-transparent p-0 text-sm focus:outline-none focus:ring-0"
                        style="color: var(--ui-text);">
                </div>
            </div>

            <div class="xl:col-span-2">
                <label class="ui-label text-xs uppercase tracking-[0.14em]">Dirección</label>
                <div
                    class="gp-input-shell flex items-center gap-3 rounded-2xl border px-4 py-3 shadow-sm transition focus-within:ring-4">
                    <input type="text" wire:model.live.debounce.400ms="direccion" placeholder="Zona, calle, ciudad..."
                        class="w-full border-0 bg-transparent p-0 text-sm focus:outline-none focus:ring-0"
                        style="color: var(--ui-text);">
                </div>
            </div>

            <div class="xl:col-span-1">
                <label class="ui-label text-xs uppercase tracking-[0.14em]">Género</label>
                <div class="relative gp-input-shell rounded-2xl border shadow-sm transition focus-within:ring-4">
                    <select wire:model.live="genero"
                        class="block w-full appearance-none rounded-2xl border-0 bg-transparent px-4 py-3 text-sm font-bold outline-none focus:ring-0"
                        style="color: var(--ui-text);">
                        <option value="">Todos</option>
                        <option value="M">M</option>
                        <option value="F">F</option>
                    </select>
                    <div class="pointer-events-none absolute inset-y-0 right-3 flex items-center"
                        style="color: var(--ui-muted);">
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.8">
                            <path stroke-linecap="round" stroke-linejoin="round" d="m19 9-7 7-7-7" />
                        </svg>
                    </div>
                </div>
            </div>

            <div class="xl:col-span-1">
                <label class="ui-label text-xs uppercase tracking-[0.14em]">Estado</label>
                <div class="relative gp-input-shell rounded-2xl border shadow-sm transition focus-within:ring-4">
                    <select wire:model.live="estado"
                        class="block w-full appearance-none rounded-2xl border-0 bg-transparent px-4 py-3 text-sm font-bold outline-none focus:ring-0"
                        style="color: var(--ui-text);">
                        <option value="">Todos</option>
                        <option value="1">Activo</option>
                        <option value="0">Inactivo</option>
                    </select>
                    <div class="pointer-events-none absolute inset-y-0 right-3 flex items-center"
                        style="color: var(--ui-muted);">
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.8">
                            <path stroke-linecap="round" stroke-linejoin="round" d="m19 9-7 7-7-7" />
                        </svg>
                    </div>
                </div>
            </div>

            <div class="xl:col-span-2">
                <label class="ui-label text-xs uppercase tracking-[0.14em]">Cuenta</label>
                <div class="relative gp-input-shell rounded-2xl border shadow-sm transition focus-within:ring-4">
                    <select wire:model.live="cuentaUsuario"
                        class="block w-full appearance-none rounded-2xl border-0 bg-transparent px-4 py-3 text-sm font-bold outline-none focus:ring-0"
                        style="color: var(--ui-text);">
                        <option value="">Todas</option>
                        <option value="con_usuario">Con usuario</option>
                        <option value="sin_usuario">Sin usuario</option>
                    </select>
                    <div class="pointer-events-none absolute inset-y-0 right-3 flex items-center"
                        style="color: var(--ui-muted);">
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.8">
                            <path stroke-linecap="round" stroke-linejoin="round" d="m19 9-7 7-7-7" />
                        </svg>
                    </div>
                </div>
            </div>

            <div class="xl:col-span-3">
                <label class="ui-label text-xs uppercase tracking-[0.14em]">Tipo de vista</label>
                <div class="grid grid-cols-4 gap-2">
                    <button type="button" x-on:click="setView('tabla')"
                        class="gp-view-btn rounded-2xl border px-3 py-3 text-xs font-black transition hover:-translate-y-0.5"
                        x-bind:class="view === 'tabla' ? 'gp-view-btn-active' : ''">
                        Tabla
                    </button>
                    <button type="button" x-on:click="setView('tarjetas')"
                        class="gp-view-btn rounded-2xl border px-3 py-3 text-xs font-black transition hover:-translate-y-0.5"
                        x-bind:class="view === 'tarjetas' ? 'gp-view-btn-active' : ''">
                        Tarjetas
                    </button>
                    <button type="button" x-on:click="setView('fotos')"
                        class="gp-view-btn rounded-2xl border px-3 py-3 text-xs font-black transition hover:-translate-y-0.5"
                        x-bind:class="view === 'fotos' ? 'gp-view-btn-active' : ''">
                        Fotos
                    </button>
                    <button type="button" x-on:click="setView('compacta')"
                        class="gp-view-btn rounded-2xl border px-3 py-3 text-xs font-black transition hover:-translate-y-0.5"
                        x-bind:class="view === 'compacta' ? 'gp-view-btn-active' : ''">
                        Compacta
                    </button>
                </div>
            </div>
        </div>

        <div class="mt-4 flex flex-col gap-3 xl:flex-row xl:items-center xl:justify-between">
            <div class="flex flex-wrap gap-2">
                <span class="rounded-full border px-3 py-1 text-xs font-black"
                    style="background: var(--ui-surface-soft); border-color: var(--ui-border); color: var(--ui-muted);">
                    {{ $personas->total() }} resultados
                </span>
                <span class="rounded-full border px-3 py-1 text-xs font-black"
                    style="background: var(--ui-primary-soft); border-color: var(--ui-primary-border); color: var(--ui-primary);">
                    {{ $totalActivas ?? 0 }} activas
                </span>
                <span class="rounded-full border px-3 py-1 text-xs font-black"
                    style="background: var(--ui-danger-soft); border-color: var(--ui-danger-border); color: var(--ui-danger);">
                    {{ $totalInactivas ?? 0 }} inactivas
                </span>
                <span class="rounded-full border px-3 py-1 text-xs font-black"
                    style="background: var(--ui-warning-soft); border-color: var(--ui-warning-border); color: var(--ui-warning);">
                    {{ $totalSinUsuario ?? 0 }} sin usuario
                </span>
            </div>

            <div class="flex flex-wrap gap-2">
                @if ($pdfDisponible)
                    <button type="button" wire:click="exportarPersonasPdf('filtrado')" wire:loading.attr="disabled"
                        wire:target="exportarPersonasPdf" class="ui-btn-secondary disabled:cursor-wait disabled:opacity-60">
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.8">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M6.75 3h7.5L19.5 8.25v12A.75.75 0 0 1 18.75 21H5.25a.75.75 0 0 1-.75-.75V3.75A.75.75 0 0 1 5.25 3h1.5Z" />
                        </svg>
                        PDF filtrado
                    </button>
                @endif

                <button type="button" wire:click="limpiarFiltros" class="ui-btn-secondary">
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.8">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" />
                    </svg>
                    Limpiar filtros
                </button>
            </div>
        </div>
    </section>

    {{-- CARGA --}}
    <div wire:loading.flex wire:target="search,genero,estado,cuentaUsuario,direccion,limpiarFiltros"
        class="items-center gap-3 rounded-2xl border px-5 py-3 text-sm font-black gp-appear"
        style="background: var(--ui-surface); border-color: var(--ui-border); color: var(--ui-muted);">
        <svg class="h-4 w-4 animate-spin" fill="none" viewBox="0 0 24 24">
            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 0 1 8-8v4a4 4 0 0 0-4 4H4z"></path>
        </svg>
        Actualizando información...
    </div>

    {{-- VISTA TABLA --}}
    <section x-show="view === 'tabla'" x-cloak x-transition.opacity.duration.200ms class="ui-table-wrap gp-rise">
        <div class="overflow-x-auto">
            <table class="ui-table">
                <thead>
                    <tr>
                        <th>Persona</th>
                        <th>Identificación</th>
                        <th>Edad / género</th>
                        <th>Contacto</th>
                        <th>Dirección</th>
                        <th>Cuenta</th>
                        <th>Estado</th>
                        <th class="text-center">Acciones</th>
                    </tr>
                </thead>

                <tbody>
                    @forelse ($personas as $persona)
                        @php
                            $nombreCompleto = $nombrePersona($persona);
                            $estadoPersona = (bool) $persona->est_per;
                            $tieneUsuario = isset($persona->usuario);
                            $edad = $persona->fec_nac_per ? \Carbon\Carbon::parse($persona->fec_nac_per)->age : null;
                        @endphp

                        <tr wire:key="tabla-persona-{{ $persona->cod_per }}"
                            class="{{ !$estadoPersona ? 'opacity-70' : '' }}">
                            <td>
                                <div class="flex items-center gap-3">
                                    <div class="h-12 w-12 overflow-hidden rounded-2xl ring-1"
                                        style="background: var(--ui-surface-soft); --tw-ring-color: var(--ui-border);">
                                        @if ($persona->fot_per)
                                            <img src="{{ asset('storage/' . $persona->fot_per) }}"
                                                class="h-full w-full object-cover" alt="Foto de {{ $nombreCompleto }}">
                                        @else
                                            <div class="flex h-full w-full items-center justify-center text-sm font-black"
                                                style="background: linear-gradient(135deg, var(--ui-primary-soft), var(--ui-info-soft)); color: var(--ui-text-soft);">
                                                {{ $inicialPersona($persona) }}
                                            </div>
                                        @endif
                                    </div>

                                    <div class="min-w-0">
                                        <p class="truncate text-sm font-black" style="color: var(--ui-text);">
                                            {{ $nombreCompleto ?: 'Persona sin nombre' }}
                                        </p>
                                        <p class="truncate text-xs" style="color: var(--ui-muted);">
                                            Registro personal
                                        </p>
                                    </div>
                                </div>
                            </td>

                            <td>
                                <p class="text-sm font-black" style="color: var(--ui-text-soft);">
                                    {{ $persona->ci_per }}
                                    @if ($persona->com_per)
                                        - {{ $persona->com_per }}
                                    @endif
                                </p>
                                <p class="text-xs" style="color: var(--ui-muted);">
                                    Exp. {{ $persona->exp_per ?? '—' }}
                                </p>
                            </td>

                            <td>
                                <p class="text-sm font-bold" style="color: var(--ui-text-soft);">
                                    {{ $edad ? $edad . ' años' : 'Sin edad' }}
                                </p>

                                @if ($persona->gen_per === 'M')
                                    <span class="ui-badge-info mt-1">Masculino</span>
                                @elseif ($persona->gen_per === 'F')
                                    <span class="ui-badge-violet mt-1">Femenino</span>
                                @else
                                    <span class="ui-badge-muted mt-1">No definido</span>
                                @endif
                            </td>

                            <td>
                                <p class="text-sm font-semibold" style="color: var(--ui-text-soft);">
                                    {{ $persona->tel_per ?: 'Sin teléfono' }}
                                </p>
                                <p class="text-xs" style="color: var(--ui-muted);">
                                    {{ $persona->ema_per ?: 'Sin correo' }}
                                </p>
                            </td>

                            <td>
                                <p class="line-clamp-2 max-w-[280px] text-xs font-semibold leading-5"
                                    style="color: var(--ui-muted);">
                                    {{ $direccionPersona($persona) }}
                                </p>
                            </td>

                            <td>
                                @if ($tieneUsuario)
                                    <span class="ui-badge-success">Con usuario</span>
                                @else
                                    <span class="ui-badge-warning">Sin usuario</span>
                                @endif
                            </td>

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

                            <td>
                                <div class="flex items-center justify-center gap-1.5">
                                    <button type="button" wire:click="abrirModalVer('{{ $persona->cod_per }}')"
                                        wire:loading.attr="disabled" wire:target="abrirModalVer('{{ $persona->cod_per }}')"
                                        class="ui-icon-btn disabled:cursor-wait disabled:opacity-60" title="Ver detalle">
                                        <svg wire:loading.remove wire:target="abrirModalVer('{{ $persona->cod_per }}')"
                                            class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"
                                            stroke-width="1.8">
                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                d="M2.036 12.322a1 1 0 0 1 0-.644C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.01 9.963 7.178a1 1 0 0 1 0 .644C20.577 16.49 16.639 19.5 12 19.5c-4.638 0-8.573-3.01-9.964-7.178Z" />
                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" />
                                        </svg>
                                        <svg wire:loading wire:target="abrirModalVer('{{ $persona->cod_per }}')"
                                            class="h-5 w-5 animate-spin" fill="none" viewBox="0 0 24 24">
                                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor"
                                                stroke-width="4"></circle>
                                            <path class="opacity-75" fill="currentColor"
                                                d="M4 12a8 8 0 0 1 8-8v4a4 4 0 0 0-4 4H4z"></path>
                                        </svg>
                                    </button>

                                    <button type="button" wire:click="abrirModalEditar('{{ $persona->cod_per }}')"
                                        wire:loading.attr="disabled"
                                        wire:target="abrirModalEditar('{{ $persona->cod_per }}')"
                                        class="ui-icon-btn disabled:cursor-wait disabled:opacity-60" title="Editar">
                                        <svg wire:loading.remove wire:target="abrirModalEditar('{{ $persona->cod_per }}')"
                                            class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"
                                            stroke-width="1.8">
                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                d="m16.862 4.487 1.687-1.688a2.25 2.25 0 1 1 3.182 3.182L10.582 17.13a4.5 4.5 0 0 1-1.897 1.13L6 19l.74-2.685a4.5 4.5 0 0 1 1.13-1.897L16.862 4.487Z" />
                                        </svg>
                                        <svg wire:loading wire:target="abrirModalEditar('{{ $persona->cod_per }}')"
                                            class="h-5 w-5 animate-spin" fill="none" viewBox="0 0 24 24">
                                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor"
                                                stroke-width="4"></circle>
                                            <path class="opacity-75" fill="currentColor"
                                                d="M4 12a8 8 0 0 1 8-8v4a4 4 0 0 0-4 4H4z"></path>
                                        </svg>
                                    </button>

                                    @if ($estadoPersona)
                                        <button type="button"
                                            x-on:click="confirmarEstado('desactivar', '{{ $persona->cod_per }}', @js($nombreCompleto ?: 'esta persona'))"
                                            class="ui-icon-btn" title="Desactivar">
                                            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"
                                                stroke-width="1.8">
                                                <path stroke-linecap="round" stroke-linejoin="round"
                                                    d="M18 12H6m15 0a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                                            </svg>
                                        </button>
                                    @else
                                        <button type="button"
                                            x-on:click="confirmarEstado('reactivar', '{{ $persona->cod_per }}', @js($nombreCompleto ?: 'esta persona'))"
                                            class="ui-icon-btn" title="Reactivar">
                                            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"
                                                stroke-width="1.8">
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
                            <td colspan="8" class="px-6 py-14 text-center">
                                <div class="mx-auto max-w-md">
                                    <div class="mx-auto flex h-16 w-16 items-center justify-center rounded-[1.5rem]"
                                        style="background: var(--ui-surface-muted); color: var(--ui-muted);">
                                        <svg class="h-8 w-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"
                                            stroke-width="1.8">
                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                d="M17 20h5V4H2v16h5m10 0v-4a2 2 0 0 0-2-2H9a2 2 0 0 0-2 2v4" />
                                        </svg>
                                    </div>
                                    <h3 class="mt-5 text-lg font-black" style="color: var(--ui-text);">
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
            <div>{{ $personas->links() }}</div>
        </div>
    </section>

    {{-- VISTA TARJETAS --}}
    <section x-show="view === 'tarjetas'" x-cloak x-transition.opacity.duration.200ms class="gp-rise">
        <div class="grid gap-4 md:grid-cols-2 xl:grid-cols-3">
            @forelse ($personas as $persona)
                @php
                    $nombreCompleto = $nombrePersona($persona);
                    $estadoPersona = (bool) $persona->est_per;
                    $tieneUsuario = isset($persona->usuario);
                    $edad = $persona->fec_nac_per ? \Carbon\Carbon::parse($persona->fec_nac_per)->age : null;
                @endphp

                <article wire:key="tarjeta-persona-{{ $persona->cod_per }}"
                    class="ui-card rounded-[2rem] p-5 transition hover:-translate-y-1 hover:shadow-xl {{ !$estadoPersona ? 'opacity-75' : '' }}">
                    <div class="flex items-start gap-4">
                        <div class="h-16 w-16 shrink-0 overflow-hidden rounded-3xl ring-1"
                            style="background: var(--ui-surface-soft); --tw-ring-color: var(--ui-border);">
                            @if ($persona->fot_per)
                                <img src="{{ asset('storage/' . $persona->fot_per) }}" class="h-full w-full object-cover"
                                    alt="Foto de {{ $nombreCompleto }}">
                            @else
                                <div class="flex h-full w-full items-center justify-center text-xl font-black"
                                    style="background: linear-gradient(135deg, var(--ui-primary-soft), var(--ui-info-soft)); color: var(--ui-text-soft);">
                                    {{ $inicialPersona($persona) }}
                                </div>
                            @endif
                        </div>

                        <div class="min-w-0 flex-1">
                            <h3 class="line-clamp-1 text-lg font-black" style="color: var(--ui-text);">
                                {{ $nombreCompleto ?: 'Persona sin nombre' }}
                            </h3>
                            <p class="mt-1 text-sm font-bold" style="color: var(--ui-text-soft);">
                                CI {{ $persona->ci_per }}
                                @if ($persona->com_per)
                                    - {{ $persona->com_per }}
                                @endif
                            </p>

                            <div class="mt-3 flex flex-wrap gap-2">
                                @if ($estadoPersona)
                                    <span class="ui-badge-success">Activo</span>
                                @else
                                    <span class="ui-badge-danger">Inactivo</span>
                                @endif

                                @if ($tieneUsuario)
                                    <span class="ui-badge-success">Con usuario</span>
                                @else
                                    <span class="ui-badge-warning">Sin usuario</span>
                                @endif
                            </div>
                        </div>
                    </div>

                    <div class="mt-5 grid gap-3">
                        <div class="rounded-2xl border px-4 py-3"
                            style="background: var(--ui-surface-soft); border-color: var(--ui-border);">
                            <p class="text-xs font-black uppercase tracking-[0.12em]" style="color: var(--ui-muted);">
                                Contacto
                            </p>
                            <p class="mt-1 text-sm font-bold" style="color: var(--ui-text);">
                                {{ $persona->tel_per ?: 'Sin teléfono' }}
                            </p>
                            <p class="text-xs" style="color: var(--ui-muted);">
                                {{ $persona->ema_per ?: 'Sin correo' }}
                            </p>
                        </div>

                        <div class="rounded-2xl border px-4 py-3"
                            style="background: var(--ui-surface-soft); border-color: var(--ui-border);">
                            <p class="text-xs font-black uppercase tracking-[0.12em]" style="color: var(--ui-muted);">
                                Datos personales
                            </p>
                            <p class="mt-1 text-sm font-bold" style="color: var(--ui-text);">
                                {{ $edad ? $edad . ' años' : 'Sin edad' }} ·
                                {{ $persona->gen_per === 'M' ? 'Masculino' : ($persona->gen_per === 'F' ? 'Femenino' : 'No definido') }}
                            </p>
                        </div>

                        <div class="rounded-2xl border px-4 py-3"
                            style="background: var(--ui-surface-soft); border-color: var(--ui-border);">
                            <p class="text-xs font-black uppercase tracking-[0.12em]" style="color: var(--ui-muted);">
                                Dirección
                            </p>
                            <p class="mt-1 line-clamp-2 text-sm font-bold leading-6" style="color: var(--ui-text);">
                                {{ $direccionPersona($persona) }}
                            </p>
                        </div>
                    </div>

                    <div class="mt-5 grid grid-cols-2 gap-2">
                        <button type="button" wire:click="abrirModalVer('{{ $persona->cod_per }}')"
                            class="ui-btn-secondary justify-center">
                            Ver
                        </button>
                        <button type="button" wire:click="abrirModalEditar('{{ $persona->cod_per }}')"
                            class="ui-btn-primary justify-center">
                            Editar
                        </button>
                    </div>
                </article>
            @empty
                <div class="ui-card col-span-full rounded-[2rem] p-10 text-center">
                    <h3 class="text-lg font-black" style="color: var(--ui-text);">No se encontraron personas</h3>
                    <p class="mt-2 text-sm" style="color: var(--ui-muted);">Limpia filtros o registra una nueva persona.</p>
                </div>
            @endforelse
        </div>

        <div class="mt-5">{{ $personas->links() }}</div>
    </section>

    {{-- VISTA FOTOS --}}
    <section x-show="view === 'fotos'" x-cloak x-transition.opacity.duration.200ms class="gp-rise">
        <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-3 2xl:grid-cols-4">
            @forelse ($personas as $persona)
                @php
                    $nombreCompleto = $nombrePersona($persona);
                    $estadoPersona = (bool) $persona->est_per;
                    $tieneUsuario = isset($persona->usuario);
                @endphp

                <article wire:key="foto-persona-{{ $persona->cod_per }}"
                    class="ui-card overflow-hidden rounded-[2rem] transition hover:-translate-y-1 hover:shadow-xl {{ !$estadoPersona ? 'opacity-75' : '' }}">
                    <div class="relative h-56 overflow-hidden" style="background: var(--ui-surface-soft);">
                        @if ($persona->fot_per)
                            <img src="{{ asset('storage/' . $persona->fot_per) }}"
                                class="h-full w-full object-cover transition duration-500 hover:scale-105"
                                alt="Foto de {{ $nombreCompleto }}">
                        @else
                            <div class="flex h-full w-full items-center justify-center text-6xl font-black"
                                style="background: linear-gradient(135deg, var(--ui-primary-soft), var(--ui-info-soft)); color: var(--ui-text-soft);">
                                {{ $inicialPersona($persona) }}
                            </div>
                        @endif

                        <div class="absolute left-4 top-4">
                            @if ($estadoPersona)
                                <span class="ui-badge-success shadow-sm">Activo</span>
                            @else
                                <span class="ui-badge-danger shadow-sm">Inactivo</span>
                            @endif
                        </div>

                        <div class="absolute right-4 top-4">
                            @if ($tieneUsuario)
                                <span class="ui-badge-success shadow-sm">Usuario</span>
                            @else
                                <span class="ui-badge-warning shadow-sm">Sin usuario</span>
                            @endif
                        </div>
                    </div>

                    <div class="p-5">
                        <h3 class="line-clamp-1 text-lg font-black" style="color: var(--ui-text);">
                            {{ $nombreCompleto ?: 'Persona sin nombre' }}
                        </h3>
                        <p class="mt-1 text-sm font-bold" style="color: var(--ui-text-soft);">
                            CI {{ $persona->ci_per }}
                        </p>
                        <p class="mt-3 line-clamp-2 text-xs leading-5" style="color: var(--ui-muted);">
                            {{ $direccionPersona($persona) }}
                        </p>

                        <div class="mt-5 grid grid-cols-2 gap-2">
                            <button type="button" wire:click="abrirModalVer('{{ $persona->cod_per }}')"
                                class="ui-btn-secondary justify-center">
                                Ver
                            </button>
                            <button type="button" wire:click="abrirModalEditar('{{ $persona->cod_per }}')"
                                class="ui-btn-primary justify-center">
                                Editar
                            </button>
                        </div>
                    </div>
                </article>
            @empty
                <div class="ui-card col-span-full rounded-[2rem] p-10 text-center">
                    <h3 class="text-lg font-black" style="color: var(--ui-text);">No se encontraron personas</h3>
                    <p class="mt-2 text-sm" style="color: var(--ui-muted);">Limpia filtros o registra una nueva persona.</p>
                </div>
            @endforelse
        </div>

        <div class="mt-5">{{ $personas->links() }}</div>
    </section>

    {{-- VISTA COMPACTA --}}
    <section x-show="view === 'compacta'" x-cloak x-transition.opacity.duration.200ms
        class="ui-card rounded-[2rem] p-4 gp-rise">
        <div class="space-y-2">
            @forelse ($personas as $persona)
                @php
                    $nombreCompleto = $nombrePersona($persona);
                    $estadoPersona = (bool) $persona->est_per;
                    $tieneUsuario = isset($persona->usuario);
                @endphp

                <div wire:key="compacta-persona-{{ $persona->cod_per }}"
                    class="flex flex-col gap-3 rounded-2xl border px-4 py-3 transition hover:-translate-y-0.5 hover:shadow-md md:flex-row md:items-center md:justify-between"
                    style="background: var(--ui-surface-soft); border-color: var(--ui-border);">
                    <div class="flex items-center gap-3">
                        <div class="flex h-10 w-10 items-center justify-center overflow-hidden rounded-2xl ring-1"
                            style="background: var(--ui-surface); --tw-ring-color: var(--ui-border); color: var(--ui-text-soft);">
                            @if ($persona->fot_per)
                                <img src="{{ asset('storage/' . $persona->fot_per) }}" class="h-full w-full object-cover"
                                    alt="Foto de {{ $nombreCompleto }}">
                            @else
                                <span class="text-sm font-black">{{ $inicialPersona($persona) }}</span>
                            @endif
                        </div>

                        <div>
                            <p class="text-sm font-black" style="color: var(--ui-text);">
                                {{ $nombreCompleto ?: 'Persona sin nombre' }}
                            </p>
                            <p class="text-xs" style="color: var(--ui-muted);">
                                CI {{ $persona->ci_per }} · {{ $persona->tel_per ?: 'Sin teléfono' }}
                            </p>
                        </div>
                    </div>

                    <div class="flex flex-wrap items-center gap-2">
                        @if ($estadoPersona)
                            <span class="ui-badge-success">Activo</span>
                        @else
                            <span class="ui-badge-danger">Inactivo</span>
                        @endif

                        @if ($tieneUsuario)
                            <span class="ui-badge-success">Con usuario</span>
                        @else
                            <span class="ui-badge-warning">Sin usuario</span>
                        @endif

                        <button type="button" wire:click="abrirModalVer('{{ $persona->cod_per }}')" class="ui-icon-btn"
                            title="Ver">
                            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.8">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M2.036 12.322a1 1 0 0 1 0-.644C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.01 9.963 7.178a1 1 0 0 1 0 .644C20.577 16.49 16.639 19.5 12 19.5c-4.638 0-8.573-3.01-9.964-7.178Z" />
                            </svg>
                        </button>

                        <button type="button" wire:click="abrirModalEditar('{{ $persona->cod_per }}')" class="ui-icon-btn"
                            title="Editar">
                            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.8">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="m16.862 4.487 1.687-1.688a2.25 2.25 0 1 1 3.182 3.182L10.582 17.13a4.5 4.5 0 0 1-1.897 1.13L6 19l.74-2.685a4.5 4.5 0 0 1 1.13-1.897L16.862 4.487Z" />
                            </svg>
                        </button>
                    </div>
                </div>
            @empty
                <div class="rounded-2xl border p-8 text-center"
                    style="border-color: var(--ui-border); color: var(--ui-muted);">
                    No se encontraron personas.
                </div>
            @endforelse
        </div>

        <div class="mt-5">{{ $personas->links() }}</div>
    </section>

    {{-- MODAL CREAR --}}
    @if ($modalCrear)
        <div wire:key="modal-crear-persona" class="fixed inset-0 z-50 flex items-center justify-center px-4 py-6 sm:px-6">
            <div class="gp-overlay absolute inset-0" wire:click="cerrarModalCrear"></div>

            <div x-data="personaFormValidation('crear')" x-init="init()" x-on:input.debounce.120ms="validate()"
                x-on:change.debounce.120ms="validate()"
                class="relative w-full max-w-7xl overflow-hidden rounded-[2rem] border-2 shadow-2xl gp-pop"
                style="background: var(--ui-surface); border-color: var(--ui-border);">

                <div class="relative overflow-hidden px-6 py-5"
                    style="background: linear-gradient(135deg, var(--ui-primary), var(--ui-info)); color: white;">
                    <div class="absolute -left-20 -top-20 h-56 w-56 rounded-full bg-white/10 blur-3xl"></div>
                    <div class="absolute -right-24 -bottom-24 h-64 w-64 rounded-full bg-white/10 blur-3xl"></div>

                    <div class="relative flex items-start justify-between gap-4">
                        <div>
                            <p class="text-xs font-black uppercase tracking-[0.18em] text-white/80">
                                Nuevo registro
                            </p>
                            <h3 class="mt-2 text-2xl font-black md:text-3xl">
                                Registrar persona
                            </h3>
                            <p class="mt-2 max-w-3xl text-sm leading-6 text-white/90">
                                Registra datos personales, identificación, contacto, fotografía y dirección. No se asignan
                                roles en este módulo.
                            </p>
                        </div>

                        <button type="button" wire:click="cerrarModalCrear"
                            class="rounded-2xl bg-white/10 p-2 text-white transition hover:scale-105 hover:bg-white/20">
                            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.8">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" />
                            </svg>
                        </button>
                    </div>
                </div>

                <div class="max-h-[76vh] overflow-y-auto gp-scroll px-6 py-6">
                    <div class="grid gap-6 xl:grid-cols-[1fr,0.78fr]">
                        <div class="space-y-5">
                            <div x-show="showErrors && !validation.valid" x-cloak x-transition
                                class="rounded-2xl border px-4 py-3"
                                style="background: var(--ui-danger-soft); border-color: var(--ui-danger-border); color: var(--ui-danger);">
                                <p class="text-sm font-black">Datos pendientes de corrección</p>
                                <ul class="mt-2 list-disc space-y-1 pl-5 text-xs font-semibold leading-5">
                                    <template x-for="error in validation.list" :key="error">
                                        <li x-text="error"></li>
                                    </template>
                                </ul>
                            </div>

                            <div class="ui-card-soft p-4">
                                <p class="text-sm font-black" style="color: var(--ui-text);">Fotografía</p>

                                <div class="mt-4 flex flex-col gap-4 sm:flex-row sm:items-center">
                                    <div class="h-24 w-24 overflow-hidden rounded-3xl ring-1"
                                        style="background: var(--ui-surface); --tw-ring-color: var(--ui-border);">
                                        @if ($foto)
                                            <img src="{{ $foto->temporaryUrl() }}" class="h-full w-full object-cover"
                                                alt="Vista previa">
                                        @else
                                            <div class="flex h-full w-full items-center justify-center text-3xl font-black"
                                                style="background: linear-gradient(135deg, var(--ui-primary-soft), var(--ui-info-soft)); color: var(--ui-text-soft);">
                                                +
                                            </div>
                                        @endif
                                    </div>

                                    <div class="flex-1">
                                        <input type="file" wire:model="foto" accept="image/*" class="ui-input">
                                        @error('foto') <p class="ui-error">{{ $message }}</p> @enderror
                                        <p class="ui-help">JPG, PNG o WEBP. Tamaño máximo: 2 MB.</p>
                                    </div>
                                </div>
                            </div>

                            <div class="grid gap-5 md:grid-cols-2">
                                <div>
                                    <label class="ui-label">Nombre <span class="text-red-500">*</span></label>
                                    <input type="text" wire:model.live.debounce.300ms="form.nom_per" data-field="nombre"
                                        maxlength="100" autocomplete="off"
                                        x-on:input="touch('nombre'); $event.target.value = cleanLetters($event.target.value)"
                                        class="ui-input" placeholder="Ej. Víctor">
                                    <p x-show="shouldShow('nombre')" x-cloak x-text="validation.errors.nombre"
                                        class="ui-error"></p>
                                    @error('form.nom_per') <p class="ui-error">{{ $message }}</p> @enderror
                                </div>

                                <div>
                                    <label class="ui-label">Apellido paterno <span class="text-red-500">*</span></label>
                                    <input type="text" wire:model.live.debounce.300ms="form.ape_pat_per"
                                        data-field="apellido" maxlength="100" autocomplete="off"
                                        x-on:input="touch('apellido'); $event.target.value = cleanLetters($event.target.value)"
                                        class="ui-input" placeholder="Ej. Asturizaga">
                                    <p x-show="shouldShow('apellido')" x-cloak x-text="validation.errors.apellido"
                                        class="ui-error"></p>
                                    @error('form.ape_pat_per') <p class="ui-error">{{ $message }}</p> @enderror
                                </div>

                                <div>
                                    <label class="ui-label">Apellido materno</label>
                                    <input type="text" wire:model.live.debounce.300ms="form.ape_mat_per" maxlength="100"
                                        autocomplete="off"
                                        x-on:input="$event.target.value = cleanLetters($event.target.value)"
                                        class="ui-input" placeholder="Ej. Plata">
                                    @error('form.ape_mat_per') <p class="ui-error">{{ $message }}</p> @enderror
                                </div>

                                <div>
                                    <label class="ui-label">CI <span class="text-red-500">*</span></label>
                                    <input type="text" wire:model.live.debounce.300ms="form.ci_per" data-field="ci"
                                        maxlength="12" inputmode="numeric" autocomplete="off"
                                        x-on:input="touch('ci'); $event.target.value = cleanNumbers($event.target.value)"
                                        class="ui-input" placeholder="Ej. 12345678">
                                    <p x-show="shouldShow('ci')" x-cloak x-text="validation.errors.ci" class="ui-error"></p>
                                    @error('form.ci_per') <p class="ui-error">{{ $message }}</p> @enderror
                                    <p class="ui-help">Entre 4 y 12 números.</p>
                                </div>

                                <div>
                                    <label class="ui-label">Complemento</label>
                                    <input type="text" wire:model.live.debounce.300ms="form.com_per"
                                        data-field="complemento" maxlength="2" autocomplete="off"
                                        x-on:input="touch('complemento'); $event.target.value = cleanComplement($event.target.value)"
                                        class="ui-input" placeholder="Opcional. Ej. 1A">
                                    <p x-show="shouldShow('complemento')" x-cloak x-text="validation.errors.complemento"
                                        class="ui-error"></p>
                                    @error('form.com_per') <p class="ui-error">{{ $message }}</p> @enderror
                                    <p class="ui-help">Opcional. Si corresponde, usa formato número + letra. Ej. 1A.</p>
                                </div>

                                <div>
                                    <label class="ui-label">Expedido <span class="text-red-500">*</span></label>
                                    <select wire:model.live="form.exp_per" data-field="expedido"
                                        x-on:change="touch('expedido')" class="ui-select">
                                        <option value="">Seleccionar</option>
                                        <option value="LP">LP</option>
                                        <option value="CBBA">CBBA</option>
                                        <option value="SCZ">SCZ</option>
                                        <option value="ORU">ORU</option>
                                        <option value="PT">PT</option>
                                        <option value="CH">CH</option>
                                        <option value="TJ">TJ</option>
                                        <option value="BN">BN</option>
                                        <option value="PD">PD</option>
                                    </select>
                                    <p x-show="shouldShow('expedido')" x-cloak x-text="validation.errors.expedido"
                                        class="ui-error"></p>
                                    @error('form.exp_per') <p class="ui-error">{{ $message }}</p> @enderror
                                </div>

                                <div>
                                    <label class="ui-label">Fecha de nacimiento <span class="text-red-500">*</span></label>
                                    <input type="date" wire:model.live="form.fec_nac_per" data-field="fecha"
                                        min="1906-01-01" max="{{ now()->format('Y-m-d') }}" x-on:change="touch('fecha')"
                                        class="ui-input">
                                    <p x-show="shouldShow('fecha')" x-cloak x-text="validation.errors.fecha"
                                        class="ui-error"></p>
                                    @error('form.fec_nac_per') <p class="ui-error">{{ $message }}</p> @enderror
                                </div>

                                <div>
                                    <label class="ui-label">Género <span class="text-red-500">*</span></label>
                                    <select wire:model.live="form.gen_per" data-field="genero" x-on:change="touch('genero')"
                                        class="ui-select">
                                        <option value="">Seleccionar</option>
                                        <option value="M">Masculino</option>
                                        <option value="F">Femenino</option>
                                    </select>
                                    <p x-show="shouldShow('genero')" x-cloak x-text="validation.errors.genero"
                                        class="ui-error"></p>
                                    @error('form.gen_per') <p class="ui-error">{{ $message }}</p> @enderror
                                </div>

                                <div>
                                    <label class="ui-label">Teléfono</label>
                                    <input type="text" wire:model.live.debounce.300ms="form.tel_per" data-field="telefono"
                                        maxlength="20" inputmode="tel" autocomplete="off"
                                        x-on:input="touch('telefono'); $event.target.value = cleanPhone($event.target.value)"
                                        class="ui-input" placeholder="Ej. 70123456">
                                    <p x-show="shouldShow('telefono')" x-cloak x-text="validation.errors.telefono"
                                        class="ui-error"></p>
                                    @error('form.tel_per') <p class="ui-error">{{ $message }}</p> @enderror
                                </div>

                                <div>
                                    <label class="ui-label">Correo</label>
                                    <input type="email" wire:model.live.debounce.300ms="form.ema_per" data-field="email"
                                        maxlength="150" autocomplete="off"
                                        x-on:input="touch('email'); $event.target.value = cleanEmail($event.target.value)"
                                        class="ui-input" placeholder="persona@gmail.com">
                                    <p x-show="shouldShow('email')" x-cloak x-text="validation.errors.email"
                                        class="ui-error"></p>
                                    @error('form.ema_per') <p class="ui-error">{{ $message }}</p> @enderror
                                </div>
                            </div>

                            <section class="rounded-[1.7rem] border p-5"
                                style="background: var(--ui-surface-soft); border-color: var(--ui-border);">
                                <div class="flex flex-col gap-4 md:flex-row md:items-start md:justify-between">
                                    <div>
                                        <p class="text-xs font-black uppercase tracking-[0.16em]"
                                            style="color: var(--ui-primary);">
                                            Dirección
                                        </p>
                                        <h4 class="mt-1 text-lg font-black" style="color: var(--ui-text);">
                                            Completa o por campos
                                        </h4>
                                        <p class="mt-1 text-xs leading-5" style="color: var(--ui-muted);">
                                            Puedes escribir “Calle Tocopilla, Zona Bajo Tejar, #1423” o llenar los campos
                                            por separado.
                                        </p>
                                    </div>

                                    <div class="grid grid-cols-2 gap-2">
                                        <button type="button" wire:click="cambiarModoDireccionCrear('inteligente')"
                                            class="rounded-2xl border px-4 py-2 text-xs font-black transition"
                                            style="{{ $modoDireccionCrear === 'inteligente' ? 'background: var(--ui-primary-soft); border-color: var(--ui-primary-border); color: var(--ui-primary);' : 'background: var(--ui-surface); border-color: var(--ui-border); color: var(--ui-muted);' }}">
                                            Automática
                                        </button>

                                        <button type="button" wire:click="cambiarModoDireccionCrear('manual')"
                                            class="rounded-2xl border px-4 py-2 text-xs font-black transition"
                                            style="{{ $modoDireccionCrear === 'manual' ? 'background: var(--ui-primary-soft); border-color: var(--ui-primary-border); color: var(--ui-primary);' : 'background: var(--ui-surface); border-color: var(--ui-border); color: var(--ui-muted);' }}">
                                            Manual
                                        </button>
                                    </div>
                                </div>

                                <div class="mt-5">
                                    <label class="ui-label">Dirección completa</label>
                                    <input type="text" wire:model.live.debounce.500ms="form.dir_per" maxlength="255"
                                        autocomplete="off" class="ui-input"
                                        placeholder="Ej. Calle Tocopilla, Zona Bajo Tejar, #1423">
                                    @error('form.dir_per') <p class="ui-error">{{ $message }}</p> @enderror
                                </div>

                                <div class="mt-5 grid gap-4 md:grid-cols-2">
                                    <div>
                                        <label class="ui-label">Zona</label>
                                        <input type="text" wire:model.live.debounce.400ms="form.zona_per" class="ui-input"
                                            placeholder="Ej. Bajo Tejar">
                                        @error('form.zona_per') <p class="ui-error">{{ $message }}</p> @enderror
                                    </div>

                                    <div>
                                        <label class="ui-label">Avenida</label>
                                        <input type="text" wire:model.live.debounce.400ms="form.ave_per" class="ui-input"
                                            placeholder="Ej. Buenos Aires">
                                        @error('form.ave_per') <p class="ui-error">{{ $message }}</p> @enderror
                                    </div>

                                    <div>
                                        <label class="ui-label">Calle</label>
                                        <input type="text" wire:model.live.debounce.400ms="form.cal_per" class="ui-input"
                                            placeholder="Ej. Tocopilla">
                                        @error('form.cal_per') <p class="ui-error">{{ $message }}</p> @enderror
                                    </div>

                                    <div>
                                        <label class="ui-label">Número</label>
                                        <input type="text" wire:model.live.debounce.400ms="form.num_per" class="ui-input"
                                            placeholder="Ej. 1423">
                                        @error('form.num_per') <p class="ui-error">{{ $message }}</p> @enderror
                                    </div>

                                    <div>
                                        <label class="ui-label">Ciudad</label>
                                        <input type="text" wire:model.live.debounce.400ms="form.ciu_per" class="ui-input"
                                            placeholder="Ej. La Paz">
                                        @error('form.ciu_per') <p class="ui-error">{{ $message }}</p> @enderror
                                    </div>

                                    <div>
                                        <label class="ui-label">Municipio</label>
                                        <input type="text" wire:model.live.debounce.400ms="form.mun_per" class="ui-input"
                                            placeholder="Ej. La Paz">
                                        @error('form.mun_per') <p class="ui-error">{{ $message }}</p> @enderror
                                    </div>

                                    <div>
                                        <label class="ui-label">Departamento</label>
                                        <input type="text" wire:model.live.debounce.400ms="form.dep_per" class="ui-input"
                                            placeholder="Ej. La Paz">
                                        @error('form.dep_per') <p class="ui-error">{{ $message }}</p> @enderror
                                    </div>

                                    <div>
                                        <label class="ui-label">Referencia</label>
                                        <input type="text" wire:model.live.debounce.400ms="form.ref_per" class="ui-input"
                                            placeholder="Ej. Cerca del mercado">
                                        @error('form.ref_per') <p class="ui-error">{{ $message }}</p> @enderror
                                    </div>
                                </div>
                            </section>

                            <div>
                                <label class="ui-label">Estado</label>
                                <select wire:model.live="form.est_per" class="ui-select">
                                    <option value="1">Activo</option>
                                    <option value="0">Inactivo</option>
                                </select>
                                @error('form.est_per') <p class="ui-error">{{ $message }}</p> @enderror
                            </div>
                        </div>

                        <aside class="space-y-4">
                            <section class="rounded-[1.7rem] border p-5"
                                style="background: var(--ui-surface-soft); border-color: var(--ui-border);">
                                <div class="flex items-start justify-between gap-3">
                                    <div>
                                        <p class="text-xs font-black uppercase tracking-[0.16em]"
                                            style="color: var(--ui-muted);">
                                            Revisión del registro
                                        </p>
                                        <h4 class="mt-1 text-xl font-black" style="color: var(--ui-text);">
                                            Estado de validación
                                        </h4>
                                    </div>

                                    <span
                                        class="rounded-full border px-3 py-1 text-xs font-black {{ $badgeRevision($estadoAnalisisCrear, $puedeContinuarCrear) }}">
                                        {{ $estadoAnalisisCrear }}
                                    </span>
                                </div>

                                <div
                                    class="mt-4 rounded-2xl border p-4 {{ $badgeRevision($estadoAnalisisCrear, $puedeContinuarCrear) }}">
                                    <p class="text-sm font-black">
                                        {{ $analisisPersona['mensaje'] ?? 'Completa los datos principales para revisar el registro.' }}
                                    </p>
                                </div>

                                @if (!empty($analisisPersona['bloqueos'] ?? []))
                                    <div class="mt-4 space-y-2">
                                        @foreach ($analisisPersona['bloqueos'] as $bloqueo)
                                            <div
                                                class="rounded-2xl border border-rose-200 bg-rose-50 px-4 py-3 text-xs font-bold leading-5 text-rose-800 dark:border-rose-500/30 dark:bg-rose-950 dark:text-rose-200">
                                                {{ $bloqueo }}
                                            </div>
                                        @endforeach
                                    </div>
                                @endif

                                @if (!empty($analisisPersona['advertencias'] ?? []))
                                    <div class="mt-4 space-y-2">
                                        @foreach ($analisisPersona['advertencias'] as $advertencia)
                                            <div
                                                class="rounded-2xl border border-amber-200 bg-amber-50 px-4 py-3 text-xs font-bold leading-5 text-amber-800 dark:border-amber-500/30 dark:bg-amber-950 dark:text-amber-200">
                                                {{ $advertencia }}
                                            </div>
                                        @endforeach
                                    </div>
                                @endif

                                @if (!empty($analisisPersona['sugerencias'] ?? []))
                                    <div class="mt-4 space-y-2">
                                        @foreach ($analisisPersona['sugerencias'] as $sugerencia)
                                            <div
                                                class="rounded-2xl border border-teal-200 bg-teal-50 px-4 py-3 text-xs font-bold leading-5 text-teal-800 dark:border-teal-500/30 dark:bg-teal-950 dark:text-teal-200">
                                                {{ $sugerencia }}
                                            </div>
                                        @endforeach
                                    </div>
                                @endif
                            </section>

                            <section class="rounded-[1.7rem] border p-5"
                                style="background: var(--ui-surface); border-color: var(--ui-border);">
                                <p class="text-xs font-black uppercase tracking-[0.16em]" style="color: var(--ui-muted);">
                                    Vista previa
                                </p>

                                <div class="mt-4 space-y-3">
                                    <div class="rounded-2xl border px-4 py-3"
                                        style="background: var(--ui-surface-soft); border-color: var(--ui-border);">
                                        <p class="text-xs font-black uppercase tracking-[0.12em]"
                                            style="color: var(--ui-muted);">Nombre</p>
                                        <p class="mt-1 text-sm font-black" style="color: var(--ui-text);">
                                            {{ trim(($form['nom_per'] ?? '') . ' ' . ($form['ape_pat_per'] ?? '') . ' ' . ($form['ape_mat_per'] ?? '')) ?: 'Sin nombre completo' }}
                                        </p>
                                    </div>

                                    <div class="rounded-2xl border px-4 py-3"
                                        style="background: var(--ui-surface-soft); border-color: var(--ui-border);">
                                        <p class="text-xs font-black uppercase tracking-[0.12em]"
                                            style="color: var(--ui-muted);">Identificación</p>
                                        <p class="mt-1 text-sm font-black" style="color: var(--ui-text);">
                                            CI {{ $form['ci_per'] ?: '—' }}
                                            {{ $form['com_per'] ? '- ' . $form['com_per'] : '' }} · Exp.
                                            {{ $form['exp_per'] ?: '—' }}
                                        </p>
                                    </div>

                                    <div class="rounded-2xl border px-4 py-3"
                                        style="background: var(--ui-surface-soft); border-color: var(--ui-border);">
                                        <p class="text-xs font-black uppercase tracking-[0.12em]"
                                            style="color: var(--ui-muted);">Dirección</p>
                                        <p class="mt-1 text-sm font-black leading-6" style="color: var(--ui-text);">
                                            {{ $form['dir_per'] ?: 'Sin dirección registrada' }}
                                        </p>
                                    </div>

                                    <div class="rounded-2xl border px-4 py-3"
                                        style="background: var(--ui-surface-soft); border-color: var(--ui-border);">
                                        <p class="text-xs font-black uppercase tracking-[0.12em]"
                                            style="color: var(--ui-muted);">Completitud</p>
                                        <div class="mt-2 h-2 overflow-hidden rounded-full"
                                            style="background: var(--ui-border);">
                                            <div class="h-full rounded-full transition-all duration-700"
                                                style="width: {{ $analisisPersona['resumen']['campos_completitud']['porcentaje'] ?? 0 }}%; background: var(--ui-primary);">
                                            </div>
                                        </div>
                                        <p class="mt-2 text-xs font-bold" style="color: var(--ui-muted);">
                                            {{ $analisisPersona['resumen']['campos_completitud']['porcentaje'] ?? 0 }}%
                                            completado
                                        </p>
                                    </div>
                                </div>
                            </section>
                        </aside>
                    </div>
                </div>

                <div class="border-t px-6 py-4" style="border-color: var(--ui-border); background: var(--ui-surface);">
                    <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                        <p x-show="showErrors && (!validation.valid || !@js($puedeContinuarCrear))" x-cloak
                            class="text-sm font-bold" style="color: var(--ui-danger);">
                            Corrige los campos marcados antes de guardar.
                        </p>

                        <p x-show="validation.valid && @js($puedeContinuarCrear)" x-cloak class="text-sm font-bold"
                            style="color: var(--ui-primary);">
                            Registro listo para guardar.
                        </p>

                        <div class="flex flex-wrap gap-3">
                            <button type="button" wire:click="cerrarModalCrear" class="ui-btn-secondary">
                                Cancelar
                            </button>

                            <button type="button" x-on:click="submitCreate()" wire:loading.attr="disabled"
                                wire:target="guardarPersona"
                                x-bind:disabled="!validation.valid || !@js($puedeContinuarCrear)"
                                x-bind:class="(validation.valid && @js($puedeContinuarCrear)) ? 'ui-btn-primary' : 'ui-btn cursor-not-allowed bg-slate-300 text-slate-500 shadow-none'"
                                class="rounded-2xl px-5 py-3 text-sm font-bold transition">
                                <svg wire:loading wire:target="guardarPersona" class="h-4 w-4 animate-spin" fill="none"
                                    viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor"
                                        stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 0 1 8-8v4a4 4 0 0 0-4 4H4z">
                                    </path>
                                </svg>
                                <span wire:loading.remove wire:target="guardarPersona">Guardar persona</span>
                                <span wire:loading wire:target="guardarPersona">Validando...</span>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @endif

    {{-- MODAL VER --}}
    @if ($modalVer && $personaDetalle)
        @php
            $nombreDetalle = $nombrePersona($personaDetalle);
            $estadoDetalle = (bool) $personaDetalle->est_per;
            $tieneUsuarioDetalle = isset($personaDetalle->usuario);
            $edadDetalle = $personaDetalle->fec_nac_per ? \Carbon\Carbon::parse($personaDetalle->fec_nac_per)->age : null;
            $telefonoDetalle = preg_replace('/\D/', '', $personaDetalle->tel_per ?? '');
        @endphp

        <div wire:key="modal-ver-persona-{{ $personaDetalle->cod_per }}"
            class="fixed inset-0 z-50 flex items-center justify-center px-4 py-6 sm:px-6">
            <div class="gp-overlay absolute inset-0" wire:click="cerrarModalVer"></div>

            <div class="relative w-full max-w-4xl overflow-hidden rounded-[2rem] border-2 shadow-2xl gp-pop"
                style="background: var(--ui-surface); border-color: var(--ui-border);">
                <div class="px-6 py-5"
                    style="background: linear-gradient(135deg, var(--ui-info), var(--ui-primary)); color: white;">
                    <div class="flex items-start justify-between gap-4">
                        <div>
                            <p class="text-xs font-black uppercase tracking-[0.18em] text-white/80">Detalle personal</p>
                            <h3 class="mt-2 text-2xl font-black">Información de la persona</h3>
                            <p class="mt-2 text-sm text-white/90">Ficha base de identidad institucional.</p>
                        </div>

                        <button type="button" wire:click="cerrarModalVer"
                            class="rounded-2xl bg-white/10 p-2 text-white transition hover:scale-105 hover:bg-white/20">
                            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.8">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" />
                            </svg>
                        </button>
                    </div>
                </div>

                <div class="max-h-[76vh] overflow-y-auto gp-scroll px-6 py-6">
                    <div class="ui-card-soft mb-5 flex flex-col gap-4 p-4 sm:flex-row sm:items-center">
                        <div class="h-24 w-24 overflow-hidden rounded-3xl ring-1"
                            style="--tw-ring-color: var(--ui-border);">
                            @if ($personaDetalle->fot_per)
                                <img src="{{ asset('storage/' . $personaDetalle->fot_per) }}" alt="Foto de {{ $nombreDetalle }}"
                                    class="h-full w-full object-cover">
                            @else
                                <div class="flex h-full w-full items-center justify-center text-3xl font-black"
                                    style="background: linear-gradient(135deg, var(--ui-primary-soft), var(--ui-info-soft)); color: var(--ui-text-soft);">
                                    {{ $inicialPersona($personaDetalle) }}
                                </div>
                            @endif
                        </div>

                        <div class="min-w-0 flex-1">
                            <h4 class="truncate text-xl font-black" style="color: var(--ui-text);">
                                {{ $nombreDetalle ?: 'Persona sin nombre' }}
                            </h4>

                            <div class="mt-3 flex flex-wrap gap-2">
                                @if ($estadoDetalle)
                                    <span class="ui-badge-success">Activo</span>
                                @else
                                    <span class="ui-badge-danger">Inactivo</span>
                                @endif

                                @if ($tieneUsuarioDetalle)
                                    <span class="ui-badge-success">Con usuario</span>
                                @else
                                    <span class="ui-badge-warning">Sin usuario</span>
                                @endif
                            </div>
                        </div>
                    </div>

                    <div class="grid gap-4 sm:grid-cols-2">
                        <div class="ui-card-soft p-4">
                            <p class="text-xs font-black uppercase tracking-[0.14em]" style="color: var(--ui-muted);">CI</p>
                            <p class="mt-2 font-black" style="color: var(--ui-text);">
                                {{ $personaDetalle->ci_per }}
                                @if ($personaDetalle->com_per)
                                    - {{ $personaDetalle->com_per }}
                                @endif
                            </p>
                            <p class="text-xs" style="color: var(--ui-muted);">Exp. {{ $personaDetalle->exp_per ?? '—' }}
                            </p>
                        </div>

                        <div class="ui-card-soft p-4">
                            <p class="text-xs font-black uppercase tracking-[0.14em]" style="color: var(--ui-muted);">Edad /
                                género</p>
                            <p class="mt-2 font-black" style="color: var(--ui-text);">
                                {{ $edadDetalle ? $edadDetalle . ' años' : 'Edad no registrada' }} ·
                                {{ $personaDetalle->gen_per === 'M' ? 'Masculino' : ($personaDetalle->gen_per === 'F' ? 'Femenino' : 'No definido') }}
                            </p>
                        </div>

                        <div class="ui-card-soft p-4">
                            <p class="text-xs font-black uppercase tracking-[0.14em]" style="color: var(--ui-muted);">
                                Teléfono</p>
                            @if ($personaDetalle->tel_per)
                                <a href="https://wa.me/591{{ $telefonoDetalle }}" target="_blank"
                                    class="mt-2 inline-flex items-center gap-2 rounded-xl px-3 py-2 text-sm font-black ring-1 transition hover:-translate-y-0.5"
                                    style="background: var(--ui-primary-soft); color: var(--ui-primary); --tw-ring-color: var(--ui-primary-border);">
                                    {{ $personaDetalle->tel_per }}
                                </a>
                            @else
                                <p class="mt-2 font-black" style="color: var(--ui-text);">Sin teléfono</p>
                            @endif
                        </div>

                        <div class="ui-card-soft p-4">
                            <p class="text-xs font-black uppercase tracking-[0.14em]" style="color: var(--ui-muted);">Correo
                            </p>
                            @if ($personaDetalle->ema_per)
                                <a href="mailto:{{ $personaDetalle->ema_per }}"
                                    class="mt-2 inline-flex items-center gap-2 rounded-xl px-3 py-2 text-sm font-black ring-1 transition hover:-translate-y-0.5"
                                    style="background: var(--ui-info-soft); color: var(--ui-info); --tw-ring-color: var(--ui-info-border);">
                                    {{ $personaDetalle->ema_per }}
                                </a>
                            @else
                                <p class="mt-2 font-black" style="color: var(--ui-text);">Sin correo</p>
                            @endif
                        </div>

                        <div class="ui-card-soft p-4 sm:col-span-2">
                            <p class="text-xs font-black uppercase tracking-[0.14em]" style="color: var(--ui-muted);">
                                Dirección</p>
                            <p class="mt-2 font-black leading-6" style="color: var(--ui-text);">
                                {{ $direccionPersona($personaDetalle) }}
                            </p>
                        </div>
                    </div>
                </div>

                <div class="border-t px-6 py-4 text-right" style="border-color: var(--ui-border);">
                    <button type="button" wire:click="cerrarModalVer" class="ui-btn-secondary">Cerrar</button>
                </div>
            </div>
        </div>
    @endif

    {{-- MODAL EDITAR --}}
    @if ($modalEditar)
        <div wire:key="modal-editar-persona" class="fixed inset-0 z-50 flex items-center justify-center px-4 py-6 sm:px-6">
            <div class="gp-overlay absolute inset-0" wire:click="cerrarModalEditar"></div>

            <div x-data="personaFormValidation('editar')" x-init="init()" x-on:input.debounce.120ms="validate()"
                x-on:change.debounce.120ms="validate()"
                class="relative w-full max-w-7xl overflow-hidden rounded-[2rem] border-2 shadow-2xl gp-pop"
                style="background: var(--ui-surface); border-color: var(--ui-border);">

                <div class="px-6 py-5"
                    style="background: linear-gradient(135deg, var(--ui-primary), var(--ui-info)); color: white;">
                    <div class="flex items-start justify-between gap-4">
                        <div>
                            <p class="text-xs font-black uppercase tracking-[0.18em] text-white/80">Edición de registro</p>
                            <h3 class="mt-2 text-2xl font-black md:text-3xl">Editar persona</h3>
                            <p class="mt-2 max-w-3xl text-sm leading-6 text-white/90">
                                Actualiza datos personales y dirección sin modificar roles ni permisos.
                            </p>
                        </div>

                        <button type="button" wire:click="cerrarModalEditar"
                            class="rounded-2xl bg-white/10 p-2 text-white transition hover:scale-105 hover:bg-white/20">
                            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.8">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" />
                            </svg>
                        </button>
                    </div>
                </div>

                <div class="max-h-[76vh] overflow-y-auto gp-scroll px-6 py-6">
                    <div class="grid gap-6 xl:grid-cols-[1fr,0.78fr]">
                        <div class="space-y-5">
                            <div x-show="showErrors && !validation.valid" x-cloak x-transition
                                class="rounded-2xl border px-4 py-3"
                                style="background: var(--ui-danger-soft); border-color: var(--ui-danger-border); color: var(--ui-danger);">
                                <p class="text-sm font-black">Datos pendientes de corrección</p>
                                <ul class="mt-2 list-disc space-y-1 pl-5 text-xs font-semibold leading-5">
                                    <template x-for="error in validation.list" :key="error">
                                        <li x-text="error"></li>
                                    </template>
                                </ul>
                            </div>

                            <div class="ui-card-soft p-4">
                                <p class="text-sm font-black" style="color: var(--ui-text);">Fotografía</p>

                                <div class="mt-4 flex flex-col gap-4 sm:flex-row sm:items-center">
                                    <div class="h-24 w-24 overflow-hidden rounded-3xl ring-1"
                                        style="background: var(--ui-surface); --tw-ring-color: var(--ui-border);">
                                        @if ($fotoEditar)
                                            <img src="{{ $fotoEditar->temporaryUrl() }}" class="h-full w-full object-cover"
                                                alt="Nueva foto">
                                        @elseif (!empty($formEditar['fot_per']))
                                            <img src="{{ asset('storage/' . $formEditar['fot_per']) }}"
                                                class="h-full w-full object-cover" alt="Foto actual">
                                        @else
                                            <div class="flex h-full w-full items-center justify-center text-3xl font-black"
                                                style="background: linear-gradient(135deg, var(--ui-primary-soft), var(--ui-info-soft)); color: var(--ui-text-soft);">
                                                {{ strtoupper(substr($formEditar['nom_per'] ?? 'P', 0, 1)) }}
                                            </div>
                                        @endif
                                    </div>

                                    <div class="flex-1">
                                        <input type="file" wire:model="fotoEditar" accept="image/*" class="ui-input">
                                        @error('fotoEditar') <p class="ui-error">{{ $message }}</p> @enderror

                                        <p class="ui-help">JPG, PNG o WEBP. Tamaño máximo: 2 MB.</p>

                                        @if (!empty($formEditar['fot_per']))
                                            <button type="button" wire:click="eliminarFotoEditar" wire:loading.attr="disabled"
                                                wire:target="eliminarFotoEditar"
                                                class="mt-3 rounded-xl border px-3 py-2 text-xs font-black transition hover:-translate-y-0.5 disabled:cursor-wait disabled:opacity-60"
                                                style="background: var(--ui-danger-soft); color: var(--ui-danger); border-color: var(--ui-danger-border);">
                                                Quitar foto actual
                                            </button>
                                        @endif
                                    </div>
                                </div>
                            </div>

                            <div class="grid gap-5 md:grid-cols-2">
                                <div>
                                    <label class="ui-label">Nombre <span class="text-red-500">*</span></label>
                                    <input type="text" wire:model.live.debounce.300ms="formEditar.nom_per"
                                        data-field="nombre" maxlength="100" autocomplete="off"
                                        x-on:input="touch('nombre'); $event.target.value = cleanLetters($event.target.value)"
                                        class="ui-input" placeholder="Ej. Víctor">
                                    <p x-show="shouldShow('nombre')" x-cloak x-text="validation.errors.nombre"
                                        class="ui-error"></p>
                                    @error('formEditar.nom_per') <p class="ui-error">{{ $message }}</p> @enderror
                                </div>

                                <div>
                                    <label class="ui-label">Apellido paterno <span class="text-red-500">*</span></label>
                                    <input type="text" wire:model.live.debounce.300ms="formEditar.ape_pat_per"
                                        data-field="apellido" maxlength="100" autocomplete="off"
                                        x-on:input="touch('apellido'); $event.target.value = cleanLetters($event.target.value)"
                                        class="ui-input" placeholder="Ej. Asturizaga">
                                    <p x-show="shouldShow('apellido')" x-cloak x-text="validation.errors.apellido"
                                        class="ui-error"></p>
                                    @error('formEditar.ape_pat_per') <p class="ui-error">{{ $message }}</p> @enderror
                                </div>

                                <div>
                                    <label class="ui-label">Apellido materno</label>
                                    <input type="text" wire:model.live.debounce.300ms="formEditar.ape_mat_per"
                                        maxlength="100" autocomplete="off"
                                        x-on:input="$event.target.value = cleanLetters($event.target.value)"
                                        class="ui-input" placeholder="Ej. Plata">
                                    @error('formEditar.ape_mat_per') <p class="ui-error">{{ $message }}</p> @enderror
                                </div>

                                <div>
                                    <label class="ui-label">CI <span class="text-red-500">*</span></label>
                                    <input type="text" wire:model.live.debounce.300ms="formEditar.ci_per" data-field="ci"
                                        maxlength="12" inputmode="numeric" autocomplete="off"
                                        x-on:input="touch('ci'); $event.target.value = cleanNumbers($event.target.value)"
                                        class="ui-input" placeholder="Ej. 12345678">
                                    <p x-show="shouldShow('ci')" x-cloak x-text="validation.errors.ci" class="ui-error"></p>
                                    @error('formEditar.ci_per') <p class="ui-error">{{ $message }}</p> @enderror
                                </div>

                                <div>
                                    <label class="ui-label">Complemento</label>
                                    <input type="text" wire:model.live.debounce.300ms="formEditar.com_per"
                                        data-field="complemento" maxlength="2" autocomplete="off"
                                        x-on:input="touch('complemento'); $event.target.value = cleanComplement($event.target.value)"
                                        class="ui-input" placeholder="Opcional. Ej. 1A">
                                    <p x-show="shouldShow('complemento')" x-cloak x-text="validation.errors.complemento"
                                        class="ui-error"></p>
                                    @error('formEditar.com_per') <p class="ui-error">{{ $message }}</p> @enderror
                                </div>

                                <div>
                                    <label class="ui-label">Expedido <span class="text-red-500">*</span></label>
                                    <select wire:model.live="formEditar.exp_per" data-field="expedido"
                                        x-on:change="touch('expedido')" class="ui-select">
                                        <option value="">Seleccionar</option>
                                        <option value="LP">LP</option>
                                        <option value="CBBA">CBBA</option>
                                        <option value="SCZ">SCZ</option>
                                        <option value="ORU">ORU</option>
                                        <option value="PT">PT</option>
                                        <option value="CH">CH</option>
                                        <option value="TJ">TJ</option>
                                        <option value="BN">BN</option>
                                        <option value="PD">PD</option>
                                    </select>
                                    <p x-show="shouldShow('expedido')" x-cloak x-text="validation.errors.expedido"
                                        class="ui-error"></p>
                                    @error('formEditar.exp_per') <p class="ui-error">{{ $message }}</p> @enderror
                                </div>

                                <div>
                                    <label class="ui-label">Fecha de nacimiento <span class="text-red-500">*</span></label>
                                    <input type="date" wire:model.live="formEditar.fec_nac_per" data-field="fecha"
                                        min="1906-01-01" max="{{ now()->format('Y-m-d') }}" x-on:change="touch('fecha')"
                                        class="ui-input">
                                    <p x-show="shouldShow('fecha')" x-cloak x-text="validation.errors.fecha"
                                        class="ui-error"></p>
                                    @error('formEditar.fec_nac_per') <p class="ui-error">{{ $message }}</p> @enderror
                                </div>

                                <div>
                                    <label class="ui-label">Género <span class="text-red-500">*</span></label>
                                    <select wire:model.live="formEditar.gen_per" data-field="genero"
                                        x-on:change="touch('genero')" class="ui-select">
                                        <option value="">Seleccionar</option>
                                        <option value="M">Masculino</option>
                                        <option value="F">Femenino</option>
                                    </select>
                                    <p x-show="shouldShow('genero')" x-cloak x-text="validation.errors.genero"
                                        class="ui-error"></p>
                                    @error('formEditar.gen_per') <p class="ui-error">{{ $message }}</p> @enderror
                                </div>

                                <div>
                                    <label class="ui-label">Teléfono</label>
                                    <input type="text" wire:model.live.debounce.300ms="formEditar.tel_per"
                                        data-field="telefono" maxlength="20" inputmode="tel" autocomplete="off"
                                        x-on:input="touch('telefono'); $event.target.value = cleanPhone($event.target.value)"
                                        class="ui-input" placeholder="Ej. 70123456">
                                    <p x-show="shouldShow('telefono')" x-cloak x-text="validation.errors.telefono"
                                        class="ui-error"></p>
                                    @error('formEditar.tel_per') <p class="ui-error">{{ $message }}</p> @enderror
                                </div>

                                <div>
                                    <label class="ui-label">Correo</label>
                                    <input type="email" wire:model.live.debounce.300ms="formEditar.ema_per"
                                        data-field="email" maxlength="150" autocomplete="off"
                                        x-on:input="touch('email'); $event.target.value = cleanEmail($event.target.value)"
                                        class="ui-input" placeholder="persona@gmail.com">
                                    <p x-show="shouldShow('email')" x-cloak x-text="validation.errors.email"
                                        class="ui-error"></p>
                                    @error('formEditar.ema_per') <p class="ui-error">{{ $message }}</p> @enderror
                                </div>
                            </div>

                            <section class="rounded-[1.7rem] border p-5"
                                style="background: var(--ui-surface-soft); border-color: var(--ui-border);">
                                <div class="flex flex-col gap-4 md:flex-row md:items-start md:justify-between">
                                    <div>
                                        <p class="text-xs font-black uppercase tracking-[0.16em]"
                                            style="color: var(--ui-primary);">
                                            Dirección
                                        </p>
                                        <h4 class="mt-1 text-lg font-black" style="color: var(--ui-text);">
                                            Completa o por campos
                                        </h4>
                                    </div>

                                    <div class="grid grid-cols-2 gap-2">
                                        <button type="button" wire:click="cambiarModoDireccionEditar('inteligente')"
                                            class="rounded-2xl border px-4 py-2 text-xs font-black transition"
                                            style="{{ $modoDireccionEditar === 'inteligente' ? 'background: var(--ui-primary-soft); border-color: var(--ui-primary-border); color: var(--ui-primary);' : 'background: var(--ui-surface); border-color: var(--ui-border); color: var(--ui-muted);' }}">
                                            Automática
                                        </button>

                                        <button type="button" wire:click="cambiarModoDireccionEditar('manual')"
                                            class="rounded-2xl border px-4 py-2 text-xs font-black transition"
                                            style="{{ $modoDireccionEditar === 'manual' ? 'background: var(--ui-primary-soft); border-color: var(--ui-primary-border); color: var(--ui-primary);' : 'background: var(--ui-surface); border-color: var(--ui-border); color: var(--ui-muted);' }}">
                                            Manual
                                        </button>
                                    </div>
                                </div>

                                <div class="mt-5">
                                    <label class="ui-label">Dirección completa</label>
                                    <input type="text" wire:model.live.debounce.500ms="formEditar.dir_per" maxlength="255"
                                        autocomplete="off" class="ui-input"
                                        placeholder="Ej. Calle Tocopilla, Zona Bajo Tejar, #1423">
                                    @error('formEditar.dir_per') <p class="ui-error">{{ $message }}</p> @enderror
                                </div>

                                <div class="mt-5 grid gap-4 md:grid-cols-2">
                                    <div>
                                        <label class="ui-label">Zona</label>
                                        <input type="text" wire:model.live.debounce.400ms="formEditar.zona_per"
                                            class="ui-input" placeholder="Ej. Bajo Tejar">
                                        @error('formEditar.zona_per') <p class="ui-error">{{ $message }}</p> @enderror
                                    </div>

                                    <div>
                                        <label class="ui-label">Avenida</label>
                                        <input type="text" wire:model.live.debounce.400ms="formEditar.ave_per"
                                            class="ui-input" placeholder="Ej. Buenos Aires">
                                        @error('formEditar.ave_per') <p class="ui-error">{{ $message }}</p> @enderror
                                    </div>

                                    <div>
                                        <label class="ui-label">Calle</label>
                                        <input type="text" wire:model.live.debounce.400ms="formEditar.cal_per"
                                            class="ui-input" placeholder="Ej. Tocopilla">
                                        @error('formEditar.cal_per') <p class="ui-error">{{ $message }}</p> @enderror
                                    </div>

                                    <div>
                                        <label class="ui-label">Número</label>
                                        <input type="text" wire:model.live.debounce.400ms="formEditar.num_per"
                                            class="ui-input" placeholder="Ej. 1423">
                                        @error('formEditar.num_per') <p class="ui-error">{{ $message }}</p> @enderror
                                    </div>

                                    <div>
                                        <label class="ui-label">Ciudad</label>
                                        <input type="text" wire:model.live.debounce.400ms="formEditar.ciu_per"
                                            class="ui-input" placeholder="Ej. La Paz">
                                        @error('formEditar.ciu_per') <p class="ui-error">{{ $message }}</p> @enderror
                                    </div>

                                    <div>
                                        <label class="ui-label">Municipio</label>
                                        <input type="text" wire:model.live.debounce.400ms="formEditar.mun_per"
                                            class="ui-input" placeholder="Ej. La Paz">
                                        @error('formEditar.mun_per') <p class="ui-error">{{ $message }}</p> @enderror
                                    </div>

                                    <div>
                                        <label class="ui-label">Departamento</label>
                                        <input type="text" wire:model.live.debounce.400ms="formEditar.dep_per"
                                            class="ui-input" placeholder="Ej. La Paz">
                                        @error('formEditar.dep_per') <p class="ui-error">{{ $message }}</p> @enderror
                                    </div>

                                    <div>
                                        <label class="ui-label">Referencia</label>
                                        <input type="text" wire:model.live.debounce.400ms="formEditar.ref_per"
                                            class="ui-input" placeholder="Ej. Cerca del mercado">
                                        @error('formEditar.ref_per') <p class="ui-error">{{ $message }}</p> @enderror
                                    </div>
                                </div>
                            </section>

                            <div>
                                <label class="ui-label">Estado</label>
                                <select wire:model.live="formEditar.est_per" class="ui-select">
                                    <option value="1">Activo</option>
                                    <option value="0">Inactivo</option>
                                </select>
                                @error('formEditar.est_per') <p class="ui-error">{{ $message }}</p> @enderror
                            </div>
                        </div>

                        <aside class="space-y-4">
                            <section class="rounded-[1.7rem] border p-5"
                                style="background: var(--ui-surface-soft); border-color: var(--ui-border);">
                                <div class="flex items-start justify-between gap-3">
                                    <div>
                                        <p class="text-xs font-black uppercase tracking-[0.16em]"
                                            style="color: var(--ui-muted);">
                                            Revisión del registro
                                        </p>
                                        <h4 class="mt-1 text-xl font-black" style="color: var(--ui-text);">
                                            Estado de edición
                                        </h4>
                                    </div>

                                    <span
                                        class="rounded-full border px-3 py-1 text-xs font-black {{ $badgeRevision($estadoAnalisisEditar, $puedeContinuarEditar) }}">
                                        {{ $estadoAnalisisEditar }}
                                    </span>
                                </div>

                                <div
                                    class="mt-4 rounded-2xl border p-4 {{ $badgeRevision($estadoAnalisisEditar, $puedeContinuarEditar) }}">
                                    <p class="text-sm font-black">
                                        {{ $analisisPersonaEditar['mensaje'] ?? 'Modifica datos para revisar el registro.' }}
                                    </p>
                                </div>

                                @if (!empty($analisisPersonaEditar['bloqueos'] ?? []))
                                    <div class="mt-4 space-y-2">
                                        @foreach ($analisisPersonaEditar['bloqueos'] as $bloqueo)
                                            <div
                                                class="rounded-2xl border border-rose-200 bg-rose-50 px-4 py-3 text-xs font-bold leading-5 text-rose-800 dark:border-rose-500/30 dark:bg-rose-950 dark:text-rose-200">
                                                {{ $bloqueo }}
                                            </div>
                                        @endforeach
                                    </div>
                                @endif

                                @if (!empty($analisisPersonaEditar['advertencias'] ?? []))
                                    <div class="mt-4 space-y-2">
                                        @foreach ($analisisPersonaEditar['advertencias'] as $advertencia)
                                            <div
                                                class="rounded-2xl border border-amber-200 bg-amber-50 px-4 py-3 text-xs font-bold leading-5 text-amber-800 dark:border-amber-500/30 dark:bg-amber-950 dark:text-amber-200">
                                                {{ $advertencia }}
                                            </div>
                                        @endforeach
                                    </div>
                                @endif
                            </section>

                            <section class="rounded-[1.7rem] border p-5"
                                style="background: var(--ui-surface); border-color: var(--ui-border);">
                                <p class="text-xs font-black uppercase tracking-[0.16em]" style="color: var(--ui-muted);">
                                    Vista previa
                                </p>

                                <div class="mt-4 space-y-3">
                                    <div class="rounded-2xl border px-4 py-3"
                                        style="background: var(--ui-surface-soft); border-color: var(--ui-border);">
                                        <p class="text-xs font-black uppercase tracking-[0.12em]"
                                            style="color: var(--ui-muted);">Nombre</p>
                                        <p class="mt-1 text-sm font-black" style="color: var(--ui-text);">
                                            {{ trim(($formEditar['nom_per'] ?? '') . ' ' . ($formEditar['ape_pat_per'] ?? '') . ' ' . ($formEditar['ape_mat_per'] ?? '')) ?: 'Sin nombre completo' }}
                                        </p>
                                    </div>

                                    <div class="rounded-2xl border px-4 py-3"
                                        style="background: var(--ui-surface-soft); border-color: var(--ui-border);">
                                        <p class="text-xs font-black uppercase tracking-[0.12em]"
                                            style="color: var(--ui-muted);">Dirección</p>
                                        <p class="mt-1 text-sm font-black leading-6" style="color: var(--ui-text);">
                                            {{ $formEditar['dir_per'] ?: 'Sin dirección registrada' }}
                                        </p>
                                    </div>
                                </div>
                            </section>
                        </aside>
                    </div>
                </div>

                <div class="border-t px-6 py-4" style="border-color: var(--ui-border); background: var(--ui-surface);">
                    <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                        <p x-show="showErrors && (!validation.valid || !@js($puedeContinuarEditar))" x-cloak
                            class="text-sm font-bold" style="color: var(--ui-danger);">
                            Corrige los campos marcados antes de actualizar.
                        </p>

                        <p x-show="validation.valid && @js($puedeContinuarEditar)" x-cloak class="text-sm font-bold"
                            style="color: var(--ui-primary);">
                            Registro listo para actualizar.
                        </p>

                        <div class="flex flex-wrap gap-3">
                            <button type="button" wire:click="cerrarModalEditar" class="ui-btn-secondary">
                                Cancelar
                            </button>

                            <button type="button" x-on:click="submitEdit()" wire:loading.attr="disabled"
                                wire:target="actualizarPersona"
                                x-bind:disabled="!validation.valid || !@js($puedeContinuarEditar)"
                                x-bind:class="(validation.valid && @js($puedeContinuarEditar)) ? 'ui-btn-primary' : 'ui-btn cursor-not-allowed bg-slate-300 text-slate-500 shadow-none'"
                                class="rounded-2xl px-5 py-3 text-sm font-bold transition">
                                <svg wire:loading wire:target="actualizarPersona" class="h-4 w-4 animate-spin" fill="none"
                                    viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor"
                                        stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 0 1 8-8v4a4 4 0 0 0-4 4H4z">
                                    </path>
                                </svg>
                                <span wire:loading.remove wire:target="actualizarPersona">Guardar cambios</span>
                                <span wire:loading wire:target="actualizarPersona">Validando...</span>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @endif

    @push('scripts')
        <script>
            function gestionPersonasPage() {
                return {
                    view: localStorage.getItem('gestion-personas-view') || 'tabla',

                    init() {
                        this.$nextTick(() => {
                            iniciarGraficosPersonas();
                        });
                    },

                    setView(view) {
                        this.view = view;
                        localStorage.setItem('gestion-personas-view', view);
                    },

                    notify(icon, title, text) {
                        if (!window.Swal) return;

                        Swal.fire({
                            icon,
                            title,
                            text,
                            confirmButtonText: 'Entendido',
                            confirmButtonColor: '#059669',
                            background: document.documentElement.classList.contains('dark') ? '#020617' : '#ffffff',
                            color: document.documentElement.classList.contains('dark') ? '#e5e7eb' : '#0f172a',
                            showClass: {
                                popup: 'swal2-show'
                            },
                            hideClass: {
                                popup: 'swal2-hide'
                            }
                        });
                    },

                    confirmarEstado(tipo, codPer, nombre) {
                        const desactivar = tipo === 'desactivar';

                        if (!window.Swal) {
                            desactivar ? @this.desactivarPersona(codPer) : @this.reactivarPersona(codPer);
                            return;
                        }

                        Swal.fire({
                            icon: desactivar ? 'warning' : 'question',
                            title: desactivar ? '¿Desactivar registro?' : '¿Reactivar registro?',
                            text: desactivar
                                ? `El registro de ${nombre} no será eliminado físicamente. Solo quedará inactivo.`
                                : `El registro de ${nombre} volverá a estar activo.`,
                            showCancelButton: true,
                            confirmButtonText: desactivar ? 'Sí, desactivar' : 'Sí, reactivar',
                            cancelButtonText: 'Cancelar',
                            confirmButtonColor: desactivar ? '#d97706' : '#059669',
                            cancelButtonColor: '#64748b',
                            background: document.documentElement.classList.contains('dark') ? '#020617' : '#ffffff',
                            color: document.documentElement.classList.contains('dark') ? '#e5e7eb' : '#0f172a',
                        }).then((result) => {
                            if (!result.isConfirmed) return;

                            desactivar ? @this.desactivarPersona(codPer) : @this.reactivarPersona(codPer);
                        });
                    }
                }
            }

            function personaFormValidation(mode) {
                return {
                    mode,
                    touched: {},
                    submitted: false,
                    showErrors: false,
                    validation: {
                        valid: false,
                        errors: {},
                        list: [],
                    },

                    init() {
                        this.$nextTick(() => this.validate());
                    },

                    touch(field) {
                        this.touched[field] = true;
                        this.validate();
                    },

                    shouldShow(field) {
                        return Boolean(this.validation.errors[field]) && (this.touched[field] || this.showErrors);
                    },

                    submitCreate() {
                        this.submitted = true;
                        this.showErrors = true;
                        this.validate();

                        if (!this.validation.valid) return;

                        @this.guardarPersona();
                    },

                    submitEdit() {
                        this.submitted = true;
                        this.showErrors = true;
                        this.validate();

                        if (!this.validation.valid) return;

                        @this.actualizarPersona();
                    },

                    validate() {
                        this.validation = validatePersonaForm(this.$root);
                    },

                    cleanLetters(value) {
                        return String(value ?? '')
                            .replace(/[^A-Za-zÁÉÍÓÚáéíóúÑñÜü\s]/g, '')
                            .replace(/\s{2,}/g, ' ');
                    },

                    cleanNumbers(value) {
                        return String(value ?? '').replace(/[^0-9]/g, '');
                    },

                    cleanComplement(value) {
                        return String(value ?? '')
                            .replace(/[^0-9A-Za-z]/g, '')
                            .toUpperCase()
                            .slice(0, 2);
                    },

                    cleanEmail(value) {
                        return String(value ?? '').trim().toLowerCase();
                    },

                    cleanPhone(value) {
                        return String(value ?? '')
                            .replace(/[^0-9+\-\s]/g, '')
                            .replace(/\s{2,}/g, ' ');
                    }
                }
            }

            function getFieldValue(root, field) {
                return root.querySelector(`[data-field="${field}"]`)?.value?.trim() ?? '';
            }

            function markField(root, field, hasError) {
                const input = root.querySelector(`[data-field="${field}"]`);

                if (!input) return;

                input.classList.toggle('ring-2', hasError);
                input.classList.toggle('ring-red-400', hasError);
                input.classList.toggle('border-red-400', hasError);
            }

            function validatePersonaForm(root) {
                const errors = {};

                const nombre = getFieldValue(root, 'nombre');
                const apellido = getFieldValue(root, 'apellido');
                const ci = getFieldValue(root, 'ci');
                const complemento = getFieldValue(root, 'complemento');
                const expedido = getFieldValue(root, 'expedido');
                const fecha = getFieldValue(root, 'fecha');
                const genero = getFieldValue(root, 'genero');
                const telefono = getFieldValue(root, 'telefono');
                const email = getFieldValue(root, 'email').toLowerCase();

                if (nombre.length === 0) {
                    errors.nombre = 'El nombre es obligatorio.';
                } else if (nombre.length < 2) {
                    errors.nombre = 'El nombre debe tener al menos 2 letras.';
                } else if (!/^[A-Za-zÁÉÍÓÚáéíóúÑñÜü\s]+$/.test(nombre)) {
                    errors.nombre = 'El nombre solo debe contener letras.';
                }

                if (apellido.length === 0) {
                    errors.apellido = 'El apellido paterno es obligatorio.';
                } else if (apellido.length < 2) {
                    errors.apellido = 'El apellido paterno debe tener al menos 2 letras.';
                } else if (!/^[A-Za-zÁÉÍÓÚáéíóúÑñÜü\s]+$/.test(apellido)) {
                    errors.apellido = 'El apellido paterno solo debe contener letras.';
                }

                if (ci.length === 0) {
                    errors.ci = 'El CI es obligatorio.';
                } else if (!/^[0-9]+$/.test(ci)) {
                    errors.ci = 'El CI solo debe contener números.';
                } else if (!/^[0-9]{4,12}$/.test(ci)) {
                    errors.ci = 'El CI debe tener entre 4 y 12 números.';
                }

                if (complemento !== '' && !/^[0-9][A-Z]$/.test(complemento)) {
                    errors.complemento = 'El complemento debe tener formato número + letra. Ejemplo: 1A.';
                }

                if (expedido === '') {
                    errors.expedido = 'Debes seleccionar el lugar de expedición.';
                } else if (!['LP', 'CBBA', 'SCZ', 'ORU', 'PT', 'CH', 'TJ', 'BN', 'PD'].includes(expedido)) {
                    errors.expedido = 'La expedición seleccionada no es válida.';
                }

                if (fecha === '') {
                    errors.fecha = 'La fecha de nacimiento es obligatoria.';
                } else {
                    const nacimiento = new Date(fecha + 'T00:00:00');
                    const minima = new Date();
                    minima.setFullYear(minima.getFullYear() - 120);
                    const maxima = new Date();

                    if (Number.isNaN(nacimiento.getTime())) {
                        errors.fecha = 'La fecha de nacimiento no es válida.';
                    } else if (nacimiento < minima) {
                        errors.fecha = 'La fecha de nacimiento no es coherente.';
                    } else if (nacimiento > maxima) {
                        errors.fecha = 'La fecha de nacimiento no puede ser futura.';
                    }
                }

                if (genero === '') {
                    errors.genero = 'Debes seleccionar el género.';
                } else if (!['M', 'F'].includes(genero)) {
                    errors.genero = 'El género seleccionado no es válido.';
                }

                if (telefono !== '' && !/^[0-9+\-\s]{6,20}$/.test(telefono)) {
                    errors.telefono = 'El teléfono es opcional, pero si se llena debe tener formato válido.';
                }

                if (email !== '' && !/^[^\s@]+@[^\s@]+\.[^\s@]{2,}$/.test(email)) {
                    errors.email = 'El correo es opcional, pero si se llena debe tener formato válido.';
                }

                [
                    'nombre',
                    'apellido',
                    'ci',
                    'complemento',
                    'expedido',
                    'fecha',
                    'genero',
                    'telefono',
                    'email',
                ].forEach(field => markField(root, field, Boolean(errors[field])));

                return {
                    valid: Object.keys(errors).length === 0,
                    errors,
                    list: Object.values(errors),
                };
            }

            document.addEventListener('DOMContentLoaded', iniciarGraficosPersonas);
            document.addEventListener('livewire:navigated', iniciarGraficosPersonas);
            window.addEventListener('theme-changed', iniciarGraficosPersonas);
            window.addEventListener('actualizar-graficos-personas', () => {
                setTimeout(() => iniciarGraficosPersonas(), 120);
            });

            let chartEstadoPersonas = null;
            let chartGeneroPersonas = null;
            let chartUsuariosPersonas = null;

            function getChartThemePersonas() {
                const styles = getComputedStyle(document.documentElement);

                return {
                    text: styles.getPropertyValue('--ui-text').trim() || '#0f172a',
                    muted: styles.getPropertyValue('--ui-muted').trim() || '#64748b',
                    border: styles.getPropertyValue('--ui-border').trim() || '#e2e8f0',
                    surface: styles.getPropertyValue('--ui-surface').trim() || '#ffffff',
                    primary: styles.getPropertyValue('--ui-primary').trim() || '#059669',
                    info: styles.getPropertyValue('--ui-info').trim() || '#0284c7',
                    violet: styles.getPropertyValue('--ui-violet').trim() || '#7c3aed',
                    warning: styles.getPropertyValue('--ui-warning').trim() || '#d97706',
                    danger: styles.getPropertyValue('--ui-danger').trim() || '#dc2626',
                };
            }

            function iniciarGraficosPersonas() {
                const estadoCanvas = document.getElementById('chartEstadoPersonas');
                const generoCanvas = document.getElementById('chartGeneroPersonas');
                const usuariosCanvas = document.getElementById('chartUsuariosPersonas');

                if (!estadoCanvas || !generoCanvas || !usuariosCanvas) return;
                if (!window.Chart) return;

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
                            backgroundColor: [theme.primary, theme.danger],
                            borderColor: theme.surface,
                            borderWidth: 4,
                            hoverOffset: 10,
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        cutout: '70%',
                        animation: {
                            animateRotate: true,
                            animateScale: true,
                            duration: 900,
                            easing: 'easeOutQuart',
                        },
                        plugins: {
                            legend: {
                                position: 'bottom',
                                labels: {
                                    color: theme.muted,
                                    usePointStyle: true,
                                    pointStyle: 'circle',
                                    padding: 18,
                                    font: { size: 12, weight: '700' },
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
                            borderRadius: 12,
                            maxBarThickness: 48,
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        animation: {
                            duration: 850,
                            easing: 'easeOutQuart',
                        },
                        plugins: { legend: { display: false } },
                        scales: {
                            x: {
                                ticks: { color: theme.muted, font: { weight: '700' } },
                                grid: { display: false },
                                border: { color: theme.border }
                            },
                            y: {
                                beginAtZero: true,
                                ticks: { color: theme.muted, precision: 0, font: { weight: '700' } },
                                grid: { color: theme.border },
                                border: { color: theme.border }
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
                            backgroundColor: [theme.info, theme.warning],
                            borderColor: theme.surface,
                            borderWidth: 4,
                            hoverOffset: 10,
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        cutout: '70%',
                        animation: {
                            animateRotate: true,
                            animateScale: true,
                            duration: 900,
                            easing: 'easeOutQuart',
                        },
                        plugins: {
                            legend: {
                                position: 'bottom',
                                labels: {
                                    color: theme.muted,
                                    usePointStyle: true,
                                    pointStyle: 'circle',
                                    padding: 18,
                                    font: { size: 12, weight: '700' },
                                }
                            }
                        }
                    }
                });
            }
        </script>
    @endpush
</div>