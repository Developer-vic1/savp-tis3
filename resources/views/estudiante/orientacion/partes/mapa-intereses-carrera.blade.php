@php
    $codigoReferencia = data_get($referenciaIntereses ?? [], 'codigo');
    $areasReferencia = is_string($codigoReferencia) ? str_split($codigoReferencia) : [];
@endphp
<div class="intereses-mapa" role="group" aria-label="Tus intereses y las áreas de esta referencia">
    <div class="intereses-mapa-leyenda"><span><i class="ph-duotone ph-user" aria-hidden="true"></i>Tus respuestas, de 0 a 20</span><span><i class="ph-duotone ph-link" aria-hidden="true"></i>Área de la referencia</span></div>
    <ul class="intereses-mapa-barras" aria-label="Tus seis puntajes de interés">
        @foreach(data_get($perfilComparacion, 'areas', []) as $areaMapa)
            <li @if(in_array($areaMapa['codigo'], $areasReferencia, true)) data-referencia @endif>
                <span class="intereses-mapa-codigo">{{ $areaMapa['codigo'] }}</span><span class="intereses-mapa-nombre">{{ $areaMapa['nombre'] }}</span>
                @if($areaMapa['puntaje'] !== null)
                    <span class="intereses-mapa-pista" aria-hidden="true"><span style="width: {{ $areaMapa['porcentaje'] }}%"></span></span><strong>{{ $areaMapa['puntaje'] }}<small>/20</small></strong>
                @else
                    <span class="intereses-mapa-pista" aria-hidden="true"></span><span>—<span class="sr-only">Sin puntaje vigente</span></span>
                @endif
                <i class="ph-duotone ph-link intereses-mapa-enlace" aria-label="{{ in_array($areaMapa['codigo'], $areasReferencia, true) ? 'Esta área aparece en la referencia' : '' }}" @if(!in_array($areaMapa['codigo'], $areasReferencia, true)) aria-hidden="true" @endif></i>
            </li>
        @endforeach
    </ul>
    @if($codigoReferencia)
        <p class="intereses-texto">Las áreas marcadas pertenecen a la referencia <strong>{{ $codigoReferencia }}</strong>. Las barras muestran tus respuestas; la referencia no aporta seis puntajes para calcular una correlación.</p>
    @else
        <p class="intereses-texto">Aquí puedes ver tus intereses. Esta opción todavía no tiene un código ocupacional verificable en el estudio guardado.</p>
    @endif
</div>
