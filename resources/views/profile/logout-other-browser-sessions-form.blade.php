@php
    $otrasSesiones = collect($this->sessions ?? [])->where('is_current_device', false)->count();
    $sesionesTotales = collect($this->sessions ?? [])->count();
@endphp

<x-action-section>
    <x-slot name="title">
        Sesiones activas
    </x-slot>

    <x-slot name="description">
        Administra y cierra las sesiones abiertas en otros navegadores y dispositivos para reforzar la seguridad de tu cuenta.
    </x-slot>

    <x-slot name="content">
        <div class="rounded-[1.8rem] border border-slate-200 bg-white p-5 shadow-sm sm:p-6">
            {{-- RESUMEN --}}
            <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
                <div>
                    <h3 class="text-lg font-bold text-slate-900">
                        Gestión de sesiones del sistema
                    </h3>

                    <p class="mt-2 max-w-2xl text-sm leading-6 text-slate-600">
                        Desde aquí puedes cerrar todas las sesiones abiertas en otros dispositivos. Tu sesión actual no se cerrará.
                    </p>
                </div>

                <div class="shrink-0">
                    @if ($otrasSesiones > 0)
                        <span class="inline-flex rounded-full bg-amber-50 px-3 py-1 text-xs font-semibold text-amber-700 ring-1 ring-amber-200">
                            {{ $otrasSesiones }} {{ $otrasSesiones === 1 ? 'sesión externa detectada' : 'sesiones externas detectadas' }}
                        </span>
                    @else
                        <span class="inline-flex rounded-full bg-emerald-50 px-3 py-1 text-xs font-semibold text-emerald-700 ring-1 ring-emerald-200">
                            No hay otras sesiones activas
                        </span>
                    @endif
                </div>
            </div>

            {{-- AVISO --}}
            <div class="mt-5 rounded-2xl border border-sky-200 bg-sky-50 px-4 py-4 text-sm text-sky-800">
                @if ($otrasSesiones > 0)
                    Se {{ $otrasSesiones === 1 ? 'cerrará 1 sesión activa en otro dispositivo' : 'cerrarán ' . $otrasSesiones . ' sesiones activas en otros dispositivos' }}.
                    Cuando esas personas o dispositivos intenten continuar, deberán volver a iniciar sesión.
                @else
                    Actualmente no hay otras sesiones activas fuera del dispositivo que estás utilizando en este momento.
                @endif
            </div>

            {{-- LISTA DE SESIONES --}}
            @if ($sesionesTotales > 0)
                <div class="mt-6 space-y-4">
                    <div>
                        <p class="text-sm font-semibold uppercase tracking-[0.16em] text-slate-500">
                            Dispositivos reconocidos
                        </p>
                    </div>

                    @foreach ($this->sessions as $session)
                        <div class="rounded-2xl border border-slate-200 bg-slate-50 p-4">
                            <div class="flex items-start gap-4">
                                {{-- ICONO --}}
                                <div class="mt-1 shrink-0 text-slate-500">
                                    @if ($session->agent->isDesktop())
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8" fill="none"
                                            viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.6">
                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                d="M9 17.25v1.007a3 3 0 01-.879 2.122L7.5 21h9l-.621-.621A3 3 0 0115 18.257V17.25m6-12V15a2.25 2.25 0 01-2.25 2.25H5.25A2.25 2.25 0 013 15V5.25m18 0A2.25 2.25 0 0018.75 3H5.25A2.25 2.25 0 003 5.25m18 0V12a2.25 2.25 0 01-2.25 2.25H5.25A2.25 2.25 0 013 12V5.25" />
                                        </svg>
                                    @else
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8" fill="none"
                                            viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.6">
                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                d="M10.5 1.5H8.25A2.25 2.25 0 006 3.75v16.5a2.25 2.25 0 002.25 2.25h7.5A2.25 2.25 0 0018 20.25V3.75a2.25 2.25 0 00-2.25-2.25H13.5m-3 0V3h3V1.5m-3 0h3m-3 18.75h3" />
                                        </svg>
                                    @endif
                                </div>

                                {{-- INFO --}}
                                <div class="min-w-0 flex-1">
                                    <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
                                        <div>
                                            <p class="text-sm font-bold text-slate-900">
                                                {{ $session->agent->platform() ?: 'Plataforma no identificada' }}
                                                ·
                                                {{ $session->agent->browser() ?: 'Navegador no identificado' }}
                                            </p>

                                            <p class="mt-1 text-xs text-slate-500">
                                                IP: {{ $session->ip_address }}
                                            </p>
                                        </div>

                                        <div class="shrink-0">
                                            @if ($session->is_current_device)
                                                <span class="inline-flex rounded-full bg-emerald-50 px-3 py-1 text-xs font-semibold text-emerald-700 ring-1 ring-emerald-200">
                                                    Este dispositivo
                                                </span>
                                            @else
                                                <span class="inline-flex rounded-full bg-slate-100 px-3 py-1 text-xs font-semibold text-slate-600 ring-1 ring-slate-200">
                                                    Última actividad {{ $session->last_active }}
                                                </span>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif

            {{-- ACCIÓN PRINCIPAL --}}
            <div class="mt-6 flex flex-wrap items-center gap-3">
                <x-button
                    wire:click="confirmLogout"
                    wire:loading.attr="disabled"
                    class="rounded-2xl bg-gradient-to-r from-emerald-600 to-sky-600 px-5 py-3 text-white shadow-lg shadow-emerald-500/20"
                >
                    Cerrar otras sesiones
                </x-button>

                <x-action-message class="text-emerald-700" on="loggedOut">
                    Sesiones cerradas correctamente.
                </x-action-message>
            </div>

            {{-- MODAL DE CONFIRMACIÓN --}}
            <x-dialog-modal wire:model.live="confirmingLogout">
                <x-slot name="title">
                    <div class="flex items-center gap-3">
                        <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-emerald-100 text-emerald-700">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none"
                                viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M15.75 9V5.25A2.25 2.25 0 0013.5 3h-7.5A2.25 2.25 0 003.75 5.25v13.5A2.25 2.25 0 006 21h7.5a2.25 2.25 0 002.25-2.25V15m-6-3h12m0 0-3-3m3 3-3 3" />
                            </svg>
                        </div>

                        <div>
                            <h2 class="text-base font-bold text-slate-900">
                                Confirmar cierre de sesiones
                            </h2>
                            <p class="text-xs text-slate-500">
                                Validación de seguridad
                            </p>
                        </div>
                    </div>
                </x-slot>

                <x-slot name="content">
                    <div class="text-sm leading-6 text-slate-600">
                        Estás a punto de cerrar todas las sesiones abiertas en otros navegadores y dispositivos.
                        Tu sesión actual permanecerá activa.
                    </div>

                    <div class="mt-4 rounded-2xl border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-800">
                        @if ($otrasSesiones > 0)
                            Se {{ $otrasSesiones === 1 ? 'cerrará 1 sesión externa' : 'cerrarán ' . $otrasSesiones . ' sesiones externas' }}.
                            Para continuar, confirma tu contraseña actual.
                        @else
                            No se detectaron otras sesiones activas, pero puedes continuar si deseas verificar el cierre de accesos externos.
                        @endif
                    </div>

                    <div class="mt-5 rounded-2xl border border-slate-200 bg-slate-50 p-4"
                        x-data="{ showPassword: false }"
                        x-on:confirming-logout-other-browser-sessions.window="setTimeout(() => $refs.password.focus(), 250)">
                        <x-label for="password" value="Contraseña actual" />

                        <div class="relative mt-2">
                            <x-input
                                id="password"
                                x-bind:type="showPassword ? 'text' : 'password'"
                                class="block w-full rounded-2xl border-slate-300 pr-12"
                                autocomplete="current-password"
                                placeholder="Ingresa tu contraseña"
                                x-ref="password"
                                wire:model="password"
                                wire:keydown.enter="logoutOtherBrowserSessions"
                            />

                            <button
                                type="button"
                                @click="showPassword = !showPassword"
                                class="absolute inset-y-0 right-0 flex items-center pr-4 text-slate-400 transition hover:text-slate-700"
                                title="Mostrar u ocultar contraseña"
                            >
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

                        <x-input-error for="password" class="mt-2" />
                    </div>
                </x-slot>

                <x-slot name="footer">
                    <x-secondary-button
                        wire:click="$toggle('confirmingLogout')"
                        wire:loading.attr="disabled"
                        class="rounded-2xl"
                    >
                        Cancelar
                    </x-secondary-button>

                    <x-button
                        class="ms-3 rounded-2xl bg-gradient-to-r from-emerald-600 to-sky-600 px-5 py-3 text-white shadow-lg shadow-emerald-500/20"
                        wire:click="logoutOtherBrowserSessions"
                        wire:loading.attr="disabled"
                    >
                        Confirmar cierre
                    </x-button>
                </x-slot>
            </x-dialog-modal>
        </div>
    </x-slot>
</x-action-section>