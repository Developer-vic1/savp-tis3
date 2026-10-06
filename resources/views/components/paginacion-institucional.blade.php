@props(['paginador', 'elementos', 'cantidad', 'entidad' => 'personas', 'singular' => 'persona'])
<div class="ui-card ui-paginacion" wire:key="paginacion-{{ $entidad }}-{{ $paginador->currentPage() }}-{{ $cantidad }}">
    <div class="ui-paginacion-resumen">
        <span class="ui-paginacion-icono"><i class="ph-duotone ph-stack" aria-hidden="true"></i></span>
        <p class="ui-muted text-sm" role="status"><strong class="ui-title">{{ $paginador->firstItem() ?? 0 }}–{{ $paginador->lastItem() ?? 0 }}</strong> de {{ $paginador->total() }} {{ $paginador->total() === 1 ? $singular : $entidad }}<span>Página {{ $paginador->currentPage() }} de {{ $paginador->lastPage() }}</span></p>
    </div>
    <div class="ui-paginacion-cantidad" role="group" aria-label="{{ ucfirst($entidad) }} por página">
        <span class="ui-muted text-xs">Por página</span>
        <div class="ui-paginacion-tipos-vista">@foreach([10,20,50] as $numero)<button type="button" aria-label="Mostrar {{ $numero }} {{ $entidad }} por página" aria-pressed="{{ $cantidad === $numero ? 'true' : 'false' }}" x-on:click="cambiarCantidad({{ $numero }})" :disabled="cargandoPagina" wire:loading.attr="disabled" wire:target="perPage,gotoPage">{{ $numero }}</button>@endforeach</div>
    </div>
    @if($paginador->hasPages())
    <nav class="ui-paginacion-paginas" aria-label="Páginas del registro de {{ $entidad }}">
        <button type="button" aria-label="Página anterior"  x-on:click="navegarPagina({{ $paginador->currentPage() - 1 }}, '{{ $paginador->getPageName() }}')" :disabled="cargandoPagina || {{ $paginador->onFirstPage() ? 'true' : 'false' }}" wire:loading.attr="disabled" wire:target="gotoPage,perPage"><i class="ph-duotone ph-caret-left" aria-hidden="true"></i></button>
        @foreach(array_filter($elementos) as $element)
            @if(is_string($element))<span class="ui-paginacion-puntos" aria-hidden="true">…</span>@else
                @foreach($element as $pagina => $url)
                    <button type="button" aria-label="Ir a la página {{ $pagina }}" @if($pagina === $paginador->currentPage()) aria-current="page" @endif x-on:click="navegarPagina({{ $pagina }}, '{{ $paginador->getPageName() }}')" :disabled="cargandoPagina" wire:loading.attr="disabled" wire:target="gotoPage,perPage">{{ $pagina }}</button>
                @endforeach
            @endif
        @endforeach
        <button type="button" aria-label="Página siguiente"  x-on:click="navegarPagina({{ $paginador->currentPage() + 1 }}, '{{ $paginador->getPageName() }}')" :disabled="cargandoPagina || {{ !$paginador->hasMorePages() ? 'true' : 'false' }}" wire:loading.attr="disabled" wire:target="gotoPage,perPage"><i class="ph-duotone ph-caret-right" aria-hidden="true"></i></button>
    </nav>
    @endif
    <x-input-error for="perPage" />
</div>
