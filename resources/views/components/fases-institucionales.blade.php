@props(['actual'=>1,'pasos'=>[]])
<ol class="ui-fases" aria-label="Pasos del proceso">
    @foreach($pasos as $numero=>$paso)
    <li aria-current="{{ $actual===$numero?'step':'false' }}" class="{{ $actual===$numero?'ui-fase-actual':'' }}">
        <span class="ui-fase-numero">@if($numero<$actual)<i class="ph-duotone ph-check" aria-hidden="true"></i>@else{{ $numero }}@endif</span>
        <span><i class="ph-duotone {{ $paso['icono'] }}" aria-hidden="true"></i> {{ $paso['titulo'] }}</span>
    </li>
    @endforeach
</ol>
