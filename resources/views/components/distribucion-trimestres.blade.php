@props(['periodos'=>[], 'anio'])
@php
    $base=\Carbon\CarbonImmutable::create((int)$anio,1,1)->startOfDay();
    $limite=$base->endOfYear()->startOfDay();
    $diasAnio=$base->diffInDays($limite)+1;
@endphp
<div class="pa-trimestres" aria-label="Distribución de trimestres {{ $anio }}">
    <div class="pa-eje-anual" aria-hidden="true">@foreach(['Ene','Feb','Mar','Abr','May','Jun','Jul','Ago','Sep','Oct','Nov','Dic'] as $mes)<span>{{ $mes }}</span>@endforeach</div>
    @forelse($periodos as $p)
    @php
        $inicio=$p['fecha_inicio']?\Carbon\CarbonImmutable::parse($p['fecha_inicio'])->startOfDay():null;
        $fin=$p['fecha_fin']?\Carbon\CarbonImmutable::parse($p['fecha_fin'])->startOfDay():null;
        $representable=$inicio && $fin && $inicio<=$fin && $inicio<=$limite && $fin>=$base;
        $izquierda=$representable?max(0,$base->diffInDays($inicio,false))/$diasAnio*100:0;
        $ancho=$representable?(max($inicio,$base)->diffInDays(min($fin,$limite))+1)/$diasAnio*100:0;
    @endphp
    <div class="pa-rango-anual" data-tono="{{ $p['orden'] }}" role="img" aria-label="{{ $p['nombre'] }}: {{ $inicio?->format('d/m/Y')??'inicio pendiente' }} a {{ $fin?->format('d/m/Y')??'cierre pendiente' }}">
        @if($representable)<span style="left:{{ $izquierda }}%;width:{{ $ancho }}%">T{{ $p['orden'] }}</span>@else<span class="pa-rango-pendiente">{{ $p['nombre'] }} · Fechas por confirmar</span>@endif
    </div>
    @empty<p class="ui-muted text-sm">No hay trimestres registrados en esta gestión.</p>@endforelse
    <p class="ui-muted text-xs mt-3">La estimación cuenta días laborables y eventos generales confirmados. No sustituye los días efectivos establecidos por la norma.</p>
    <div class="pa-periodos-tarjetas">@foreach($periodos as $p)<article data-tono="{{ $p['orden'] }}"><div class="pa-periodo-cabecera"><span>T{{ $p['orden'] }}</span><strong>{{ $p['nombre'] }}</strong></div><p class="ui-muted text-xs">{{ $p['fecha_inicio']?\Carbon\Carbon::parse($p['fecha_inicio'])->format('d/m/Y'):'Inicio pendiente' }} — {{ $p['fecha_fin']?\Carbon\Carbon::parse($p['fecha_fin'])->format('d/m/Y'):'Cierre pendiente' }}</p>@if(is_numeric($p['progreso']))<div class="pa-periodo-progreso" role="progressbar" aria-label="Avance temporal de {{ $p['nombre'] }}" aria-valuenow="{{ $p['progreso'] }}" aria-valuemin="0" aria-valuemax="100"><span style="width:{{ $p['progreso'] }}%"></span></div><p class="ui-muted text-xs">{{ $p['progreso'] }}% del rango temporal</p>@else<p class="ui-muted text-xs">Avance pendiente de fechas.</p>@endif<p class="ui-muted text-xs">{{ ucfirst(mb_strtolower($p['estado'])) }} · {{ $p['dias_habiles_referencia']!==null?$p['dias_habiles_referencia'].' días previstos registrados':(isset($p['dias_estimados'])?$p['dias_estimados'].' días estimados por calendario':'Fechas pendientes para calcular días') }}</p></article>@endforeach</div>
</div>
