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
            .reset-stage {
                position: relative;
                overflow: hidden;
                width: min(100%, 46rem);
                min-height: 650px;
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

            html.dark .reset-stage {
                background:
                    radial-gradient(circle at top left, rgba(52, 211, 153, 0.13), transparent 30%),
                    radial-gradient(circle at top right, rgba(56, 189, 248, 0.12), transparent 30%),
                    linear-gradient(135deg, rgba(15, 23, 42, 0.97), rgba(30, 41, 59, 0.97));
                border-color: rgba(71, 85, 105, 0.86);
                box-shadow:
                    0 28px 95px rgba(0, 0, 0, 0.40),
                    0 10px 28px rgba(0, 0, 0, 0.24);
            }

            .reset-stage::before {
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

            html.dark .reset-stage::before {
                background-image:
                    linear-gradient(rgba(148, 163, 184, 0.055) 1px, transparent 1px),
                    linear-gradient(90deg, rgba(148, 163, 184, 0.055) 1px, transparent 1px);
            }

            .reset-back-btn,
            .reset-theme-btn {
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

            .reset-back-btn {
                left: 1.25rem;
            }

            .reset-theme-btn {
                right: 1.25rem;
            }

            html.dark .reset-back-btn,
            html.dark .reset-theme-btn {
                background: rgba(15, 23, 42, .78);
                color: #cbd5e1;
                border-color: rgba(71, 85, 105, .86);
            }

            .reset-back-btn:hover {
                transform: translateY(-2px);
                color: #059669;
                border-color: rgba(16, 185, 129, .48);
            }

            .reset-theme-btn:hover {
                transform: translateY(-2px);
                color: #0284c7;
                border-color: rgba(14, 165, 233, .48);
            }

            .reset-input {
                transition: all .22s ease;
            }

            .reset-input:focus {
                transform: translateY(-1px);
                box-shadow: 0 0 0 4px rgba(16, 185, 129, 0.13);
            }

            .reset-input.input-valid {
                border-color: rgb(16 185 129) !important;
            }

            .reset-input.input-invalid {
                border-color: rgb(248 113 113) !important;
            }

            .reset-btn {
                transition: all .22s ease;
            }

            .reset-btn:hover {
                transform: translateY(-1px) scale(1.01);
                box-shadow: 0 18px 38px rgba(16, 185, 129, 0.24);
            }

            .reset-btn:disabled {
                cursor: not-allowed;
                opacity: .55;
                box-shadow: none;
                transform: none !important;
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

            .fade-up.delay-4 {
                animation-delay: .32s;
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
                return this.validateEmail((this.email ?? '').trim());
            },

            get hasMinLength() {
                return this.password.length >= 8;
            },

            get hasUppercase() {
                return /[A-Z]/.test(this.password);
            },

            get hasLowercase() {
                return /[a-z]/.test(this.password);
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

            get passwordStrength() {
                let score = 0;

                if (this.hasMinLength) score++;
                if (this.hasUppercase) score++;
                if (this.hasLowercase) score++;
                if (this.hasNumber) score++;
                if (this.hasSpecial) score++;
                if (this.password.length >= 12) score++;

                return score;
            },

            get strengthLabel() {
                if (this.password.length === 0) return 'Sin evaluar';
                if (this.passwordStrength <= 2) return 'Débil';
                if (this.passwordStrength <= 4) return 'Aceptable';
                return 'Segura';
            },

            get strengthWidth() {
                if (this.password.length === 0) return '0%';
                if (this.passwordStrength <= 2) return '33%';
                if (this.passwordStrength <= 4) return '66%';
                return '100%';
            },

            get strengthColor() {
                if (this.password.length === 0) return '#94a3b8';
                if (this.passwordStrength <= 2) return '#dc2626';
                if (this.passwordStrength <= 4) return '#f59e0b';
                return '#059669';
            },

            get canSubmit() {
                return this.emailValid
                    && this.hasMinLength
                    && this.hasUppercase
                    && this.hasLowercase
                    && this.hasNumber
                    && this.hasSpecial
                    && this.passwordsMatch
                    && !this.isSubmitting;
            },

            submitForm(event) {
                if (!this.canSubmit) {
                    event.preventDefault();
                    return;
                }

                this.isSubmitting = true;
                event.target.submit();
            }
        }" class="reset-stage px-7 pb-8 pt-20 sm:px-10 sm:pb-10">

            {{-- Botón volver al welcome --}}
            <a href="{{ url('/') }}" class="reset-back-btn" title="Volver al inicio" aria-label="Volver al inicio">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24"
                    stroke="currentColor" stroke-width="1.8">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5 3 12m0 0 7.5-7.5M3 12h18" />
                </svg>
            </a>

            {{-- Botón tema --}}
            <button type="button" id="resetThemeToggle" class="reset-theme-btn" title="Cambiar tema"
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
                        Restablecimiento seguro
                    </div>

                    <h1 class="text-4xl font-black tracking-tight text-slate-950 dark:text-white">
                        Nueva contraseña
                    </h1>

                    <p class="mx-auto mt-3 max-w-lg text-base leading-7 text-slate-600 dark:text-slate-300">
                        Define una nueva contraseña segura para recuperar el acceso a tu cuenta institucional.
                    </p>
                </div>

                <div class="fade-up delay-1 mt-7">
                    <x-validation-errors
                        class="mb-4 hidden rounded-2xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-700 dark:border-rose-400/20 dark:bg-rose-400/10 dark:text-rose-200" />

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

                <form method="POST" action="{{ route('password.update') }}" class="fade-up delay-2 space-y-6"
                    id="resetForm" @submit.prevent="submitForm($event)" novalidate>
                    @csrf

                    <input type="hidden" name="token" value="{{ $request->route('token') }}">

                    {{-- CORREO --}}
                    <div>
                        <label for="email" class="mb-2 block text-sm font-bold text-slate-700 dark:text-slate-200">
                            Correo electrónico
                        </label>

                        <div class="relative">
                            <span
                                class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-4 text-slate-400 dark:text-slate-500">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20"
                                    fill="currentColor">
                                    <path d="M2.94 5.5A2 2 0 014.75 4h10.5a2 2 0 011.81 1.5L10 9.88 2.94 5.5z" />
                                    <path
                                        d="M18 8.12l-7.45 4.16a1 1 0 01-1.1 0L2 8.12V14a2 2 0 002 2h12a2 2 0 002-2V8.12z" />
                                </svg>
                            </span>

                            <x-input id="email"
                                class="reset-input mt-1 block w-full rounded-2xl border-slate-300 bg-white/95 py-4 pl-11 pr-4 text-base text-slate-800 placeholder-slate-400 shadow-sm focus:border-emerald-500 focus:ring-emerald-500 dark:border-slate-600 dark:bg-slate-900/80 dark:text-slate-100 dark:placeholder-slate-500"
                                type="email" name="email" :value="old('email', $request->email)" x-model="email"
                                required autofocus autocomplete="username" inputmode="email" maxlength="120"
                                placeholder="usuario@gmail.com"
                                x-bind:class="email.length === 0 ? '' : (emailValid ? 'input-valid' : 'input-invalid')" />
                        </div>

                        <p x-show="email && !emailValid" x-cloak
                            class="mt-2 text-xs font-bold text-rose-500 dark:text-rose-300">
                            Ingresa un correo válido con dominio gmail.com, por ejemplo: usuario@gmail.com
                        </p>

                        <p x-show="emailValid" x-cloak
                            class="mt-2 text-xs font-bold text-emerald-600 dark:text-emerald-300">
                            Correo válido para continuar.
                        </p>
                    </div>

                    {{-- NUEVA CONTRASEÑA --}}
                    <div>
                        <label for="password" class="mb-2 block text-sm font-bold text-slate-700 dark:text-slate-200">
                            Nueva contraseña
                        </label>

                        <div class="relative">
                            <span
                                class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-4 text-slate-400 dark:text-slate-500">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20"
                                    fill="currentColor">
                                    <path fill-rule="evenodd"
                                        d="M5 8a5 5 0 1110 0v1h.5A1.5 1.5 0 0117 10.5v5A1.5 1.5 0 0115.5 17h-11A1.5 1.5 0 013 15.5v-5A1.5 1.5 0 014.5 9H5V8zm2 1h6V8a3 3 0 10-6 0v1z"
                                        clip-rule="evenodd" />
                                </svg>
                            </span>

                            <x-input id="password"
                                class="reset-input mt-1 block w-full rounded-2xl border-slate-300 bg-white/95 py-4 pl-11 pr-12 text-base text-slate-800 placeholder-slate-400 shadow-sm focus:border-emerald-500 focus:ring-emerald-500 dark:border-slate-600 dark:bg-slate-900/80 dark:text-slate-100 dark:placeholder-slate-500"
                                x-bind:type="showPassword ? 'text' : 'password'" name="password" x-model="password"
                                required autocomplete="new-password" placeholder="Ingresa tu nueva contraseña"
                                x-bind:class="password.length === 0 ? '' : ((hasMinLength && hasUppercase && hasLowercase && hasNumber && hasSpecial) ? 'input-valid' : 'input-invalid')" />

                            <button type="button" @click="showPassword = !showPassword"
                                class="absolute inset-y-0 right-0 flex items-center pr-4 text-slate-400 transition hover:text-emerald-600 dark:text-slate-500 dark:hover:text-emerald-300"
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

                        {{-- Barra de seguridad --}}
                        <div class="mt-3">
                            <div class="flex items-center justify-between text-xs font-bold">
                                <span class="text-slate-500 dark:text-slate-400">
                                    Seguridad de contraseña
                                </span>
                                <span x-text="strengthLabel" x-bind:style="'color:' + strengthColor"></span>
                            </div>

                            <div class="mt-2 h-2 overflow-hidden rounded-full bg-slate-200 dark:bg-slate-700">
                                <div class="h-full rounded-full transition-all duration-300"
                                    x-bind:style="'width:' + strengthWidth + '; background:' + strengthColor"></div>
                            </div>
                        </div>
                    </div>

                    {{-- CONFIRMAR CONTRASEÑA --}}
                    <div>
                        <label for="password_confirmation"
                            class="mb-2 block text-sm font-bold text-slate-700 dark:text-slate-200">
                            Confirmar nueva contraseña
                        </label>

                        <div class="relative">
                            <span
                                class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-4 text-slate-400 dark:text-slate-500">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20"
                                    fill="currentColor">
                                    <path fill-rule="evenodd"
                                        d="M5 8a5 5 0 1110 0v1h.5A1.5 1.5 0 0117 10.5v5A1.5 1.5 0 0115.5 17h-11A1.5 1.5 0 013 15.5v-5A1.5 1.5 0 014.5 9H5V8zm2 1h6V8a3 3 0 10-6 0v1z"
                                        clip-rule="evenodd" />
                                </svg>
                            </span>

                            <x-input id="password_confirmation"
                                class="reset-input mt-1 block w-full rounded-2xl border-slate-300 bg-white/95 py-4 pl-11 pr-12 text-base text-slate-800 placeholder-slate-400 shadow-sm focus:border-emerald-500 focus:ring-emerald-500 dark:border-slate-600 dark:bg-slate-900/80 dark:text-slate-100 dark:placeholder-slate-500"
                                x-bind:type="showConfirm ? 'text' : 'password'" name="password_confirmation"
                                x-model="password_confirmation" required autocomplete="new-password"
                                placeholder="Confirma tu nueva contraseña"
                                x-bind:class="password_confirmation.length === 0 ? '' : (passwordsMatch ? 'input-valid' : 'input-invalid')" />

                            <button type="button" @click="showConfirm = !showConfirm"
                                class="absolute inset-y-0 right-0 flex items-center pr-4 text-slate-400 transition hover:text-emerald-600 dark:text-slate-500 dark:hover:text-emerald-300"
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

                        <p x-show="password_confirmation && !passwordsMatch" x-cloak
                            class="mt-2 text-xs font-bold text-rose-500 dark:text-rose-300">
                            Las contraseñas no coinciden.
                        </p>

                        <p x-show="passwordsMatch" x-cloak
                            class="mt-2 text-xs font-bold text-emerald-600 dark:text-emerald-300">
                            Las contraseñas coinciden.
                        </p>
                    </div>

                    {{-- REQUISITOS --}}
                    <div
                        class="rounded-2xl border border-slate-200 bg-slate-50 px-4 py-4 dark:border-slate-700 dark:bg-slate-900/70">
                        <p class="text-sm font-bold text-slate-800 dark:text-slate-100">
                            Requisitos de seguridad
                        </p>

                        <div class="mt-3 grid gap-2 text-sm sm:grid-cols-2">
                            <div class="flex items-center gap-2">
                                <span x-text="hasMinLength ? '✓' : '•'"
                                    :class="hasMinLength ? 'text-emerald-600 dark:text-emerald-300' : 'text-slate-400'"></span>
                                <span
                                    :class="hasMinLength ? 'text-emerald-700 dark:text-emerald-300' : 'text-slate-600 dark:text-slate-400'">
                                    Mínimo 8 caracteres
                                </span>
                            </div>

                            <div class="flex items-center gap-2">
                                <span x-text="hasUppercase ? '✓' : '•'"
                                    :class="hasUppercase ? 'text-emerald-600 dark:text-emerald-300' : 'text-slate-400'"></span>
                                <span
                                    :class="hasUppercase ? 'text-emerald-700 dark:text-emerald-300' : 'text-slate-600 dark:text-slate-400'">
                                    Una letra mayúscula
                                </span>
                            </div>

                            <div class="flex items-center gap-2">
                                <span x-text="hasLowercase ? '✓' : '•'"
                                    :class="hasLowercase ? 'text-emerald-600 dark:text-emerald-300' : 'text-slate-400'"></span>
                                <span
                                    :class="hasLowercase ? 'text-emerald-700 dark:text-emerald-300' : 'text-slate-600 dark:text-slate-400'">
                                    Una letra minúscula
                                </span>
                            </div>

                            <div class="flex items-center gap-2">
                                <span x-text="hasNumber ? '✓' : '•'"
                                    :class="hasNumber ? 'text-emerald-600 dark:text-emerald-300' : 'text-slate-400'"></span>
                                <span
                                    :class="hasNumber ? 'text-emerald-700 dark:text-emerald-300' : 'text-slate-600 dark:text-slate-400'">
                                    Al menos un número
                                </span>
                            </div>

                            <div class="flex items-center gap-2">
                                <span x-text="hasSpecial ? '✓' : '•'"
                                    :class="hasSpecial ? 'text-emerald-600 dark:text-emerald-300' : 'text-slate-400'"></span>
                                <span
                                    :class="hasSpecial ? 'text-emerald-700 dark:text-emerald-300' : 'text-slate-600 dark:text-slate-400'">
                                    Un símbolo especial
                                </span>
                            </div>

                            <div class="flex items-center gap-2">
                                <span x-text="passwordsMatch ? '✓' : '•'"
                                    :class="passwordsMatch ? 'text-emerald-600 dark:text-emerald-300' : 'text-slate-400'"></span>
                                <span
                                    :class="passwordsMatch ? 'text-emerald-700 dark:text-emerald-300' : 'text-slate-600 dark:text-slate-400'">
                                    Confirmación correcta
                                </span>
                            </div>
                        </div>
                    </div>

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
                                    Importante
                                </p>
                                <p class="mt-1">
                                    Una vez guardada, deberás iniciar sesión usando tu nueva contraseña.
                                </p>
                            </div>
                        </div>
                    </div>

                    <div class="fade-up delay-3 pt-1">
                        <button type="submit" x-bind:disabled="!canSubmit"
                            x-bind:class="canSubmit
                                ? 'bg-gradient-to-r from-emerald-600 via-emerald-500 to-sky-600 text-white shadow-lg shadow-emerald-500/25 hover:from-emerald-700 hover:via-emerald-600 hover:to-sky-700'
                                : 'bg-slate-200 text-slate-400 cursor-not-allowed dark:bg-slate-800 dark:text-slate-500'"
                            class="reset-btn flex w-full items-center justify-center gap-2 rounded-2xl px-5 py-4 text-base font-black tracking-wide transition">

                            <svg x-show="isSubmitting" x-cloak xmlns="http://www.w3.org/2000/svg"
                                class="h-4 w-4 animate-spin" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor"
                                    stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z">
                                </path>
                            </svg>

                            <span
                                x-text="isSubmitting ? 'Actualizando contraseña...' : 'Restablecer contraseña'"></span>
                        </button>
                    </div>
                </form>

                <div class="mt-7 border-t border-slate-200/80 pt-5 text-center dark:border-slate-700/70">
                    <p class="text-sm leading-6 text-slate-500 dark:text-slate-400">
                        ¿Ya tienes acceso?
                        <a href="{{ route('login') }}"
                            class="font-black text-emerald-700 transition hover:text-emerald-800 hover:underline dark:text-emerald-300 dark:hover:text-emerald-200">
                            Volver al inicio de sesión
                        </a>
                    </p>
                </div>
            </div>
        </div>

        <script>
            document.addEventListener('DOMContentLoaded', function () {
                const themeToggle = document.getElementById('resetThemeToggle');

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