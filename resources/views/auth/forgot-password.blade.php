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
            .forgot-stage {
                position: relative;
                overflow: hidden;
                width: min(100%, 46rem);
                min-height: 560px;
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

            html.dark .forgot-stage {
                background:
                    radial-gradient(circle at top left, rgba(52, 211, 153, 0.13), transparent 30%),
                    radial-gradient(circle at top right, rgba(56, 189, 248, 0.12), transparent 30%),
                    linear-gradient(135deg, rgba(15, 23, 42, 0.97), rgba(30, 41, 59, 0.97));
                border-color: rgba(71, 85, 105, 0.86);
                box-shadow:
                    0 28px 95px rgba(0, 0, 0, 0.40),
                    0 10px 28px rgba(0, 0, 0, 0.24);
            }

            .forgot-stage::before {
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

            html.dark .forgot-stage::before {
                background-image:
                    linear-gradient(rgba(148, 163, 184, 0.055) 1px, transparent 1px),
                    linear-gradient(90deg, rgba(148, 163, 184, 0.055) 1px, transparent 1px);
            }

            .forgot-back-btn,
            .forgot-theme-btn {
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

            .forgot-back-btn {
                left: 1.25rem;
            }

            .forgot-theme-btn {
                right: 1.25rem;
            }

            html.dark .forgot-back-btn,
            html.dark .forgot-theme-btn {
                background: rgba(15, 23, 42, .78);
                color: #cbd5e1;
                border-color: rgba(71, 85, 105, .86);
            }

            .forgot-back-btn:hover {
                transform: translateY(-2px);
                color: #059669;
                border-color: rgba(16, 185, 129, .48);
            }

            .forgot-theme-btn:hover {
                transform: translateY(-2px);
                color: #0284c7;
                border-color: rgba(14, 165, 233, .48);
            }

            .forgot-input {
                transition: all .22s ease;
            }

            .forgot-input:focus {
                transform: translateY(-1px);
                box-shadow: 0 0 0 4px rgba(16, 185, 129, 0.13);
            }

            .forgot-input.input-valid {
                border-color: rgb(16 185 129) !important;
            }

            .forgot-input.input-invalid {
                border-color: rgb(248 113 113) !important;
            }

            .forgot-btn {
                transition: all .22s ease;
            }

            .forgot-btn:hover {
                transform: translateY(-1px) scale(1.01);
                box-shadow: 0 18px 38px rgba(16, 185, 129, 0.24);
            }

            .forgot-btn:disabled {
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

        <div class="forgot-stage px-7 pb-8 pt-20 sm:px-10 sm:pb-10">
            {{-- Botón volver al welcome --}}
            <a href="{{ url('/') }}" class="forgot-back-btn" title="Volver al inicio" aria-label="Volver al inicio">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24"
                    stroke="currentColor" stroke-width="1.8">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5 3 12m0 0 7.5-7.5M3 12h18" />
                </svg>
            </a>

            {{-- Botón tema --}}
            <button type="button" id="forgotThemeToggle" class="forgot-theme-btn" title="Cambiar tema"
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
                        Recuperación de acceso
                    </div>

                    <h1 class="text-4xl font-black tracking-tight text-slate-950 dark:text-white">
                        ¿Olvidaste tu contraseña?
                    </h1>

                    <p class="mx-auto mt-3 max-w-lg text-base leading-7 text-slate-600 dark:text-slate-300">
                        Ingresa tu correo institucional registrado y te enviaremos un enlace seguro para
                        restablecer tu contraseña.
                    </p>
                </div>

                <div class="fade-up delay-1 mt-7">
                    <x-validation-errors
                        class="mb-4 hidden rounded-2xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-700 dark:border-rose-400/20 dark:bg-rose-400/10 dark:text-rose-200" />

                    @session('status')
                        <script>
                            document.addEventListener('DOMContentLoaded', function () {
                                Swal.fire({
                                    icon: 'success',
                                    title: 'Enlace enviado',
                                    text: 'Si el correo está registrado, recibirás instrucciones para restablecer tu contraseña.',
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
                                    title: 'No se pudo enviar el enlace',
                                    text: @json($errors->first()),
                                    confirmButtonText: 'Entendido',
                                    confirmButtonColor: '#dc2626'
                                });
                            });
                        </script>
                    @endif
                </div>

                <form method="POST" action="{{ route('password.email') }}" class="fade-up delay-2 space-y-6"
                    id="forgotForm" novalidate>
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
                                class="forgot-input mt-1 block w-full rounded-2xl border-slate-300 bg-white/95 py-4 pl-11 pr-4 text-base text-slate-800 placeholder-slate-400 shadow-sm focus:border-emerald-500 focus:ring-emerald-500 dark:border-slate-600 dark:bg-slate-900/80 dark:text-slate-100 dark:placeholder-slate-500"
                                type="email" name="email" value="" required autofocus autocomplete="username"
                                inputmode="email" maxlength="120" placeholder="usuario@gmail.com" />
                        </div>

                        <p id="emailError" class="mt-2 hidden text-xs font-bold text-rose-500 dark:text-rose-300">
                            Ingresa un correo válido con dominio gmail.com, por ejemplo: usuario@gmail.com
                        </p>

                        <p id="emailOk" class="mt-2 hidden text-xs font-bold text-emerald-600 dark:text-emerald-300">
                            Correo válido. Puedes enviar el enlace de recuperación.
                        </p>
                    </div>

                    <div
                        class="rounded-2xl border border-sky-200 bg-sky-50 px-4 py-4 text-sm leading-6 text-sky-800 dark:border-sky-400/20 dark:bg-sky-400/10 dark:text-sky-100">
                        <div class="flex gap-3">
                            <div
                                class="mt-0.5 flex h-8 w-8 shrink-0 items-center justify-center rounded-xl bg-sky-100 text-sky-700 dark:bg-sky-400/15 dark:text-sky-200">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24"
                                    stroke="currentColor" stroke-width="1.8">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                        d="M12 6v6l4 2m6-2a10 10 0 1 1-20 0 10 10 0 0 1 20 0Z" />
                                </svg>
                            </div>

                            <div>
                                <p class="font-bold">
                                    Revisa tu correo después del envío
                                </p>
                                <p class="mt-1">
                                    Si la cuenta existe, el enlace llegará al correo registrado. También revisa spam o
                                    correo no deseado.
                                </p>
                            </div>
                        </div>
                    </div>

                    <div class="fade-up delay-3 pt-1">
                        <x-button id="forgotBtn"
                            class="group forgot-btn flex w-full items-center justify-center gap-2 rounded-2xl border-0 bg-gradient-to-r from-emerald-600 via-emerald-500 to-sky-600 px-5 py-4 text-base font-black tracking-wide text-white shadow-lg shadow-emerald-500/25 transition hover:from-emerald-700 hover:via-emerald-600 hover:to-sky-700 disabled:cursor-not-allowed disabled:opacity-50"
                            disabled>
                            <span>Enviar enlace de recuperación</span>

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
                        ¿Recordaste tu contraseña?
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
                const emailInput = document.getElementById('email');
                const emailError = document.getElementById('emailError');
                const emailOk = document.getElementById('emailOk');
                const forgotBtn = document.getElementById('forgotBtn');
                const forgotForm = document.getElementById('forgotForm');
                const themeToggle = document.getElementById('forgotThemeToggle');

                function validateEmail(email) {
                    return /^[a-zA-Z0-9._%+-]+@gmail\.com$/.test(email);
                }

                function updateFormState() {
                    if (!emailInput || !forgotBtn || !emailError || !emailOk) return;

                    const emailValue = emailInput.value.trim();
                    const isValid = validateEmail(emailValue);

                    emailInput.classList.remove('input-valid', 'input-invalid');

                    if (emailValue.length === 0) {
                        emailError.classList.add('hidden');
                        emailOk.classList.add('hidden');
                        forgotBtn.disabled = true;
                        return;
                    }

                    if (isValid) {
                        emailInput.classList.add('input-valid');
                        emailError.classList.add('hidden');
                        emailOk.classList.remove('hidden');
                        forgotBtn.disabled = false;
                    } else {
                        emailInput.classList.add('input-invalid');
                        emailError.classList.remove('hidden');
                        emailOk.classList.add('hidden');
                        forgotBtn.disabled = true;
                    }
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

                if (emailInput) {
                    emailInput.addEventListener('input', updateFormState);
                    emailInput.addEventListener('blur', updateFormState);
                    updateFormState();
                }

                @if ($errors->any())
                    if (emailInput) emailInput.value = '';
                    updateFormState();
                @endif

                if (forgotForm && forgotBtn) {
                    forgotForm.addEventListener('submit', function (event) {
                        const emailValue = emailInput ? emailInput.value.trim() : '';
                        const isValid = validateEmail(emailValue);

                        if (!isValid) {
                            event.preventDefault();
                            updateFormState();
                            return;
                        }

                        forgotBtn.disabled = true;
                        forgotBtn.setAttribute('aria-disabled', 'true');
                    });
                }
            });
        </script>
    </x-authentication-card>
</x-guest-layout>