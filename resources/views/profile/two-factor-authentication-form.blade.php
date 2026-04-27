<div x-data="{ mostrarCodigos: @js($showingRecoveryCodes) }">
    <x-action-section>
        <x-slot name="title">
            Autenticación en dos factores
        </x-slot>

        <x-slot name="description">
            Añade una capa adicional de seguridad a tu cuenta utilizando un código de verificación desde una aplicación autenticadora.
        </x-slot>

        <x-slot name="content">
            <div class="rounded-[1.8rem] border border-slate-200 bg-white p-5 shadow-sm sm:p-6">
                {{-- ESTADO --}}
                <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
                    <div>
                        <h3 class="text-lg font-bold text-slate-900">
                            @if ($this->enabled)
                                @if ($showingConfirmation)
                                    Finaliza la activación de la autenticación en dos factores
                                @else
                                    La autenticación en dos factores está activada
                                @endif
                            @else
                                La autenticación en dos factores está desactivada
                            @endif
                        </h3>

                        <p class="mt-2 max-w-2xl text-sm leading-6 text-slate-600">
                            Cuando esta opción está habilitada, además de tu contraseña se solicitará un código temporal generado desde una aplicación autenticadora en tu dispositivo móvil.
                        </p>
                    </div>

                    <div class="shrink-0">
                        @if ($this->enabled)
                            @if ($showingConfirmation)
                                <span class="inline-flex rounded-full bg-amber-50 px-3 py-1 text-xs font-semibold text-amber-700 ring-1 ring-amber-200">
                                    Pendiente de confirmación
                                </span>
                            @else
                                <span class="inline-flex rounded-full bg-emerald-50 px-3 py-1 text-xs font-semibold text-emerald-700 ring-1 ring-emerald-200">
                                    Activa
                                </span>
                            @endif
                        @else
                            <span class="inline-flex rounded-full bg-slate-100 px-3 py-1 text-xs font-semibold text-slate-600 ring-1 ring-slate-200">
                                Desactivada
                            </span>
                        @endif
                    </div>
                </div>

                {{-- CONFIGURACIÓN QR --}}
                @if ($this->enabled && $showingQrCode)
                    <div class="mt-6 rounded-[1.5rem] border border-slate-200 bg-slate-50 p-5">
                        <div class="mb-4">
                            <p class="text-sm font-semibold uppercase tracking-[0.16em] text-sky-700">
                                Configuración
                            </p>
                            <h4 class="mt-2 text-base font-bold text-slate-900">
                                @if ($showingConfirmation)
                                    Escanea el código QR y confirma la activación
                                @else
                                    Configuración disponible
                                @endif
                            </h4>
                        </div>

                        <div class="max-w-3xl text-sm leading-6 text-slate-600">
                            @if ($showingConfirmation)
                                <p>
                                    Escanea el siguiente código QR desde tu aplicación autenticadora y luego introduce el código temporal generado para finalizar la activación.
                                </p>
                            @else
                                <p>
                                    Tu cuenta ya tiene la autenticación en dos factores habilitada. Puedes volver a escanear el QR o usar la clave de configuración si cambias de dispositivo.
                                </p>
                            @endif
                        </div>

                        <div class="mt-5 flex flex-col gap-6 lg:flex-row lg:items-start">
                            {{-- QR --}}
                            <div class="rounded-[1.3rem] border border-slate-200 bg-white p-4 shadow-sm">
                                <div class="inline-block">
                                    {!! $this->user->twoFactorQrCodeSvg() !!}
                                </div>
                            </div>

                            {{-- CLAVE + CONFIRMACIÓN --}}
                            <div class="flex-1 space-y-4">
                                <div class="rounded-2xl border border-slate-200 bg-white p-4">
                                    <p class="text-xs font-semibold uppercase tracking-[0.14em] text-slate-500">
                                        Clave de configuración
                                    </p>
                                    <p class="mt-2 break-all font-mono text-sm font-semibold text-slate-900">
                                        {{ decrypt($this->user->two_factor_secret) }}
                                    </p>
                                </div>

                                @if ($showingConfirmation)
                                    <div class="rounded-2xl border border-amber-200 bg-amber-50 p-4">
                                        <x-label for="code" value="Código de verificación" />

                                        <x-input
                                            id="code"
                                            type="text"
                                            name="code"
                                            class="mt-2 block w-full rounded-2xl border-slate-300 sm:w-72"
                                            inputmode="numeric"
                                            autofocus
                                            autocomplete="one-time-code"
                                            wire:model="code"
                                            wire:keydown.enter="confirmTwoFactorAuthentication"
                                        />

                                        <x-input-error for="code" class="mt-2" />

                                        <p class="mt-3 text-sm text-amber-800">
                                            Introduce el código generado por tu aplicación autenticadora para confirmar la activación.
                                        </p>
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>
                @endif

                {{-- CÓDIGOS DE RECUPERACIÓN --}}
                @if ($this->enabled && $showingRecoveryCodes)
                    <div class="mt-6 rounded-[1.5rem] border border-emerald-200 bg-emerald-50/60 p-5">
                        <div class="flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
                            <div>
                                <p class="text-sm font-semibold uppercase tracking-[0.16em] text-emerald-700">
                                    Recuperación de acceso
                                </p>
                                <h4 class="mt-2 text-base font-bold text-slate-900">
                                    Códigos de recuperación
                                </h4>
                            </div>

                            <div class="flex flex-wrap gap-3">
                                <button
                                    type="button"
                                    @click="mostrarCodigos = !mostrarCodigos"
                                    class="rounded-2xl border border-slate-300 bg-white px-4 py-2 text-sm font-semibold text-slate-700 transition hover:bg-slate-50"
                                >
                                    <span x-show="!mostrarCodigos">Ver códigos</span>
                                    <span x-show="mostrarCodigos" x-cloak>Ocultar códigos</span>
                                </button>

                                <x-confirms-password wire:then="regenerateRecoveryCodes">
                                    <x-secondary-button class="rounded-2xl">
                                        Regenerar códigos
                                    </x-secondary-button>
                                </x-confirms-password>
                            </div>
                        </div>

                        <p class="mt-4 max-w-3xl text-sm leading-6 text-slate-600">
                            Guarda estos códigos en un lugar seguro. Podrás utilizarlos para recuperar el acceso a tu cuenta si pierdes tu dispositivo o no puedes generar el código de verificación temporal.
                        </p>

                        <div x-show="mostrarCodigos" x-transition x-cloak class="mt-5">
                            <div class="grid gap-2 rounded-2xl border border-slate-200 bg-white px-4 py-4 font-mono text-sm text-slate-800 shadow-sm sm:grid-cols-2">
                                @foreach (json_decode(decrypt($this->user->two_factor_recovery_codes), true) as $code)
                                    <div class="rounded-xl bg-slate-50 px-3 py-2 ring-1 ring-slate-200">
                                        {{ $code }}
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                @endif

                {{-- ACCIONES --}}
                <div class="mt-6 flex flex-wrap gap-3">
                    @if (! $this->enabled)
                        <x-confirms-password wire:then="enableTwoFactorAuthentication">
                            <x-button
                                type="button"
                                wire:loading.attr="disabled"
                                class="rounded-2xl bg-gradient-to-r from-emerald-600 to-sky-600 px-5 py-3 text-white shadow-lg shadow-emerald-500/20"
                            >
                                Activar autenticación
                            </x-button>
                        </x-confirms-password>
                    @else
                        @if ($showingConfirmation)
                            <x-confirms-password wire:then="confirmTwoFactorAuthentication">
                                <x-button
                                    type="button"
                                    class="rounded-2xl bg-gradient-to-r from-emerald-600 to-sky-600 px-5 py-3 text-white shadow-lg shadow-emerald-500/20"
                                    wire:loading.attr="disabled"
                                >
                                    Confirmar activación
                                </x-button>
                            </x-confirms-password>

                            <x-confirms-password wire:then="disableTwoFactorAuthentication">
                                <x-secondary-button wire:loading.attr="disabled" class="rounded-2xl">
                                    Cancelar activación
                                </x-secondary-button>
                            </x-confirms-password>
                        @else
                            @if (! $showingRecoveryCodes)
                                <x-confirms-password wire:then="showRecoveryCodes">
                                    <x-secondary-button class="rounded-2xl" x-on:click="mostrarCodigos = true">
                                        Ver códigos de recuperación
                                    </x-secondary-button>
                                </x-confirms-password>
                            @endif

                            <x-confirms-password wire:then="disableTwoFactorAuthentication">
                                <x-danger-button wire:loading.attr="disabled" class="rounded-2xl">
                                    Desactivar autenticación
                                </x-danger-button>
                            </x-confirms-password>
                        @endif
                    @endif
                </div>
            </div>
        </x-slot>
    </x-action-section>
</div>