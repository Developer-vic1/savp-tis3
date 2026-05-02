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
            .login-stage {
                position: relative;
                overflow: hidden;
                border-radius: 2.2rem;
                width: min(100%, 46rem);
                min-height: 620px;
                background:
                    radial-gradient(circle at top left, rgba(16, 185, 129, 0.18), transparent 30%),
                    radial-gradient(circle at top right, rgba(14, 165, 233, 0.16), transparent 30%),
                    linear-gradient(135deg, rgba(255, 255, 255, 0.97), rgba(248, 250, 252, 0.97));
                border: 1px solid rgba(226, 232, 240, 0.95);
                box-shadow:
                    0 28px 90px rgba(15, 23, 42, 0.14),
                    0 10px 28px rgba(15, 23, 42, 0.08);
            }

            html.dark .login-stage {
                background:
                    radial-gradient(circle at top left, rgba(52, 211, 153, 0.13), transparent 30%),
                    radial-gradient(circle at top right, rgba(56, 189, 248, 0.12), transparent 30%),
                    linear-gradient(135deg, rgba(15, 23, 42, 0.97), rgba(30, 41, 59, 0.97));
                border-color: rgba(71, 85, 105, 0.86);
                box-shadow:
                    0 28px 95px rgba(0, 0, 0, 0.40),
                    0 10px 28px rgba(0, 0, 0, 0.24);
            }

            .login-stage::before {
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

            html.dark .login-stage::before {
                background-image:
                    linear-gradient(rgba(148, 163, 184, 0.055) 1px, transparent 1px),
                    linear-gradient(90deg, rgba(148, 163, 184, 0.055) 1px, transparent 1px);
            }

            .login-back-btn {
                position: absolute;
                left: 1.25rem;
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

            html.dark .login-back-btn {
                background: rgba(15, 23, 42, .78);
                color: #cbd5e1;
                border-color: rgba(71, 85, 105, .86);
            }

            .login-back-btn:hover {
                transform: translateY(-2px);
                color: #059669;
                border-color: rgba(16, 185, 129, .48);
            }

            .login-theme-btn {
                position: absolute;
                right: 1.25rem;
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

            html.dark .login-theme-btn {
                background: rgba(15, 23, 42, .78);
                color: #cbd5e1;
                border-color: rgba(71, 85, 105, .86);
            }

            .login-theme-btn:hover {
                transform: translateY(-2px);
                color: #0284c7;
                border-color: rgba(14, 165, 233, .48);
            }

            .login-input {
                transition: all .22s ease;
            }

            .login-input:focus {
                transform: translateY(-1px);
                box-shadow: 0 0 0 4px rgba(16, 185, 129, 0.13);
            }

            .login-input.input-valid {
                border-color: rgb(16 185 129) !important;
            }

            .login-input.input-invalid {
                border-color: rgb(248 113 113) !important;
            }

            .login-btn {
                transition: all .22s ease;
            }

            .login-btn:hover {
                transform: translateY(-1px) scale(1.01);
                box-shadow: 0 18px 38px rgba(16, 185, 129, 0.24);
            }

            .login-btn:disabled {
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

        <div class="login-stage px-7 pb-8 pt-20 sm:px-10 sm:pb-10 sm:pt-22">
            {{-- Botón volver al welcome --}}
            <a href="{{ url('/') }}" class="login-back-btn" title="Volver al inicio" aria-label="Volver al inicio">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24"
                    stroke="currentColor" stroke-width="1.8">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5 3 12m0 0 7.5-7.5M3 12h18" />
                </svg>
            </a>

            {{-- Botón tema --}}
            <button type="button" id="loginThemeToggle" class="login-theme-btn" title="Cambiar tema"
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
                <div class="fade-up">
                    <div
                        class="mx-auto mb-4 inline-flex items-center gap-2 rounded-full border border-emerald-200 bg-white/80 px-3 py-1 text-[11px] font-black uppercase tracking-[0.18em] text-emerald-700 shadow-sm dark:border-emerald-400/20 dark:bg-emerald-400/10 dark:text-emerald-300">
                        <span class="h-2 w-2 rounded-full bg-emerald-500 animate-pulse"></span>
                        Acceso institucional
                    </div>

                    <h1 class="text-center text-4xl font-black tracking-tight text-slate-950 dark:text-white">
                        Bienvenido
                    </h1>

                    <p class="mx-auto mt-3 max-w-lg text-center text-base leading-7 text-slate-600 dark:text-slate-300">
                        Inicia sesión para acceder al sistema de la
                        <span class="font-bold text-emerald-700 dark:text-emerald-300">
                            Unidad Educativa Franz Tamayo N°3
                        </span>.
                    </p>
                </div>

                <div class="fade-up delay-2 mt-7">
                    <x-validation-errors
                        class="mb-4 hidden rounded-2xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-700 dark:border-rose-400/20 dark:bg-rose-400/10 dark:text-rose-200" />

                    @session('status')
                        <script>
                            document.addEventListener('DOMContentLoaded', function () {
                                Swal.fire({
                                    icon: 'success',
                                    title: 'Proceso completado',
                                    text: @json($value),
                                    confirmButtonText: 'Entendido',
                                    confirmButtonColor: '#059669'
                                });
                            });
                        </script>
                    @endsession

                    @if ($errors->any())
                        <script>
                            document.addEventListener('DOMContentLoaded', function () {
                                Swal.fire({
                                    icon: 'error',
                                    title: 'No se pudo iniciar sesión',
                                    text: @json($errors->first()),
                                    confirmButtonText: 'Entendido',
                                    confirmButtonColor: '#dc2626'
                                });
                            });
                        </script>
                    @endif
                </div>

                <form method="POST" action="{{ route('login') }}" class="fade-up delay-3 space-y-6" novalidate
                    id="loginForm">
                    @csrf

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
                                class="login-input mt-1 block w-full rounded-2xl border-slate-300 bg-white/95 py-4 pl-11 pr-4 text-base text-slate-800 placeholder-slate-400 shadow-sm focus:border-emerald-500 focus:ring-emerald-500 dark:border-slate-600 dark:bg-slate-900/80 dark:text-slate-100 dark:placeholder-slate-500"
                                type="email" name="email" value="" required autofocus autocomplete="username"
                                inputmode="email" maxlength="120" placeholder="usuario@gmail.com" />
                        </div>

                        <p id="emailError" class="mt-2 hidden text-xs font-bold text-rose-500 dark:text-rose-300">
                            Ingresa un correo válido, por ejemplo: usuario@gmail.com
                        </p>
                    </div>

                    <div>
                        <label for="password" class="mb-2 block text-sm font-bold text-slate-700 dark:text-slate-200">
                            Contraseña
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
                                class="login-input mt-1 block w-full rounded-2xl border-slate-300 bg-white/95 py-4 pl-11 pr-12 text-base text-slate-800 placeholder-slate-400 shadow-sm focus:border-emerald-500 focus:ring-emerald-500 dark:border-slate-600 dark:bg-slate-900/80 dark:text-slate-100 dark:placeholder-slate-500"
                                type="password" name="password" required autocomplete="current-password"
                                placeholder="Ingresa tu contraseña" />

                            <button type="button" id="togglePassword"
                                class="absolute inset-y-0 right-0 flex items-center pr-4 text-slate-400 transition hover:text-emerald-600 dark:text-slate-500 dark:hover:text-emerald-300"
                                aria-label="Mostrar u ocultar contraseña">
                                <svg id="eyeOpen" xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20"
                                    fill="currentColor">
                                    <path
                                        d="M10 3c4.5 0 8.27 2.94 9.54 7-.68 2.16-2.02 4-3.83 5.32A9.96 9.96 0 0110 17c-4.5 0-8.27-2.94-9.54-7C1.73 5.94 5.5 3 10 3zm0 3.5A3.5 3.5 0 1013.5 10 3.5 3.5 0 0010 6.5z" />
                                </svg>

                                <svg id="eyeClosed" xmlns="http://www.w3.org/2000/svg" class="hidden h-5 w-5"
                                    viewBox="0 0 20 20" fill="currentColor">
                                    <path
                                        d="M3.28 2.22a.75.75 0 10-1.06 1.06l1.65 1.65A10.94 10.94 0 00.46 10 10.94 10.94 0 002 12.55a10.52 10.52 0 003.12 2.88 10.85 10.85 0 004.88 1.12c1.37 0 2.68-.24 3.9-.68l2.82 2.82a.75.75 0 001.06-1.06L3.28 2.22zM10 6.5c.54 0 1.04.12 1.49.33l-4.66 4.66A3.5 3.5 0 0110 6.5zM10 14a3.5 3.5 0 01-2.02-.64l4.88-4.88A3.5 3.5 0 0110 14z" />
                                </svg>
                            </button>
                        </div>
                    </div>

                    <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                        <label for="remember_me"
                            class="flex items-center text-sm font-medium text-slate-600 dark:text-slate-300">
                            <x-checkbox id="remember_me" name="remember"
                                class="rounded border-slate-300 text-emerald-600 shadow-sm focus:ring-emerald-500 dark:border-slate-600 dark:bg-slate-900" />
                            <span class="ms-2">Recordarme</span>
                        </label>

                        @if (Route::has('password.request'))
                            <a class="text-sm font-bold text-emerald-700 transition hover:text-emerald-800 hover:underline dark:text-emerald-300 dark:hover:text-emerald-200"
                                href="{{ route('password.request') }}">
                                ¿Olvidaste tu contraseña?
                            </a>
                        @endif
                    </div>

                    <div class="fade-up delay-4 pt-1">
                        <x-button id="loginBtn"
                            class="group login-btn flex w-full items-center justify-center gap-2 rounded-2xl border-0 bg-gradient-to-r from-emerald-600 via-emerald-500 to-sky-600 px-5 py-4 text-base font-black tracking-wide text-white shadow-lg shadow-emerald-500/25 transition hover:from-emerald-700 hover:via-emerald-600 hover:to-sky-700 disabled:cursor-not-allowed disabled:opacity-50"
                            disabled>
                            <span>Ingresar al sistema</span>

                            <svg xmlns="http://www.w3.org/2000/svg"
                                class="h-4 w-4 transition group-hover:translate-x-0.5" viewBox="0 0 20 20"
                                fill="currentColor">
                                <path fill-rule="evenodd"
                                    d="M10.293 15.707a1 1 0 010-1.414L13.586 11H3a1 1 0 110-2h10.586l-3.293-3.293a1 1 0 111.414-1.414l5 5a1 1 0 010 1.414l-5 5a1 1 0 01-1.414 0z"
                                    clip-rule="evenodd" />
                            </svg>
                        </x-button>
                    </div>
                </form>

                <div class="mt-7 border-t border-slate-200/80 pt-5 text-center dark:border-slate-700/70">
                    <p class="text-sm leading-6 text-slate-500 dark:text-slate-400">
                        Acceso exclusivo para usuarios autorizados del sistema institucional.
                    </p>
                </div>
            </div>
        </div>

        <script>
            document.addEventListener('DOMContentLoaded', function () {
                const passwordInput = document.getElementById('password');
                const togglePassword = document.getElementById('togglePassword');
                const eyeOpen = document.getElementById('eyeOpen');
                const eyeClosed = document.getElementById('eyeClosed');

                const emailInput = document.getElementById('email');
                const emailError = document.getElementById('emailError');
                const loginBtn = document.getElementById('loginBtn');
                const loginForm = document.getElementById('loginForm');
                const themeToggle = document.getElementById('loginThemeToggle');

                function validateEmail(email) {
                    return /^[a-zA-Z0-9._%+-]+@gmail\.com$/.test(email);
                }

                function updateFormState() {
                    if (!emailInput || !passwordInput || !loginBtn || !emailError) return;

                    const emailValue = emailInput.value.trim();
                    const passwordValue = passwordInput.value.trim();

                    const isEmailValid = validateEmail(emailValue);
                    const hasPassword = passwordValue.length > 0;

                    emailInput.classList.remove('input-valid', 'input-invalid');

                    if (emailValue.length === 0) {
                        emailError.classList.add('hidden');
                    } else if (isEmailValid) {
                        emailInput.classList.add('input-valid');
                        emailError.classList.add('hidden');
                    } else {
                        emailInput.classList.add('input-invalid');
                        emailError.classList.remove('hidden');
                    }

                    loginBtn.disabled = !(isEmailValid && hasPassword);
                }

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

                if (togglePassword && passwordInput) {
                    togglePassword.addEventListener('click', function () {
                        const isPassword = passwordInput.getAttribute('type') === 'password';

                        passwordInput.setAttribute('type', isPassword ? 'text' : 'password');

                        if (eyeOpen && eyeClosed) {
                            eyeOpen.classList.toggle('hidden');
                            eyeClosed.classList.toggle('hidden');
                        }
                    });
                }

                if (emailInput) {
                    emailInput.addEventListener('input', updateFormState);
                    emailInput.addEventListener('blur', updateFormState);
                }

                if (passwordInput) {
                    passwordInput.addEventListener('input', updateFormState);
                    passwordInput.addEventListener('blur', updateFormState);
                }

                updateFormState();

                @if ($errors->any())
                    if (emailInput) emailInput.value = '';
                    if (passwordInput) passwordInput.value = '';
                    updateFormState();
                @endif

                if (loginForm && loginBtn) {
                    loginForm.addEventListener('submit', function (event) {
                        const emailValue = emailInput ? emailInput.value.trim() : '';
                        const passwordValue = passwordInput ? passwordInput.value.trim() : '';

                        const isEmailValid = validateEmail(emailValue);
                        const hasPassword = passwordValue.length > 0;

                        if (!isEmailValid || !hasPassword) {
                            event.preventDefault();
                            updateFormState();
                            return;
                        }

                        loginBtn.disabled = true;
                        loginBtn.setAttribute('aria-disabled', 'true');
                    });
                }
            });
        </script>
    </x-authentication-card>
</x-guest-layout>