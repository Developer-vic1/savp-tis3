<div class="relative" x-data="{open:false}" @keydown.escape.window="open=false">
    <button type="button" class="ui-btn-secondary" @click="open=!open" :aria-expanded="open" aria-controls="notification-panel" aria-label="Notificaciones">🔔 @if($available && $unread)<span>{{ $unread }}</span>@endif</button>
    <section id="notification-panel" x-show="open" x-cloak @click.outside="open=false" class="ui-panel absolute right-0 z-40 mt-2 max-h-[70vh] w-72 max-w-[80vw] overflow-y-auto" aria-label="Notificaciones">
        <h2 class="ui-title font-bold">Notificaciones</h2>
        @if(!$available)<p class="ui-muted mt-3" role="status">El centro de notificaciones aún no está habilitado. Puedes consultar tus actividades desde el menú.</p>
        @else
        @forelse($rows as $item)
            <article class="ui-card-soft mt-3 p-3" wire:key="notification-{{ $item->id }}">
                <h3 class="ui-title font-semibold">{{ is_string($item->data['title'] ?? null) ? str($item->data['title'])->limit(150) : 'Aviso institucional' }}</h3>
                <p class="ui-muted">{{ is_string($item->data['message'] ?? null) ? str($item->data['message'])->limit(500) : '' }}</p>
                <p class="ui-muted text-xs">{{ $item->created_at?->format('d/m/Y H:i') }} · {{ $item->read_at ? 'Leída' : 'No leída' }}</p>
                @if($link=$service->link(auth()->user(),$item->data))<a class="underline" href="{{ $link }}">Abrir área autorizada</a>@endif
                <button type="button" class="ui-btn-secondary mt-2" wire:click="mark(@js($item->id), {{ $item->read_at ? 'false' : 'true' }})" wire:loading.attr="disabled" wire:target="mark">{{ $item->read_at ? 'Marcar no leída' : 'Marcar leída' }}</button>
            </article>
        @empty<p class="ui-muted mt-3">No tienes avisos institucionales registrados.</p>@endforelse
        {{ $rows->links() }}
        @endif
        <p wire:loading wire:target="mark,gotoPage,nextPage,previousPage" class="ui-muted mt-3" role="status">Actualizando notificaciones…</p>
    </section>
</div>
