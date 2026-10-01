<section class="ui-panel space-y-4">
    <h2 class="ui-title text-xl font-bold">Revisión preventiva del borrador</h2>
    <p class="ui-muted">Puedes revisar el texto antes del registro. Este borrador no se guarda y no sustituye la selección autorizada de estudiante, curso y catálogos.</p>
    <form wire:submit="analizar" class="grid gap-4 md:grid-cols-2">
        <label class="ui-label md:col-span-2">Observación<textarea class="ui-input" wire:model.live.debounce.500ms="form.mot_seg" maxlength="2000" rows="4"></textarea>@error('mot_seg')<span class="ui-error">{{ $message }}</span>@enderror</label>
        <label class="ui-label">Contexto<input class="ui-input" wire:model.live.debounce.500ms="form.ori_seg" maxlength="100">@error('ori_seg')<span class="ui-error">{{ $message }}</span>@enderror</label>
        <label class="ui-label">Acción de acompañamiento<textarea class="ui-input" wire:model.live.debounce.500ms="form.pro_acc_seg" maxlength="2000"></textarea>@error('pro_acc_seg')<span class="ui-error">{{ $message }}</span>@enderror</label>
        <button class="ui-btn-secondary justify-self-start" wire:loading.attr="disabled" type="submit">Revisar borrador</button>
    </form>
    <p class="ui-muted" wire:loading role="status">Revisando el texto…</p>
    <x-asistencia-inteligente :analisis="$analisis" />
</section>
