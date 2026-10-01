<div>
    <input aria-label="Fecha de nacimiento" wire:model.live="form.fec_nac_per">
    <x-asistencia-inteligente :analisis="$analisisPersona" />
    <x-asistencia-inteligente :analisis="$analisisPersonaEditar" />
</div>
