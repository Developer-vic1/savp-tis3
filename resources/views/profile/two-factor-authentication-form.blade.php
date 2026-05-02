<div x-data="{ mostrarCodigos: @js($showingRecoveryCodes), mostrarClave: false }"
    class="relative overflow-hidden rounded-[1.8rem] border border-[var(--ui-border)] bg-[var(--ui-card)] p-5 shadow-sm sm:p-6">

    {{-- Fondos suaves --}}
    <div class="pointer-events-none absolute -right-24 -top-24 h-64 w-64 rounded-full bg-violet-400/10 blur-3xl"></div>
    <div class="pointer-events-none absolute -bottom-24 left-10 h-64 w-64 rounded-full bg-sky-400/10 blur-3xl"></div>
    <div class="pointer-events-none absolute bottom-0 right-1/3 h-52 w-52 rounded-full bg-emerald-400/10 blur-3xl">
    </div>

    <div class="relative space-y-6">

        {{-- ============================================================
        CABECERA
        ============================================================ --}}
        <div class="flex flex-col gap-5 xl:flex-row xl:items-start xl:justify-between">
            <div class="max-w-3xl">
                <div class="flex flex-wrap items-center gap-2">
                    <span
                        class="inline-flex items-center gap-2 rounded-full border border-violet-200/70 bg-violet-50 px-3 py-1 text-xs font-black uppercase tracking-[0.14em] text-violet-700 dark:border-violet-400/20 dark:bg-violet-400/10 dark:text-violet-300">
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"
                                d="M12 3.75 4.5 6.75v5.625c0 4.038 3.06 7.82 7.5 8.875 4.44-1.055 7.5-4.837 7.5-8.875V6.75L12 3.75Z" />
                        </svg>
                        Seguridad institucional
                    </span>

                    @if ($this->enabled)
                        @if ($showingConfirmation)
                            <span
                                class="inline-flex rounded-full border border-amber-200 bg-amber-50 px-3 py-1 text-xs font-bold text-amber-700 dark:border-amber-400/20 dark:bg-amber-400/10 dark:text-amber-300">
                                Pendiente de confirmación
                            </span>
                        @else
                            <span
                                class="inline-flex rounded-full border border-emerald-200 bg-emerald-50 px-3 py-1 text-xs font-bold text-emerald-700 dark:border-emerald-400/20 dark:bg-emerald-400/10 dark:text-emerald-300">
                                Protección activa
                            </span>
                        @endif
                    @else
                        <span
                            class="inline-flex rounded-full border border-[var(--ui-border)] bg-[var(--ui-soft)] px-3 py-1 text-xs font-bold text-[var(--ui-muted)]">
                            Protección desactivada
                        </span>
                    @endif
                </div>

                <h3 class="mt-4 text-xl font-black tracking-tight text-[var(--ui-text)]">
                    @if ($this->enabled)
                        @if ($showingConfirmation)
                            Finaliza la activación de la verificación en dos pasos
                        @else
                            Verificación en dos pasos activa
                        @endif
                    @else
                        Verificación en dos pasos desactivada
                    @endif
                </h3>

                <p class="mt-2 text-sm leading-7 text-[var(--ui-muted)]">
                    La autenticación en dos factores agrega una capa adicional de protección al acceso institucional.
                    Además de tu contraseña, se solicitará un código temporal generado desde una aplicación
                    autenticadora.
                </p>
            </div>

            <div class="grid grid-cols-2 gap-3 xl:min-w-[320px]">
                <div class="rounded-2xl border border-[var(--ui-border)] bg-[var(--ui-soft)] px-4 py-3">
                    <p class="text-xs font-bold uppercase tracking-[0.12em] text-[var(--ui-muted)]">
                        Estado
                    </p>

                    <p class="mt-1 text-2xl font-black text-[var(--ui-text)]">
                        @if ($this->enabled)
                            @if ($showingConfirmation)
                                Proceso
                            @else
                                Activa
                            @endif
                        @else
                            Inactiva
                        @endif
                    </p>

                    <p class="mt-1 text-xs text-[var(--ui-muted)]">
                        Protección de cuenta
                    </p>
                </div>

                <div class="rounded-2xl border border-[var(--ui-border)] bg-[var(--ui-soft)] px-4 py-3">
                    <p class="text-xs font-bold uppercase tracking-[0.12em] text-[var(--ui-muted)]">
                        Método
                    </p>

                    <p class="mt-1 text-2xl font-black text-[var(--ui-text)]">
                        App
                    </p>

                    <p class="mt-1 text-xs text-[var(--ui-muted)]">
                        Código temporal
                    </p>
                </div>
            </div>
        </div>

        {{-- ============================================================
        AVISO INSTITUCIONAL
        ============================================================ --}}
        <div class="rounded-2xl border px-4 py-4 text-sm leading-6
            @if ($this->enabled)
                border-emerald-200 bg-emerald-50 text-emerald-800 dark:border-emerald-400/20 dark:bg-emerald-400/10 dark:text-emerald-200
            @else
                border-sky-200 bg-sky-50 text-sky-800 dark:border-sky-400/20 dark:bg-sky-400/10 dark:text-sky-200
            @endif">
            @if ($this->enabled)
                @if ($showingConfirmation)
                    Escanea el código QR con tu aplicación autenticadora y confirma el código temporal para completar la
                    activación.
                @else
                    Tu cuenta cuenta con una capa adicional de protección. Conserva tus códigos de recuperación en un lugar
                    seguro.
                @endif
            @else
                Se recomienda activar esta protección en cuentas administrativas, docentes o con acceso a información
                académica sensible.
            @endif
        </div>

        {{-- ============================================================
        CONFIGURACIÓN QR
        ============================================================ --}}
        @if ($this->enabled && $showingQrCode)
            <section class="rounded-[1.5rem] border border-[var(--ui-border)] bg-[var(--ui-soft)] p-5">
                <div class="mb-5">
                    <p class="text-sm font-black uppercase tracking-[0.16em] text-sky-600 dark:text-sky-300">
                        Configuración de seguridad
                    </p>

                    <h4 class="mt-2 text-base font-black text-[var(--ui-text)]">
                        @if ($showingConfirmation)
                            Escanea el código QR y confirma la activación
                        @else
                            Configuración disponible para cambio de dispositivo
                        @endif
                    </h4>

                    <p class="mt-2 max-w-3xl text-sm leading-7 text-[var(--ui-muted)]">
                        @if ($showingConfirmation)
                            Abre tu aplicación autenticadora, escanea el código QR y luego introduce el código temporal
                            generado.
                        @else
                            Tu cuenta ya tiene autenticación en dos factores habilitada. Usa esta información únicamente si
                            necesitas configurar un nuevo dispositivo autenticador.
                        @endif
                    </p>
                </div>

                <div class="grid gap-6 lg:grid-cols-[260px,1fr]">
                    {{-- QR --}}
                    <div class="flex justify-center lg:justify-start">
                        <div class="rounded-[1.3rem] border border-[var(--ui-border)] bg-white p-4 shadow-sm dark:bg-white">
                            <div class="inline-block">
                                {!! $this->user->twoFactorQrCodeSvg() !!}
                            </div>
                        </div>
                    </div>

                    {{-- CLAVE + CONFIRMACIÓN --}}
                    <div class="space-y-4">
                        <div class="rounded-2xl border border-[var(--ui-border)] bg-[var(--ui-card)] p-4">
                            <div class="flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
                                <div>
                                    <p class="text-xs font-bold uppercase tracking-[0.14em] text-[var(--ui-muted)]">
                                        Clave de configuración
                                    </p>
                                    <p class="mt-1 text-xs leading-5 text-[var(--ui-muted)]">
                                        Úsala solo si no puedes escanear el código QR.
                                    </p>
                                </div>

                                <button type="button" @click="mostrarClave = !mostrarClave"
                                    class="rounded-xl border border-[var(--ui-border)] bg-[var(--ui-soft)] px-3 py-2 text-xs font-bold text-[var(--ui-text)] transition hover:border-[var(--ui-primary)] hover:text-[var(--ui-primary)]">
                                    <span x-show="!mostrarClave">Mostrar clave</span>
                                    <span x-show="mostrarClave" x-cloak>Ocultar clave</span>
                                </button>
                            </div>

                            <div x-show="mostrarClave" x-transition x-cloak
                                class="mt-4 rounded-2xl border border-[var(--ui-border)] bg-[var(--ui-soft)] p-4">
                                <p class="break-all font-mono text-sm font-semibold text-[var(--ui-text)]">
                                    {{ decrypt($this->user->two_factor_secret) }}
                                </p>
                            </div>
                        </div>

                        @if ($showingConfirmation)
                            <div
                                class="rounded-2xl border border-amber-200 bg-amber-50 p-4 dark:border-amber-400/20 dark:bg-amber-400/10">
                                <label for="code" class="block text-sm font-bold text-amber-900 dark:text-amber-100">
                                    Código de verificación
                                </label>

                                <input id="code" type="text" name="code" class="ui-input mt-2 block w-full sm:w-80"
                                    inputmode="numeric" autofocus autocomplete="one-time-code" placeholder="Ejemplo: 123456"
                                    wire:model="code" wire:keydown.enter="confirmTwoFactorAuthentication" />

                                <x-input-error for="code" class="mt-2" />

                                <p class="mt-3 text-sm leading-6 text-amber-800 dark:text-amber-200">
                                    Introduce el código temporal generado por tu aplicación autenticadora.
                                </p>
                            </div>
                        @endif
                    </div>
                </div>
            </section>
        @endif

        {{-- ============================================================
        CÓDIGOS DE RECUPERACIÓN
        ============================================================ --}}
        @if ($this->enabled && $showingRecoveryCodes)
            <section
                class="rounded-[1.5rem] border border-emerald-200 bg-emerald-50/70 p-5 dark:border-emerald-400/20 dark:bg-emerald-400/10">
                <div class="flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
                    <div>
                        <p class="text-sm font-black uppercase tracking-[0.16em] text-emerald-700 dark:text-emerald-300">
                            Recuperación de acceso
                        </p>

                        <h4 class="mt-2 text-base font-black text-[var(--ui-text)]">
                            Códigos de recuperación
                        </h4>

                        <p class="mt-2 max-w-3xl text-sm leading-7 text-[var(--ui-muted)]">
                            Guarda estos códigos en un lugar seguro. Permiten recuperar el acceso si pierdes tu dispositivo
                            o no puedes generar el código temporal.
                        </p>
                    </div>

                    <div class="flex flex-wrap gap-3">
                        <button type="button" @click="mostrarCodigos = !mostrarCodigos"
                            class="inline-flex items-center justify-center rounded-2xl border border-[var(--ui-border)] bg-[var(--ui-card)] px-4 py-2 text-sm font-bold text-[var(--ui-text)] transition hover:bg-[var(--ui-soft)]">
                            <span x-show="!mostrarCodigos">Ver códigos</span>
                            <span x-show="mostrarCodigos" x-cloak>Ocultar códigos</span>
                        </button>

                        <x-confirms-password wire:then="regenerateRecoveryCodes">
                            <button type="button"
                                class="inline-flex items-center justify-center rounded-2xl border border-[var(--ui-border)] bg-[var(--ui-card)] px-4 py-2 text-sm font-bold text-[var(--ui-text)] transition hover:bg-[var(--ui-soft)]">
                                Regenerar códigos
                            </button>
                        </x-confirms-password>
                    </div>
                </div>

                <div x-show="mostrarCodigos" x-transition x-cloak class="mt-5">
                    <div
                        class="grid gap-2 rounded-2xl border border-[var(--ui-border)] bg-[var(--ui-card)] px-4 py-4 font-mono text-sm text-[var(--ui-text)] shadow-sm sm:grid-cols-2">
                        @foreach (json_decode(decrypt($this->user->two_factor_recovery_codes), true) as $code)
                            <div class="rounded-xl border border-[var(--ui-border)] bg-[var(--ui-soft)] px-3 py-2">
                                {{ $code }}
                            </div>
                        @endforeach
                    </div>
                </div>
            </section>
        @endif

        {{-- ============================================================
        ACCIONES
        ============================================================ --}}
        <div
            class="flex flex-col gap-3 border-t border-[var(--ui-border)] pt-5 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <p class="text-sm font-black text-[var(--ui-text)]">
                    Control de verificación en dos pasos
                </p>
                <p class="mt-1 text-xs leading-5 text-[var(--ui-muted)]">
                    Las acciones sensibles solicitarán confirmación de contraseña.
                </p>
            </div>

            <div class="flex flex-wrap gap-3">
                @if (!$this->enabled)
                    <x-confirms-password wire:then="enableTwoFactorAuthentication">
                        <button type="button" wire:loading.attr="disabled"
                            class="inline-flex items-center gap-2 rounded-2xl bg-gradient-to-r from-emerald-600 to-sky-600 px-5 py-3 text-sm font-bold text-white shadow-lg shadow-emerald-500/20 transition hover:-translate-y-0.5 hover:shadow-xl disabled:cursor-not-allowed disabled:opacity-60">
                            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"
                                    d="M12 3.75 4.5 6.75v5.625c0 4.038 3.06 7.82 7.5 8.875 4.44-1.055 7.5-4.837 7.5-8.875V6.75L12 3.75Z" />
                            </svg>
                            Activar verificación
                        </button>
                    </x-confirms-password>
                @else
                    @if ($showingConfirmation)
                        <x-confirms-password wire:then="confirmTwoFactorAuthentication">
                            <button type="button" wire:loading.attr="disabled"
                                class="inline-flex items-center gap-2 rounded-2xl bg-gradient-to-r from-emerald-600 to-sky-600 px-5 py-3 text-sm font-bold text-white shadow-lg shadow-emerald-500/20 transition hover:-translate-y-0.5 hover:shadow-xl disabled:cursor-not-allowed disabled:opacity-60">
                                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"
                                        d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                                </svg>
                                Confirmar activación
                            </button>
                        </x-confirms-password>

                        <x-confirms-password wire:then="disableTwoFactorAuthentication">
                            <button type="button" wire:loading.attr="disabled"
                                class="inline-flex items-center justify-center rounded-2xl border border-[var(--ui-border)] bg-[var(--ui-card)] px-5 py-3 text-sm font-bold text-[var(--ui-text)] transition hover:bg-[var(--ui-soft)] disabled:cursor-not-allowed disabled:opacity-60">
                                Cancelar activación
                            </button>
                        </x-confirms-password>
                    @else
                        @if (!$showingRecoveryCodes)
                            <x-confirms-password wire:then="showRecoveryCodes">
                                <button type="button" x-on:click="mostrarCodigos = true"
                                    class="inline-flex items-center justify-center rounded-2xl border border-[var(--ui-border)] bg-[var(--ui-card)] px-5 py-3 text-sm font-bold text-[var(--ui-text)] transition hover:bg-[var(--ui-soft)]">
                                    Ver códigos de recuperación
                                </button>
                            </x-confirms-password>
                        @endif

                        <x-confirms-password wire:then="disableTwoFactorAuthentication">
                            <button type="button" wire:loading.attr="disabled"
                                class="inline-flex items-center gap-2 rounded-2xl bg-gradient-to-r from-rose-600 to-red-700 px-5 py-3 text-sm font-bold text-white shadow-lg shadow-rose-500/20 transition hover:-translate-y-0.5 hover:shadow-xl disabled:cursor-not-allowed disabled:opacity-60">
                                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"
                                        d="M18.364 18.364A9 9 0 0 1 5.636 5.636m12.728 12.728A9 9 0 0 0 5.636 5.636m12.728 12.728L5.636 5.636" />
                                </svg>
                                Desactivar verificación
                            </button>
                        </x-confirms-password>
                    @endif
                @endif
            </div>
        </div>
    </div>
</div>