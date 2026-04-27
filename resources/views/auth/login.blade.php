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
            .auth-bg {
                position: fixed;
                inset: 0;
                overflow: hidden;
                z-index: -1;
                background:
                    radial-gradient(circle at 20% 20%, rgba(16, 185, 129, 0.16), transparent 26%),
                    radial-gradient(circle at 80% 18%, rgba(14, 165, 233, 0.14), transparent 24%),
                    radial-gradient(circle at 75% 80%, rgba(59, 130, 246, 0.12), transparent 22%),
                    linear-gradient(135deg, #f8fafc 0%, #f1f5f9 45%, #eef2f7 100%);
            }

            .auth-bg::before {
                content: "";
                position: absolute;
                inset: 0;
                opacity: .06;
                background-image:
                    linear-gradient(rgba(15, 23, 42, 0.8) 1px, transparent 1px),
                    linear-gradient(90deg, rgba(15, 23, 42, 0.8) 1px, transparent 1px);
                background-size: 36px 36px;
                mask-image: radial-gradient(circle at center, black 45%, transparent 100%);
                pointer-events: none;
            }

            .bg-blob {
                position: absolute;
                border-radius: 9999px;
                filter: blur(60px);
                opacity: .55;
                animation: blobFloat 16s ease-in-out infinite;
                will-change: transform;
            }

            .bg-blob.one {
                width: 340px;
                height: 340px;
                left: -80px;
                top: 8%;
                background: rgba(16, 185, 129, 0.22);
                animation-delay: 0s;
            }

            .bg-blob.two {
                width: 300px;
                height: 300px;
                right: -60px;
                top: 14%;
                background: rgba(14, 165, 233, 0.18);
                animation-delay: -3s;
            }

            .bg-blob.three {
                width: 360px;
                height: 360px;
                left: 10%;
                bottom: -120px;
                background: rgba(45, 212, 191, 0.14);
                animation-delay: -6s;
            }

            .bg-blob.four {
                width: 320px;
                height: 320px;
                right: 8%;
                bottom: -90px;
                background: rgba(59, 130, 246, 0.16);
                animation-delay: -9s;
            }

            .bg-orbit {
                position: absolute;
                border: 1px solid rgba(148, 163, 184, 0.18);
                border-radius: 9999px;
                animation: spinSlow linear infinite;
                transform-origin: center;
            }

            .bg-orbit.one {
                width: 520px;
                height: 520px;
                left: 8%;
                top: 12%;
                animation-duration: 32s;
            }

            .bg-orbit.two {
                width: 680px;
                height: 680px;
                right: 4%;
                bottom: 4%;
                animation-duration: 40s;
                animation-direction: reverse;
            }

            .bg-orbit::after {
                content: "";
                position: absolute;
                top: 18%;
                left: 85%;
                width: 14px;
                height: 14px;
                border-radius: 9999px;
                background: linear-gradient(135deg, #10b981, #38bdf8);
                box-shadow: 0 0 18px rgba(16, 185, 129, .35);
            }

            .bg-particle {
                position: absolute;
                border-radius: 9999px;
                pointer-events: none;
                opacity: .7;
                animation: particleFloat linear infinite;
            }

            .bg-particle.p1 {
                width: 8px;
                height: 8px;
                left: 12%;
                top: 22%;
                background: rgba(16, 185, 129, .45);
                animation-duration: 10s;
            }

            .bg-particle.p2 {
                width: 10px;
                height: 10px;
                right: 18%;
                top: 30%;
                background: rgba(14, 165, 233, .38);
                animation-duration: 13s;
            }

            .bg-particle.p3 {
                width: 6px;
                height: 6px;
                left: 22%;
                bottom: 18%;
                background: rgba(52, 211, 153, .42);
                animation-duration: 9s;
            }

            .bg-particle.p4 {
                width: 12px;
                height: 12px;
                right: 10%;
                bottom: 20%;
                background: rgba(56, 189, 248, .30);
                animation-duration: 14s;
            }

            .bg-particle.p5 {
                width: 7px;
                height: 7px;
                left: 50%;
                top: 12%;
                background: rgba(16, 185, 129, .30);
                animation-duration: 11s;
            }

            .bg-particle.p6 {
                width: 9px;
                height: 9px;
                right: 36%;
                bottom: 12%;
                background: rgba(59, 130, 246, .28);
                animation-duration: 12s;
            }

            .bg-wave {
                position: absolute;
                left: -10%;
                width: 120%;
                height: 220px;
                border-radius: 50%;
                filter: blur(10px);
                opacity: .18;
                background: linear-gradient(90deg, rgba(16, 185, 129, .12), rgba(14, 165, 233, .12));
                animation: waveMove 18s ease-in-out infinite;
            }

            .bg-wave.one {
                bottom: 8%;
            }

            .bg-wave.two {
                bottom: 2%;
                animation-delay: -7s;
            }

            .login-stage {
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

            .login-stage::before {
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
                top: 12%;
                background: rgba(16, 185, 129, 0.38);
                animation-duration: 7s;
            }

            .particle.two {
                width: 8px;
                height: 8px;
                right: 12%;
                top: 18%;
                background: rgba(14, 165, 233, 0.35);
                animation-duration: 9s;
            }

            .particle.three {
                width: 12px;
                height: 12px;
                left: 14%;
                bottom: 18%;
                background: rgba(52, 211, 153, 0.25);
                animation-duration: 11s;
            }

            .particle.four {
                width: 7px;
                height: 7px;
                right: 16%;
                bottom: 12%;
                background: rgba(56, 189, 248, 0.30);
                animation-duration: 8s;
            }

            .particle.five {
                width: 6px;
                height: 6px;
                left: 48%;
                top: 10%;
                background: rgba(16, 185, 129, 0.28);
                animation-duration: 10s;
            }

            .shine-line {
                position: absolute;
                inset: 0;
                overflow: hidden;
                border-radius: 1.75rem;
                pointer-events: none;
            }

            .shine-line::after {
                content: "";
                position: absolute;
                top: -20%;
                left: -35%;
                width: 35%;
                height: 140%;
                transform: rotate(18deg);
                background: linear-gradient(to right,
                        transparent,
                        rgba(255, 255, 255, .45),
                        transparent);
                animation: sweep 6s infinite;
            }

            .login-input {
                transition: all .22s ease;
            }

            .login-input.input-valid {
                border-color: rgb(16 185 129) !important;
            }

            .login-input.input-invalid {
                border-color: rgb(248 113 113) !important;
            }

            .login-btn:disabled {
                cursor: not-allowed;
                opacity: .55;
                box-shadow: none;
                transform: none !important;
            }

            .login-input:focus {
                transform: translateY(-1px);
                box-shadow: 0 0 0 4px rgba(16, 185, 129, 0.12);
            }

            .login-btn {
                transition: all .22s ease;
            }

            .login-btn:hover {
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

            .fade-up.delay-3 {
                animation-delay: .24s;
            }

            .fade-up.delay-4 {
                animation-delay: .32s;
            }

            @keyframes blobFloat {

                0%,
                100% {
                    transform: translate3d(0, 0, 0) scale(1);
                }

                25% {
                    transform: translate3d(30px, -20px, 0) scale(1.04);
                }

                50% {
                    transform: translate3d(-18px, 26px, 0) scale(0.98);
                }

                75% {
                    transform: translate3d(22px, 12px, 0) scale(1.02);
                }
            }

            @keyframes spinSlow {
                from {
                    transform: rotate(0deg);
                }

                to {
                    transform: rotate(360deg);
                }
            }

            @keyframes particleFloat {

                0%,
                100% {
                    transform: translateY(0px) translateX(0px);
                }

                20% {
                    transform: translateY(-10px) translateX(6px);
                }

                50% {
                    transform: translateY(-22px) translateX(-5px);
                }

                80% {
                    transform: translateY(-8px) translateX(8px);
                }
            }

            @keyframes waveMove {

                0%,
                100% {
                    transform: translateX(0) scaleX(1);
                }

                50% {
                    transform: translateX(3%) scaleX(1.03);
                }
            }

            @keyframes floatY {
                0% {
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

                100% {
                    transform: translateY(0px) translateX(0px);
                }
            }

            @keyframes sweep {
                0% {
                    left: -40%;
                }

                100% {
                    left: 130%;
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

                .bg-blob,
                .bg-orbit,
                .bg-particle,
                .bg-wave,
                .particle,
                .shine-line::after,
                .fade-up,
                .animate-\[floatLogo_5s_ease-in-out_infinite\] {
                    animation: none !important;
                }
            }
        </style>

        <div class="auth-bg" aria-hidden="true">
            <div class="bg-blob one"></div>
            <div class="bg-blob two"></div>
            <div class="bg-blob three"></div>
            <div class="bg-blob four"></div>

            <div class="bg-orbit one"></div>
            <div class="bg-orbit two"></div>

            <div class="bg-particle p1"></div>
            <div class="bg-particle p2"></div>
            <div class="bg-particle p3"></div>
            <div class="bg-particle p4"></div>
            <div class="bg-particle p5"></div>
            <div class="bg-particle p6"></div>

            <div class="bg-wave one"></div>
            <div class="bg-wave two"></div>
        </div>

        <div class="login-stage px-6 py-7 sm:px-8 sm:py-8">
            <div class="particle one"></div>
            <div class="particle two"></div>
            <div class="particle three"></div>
            <div class="particle four"></div>
            <div class="particle five"></div>
            <div class="shine-line"></div>

            <div class="relative z-10">
                <div class="fade-up">
                    <div
                        class="mb-3 inline-flex items-center gap-2 rounded-full border border-emerald-200 bg-white/80 px-3 py-1 text-[11px] font-semibold uppercase tracking-[0.18em] text-emerald-700 shadow-sm">
                        <span class="h-2 w-2 rounded-full bg-emerald-500 animate-pulse"></span>
                        Acceso institucional
                    </div>

                    <h1 class="text-center text-3xl font-black tracking-tight text-slate-900">
                        Bienvenido
                    </h1>

                    <p class="mx-auto mt-2 max-w-md text-center text-sm leading-6 text-slate-600">
                        Inicia sesión para acceder al sistema de la
                        <span class="font-semibold text-emerald-700">Unidad Educativa Franz Tamayo N°3</span>.
                    </p>
                </div>

                <div class="fade-up delay-2 mt-6">
                    <x-validation-errors
                        class="mb-4 hidden rounded-2xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-700" />

                    @session('status')
                        <script>
                            document.addEventListener('DOMContentLoaded', function () {
                                let titulo = 'Contraseña actualizada';

                                if (@json($value) === 'Tu contraseña fue restablecida correctamente.') {
                                    titulo = 'Contraseña actualizada';
                                }

                                Swal.fire({
                                    icon: 'success',
                                    title: titulo,
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

                <form method="POST" action="{{ route('login') }}" class="fade-up delay-3 space-y-5" novalidate
                    id="loginForm">
                    @csrf

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
                                class="login-input block mt-1 w-full rounded-2xl border-slate-300 bg-white/90 py-3.5 pl-11 pr-4 text-sm text-slate-800 placeholder-slate-400 shadow-sm focus:border-emerald-500 focus:ring-emerald-500"
                                type="email" name="email" value="" required autofocus autocomplete="username"
                                inputmode="email" maxlength="120" placeholder="usuario@gmail.com" />
                        </div>

                        <p id="emailError" class="mt-2 hidden text-xs font-medium text-rose-500">
                            Ingresa un correo válido, por ejemplo: usuario@gmail.com
                        </p>
                    </div>

                    <div>
                        <label for="password" class="mb-2 block text-sm font-semibold text-slate-700">
                            Contraseña
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
                                class="login-input block mt-1 w-full rounded-2xl border-slate-300 bg-white/90 py-3.5 pl-11 pr-12 text-sm text-slate-800 placeholder-slate-400 shadow-sm focus:border-emerald-500 focus:ring-emerald-500"
                                type="password" name="password" required autocomplete="current-password"
                                placeholder="Ingresa tu contraseña" />

                            <button type="button" id="togglePassword"
                                class="absolute inset-y-0 right-0 flex items-center pr-4 text-slate-400 transition hover:text-emerald-600"
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
                        <label for="remember_me" class="flex items-center text-sm text-slate-600">
                            <x-checkbox id="remember_me" name="remember"
                                class="rounded border-slate-300 text-emerald-600 shadow-sm focus:ring-emerald-500" />
                            <span class="ms-2">Recordarme</span>
                        </label>

                        @if (Route::has('password.request'))
                            <a class="text-sm font-medium text-emerald-700 transition hover:text-emerald-800 hover:underline"
                                href="{{ route('password.request') }}">
                                ¿Olvidaste tu contraseña?
                            </a>
                        @endif
                    </div>

                    <div class="fade-up delay-4 pt-1">
                        <x-button id="loginBtn"
                            class="group login-btn flex w-full items-center justify-center gap-2 rounded-2xl border-0 bg-gradient-to-r from-emerald-600 via-emerald-500 to-sky-600 px-5 py-3.5 text-sm font-bold tracking-wide text-white shadow-lg shadow-emerald-500/25 transition hover:from-emerald-700 hover:via-emerald-600 hover:to-sky-700 disabled:cursor-not-allowed disabled:opacity-50"
                            disabled>
                            <span>Ingresar</span>
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

                <div class="mt-6 border-t border-slate-200/80 pt-5 text-center">
                    <p class="text-xs leading-6 text-slate-500">
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

                if (togglePassword && passwordInput) {
                    togglePassword.addEventListener('click', function () {
                        const isPassword = passwordInput.getAttribute('type') === 'password';
                        passwordInput.setAttribute('type', isPassword ? 'text' : 'password');

                        eyeOpen.classList.toggle('hidden');
                        eyeClosed.classList.toggle('hidden');
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
                @endif

                if (loginForm && loginBtn) {
                    loginForm.addEventListener('submit', function (e) {
                        const emailValue = emailInput ? emailInput.value.trim() : '';
                        const passwordValue = passwordInput ? passwordInput.value.trim() : '';

                        const isEmailValid = validateEmail(emailValue);
                        const hasPassword = passwordValue.length > 0;

                        if (!isEmailValid || !hasPassword) {
                            e.preventDefault();
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