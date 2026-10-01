<form wire:submit="guardar" class="ui-panel space-y-4">
    <h2 class="ui-title text-xl font-bold">{{ $editing ? 'Editar tarea' : 'Nueva tarea' }}</h2>
    <label class="ui-label block">Título<input class="ui-input" wire:model="tit_tar" maxlength="180" required></label>
    @error('tit_tar')<p class="ui-error">{{ $message }}</p>@enderror
    <label class="ui-label block">Instrucciones<textarea class="ui-textarea" wire:model="des_tar" maxlength="10000" rows="4"></textarea></label>
    <label class="ui-label block">Tipo<select class="ui-select" wire:model="tip_tar">@foreach(['TAREA', 'PRACTICA', 'PROYECTO', 'INVESTIGACION', 'LABORATORIO', 'EVALUACION'] as $type)<option>{{ $type }}</option>@endforeach</select></label>
    <div class="grid gap-4 sm:grid-cols-2">
        <label class="ui-label">Fecha límite<input class="ui-input" wire:model="fec_lim_tar" type="datetime-local"></label>
        <label class="ui-label">Puntaje máximo<input class="ui-input" wire:model="pun_max_tar" type="number" min="1" max="1000" required></label>
    </div>
    @error('fec_lim_tar')<p class="ui-error">{{ $message }}</p>@enderror
    @error('pun_max_tar')<p class="ui-error">{{ $message }}</p>@enderror
    <label class="ui-label flex gap-2"><input type="checkbox" wire:model="perm_ent_tardia">Permitir entregas tardías</label>
    @if(!$editing)
        <label class="ui-label block">Archivo de instrucciones opcional (hasta 10 MB)<input class="ui-input" wire:model="archivo" type="file"></label>
        @error('archivo')<p class="ui-error">{{ $message }}</p>@enderror
        <span wire:loading wire:target="archivo" role="status">Subiendo archivo…</span>
    @endif
    <label class="ui-label block">Estado<select class="ui-select" wire:model="est_tar"><option value="BORRADOR">Borrador</option><option value="PUBLICADA">Publicar</option>@if($editing)<option value="CERRADA">Cerrar</option>@endif</select></label>
    @error('est_tar')<p class="ui-error">{{ $message }}</p>@enderror
    @if($editing)
        <label class="ui-label block">Motivo del cambio<textarea class="ui-textarea" wire:model="motivo" maxlength="2000" rows="2"></textarea></label>
        @error('motivo')<p class="ui-error">{{ $message }}</p>@enderror
    @endif
    <button type="submit" class="ui-btn-primary" wire:loading.attr="disabled" wire:target="guardar,archivo"><span wire:loading.remove wire:target="guardar">Guardar tarea</span><span wire:loading wire:target="guardar">Guardando…</span></button>
</form>
