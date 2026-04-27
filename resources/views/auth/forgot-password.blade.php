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
            .forgot-stage {
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

            .forgot-stage::before {
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

            .forgot-input {
                transition: all .22s ease;
            }

            .forgot-input.input-valid {
                border-color: rgb(16 185 129) !important;
            }

            .forgot-input.input-invalid {
                border-color: rgb(248 113 113) !important;
            }

            .forgot-input:focus {
                transform: translateY(-1px);
                box-shadow: 0 0 0 4px rgba(16, 185, 129, 0.12);
            }

            .forgot-btn {
                transition: all .22s ease;
            }

            .forgot-btn:hover {
                transform: translateY(-1px) scale(1.01);
                box-shadow: 0 14px 30px rgba(16, 185, 129, 0.22);
            }

            .forgot-btn:disabled {
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

        <div class="forgot-stage px-6 py-7 sm:px-8 sm:py-8">
            <div class="relative z-10">
                <div class="fade-up">
                    <div
                        class="mb-3 inline-flex items-center gap-2 rounded-full border border-emerald-200 bg-white/80 px-3 py-1 text-[11px] font-semibold uppercase tracking-[0.18em] text-emerald-700 shadow-sm">
                        <span class="h-2 w-2 rounded-full bg-emerald-500 animate-pulse"></span>
                        Recuperación de acceso
                    </div>

                    <h1 class="text-center text-3xl font-black tracking-tight text-slate-900">
                        ¿Olvidaste tu contraseña?
                    </h1>

                    <p class="mx-auto mt-2 max-w-md text-center text-sm leading-6 text-slate-600">
                        Ingresa tu correo electrónico y te enviaremos un enlace para restablecer tu contraseña y
                        recuperar el acceso a tu cuenta.
                    </p>
                </div>

                <div class="fade-up delay-1 mt-6">
                    <x-validation-errors
                        class="mb-4 hidden rounded-2xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-700" />

                    @session('status')
                        <script>
                            document.addEventListener('DOMContentLoaded', function () {
                                Swal.fire({
                                    icon: 'success',
                                    title: 'Enlace enviado',
                                    text: 'Te enviamos un enlace de recuperación a tu correo electrónico.',
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

                <form method="POST" action="{{ route('password.email') }}" class="fade-up delay-2 space-y-5"
                    id="forgotForm" novalidate>
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
                                class="forgot-input block mt-1 w-full rounded-2xl border-slate-300 bg-white/90 py-3.5 pl-11 pr-4 text-sm text-slate-800 placeholder-slate-400 shadow-sm focus:border-emerald-500 focus:ring-emerald-500"
                                type="email" name="email" value="" required autofocus autocomplete="username"
                                inputmode="email" maxlength="120" placeholder="usuario@gmail.com" />
                        </div>

                        <p id="emailError" class="mt-2 hidden text-xs font-medium text-rose-500">
                            Ingresa un correo válido, por ejemplo: usuario@gmail.com
                        </p>
                    </div>

                    <div class="rounded-2xl border border-sky-200 bg-sky-50 px-4 py-3 text-sm text-sky-800">
                        Asegúrate de ingresar el correo con el que fue registrada tu cuenta dentro del sistema.
                    </div>

                    <div class="fade-up delay-3 pt-1">
                        <x-button id="forgotBtn"
                            class="group forgot-btn flex w-full items-center justify-center gap-2 rounded-2xl border-0 bg-gradient-to-r from-emerald-600 via-emerald-500 to-sky-600 px-5 py-3.5 text-sm font-bold tracking-wide text-white shadow-lg shadow-emerald-500/25 transition hover:from-emerald-700 hover:via-emerald-600 hover:to-sky-700 disabled:cursor-not-allowed disabled:opacity-50"
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

                <div class="mt-6 border-t border-slate-200/80 pt-5 text-center">
                    <p class="text-xs leading-6 text-slate-500">
                        Si el correo está registrado, recibirás instrucciones para restablecer tu contraseña.
                    </p>
                </div>
            </div>
        </div>

        <script>
            document.addEventListener('DOMContentLoaded', function () {
                const emailInput = document.getElementById('email');
                const emailError = document.getElementById('emailError');
                const forgotBtn = document.getElementById('forgotBtn');
                const forgotForm = document.getElementById('forgotForm');

                function validateEmail(email) {
                    return /^[a-zA-Z0-9._%+-]+@gmail\.com$/.test(email);
                }

                function updateFormState() {
                    if (!emailInput || !forgotBtn || !emailError) return;

                    const emailValue = emailInput.value.trim();
                    const isValid = validateEmail(emailValue);

                    emailInput.classList.remove('input-valid', 'input-invalid');

                    if (emailValue.length === 0) {
                        emailError.classList.add('hidden');
                        forgotBtn.disabled = true;
                        return;
                    }

                    if (isValid) {
                        emailInput.classList.add('input-valid');
                        emailError.classList.add('hidden');
                        forgotBtn.disabled = false;
                    } else {
                        emailInput.classList.add('input-invalid');
                        emailError.classList.remove('hidden');
                        forgotBtn.disabled = true;
                    }
                }

                if (emailInput) {
                    emailInput.addEventListener('input', updateFormState);
                    emailInput.addEventListener('blur', updateFormState);
                    updateFormState();
                }

                @if ($errors->any())
                    if (emailInput) emailInput.value = '';
                @endif

                if (forgotForm && forgotBtn) {
                    forgotForm.addEventListener('submit', function (e) {
                        const emailValue = emailInput ? emailInput.value.trim() : '';
                        const isValid = validateEmail(emailValue);

                        if (!isValid) {
                            e.preventDefault();
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