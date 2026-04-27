<x-guest-layout>
    <x-authentication-card>
        <x-slot name="logo">
            <div class="text-center">
                <div
                    class="relative mx-auto flex h-24 w-24 items-center justify-center rounded-3xl bg-white shadow-[0_20px_50px_rgba(16,185,129,0.18)] ring-4 ring-white/70 animate-[floatLogo_5s_ease-in-out_infinite]">
                    <img src="{{ asset('image/LOGO FT3 A.jpg') }}" alt="Logo Franz Tamayo N°3"
                        class="h-16 w-16 object-contain rounded-2xl">

                    <span
                        class="absolute -right-1 -top-1 h-4 w-4 rounded-full bg-sky-400 shadow-lg shadow-sky-400/60 animate-pulse"></span>
                    <span
                        class="absolute -bottom-1 -left-1 h-3 w-3 rounded-full bg-emerald-300 shadow-lg shadow-emerald-300/60 animate-pulse"></span>
                </div>

                <div class="mt-4">
                    <p class="font-display text-sm font-bold text-slate-900">
                        Franz Tamayo N°3
                    </p>
                    <p class="text-xs font-semibold uppercase tracking-[0.18em] text-emerald-600">
                        Sistema SAVP – TIS 3
                    </p>
                </div>
            </div>
        </x-slot>

        <style>
            .reset-stage {
                position: relative;
                overflow: hidden;
                border-radius: 1.75rem;
                background:
                    radial-gradient(circle at top left, rgba(16, 185, 129, 0.18), transparent 28%),
                    radial-gradient(circle at top right, rgba(14, 165, 233, 0.16), transparent 28%),
                    linear-gradient(135deg, rgba(255, 255, 255, 0.96), rgba(248, 250, 252, 0.96));
                border: 1px solid rgba(226, 232, 240, 0.9);
                box-shadow:
                    0 20px 60px rgba(15, 23, 42, 0.10),
                    0 8px 24px rgba(15, 23, 42, 0.06);
            }

            .reset-stage::before {
                content: "";
                position: absolute;
                inset: 0;
                background-image:
                    linear-gradient(rgba(15, 23, 42, 0.04) 1px, transparent 1px),
                    linear-gradient(90deg, rgba(15, 23, 42, 0.04) 1px, transparent 1px);
                background-size: 26px 26px;
                pointer-events: none;
                opacity: .45;
            }

            .reset-input {
                transition: all .22s ease;
            }

            .reset-input.input-valid {
                border-color: rgb(16 185 129) !important;
            }

            .reset-input.input-invalid {
                border-color: rgb(248 113 113) !important;
            }

            .reset-input:focus {
                transform: translateY(-1px);
                box-shadow: 0 0 0 4px rgba(16, 185, 129, 0.12);
            }

            .reset-btn {
                transition: all .22s ease;
            }

            .reset-btn:hover {
                transform: translateY(-1px) scale(1.01);
                box-shadow: 0 14px 30px rgba(16, 185, 129, 0.22);
            }

            .reset-btn:disabled {
                cursor: not-allowed;
                opacity: .55;
                box-shadow: none;
                transform: none !important;
            }

            .fade-up {
                animation: fadeUp .65s ease both;
            }

            .fade-up.delay-1 {
                animation-delay: .08s;
            }

            .fade-up.delay-2 {
                animation-delay: .16s;
            }

            .fade-up.delay-3 {
                animation-delay: .24s;
            }

            @keyframes fadeUp {
                from {
                    opacity: 0;
                    transform: translateY(14px);
                }

                to {
                    opacity: 1;
                    transform: translateY(0);
                }
            }

            @keyframes floatLogo {

                0%,
                100% {
                    transform: translateY(0px);
                }

                50% {
                    transform: translateY(-8px);
                }
            }

            @media (prefers-reduced-motion: reduce) {

                .fade-up,
                .animate-\[floatLogo_5s_ease-in-out_infinite\] {
                    animation: none !important;
                }
            }
        </style>

        <div x-data="{
            showPassword: false,
            showConfirm: false,
            email: @js(old('email', $request->email)),
            password: '',
            password_confirmation: '',
            isSubmitting: false,

            validateEmail(email) {
                return /^[a-zA-Z0-9._%+-]+@gmail\.com$/.test(email);
            },

            get emailValid() {
                return this.validateEmail(this.email.trim());
            },

            get hasMinLength() {
                return this.password.length >= 8;
            },

            get hasLetter() {
                return /[A-Za-z]/.test(this.password);
            },

            get hasNumber() {
                return /[0-9]/.test(this.password);
            },

            get hasSpecial() {
                return /[^A-Za-z0-9]/.test(this.password);
            },

            get passwordsMatch() {
                return this.password !== '' && this.password === this.password_confirmation;
            },

            get canSubmit() {
                return this.emailValid
                    && this.hasMinLength
                    && this.hasLetter
                    && this.hasNumber
                    && this.hasSpecial
                    && this.passwordsMatch;
            },

            submitForm(event) {
                if (!this.canSubmit || this.isSubmitting) {
                    event.preventDefault();
                    return;
                }

                this.isSubmitting = true;
                event.target.submit();
            }
        }" class="reset-stage px-6 py-7 sm:px-8 sm:py-8">
            <div class="relative z-10">
                <div class="fade-up">
                    <div
                        class="mb-3 inline-flex items-center gap-2 rounded-full border border-emerald-200 bg-white/80 px-3 py-1 text-[11px] font-semibold uppercase tracking-[0.18em] text-emerald-700 shadow-sm">
                        <span class="h-2 w-2 rounded-full bg-emerald-500 animate-pulse"></span>
                        Restablecimiento seguro
                    </div>

                    <h1 class="text-center text-3xl font-black tracking-tight text-slate-900">
                        Nueva contraseña
                    </h1>

                    <p class="mx-auto mt-2 max-w-md text-center text-sm leading-6 text-slate-600">
                        Define una nueva contraseña para recuperar el acceso a tu cuenta institucional.
                    </p>
                </div>

                <div class="fade-up delay-1 mt-6">
                    <x-validation-errors
                        class="mb-4 hidden rounded-2xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-700" />

                    @if ($errors->any())
                        <script>
                            document.addEventListener('DOMContentLoaded', function () {
                                Swal.fire({
                                    icon: 'error',
                                    title: 'No se pudo restablecer la contraseña',
                                    text: @json($errors->first()),
                                    confirmButtonText: 'Entendido',
                                    confirmButtonColor: '#dc2626'
                                });
                            });
                        </script>
                    @endif
                </div>

                <form method="POST" action="{{ route('password.update') }}" class="fade-up delay-2 space-y-5"
                    id="resetForm" @submit.prevent="submitForm($event)">
                    @csrf

                    <input type="hidden" name="token" value="{{ $request->route('token') }}">

                    {{-- CORREO --}}
                    <div>
                        <label for="email" class="mb-2 block text-sm font-semibold text-slate-700">
                            Correo electrónico
                        </label>

                        <div class="relative">
                            <span
                                class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-4 text-slate-400">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20"
                                    fill="currentColor">
                                    <path d="M2.94 5.5A2 2 0 014.75 4h10.5a2 2 0 011.81 1.5L10 9.88 2.94 5.5z" />
                                    <path
                                        d="M18 8.12l-7.45 4.16a1 1 0 01-1.1 0L2 8.12V14a2 2 0 002 2h12a2 2 0 002-2V8.12z" />
                                </svg>
                            </span>

                            <x-input id="email"
                                class="reset-input block mt-1 w-full rounded-2xl border-slate-300 bg-white/90 py-3.5 pl-11 pr-4 text-sm text-slate-800 placeholder-slate-400 shadow-sm focus:border-emerald-500 focus:ring-emerald-500"
                                type="email" name="email" :value="old('email', $request->email)" x-model="email"
                                required autofocus autocomplete="username" inputmode="email" maxlength="120"
                                placeholder="usuario@gmail.com" />
                        </div>

                        <p x-show="email && !emailValid" x-cloak class="mt-2 text-xs font-medium text-rose-500">
                            Ingresa un correo válido, por ejemplo: usuario@gmail.com
                        </p>
                    </div>

                    {{-- NUEVA CONTRASEÑA --}}
                    <div>
                        <label for="password" class="mb-2 block text-sm font-semibold text-slate-700">
                            Nueva contraseña
                        </label>

                        <div class="relative">
                            <span
                                class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-4 text-slate-400">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20"
                                    fill="currentColor">
                                    <path fill-rule="evenodd"
                                        d="M5 8a5 5 0 1110 0v1h.5A1.5 1.5 0 0117 10.5v5A1.5 1.5 0 0115.5 17h-11A1.5 1.5 0 013 15.5v-5A1.5 1.5 0 014.5 9H5V8zm2 1h6V8a3 3 0 10-6 0v1z"
                                        clip-rule="evenodd" />
                                </svg>
                            </span>

                            <x-input id="password"
                                class="reset-input block mt-1 w-full rounded-2xl border-slate-300 bg-white/90 py-3.5 pl-11 pr-12 text-sm text-slate-800 placeholder-slate-400 shadow-sm focus:border-emerald-500 focus:ring-emerald-500"
                                x-bind:type="showPassword ? 'text' : 'password'" name="password" x-model="password"
                                required autocomplete="new-password" placeholder="Ingresa tu nueva contraseña" />

                            <button type="button" @click="showPassword = !showPassword"
                                class="absolute inset-y-0 right-0 flex items-center pr-4 text-slate-400 transition hover:text-emerald-600"
                                title="Mostrar u ocultar contraseña">
                                <svg x-show="!showPassword" xmlns="http://www.w3.org/2000/svg" class="h-5 w-5"
                                    fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                        d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7Z" />
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                        d="M12 15.75A3.75 3.75 0 1 0 12 8.25a3.75 3.75 0 0 0 0 7.5Z" />
                                </svg>

                                <svg x-show="showPassword" x-cloak xmlns="http://www.w3.org/2000/svg" class="h-5 w-5"
                                    fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M3 3l18 18" />
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                        d="M10.584 10.587A2.25 2.25 0 0 0 13.41 13.41" />
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                        d="M9.88 5.09A9.953 9.953 0 0 1 12 4.875c5.053 0 9.27 3.11 10.5 7.125a11.03 11.03 0 0 1-4.04 5.411M6.228 6.228A11.03 11.03 0 0 0 1.5 12c.69 2.25 2.14 4.175 4.04 5.411A9.953 9.953 0 0 0 12 19.125c.73 0 1.442-.078 2.125-.227" />
                                </svg>
                            </button>
                        </div>
                    </div>

                    {{-- CONFIRMAR CONTRASEÑA --}}
                    <div>
                        <label for="password_confirmation" class="mb-2 block text-sm font-semibold text-slate-700">
                            Confirmar nueva contraseña
                        </label>

                        <div class="relative">
                            <span
                                class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-4 text-slate-400">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20"
                                    fill="currentColor">
                                    <path fill-rule="evenodd"
                                        d="M5 8a5 5 0 1110 0v1h.5A1.5 1.5 0 0117 10.5v5A1.5 1.5 0 0115.5 17h-11A1.5 1.5 0 013 15.5v-5A1.5 1.5 0 014.5 9H5V8zm2 1h6V8a3 3 0 10-6 0v1z"
                                        clip-rule="evenodd" />
                                </svg>
                            </span>

                            <x-input id="password_confirmation"
                                class="reset-input block mt-1 w-full rounded-2xl border-slate-300 bg-white/90 py-3.5 pl-11 pr-12 text-sm text-slate-800 placeholder-slate-400 shadow-sm focus:border-emerald-500 focus:ring-emerald-500"
                                x-bind:type="showConfirm ? 'text' : 'password'" name="password_confirmation"
                                x-model="password_confirmation" required autocomplete="new-password"
                                placeholder="Confirma tu nueva contraseña" />

                            <button type="button" @click="showConfirm = !showConfirm"
                                class="absolute inset-y-0 right-0 flex items-center pr-4 text-slate-400 transition hover:text-emerald-600"
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
                    </div>

                    {{-- REQUISITOS --}}
                    <div class="rounded-2xl border border-slate-200 bg-slate-50 px-4 py-4">
                        <p class="text-sm font-semibold text-slate-800">
                            Requisitos de la nueva contraseña
                        </p>

                        <div class="mt-3 space-y-2 text-sm">
                            <div class="flex items-center gap-2">
                                <span x-text="hasMinLength ? '✓' : '•'"
                                    :class="hasMinLength ? 'text-emerald-600' : 'text-slate-400'"></span>
                                <span :class="hasMinLength ? 'text-emerald-700' : 'text-slate-600'">
                                    Mínimo 8 caracteres
                                </span>
                            </div>

                            <div class="flex items-center gap-2">
                                <span x-text="hasLetter ? '✓' : '•'"
                                    :class="hasLetter ? 'text-emerald-600' : 'text-slate-400'"></span>
                                <span :class="hasLetter ? 'text-emerald-700' : 'text-slate-600'">
                                    Al menos una letra
                                </span>
                            </div>

                            <div class="flex items-center gap-2">
                                <span x-text="hasNumber ? '✓' : '•'"
                                    :class="hasNumber ? 'text-emerald-600' : 'text-slate-400'"></span>
                                <span :class="hasNumber ? 'text-emerald-700' : 'text-slate-600'">
                                    Al menos un número
                                </span>
                            </div>

                            <div class="flex items-center gap-2">
                                <span x-text="hasSpecial ? '✓' : '•'"
                                    :class="hasSpecial ? 'text-emerald-600' : 'text-slate-400'"></span>
                                <span :class="hasSpecial ? 'text-emerald-700' : 'text-slate-600'">
                                    Al menos un carácter especial
                                </span>
                            </div>

                            <div class="flex items-center gap-2">
                                <span x-text="passwordsMatch ? '✓' : '•'"
                                    :class="passwordsMatch ? 'text-emerald-600' : 'text-slate-400'"></span>
                                <span :class="passwordsMatch ? 'text-emerald-700' : 'text-slate-600'">
                                    La confirmación coincide con la contraseña
                                </span>
                            </div>
                        </div>
                    </div>

                    <div class="rounded-2xl border border-sky-200 bg-sky-50 px-4 py-3 text-sm text-sky-800">
                        Una vez guardada, deberás iniciar sesión con tu nueva contraseña.
                    </div>

                    <div class="fade-up delay-3 pt-1">
                        <button type="submit" x-bind:disabled="!canSubmit || isSubmitting" x-bind:class="(canSubmit && !isSubmitting)
                                ? 'bg-gradient-to-r from-emerald-600 via-emerald-500 to-sky-600 text-white shadow-lg shadow-emerald-500/25 hover:from-emerald-700 hover:via-emerald-600 hover:to-sky-700'
                                : 'bg-slate-200 text-slate-400 cursor-not-allowed'"
                            class="reset-btn flex w-full items-center justify-center gap-2 rounded-2xl px-5 py-3.5 text-sm font-bold tracking-wide transition">
                            <svg x-show="isSubmitting" x-cloak xmlns="http://www.w3.org/2000/svg"
                                class="h-4 w-4 animate-spin" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor"
                                    stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z">
                                </path>
                            </svg>

                            <span x-text="isSubmitting ? 'Actualizando...' : 'Restablecer contraseña'"></span>
                        </button>
                    </div>
                </form>

                <div class="mt-6 border-t border-slate-200/80 pt-5 text-center">
                    <p class="text-xs leading-6 text-slate-500">
                        Este cambio actualizará el acceso de tu cuenta dentro del sistema institucional.
                    </p>
                </div>
            </div>
        </div>
    </x-authentication-card>
</x-guest-layout>