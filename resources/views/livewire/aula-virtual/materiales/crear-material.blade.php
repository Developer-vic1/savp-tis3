<form wire:submit="guardar" class="ui-panel space-y-4">
    <h2 class="ui-title text-xl font-bold">{{ $editing ? 'Editar material' : 'Nuevo material' }}</h2>
    <label class="ui-label block">Nombre<input class="ui-input" wire:model="nom_mat" required maxlength="180"></label>
    @error('nom_mat')<p class="ui-error">{{ $message }}</p>@enderror
    <label class="ui-label block">Tipo<select class="ui-select" wire:model="tip_mat">@foreach(['DOCUMENTO', 'PDF', 'IMAGEN', 'ENLACE', 'VIDEO', 'OTRO'] as $type)<option>{{ $type }}</option>@endforeach</select></label>
    <label class="ui-label block">Enlace opcional<input class="ui-input" wire:model="url_mat" type="url" maxlength="500"></label>
    @error('url_mat')<p class="ui-error">{{ $message }}</p>@enderror
    @if (!$editing)
    <label class="ui-label block">Archivo opcional (hasta 10 MB)<input class="ui-input" wire:model="archivo" type="file" accept=".pdf,.doc,.docx,.ppt,.pptx,.xls,.xlsx,.jpg,.jpeg,.png,.mp4,.txt"></label>
    @else <p class="ui-muted">El archivo adjunto se conserva. Puedes editar el nombre, el enlace y la visibilidad.</p> @endif
    @error('archivo')<p class="ui-error">{{ $message }}</p>@enderror
    <label class="ui-label block">Visibilidad<select class="ui-select" wire:model="est_mat"><option value="OCULTO">Guardar oculto</option><option value="ACTIVO">Publicar</option></select></label>
    <button type="submit" class="ui-btn-primary" wire:loading.attr="disabled" wire:target="guardar,archivo"><span wire:loading.remove wire:target="guardar">Guardar material</span><span wire:loading wire:target="guardar">Guardando…</span></button>
    <span wire:loading wire:target="archivo" role="status">Subiendo archivo…</span>
</form>
