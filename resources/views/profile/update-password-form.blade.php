<div x-data="{
        showCurrent: false,
        showNew: false,
        showConfirm: false,
        currentPassword: '',
        newPassword: '',
        confirmPassword: '',

        get hasMinLength() {
            return this.newPassword.length >= 8;
        },

        get hasLetter() {
            return /[A-Za-z]/.test(this.newPassword);
        },

        get hasNumber() {
            return /[0-9]/.test(this.newPassword);
        },

        get hasSpecial() {
            return /[^A-Za-z0-9]/.test(this.newPassword);
        },

        get passwordsMatch() {
            return this.newPassword !== '' && this.newPassword === this.confirmPassword;
        },

        get currentPasswordFilled() {
            return this.currentPassword.trim().length > 0;
        },

        get strengthScore() {
            let score = 0;

            if (this.hasMinLength) score++;
            if (this.hasLetter) score++;
            if (this.hasNumber) score++;
            if (this.hasSpecial) score++;

            return score;
        },

        get strengthWidth() {
            switch (this.strengthScore) {
                case 1:
                    return '25%';
                case 2:
                    return '50%';
                case 3:
                    return '75%';
                case 4:
                    return '100%';
                default:
                    return '0%';
            }
        },

        get strengthColor() {
            switch (this.strengthScore) {
                case 1:
                    return 'bg-rose-500';
                case 2:
                    return 'bg-amber-500';
                case 3:
                    return 'bg-sky-500';
                case 4:
                    return 'bg-emerald-500';
                default:
                    return 'bg-[var(--ui-border)]';
            }
        },

        get strengthText() {
            switch (this.strengthScore) {
                case 1:
                    return 'Muy débil';
                case 2:
                    return 'Débil';
                case 3:
                    return 'Media';
                case 4:
                    return 'Fuerte';
                default:
                    return 'Sin evaluar';
            }
        },

        get strengthTextColor() {
            switch (this.strengthScore) {
                case 1:
                    return 'text-rose-600 dark:text-rose-300';
                case 2:
                    return 'text-amber-600 dark:text-amber-300';
                case 3:
                    return 'text-sky-600 dark:text-sky-300';
                case 4:
                    return 'text-emerald-600 dark:text-emerald-300';
                default:
                    return 'text-[var(--ui-muted)]';
            }
        },

        get canSubmit() {
            return this.currentPasswordFilled
                && this.hasMinLength
                && this.hasLetter
                && this.hasNumber
                && this.hasSpecial
                && this.passwordsMatch;
        },

        resetLocalFields() {
            this.currentPassword = '';
            this.newPassword = '';
            this.confirmPassword = '';
            this.showCurrent = false;
            this.showNew = false;
            this.showConfirm = false;
        }
    }" x-on:saved.window="
        if (window.Swal) {
            Swal.fire({
                icon: 'success',
                title: 'Contraseña actualizada',
                text: 'Tu contraseña institucional se actualizó correctamente.',
                confirmButtonText: 'Entendido',
                confirmButtonColor: '#059669'
            });
        }

        resetLocalFields();
        window.dispatchEvent(new CustomEvent('password-actualizado'));
    ">
    <form wire:submit.prevent="updatePassword"
        class="relative overflow-hidden rounded-[1.8rem] border border-[var(--ui-border)] bg-[var(--ui-card)] p-5 shadow-sm sm:p-6">

        {{-- Fondos suaves --}}
        <div class="pointer-events-none absolute -right-24 -top-24 h-64 w-64 rounded-full bg-emerald-400/10 blur-3xl">
        </div>
        <div class="pointer-events-none absolute -bottom-24 left-10 h-64 w-64 rounded-full bg-sky-400/10 blur-3xl">
        </div>
        <div class="pointer-events-none absolute bottom-0 right-1/3 h-52 w-52 rounded-full bg-violet-400/10 blur-3xl">
        </div>

        <div class="relative space-y-6">

            {{-- ============================================================
            CABECERA
            ============================================================ --}}
            <div class="flex flex-col gap-5 xl:flex-row xl:items-start xl:justify-between">
                <div class="max-w-3xl">
                    <div class="flex flex-wrap items-center gap-2">
                        <span
                            class="inline-flex items-center gap-2 rounded-full border border-emerald-200/70 bg-emerald-50 px-3 py-1 text-xs font-black uppercase tracking-[0.14em] text-emerald-700 dark:border-emerald-400/20 dark:bg-emerald-400/10 dark:text-emerald-300">
                            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"
                                    d="M12 3.75 4.5 6.75v5.625c0 4.038 3.06 7.82 7.5 8.875 4.44-1.055 7.5-4.837 7.5-8.875V6.75L12 3.75Z" />
                            </svg>
                            Credenciales de acceso
                        </span>

                        <span
                            class="inline-flex rounded-full border border-sky-200 bg-sky-50 px-3 py-1 text-xs font-bold text-sky-700 dark:border-sky-400/20 dark:bg-sky-400/10 dark:text-sky-300">
                            Validación requerida
                        </span>
                    </div>

                    <h3 class="mt-4 text-xl font-black tracking-tight text-[var(--ui-text)]">
                        Cambio seguro de contraseña
                    </h3>

                    <p class="mt-2 text-sm leading-7 text-[var(--ui-muted)]">
                        Actualiza tu contraseña institucional usando una clave robusta. El sistema verificará requisitos
                        mínimos antes de permitir guardar los cambios.
                    </p>
                </div>

                <div class="grid grid-cols-2 gap-3 xl:min-w-[320px]">
                    <div class="rounded-2xl border border-[var(--ui-border)] bg-[var(--ui-soft)] px-4 py-3">
                        <p class="text-xs font-bold uppercase tracking-[0.12em] text-[var(--ui-muted)]">
                            Longitud
                        </p>

                        <p class="mt-1 text-2xl font-black text-[var(--ui-text)]">
                            8+
                        </p>

                        <p class="mt-1 text-xs text-[var(--ui-muted)]">
                            Caracteres mínimos
                        </p>
                    </div>

                    <div class="rounded-2xl border border-[var(--ui-border)] bg-[var(--ui-soft)] px-4 py-3">
                        <p class="text-xs font-bold uppercase tracking-[0.12em] text-[var(--ui-muted)]">
                            Nivel
                        </p>

                        <p class="mt-1 text-2xl font-black" :class="strengthTextColor" x-text="strengthText"></p>

                        <p class="mt-1 text-xs text-[var(--ui-muted)]">
                            Evaluación local
                        </p>
                    </div>
                </div>
            </div>

            {{-- ============================================================
            AVISO
            ============================================================ --}}
            <div
                class="rounded-2xl border border-sky-200 bg-sky-50 px-4 py-4 text-sm leading-6 text-sky-800 dark:border-sky-400/20 dark:bg-sky-400/10 dark:text-sky-200">
                Para proteger tu cuenta, usa una contraseña distinta a la de otros servicios. Evita nombres, fechas
                personales o combinaciones fáciles de adivinar.
            </div>

            {{-- ============================================================
            CAMPOS
            ============================================================ --}}
            <section class="grid gap-5 lg:grid-cols-2">

                {{-- Contraseña actual --}}
                <div class="lg:col-span-2">
                    <label for="current_password" class="block text-sm font-bold text-[var(--ui-text)]">
                        Contraseña actual
                    </label>

                    <div class="relative mt-2">
                        <input id="current_password" x-bind:type="showCurrent ? 'text' : 'password'"
                            class="ui-input block w-full pr-12" wire:model.live="state.current_password"
                            x-model="currentPassword" autocomplete="current-password"
                            placeholder="Ingresa tu contraseña actual" />

                        <button type="button" @click="showCurrent = !showCurrent"
                            class="absolute inset-y-0 right-0 flex items-center pr-4 text-[var(--ui-muted)] transition hover:text-[var(--ui-text)]"
                            title="Mostrar u ocultar contraseña">
                            <svg x-show="!showCurrent" class="h-5 w-5" fill="none" viewBox="0 0 24 24"
                                stroke="currentColor" stroke-width="1.8">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7Z" />
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M12 15.75A3.75 3.75 0 1 0 12 8.25a3.75 3.75 0 0 0 0 7.5Z" />
                            </svg>

                            <svg x-show="showCurrent" x-cloak class="h-5 w-5" fill="none" viewBox="0 0 24 24"
                                stroke="currentColor" stroke-width="1.8">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M3 3l18 18" />
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M10.584 10.587A2.25 2.25 0 0 0 13.41 13.41" />
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M9.88 5.09A9.953 9.953 0 0 1 12 4.875c5.053 0 9.27 3.11 10.5 7.125a11.03 11.03 0 0 1-4.04 5.411M6.228 6.228A11.03 11.03 0 0 0 1.5 12c.69 2.25 2.14 4.175 4.04 5.411A9.953 9.953 0 0 0 12 19.125c.73 0 1.442-.078 2.125-.227" />
                            </svg>
                        </button>
                    </div>

                    <x-input-error for="current_password" class="mt-2" />
                </div>

                {{-- Nueva contraseña --}}
                <div>
                    <label for="password" class="block text-sm font-bold text-[var(--ui-text)]">
                        Nueva contraseña
                    </label>

                    <div class="relative mt-2">
                        <input id="password" x-bind:type="showNew ? 'text' : 'password'"
                            class="ui-input block w-full pr-12" wire:model.live="state.password" x-model="newPassword"
                            autocomplete="new-password" placeholder="Crea una contraseña segura" />

                        <button type="button" @click="showNew = !showNew"
                            class="absolute inset-y-0 right-0 flex items-center pr-4 text-[var(--ui-muted)] transition hover:text-[var(--ui-text)]"
                            title="Mostrar u ocultar contraseña">
                            <svg x-show="!showNew" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                                stroke-width="1.8">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7Z" />
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M12 15.75A3.75 3.75 0 1 0 12 8.25a3.75 3.75 0 0 0 0 7.5Z" />
                            </svg>

                            <svg x-show="showNew" x-cloak class="h-5 w-5" fill="none" viewBox="0 0 24 24"
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

                {{-- Confirmación --}}
                <div>
                    <label for="password_confirmation" class="block text-sm font-bold text-[var(--ui-text)]">
                        Confirmar nueva contraseña
                    </label>

                    <div class="relative mt-2">
                        <input id="password_confirmation" x-bind:type="showConfirm ? 'text' : 'password'"
                            class="ui-input block w-full pr-12" wire:model.live="state.password_confirmation"
                            x-model="confirmPassword" autocomplete="new-password"
                            placeholder="Repite la nueva contraseña" />

                        <button type="button" @click="showConfirm = !showConfirm"
                            class="absolute inset-y-0 right-0 flex items-center pr-4 text-[var(--ui-muted)] transition hover:text-[var(--ui-text)]"
                            title="Mostrar u ocultar contraseña">
                            <svg x-show="!showConfirm" class="h-5 w-5" fill="none" viewBox="0 0 24 24"
                                stroke="currentColor" stroke-width="1.8">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7Z" />
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M12 15.75A3.75 3.75 0 1 0 12 8.25a3.75 3.75 0 0 0 0 7.5Z" />
                            </svg>

                            <svg x-show="showConfirm" x-cloak class="h-5 w-5" fill="none" viewBox="0 0 24 24"
                                stroke="currentColor" stroke-width="1.8">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M3 3l18 18" />
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M10.584 10.587A2.25 2.25 0 0 0 13.41 13.41" />
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M9.88 5.09A9.953 9.953 0 0 1 12 4.875c5.053 0 9.27 3.11 10.5 7.125a11.03 11.03 0 0 1-4.04 5.411M6.228 6.228A11.03 11.03 0 0 0 1.5 12c.69 2.25 2.14 4.175 4.04 5.411A9.953 9.953 0 0 0 12 19.125c.73 0 1.442-.078 2.125-.227" />
                            </svg>
                        </button>
                    </div>

                    <x-input-error for="password_confirmation" class="mt-2" />
                </div>
            </section>

            {{-- ============================================================
            FORTALEZA
            ============================================================ --}}
            <section class="rounded-2xl border border-[var(--ui-border)] bg-[var(--ui-soft)] px-4 py-4">
                <div class="flex items-center justify-between gap-3">
                    <div>
                        <p class="text-sm font-black text-[var(--ui-text)]">
                            Nivel de seguridad
                        </p>
                        <p class="mt-1 text-xs text-[var(--ui-muted)]">
                            La barra se actualiza según los criterios cumplidos.
                        </p>
                    </div>

                    <span class="text-sm font-black" :class="strengthTextColor" x-text="strengthText"></span>
                </div>

                <div class="mt-3 h-2.5 w-full overflow-hidden rounded-full bg-[var(--ui-border)]">
                    <div class="h-2.5 rounded-full transition-all duration-300" :class="strengthColor"
                        :style="`width: ${strengthWidth}`">
                    </div>
                </div>
            </section>

            {{-- ============================================================
            REQUISITOS
            ============================================================ --}}
            <section class="rounded-2xl border border-[var(--ui-border)] bg-[var(--ui-soft)] px-4 py-4">
                <p class="text-sm font-black text-[var(--ui-text)]">
                    Requisitos de la nueva contraseña
                </p>

                <div class="mt-4 grid gap-3 text-sm sm:grid-cols-2">
                    {{-- Mínimo --}}
                    <div class="flex items-center gap-3">
                        <span class="flex h-5 w-5 items-center justify-center">
                            <svg x-show="hasMinLength" class="h-5 w-5 text-emerald-600 dark:text-emerald-300"
                                viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd"
                                    d="M16.704 5.29a1 1 0 0 1 .006 1.414l-7.2 7.26a1 1 0 0 1-1.42.005L3.3 9.173a1 1 0 1 1 1.4-1.428l3.08 3.022 6.5-6.55a1 1 0 0 1 1.424-.006Z"
                                    clip-rule="evenodd" />
                            </svg>

                            <svg x-show="!hasMinLength" class="h-5 w-5 text-rose-500 dark:text-rose-300"
                                viewBox="0 0 20 20" fill="currentColor">
                                <path
                                    d="M6.28 5.22a.75.75 0 0 0-1.06 1.06L8.94 10l-3.72 3.72a.75.75 0 1 0 1.06 1.06L10 11.06l3.72 3.72a.75.75 0 0 0 1.06-1.06L11.06 10l3.72-3.72a.75.75 0 1 0-1.06-1.06L10 8.94 6.28 5.22Z" />
                            </svg>
                        </span>

                        <span
                            :class="hasMinLength ? 'text-emerald-700 dark:text-emerald-300' : 'text-[var(--ui-muted)]'">
                            Mínimo 8 caracteres
                        </span>
                    </div>

                    {{-- Letra --}}
                    <div class="flex items-center gap-3">
                        <span class="flex h-5 w-5 items-center justify-center">
                            <svg x-show="hasLetter" class="h-5 w-5 text-emerald-600 dark:text-emerald-300"
                                viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd"
                                    d="M16.704 5.29a1 1 0 0 1 .006 1.414l-7.2 7.26a1 1 0 0 1-1.42.005L3.3 9.173a1 1 0 1 1 1.4-1.428l3.08 3.022 6.5-6.55a1 1 0 0 1 1.424-.006Z"
                                    clip-rule="evenodd" />
                            </svg>

                            <svg x-show="!hasLetter" class="h-5 w-5 text-rose-500 dark:text-rose-300"
                                viewBox="0 0 20 20" fill="currentColor">
                                <path
                                    d="M6.28 5.22a.75.75 0 0 0-1.06 1.06L8.94 10l-3.72 3.72a.75.75 0 1 0 1.06 1.06L10 11.06l3.72 3.72a.75.75 0 0 0 1.06-1.06L11.06 10l3.72-3.72a.75.75 0 1 0-1.06-1.06L10 8.94 6.28 5.22Z" />
                            </svg>
                        </span>

                        <span :class="hasLetter ? 'text-emerald-700 dark:text-emerald-300' : 'text-[var(--ui-muted)]'">
                            Al menos una letra
                        </span>
                    </div>

                    {{-- Número --}}
                    <div class="flex items-center gap-3">
                        <span class="flex h-5 w-5 items-center justify-center">
                            <svg x-show="hasNumber" class="h-5 w-5 text-emerald-600 dark:text-emerald-300"
                                viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd"
                                    d="M16.704 5.29a1 1 0 0 1 .006 1.414l-7.2 7.26a1 1 0 0 1-1.42.005L3.3 9.173a1 1 0 1 1 1.4-1.428l3.08 3.022 6.5-6.55a1 1 0 0 1 1.424-.006Z"
                                    clip-rule="evenodd" />
                            </svg>

                            <svg x-show="!hasNumber" class="h-5 w-5 text-rose-500 dark:text-rose-300"
                                viewBox="0 0 20 20" fill="currentColor">
                                <path
                                    d="M6.28 5.22a.75.75 0 0 0-1.06 1.06L8.94 10l-3.72 3.72a.75.75 0 1 0 1.06 1.06L10 11.06l3.72 3.72a.75.75 0 0 0 1.06-1.06L11.06 10l3.72-3.72a.75.75 0 1 0-1.06-1.06L10 8.94 6.28 5.22Z" />
                            </svg>
                        </span>

                        <span :class="hasNumber ? 'text-emerald-700 dark:text-emerald-300' : 'text-[var(--ui-muted)]'">
                            Al menos un número
                        </span>
                    </div>

                    {{-- Especial --}}
                    <div class="flex items-center gap-3">
                        <span class="flex h-5 w-5 items-center justify-center">
                            <svg x-show="hasSpecial" class="h-5 w-5 text-emerald-600 dark:text-emerald-300"
                                viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd"
                                    d="M16.704 5.29a1 1 0 0 1 .006 1.414l-7.2 7.26a1 1 0 0 1-1.42.005L3.3 9.173a1 1 0 1 1 1.4-1.428l3.08 3.022 6.5-6.55a1 1 0 0 1 1.424-.006Z"
                                    clip-rule="evenodd" />
                            </svg>

                            <svg x-show="!hasSpecial" class="h-5 w-5 text-rose-500 dark:text-rose-300"
                                viewBox="0 0 20 20" fill="currentColor">
                                <path
                                    d="M6.28 5.22a.75.75 0 0 0-1.06 1.06L8.94 10l-3.72 3.72a.75.75 0 1 0 1.06 1.06L10 11.06l3.72 3.72a.75.75 0 0 0 1.06-1.06L11.06 10l3.72-3.72a.75.75 0 1 0-1.06-1.06L10 8.94 6.28 5.22Z" />
                            </svg>
                        </span>

                        <span :class="hasSpecial ? 'text-emerald-700 dark:text-emerald-300' : 'text-[var(--ui-muted)]'">
                            Al menos un carácter especial
                        </span>
                    </div>

                    {{-- Coincidencia --}}
                    <div class="flex items-center gap-3 sm:col-span-2">
                        <span class="flex h-5 w-5 items-center justify-center">
                            <svg x-show="passwordsMatch" class="h-5 w-5 text-emerald-600 dark:text-emerald-300"
                                viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd"
                                    d="M16.704 5.29a1 1 0 0 1 .006 1.414l-7.2 7.26a1 1 0 0 1-1.42.005L3.3 9.173a1 1 0 1 1 1.4-1.428l3.08 3.022 6.5-6.55a1 1 0 0 1 1.424-.006Z"
                                    clip-rule="evenodd" />
                            </svg>

                            <svg x-show="!passwordsMatch" class="h-5 w-5 text-rose-500 dark:text-rose-300"
                                viewBox="0 0 20 20" fill="currentColor">
                                <path
                                    d="M6.28 5.22a.75.75 0 0 0-1.06 1.06L8.94 10l-3.72 3.72a.75.75 0 1 0 1.06 1.06L10 11.06l3.72 3.72a.75.75 0 0 0 1.06-1.06L11.06 10l3.72-3.72a.75.75 0 1 0-1.06-1.06L10 8.94 6.28 5.22Z" />
                            </svg>
                        </span>

                        <span
                            :class="passwordsMatch ? 'text-emerald-700 dark:text-emerald-300' : 'text-[var(--ui-muted)]'">
                            La confirmación coincide con la nueva contraseña
                        </span>
                    </div>
                </div>
            </section>

            {{-- ============================================================
            ACCIONES
            ============================================================ --}}
            <div
                class="flex flex-col gap-3 border-t border-[var(--ui-border)] pt-5 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <p class="text-sm font-black text-[var(--ui-text)]">
                        Guardar nueva contraseña
                    </p>
                    <p class="mt-1 text-xs leading-5 text-[var(--ui-muted)]">
                        El botón se habilitará cuando todos los requisitos estén cumplidos.
                    </p>
                </div>

                <div class="flex flex-wrap items-center gap-3">
                    <x-action-message class="text-sm font-bold text-emerald-700 dark:text-emerald-300" on="saved">
                        Contraseña actualizada correctamente.
                    </x-action-message>

                    <button type="submit" x-bind:disabled="!canSubmit" wire:loading.attr="disabled"
                        x-bind:class="canSubmit
                            ? 'bg-gradient-to-r from-emerald-600 to-sky-600 text-white shadow-lg shadow-emerald-500/20 hover:-translate-y-0.5 hover:shadow-xl'
                            : 'border border-[var(--ui-border)] bg-[var(--ui-soft)] text-[var(--ui-muted)] cursor-not-allowed'"
                        class="inline-flex items-center justify-center rounded-2xl px-5 py-3 text-sm font-bold transition disabled:cursor-not-allowed disabled:opacity-70">
                        Guardar cambios
                    </button>
                </div>
            </div>
        </div>
    </form>
</div>