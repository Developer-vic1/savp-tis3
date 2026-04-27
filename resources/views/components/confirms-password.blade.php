@props([
    'title' => 'Confirmar contraseña',
    'content' => 'Por seguridad, debes ingresar tu contraseña para continuar con esta acción.',
    'button' => 'Confirmar'
])

@php
    $confirmableId = md5($attributes->wire('then'));
@endphp

<span
    {{ $attributes->wire('then') }}
    x-data
    x-ref="span"
    x-on:click="$wire.startConfirmingPassword('{{ $confirmableId }}')"
    x-on:password-confirmed.window="
        setTimeout(() => {
            if ($event.detail.id === '{{ $confirmableId }}') {
                $refs.span.dispatchEvent(new CustomEvent('then', { bubbles: false }))
            }
        }, 250)
    "
>
    {{ $slot }}
</span>

@once
<x-dialog-modal wire:model.live="confirmingPassword">
    {{-- HEADER --}}
    <x-slot name="title">
        <div class="flex items-center gap-3">
            <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-emerald-100 text-emerald-700">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none"
                    viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                    <path stroke-linecap="round" stroke-linejoin="round"
                        d="M12 11c0-.828.672-1.5 1.5-1.5S15 10.172 15 11s-.672 1.5-1.5 1.5S12 11.828 12 11z" />
                    <path stroke-linecap="round" stroke-linejoin="round"
                        d="M17 11V7a5 5 0 00-10 0v4M5 11h14v8H5v-8z" />
                </svg>
            </div>

            <div>
                <h2 class="text-base font-bold text-slate-900">
                    {{ $title }}
                </h2>
                <p class="text-xs text-slate-500">
                    Verificación de seguridad
                </p>
            </div>
        </div>
    </x-slot>

    {{-- CONTENT --}}
    <x-slot name="content">
        <div class="mt-2 text-sm leading-6 text-slate-600">
            {{ $content }}
        </div>

        <div
            class="mt-5 rounded-2xl border border-slate-200 bg-slate-50 p-4"
            x-data="{ showPassword: false }"
            x-on:confirming-password.window="setTimeout(() => $refs.confirmable_password.focus(), 250)"
        >
            <x-label for="confirmable_password" value="Contraseña actual" />

            <div class="relative mt-2">
                <x-input
                    id="confirmable_password"
                    x-bind:type="showPassword ? 'text' : 'password'"
                    class="block w-full rounded-2xl border-slate-300 pr-12 focus:border-emerald-500 focus:ring-emerald-500"
                    placeholder="Ingresa tu contraseña"
                    autocomplete="current-password"
                    x-ref="confirmable_password"
                    wire:model.live="confirmablePassword"
                    wire:keydown.enter="confirmPassword"
                />

                <button
                    type="button"
                    @click="showPassword = !showPassword"
                    class="absolute inset-y-0 right-0 flex items-center pr-4 text-slate-400 transition hover:text-slate-700"
                    title="Mostrar u ocultar contraseña"
                >
                    {{-- Ojo abierto --}}
                    <svg x-show="!showPassword" xmlns="http://www.w3.org/2000/svg" class="h-5 w-5"
                        fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7Z" />
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M12 15.75A3.75 3.75 0 1 0 12 8.25a3.75 3.75 0 0 0 0 7.5Z" />
                    </svg>

                    {{-- Ojo oculto --}}
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

            <x-input-error for="confirmable_password" class="mt-2" />

            <p class="mt-3 text-xs text-slate-500">
                Esta acción requiere validar tu identidad antes de continuar.
            </p>
        </div>
    </x-slot>

    {{-- FOOTER --}}
    <x-slot name="footer">
        <div class="flex gap-3" x-data>
            <x-secondary-button
                wire:click="stopConfirmingPassword"
                wire:loading.attr="disabled"
                class="rounded-2xl"
            >
                Cancelar
            </x-secondary-button>

            <button
                wire:click="confirmPassword"
                wire:loading.attr="disabled"
                dusk="confirm-password-button"
                x-bind:disabled="!$wire.confirmablePassword || $wire.confirmablePassword.trim().length < 3"
                x-bind:class="($wire.confirmablePassword && $wire.confirmablePassword.trim().length >= 3)
                    ? 'bg-gradient-to-r from-emerald-600 to-sky-600 text-white shadow-lg shadow-emerald-500/20 hover:opacity-95'
                    : 'bg-slate-200 text-slate-400 cursor-not-allowed'"
                class="rounded-2xl px-5 py-3 text-sm font-semibold transition"
            >
                {{ $button }}
            </button>
        </div>
    </x-slot>
</x-dialog-modal>
@endonce