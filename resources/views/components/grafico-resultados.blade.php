@props(['identificador', 'titulo', 'descripcion', 'datos'])
<article {{ $attributes->class(['ui-card resultados-grafico p-5']) }} aria-labelledby="{{ $identificador }}-titulo">
    <h2 id="{{ $identificador }}-titulo" class="ui-title font-black">{{ $titulo }}</h2>
    <p class="ui-help mt-2">{{ $descripcion }}</p>
    @if(count($datos['labels']) && collect($datos['series'])->contains(fn($s) => collect($s['data'])->contains(fn($v) => $v !== null)) && (($datos['tipo'] ?? '') !== 'doughnut' || collect($datos['series'][0]['data'])->sum() > 0))
        <div wire:key="{{ $identificador }}-{{ md5(json_encode($datos)) }}" x-data="graficoResultados(@js($datos))">
            <div class="resultados-lienzo mt-4" wire:ignore style="--filas:{{ max(3,count($datos['labels'])) }}">
                <canvas x-ref="canvas" role="img" aria-label="{{ $titulo }}. {{ $descripcion }} Valores en el apartado siguiente." aria-describedby="{{ $identificador }}-valores"></canvas>
            </div>
            <p x-cloak x-show="fallo" class="ui-alert-warning mt-3" role="status">El gráfico no pudo cargarse. Puedes consultar los valores debajo.</p>
        </div>
    @else
        <div class="resultados-vacio mt-4"><i class="ph-duotone ph-chart-bar text-3xl" aria-hidden="true"></i><strong>Sin datos para esta comparativa</strong><p class="ui-help">Ajusta los filtros o consulta otra gestión. La ausencia de registros no se representa como una nota cero.</p></div>
    @endif
    <details id="{{ $identificador }}-valores" class="resultados-valores mt-4">
        <summary>Consultar valores · {{ $datos['unidad'] }}</summary>
        <div class="overflow-x-auto mt-3" tabindex="0" role="region" aria-label="Valores de {{ $titulo }}">
            <table class="resultados-tabla w-full text-sm"><caption class="sr-only">{{ $titulo }} · {{ $datos['unidad'] }}</caption><thead><tr><th scope="col">Grupo / período</th>@foreach($datos['series'] as $serie)<th scope="col">{{ $serie['label'] }}</th>@endforeach</tr></thead><tbody>
            @forelse($datos['labels'] as $indice=>$etiqueta)<tr><th scope="row">{{ $etiqueta }}</th>@foreach($datos['series'] as $serie)<td>{{ $serie['data'][$indice] === null ? 'Sin datos' : number_format($serie['data'][$indice],2) }}</td>@endforeach</tr>@empty<tr><td colspan="{{ count($datos['series'])+1 }}">No hay registros en esta consulta.</td></tr>@endforelse
            </tbody></table>
        </div>
    </details>
</article>
