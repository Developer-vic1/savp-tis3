<x-guest-layout>
    <x-authentication-card>
        <x-slot name="logo">
            <div class="text-center">
                <div
                    class="relative mx-auto flex h-24 w-24 items-center justify-center rounded-[1.7rem] bg-white shadow-[0_24px_60px_rgba(16,185,129,0.18)] ring-4 ring-white/70 dark:bg-slate-900 dark:ring-slate-700/80">
                    <img src="{{ asset('image/LOGO FT3 A.jpg') }}" alt="Logo Franz Tamayo N°3"
                        class="h-16 w-16 rounded-2xl object-contain">

                    <span
                        class="absolute -right-1 -top-1 h-4 w-4 rounded-full bg-amber-400 shadow-lg shadow-amber-400/60 animate-pulse"></span>
                    <span
                        class="absolute -bottom-1 -left-1 h-3 w-3 rounded-full bg-emerald-300 shadow-lg shadow-emerald-300/60 animate-pulse"></span>
                </div>

                <div class="mt-4">
                    <p class="font-display text-base font-black text-slate-950 dark:text-white">
                        Franz Tamayo N°3
                    </p>
                    <p class="text-xs font-black uppercase tracking-[0.18em] text-emerald-600 dark:text-emerald-300">
                        SAVP-TIS3 Aula Virtual
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

            .login-back-btn,
            .login-theme-btn {
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

            .login-back-btn {
                left: 1.25rem;
            }

            .login-theme-btn {
                right: 1.25rem;
            }

            html.dark .login-back-btn,
            html.dark .login-theme-btn {
                background: rgba(15, 23, 42, .78);
                color: #cbd5e1;
                border-color: rgba(71, 85, 105, .86);
            }

            .login-back-btn:hover {
                transform: translateY(-2px);
                color: #059669;
                border-color: rgba(16, 185, 129, .48);
            }

            .login-theme-btn:hover {
                transform: translateY(-2px);
                color: #0284c7;
                border-color: rgba(14, 165, 233, .48);
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

            .aula-google-button {
                display: flex;
                width: 100%;
                align-items: center;
                justify-content: center;
                gap: .85rem;
                border-radius: 1.25rem;
                border: 1px solid rgba(203, 213, 225, .9);
                background: rgba(255, 255, 255, .92);
                padding: 1rem 1.25rem;
                font-size: 1rem;
                font-weight: 900;
                color: #334155;
                box-shadow: 0 14px 30px rgba(15, 23, 42, .08);
                transition:
                    transform .22s ease,
                    box-shadow .22s ease,
                    border-color .22s ease,
                    background .22s ease,
                    color .22s ease;
            }

            .aula-google-button:hover {
                transform: translateY(-1px) scale(1.01);
                border-color: rgba(16, 185, 129, .48);
                background: rgba(255, 255, 255, .98);
                color: #059669;
                box-shadow: 0 18px 38px rgba(16, 185, 129, .18);
            }

            html.dark .aula-google-button {
                border-color: rgba(71, 85, 105, .86);
                background: rgba(15, 23, 42, .82);
                color: #e2e8f0;
                box-shadow: 0 14px 32px rgba(0, 0, 0, .25);
            }

            html.dark .aula-google-button:hover {
                border-color: rgba(52, 211, 153, .42);
                background: rgba(30, 41, 59, .92);
                color: #34d399;
            }

            .aula-google-icon {
                width: 1.35rem;
                height: 1.35rem;
                flex-shrink: 0;
            }

            .aula-info-box {
                border-radius: 1.35rem;
                border: 1px solid rgba(167, 243, 208, .85);
                background: rgba(236, 253, 245, .82);
                padding: 1rem;
                color: #047857;
                box-shadow: 0 10px 24px rgba(16, 185, 129, .08);
            }

            html.dark .aula-info-box {
                border-color: rgba(52, 211, 153, .24);
                background: rgba(6, 78, 59, .25);
                color: #a7f3d0;
            }

            .aula-warning-box {
                border-radius: 1.35rem;
                border: 1px solid rgba(254, 205, 211, .92);
                background: rgba(255, 241, 242, .86);
                padding: 1rem;
                color: #be123c;
                box-shadow: 0 10px 24px rgba(244, 63, 94, .08);
            }

            html.dark .aula-warning-box {
                border-color: rgba(251, 113, 133, .25);
                background: rgba(76, 5, 25, .32);
                color: #fda4af;
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

            @media (prefers-reduced-motion: reduce) {

                .particle,
                .shine-line::after,
                .fade-up {
                    animation: none !important;
                }

                .aula-google-button:hover,
                .login-back-btn:hover,
                .login-theme-btn:hover {
                    transform: none !important;
                }
            }
        </style>

        <div class="login-stage px-7 pb-8 pt-20 sm:px-10 sm:pb-10 sm:pt-22">
            {{-- Botón volver al welcome --}}
            <a href="{{ route('welcome') }}" class="login-back-btn" title="Volver al inicio"
                aria-label="Volver al inicio">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24"
                    stroke="currentColor" stroke-width="1.8">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5 3 12m0 0 7.5-7.5M3 12h18" />
                </svg>
            </a>

            {{-- Botón tema --}}
            <button type="button" id="aulaLoginThemeToggle" class="login-theme-btn" title="Cambiar tema"
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
                        Acceso institucional
                    </div>

                    <h1 class="text-center text-4xl font-black tracking-tight text-slate-950 dark:text-white">
                        Aula Virtual
                    </h1>

                    <p class="mx-auto mt-3 max-w-lg text-center text-base leading-7 text-slate-600 dark:text-slate-300">
                        Ingresa con tu cuenta Google registrada para acceder a tus cursos, actividades,
                        calificaciones y seguimiento académico en la
                        <span class="font-bold text-emerald-700 dark:text-emerald-300">
                            Unidad Educativa Franz Tamayo N°3
                        </span>.
                    </p>
                </div>

                <div class="fade-up delay-1 mt-7 space-y-4">
                    @if (session('error'))
                        <div class="aula-warning-box text-sm font-semibold leading-6">
                            {{ session('error') }}
                        </div>
                    @endif

                    @if (session('aula_virtual_access_error'))
                        <div class="aula-warning-box text-sm font-semibold leading-6">
                            <span class="block text-base font-black">Acceso no habilitado</span>
                            <span class="mt-1 block">
                                Tu cuenta existe en el sistema, pero no tiene permisos habilitados para el Aula Virtual.
                                Contacta con administración para solicitar la activación correspondiente.
                            </span>
                        </div>
                    @endif
                </div>

                <div class="fade-up delay-2 mt-8 space-y-5">
                    <a href="{{ route('aula-virtual.google.redirect') }}" class="aula-google-button">
                        <svg class="aula-google-icon" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"
                            aria-hidden="true">
                            <path
                                d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92c-.26 1.37-1.04 2.53-2.21 3.31v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.09z"
                                fill="#4285F4" />
                            <path
                                d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z"
                                fill="#34A853" />
                            <path
                                d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.06H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.94l2.85-2.22.81-.63z"
                                fill="#FBBC05" />
                            <path
                                d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.47 2.18 7.06l3.66 2.84c.87-2.6 3.3-4.52 6.16-4.52z"
                                fill="#EA4335" />
                        </svg>

                        <span>Continuar con Google institucional</span>
                    </a>

                    <div class="aula-info-box text-sm font-semibold leading-7">
                        <div class="flex items-start gap-3">
                            <svg class="mt-0.5 h-5 w-5 shrink-0" fill="none" stroke="currentColor"
                                viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"
                                    d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                            </svg>

                            <p>
                                El Aula Virtual es exclusiva para estudiantes y docentes registrados. Google verifica tu
                                identidad, pero el sistema valida si tu correo está habilitado institucionalmente.
                            </p>
                        </div>
                    </div>
                </div>

                <div class="fade-up delay-3 mt-7 grid gap-3 sm:grid-cols-2">
                    <div class="rounded-2xl border bg-white/70 p-4 shadow-sm dark:bg-slate-900/50"
                        style="border-color: var(--ui-border);">
                        <div class="flex items-center gap-3">
                            <div
                                class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-emerald-100 text-emerald-700 dark:bg-emerald-400/10 dark:text-emerald-300">
                                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"
                                        d="M12 6.75c-2.25-1.5-5.25-1.5-7.5 0v11.25c2.25-1.5 5.25-1.5 7.5 0m0-11.25c2.25-1.5 5.25-1.5 7.5 0v11.25c-2.25-1.5-5.25-1.5-7.5 0m0-11.25v11.25" />
                                </svg>
                            </div>

                            <div>
                                <p class="text-sm font-black text-slate-800 dark:text-slate-100">
                                    Estudiantes
                                </p>
                                <p class="mt-1 text-xs font-semibold text-slate-500 dark:text-slate-400">
                                    Cursos, actividades y seguimiento.
                                </p>
                            </div>
                        </div>
                    </div>

                    <div class="rounded-2xl border bg-white/70 p-4 shadow-sm dark:bg-slate-900/50"
                        style="border-color: var(--ui-border);">
                        <div class="flex items-center gap-3">
                            <div
                                class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-amber-100 text-amber-700 dark:bg-amber-400/10 dark:text-amber-300">
                                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"
                                        d="M15.75 6a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0ZM4.5 20.25a8.25 8.25 0 1 1 15 0M16.5 7.5h3.75M16.5 10.5h3.75M16.5 13.5h2.25" />
                                </svg>
                            </div>

                            <div>
                                <p class="text-sm font-black text-slate-800 dark:text-slate-100">
                                    Docentes
                                </p>
                                <p class="mt-1 text-xs font-semibold text-slate-500 dark:text-slate-400">
                                    Cursos, materiales y evaluación.
                                </p>
                            </div>
                        </div>
                    </div>
                </div>

                <div
                    class="fade-up delay-4 mt-7 border-t border-slate-200/80 pt-5 text-center dark:border-slate-700/70">
                    <p class="text-sm leading-6 text-slate-500 dark:text-slate-400">
                        ¿Eres personal administrativo?
                        <a href="{{ route('login') }}"
                            class="font-black text-emerald-700 transition hover:text-emerald-800 hover:underline dark:text-emerald-300 dark:hover:text-emerald-200">
                            Ingresar al sistema administrativo
                        </a>
                    </p>
                </div>
            </div>
        </div>

        @if (session('google_auth_error'))
            <div x-data="{ open: true }" x-show="open" x-cloak
                class="fixed inset-0 z-50 flex items-center justify-center px-4 py-8">
                <div class="absolute inset-0 bg-slate-950/60 backdrop-blur-sm" @click="open = false"></div>

                <div
                    class="relative z-10 w-full max-w-lg overflow-hidden rounded-[2rem] border bg-white p-6 text-center shadow-2xl dark:border-slate-700 dark:bg-slate-900">
                    <div
                        class="mx-auto mb-4 flex h-16 w-16 items-center justify-center rounded-full bg-rose-100 text-rose-600 dark:bg-rose-500/10 dark:text-rose-400">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8" fill="none" viewBox="0 0 24 24"
                            stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                        </svg>
                    </div>

                    <h2 class="text-2xl font-black text-slate-950 dark:text-white">
                        Cuenta no registrada
                    </h2>

                    <p
                        class="mx-auto mt-3 max-w-md text-sm font-semibold leading-7 text-slate-600 dark:text-slate-300">
                        Tu cuenta de Google fue verificada correctamente, pero no se encuentra registrada en el sistema
                        institucional. Para solicitar tu habilitación, contacta con soporte.
                    </p>

                    <div class="mt-4 text-sm font-bold text-slate-500 dark:text-slate-400">
                        Soporte:
                        <span class="font-extrabold text-emerald-600 dark:text-emerald-400">
                            +591 75836807
                        </span>
                    </div>

                    <div class="mt-6 flex flex-col gap-3 sm:flex-row sm:justify-center">
                        <a href="https://wa.me/59175836807?text=Hola%2C%20necesito%20habilitar%20mi%20cuenta%20para%20acceder%20al%20Aula%20Virtual%20SAVP-TIS3"
                            target="_blank" rel="noopener noreferrer"
                            class="inline-flex items-center justify-center gap-2 rounded-2xl bg-emerald-600 px-5 py-3 text-sm font-black text-white shadow-lg shadow-emerald-500/20 transition hover:bg-emerald-700">
                            <svg class="h-5 w-5 fill-current" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"
                                aria-hidden="true">
                                <path
                                    d="M.057 24l1.687-6.163c-1.041-1.804-1.588-3.849-1.587-5.946C.06 5.348 5.397.01 12.008.01c3.202.001 6.212 1.246 8.477 3.514 2.266 2.268 3.507 5.28 3.505 8.484-.004 6.657-5.34 11.997-11.953 11.997-2.005-.001-3.973-.502-5.724-1.458L0 24zm6.59-4.846c1.6.95 3.188 1.449 4.825 1.451 5.436 0 9.86-4.42 9.864-9.858.002-2.634-1.02-5.111-2.88-6.973C16.536 1.83 14.053.8 11.415.8c-5.436 0-9.862 4.42-9.866 9.86-.001 1.765.487 3.49 1.411 5.025l-.97 3.548 3.657-.96z" />
                            </svg>
                            Contactar por WhatsApp
                        </a>

                        <button type="button" @click="open = false"
                            class="inline-flex items-center justify-center rounded-2xl border border-slate-200 bg-white px-5 py-3 text-sm font-black text-slate-600 transition hover:bg-slate-50 dark:border-slate-700 dark:bg-slate-800 dark:text-slate-200 dark:hover:bg-slate-700">
                            Cerrar
                        </button>
                    </div>
                </div>
            </div>
        @endif

        <script>
            document.addEventListener('DOMContentLoaded', function() {
                const themeToggle = document.getElementById('aulaLoginThemeToggle');

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
