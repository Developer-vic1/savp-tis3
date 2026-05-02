<x-guest-layout>
    <div
        class="relative min-h-screen overflow-hidden bg-gradient-to-br from-slate-50 via-sky-50 to-emerald-50 px-4 py-8 dark:from-slate-950 dark:via-slate-900 dark:to-emerald-950 sm:px-6 lg:px-8">

        {{-- Fondos decorativos --}}
        <div class="pointer-events-none absolute -left-24 -top-24 h-72 w-72 rounded-full bg-emerald-400/20 blur-3xl">
        </div>
        <div class="pointer-events-none absolute right-0 top-1/4 h-80 w-80 rounded-full bg-sky-400/20 blur-3xl"></div>
        <div class="pointer-events-none absolute bottom-0 left-1/3 h-72 w-72 rounded-full bg-violet-400/10 blur-3xl">
        </div>

        {{-- Botón volver --}}
        <div class="relative mx-auto mb-6 flex w-full max-w-6xl justify-start">
            <a href="{{ route('dashboard') }}"
                class="inline-flex items-center gap-2 rounded-2xl border border-white/70 bg-white/80 px-4 py-2.5 text-sm font-bold text-slate-700 shadow-sm backdrop-blur transition hover:-translate-y-0.5 hover:bg-white hover:text-emerald-700 dark:border-slate-700 dark:bg-slate-900/80 dark:text-slate-200 dark:hover:text-emerald-300">
                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"
                        d="M10.5 19.5 3 12m0 0 7.5-7.5M3 12h18" />
                </svg>
                Volver al sistema
            </a>
        </div>

        <div
            class="relative mx-auto grid min-h-[calc(100vh-8rem)] w-full max-w-6xl items-center gap-8 lg:grid-cols-[1.05fr,0.95fr]">

            {{-- Panel informativo --}}
            <section class="hidden lg:block">
                <div
                    class="relative overflow-hidden rounded-[2rem] border border-white/60 bg-white/70 p-8 shadow-2xl shadow-slate-900/10 backdrop-blur-xl dark:border-slate-700/80 dark:bg-slate-900/70 dark:shadow-black/30">
                    <div class="absolute -right-16 -top-16 h-52 w-52 rounded-full bg-emerald-400/15 blur-3xl"></div>
                    <div class="absolute -bottom-20 left-8 h-56 w-56 rounded-full bg-sky-400/15 blur-3xl"></div>

                    <div class="relative">
                        <div
                            class="mb-6 inline-flex items-center gap-2 rounded-full border border-emerald-200 bg-emerald-50 px-4 py-2 text-xs font-black uppercase tracking-[0.16em] text-emerald-700 dark:border-emerald-400/20 dark:bg-emerald-400/10 dark:text-emerald-300">
                            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"
                                    d="M12 3.75 4.5 6.75v5.625c0 4.038 3.06 7.82 7.5 8.875 4.44-1.055 7.5-4.837 7.5-8.875V6.75L12 3.75Z" />
                            </svg>
                            Área segura
                        </div>

                        <h1 class="text-4xl font-black tracking-tight text-slate-950 dark:text-white">
                            Confirmación de seguridad institucional
                        </h1>

                        <p class="mt-4 max-w-xl text-sm leading-7 text-slate-600 dark:text-slate-300">
                            Antes de continuar, confirma tu contraseña actual. Esta verificación protege acciones
                            sensibles como cambios de seguridad, sesiones activas o configuración de cuenta.
                        </p>

                        <div class="mt-8 grid gap-4 sm:grid-cols-2">
                            <div
                                class="rounded-2xl border border-slate-200 bg-white/70 p-4 dark:border-slate-700 dark:bg-slate-950/40">
                                <div
                                    class="flex h-10 w-10 items-center justify-center rounded-xl bg-emerald-100 text-emerald-700 dark:bg-emerald-400/10 dark:text-emerald-300">
                                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"
                                            d="M16.5 10.5V6.75a4.5 4.5 0 1 0-9 0v3.75m-.75 11.25h10.5A2.25 2.25 0 0 0 19.5 19.5v-7.5a2.25 2.25 0 0 0-2.25-2.25H6.75A2.25 2.25 0 0 0 4.5 12v7.5A2.25 2.25 0 0 0 6.75 21.75Z" />
                                    </svg>
                                </div>
                                <h3 class="mt-3 text-sm font-black text-slate-900 dark:text-white">
                                    Protección de cuenta
                                </h3>
                                <p class="mt-1 text-xs leading-5 text-slate-500 dark:text-slate-400">
                                    Evita cambios sensibles sin validación del usuario autenticado.
                                </p>
                            </div>

                            <div
                                class="rounded-2xl border border-slate-200 bg-white/70 p-4 dark:border-slate-700 dark:bg-slate-950/40">
                                <div
                                    class="flex h-10 w-10 items-center justify-center rounded-xl bg-sky-100 text-sky-700 dark:bg-sky-400/10 dark:text-sky-300">
                                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"
                                            d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                                    </svg>
                                </div>
                                <h3 class="mt-3 text-sm font-black text-slate-900 dark:text-white">
                                    Validación temporal
                                </h3>
                                <p class="mt-1 text-xs leading-5 text-slate-500 dark:text-slate-400">
                                    Solo se solicita para confirmar acciones importantes del sistema.
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </section>

            {{-- Formulario --}}
            <section x-data="{ showPassword: false, password: '' }"
                class="relative mx-auto w-full max-w-md overflow-hidden rounded-[2rem] border border-white/70 bg-white/90 p-6 shadow-2xl shadow-slate-900/10 backdrop-blur-xl dark:border-slate-700/80 dark:bg-slate-900/90 dark:shadow-black/30 sm:p-8">

                <div
                    class="pointer-events-none absolute -right-20 -top-20 h-56 w-56 rounded-full bg-emerald-400/10 blur-3xl">
                </div>
                <div
                    class="pointer-events-none absolute -bottom-20 left-8 h-56 w-56 rounded-full bg-sky-400/10 blur-3xl">
                </div>

                <div class="relative">
                    {{-- Logo --}}
                    <div class="mb-6 flex justify-center">
                        <div
                            class="flex h-20 w-20 items-center justify-center rounded-[1.6rem] bg-gradient-to-br from-emerald-600 to-sky-600 text-white shadow-xl shadow-emerald-500/20">
                            <svg class="h-9 w-9" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"
                                    d="M12 3.75 4.5 6.75v5.625c0 4.038 3.06 7.82 7.5 8.875 4.44-1.055 7.5-4.837 7.5-8.875V6.75L12 3.75Z" />
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"
                                    d="M9 12.75 11.25 15 15 9.75" />
                            </svg>
                        </div>
                    </div>

                    <div class="text-center">
                        <p
                            class="text-xs font-black uppercase tracking-[0.18em] text-emerald-700 dark:text-emerald-300">
                            SAVP – TIS 3
                        </p>

                        <h2 class="mt-2 text-2xl font-black tracking-tight text-slate-950 dark:text-white">
                            Confirma tu contraseña
                        </h2>

                        <p class="mt-3 text-sm leading-6 text-slate-600 dark:text-slate-300">
                            Esta es un área segura del sistema. Ingresa tu contraseña actual para continuar.
                        </p>
                    </div>

                    <x-validation-errors
                        class="mt-6 rounded-2xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-700 dark:border-rose-400/20 dark:bg-rose-400/10 dark:text-rose-300" />

                    <form method="POST" action="{{ route('password.confirm') }}" class="mt-6 space-y-5">
                        @csrf

                        <div>
                            <label for="password" class="block text-sm font-bold text-slate-800 dark:text-slate-100">
                                Contraseña actual
                            </label>

                            <div class="relative mt-2">
                                <input id="password" x-bind:type="showPassword ? 'text' : 'password'" x-model="password"
                                    class="block w-full rounded-2xl border border-slate-200 bg-white px-4 py-3 pr-12 text-sm font-semibold text-slate-900 shadow-sm outline-none transition placeholder:text-slate-400 focus:border-emerald-500 focus:ring-4 focus:ring-emerald-500/10 dark:border-slate-700 dark:bg-slate-950 dark:text-slate-100 dark:placeholder:text-slate-500"
                                    name="password" required autocomplete="current-password" autofocus
                                    placeholder="Ingresa tu contraseña" />

                                <button type="button" @click="showPassword = !showPassword"
                                    class="absolute inset-y-0 right-0 flex items-center pr-4 text-slate-400 transition hover:text-slate-700 dark:hover:text-slate-200"
                                    title="Mostrar u ocultar contraseña">
                                    <svg x-show="!showPassword" class="h-5 w-5" fill="none" viewBox="0 0 24 24"
                                        stroke="currentColor" stroke-width="1.8">
                                        <path stroke-linecap="round" stroke-linejoin="round"
                                            d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7Z" />
                                        <path stroke-linecap="round" stroke-linejoin="round"
                                            d="M12 15.75A3.75 3.75 0 1 0 12 8.25a3.75 3.75 0 0 0 0 7.5Z" />
                                    </svg>

                                    <svg x-show="showPassword" x-cloak class="h-5 w-5" fill="none" viewBox="0 0 24 24"
                                        stroke="currentColor" stroke-width="1.8">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M3 3l18 18" />
                                        <path stroke-linecap="round" stroke-linejoin="round"
                                            d="M10.584 10.587A2.25 2.25 0 0 0 13.41 13.41" />
                                        <path stroke-linecap="round" stroke-linejoin="round"
                                            d="M9.88 5.09A9.953 9.953 0 0 1 12 4.875c5.053 0 9.27 3.11 10.5 7.125a11.03 11.03 0 0 1-4.04 5.411M6.228 6.228A11.03 11.03 0 0 0 1.5 12c.69 2.25 2.14 4.175 4.04 5.411A9.953 9.953 0 0 0 12 19.125c.73 0 1.442-.078 2.125-.227" />
                                    </svg>
                                </button>
                            </div>
                        </div>

                        <div
                            class="rounded-2xl border border-sky-200 bg-sky-50 px-4 py-3 text-xs leading-5 text-sky-800 dark:border-sky-400/20 dark:bg-sky-400/10 dark:text-sky-200">
                            Por seguridad, esta confirmación no cambia tu contraseña; solo verifica tu identidad para
                            continuar con una acción sensible.
                        </div>

                        <button type="submit" x-bind:disabled="password.trim().length === 0"
                            x-bind:class="password.trim().length > 0
                                ? 'bg-gradient-to-r from-emerald-600 to-sky-600 text-white shadow-lg shadow-emerald-500/20 hover:-translate-y-0.5 hover:shadow-xl'
                                : 'cursor-not-allowed bg-slate-200 text-slate-500 dark:bg-slate-800 dark:text-slate-500'"
                            class="inline-flex w-full items-center justify-center gap-2 rounded-2xl px-5 py-3 text-sm font-black transition disabled:opacity-70">
                            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"
                                    d="M16.5 10.5V6.75a4.5 4.5 0 1 0-9 0v3.75m-.75 11.25h10.5A2.25 2.25 0 0 0 19.5 19.5v-7.5a2.25 2.25 0 0 0-2.25-2.25H6.75A2.25 2.25 0 0 0 4.5 12v7.5A2.25 2.25 0 0 0 6.75 21.75Z" />
                            </svg>
                            Confirmar y continuar
                        </button>
                    </form>
                </div>
            </section>
        </div>
    </div>
</x-guest-layout>