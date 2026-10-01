<section class="ui-panel space-y-4">
    <h2 class="ui-title font-bold text-xl">{{ $publication ? 'Editar publicación' : 'Nueva publicación' }}</h2>
    <p class="ui-muted">Anuncios, instrucciones y recursos del curso.</p>
    <form wire:submit="guardar" class="space-y-4">
        <label class="ui-label block">Título<input class="ui-input" wire:model="tit_pub" maxlength="180" required></label>
        @error('tit_pub')<p class="ui-error">{{ $message }}</p>@enderror
        <label class="ui-label block">Contenido<textarea class="ui-textarea" wire:model="con_pub" rows="4" maxlength="10000" required></textarea></label>
        @error('con_pub')<p class="ui-error">{{ $message }}</p>@enderror
        <label class="ui-label block">Tipo<select class="ui-select" wire:model="tip_pub">@foreach(['ANUNCIO','AVISO','MATERIAL','RECORDATORIO','GENERAL'] as $type)<option>{{ $type }}</option>@endforeach</select></label>
        <label class="ui-label block">Visibilidad<select class="ui-select" wire:model="est_pub"><option value="BORRADOR">Borrador</option><option value="PUBLICADO">Publicar</option><option value="OCULTO">Ocultar</option></select></label>
        @if($publication)<label class="ui-label block">Motivo del cambio<textarea class="ui-textarea" wire:model="motivo" rows="2" maxlength="2000"></textarea></label>@endif
        @error('motivo')<p class="ui-error">{{ $message }}</p>@enderror
        <button type="submit" class="ui-btn-primary" wire:loading.attr="disabled">Guardar publicación</button>
        @if($publication)<button type="button" wire:click="cancelar" class="ui-btn-secondary">Cancelar edición</button>@endif
        <span wire:loading role="status">Guardando información…</span>
    </form>
    <ul class="space-y-2">@foreach($publications as $item)<li wire:key="{{ $item->cod_pub }}" class="flex flex-wrap gap-3 items-center"><span>{{ $item->tit_pub }} · {{ $item->est_pub }}</span><button type="button" wire:click="editar(@js($item->cod_pub))" wire:loading.attr="disabled" class="ui-btn-secondary">Editar</button></li>@endforeach</ul>
    @if($publications->count() === 30)<p class="ui-muted">Se muestran las 30 publicaciones más recientes para edición.</p>@endif
</section>
