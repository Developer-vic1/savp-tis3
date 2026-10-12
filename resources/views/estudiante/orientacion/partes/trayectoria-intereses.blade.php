@php
    $trayectoria = \App\Support\Orientacion\TrayectoriaIntereses::presentar(['payload' => $payload ?? [], 'gestiones' => $gestiones ?? []]);
    $notaTexto = static fn ($nota) => $nota === null ? '—' : rtrim(rtrim(number_format($nota, 2, ',', ''), '0'), ',');
@endphp
<section class="intereses-panel trayectoria-estudiante" id="mi-trayectoria" aria-labelledby="trayectoria-titulo" x-data="{ gestion: @js($trayectoria['seleccionada']), vista: 'notas' }">
    <div class="intereses-cabecera">
        <div><p class="intereses-etiqueta">Tu punto de partida</p><h2 id="trayectoria-titulo" class="mt-2">Tus notas por gestión y materia</h2><p class="intereses-texto mt-2">Revisa cada nota, compara las materias y descubre de dónde sale su promedio. Tus intereses se interpretan por separado.</p></div>
        <a class="ui-btn-secondary" href="{{ route('estudiante.preparacion') }}">Ver Mi preparación<i class="ph-duotone ph-arrow-right" aria-hidden="true"></i></a>
    </div>
    @if(count($trayectoria['gestiones']))
        <div class="trayectoria-filtro"><x-selector-institucional identificador="trayectoria-gestion" etiqueta="Gestión que quieres revisar" enlace="gestion" :opciones="$trayectoria['opciones']" :valorInicial="$trayectoria['seleccionada']" descripcionId="trayectoria-gestion-ayuda" /><p id="trayectoria-gestion-ayuda" class="intereses-texto">Cada gestión conserva sus propias materias y períodos. Cambiar la selección solo cambia esta consulta.</p></div>
        <div class="trayectoria-vistas" role="group" aria-label="Cómo quieres revisar tus materias">
            <button type="button" class="ui-btn-secondary" @click="vista = 'notas'" :aria-pressed="vista === 'notas'"><i class="ph-duotone ph-table" aria-hidden="true"></i>Ver notas y cálculo</button>
            <button type="button" class="ui-btn-secondary" @click="vista = 'comparar'" :aria-pressed="vista === 'comparar'"><i class="ph-duotone ph-chart-bar" aria-hidden="true"></i>Comparar materias</button>
        </div>
        @foreach($trayectoria['gestiones'] as $gestion)
            <div x-show="gestion === @js($gestion['codigo'])" @if($gestion['codigo'] !== $trayectoria['seleccionada']) x-cloak @endif class="trayectoria-gestion">
                <div class="trayectoria-resumen"><h3>{{ $gestion['etiqueta'] }}</h3><div class="trayectoria-conteos"><span><strong>{{ count($gestion['materias']) }}</strong> materias</span><span><strong>{{ $gestion['registros'] }}</strong> notas disponibles</span><span><strong>{{ count($gestion['periodos']) }}</strong> períodos registrados</span>@if($gestion['trimestres'])<span><strong>{{ $gestion['trimestres'] }}</strong> trimestres identificados</span>@endif</div></div>
                @if(!count($gestion['materias']))
                    <div class="intereses-aviso mt-4"><h3>No hay notas disponibles en esta gestión</h3><p>Esto no significa que tu rendimiento sea cero. Aquí aparecerán las notas que estén registradas y disponibles para consultar.</p></div>
                @else
                    <div x-show="vista === 'comparar'" x-cloak class="trayectoria-comparacion"><h4>Compara los promedios de tus materias</h4><p class="intereses-texto mt-2">Promedio de notas disponibles, sin ponderación. Una materia con menos notas no resume el mismo recorrido que otra con más registros.</p><ul class="trayectoria-barras" aria-label="Comparación de promedios por materia">@foreach($gestion['materias'] as $materia)<li><div class="trayectoria-barra-cabecera"><strong>{{ $materia['nombre'] }}</strong><span>{{ $notaTexto($materia['promedio']) }}@if($materia['escala'][0] !== null && $materia['escala'][1] !== null)<small> · escala {{ $notaTexto($materia['escala'][0]) }}–{{ $notaTexto($materia['escala'][1]) }}</small>@endif</span></div>@if($materia['barra'] !== null)<div class="trayectoria-barra-pista" aria-hidden="true"><span style="width: {{ $materia['barra'] }}%"></span></div>@endif<p>{{ $materia['cantidad'] }} nota(s) · {{ $materia['periodos_con_nota'] }} período(s) con nota @if($materia['escalas_distintas']) · Escalas distintas: consulta las notas por separado.@endif</p></li>@endforeach</ul></div>
                    <x-plegable-institucional x-show="vista === 'notas'" etiqueta="Tus notas y el promedio de cada materia" descripcion="Consulta los valores de cada período y cómo se obtiene el promedio." icono="ph-table" :abierto="true" class="mt-5">
                        <div class="trayectoria-tabla-contenedor" tabindex="0" role="region" aria-label="Notas por materia de {{ $gestion['etiqueta'] }}">
                            <table class="trayectoria-tabla"><caption>{{ $gestion['etiqueta'] }} · Notas disponibles por período y promedio simple</caption><thead><tr><th scope="col">Materia</th>@foreach($gestion['periodos'] as $periodo)<th scope="col">{{ $periodo['nombre'] }}</th>@endforeach<th scope="col">Promedio de notas disponibles</th><th scope="col">Cómo se calcula</th></tr></thead><tbody>
                            @foreach($gestion['materias'] as $materia)<tr><th scope="row">{{ $materia['nombre'] }}</th>@foreach($gestion['periodos'] as $periodo)<td>@if(array_key_exists($periodo['clave'], $materia['celdas']))@foreach($materia['celdas'][$periodo['clave']] as $nota)<span class="trayectoria-nota">{{ $notaTexto($nota) }}@if($nota === null)<span class="sr-only">Sin nota disponible</span>@endif</span>@endforeach @else<span aria-label="Sin nota disponible">—</span>@endif</td>@endforeach<td><strong>{{ $notaTexto($materia['promedio']) }}</strong><small>{{ $materia['cantidad'] }} nota(s)</small>@if($materia['sin_nota'])<small>Sin nota en {{ $materia['sin_nota'] }} de los períodos observados.</small>@endif</td><td>@if($materia['promedio'] !== null)<span>({{ implode(' + ', array_map($notaTexto, $materia['notas'])) }}) ÷ {{ $materia['cantidad'] }}</span><small>Suma {{ $notaTexto($materia['suma']) }} ÷ {{ $materia['cantidad'] }} = {{ $notaTexto($materia['promedio']) }}</small>@elseif($materia['escalas_distintas'])<span>No se promedian escalas diferentes.</span>@else<span>No hay notas para calcularlo.</span>@endif @if($materia['varias_notas'])<small>Hay varios registros en un mismo período; cada nota disponible cuenta una vez.</small>@endif</td></tr>@endforeach
                            </tbody></table>
                        </div>
                        <p class="intereses-texto mt-3">«—» significa que no hay una nota disponible; una nota de 0 sí participa del cálculo. Los períodos mostrados son los que aparecen en los registros de esta gestión: no sabemos si faltan otros períodos por registrar.</p>
                    </x-plegable-institucional>
                @endif
                <p class="trayectoria-limite">Este promedio describe las notas que puedes ver aquí. No es un promedio anual oficial, no aplica ponderaciones institucionales y no determina aprobación, aptitud ni una carrera para ti.</p>
            </div>
        @endforeach
    @else
        <div class="intereses-aviso mt-4"><h3>Aún no hay notas con una gestión identificada</h3><p>Cuando existan registros disponibles, podrás consultar aquí cada materia y su cálculo. La ausencia de notas no representa bajo rendimiento.</p></div>
    @endif
</section>
