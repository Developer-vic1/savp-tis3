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
                case 1: return '25%';
                case 2: return '50%';
                case 3: return '75%';
                case 4: return '100%';
                default: return '0%';
            }
        },

        get strengthColor() {
            switch (this.strengthScore) {
                case 1: return 'bg-rose-500';
                case 2: return 'bg-amber-500';
                case 3: return 'bg-sky-500';
                case 4: return 'bg-emerald-500';
                default: return 'bg-slate-300';
            }
        },

        get strengthText() {
            switch (this.strengthScore) {
                case 1: return 'Muy débil';
                case 2: return 'Débil';
                case 3: return 'Media';
                case 4: return 'Fuerte';
                default: return 'Sin evaluar';
            }
        },

        get strengthTextColor() {
            switch (this.strengthScore) {
                case 1: return 'text-rose-600';
                case 2: return 'text-amber-600';
                case 3: return 'text-sky-600';
                case 4: return 'text-emerald-600';
                default: return 'text-slate-500';
            }
        },

        get canSubmit() {
            return this.currentPasswordFilled
                && this.hasMinLength
                && this.hasLetter
                && this.hasNumber
                && this.hasSpecial
                && this.passwordsMatch;
        }
    }" x-on:saved.window="
        Swal.fire({
            icon: 'success',
            title: 'Contraseña actualizada',
            text: 'Tu contraseña se actualizó correctamente.',
            confirmButtonText: 'Entendido',
            confirmButtonColor: '#059669'
        });
        window.dispatchEvent(new CustomEvent('password-actualizado'));
    ">
    <x-form-section submit="updatePassword">
        <x-slot name="title">
            Seguridad de la cuenta
        </x-slot>

        <x-slot name="description">
            Actualiza tu contraseña para mantener protegida tu cuenta dentro del sistema institucional.
        </x-slot>

        <x-slot name="form">
            <div class="col-span-6">
                <div class="rounded-[1.8rem] border border-slate-200 bg-white p-5 shadow-sm sm:p-6">
                    <div class="grid gap-5 sm:grid-cols-2">
                        {{-- CONTRASEÑA ACTUAL --}}
                        <div class="sm:col-span-2">
                            <x-label for="current_password" value="Contraseña actual" />

                            <div class="relative mt-2">
                                <x-input id="current_password" x-bind:type="showCurrent ? 'text' : 'password'"
                                    class="block w-full rounded-2xl border-slate-300 pr-12 focus:border-emerald-500 focus:ring-emerald-500"
                                    wire:model.live="state.current_password" x-model="currentPassword"
                                    autocomplete="current-password" />

                                <button type="button" @click="showCurrent = !showCurrent"
                                    class="absolute inset-y-0 right-0 flex items-center pr-4 text-slate-400 transition hover:text-slate-700"
                                    title="Mostrar u ocultar contraseña">
                                    {{-- Ojo abierto --}}
                                    <svg x-show="!showCurrent" xmlns="http://www.w3.org/2000/svg" class="h-5 w-5"
                                        fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                                        <path stroke-linecap="round" stroke-linejoin="round"
                                            d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7Z" />
                                        <path stroke-linecap="round" stroke-linejoin="round"
                                            d="M12 15.75A3.75 3.75 0 1 0 12 8.25a3.75 3.75 0 0 0 0 7.5Z" />
                                    </svg>

                                    {{-- Ojo oculto --}}
                                    <svg x-show="showCurrent" x-cloak xmlns="http://www.w3.org/2000/svg" class="h-5 w-5"
                                        fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
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

                        {{-- NUEVA CONTRASEÑA --}}
                        <div>
                            <x-label for="password" value="Nueva contraseña" />

                            <div class="relative mt-2">
                                <x-input id="password" x-bind:type="showNew ? 'text' : 'password'"
                                    class="block w-full rounded-2xl border-slate-300 pr-12 focus:border-emerald-500 focus:ring-emerald-500"
                                    wire:model.live="state.password" x-model="newPassword"
                                    autocomplete="new-password" />

                                <button type="button" @click="showNew = !showNew"
                                    class="absolute inset-y-0 right-0 flex items-center pr-4 text-slate-400 transition hover:text-slate-700"
                                    title="Mostrar u ocultar contraseña">
                                    <svg x-show="!showNew" xmlns="http://www.w3.org/2000/svg" class="h-5 w-5"
                                        fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                                        <path stroke-linecap="round" stroke-linejoin="round"
                                            d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7Z" />
                                        <path stroke-linecap="round" stroke-linejoin="round"
                                            d="M12 15.75A3.75 3.75 0 1 0 12 8.25a3.75 3.75 0 0 0 0 7.5Z" />
                                    </svg>

                                    <svg x-show="showNew" x-cloak xmlns="http://www.w3.org/2000/svg" class="h-5 w-5"
                                        fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
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

                        {{-- CONFIRMAR CONTRASEÑA --}}
                        <div>
                            <x-label for="password_confirmation" value="Confirmar nueva contraseña" />

                            <div class="relative mt-2">
                                <x-input id="password_confirmation" x-bind:type="showConfirm ? 'text' : 'password'"
                                    class="block w-full rounded-2xl border-slate-300 pr-12 focus:border-emerald-500 focus:ring-emerald-500"
                                    wire:model.live="state.password_confirmation" x-model="confirmPassword"
                                    autocomplete="new-password" />

                                <button type="button" @click="showConfirm = !showConfirm"
                                    class="absolute inset-y-0 right-0 flex items-center pr-4 text-slate-400 transition hover:text-slate-700"
                                    title="Mostrar u ocultar contraseña">
                                    <svg x-show="!showConfirm" xmlns="http://www.w3.org/2000/svg" class="h-5 w-5"
                                        fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                                        <path stroke-linecap="round" stroke-linejoin="round"
                                            d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7Z" />
                                        <path stroke-linecap="round" stroke-linejoin="round"
                                            d="M12 15.75A3.75 3.75 0 1 0 12 8.25a3.75 3.75 0 0 0 0 7.5Z" />
                                    </svg>

                                    <svg x-show="showConfirm" x-cloak xmlns="http://www.w3.org/2000/svg" class="h-5 w-5"
                                        fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
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
                    </div>

                    {{-- FORTALEZA --}}
                    <div class="mt-6 rounded-2xl border border-slate-200 bg-slate-50 px-4 py-4">
                        <div class="flex items-center justify-between gap-3">
                            <p class="text-sm font-semibold text-slate-800">
                                Nivel de seguridad
                            </p>
                            <span class="text-sm font-semibold" :class="strengthTextColor" x-text="strengthText"></span>
                        </div>

                        <div class="mt-3 h-2.5 w-full overflow-hidden rounded-full bg-slate-200">
                            <div class="h-2.5 rounded-full transition-all duration-300" :class="strengthColor"
                                :style="`width: ${strengthWidth}`">
                            </div>
                        </div>
                    </div>

                    {{-- REQUISITOS --}}
                    <div class="mt-5 rounded-2xl border border-slate-200 bg-slate-50 px-4 py-4">
                        <p class="text-sm font-semibold text-slate-800">
                            Requisitos de la nueva contraseña
                        </p>

                        <div class="mt-4 grid gap-3 sm:grid-cols-2 text-sm">
                            <div class="flex items-center gap-3">
                                <span class="flex h-5 w-5 items-center justify-center">
                                    <svg x-show="hasMinLength" xmlns="http://www.w3.org/2000/svg"
                                        class="h-5 w-5 text-emerald-600" viewBox="0 0 20 20" fill="currentColor">
                                        <path fill-rule="evenodd"
                                            d="M16.704 5.29a1 1 0 0 1 .006 1.414l-7.2 7.26a1 1 0 0 1-1.42.005L3.3 9.173a1 1 0 1 1 1.4-1.428l3.08 3.022 6.5-6.55a1 1 0 0 1 1.424-.006Z"
                                            clip-rule="evenodd" />
                                    </svg>
                                    <svg x-show="!hasMinLength" xmlns="http://www.w3.org/2000/svg"
                                        class="h-5 w-5 text-rose-500" viewBox="0 0 20 20" fill="currentColor">
                                        <path
                                            d="M6.28 5.22a.75.75 0 0 0-1.06 1.06L8.94 10l-3.72 3.72a.75.75 0 1 0 1.06 1.06L10 11.06l3.72 3.72a.75.75 0 0 0 1.06-1.06L11.06 10l3.72-3.72a.75.75 0 1 0-1.06-1.06L10 8.94 6.28 5.22Z" />
                                    </svg>
                                </span>
                                <span :class="hasMinLength ? 'text-emerald-700' : 'text-slate-600'">
                                    Mínimo 8 caracteres
                                </span>
                            </div>

                            <div class="flex items-center gap-3">
                                <span class="flex h-5 w-5 items-center justify-center">
                                    <svg x-show="hasLetter" xmlns="http://www.w3.org/2000/svg"
                                        class="h-5 w-5 text-emerald-600" viewBox="0 0 20 20" fill="currentColor">
                                        <path fill-rule="evenodd"
                                            d="M16.704 5.29a1 1 0 0 1 .006 1.414l-7.2 7.26a1 1 0 0 1-1.42.005L3.3 9.173a1 1 0 1 1 1.4-1.428l3.08 3.022 6.5-6.55a1 1 0 0 1 1.424-.006Z"
                                            clip-rule="evenodd" />
                                    </svg>
                                    <svg x-show="!hasLetter" xmlns="http://www.w3.org/2000/svg"
                                        class="h-5 w-5 text-rose-500" viewBox="0 0 20 20" fill="currentColor">
                                        <path
                                            d="M6.28 5.22a.75.75 0 0 0-1.06 1.06L8.94 10l-3.72 3.72a.75.75 0 1 0 1.06 1.06L10 11.06l3.72 3.72a.75.75 0 0 0 1.06-1.06L11.06 10l3.72-3.72a.75.75 0 1 0-1.06-1.06L10 8.94 6.28 5.22Z" />
                                    </svg>
                                </span>
                                <span :class="hasLetter ? 'text-emerald-700' : 'text-slate-600'">
                                    Al menos una letra
                                </span>
                            </div>

                            <div class="flex items-center gap-3">
                                <span class="flex h-5 w-5 items-center justify-center">
                                    <svg x-show="hasNumber" xmlns="http://www.w3.org/2000/svg"
                                        class="h-5 w-5 text-emerald-600" viewBox="0 0 20 20" fill="currentColor">
                                        <path fill-rule="evenodd"
                                            d="M16.704 5.29a1 1 0 0 1 .006 1.414l-7.2 7.26a1 1 0 0 1-1.42.005L3.3 9.173a1 1 0 1 1 1.4-1.428l3.08 3.022 6.5-6.55a1 1 0 0 1 1.424-.006Z"
                                            clip-rule="evenodd" />
                                    </svg>
                                    <svg x-show="!hasNumber" xmlns="http://www.w3.org/2000/svg"
                                        class="h-5 w-5 text-rose-500" viewBox="0 0 20 20" fill="currentColor">
                                        <path
                                            d="M6.28 5.22a.75.75 0 0 0-1.06 1.06L8.94 10l-3.72 3.72a.75.75 0 1 0 1.06 1.06L10 11.06l3.72 3.72a.75.75 0 0 0 1.06-1.06L11.06 10l3.72-3.72a.75.75 0 1 0-1.06-1.06L10 8.94 6.28 5.22Z" />
                                    </svg>
                                </span>
                                <span :class="hasNumber ? 'text-emerald-700' : 'text-slate-600'">
                                    Al menos un número
                                </span>
                            </div>

                            <div class="flex items-center gap-3">
                                <span class="flex h-5 w-5 items-center justify-center">
                                    <svg x-show="hasSpecial" xmlns="http://www.w3.org/2000/svg"
                                        class="h-5 w-5 text-emerald-600" viewBox="0 0 20 20" fill="currentColor">
                                        <path fill-rule="evenodd"
                                            d="M16.704 5.29a1 1 0 0 1 .006 1.414l-7.2 7.26a1 1 0 0 1-1.42.005L3.3 9.173a1 1 0 1 1 1.4-1.428l3.08 3.022 6.5-6.55a1 1 0 0 1 1.424-.006Z"
                                            clip-rule="evenodd" />
                                    </svg>
                                    <svg x-show="!hasSpecial" xmlns="http://www.w3.org/2000/svg"
                                        class="h-5 w-5 text-rose-500" viewBox="0 0 20 20" fill="currentColor">
                                        <path
                                            d="M6.28 5.22a.75.75 0 0 0-1.06 1.06L8.94 10l-3.72 3.72a.75.75 0 1 0 1.06 1.06L10 11.06l3.72 3.72a.75.75 0 0 0 1.06-1.06L11.06 10l3.72-3.72a.75.75 0 1 0-1.06-1.06L10 8.94 6.28 5.22Z" />
                                    </svg>
                                </span>
                                <span :class="hasSpecial ? 'text-emerald-700' : 'text-slate-600'">
                                    Al menos un carácter especial
                                </span>
                            </div>

                            <div class="flex items-center gap-3 sm:col-span-2">
                                <span class="flex h-5 w-5 items-center justify-center">
                                    <svg x-show="passwordsMatch" xmlns="http://www.w3.org/2000/svg"
                                        class="h-5 w-5 text-emerald-600" viewBox="0 0 20 20" fill="currentColor">
                                        <path fill-rule="evenodd"
                                            d="M16.704 5.29a1 1 0 0 1 .006 1.414l-7.2 7.26a1 1 0 0 1-1.42.005L3.3 9.173a1 1 0 1 1 1.4-1.428l3.08 3.022 6.5-6.55a1 1 0 0 1 1.424-.006Z"
                                            clip-rule="evenodd" />
                                    </svg>
                                    <svg x-show="!passwordsMatch" xmlns="http://www.w3.org/2000/svg"
                                        class="h-5 w-5 text-rose-500" viewBox="0 0 20 20" fill="currentColor">
                                        <path
                                            d="M6.28 5.22a.75.75 0 0 0-1.06 1.06L8.94 10l-3.72 3.72a.75.75 0 1 0 1.06 1.06L10 11.06l3.72 3.72a.75.75 0 0 0 1.06-1.06L11.06 10l3.72-3.72a.75.75 0 1 0-1.06-1.06L10 8.94 6.28 5.22Z" />
                                    </svg>
                                </span>
                                <span :class="passwordsMatch ? 'text-emerald-700' : 'text-slate-600'">
                                    La confirmación coincide con la nueva contraseña
                                </span>
                            </div>
                        </div>
                    </div>

                    {{-- AYUDA --}}
                    <div class="mt-5 rounded-2xl border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-800">
                        Usa una contraseña segura, difícil de adivinar y diferente a la que utilizas en otros servicios.
                    </div>
                </div>
            </div>
        </x-slot>

        <x-slot name="actions">
            <x-action-message class="me-3 text-emerald-700" on="saved">
                Contraseña actualizada correctamente.
            </x-action-message>

            <button type="submit" x-bind:disabled="!canSubmit" x-bind:class="canSubmit
                    ? 'bg-gradient-to-r from-emerald-600 to-sky-600 text-white shadow-lg shadow-emerald-500/20 hover:opacity-95'
                    : 'bg-slate-200 text-slate-400 cursor-not-allowed'"
                class="rounded-2xl px-5 py-3 text-sm font-semibold transition">
                Guardar cambios
            </button>
        </x-slot>
    </x-form-section>
</div>