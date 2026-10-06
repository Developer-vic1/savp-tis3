@props(['title' => 'Confirma que eres tú', 'content' => 'Ingresa tu contraseña actual para continuar con esta acción.', 'button' => 'Confirmar identidad'])
@php $confirmableId = md5($attributes->wire('then')); @endphp
<span {{ $attributes->wire('then') }} x-data x-ref="span"
    x-on:click="$wire.startConfirmingPassword('{{ $confirmableId }}')"
    x-on:password-confirmed.window="if ($event.detail.id === '{{ $confirmableId }}') $refs.span.dispatchEvent(new CustomEvent('then', { bubbles: false }))">
    {{ $slot }}
</span>
@once
<x-dialog-modal wire:model.live="confirmingPassword" maxWidth="lg">
    <x-slot name="title">
        <div class="perfil-modal-titulo">
            <span class="perfil-icono"><i class="ph-duotone ph-shield-check" aria-hidden="true"></i></span>
            <div><h2 class="ui-title text-lg font-bold">{{ $title }}</h2><p class="ui-muted text-xs">Verificación de seguridad</p></div>
        </div>
    </x-slot>
    <x-slot name="content">
        <p class="ui-muted leading-6">{{ $content }}</p>
        <div class="ui-card-soft mt-5 rounded-xl p-4" x-data="{ mostrar: false }"
            x-on:confirming-password.window="mostrar = false; $nextTick(() => $refs.confirmable_password.focus())">
            <label class="ui-label" for="confirmable_password">Contraseña actual</label>
            <div class="relative mt-2">
                <input id="confirmable_password" x-ref="confirmable_password" :type="mostrar ? 'text' : 'password'" class="ui-input w-full pr-12" placeholder="Ingresa tu contraseña" autocomplete="current-password" wire:model="confirmablePassword" wire:keydown.enter="confirmPassword" aria-describedby="confirmable-password-error" @error('confirmable_password') aria-invalid="true" @enderror />
                <button type="button" class="perfil-ver-password" x-on:click="mostrar = !mostrar" :aria-label="mostrar ? 'Ocultar contraseña actual' : 'Mostrar contraseña actual'" :aria-pressed="mostrar" aria-controls="confirmable_password"><i class="ph-duotone" :class="mostrar ? 'ph-eye-slash' : 'ph-eye'" aria-hidden="true"></i></button>
            </div>
            <div id="confirmable-password-error"><x-input-error for="confirmable_password" class="mt-2" /></div>
            <p class="ui-muted mt-3 text-xs">Puedes cancelar y volver a tu perfil sin realizar cambios.</p>
        </div>
    </x-slot>
    <x-slot name="footer">
        <button type="button" class="ui-btn ui-btn-secondary" wire:click="stopConfirmingPassword" wire:loading.attr="disabled">Cancelar</button>
        <button type="button" class="ui-btn ui-btn-primary" wire:click="confirmPassword" wire:loading.attr="disabled" dusk="confirm-password-button"><span wire:loading.remove wire:target="confirmPassword">{{ $button }}</span><span wire:loading wire:target="confirmPassword">Verificando…</span></button>
    </x-slot>
</x-dialog-modal>
@endonce
