<div class="perfil-retirar">
    <div class="perfil-seccion-cabecera">
        <div><p class="ui-kicker">Gestión de acceso</p><h2 class="ui-title mt-2 text-xl font-bold">Retirar mi acceso</h2><p class="ui-muted mt-2 text-sm leading-6">Dejarás de poder iniciar sesión en SAVP. Tus registros institucionales se conservarán.</p></div>
        <button type="button" class="ui-btn perfil-btn-sensible" wire:click="confirmUserDeletion" wire:loading.attr="disabled"><i class="ph-duotone ph-user-minus" aria-hidden="true"></i> Retirar mi acceso</button>
    </div>
    <x-dialog-modal wire:model.live="confirmingUserDeletion" maxWidth="lg">
        <x-slot name="title"><div class="perfil-modal-titulo"><span class="perfil-icono"><i class="ph-duotone ph-shield-check" aria-hidden="true"></i></span><div><h2 class="ui-title text-lg font-bold">Confirma tu decisión</h2><p class="ui-muted text-xs">Verificación de identidad</p></div></div></x-slot>
        <x-slot name="content">
            <p class="ui-muted leading-6">Se cerrará tu sesión y tu cuenta dejará de tener acceso. Los registros académicos e institucionales se conservarán. Para volver a ingresar necesitarás contactar a administración.</p>
            <div class="mt-5" x-data="{ mostrar: false }" x-on:confirming-delete-user.window="$nextTick(() => $refs.password.focus())">
                <label for="retirar-password" class="ui-label">Contraseña actual</label>
                <div class="relative mt-2">
                    <input id="retirar-password" x-ref="password" :type="mostrar ? 'text' : 'password'" class="ui-input w-full pr-12" wire:model="password" autocomplete="current-password" aria-describedby="retirar-password-error" />
                    <button type="button" class="perfil-ver-password" x-on:click="mostrar = !mostrar" :aria-label="mostrar ? 'Ocultar contraseña actual' : 'Mostrar contraseña actual'" :aria-pressed="mostrar" aria-controls="retirar-password"><i class="ph-duotone" :class="mostrar ? 'ph-eye-slash' : 'ph-eye'" aria-hidden="true"></i></button>
                </div>
                <div id="retirar-password-error"><x-input-error for="password" class="mt-2" /></div>
                <label for="retirar-confirmacion" class="ui-label mt-4 block">Escribe RETIRAR para confirmar</label>
                <input id="retirar-confirmacion" type="text" class="ui-input mt-2 w-full" wire:model="confirmacion" autocomplete="off" spellcheck="false" aria-describedby="retirar-confirmacion-error" />
                <div id="retirar-confirmacion-error"><x-input-error for="confirmacion" class="mt-2" /></div>
            </div>
        </x-slot>
        <x-slot name="footer">
            <button type="button" class="ui-btn ui-btn-secondary" wire:click="$set('confirmingUserDeletion', false)">Conservar mi acceso</button>
            <button type="button" class="ui-btn perfil-btn-sensible" wire:click="deleteUser" wire:loading.attr="disabled">Confirmar retiro</button>
        </x-slot>
    </x-dialog-modal>
</div>
