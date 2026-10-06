@props(['datos'=>[], 'modelo', 'etiqueta'=>'Distribución proporcional', 'campoValor'=>'horas', 'unidad'=>'h', 'unidadAccesible'=>'horas', 'descripcionAccion'=>'Filtrar docentes de esta área.', 'mensajeVacio'=>'Sin horas asignadas para representar.'])
@php
    $celdas = [];
    $dividir = function ($grupos,$x,$y,$ancho,$alto) use (&$dividir,&$celdas) {
        if (!$grupos->count()) return;
        if ($grupos->count()===1) {
            $celdas[] = $grupos->first()+['x'=>$x,'y'=>$y,'ancho'=>$ancho,'alto'=>$alto];
            return;
        }
        $total=$grupos->sum('horas'); $corte=1; $suma=$grupos->first()['horas'];
        while($corte<$grupos->count()-1 && abs($suma+$grupos[$corte]['horas']-$total/2)<abs($suma-$total/2)) $suma+=$grupos[$corte++]['horas'];
        $proporcion=$suma/$total;
        if ($ancho*1.8>$alto) {
            $dividir($grupos->take($corte)->values(),$x,$y,$ancho*$proporcion,$alto);
            $dividir($grupos->skip($corte)->values(),$x+$ancho*$proporcion,$y,$ancho*(1-$proporcion),$alto);
        } else {
            $dividir($grupos->take($corte)->values(),$x,$y,$ancho,$alto*$proporcion);
            $dividir($grupos->skip($corte)->values(),$x,$y+$alto*$proporcion,$ancho,$alto*(1-$proporcion));
        }
    };
    $dividir(collect($datos)->map(fn($dato)=>array_merge($dato,['horas'=>(float)($dato[$campoValor]??0)]))->where('horas','>',0)->sortByDesc('horas')->values(),0,0,100,100);
@endphp
<div class="ui-mosaico" role="group" aria-label="{{ $etiqueta }}">
    @forelse($celdas as $celda)<button type="button" style="left:{{ $celda['x'] }}%;top:{{ $celda['y'] }}%;width:{{ $celda['ancho'] }}%;height:{{ $celda['alto'] }}%" wire:click="$set(@js($modelo),@js($celda['nombre']))" title="{{ $celda['nombre'] }} · {{ $celda['horas'] }} {{ $unidadAccesible }}" aria-label="{{ $celda['nombre'] }}: {{ $celda['horas'] }} {{ $unidadAccesible }}. {{ $descripcionAccion }}"><strong>{{ $celda['horas'] }} {{ $unidad }}</strong><span>{{ $celda['nombre'] }}</span></button>@empty<p class="ui-muted text-sm p-4">{{ $mensajeVacio }}</p>@endforelse
</div>
