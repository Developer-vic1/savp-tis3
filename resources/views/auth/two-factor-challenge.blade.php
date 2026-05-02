<x-guest-layout>
    <x-authentication-card>
        <x-slot name="logo">
            <div class="text-center">
                <div
                    class="relative mx-auto flex h-24 w-24 items-center justify-center rounded-[1.7rem] bg-white shadow-[0_24px_60px_rgba(16,185,129,0.18)] ring-4 ring-white/70 dark:bg-slate-900 dark:ring-slate-700/80">
                    <img src="{{ asset('image/LOGO FT3 A.jpg') }}" alt="Logo Franz Tamayo N°3"
                        class="h-16 w-16 rounded-2xl object-contain">

                    <span
                        class="absolute -right-1 -top-1 h-4 w-4 rounded-full bg-sky-400 shadow-lg shadow-sky-400/60 animate-pulse"></span>
                    <span
                        class="absolute -bottom-1 -left-1 h-3 w-3 rounded-full bg-emerald-300 shadow-lg shadow-emerald-300/60 animate-pulse"></span>
                </div>

                <div class="mt-4">
                    <p class="font-display text-base font-black text-slate-950 dark:text-white">
                        Franz Tamayo N°3
                    </p>
                    <p class="text-xs font-black uppercase tracking-[0.18em] text-emerald-600 dark:text-emerald-300">
                        Sistema SAVP – TIS 3
                    </p>
                </div>
            </div>
        </x-slot>

        <style>
            .twofa-stage {
                position: relative;
                overflow: hidden;
                width: min(100%, 46rem);
                min-height: 570px;
                border-radius: 2.2rem;
                background:
                    radial-gradient(circle at top left, rgba(16, 185, 129, 0.18), transparent 30%),
                    radial-gradient(circle at top right, rgba(14, 165, 233, 0.16), transparent 30%),
                    linear-gradient(135deg, rgba(255, 255, 255, 0.97), rgba(248, 250, 252, 0.97));
                border: 1px solid rgba(226, 232, 240, 0.95);
                box-shadow:
                    0 28px 90px rgba(15, 23, 42, 0.14),
                    0 10px 28px rgba(15, 23, 42, 0.08);
            }

            html.dark .twofa-stage {
                background:
                    radial-gradient(circle at top left, rgba(52, 211, 153, 0.13), transparent 30%),
                    radial-gradient(circle at top right, rgba(56, 189, 248, 0.12), transparent 30%),
                    linear-gradient(135deg, rgba(15, 23, 42, 0.97), rgba(30, 41, 59, 0.97));
                border-color: rgba(71, 85, 105, 0.86);
                box-shadow:
                    0 28px 95px rgba(0, 0, 0, 0.40),
                    0 10px 28px rgba(0, 0, 0, 0.24);
            }

            .twofa-stage::before {
                content: "";
                position: absolute;
                inset: 0;
                background-image:
                    linear-gradient(rgba(15, 23, 42, 0.04) 1px, transparent 1px),
                    linear-gradient(90deg, rgba(15, 23, 42, 0.04) 1px, transparent 1px);
                background-size: 28px 28px;
                pointer-events: none;
                opacity: .55;
            }

            html.dark .twofa-stage::before {
                background-image:
                    linear-gradient(rgba(148, 163, 184, 0.055) 1px, transparent 1px),
                    linear-gradient(90deg, rgba(148, 163, 184, 0.055) 1px, transparent 1px);
            }

            .twofa-back-btn,
            .twofa-theme-btn {
                position: absolute;
                top: 1.25rem;
                z-index: 20;
                display: inline-flex;
                align-items: center;
                justify-content: center;
                width: 2.8rem;
                height: 2.8rem;
                border-radius: 1rem;
                border: 1px solid rgba(203, 213, 225, .8);
                background: rgba(255, 255, 255, .84);
                color: #334155;
                box-shadow: 0 12px 26px rgba(15, 23, 42, .08);
                backdrop-filter: blur(12px);
                transition: transform .22s ease, color .22s ease, border-color .22s ease, background .22s ease;
            }

            .twofa-back-btn {
                left: 1.25rem;
            }

            .twofa-theme-btn {
                right: 1.25rem;
            }

            html.dark .twofa-back-btn,
            html.dark .twofa-theme-btn {
                background: rgba(15, 23, 42, .78);
                color: #cbd5e1;
                border-color: rgba(71, 85, 105, .86);
            }

            .twofa-back-btn:hover {
                transform: translateY(-2px);
                color: #059669;
                border-color: rgba(16, 185, 129, .48);
            }

            .twofa-theme-btn:hover {
                transform: translateY(-2px);
                color: #0284c7;
                border-color: rgba(14, 165, 233, .48);
            }

            .twofa-input {
                transition: all .22s ease;
            }

            .twofa-input:focus {
                transform: translateY(-1px);
                box-shadow: 0 0 0 4px rgba(16, 185, 129, 0.13);
            }

            .twofa-input.input-valid {
                border-color: rgb(16 185 129) !important;
            }

            .twofa-input.input-invalid {
                border-color: rgb(248 113 113) !important;
            }

            .twofa-btn {
                transition: all .22s ease;
            }

            .twofa-btn:hover {
                transform: translateY(-1px) scale(1.01);
                box-shadow: 0 18px 38px rgba(16, 185, 129, 0.24);
            }

            .twofa-btn:disabled {
                cursor: not-allowed;
                opacity: .55;
                box-shadow: none;
                transform: none !important;
            }

            .twofa-option {
                transition: transform .2s ease, border-color .2s ease, background .2s ease;
            }

            .twofa-option:hover {
                transform: translateY(-1px);
            }

            .particle {
                position: absolute;
                border-radius: 9999px;
                pointer-events: none;
                animation: floatY linear infinite;
                opacity: .7;
                filter: blur(.2px);
            }

            .particle.one {
                width: 10px;
                height: 10px;
                left: 8%;
                top: 14%;
                background: rgba(16, 185, 129, 0.38);
                animation-duration: 7s;
            }

            .particle.two {
                width: 8px;
                height: 8px;
                right: 12%;
                top: 20%;
                background: rgba(14, 165, 233, 0.35);
                animation-duration: 9s;
            }

            .particle.three {
                width: 12px;
                height: 12px;
                left: 12%;
                bottom: 16%;
                background: rgba(52, 211, 153, 0.25);
                animation-duration: 11s;
            }

            .particle.four {
                width: 7px;
                height: 7px;
                right: 15%;
                bottom: 14%;
                background: rgba(56, 189, 248, 0.30);
                animation-duration: 8s;
            }

            .shine-line {
                position: absolute;
                inset: 0;
                overflow: hidden;
                border-radius: 2.2rem;
                pointer-events: none;
            }

            .shine-line::after {
                content: "";
                position: absolute;
                top: -20%;
                left: -40%;
                width: 34%;
                height: 140%;
                transform: rotate(18deg);
                background: linear-gradient(to right,
                        transparent,
                        rgba(255, 255, 255, .48),
                        transparent);
                animation: sweep 6s infinite;
            }

            html.dark .shine-line::after {
                background: linear-gradient(to right,
                        transparent,
                        rgba(255, 255, 255, .12),
                        transparent);
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

            @keyframes floatY {

                0%,
                100% {
                    transform: translateY(0px) translateX(0px);
                }

                25% {
                    transform: translateY(-10px) translateX(5px);
                }

                50% {
                    transform: translateY(-18px) translateX(-4px);
                }

                75% {
                    transform: translateY(-8px) translateX(6px);
                }
            }

            @keyframes sweep {
                0% {
                    left: -42%;
                }

                100% {
                    left: 135%;
                }
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

                .particle,
                .shine-line::after,
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

            normalizeCode(value) {
                return value.replace(/[^0-9]/g, '').slice(0, 6);
            },

            normalizeRecovery(value) {
                return value.replace(/[^A-Za-z0-9-]/g, '').toUpperCase().slice(0, 32);
            },

            get codeValid() {
                return /^[0-9]{6}$/.test(this.code.trim());
            },

            get recoveryValid() {
                return this.recovery_code.trim().length >= 6;
            },

            get canSubmit() {
                return this.recovery
                    ? this.recoveryValid && !this.isSubmitting
                    : this.codeValid && !this.isSubmitting;
            },

            submitForm(event) {
                if (!this.canSubmit) {
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
                    this.$refs.recovery_code?.focus();
                });
            },

            switchToCode() {
                this.recovery = false;
                this.recovery_code = '';
                this.$nextTick(() => {
                    this.$refs.code?.focus();
                });
            }
        }" class="twofa-stage px-7 pb-8 pt-20 sm:px-10 sm:pb-10">

            {{-- Botón volver al welcome --}}
            <a href="{{ url('/') }}" class="twofa-back-btn" title="Volver al inicio" aria-label="Volver al inicio">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24"
                    stroke="currentColor" stroke-width="1.8">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5 3 12m0 0 7.5-7.5M3 12h18" />
                </svg>
            </a>

            {{-- Botón tema --}}
            <button type="button" id="twofaThemeToggle" class="twofa-theme-btn" title="Cambiar tema"
                aria-label="Cambiar tema">
                <svg class="h-5 w-5 dark:hidden" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"
                        d="M21.752 15.002A9.72 9.72 0 0 1 18 15.75 9.75 9.75 0 0 1 8.25 6c0-1.33.266-2.597.748-3.752A9.753 9.753 0 0 0 3 11.25 9.75 9.75 0 0 0 12.75 21a9.753 9.753 0 0 0 9.002-5.998Z" />
                </svg>

                <svg class="hidden h-5 w-5 dark:block" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"
                        d="M12 3v2.25m0 13.5V21m9-9h-2.25M5.25 12H3m15.364-6.364-1.591 1.591M7.227 16.773l-1.591 1.591m12.728 0-1.591-1.591M7.227 7.227 5.636 5.636M15.75 12a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0Z" />
                </svg>
            </button>

            <div class="particle one"></div>
            <div class="particle two"></div>
            <div class="particle three"></div>
            <div class="particle four"></div>
            <div class="shine-line"></div>

            <div class="relative z-10">
                <div class="fade-up text-center">
                    <div
                        class="mx-auto mb-4 inline-flex items-center gap-2 rounded-full border border-emerald-200 bg-white/80 px-3 py-1 text-[11px] font-black uppercase tracking-[0.18em] text-emerald-700 shadow-sm dark:border-emerald-400/20 dark:bg-emerald-400/10 dark:text-emerald-300">
                        <span class="h-2 w-2 rounded-full bg-emerald-500 animate-pulse"></span>
                        Seguridad adicional
                    </div>

                    <h1 class="text-4xl font-black tracking-tight text-slate-950 dark:text-white">
                        Verificación de acceso
                    </h1>

                    <p class="mx-auto mt-3 max-w-lg text-base leading-7 text-slate-600 dark:text-slate-300"
                        x-show="!recovery">
                        Confirma el acceso a tu cuenta ingresando el código temporal generado por tu aplicación
                        autenticadora.
                    </p>

                    <p class="mx-auto mt-3 max-w-lg text-base leading-7 text-slate-600 dark:text-slate-300" x-cloak
                        x-show="recovery">
                        Confirma el acceso utilizando uno de tus códigos de recuperación de emergencia.
                    </p>
                </div>

                <div class="fade-up delay-1 mt-7">
                    <x-validation-errors
                        class="mb-4 hidden rounded-2xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-700 dark:border-rose-400/20 dark:bg-rose-400/10 dark:text-rose-200" />
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

                <form method="POST" action="{{ route('two-factor.login') }}" class="fade-up delay-2 space-y-6"
                    @submit.prevent="submitForm($event)" novalidate>
                    @csrf

                    {{-- SELECTOR DE MÉTODO --}}
                    <div class="grid gap-3 sm:grid-cols-2">
                        <button type="button" @click="switchToCode()"
                            class="twofa-option rounded-2xl border px-4 py-4 text-left" x-bind:style="!recovery
                                ? 'background: rgba(16,185,129,.12); border-color: rgba(16,185,129,.45);'
                                : 'background: rgba(255,255,255,.72); border-color: rgba(203,213,225,.72);'"
                            x-bind:class="recovery ? 'dark:bg-slate-900/60 dark:border-slate-700' : ''">
                            <div class="flex items-center gap-3">
                                <div
                                    class="flex h-10 w-10 items-center justify-center rounded-2xl bg-emerald-100 text-emerald-700 dark:bg-emerald-400/10 dark:text-emerald-300">
                                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"
                                            d="M15 7.5V6a3 3 0 1 0-6 0v1.5m-1.5 0h9A1.5 1.5 0 0 1 18 9v9a1.5 1.5 0 0 1-1.5 1.5h-9A1.5 1.5 0 0 1 6 18V9a1.5 1.5 0 0 1 1.5-1.5Z" />
                                    </svg>
                                </div>

                                <div>
                                    <p class="text-sm font-black text-slate-900 dark:text-white">
                                        Código autenticador
                                    </p>
                                    <p class="text-xs text-slate-500 dark:text-slate-400">
                                        6 dígitos temporales.
                                    </p>
                                </div>
                            </div>
                        </button>

                        <button type="button" @click="switchToRecovery()"
                            class="twofa-option rounded-2xl border px-4 py-4 text-left" x-bind:style="recovery
                                ? 'background: rgba(14,165,233,.12); border-color: rgba(14,165,233,.45);'
                                : 'background: rgba(255,255,255,.72); border-color: rgba(203,213,225,.72);'"
                            x-bind:class="!recovery ? 'dark:bg-slate-900/60 dark:border-slate-700' : ''">
                            <div class="flex items-center gap-3">
                                <div
                                    class="flex h-10 w-10 items-center justify-center rounded-2xl bg-sky-100 text-sky-700 dark:bg-sky-400/10 dark:text-sky-300">
                                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"
                                            d="M16.5 10.5V6.75A2.25 2.25 0 0 0 14.25 4.5h-9A2.25 2.25 0 0 0 3 6.75v10.5A2.25 2.25 0 0 0 5.25 19.5h9a2.25 2.25 0 0 0 2.25-2.25V13.5M9 12l2 2 4-4" />
                                    </svg>
                                </div>

                                <div>
                                    <p class="text-sm font-black text-slate-900 dark:text-white">
                                        Recuperación
                                    </p>
                                    <p class="text-xs text-slate-500 dark:text-slate-400">
                                        Código de emergencia.
                                    </p>
                                </div>
                            </div>
                        </button>
                    </div>

                    {{-- MODO CÓDIGO AUTENTICADOR --}}
                    <div x-show="!recovery" x-transition.opacity>
                        <label for="code" class="mb-2 block text-sm font-bold text-slate-700 dark:text-slate-200">
                            Código de verificación
                        </label>

                        <div class="relative">
                            <span
                                class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-4 text-slate-400 dark:text-slate-500">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24"
                                    stroke="currentColor" stroke-width="1.8">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                        d="M15 7.5V6a3 3 0 1 0-6 0v1.5m-1.5 0h9A1.5 1.5 0 0 1 18 9v9a1.5 1.5 0 0 1-1.5 1.5h-9A1.5 1.5 0 0 1 6 18V9a1.5 1.5 0 0 1 1.5-1.5Z" />
                                </svg>
                            </span>

                            <x-input id="code"
                                class="twofa-input mt-1 block w-full rounded-2xl border-slate-300 bg-white/95 py-4 pl-11 pr-4 text-center text-2xl font-black tracking-[0.42em] text-slate-800 placeholder:text-base placeholder:font-medium placeholder:tracking-normal placeholder-slate-400 shadow-sm focus:border-emerald-500 focus:ring-emerald-500 dark:border-slate-600 dark:bg-slate-900/80 dark:text-slate-100 dark:placeholder-slate-500"
                                type="text" inputmode="numeric" name="code" x-ref="code" x-model="code"
                                x-on:input="code = normalizeCode($event.target.value)" autocomplete="one-time-code"
                                maxlength="6" placeholder="000000"
                                x-bind:class="code.length === 0 ? '' : (codeValid ? 'input-valid' : 'input-invalid')" />
                        </div>

                        <p x-show="code && !codeValid" x-cloak
                            class="mt-2 text-xs font-bold text-rose-500 dark:text-rose-300">
                            El código debe tener exactamente 6 dígitos.
                        </p>

                        <p x-show="codeValid" x-cloak
                            class="mt-2 text-xs font-bold text-emerald-600 dark:text-emerald-300">
                            Código listo para verificar.
                        </p>
                    </div>

                    {{-- MODO CÓDIGO DE RECUPERACIÓN --}}
                    <div x-cloak x-show="recovery" x-transition.opacity>
                        <label for="recovery_code"
                            class="mb-2 block text-sm font-bold text-slate-700 dark:text-slate-200">
                            Código de recuperación
                        </label>

                        <div class="relative">
                            <span
                                class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-4 text-slate-400 dark:text-slate-500">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24"
                                    stroke="currentColor" stroke-width="1.8">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                        d="M16.5 10.5V6.75A2.25 2.25 0 0 0 14.25 4.5h-9A2.25 2.25 0 0 0 3 6.75v10.5A2.25 2.25 0 0 0 5.25 19.5h9a2.25 2.25 0 0 0 2.25-2.25V13.5M9 12l2 2 4-4" />
                                </svg>
                            </span>

                            <x-input id="recovery_code"
                                class="twofa-input mt-1 block w-full rounded-2xl border-slate-300 bg-white/95 py-4 pl-11 pr-4 text-base font-bold tracking-[0.08em] text-slate-800 placeholder:font-medium placeholder:tracking-normal placeholder-slate-400 shadow-sm focus:border-sky-500 focus:ring-sky-500 dark:border-slate-600 dark:bg-slate-900/80 dark:text-slate-100 dark:placeholder-slate-500"
                                type="text" name="recovery_code" x-ref="recovery_code" x-model="recovery_code"
                                x-on:input="recovery_code = normalizeRecovery($event.target.value)"
                                autocomplete="one-time-code" maxlength="32"
                                placeholder="Ingresa un código de recuperación"
                                x-bind:class="recovery_code.length === 0 ? '' : (recoveryValid ? 'input-valid' : 'input-invalid')" />
                        </div>

                        <p x-show="recovery_code && !recoveryValid" x-cloak
                            class="mt-2 text-xs font-bold text-rose-500 dark:text-rose-300">
                            El código de recuperación debe tener al menos 6 caracteres.
                        </p>

                        <p x-show="recoveryValid" x-cloak
                            class="mt-2 text-xs font-bold text-emerald-600 dark:text-emerald-300">
                            Código de recuperación listo para verificar.
                        </p>
                    </div>

                    {{-- AVISO --}}
                    <div
                        class="rounded-2xl border border-sky-200 bg-sky-50 px-4 py-4 text-sm leading-6 text-sky-800 dark:border-sky-400/20 dark:bg-sky-400/10 dark:text-sky-100">
                        <div class="flex gap-3">
                            <div
                                class="mt-0.5 flex h-8 w-8 shrink-0 items-center justify-center rounded-xl bg-sky-100 text-sky-700 dark:bg-sky-400/15 dark:text-sky-200">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24"
                                    stroke="currentColor" stroke-width="1.8">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                        d="M12 15v2m-6 4h12a2 2 0 0 0 2-2v-6a2 2 0 0 0-2-2H6a2 2 0 0 0-2 2v6a2 2 0 0 0 2 2Zm10-10V7a4 4 0 0 0-8 0v4" />
                                </svg>
                            </div>

                            <div>
                                <p class="font-bold">
                                    Verificación obligatoria
                                </p>

                                <p class="mt-1" x-show="!recovery">
                                    Usa el código actual generado por tu aplicación autenticadora para continuar.
                                </p>

                                <p class="mt-1" x-cloak x-show="recovery">
                                    Usa un código de recuperación únicamente si no tienes acceso a tu aplicación
                                    autenticadora.
                                </p>
                            </div>
                        </div>
                    </div>

                    {{-- ESTADO --}}
                    <div
                        class="rounded-2xl border border-slate-200 bg-slate-50 px-4 py-4 dark:border-slate-700 dark:bg-slate-900/70">
                        <div class="flex items-center gap-3 text-sm">
                            <span class="flex h-5 w-5 items-center justify-center">
                                <svg x-show="canSubmit" xmlns="http://www.w3.org/2000/svg"
                                    class="h-5 w-5 text-emerald-600 dark:text-emerald-300" viewBox="0 0 20 20"
                                    fill="currentColor">
                                    <path fill-rule="evenodd"
                                        d="M16.704 5.29a1 1 0 0 1 .006 1.414l-7.2 7.26a1 1 0 0 1-1.42.005L3.3 9.173a1 1 0 1 1 1.4-1.428l3.08 3.022 6.5-6.55a1 1 0 0 1 1.424-.006Z"
                                        clip-rule="evenodd" />
                                </svg>

                                <svg x-show="!canSubmit" xmlns="http://www.w3.org/2000/svg"
                                    class="h-5 w-5 text-slate-400" viewBox="0 0 20 20" fill="currentColor">
                                    <path
                                        d="M10 18a8 8 0 1 0 0-16 8 8 0 0 0 0 16Zm3.53-10.97a.75.75 0 1 0-1.06-1.06L10 8.44 7.53 5.97a.75.75 0 1 0-1.06 1.06L8.94 9.5 6.47 11.97a.75.75 0 1 0 1.06 1.06L10 10.56l2.47 2.47a.75.75 0 0 0 1.06-1.06L11.06 9.5l2.47-2.47Z" />
                                </svg>
                            </span>

                            <span x-bind:class="canSubmit
                                ? 'text-emerald-700 dark:text-emerald-300 font-bold'
                                : 'text-slate-600 dark:text-slate-400'">
                                Ingresa un código válido para habilitar la verificación.
                            </span>
                        </div>
                    </div>

                    {{-- BOTONES --}}
                    <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                        <a href="{{ route('login') }}"
                            class="text-center text-sm font-bold text-slate-500 transition hover:text-slate-700 hover:underline dark:text-slate-400 dark:hover:text-slate-200">
                            Volver al login
                        </a>

                        <button type="submit" x-bind:disabled="!canSubmit"
                            x-bind:class="canSubmit
                                ? 'bg-gradient-to-r from-emerald-600 via-emerald-500 to-sky-600 text-white shadow-lg shadow-emerald-500/25 hover:from-emerald-700 hover:via-emerald-600 hover:to-sky-700'
                                : 'bg-slate-200 text-slate-400 cursor-not-allowed dark:bg-slate-800 dark:text-slate-500'"
                            class="twofa-btn flex items-center justify-center gap-2 rounded-2xl px-5 py-4 text-base font-black tracking-wide transition">

                            <svg x-show="isSubmitting" x-cloak xmlns="http://www.w3.org/2000/svg"
                                class="h-4 w-4 animate-spin" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor"
                                    stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 0 1 8-8v4a4 4 0 0 0-4 4H4z">
                                </path>
                            </svg>

                            <span x-text="isSubmitting ? 'Verificando...' : 'Verificar acceso'"></span>

                            <svg x-show="!isSubmitting" xmlns="http://www.w3.org/2000/svg" class="h-4 w-4"
                                viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd"
                                    d="M10.293 15.707a1 1 0 0 1 0-1.414L13.586 11H3a1 1 0 1 1 0-2h10.586l-3.293-3.293a1 1 0 1 1 1.414-1.414l5 5a1 1 0 0 1 0 1.414l-5 5a1 1 0 0 1-1.414 0Z"
                                    clip-rule="evenodd" />
                            </svg>
                        </button>
                    </div>
                </form>

                <div class="mt-7 border-t border-slate-200/80 pt-5 text-center dark:border-slate-700/70">
                    <p class="text-sm leading-6 text-slate-500 dark:text-slate-400">
                        Este paso adicional protege el acceso a tu cuenta institucional.
                    </p>
                </div>
            </div>
        </div>

        <script>
            document.addEventListener('DOMContentLoaded', function () {
                const themeToggle = document.getElementById('twofaThemeToggle');

                function toggleTheme() {
                    if (window.themeManager && typeof window.themeManager.toggle === 'function') {
                        window.themeManager.toggle();
                        return;
                    }

                    const isDark = document.documentElement.classList.toggle('dark');
                    document.documentElement.dataset.theme = isDark ? 'dark' : 'light';
                    localStorage.setItem('savp-theme', isDark ? 'dark' : 'light');

                    window.dispatchEvent(new CustomEvent('theme-changed', {
                        detail: {
                            theme: isDark ? 'dark' : 'light'
                        }
                    }));
                }

                if (themeToggle) {
                    themeToggle.addEventListener('click', toggleTheme);
                }
            });
        </script>
    </x-authentication-card>
</x-guest-layout>