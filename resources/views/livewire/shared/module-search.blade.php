<div class="relative w-full">
    <label class="sr-only" for="module-search">Buscar áreas de trabajo autorizadas</label>
    <input id="module-search" type="search" wire:model.live.debounce.300ms="search" maxlength="100"
        placeholder="Buscar áreas de trabajo…" class="ui-input w-full" autocomplete="off">
    <span wire:loading wire:target="search" class="ui-muted text-xs" role="status">Buscando…</span>
    @if (trim($search) !== '')
        <div class="ui-card absolute left-0 right-0 z-50 mt-2 max-h-72 overflow-y-auto p-2" role="region" aria-label="Resultados de búsqueda">
            @forelse ($links as $link)
                <a class="block rounded-lg px-3 py-2 hover:bg-[var(--ui-primary-soft)]"
                    href="{{ route($link['route'], $link['params']) }}">{{ $link['label'] }}</a>
            @empty
                <p class="ui-muted p-3 text-sm">No hay áreas disponibles para esta búsqueda.</p>
            @endforelse
        </div>
    @endif
</div>
