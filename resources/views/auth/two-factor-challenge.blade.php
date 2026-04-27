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
            .twofa-stage {
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

            .twofa-stage::before {
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

            .twofa-input {
                transition: all .22s ease;
            }

            .twofa-input:focus {
                transform: translateY(-1px);
                box-shadow: 0 0 0 4px rgba(16, 185, 129, 0.12);
            }

            .twofa-btn {
                transition: all .22s ease;
            }

            .twofa-btn:hover {
                transform: translateY(-1px) scale(1.01);
                box-shadow: 0 14px 30px rgba(16, 185, 129, 0.22);
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
                recovery: {{ old('recovery_code') ? 'true' : 'false' }} === 'true',
                code: '',
                recovery_code: '',
                isSubmitting: false,

                get canSubmit() {
                    return this.recovery
                        ? this.recovery_code.trim().length >= 6
                        : this.code.trim().length >= 6;
                },

                submitForm(event) {
                    if (!this.canSubmit || this.isSubmitting) {
                        event.preventDefault();
                        return;
                    }

                    this.isSubmitting = true;
                    event.target.submit();
                },

                switchToRecovery() {
                    this.recovery = true;
                    this.code = '';
                    this.$nextTick(() => {
                        this.$refs.recovery_code.focus();
                    });
                },

                switchToCode() {
                    this.recovery = false;
                    this.recovery_code = '';
                    this.$nextTick(() => {
                        this.$refs.code.focus();
                    });
                }
            }" class="twofa-stage px-6 py-7 sm:px-8 sm:py-8">
            <div class="relative z-10">
                <div class="fade-up">
                    <div
                        class="mb-3 inline-flex items-center gap-2 rounded-full border border-emerald-200 bg-white/80 px-3 py-1 text-[11px] font-semibold uppercase tracking-[0.18em] text-emerald-700 shadow-sm">
                        <span class="h-2 w-2 rounded-full bg-emerald-500 animate-pulse"></span>
                        Seguridad adicional
                    </div>

                    <h1 class="text-center text-3xl font-black tracking-tight text-slate-900">
                        Verificación de acceso
                    </h1>

                    <p class="mx-auto mt-2 max-w-md text-center text-sm leading-6 text-slate-600" x-show="!recovery">
                        Confirma el acceso a tu cuenta ingresando el código generado por tu aplicación autenticadora.
                    </p>

                    <p class="mx-auto mt-2 max-w-md text-center text-sm leading-6 text-slate-600" x-cloak
                        x-show="recovery">
                        Confirma el acceso utilizando uno de tus códigos de recuperación de emergencia.
                    </p>
                </div>

                <div class="fade-up delay-1 mt-6">
                    <x-validation-errors
                        class="mb-4 hidden rounded-2xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-700" />
                </div>

                @if ($errors->any())
                    <script>
                        document.addEventListener('DOMContentLoaded', function () {
                            let mensaje = 'El código ingresado no es válido. Intenta nuevamente.';

                            @if (old('recovery_code'))
                                mensaje = 'El código de recuperación ingresado no es válido. Verifica e intenta nuevamente.';
                            @else
                                mensaje = 'El código de verificación ingresado no es válido. Intenta nuevamente.';
                            @endif

                                const codeInput = document.getElementById('code');
                            const recoveryInput = document.getElementById('recovery_code');

                            if (codeInput) codeInput.value = '';
                            if (recoveryInput) recoveryInput.value = '';

                            Swal.fire({
                                icon: 'error',
                                title: 'No se pudo verificar el acceso',
                                text: mensaje,
                                confirmButtonText: 'Entendido',
                                confirmButtonColor: '#dc2626'
                            });
                        });
                    </script>
                @endif

                <form method="POST" action="{{ route('two-factor.login') }}" class="fade-up delay-2 space-y-5"
                    @submit.prevent="submitForm($event)">
                    @csrf

                    {{-- MODO CÓDIGO AUTENTICADOR --}}
                    <div x-show="!recovery">
                        <label for="code" class="mb-2 block text-sm font-semibold text-slate-700">
                            Código de verificación
                        </label>

                        <div class="relative">
                            <span
                                class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-4 text-slate-400">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24"
                                    stroke="currentColor" stroke-width="1.8">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                        d="M15 7.5V6a3 3 0 10-6 0v1.5m-1.5 0h9A1.5 1.5 0 0118 9v9a1.5 1.5 0 01-1.5 1.5h-9A1.5 1.5 0 016 18V9a1.5 1.5 0 011.5-1.5z" />
                                </svg>
                            </span>

                            <x-input id="code"
                                class="twofa-input block mt-1 w-full rounded-2xl border-slate-300 bg-white/90 py-3.5 pl-11 pr-4 text-sm text-slate-800 placeholder-slate-400 shadow-sm focus:border-emerald-500 focus:ring-emerald-500"
                                type="text" inputmode="numeric" name="code" x-ref="code" x-model="code"
                                autocomplete="one-time-code" placeholder="Ingresa el código temporal" />
                        </div>
                    </div>

                    {{-- MODO CÓDIGO DE RECUPERACIÓN --}}
                    <div x-cloak x-show="recovery">
                        <label for="recovery_code" class="mb-2 block text-sm font-semibold text-slate-700">
                            Código de recuperación
                        </label>

                        <div class="relative">
                            <span
                                class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-4 text-slate-400">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24"
                                    stroke="currentColor" stroke-width="1.8">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                        d="M16.5 10.5V6.75A2.25 2.25 0 0014.25 4.5h-9A2.25 2.25 0 003 6.75v10.5A2.25 2.25 0 005.25 19.5h9a2.25 2.25 0 002.25-2.25V13.5M9 12l2 2 4-4" />
                                </svg>
                            </span>

                            <x-input id="recovery_code"
                                class="twofa-input block mt-1 w-full rounded-2xl border-slate-300 bg-white/90 py-3.5 pl-11 pr-4 text-sm text-slate-800 placeholder-slate-400 shadow-sm focus:border-emerald-500 focus:ring-emerald-500"
                                type="text" name="recovery_code" x-ref="recovery_code" x-model="recovery_code"
                                autocomplete="one-time-code" placeholder="Ingresa un código de recuperación" />
                        </div>
                    </div>

                    <div class="rounded-2xl border border-sky-200 bg-sky-50 px-4 py-3 text-sm text-sky-800">
                        <span x-show="!recovery">
                            Usa el código actual generado por tu aplicación autenticadora para continuar.
                        </span>
                        <span x-cloak x-show="recovery">
                            Usa un código de recuperación únicamente si no tienes acceso a tu aplicación autenticadora.
                        </span>
                    </div>

                    <div class="rounded-2xl border border-slate-200 bg-slate-50 px-4 py-4">
                        <div class="flex items-center gap-3 text-sm">
                            <span class="flex h-5 w-5 items-center justify-center">
                                <svg x-show="canSubmit" xmlns="http://www.w3.org/2000/svg"
                                    class="h-5 w-5 text-emerald-600" viewBox="0 0 20 20" fill="currentColor">
                                    <path fill-rule="evenodd"
                                        d="M16.704 5.29a1 1 0 0 1 .006 1.414l-7.2 7.26a1 1 0 0 1-1.42.005L3.3 9.173a1 1 0 1 1 1.4-1.428l3.08 3.022 6.5-6.55a1 1 0 0 1 1.424-.006Z"
                                        clip-rule="evenodd" />
                                </svg>

                                <svg x-show="!canSubmit" xmlns="http://www.w3.org/2000/svg"
                                    class="h-5 w-5 text-slate-400" viewBox="0 0 20 20" fill="currentColor">
                                    <path
                                        d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.53-10.97a.75.75 0 10-1.06-1.06L10 8.44 7.53 5.97a.75.75 0 10-1.06 1.06L8.94 9.5 6.47 11.97a.75.75 0 101.06 1.06L10 10.56l2.47 2.47a.75.75 0 001.06-1.06L11.06 9.5l2.47-2.47z" />
                                </svg>
                            </span>

                            <span :class="canSubmit ? 'text-emerald-700' : 'text-slate-600'">
                                Ingresa un código válido para habilitar la verificación.
                            </span>
                        </div>
                    </div>

                    <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                        <button type="button"
                            class="text-sm font-medium text-emerald-700 transition hover:text-emerald-800 hover:underline"
                            x-show="!recovery" @click="switchToRecovery()">
                            Usar un código de recuperación
                        </button>

                        <button type="button"
                            class="text-sm font-medium text-emerald-700 transition hover:text-emerald-800 hover:underline"
                            x-cloak x-show="recovery" @click="switchToCode()">
                            Usar código de autenticación
                        </button>

                        <button type="submit" x-bind:disabled="!canSubmit || isSubmitting" x-bind:class="(canSubmit && !isSubmitting)
                                ? 'bg-gradient-to-r from-emerald-600 via-emerald-500 to-sky-600 text-white shadow-lg shadow-emerald-500/25 hover:from-emerald-700 hover:via-emerald-600 hover:to-sky-700'
                                : 'bg-slate-200 text-slate-400 cursor-not-allowed'"
                            class="twofa-btn flex items-center justify-center gap-2 rounded-2xl px-5 py-3.5 text-sm font-bold tracking-wide transition">
                            <svg x-show="isSubmitting" x-cloak xmlns="http://www.w3.org/2000/svg"
                                class="h-4 w-4 animate-spin" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor"
                                    stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z">
                                </path>
                            </svg>

                            <span x-text="isSubmitting ? 'Verificando...' : 'Verificar acceso'"></span>

                            <svg x-show="!isSubmitting" xmlns="http://www.w3.org/2000/svg" class="h-4 w-4"
                                viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd"
                                    d="M10.293 15.707a1 1 0 010-1.414L13.586 11H3a1 1 0 110-2h10.586l-3.293-3.293a1 1 0 111.414-1.414l5 5a1 1 0 010 1.414l-5 5a1 1 0 01-1.414 0z"
                                    clip-rule="evenodd" />
                            </svg>
                        </button>
                    </div>
                </form>

                <div class="mt-6 border-t border-slate-200/80 pt-5 text-center">
                    <p class="text-xs leading-6 text-slate-500">
                        Este paso adicional protege el acceso a tu cuenta institucional.
                    </p>
                </div>
            </div>
        </div>
    </x-authentication-card>
</x-guest-layout>