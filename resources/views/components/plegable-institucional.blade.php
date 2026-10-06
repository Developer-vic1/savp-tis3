@props(['etiqueta' => '', 'descripcion' => null, 'icono' => 'ph-folder-open', 'abierto' => false, 'compacto' => false])

{{-- Conserva la semántica nativa, el teclado y los atributos de Alpine/Livewire del contexto. --}}
<details {{ $attributes->class(['ui-plegable', 'ui-plegable-compacto' => $compacto]) }} @if($abierto) open @endif>
    <summary class="ui-plegable-resumen">
        <span class="ui-plegable-icono"><i class="ph-duotone {{ $icono }}" aria-hidden="true"></i></span>
        <span class="ui-plegable-texto">
            <span class="ui-plegable-titulo">{{ $titulo ?? $etiqueta }}</span>
            @if($descripcion)<span class="ui-plegable-descripcion">{{ $descripcion }}</span>@endif
        </span>
        @isset($contador)<span class="ui-plegable-contador">{{ $contador }}</span>@endisset
        <i class="ph-duotone ph-caret-down ui-plegable-flecha" aria-hidden="true"></i>
    </summary>
    <div class="ui-plegable-contenido">{{ $slot }}</div>
</details>
