<section class="ui-panel space-y-4">
    <h2 class="ui-title text-xl font-bold">Buscar fuentes académicas</h2>
    <form wire:submit="buscar" class="space-y-3"><label class="ui-label block" for="academic-source-query">Carrera, institución o tema<input id="academic-source-query" class="ui-input" type="search" maxlength="1000" wire:model="query" required></label>@error('query')<p class="ui-error">{{ $message }}</p>@enderror
        <button type="submit" class="ui-btn-primary" wire:loading.attr="disabled" wire:target="buscar"><span wire:loading.remove wire:target="buscar">Buscar fuentes</span><span wire:loading wire:target="buscar">Consultando corpus…</span></button>
    </form>
    @if($statusMessage)<p class="ui-alert-info" role="status">{{ $statusMessage }}</p>@endif
    @foreach($sources as $source)<article class="ui-card-soft p-4" wire:key="source-{{ $loop->index }}"><h3 class="ui-title font-bold">{{ $source['title'] }}</h3><p class="ui-muted mt-2">{{ $source['institution'] }} · {{ $source['source_type']??'Fuente académica' }} · {{ $source['publication_date']??'Fecha no informada' }}</p><p class="mt-2">{{ $source['summary'] }}</p>
        <p class="ui-muted mt-2">{{ $source['official'] ? 'Marcada como oficial en el corpus; verifica la referencia original.' : 'No marcada como oficial en el corpus.' }}</p>
        @if(filter_var($source['reference'], FILTER_VALIDATE_URL) && in_array(strtolower(parse_url($source['reference'],PHP_URL_SCHEME)),['http','https'],true))<a class="underline" href="{{ $source['reference'] }}" target="_blank" rel="noopener noreferrer">Abrir referencia original</a>@else<p class="ui-muted">Referencia: {{ $source['reference'] }}</p>@endif
    </article>@endforeach
</section>
