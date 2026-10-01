<div>
    @if($detail)
        <div class="fixed inset-0 z-50 flex justify-end" x-data="{previous: document.activeElement, destroy() { this.previous?.focus(); }}" x-init="$nextTick(() => $refs.close.focus())" @keydown.escape.window="$wire.close()">
            <button type="button" class="absolute inset-0 bg-black/50" wire:click="close" aria-label="Cerrar detalle"></button>
            <section class="ui-panel relative m-0 h-full w-full max-w-xl overflow-y-auto rounded-none p-6 shadow-xl" role="dialog" aria-modal="true" aria-labelledby="institutional-detail-title" @keydown.tab.prevent="const items=[...$el.querySelectorAll('button,a[href],input,select,textarea,[tabindex]')].filter(el=>!el.disabled); const i=items.indexOf(document.activeElement); items[(i+($event.shiftKey?-1:1)+items.length)%items.length]?.focus()">
                <header class="flex items-start justify-between gap-4"><div><p class="ui-kicker">Detalle de solo lectura</p><h2 id="institutional-detail-title" class="ui-title text-xl font-bold">{{ $detail['title'] }}</h2></div><button type="button" x-ref="close" wire:click="close" class="ui-btn-secondary">Cerrar</button></header>
                <dl class="ui-card-soft mt-5 p-4">@foreach($detail['fields'] as $label => $value)<div class="mb-3"><dt class="ui-muted text-sm">{{ $label }}</dt><dd class="ui-title">{{ $value ?? 'Sin datos registrados' }}</dd></div>@endforeach</dl>
                <h3 class="ui-title mt-6 font-bold">{{ $detail['itemsTitle'] }}</h3>
                @forelse($detail['items'] as $item)<p class="ui-card-soft mt-2 p-3">{{ $item }}</p>@empty<p class="ui-muted mt-2">No hay registros para este contexto.</p>@endforelse
                @if($detail['truncated'])<p class="ui-alert-info mt-3">Se muestran los primeros 50 registros.</p>@endif
            </section>
        </div>
    @endif
</div>
