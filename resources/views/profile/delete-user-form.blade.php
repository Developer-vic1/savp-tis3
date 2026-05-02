<div
    class="relative overflow-hidden rounded-[1.8rem] border border-rose-200 bg-rose-50/80 p-5 shadow-sm dark:border-rose-400/20 dark:bg-rose-400/10 sm:p-6">

    {{-- Fondos suaves --}}
    <div class="pointer-events-none absolute -right-24 -top-24 h-64 w-64 rounded-full bg-rose-400/10 blur-3xl"></div>
    <div class="pointer-events-none absolute -bottom-24 left-10 h-64 w-64 rounded-full bg-amber-400/10 blur-3xl"></div>
    <div class="pointer-events-none absolute bottom-0 right-1/3 h-52 w-52 rounded-full bg-red-400/10 blur-3xl"></div>

    <div class="relative space-y-6">

        {{-- ============================================================
        CABECERA
        ============================================================ --}}
        <div class="flex flex-col gap-5 xl:flex-row xl:items-start xl:justify-between">
            <div class="max-w-3xl">
                <div class="flex flex-wrap items-center gap-2">
                    <span
                        class="inline-flex items-center gap-2 rounded-full border border-rose-200 bg-white/70 px-3 py-1 text-xs font-black uppercase tracking-[0.14em] text-rose-700 dark:border-rose-400/20 dark:bg-rose-400/10 dark:text-rose-300">
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"
                                d="M12 9v3.75m0 3.75h.008v.008H12v-.008ZM10.29 3.86 1.82 18a1.875 1.875 0 0 0 1.607 2.84h17.146A1.875 1.875 0 0 0 22.18 18L13.71 3.86a1.875 1.875 0 0 0-3.42 0Z" />
                        </svg>
                        Zona crítica
                    </span>

                    <span
                        class="inline-flex rounded-full border border-amber-200 bg-amber-50 px-3 py-1 text-xs font-bold text-amber-700 dark:border-amber-400/20 dark:bg-amber-400/10 dark:text-amber-300">
                        Requiere contraseña
                    </span>

                    <span
                        class="inline-flex rounded-full border border-rose-200 bg-rose-100 px-3 py-1 text-xs font-bold text-rose-700 dark:border-rose-400/20 dark:bg-rose-400/10 dark:text-rose-300">
                        Acción irreversible
                    </span>
                </div>

                <h3 class="mt-4 text-xl font-black tracking-tight text-rose-950 dark:text-rose-100">
                    Eliminación permanente de cuenta
                </h3>

                <p class="mt-2 text-sm leading-7 text-rose-800/85 dark:text-rose-100/80">
                    Esta sección permite solicitar la eliminación de tu cuenta de acceso. En un sistema institucional,
                    esta operación debe considerarse de alto impacto porque puede afectar el acceso, los recursos
                    asociados y la continuidad de uso dentro de SAVP – TIS 3.
                </p>
            </div>

            <div class="grid grid-cols-2 gap-3 xl:min-w-[320px]">
                <div
                    class="rounded-2xl border border-rose-200 bg-white/70 px-4 py-3 dark:border-rose-400/20 dark:bg-slate-950/40">
                    <p class="text-xs font-bold uppercase tracking-[0.12em] text-rose-700 dark:text-rose-300">
                        Nivel
                    </p>
                    <p class="mt-1 text-2xl font-black text-rose-800 dark:text-rose-200">
                        Alto
                    </p>
                    <p class="mt-1 text-xs text-rose-700/80 dark:text-rose-200/70">
                        Riesgo operativo
                    </p>
                </div>

                <div
                    class="rounded-2xl border border-amber-200 bg-white/70 px-4 py-3 dark:border-amber-400/20 dark:bg-slate-950/40">
                    <p class="text-xs font-bold uppercase tracking-[0.12em] text-amber-700 dark:text-amber-300">
                        Control
                    </p>
                    <p class="mt-1 text-2xl font-black text-amber-800 dark:text-amber-200">
                        Sí
                    </p>
                    <p class="mt-1 text-xs text-amber-700/80 dark:text-amber-200/70">
                        Validación requerida
                    </p>
                </div>
            </div>
        </div>

        {{-- ============================================================
        ADVERTENCIAS
        ============================================================ --}}
        <section class="grid gap-3 md:grid-cols-3">
            <article
                class="rounded-2xl border border-rose-200 bg-white/70 px-4 py-3 dark:border-rose-400/20 dark:bg-slate-950/40">
                <div class="flex items-center gap-2">
                    <div
                        class="flex h-9 w-9 items-center justify-center rounded-xl bg-rose-100 text-rose-700 dark:bg-rose-400/10 dark:text-rose-300">
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"
                                d="M15.75 9V5.25A2.25 2.25 0 0 0 13.5 3h-7.5A2.25 2.25 0 0 0 3.75 5.25v13.5A2.25 2.25 0 0 0 6 21h7.5a2.25 2.25 0 0 0 2.25-2.25V15" />
                        </svg>
                    </div>

                    <p class="text-xs font-bold uppercase tracking-[0.12em] text-rose-700 dark:text-rose-300">
                        Acceso
                    </p>
                </div>

                <p class="mt-3 text-sm font-semibold leading-6 text-rose-900 dark:text-rose-100">
                    Se perderá el ingreso a la cuenta institucional.
                </p>
            </article>

            <article
                class="rounded-2xl border border-rose-200 bg-white/70 px-4 py-3 dark:border-rose-400/20 dark:bg-slate-950/40">
                <div class="flex items-center gap-2">
                    <div
                        class="flex h-9 w-9 items-center justify-center rounded-xl bg-rose-100 text-rose-700 dark:bg-rose-400/10 dark:text-rose-300">
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"
                                d="M19.5 14.25v-2.625a3.375 3.375 0 0 0-3.375-3.375h-1.5A1.125 1.125 0 0 1 13.5 7.125v-1.5A3.375 3.375 0 0 0 10.125 2.25H8.25m0 12.75h7.5m-7.5 3h4.5" />
                        </svg>
                    </div>

                    <p class="text-xs font-bold uppercase tracking-[0.12em] text-rose-700 dark:text-rose-300">
                        Datos
                    </p>
                </div>

                <p class="mt-3 text-sm font-semibold leading-6 text-rose-900 dark:text-rose-100">
                    La eliminación puede afectar recursos y datos asociados.
                </p>
            </article>

            <article
                class="rounded-2xl border border-rose-200 bg-white/70 px-4 py-3 dark:border-rose-400/20 dark:bg-slate-950/40">
                <div class="flex items-center gap-2">
                    <div
                        class="flex h-9 w-9 items-center justify-center rounded-xl bg-rose-100 text-rose-700 dark:bg-rose-400/10 dark:text-rose-300">
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"
                                d="M16.5 10.5V6.75a4.5 4.5 0 1 0-9 0v3.75m-.75 11.25h10.5A2.25 2.25 0 0 0 19.5 19.5v-7.5a2.25 2.25 0 0 0-2.25-2.25H6.75A2.25 2.25 0 0 0 4.5 12v7.5A2.25 2.25 0 0 0 6.75 21.75Z" />
                        </svg>
                    </div>

                    <p class="text-xs font-bold uppercase tracking-[0.12em] text-rose-700 dark:text-rose-300">
                        Seguridad
                    </p>
                </div>

                <p class="mt-3 text-sm font-semibold leading-6 text-rose-900 dark:text-rose-100">
                    Se solicitará contraseña antes de continuar.
                </p>
            </article>
        </section>

        {{-- ============================================================
        AVISO INSTITUCIONAL
        ============================================================ --}}
        <div
            class="rounded-2xl border border-amber-200 bg-amber-50 px-4 py-4 text-sm leading-6 text-amber-800 dark:border-amber-400/20 dark:bg-amber-400/10 dark:text-amber-200">
            Antes de continuar, verifica que esta acción realmente corresponda. Para salir del sistema, usa
            <span class="font-black">Cerrar sesión</span>. Para suspender una cuenta institucional, lo más recomendable
            es una desactivación controlada por administración.
        </div>

        {{-- ============================================================
        ACCIÓN
        ============================================================ --}}
        <div
            class="flex flex-col gap-3 border-t border-rose-200/70 pt-5 dark:border-rose-400/20 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <p class="text-sm font-black text-rose-950 dark:text-rose-100">
                    Solicitar eliminación de cuenta
                </p>
                <p class="mt-1 text-xs leading-5 text-rose-800/75 dark:text-rose-100/70">
                    La operación requerirá confirmación con tu contraseña actual.
                </p>
            </div>

            <button type="button" wire:click="confirmUserDeletion" wire:loading.attr="disabled"
                class="inline-flex items-center justify-center gap-2 rounded-2xl bg-gradient-to-r from-rose-600 to-red-700 px-5 py-3 text-sm font-bold text-white shadow-lg shadow-rose-500/20 transition hover:-translate-y-0.5 hover:shadow-xl disabled:cursor-not-allowed disabled:opacity-60">
                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"
                        d="m14.74 9-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166M18.16 19.673A2.25 2.25 0 0 1 15.916 21H8.084a2.25 2.25 0 0 1-2.244-1.827L4.772 5.79m14.456 0A48.108 48.108 0 0 0 12 5.25c-2.497 0-4.913.19-7.228.54m14.456 0-.214-1.281A2.25 2.25 0 0 0 16.793 2.625H7.207a2.25 2.25 0 0 0-2.221 1.884L4.772 5.79m0 0c-.34.059-.68.114-1.022.166" />
                </svg>
                Eliminar mi cuenta
            </button>
        </div>
    </div>

    {{-- ============================================================
    MODAL DE CONFIRMACIÓN
    ============================================================ --}}
    <x-dialog-modal wire:model.live="confirmingUserDeletion">
        <x-slot name="title">
            <div class="flex items-center gap-3">
                <div
                    class="flex h-10 w-10 items-center justify-center rounded-xl bg-rose-100 text-rose-700 dark:bg-rose-400/10 dark:text-rose-300">
                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M12 9v3.75m0 3.75h.008v.008H12v-.008ZM10.29 3.86 1.82 18a1.875 1.875 0 0 0 1.607 2.84h17.146A1.875 1.875 0 0 0 22.18 18L13.71 3.86a1.875 1.875 0 0 0-3.42 0Z" />
                    </svg>
                </div>

                <div>
                    <h2 class="text-base font-black text-[var(--ui-text)]">
                        Confirmar eliminación permanente
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
                    Estás a punto de eliminar tu cuenta. Esta operación puede borrar de forma permanente recursos
                    y datos asociados. Para continuar, confirma tu contraseña actual.
                </p>

                <div
                    class="rounded-2xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm leading-6 text-rose-800 dark:border-rose-400/20 dark:bg-rose-400/10 dark:text-rose-200">
                    Esta acción no debe utilizarse como cierre de sesión. Si solo quieres salir del sistema, usa la
                    opción “Cerrar sesión” del menú de usuario.
                </div>

                <div class="rounded-2xl border border-[var(--ui-border)] bg-[var(--ui-soft)] p-4"
                    x-data="{ showPassword: false }"
                    x-on:confirming-delete-user.window="setTimeout(() => $refs.password.focus(), 250)">
                    <label for="password" class="block text-sm font-bold text-[var(--ui-text)]">
                        Contraseña actual
                    </label>

                    <div class="relative mt-2">
                        <input id="password" x-bind:type="showPassword ? 'text' : 'password'"
                            class="ui-input block w-full pr-12" autocomplete="current-password"
                            placeholder="Ingresa tu contraseña" x-ref="password" wire:model="password"
                            wire:keydown.enter="deleteUser" />

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
            <button type="button" wire:click="$toggle('confirmingUserDeletion')" wire:loading.attr="disabled"
                class="inline-flex items-center justify-center rounded-2xl border border-[var(--ui-border)] bg-[var(--ui-card)] px-5 py-3 text-sm font-bold text-[var(--ui-text)] transition hover:bg-[var(--ui-soft)] disabled:cursor-not-allowed disabled:opacity-60">
                Cancelar
            </button>

            <button type="button" wire:click="deleteUser" wire:loading.attr="disabled"
                class="ms-3 inline-flex items-center justify-center rounded-2xl bg-gradient-to-r from-rose-600 to-red-700 px-5 py-3 text-sm font-bold text-white shadow-lg shadow-rose-500/20 transition hover:-translate-y-0.5 hover:shadow-xl disabled:cursor-not-allowed disabled:opacity-60">
                Confirmar eliminación
            </button>
        </x-slot>
    </x-dialog-modal>
</div>