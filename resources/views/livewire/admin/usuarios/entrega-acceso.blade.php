<div class="ui-card-soft p-4 usuarios-entrega">
    <h3 class="ui-title font-bold text-sm">¿Dónde recibirá su acceso?</h3>
    <p class="ui-muted mt-2 text-xs">Correo registrado en Personas: {{ $this->correoPersonalSugerido() ?: 'No registrado' }}.</p>
    <label for="{{ $prefijo }}-entrega" class="ui-label mt-4 block">Correo de entrega *</label>
    <input id="{{ $prefijo }}-entrega" class="ui-input mt-2 w-full" type="email" wire:model.live.debounce.450ms="correoEntrega" maxlength="255" required @readonly(!$cambiarCorreoEntrega) />
    <x-input-error for="correoEntrega" />
    @if(!$cambiarCorreoEntrega)<button type="button" class="ui-btn ui-btn-secondary mt-3" wire:click="habilitarCambioCorreoEntrega"><i class="ph-duotone ph-pencil-simple" aria-hidden="true"></i>Cambiar correo de entrega</button>
    @else
    <button type="button" class="personas-enlace mt-3" wire:click="restaurarCorreoEntrega">Usar el correo registrado</button>
    <label for="{{ $prefijo }}-motivo" class="ui-label mt-4 block">Motivo del cambio *</label>
    <textarea id="{{ $prefijo }}-motivo" class="ui-input mt-2 w-full" wire:model.live.debounce.450ms="motivoCorreo" maxlength="500" rows="2" placeholder="Describe por qué se usará otro correo." @required($this->requiereMotivoCorreo())></textarea>
    <p class="ui-muted mt-2 text-xs">Explica el motivo con al menos 10 caracteres. Se guardará en la descripción de bitácora.</p>
    <x-input-error for="motivoCorreo" />
    @endif
    <label class="ui-muted mt-4 flex items-start gap-2 text-sm"><input type="checkbox" wire:model.live="entregaAutorizada" required /><span>La persona autorizó recibir el acceso en este correo.</span></label>
    <x-input-error for="entregaAutorizada" />
</div>
