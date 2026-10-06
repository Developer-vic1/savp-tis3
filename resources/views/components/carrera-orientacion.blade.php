@props(['nombre', 'url'=>null, 'institucion'=>'', 'registrada'=>false])
@php
    $texto=mb_strtolower($nombre);
    $icono=match(true){str_contains($texto,'medic'),str_contains($texto,'salud')=>'ph-heartbeat',str_contains($texto,'ingenier'),str_contains($texto,'sistema')=>'ph-cpu',str_contains($texto,'derecho')=>'ph-scales',str_contains($texto,'econom'),str_contains($texto,'admin'),str_contains($texto,'contad')=>'ph-chart-line-up',str_contains($texto,'dise'),str_contains($texto,'arquitect')=>'ph-pencil-ruler',default=>'ph-graduation-cap'};
@endphp
<article class="ga-carrera-tarjeta">
    <span class="ga-carrera-icono"><i class="ph-duotone {{ $icono }}" aria-hidden="true"></i></span>
    <div class="ga-carrera-contenido"><span class="{{ $registrada?'ui-badge-success':'ui-badge-info' }}">{{ $registrada?'En el estudio':'Por revisar' }}</span><h4 class="ui-title">{{ $nombre }}</h4>
        @if($url)<div class="ga-carrera-acciones"><a class="ui-link" href="{{ $url }}" target="_blank" rel="noopener noreferrer">Ver carrera <i class="ph-duotone ph-arrow-square-out" aria-hidden="true"></i></a>
        @can('conocimiento.proponer')<a class="ui-btn ui-btn-secondary" href="{{ route('conocimiento.fuentes.index',['url'=>$url,'carrera'=>$nombre,'institucion'=>$institucion]) }}" target="_blank" rel="noopener noreferrer"><i class="ph-duotone ph-book-open-text" aria-hidden="true"></i>Preparar estudio</a>@endcan</div>@endif
    </div>
</article>
