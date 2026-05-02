@php
    $sesiones = collect($this->sessions ?? []);
    $otrasSesiones = $sesiones->where('is_current_device', false)->count();
    $sesionesTotales = $sesiones->count();
@endphp

<div
    class="relative overflow-hidden rounded-[1.8rem] border border-[var(--ui-border)] bg-[var(--ui-card)] p-5 shadow-sm sm:p-6">

    {{-- Fondos suaves --}}
    <div class="pointer-events-none absolute -right-24 -top-24 h-64 w-64 rounded-full bg-sky-400/10 blur-3xl"></div>
    <div class="pointer-events-none absolute -bottom-24 left-10 h-64 w-64 rounded-full bg-emerald-400/10 blur-3xl">
    </div>
    <div class="pointer-events-none absolute bottom-0 right-1/3 h-52 w-52 rounded-full bg-violet-400/10 blur-3xl"></div>

    <div class="relative space-y-6">

        {{-- ============================================================
        CABECERA
        ============================================================ --}}
        <div class="flex flex-col gap-5 xl:flex-row xl:items-start xl:justify-between">
            <div class="max-w-3xl">
                <div class="flex flex-wrap items-center gap-2">
                    <span
                        class="inline-flex items-center gap-2 rounded-full border border-sky-200/70 bg-sky-50 px-3 py-1 text-xs font-black uppercase tracking-[0.14em] text-sky-700 dark:border-sky-400/20 dark:bg-sky-400/10 dark:text-sky-200">
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"
                                d="M12 3.75 4.5 6.75v5.625c0 4.038 3.06 7.82 7.5 8.875 4.44-1.055 7.5-4.837 7.5-8.875V6.75L12 3.75Z" />
                        </svg>
                        Seguridad de acceso
                    </span>

                    @if ($otrasSesiones > 0)
                        <span
                            class="inline-flex rounded-full border border-amber-200 bg-amber-50 px-3 py-1 text-xs font-bold text-amber-700 dark:border-amber-400/20 dark:bg-amber-400/10 dark:text-amber-300">
                            {{ $otrasSesiones }} {{ $otrasSesiones === 1 ? 'sesión externa' : 'sesiones externas' }}
                        </span>
                    @else
                        <span
                            class="inline-flex rounded-full border border-emerald-200 bg-emerald-50 px-3 py-1 text-xs font-bold text-emerald-700 dark:border-emerald-400/20 dark:bg-emerald-400/10 dark:text-emerald-300">
                            Sin accesos externos activos
                        </span>
                    @endif
                </div>

                <h3 class="mt-4 text-xl font-black tracking-tight text-[var(--ui-text)]">
                    Sesiones activas
                </h3>

                <p class="mt-2 text-sm leading-7 text-[var(--ui-muted)]">
                    Administra los accesos abiertos en otros navegadores y dispositivos. Tu sesión actual permanecerá
                    activa para evitar interrumpir tu trabajo dentro del sistema.
                </p>
            </div>

            <div class="grid grid-cols-2 gap-3 xl:min-w-[300px]">
                <div class="rounded-2xl border border-[var(--ui-border)] bg-[var(--ui-soft)] px-4 py-3">
                    <p class="text-xs font-bold uppercase tracking-[0.12em] text-[var(--ui-muted)]">
                        Total
                    </p>
                    <p class="mt-1 text-3xl font-black text-[var(--ui-text)]">
                        {{ $sesionesTotales }}
                    </p>
                    <p class="mt-1 text-xs text-[var(--ui-muted)]">
                        Dispositivos detectados
                    </p>
                </div>

                <div class="rounded-2xl border border-[var(--ui-border)] bg-[var(--ui-soft)] px-4 py-3">
                    <p class="text-xs font-bold uppercase tracking-[0.12em] text-[var(--ui-muted)]">
                        Externas
                    </p>
                    <p
                        class="mt-1 text-3xl font-black {{ $otrasSesiones > 0 ? 'text-amber-600 dark:text-amber-300' : 'text-emerald-600 dark:text-emerald-300' }}">
                        {{ $otrasSesiones }}
                    </p>
                    <p class="mt-1 text-xs text-[var(--ui-muted)]">
                        Fuera del actual
                    </p>
                </div>
            </div>
        </div>

        {{-- ============================================================
        AVISO
        ============================================================ --}}
        <div
            class="rounded-2xl border px-4 py-4 text-sm leading-6
            {{ $otrasSesiones > 0
    ? 'border-amber-200 bg-amber-50 text-amber-800 dark:border-amber-400/20 dark:bg-amber-400/10 dark:text-amber-200'
    : 'border-sky-200 bg-sky-50 text-sky-800 dark:border-sky-400/20 dark:bg-sky-400/10 dark:text-sky-200' }}">
            @if ($otrasSesiones > 0)
                Se
                {{ $otrasSesiones === 1 ? 'cerrará 1 sesión activa en otro dispositivo' : 'cerrarán ' . $otrasSesiones . ' sesiones activas en otros dispositivos' }}.
                Cuando esos dispositivos intenten continuar, deberán volver a iniciar sesión.
            @else
                Actualmente no hay otras sesiones activas fuera del dispositivo que estás utilizando.
            @endif
        </div>

        {{-- ============================================================
        LISTA DE SESIONES
        ============================================================ --}}
        @if ($sesionesTotales > 0)
            <section class="space-y-4">
                <div class="flex flex-col gap-1 sm:flex-row sm:items-end sm:justify-between">
                    <div>
                        <p class="text-sm font-black uppercase tracking-[0.16em] text-[var(--ui-muted)]">
                            Dispositivos reconocidos
                        </p>
                        <p class="mt-1 text-xs text-[var(--ui-muted)]">
                            Revisa desde dónde se encuentra abierta tu cuenta institucional.
                        </p>
                    </div>
                </div>

                <div class="grid gap-3">
                    @foreach ($this->sessions as $session)
                        <article
                            class="group rounded-2xl border border-[var(--ui-border)] bg-[var(--ui-soft)] p-4 transition hover:border-[var(--ui-primary)]/40 hover:bg-[var(--ui-card)]">
                            <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">

                                <div class="flex min-w-0 items-start gap-4">
                                    {{-- ICONO --}}
                                    <div
                                        class="flex h-12 w-12 shrink-0 items-center justify-center rounded-2xl border border-[var(--ui-border)] bg-[var(--ui-card)] text-[var(--ui-muted)] transition group-hover:text-[var(--ui-primary)]">
                                        @if ($session->agent->isDesktop())
                                            <svg class="h-7 w-7" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                                                stroke-width="1.7">
                                                <path stroke-linecap="round" stroke-linejoin="round"
                                                    d="M9 17.25v1.007a3 3 0 0 1-.879 2.122L7.5 21h9l-.621-.621A3 3 0 0 1 15 18.257V17.25m6-12V15a2.25 2.25 0 0 1-2.25 2.25H5.25A2.25 2.25 0 0 1 3 15V5.25m18 0A2.25 2.25 0 0 0 18.75 3H5.25A2.25 2.25 0 0 0 3 5.25m18 0V12a2.25 2.25 0 0 1-2.25 2.25H5.25A2.25 2.25 0 0 1 3 12V5.25" />
                                            </svg>
                                        @else
                                            <svg class="h-7 w-7" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                                                stroke-width="1.7">
                                                <path stroke-linecap="round" stroke-linejoin="round"
                                                    d="M10.5 1.5H8.25A2.25 2.25 0 0 0 6 3.75v16.5a2.25 2.25 0 0 0 2.25 2.25h7.5A2.25 2.25 0 0 0 18 20.25V3.75a2.25 2.25 0 0 0-2.25-2.25H13.5m-3 0V3h3V1.5m-3 0h3m-3 18.75h3" />
                                            </svg>
                                        @endif
                                    </div>

                                    {{-- INFO --}}
                                    <div class="min-w-0 flex-1">
                                        <p class="text-sm font-black text-[var(--ui-text)]">
                                            {{ $session->agent->platform() ?: 'Plataforma no identificada' }}
                                            ·
                                            {{ $session->agent->browser() ?: 'Navegador no identificado' }}
                                        </p>

                                        <div class="mt-2 flex flex-wrap gap-2 text-xs">
                                            <span
                                                class="rounded-full border border-[var(--ui-border)] bg-[var(--ui-card)] px-3 py-1 font-semibold text-[var(--ui-muted)]">
                                                IP: {{ $session->ip_address }}
                                            </span>

                                            @unless ($session->is_current_device)
                                                <span
                                                    class="rounded-full border border-[var(--ui-border)] bg-[var(--ui-card)] px-3 py-1 font-semibold text-[var(--ui-muted)]">
                                                    Última actividad {{ $session->last_active }}
                                                </span>
                                            @endunless
                                        </div>
                                    </div>
                                </div>

                                <div class="shrink-0">
                                    @if ($session->is_current_device)
                                        <span
                                            class="inline-flex rounded-full border border-emerald-200 bg-emerald-50 px-3 py-1 text-xs font-bold text-emerald-700 dark:border-emerald-400/20 dark:bg-emerald-400/10 dark:text-emerald-300">
                                            Este dispositivo
                                        </span>
                                    @else
                                        <span
                                            class="inline-flex rounded-full border border-amber-200 bg-amber-50 px-3 py-1 text-xs font-bold text-amber-700 dark:border-amber-400/20 dark:bg-amber-400/10 dark:text-amber-300">
                                            Sesión externa
                                        </span>
                                    @endif
                                </div>
                            </div>
                        </article>
                    @endforeach
                </div>
            </section>
        @endif

        {{-- ============================================================
        ACCIÓN PRINCIPAL
        ============================================================ --}}
        <div
            class="flex flex-col gap-3 border-t border-[var(--ui-border)] pt-5 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <p class="text-sm font-black text-[var(--ui-text)]">
                    Cierre de accesos externos
                </p>
                <p class="mt-1 text-xs leading-5 text-[var(--ui-muted)]">
                    Se solicitará tu contraseña para confirmar esta acción de seguridad.
                </p>
            </div>

            <div class="flex flex-wrap items-center gap-3">
                <x-action-message class="text-sm font-bold text-emerald-700 dark:text-emerald-300" on="loggedOut">
                    Sesiones cerradas correctamente.
                </x-action-message>

                <button type="button" wire:click="confirmLogout" wire:loading.attr="disabled"
                    class="inline-flex items-center gap-2 rounded-2xl bg-gradient-to-r from-emerald-600 to-sky-600 px-5 py-3 text-sm font-bold text-white shadow-lg shadow-emerald-500/20 transition hover:-translate-y-0.5 hover:shadow-xl disabled:cursor-not-allowed disabled:opacity-60">
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"
                            d="M15.75 9V5.25A2.25 2.25 0 0 0 13.5 3h-7.5A2.25 2.25 0 0 0 3.75 5.25v13.5A2.25 2.25 0 0 0 6 21h7.5a2.25 2.25 0 0 0 2.25-2.25V15m-6-3h12m0 0-3-3m3 3-3 3" />
                    </svg>
                    Cerrar otras sesiones
                </button>
            </div>
        </div>
    </div>

    {{-- ============================================================
    MODAL DE CONFIRMACIÓN
    ============================================================ --}}
    <x-dialog-modal wire:model.live="confirmingLogout">
        <x-slot name="title">
            <div class="flex items-center gap-3">
                <div
                    class="flex h-10 w-10 items-center justify-center rounded-xl bg-emerald-100 text-emerald-700 dark:bg-emerald-400/10 dark:text-emerald-300">
                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M15.75 9V5.25A2.25 2.25 0 0 0 13.5 3h-7.5A2.25 2.25 0 0 0 3.75 5.25v13.5A2.25 2.25 0 0 0 6 21h7.5a2.25 2.25 0 0 0 2.25-2.25V15m-6-3h12m0 0-3-3m3 3-3 3" />
                    </svg>
                </div>

                <div>
                    <h2 class="text-base font-black text-[var(--ui-text)]">
                        Confirmar cierre de sesiones
                    </h2>
                    <p class="text-xs text-[var(--ui-muted)]">
                        Validación de seguridad institucional
                    </p>
                </div>
            </div>
        </x-slot>

        <x-slot name="content">
            <div class="space-y-5">
                <p class="text-sm leading-7 text-[var(--ui-muted)]">
                    Estás a punto de cerrar todas las sesiones abiertas en otros navegadores y dispositivos.
                    Tu sesión actual permanecerá activa.
                </p>

                <div
                    class="rounded-2xl border border-amber-200 bg-amber-50 px-4 py-3 text-sm leading-6 text-amber-800 dark:border-amber-400/20 dark:bg-amber-400/10 dark:text-amber-200">
                    @if ($otrasSesiones > 0)
                        Se
                        {{ $otrasSesiones === 1 ? 'cerrará 1 sesión externa' : 'cerrarán ' . $otrasSesiones . ' sesiones externas' }}.
                        Para continuar, confirma tu contraseña actual.
                    @else
                        No se detectaron otras sesiones activas, pero puedes continuar si deseas verificar el cierre de
                        accesos externos.
                    @endif
                </div>

                <div class="rounded-2xl border border-[var(--ui-border)] bg-[var(--ui-soft)] p-4"
                    x-data="{ showPassword: false }"
                    x-on:confirming-logout-other-browser-sessions.window="setTimeout(() => $refs.password.focus(), 250)">
                    <label for="password" class="block text-sm font-bold text-[var(--ui-text)]">
                        Contraseña actual
                    </label>

                    <div class="relative mt-2">
                        <input id="password" x-bind:type="showPassword ? 'text' : 'password'"
                            class="ui-input block w-full pr-12" autocomplete="current-password"
                            placeholder="Ingresa tu contraseña" x-ref="password" wire:model="password"
                            wire:keydown.enter="logoutOtherBrowserSessions" />

                        <button type="button" @click="showPassword = !showPassword"
                            class="absolute inset-y-0 right-0 flex items-center pr-4 text-[var(--ui-muted)] transition hover:text-[var(--ui-text)]"
                            title="Mostrar u ocultar contraseña">
                            <svg x-show="!showPassword" class="h-5 w-5" fill="none" viewBox="0 0 24 24"
                                stroke="currentColor" stroke-width="1.8">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7Z" />
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M12 15.75A3.75 3.75 0 1 0 12 8.25a3.75 3.75 0 0 0 0 7.5Z" />
                            </svg>

                            <svg x-show="showPassword" x-cloak class="h-5 w-5" fill="none" viewBox="0 0 24 24"
                                stroke="currentColor" stroke-width="1.8">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M3 3l18 18" />
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M10.584 10.587A2.25 2.25 0 0 0 13.41 13.41" />
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M9.88 5.09A9.953 9.953 0 0 1 12 4.875c5.053 0 9.27 3.11 10.5 7.125a11.03 11.03 0 0 1-4.04 5.411M6.228 6.228A11.03 11.03 0 0 0 1.5 12c.69 2.25 2.14 4.175 4.04 5.411A9.953 9.953 0 0 0 12 19.125c.73 0 1.442-.078 2.125-.227" />
                            </svg>
                        </button>
                    </div>

                    <x-input-error for="password" class="mt-2" />
                </div>
            </div>
        </x-slot>

        <x-slot name="footer">
            <button type="button" wire:click="$toggle('confirmingLogout')" wire:loading.attr="disabled"
                class="inline-flex items-center justify-center rounded-2xl border border-[var(--ui-border)] bg-[var(--ui-card)] px-5 py-3 text-sm font-bold text-[var(--ui-text)] transition hover:bg-[var(--ui-soft)] disabled:cursor-not-allowed disabled:opacity-60">
                Cancelar
            </button>

            <button type="button" wire:click="logoutOtherBrowserSessions" wire:loading.attr="disabled"
                class="ms-3 inline-flex items-center justify-center rounded-2xl bg-gradient-to-r from-emerald-600 to-sky-600 px-5 py-3 text-sm font-bold text-white shadow-lg shadow-emerald-500/20 transition hover:-translate-y-0.5 hover:shadow-xl disabled:cursor-not-allowed disabled:opacity-60">
                Confirmar cierre
            </button>
        </x-slot>
    </x-dialog-modal>
</div>