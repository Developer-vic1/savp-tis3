<div class="personas-acciones personal-accesos">
    @if($registro->docente)<button type="button" class="ui-btn ui-btn-primary" wire:click="abrirFichaPersonal(@js($registro->getKey()), 'carga')" aria-label="Ver carga horaria de {{ $nombre }}"><i class="ph-duotone ph-calendar-dots" aria-hidden="true"></i>Horario y carga</button>@endif
    <button type="button" class="ui-btn ui-btn-secondary" wire:click="abrirFichaPersonal(@js($registro->getKey()))" aria-label="Ver ficha institucional de {{ $nombre }}"><i class="ph-duotone ph-identification-card" aria-hidden="true"></i>Ficha</button>
    <button type="button" class="personas-accion" wire:click="abrirFichaPersonal(@js($registro->getKey()), 'historial')" aria-label="Ver historial y documentos de {{ $nombre }}" title="Historial y documentos"><i class="ph-duotone ph-files" aria-hidden="true"></i></button>
</div>
