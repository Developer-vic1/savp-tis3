@props(['identificador', 'titulo', 'icono' => 'caret-down', 'resumen' => '', 'abierto' => false])

<section {{ $attributes->class('ui-desplegable') }} x-data="{abierto:@js($abierto)}">
    <h3>
        <button type="button" class="ui-desplegable-cabecera" x-on:click="abierto=!abierto"
            :aria-expanded="abierto" aria-controls="{{ $identificador }}">
            <i class="ph-duotone ph-{{ $icono }}" aria-hidden="true"></i>
            <span><strong>{{ $titulo }}</strong>@if($resumen)<small>{{ $resumen }}</small>@endif</span>
            <i class="ph-duotone ph-caret-down ui-desplegable-caret" :class="{'ui-desplegable-caret-abierto':abierto}" aria-hidden="true"></i>
        </button>
    </h3>
    <div id="{{ $identificador }}" class="ui-desplegable-cuerpo" :class="{'ui-desplegable-cuerpo-abierto':abierto}"
        :inert="!abierto" :aria-hidden="!abierto">
        <div><div class="ui-desplegable-contenido">{{ $slot }}</div></div>
    </div>
</section>
