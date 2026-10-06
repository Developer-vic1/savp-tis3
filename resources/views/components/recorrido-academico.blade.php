@props(['nodos'=>[]])
<div class="pa-recorrido" role="group" aria-label="Conexiones del expediente académico">
    @foreach($nodos as $nodo)<button type="button" class="pa-nodo" data-tono="{{ $nodo['tono'] }}" wire:click="cambiarVista(@js($nodo['vista']))" aria-label="{{ $nodo['nombre'] }}: {{ $nodo['valor'] }}. Consultar {{ $nodo['nombre'] }}.">
        <span class="pa-hexagono"><svg viewBox="0 0 100 110" aria-hidden="true"><polygon points="50,3 96,29 96,81 50,107 4,81 4,29"/></svg><i class="ph-duotone {{ $nodo['icono'] }}" aria-hidden="true"></i><strong>{{ $nodo['valor'] }}</strong></span><span class="pa-nodo-nombre">{{ $nodo['nombre'] }}</span><small>{{ $nodo['detalle'] }}</small><i class="ph-duotone ph-arrow-up-right pa-nodo-flecha" aria-hidden="true"></i>
    </button>@endforeach
</div>
