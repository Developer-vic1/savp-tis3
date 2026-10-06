<div class="personas-fila-acciones">
    <button type="button" class="personas-accion" wire:click="abrirModalVer('{{ $persona->cod_per }}')" aria-label="Ver ficha de {{ $this->nombreCompleto($persona) }}" title="Ver ficha"><i class="ph-duotone ph-eye" aria-hidden="true"></i></button>
    @can('update', $persona)
    <button type="button" class="personas-accion" wire:click="abrirModalEditar('{{ $persona->cod_per }}')" aria-label="Editar a {{ $this->nombreCompleto($persona) }}" title="Editar"><i class="ph-duotone ph-pencil-simple" aria-hidden="true"></i></button>
    @endcan
</div>
