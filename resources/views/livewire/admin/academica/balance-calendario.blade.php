@if($panelAcademico['dias'])
@php
    $balanceDias=$panelAcademico['dias'];
    $objetivoDias=(int)($gestionSeleccionada['anio']??0)===2026?200:null;
    $proporcionDias=$objetivoDias?min(100,100*$balanceDias['estimados']/$objetivoDias):0;
@endphp
<article class="ui-card ga-balance-calendario" x-show="seccion!=='impacto'"><header><div><p class="ui-kicker">Lectura del calendario</p><h2 class="ui-title">Jornadas previstas</h2></div><i class="ph-duotone ph-calendar-check" aria-hidden="true"></i></header><div class="ga-balance-principal"><strong>{{ $balanceDias['estimados'] }}</strong><div><span>días estimados</span><small>Con los eventos registrados</small></div></div>
@if($objetivoDias)<div class="ga-balance-pista" role="img" aria-label="{{ $balanceDias['estimados'] }} días estimados frente a {{ $objetivoDias }} días efectivos de referencia. El cálculo no certifica cumplimiento."><span style="width:{{ $proporcionDias }}%"></span></div><div class="ga-balance-meta"><span>Referencia curricular</span><strong>{{ $objetivoDias }} días efectivos</strong></div>@endif
<p class="ui-muted text-xs mt-3">Estimación por revisar: no acredita días efectivos de clase.</p><dl><div><dt>Base de lunes a viernes</dt><dd>{{ $balanceDias['base'] }}</dd></div><div><dt>{{ $balanceDias['ajuste']>=0?'Descontados por eventos':'Recuperaciones adicionales netas' }}</dt><dd>{{ abs($balanceDias['ajuste']) }}</dd></div></dl>
<x-plegable-institucional icono="ph-info"><x-slot:titulo>¿Cómo se calcula?</x-slot:titulo><p>Parte del rango registrado, descuenta suspensiones generales confirmadas y considera recuperaciones lectivas. Los eventos de grupos concretos y feriados pendientes necesitan revisión.</p>@if($objetivoDias)<p>La referencia procede de la RM 0001/2026. Este cálculo estima jornadas del calendario; no certifica días efectivos ni cumplimiento ministerial.</p>@else<p>Contrasta la referencia curricular con la norma vigente de esta gestión.</p>@endif</x-plegable-institucional><button type="button" class="ui-btn ui-btn-secondary" x-on:click="seccion='referencias'">Revisar disposiciones <i class="ph-duotone ph-arrow-right" aria-hidden="true"></i></button></article>
@endif
