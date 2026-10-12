@php
    $carrerasComparacion = collect($comparacion['carreras'])->keyBy('clave');
    $opcionesComparacion = $carrerasComparacion->map(fn ($carrera) => ['valor' => $carrera['clave'], 'etiqueta' => $carrera['nombre']])->prepend(['valor' => '', 'etiqueta' => 'Elige una carrera'])->values()->all();
    $pedidasComparacion = request('compare', []);
    $pedidasComparacion = is_array($pedidasComparacion) ? array_values(array_unique(array_filter($pedidasComparacion, fn ($clave) => is_string($clave) && $carrerasComparacion->has($clave)))) : [];
    $seleccionComparacion = array_slice($pedidasComparacion, 0, 3);
    $comparablesSeleccionadas = count($seleccionComparacion) >= 2 ? $seleccionComparacion : [];
@endphp
<section class="ui-panel comparacion-carreras" id="comparar-carreras" aria-labelledby="comparar-titulo">
    <p class="ui-kicker">Tú eliges qué investigar</p><h2 id="comparar-titulo" class="ui-title mt-2 text-2xl font-black">Compara dos o tres carreras</h2><p class="ui-subtitle mt-2">Elige las carreras que te interesan, incluso si no aparecen entre tus áreas destacadas. Después revisa qué universidades ofrecen información para cada una y compara sus mallas antes de decidir.</p>
    <form method="GET" action="{{ $destinoComparacion }}#comparar-carreras" class="mt-5 space-y-4" x-data="{ programasElegidos: @js(array_pad($seleccionComparacion, 3, '')) }">
        <input type="hidden" name="explore" value="compare">
        <div class="grid gap-4 lg:grid-cols-3">@for($indiceComparacion = 0; $indiceComparacion < 3; $indiceComparacion++)<x-selector-institucional :identificador="'comparar-carrera-'.$indiceComparacion" :etiqueta="'Carrera '.($indiceComparacion + 1).($indiceComparacion === 2 ? ' (opcional)' : '')" nombre="compare[]" :enlace="'programasElegidos['.$indiceComparacion.']'" :valorInicial="$seleccionComparacion[$indiceComparacion] ?? ''" :opciones="$opcionesComparacion" />@endfor</div>
        <div class="flex flex-wrap items-center gap-3"><button class="ui-btn-primary" type="submit" x-bind:disabled="new Set(programasElegidos.filter(Boolean)).size < 2 || programasElegidos.filter(Boolean).length !== new Set(programasElegidos.filter(Boolean)).size"><i class="ph-duotone ph-git-diff" aria-hidden="true"></i>Comparar carreras</button><p class="ui-subtitle text-sm">Elige al menos dos carreras distintas; puedes dejar la tercera vacía.</p></div>
    </form>
    @if($comparablesSeleccionadas !== [])
        <section class="mt-6" aria-labelledby="grafico-perfil-titulo"><h3 id="grafico-perfil-titulo" class="font-bold">Tus seis áreas de intereses</h3><p class="ui-subtitle mt-2 text-sm">Las barras representan tus respuestas, de 0 a 20. Las referencias ocupacionales solo aportan letras; no inventamos puntajes para dibujar un segundo perfil.</p><div class="comparacion-barras mt-4">@foreach($comparacion['perfil']['areas'] as $areaComparada)<div><span>{{ $areaComparada['codigo'] }} · {{ $areaComparada['nombre'] }}</span><span class="comparacion-pista" aria-hidden="true">@if($areaComparada['porcentaje'] !== null)<span style="width:{{ $areaComparada['porcentaje'] }}%"></span>@endif</span><strong>{{ $areaComparada['puntaje'] === null ? 'Sin dato' : $areaComparada['puntaje'].'/20' }}</strong></div>@endforeach</div><p class="ui-subtitle mt-3 text-sm">{{ $comparacion['perfil']['nota'] }}</p></section>
        <div class="mt-6 grid items-start gap-5 lg:grid-cols-2 xl:grid-cols-3">@foreach($comparablesSeleccionadas as $claveComparada)
            @php
                $opcionComparada = $carrerasComparacion->get($claveComparada);
            @endphp
            <article class="ui-card p-4"><p class="ui-kicker">Carrera que quieres explorar</p><h3 class="ui-title mt-2 text-lg font-black">{{ $opcionComparada['nombre'] }}</h3>
            @forelse($opcionComparada['referencias'] as $referenciaComparada)<x-plegable-institucional class="comparacion-referencia mt-4" :etiqueta="'Áreas '.($referenciaComparada['codigo'] ?? 'por revisar')" :descripcion="$loop->first ? 'Comienza por esta perspectiva de intereses.' : 'Abre otra perspectiva vinculada con esta carrera.'" icono="ph-fingerprint" :abierto="$loop->first" :compacto="true"><h4 class="font-bold">{{ $referenciaComparada['titulo'] }}</h4><p class="ui-subtitle mt-1 text-sm">Referencia ocupacional {{ $referenciaComparada['codigo'] ?? 'sin código verificable' }}</p>
                @if(data_get($referenciaComparada, 'universidades_origen'))<p class="ui-subtitle mt-1 text-xs">Incluida en la ficha de {{ implode(', ', $referenciaComparada['universidades_origen']) }}.</p>@endif
                @if($referenciaComparada['cantidad'] !== null)<p class="mt-3 font-bold">Comparten {{ $referenciaComparada['cantidad'] }} área(s){{ $referenciaComparada['compartidas'] !== [] ? ': '.implode(', ', array_map(fn ($codigo) => \App\Support\Orientacion\ExperienciaIntereses::AREAS[$codigo]['nombre'], $referenciaComparada['compartidas'])) : '' }}.</p><p class="ui-subtitle mt-1 text-xs">{{ $referenciaComparada['cantidad'] }} de {{ $referenciaComparada['denominador_estudiante'] }} de tus áreas destacadas{{ $comparacion['perfil']['empatadas_corte'] ? ' (incluye el empate)' : '' }}; {{ $referenciaComparada['cantidad'] }} de {{ $referenciaComparada['denominador_referencia'] }} de esta referencia.</p>@endif
                @include('estudiante.orientacion.partes.mapa-intereses-carrera', ['perfilComparacion' => $comparacion['perfil'], 'referenciaIntereses' => $referenciaComparada])
                <p class="ui-subtitle mt-2 text-sm">{{ $referenciaComparada['nota'] }}</p>@if($referenciaComparada['hipotesis'])<p class="ui-subtitle mt-2 text-sm">La relación entre carrera y ocupación es una interpretación o propuesta, no una equivalencia confirmada.</p>@endif
                <x-plegable-institucional etiqueta="Consultar el respaldo" icono="ph-file-text" :compacto="true" class="mt-3">@foreach($referenciaComparada['fuentes'] as $fuenteComparada)<a class="ui-btn-secondary mt-2 text-sm" href="{{ data_get($fuenteComparada, 'reference') }}" target="_blank" rel="noopener noreferrer">{{ data_get($fuenteComparada, 'title') ?: 'Referencia documentada' }}<span class="sr-only"> (nueva pestaña)</span></a>@endforeach</x-plegable-institucional>
            </x-plegable-institucional>@empty<p class="ui-subtitle mt-4 text-sm">{{ $opcionComparada['nota'] }}</p>@endforelse
            <div class="mt-4 rounded-xl border border-[var(--ui-border)] bg-[var(--ui-surface-soft)] p-3">
                <h4 class="font-bold"><i class="ph-duotone ph-buildings mr-2" aria-hidden="true"></i>Universidades para investigar</h4>
                <p class="ui-subtitle mt-1 text-xs">La malla y la preparación se revisan por programa universitario. Una sede no representa a las demás.</p>
                <ul class="mt-3 space-y-3">
                    @forelse($opcionComparada['ofertas'] as $ofertaComparada)
                        @php($programaComparado = collect($opcionComparada['programas'])->firstWhere('clave', $ofertaComparada['clave']))
                        <li class="rounded-lg border border-[var(--ui-border)] bg-[var(--ui-surface)] p-3 text-sm">
                            <div class="flex flex-wrap items-baseline justify-between gap-1"><strong>{{ $ofertaComparada['universidad'] }}</strong><span class="ui-subtitle text-xs">{{ $ofertaComparada['estado'] === 'DOCUMENTADA' ? 'Programa documentado en el estudio' : 'Referencia de universidad; oferta por confirmar' }}</span></div>
                            @if($programaComparado)
                                <x-plegable-institucional etiqueta="Ver malla y preparación de esta sede" icono="ph-books" :compacto="true" class="mt-3">
                                    @if($programaComparado['materias'])<p class="ui-subtitle text-sm">Materias documentadas de {{ $ofertaComparada['universidad'] }}:</p><ul class="mt-2 list-disc space-y-1 pl-5">@foreach(array_slice($programaComparado['materias'], 0, 6) as $materiaComparada)<li>{{ \Illuminate\Support\Str::ucfirst($materiaComparada) }}</li>@endforeach</ul>@else<p class="ui-subtitle text-sm">Aún falta la malla detallada de esta sede.</p>@endif
                                    @if($programaComparado['refuerzos'])<h5 class="mt-3 font-bold">Contenidos para prepararte</h5><p class="ui-subtitle text-xs">Propuesta del estudio a partir de este programa; acuerda tu acción con un orientador.</p><ul class="mt-2 list-disc space-y-2 pl-5">@foreach(array_slice($programaComparado['refuerzos'], 0, 3) as $refuerzoComparado)<li>{{ \Illuminate\Support\Str::ucfirst(data_get($refuerzoComparado, 'competency', 'Contenido por revisar')) }}</li>@endforeach</ul>@endif
                                    @foreach(array_slice($programaComparado['fuentes'], 0, 3) as $fuentePrograma)<a class="intereses-oferta-fuente" href="{{ $fuentePrograma['reference'] }}" target="_blank" rel="noopener noreferrer">{{ data_get($fuentePrograma, 'title', 'Consultar fuente académica') }}<span class="sr-only"> (nueva pestaña)</span><i class="ph-duotone ph-arrow-square-out" aria-hidden="true"></i></a>@endforeach
                                </x-plegable-institucional>
                            @endif
                            @if($ofertaComparada['fuentes_economicas'])<div class="intereses-oferta-economia"><span>Ingreso y ayudas</span>@foreach($ofertaComparada['fuentes_economicas'] as $fuenteEconomica)<a href="{{ $fuenteEconomica['url'] }}" target="_blank" rel="noopener noreferrer" class="font-bold underline">{{ $fuenteEconomica['titulo'] }}<span class="sr-only"> (nueva pestaña)</span></a>@endforeach</div>@endif
                        </li>
                    @empty
                        <li class="ui-subtitle text-sm">Todavía no hay una universidad con fuente para esta carrera.</li>
                    @endforelse
                </ul>
                <p class="ui-subtitle mt-3 text-xs">El costo total depende de aranceles, becas, materiales y transporte. Aún no tenemos cifras verificadas para comparar precios; consulta cada sede.</p>
            </div>
            <a class="ui-btn-secondary mt-4" href="{{ route('estudiante.futuro', ['explore' => 'all', 'career' => $opcionComparada['clave']]) }}">Ver universidades y mallas<i class="ph-duotone ph-arrow-right" aria-hidden="true"></i></a>
        </article>@endforeach</div>
        <x-plegable-institucional etiqueta="Cómo leer esta comparación" descripcion="Una coincidencia descriptiva no equivale a una carrera compatible." icono="ph-info" class="mt-5"><p class="ui-subtitle">{{ $comparacion['metodo'] }}</p><p class="ui-subtitle mt-3">La ocupación y el programa universitario son cosas distintas. No hay porcentaje de admisión ni de éxito. Las notas y los intereses no se mezclan en un puntaje único.</p><a class="ui-btn-secondary mt-3" href="{{ $comparacion['fuente_metodo'] }}" target="_blank" rel="noopener noreferrer">Método de correspondencia de O*NET<span class="sr-only"> (nueva pestaña)</span></a></x-plegable-institucional>
    @endif
</section>
